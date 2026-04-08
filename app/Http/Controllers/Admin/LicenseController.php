<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\School;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class LicenseController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', License::class);

        $query = License::with(['school', 'admin']);

        if ($search = $request->input('search')) {
            $query->whereHas('school', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }
        if ($schoolId = $request->input('school_id')) {
            $query->where('school_id', $schoolId);
        }
        if ($request->filled('status')) {
            if ($request->input('status') === 'active') {
                $query->active();
            } elseif ($request->input('status') === 'expired') {
                $query->where(function ($q) {
                    $q->where('expires_at', '<', now());
                });
            } else {
                $query->where('is_active', false);
            }
        }

        $sort = $request->input('sort', 'created_at');
        $dir = $request->input('dir', 'desc');
        $allowed = ['seat_count', 'used_seats', 'starts_at', 'expires_at', 'created_at'];
        if (in_array($sort, $allowed)) {
            $query->orderBy($sort, $dir === 'asc' ? 'asc' : 'desc');
        }

        $licenses = $query->paginate(20)->withQueryString();
        $schools = School::active()->get();

        $stats = [
            'total' => License::count(),
            'active' => License::active()->count(),
            'expired' => License::where('expires_at', '<', now())->count(),
            'total_seats' => License::active()->sum('seat_count'),
            'used_seats' => License::active()->sum('used_seats'),
        ];

        return view('admin.licenses.index', compact('licenses', 'schools', 'stats'));
    }

    public function show(License $license)
    {
        $this->authorize('view', $license);
        $license->load(['school', 'admin', 'purchases']);
        return view('admin.licenses.show', compact('license'));
    }

    public function create()
    {
        $this->authorize('create', License::class);
        $schools = School::active()->get();
        return view('admin.licenses.create', compact('schools'));
    }

    public function store(\App\Http\Requests\LicenseStoreRequest $request)
    {
        // Tek lisans kuralı — portal ile tutarlı
        if (License::where('school_id', $request->input('school_id'))->exists()) {
            return back()->withErrors(['school_id' => __('admin.license_already_exists')])->withInput();
        }

        $license = License::create([
            'school_id' => $request->input('school_id'),
            'user_id' => auth()->id(),
            'seat_count' => $request->input('seat_count'),
            'starts_at' => $request->input('starts_at'),
            'expires_at' => $request->input('expires_at'),
            'notes' => $request->input('notes'),
            'is_active' => $request->boolean('is_active'),
        ]);

        ActivityLog::log('created', 'licenses', $license);

        // Send license activation email to school admin
        try {
            $school = $license->school;
            $schoolAdmin = $school->admins->first() ?? $school->users->first();
            if ($schoolAdmin) {
                \Illuminate\Support\Facades\Mail::to($schoolAdmin->email)
                    ->send(new \App\Mail\LicenseActivated($license, $school, $schoolAdmin->locale ?? 'en'));
            }
        } catch (\Throwable $e) {
            \Log::channel('daily')->error('[License] Activation mail failed', ['error' => $e->getMessage()]);
        }

        return redirect()->route('admin.licenses.index')
            ->with('success', __('admin.license_created'));
    }

    public function edit(License $license)
    {
        $this->authorize('update', $license);
        $schools = School::active()->get();
        return view('admin.licenses.edit', compact('license', 'schools'));
    }

    public function update(\App\Http\Requests\LicenseUpdateRequest $request, License $license)
    {

        $license->update([
            'school_id' => $request->input('school_id'),
            'seat_count' => $request->input('seat_count'),
            'starts_at' => $request->input('starts_at'),
            'expires_at' => $request->input('expires_at'),
            'notes' => $request->input('notes'),
            'is_active' => $request->boolean('is_active'),
        ]);

        ActivityLog::log('updated', 'licenses', $license);

        return redirect()->route('admin.licenses.index')
            ->with('success', __('admin.license_updated'));
    }

    public function destroy(License $license)
    {
        $this->authorize('delete', $license);
        ActivityLog::log('deleted', 'licenses', $license);
        $license->delete();

        return redirect()->route('admin.licenses.index')
            ->with('success', __('admin.license_deleted'));
    }

    public function addPurchase(Request $request, License $license)
    {
        $this->authorize('update', $license);

        $request->validate([
            'seat_count' => 'required|integer|min:1',
            'amount' => 'nullable|numeric|min:0',
            'purchased_at' => 'nullable|date',
            'notes' => 'nullable|string|max:500',
        ]);

        $oldTotal = $license->totalSeats();

        $purchase = $license->purchases()->create([
            'seat_count' => $request->input('seat_count'),
            'amount' => $request->input('amount'),
            'purchased_at' => $request->input('purchased_at', now()),
            'notes' => $request->input('notes'),
        ]);

        $freshLicense = $license->fresh();
        ActivityLog::log('purchase_added', 'licenses', $license, [], [
            'old_total_seats' => $oldTotal,
            'new_total_seats' => $freshLicense->totalSeats(),
            'added_seats' => $purchase->seat_count,
            'amount' => $purchase->amount,
        ]);

        // Send seats added email to school admin
        try {
            $school = $license->school;
            $schoolAdmin = $school->admins->first() ?? $school->users->first();
            if ($schoolAdmin) {
                \Illuminate\Support\Facades\Mail::to($schoolAdmin->email)
                    ->send(new \App\Mail\LicenseSeatsAdded(
                        $school,
                        $purchase->seat_count,
                        $freshLicense->totalSeats(),
                        $freshLicense->used_seats ?? 0,
                        $schoolAdmin->locale ?? 'en'
                    ));
            }
        } catch (\Throwable $e) {
            \Log::channel('daily')->error('[License] Seats added mail failed', ['error' => $e->getMessage()]);
        }

        return redirect()->route('admin.licenses.show', $license)
            ->with('success', __('admin.purchase_added'));
    }
}
