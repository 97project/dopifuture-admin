<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RegistrationRequest;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class RegistrationRequestController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', RegistrationRequest::class);

        $query = RegistrationRequest::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('school_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('contact_name', 'like', "%{$search}%");
            });
        }
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $requests = $query->latest()->paginate(20)->withQueryString();

        $stats = [
            'total' => RegistrationRequest::count(),
            'pending' => RegistrationRequest::whereIn('status', ['new', 'pending', 'processing'])->count(),
            'approved' => RegistrationRequest::where('status', 'approved')->count(),
            'rejected' => RegistrationRequest::where('status', 'rejected')->count(),
        ];

        return view('admin.registration-requests.index', compact('requests', 'stats'));
    }

    public function show(RegistrationRequest $registrationRequest)
    {
        $this->authorize('view', $registrationRequest);
        return view('admin.registration-requests.show', compact('registrationRequest'));
    }

    public function update(Request $request, RegistrationRequest $registrationRequest)
    {
        $this->authorize('update', $registrationRequest);

        $request->validate([
            'status' => 'required|in:new,processing,approved,rejected',
            'admin_notes' => 'nullable|string|max:2000',
        ]);

        $registrationRequest->update([
            'status' => $request->input('status'),
            'admin_notes' => $request->input('admin_notes'),
        ]);

        ActivityLog::log('updated', 'registration_requests', $registrationRequest);

        return redirect()->route('admin.registration-requests.index')
            ->with('success', __('admin.saved'));
    }

    public function destroy(RegistrationRequest $registrationRequest)
    {
        $this->authorize('delete', $registrationRequest);
        ActivityLog::log('deleted', 'registration_requests', $registrationRequest);
        $registrationRequest->delete();

        return redirect()->route('admin.registration-requests.index')
            ->with('success', __('admin.deleted'));
    }
}
