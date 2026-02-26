<?php

namespace App\Http\Controllers;

use App\Models\License;
use App\Models\LicensePurchase;
use App\Models\School;
use Illuminate\Http\Request;

class PortalLicenseController extends Controller
{
    public function __construct()
    {
        // Teacher and student cannot access license management at all
        $this->middleware(function ($request, $next) {
            if (auth()->user()->hasAnyRole(['teacher', 'student'])) {
                abort(403);
            }
            return $next($request);
        });
    }

    /**
     * Guard: only school-admin can mutate licenses.
     * school-principal → read-only (index, show).
     */
    private function guardLicenseAdmin(): void
    {
        if (auth()->user()->hasRole('school-principal')) {
            abort(403, __('auth.insufficient_permissions'));
        }
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $query = License::with('school');

        if ($user->hasAnyRole(['school-admin', 'school-principal'])) {
            $schoolIds = $user->schools()->pluck('schools.id');
            $query->whereIn('school_id', $schoolIds);
        }

        if ($request->filled('search')) {
            $query->whereHas('school', fn($q) => $q->where('name', 'like', "%{$request->search}%"));
        }

        $licenses = $query->latest()->paginate(15);
        return view('portal.licenses.index', compact('licenses'));
    }

    public function show(License $license)
    {
        $this->authorizeSchool($license->school_id);
        $license->load(['school', 'purchases' => fn($q) => $q->latest('purchased_at')]);
        return view('portal.licenses.show', compact('license'));
    }

    public function addPurchase(Request $request, License $license)
    {
        $this->guardLicenseAdmin();
        $this->authorizeSchool($license->school_id);

        $user = auth()->user();
        if (!$user->hasAnyRole(['super-admin', 'admin', 'license-manager'])) {
            abort(403);
        }

        $data = $request->validate([
            'seat_count' => 'required|integer|min:1',
            'amount' => 'nullable|numeric|min:0',
            'purchased_at' => 'required|date',
            'notes' => 'nullable|string|max:500',
        ]);

        $license->purchases()->create($data);

        return redirect()->route('portal.licenses.show', $license)
            ->with('success', __('admin.purchase_added'));
    }

    public function create()
    {
        $this->guardLicenseAdmin();
        $schools = $this->getAvailableSchools();
        return view('portal.licenses.form', ['license' => new License, 'schools' => $schools]);
    }

    public function store(Request $request)
    {
        $this->guardLicenseAdmin();
        $data = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'seat_count' => 'required|integer|min:1',
            'starts_at' => 'required|date',
            'expires_at' => 'required|date|after:starts_at',
            'notes' => 'nullable|string|max:500',
        ]);

        $this->authorizeSchool($data['school_id']);

        // Single license per school — prevent duplicates
        if (License::where('school_id', $data['school_id'])->exists()) {
            $msg = __('admin.license_already_exists');
            return back()->withErrors(['school_id' => $msg])->withInput();
        }

        License::create([
            'school_id' => $data['school_id'],
            'user_id' => auth()->id(),
            'seat_count' => $data['seat_count'],
            'used_seats' => 0,
            'starts_at' => $data['starts_at'],
            'expires_at' => $data['expires_at'],
            'is_active' => true,
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()->route('portal.licenses.index')
            ->with('success', __('admin.license_created'));
    }

    public function edit(License $license)
    {
        $this->guardLicenseAdmin();
        $this->authorizeSchool($license->school_id);
        $schools = $this->getAvailableSchools();
        return view('portal.licenses.form', compact('license', 'schools'));
    }

    public function update(Request $request, License $license)
    {
        $this->guardLicenseAdmin();
        $this->authorizeSchool($license->school_id);

        $data = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'seat_count' => 'required|integer|min:1',
            'used_seats' => 'nullable|integer|min:0',
            'starts_at' => 'required|date',
            'expires_at' => 'required|date|after:starts_at',
            'is_active' => 'nullable|boolean',
            'notes' => 'nullable|string|max:500',
        ]);

        $license->update([
            'school_id' => $data['school_id'],
            'seat_count' => $data['seat_count'],
            'used_seats' => $data['used_seats'] ?? $license->used_seats,
            'starts_at' => $data['starts_at'],
            'expires_at' => $data['expires_at'],
            'is_active' => $request->boolean('is_active'),
            'notes' => $data['notes'] ?? $license->notes,
        ]);

        return redirect()->route('portal.licenses.index')
            ->with('success', __('admin.license_updated'));
    }

    public function destroy(License $license)
    {
        $this->guardLicenseAdmin();
        $this->authorizeSchool($license->school_id);
        $license->delete();
        return redirect()->route('portal.licenses.index')
            ->with('success', __('admin.license_deleted'));
    }

    private function getAvailableSchools()
    {
        $user = auth()->user();
        if ($user->hasAnyRole(['super-admin', 'admin', 'license-manager'])) {
            return School::active()->get();
        }
        return $user->schools()->get();
    }

    private function authorizeSchool(int $schoolId): void
    {
        $user = auth()->user();
        if ($user->hasAnyRole(['super-admin', 'admin', 'license-manager']))
            return;
        if ($user->schools()->where('schools.id', $schoolId)->exists())
            return;
        abort(403);
    }
}
