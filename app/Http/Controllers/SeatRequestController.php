<?php

namespace App\Http\Controllers;

use App\Models\SeatRequest;
use App\Models\License;
use Illuminate\Http\Request;

class SeatRequestController extends Controller
{
    /**
     * Portal: School admin submits a seat request.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'school_id'       => 'required|exists:schools,id',
            'requested_seats' => 'required|integer|min:1|max:10000',
            'reason'          => 'nullable|string|max:1000',
        ]);

        SeatRequest::create([
            'school_id'       => $validated['school_id'],
            'user_id'         => auth()->id(),
            'requested_seats' => $validated['requested_seats'],
            'reason'          => $validated['reason'] ?? null,
            'status'          => 'pending',
        ]);

        return redirect()->route('portal.dashboard')
            ->with('success', 'Your seat request has been submitted successfully. You will be notified once it is reviewed.');
    }

    /**
     * Admin: List all seat requests.
     */
    public function adminIndex()
    {
        $requests = SeatRequest::with(['school', 'user', 'reviewer'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.seat-requests.index', compact('requests'));
    }

    /**
     * Admin: Approve a seat request — add seats to the school's license.
     */
    public function approve(Request $request, SeatRequest $seatRequest)
    {
        $validated = $request->validate([
            'admin_notes'  => 'nullable|string|max:500',
            'seats_to_add' => 'nullable|integer|min:1',
        ]);

        $seatsToAdd = $validated['seats_to_add'] ?? $seatRequest->requested_seats;

        // Find or create the license for the school
        $license = License::firstOrCreate(
            ['school_id' => $seatRequest->school_id],
            ['seat_count' => 0, 'is_active' => true, 'starts_at' => now(), 'expires_at' => now()->addYear()]
        );

        // Add seats
        $license->increment('seat_count', $seatsToAdd);

        // Update request status
        $seatRequest->update([
            'status'      => 'approved',
            'admin_notes' => $validated['admin_notes'] ?? "Approved: +{$seatsToAdd} seats added.",
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', "Seat request approved. {$seatsToAdd} seats added to {$seatRequest->school->name}.");
    }

    /**
     * Admin: Reject a seat request.
     */
    public function reject(Request $request, SeatRequest $seatRequest)
    {
        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:500',
        ]);

        $seatRequest->update([
            'status'      => 'rejected',
            'admin_notes' => $validated['admin_notes'] ?? 'Request rejected.',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Seat request has been rejected.');
    }
}
