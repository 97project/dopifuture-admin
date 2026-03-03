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

        ActivityLog::log('purchase_added', 'licenses', $license, [], [
            'old_total_seats' => $oldTotal,
            'new_total_seats' => $license->fresh()->totalSeats(),
            'added_seats' => $purchase->seat_count,
            'amount' => $purchase->amount,
        ]);

        return redirect()->route('admin.licenses.show', $license)
            ->with('success', __('admin.purchase_added'));
    }
}
