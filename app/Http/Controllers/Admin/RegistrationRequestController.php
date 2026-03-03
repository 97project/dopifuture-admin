<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RegistrationRequest;
use App\Models\School;
use App\Models\License;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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

        $countries = \App\Models\Country::orderBy('name')->get();

        return view('admin.registration-requests.show', compact('registrationRequest', 'countries'));
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

    public function convertToSchool(Request $request, RegistrationRequest $registrationRequest)
    {
        $this->authorize('update', $registrationRequest);

        $request->validate([
            'school_name' => 'required|string|max:200',
            'country' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:150',
            'create_license' => 'nullable|boolean',
            'seat_count' => 'required_if:create_license,1|nullable|integer|min:1',
            'license_months' => 'nullable|integer|min:1|max:60',
            'create_user' => 'nullable|boolean',
        ]);

        $result = DB::transaction(function () use ($request, $registrationRequest) {
            // 1. Create School
            $school = School::create([
                'name' => $request->input('school_name'),
                'country' => $request->input('country'),
                'state' => $request->input('state'),
                'city' => $request->input('city'),
                'address' => $request->input('address'),
                'phone' => $request->input('phone') ?: $registrationRequest->phone,
                'email' => $request->input('email') ?: $registrationRequest->email,
                'is_active' => true,
            ]);

            ActivityLog::log('created', 'schools', $school, [
                'source' => 'registration_request',
                'registration_request_id' => $registrationRequest->id,
            ]);

            $license = null;
            $user = null;

            // 2. Optionally create License
            if ($request->boolean('create_license')) {
                $months = $request->input('license_months', 12);
                $license = License::create([
                    'school_id' => $school->id,
                    'user_id' => auth()->id(),
                    'seat_count' => $request->input('seat_count', 30),
                    'starts_at' => now(),
                    'expires_at' => now()->addMonths($months),
                    'is_active' => true,
                    'notes' => __('admin.license_from_request', ['id' => $registrationRequest->id]),
                ]);

                ActivityLog::log('created', 'licenses', $license, [
                    'source' => 'registration_request',
                ]);
            }

            // 3. Optionally create portal User for contact
            if ($request->boolean('create_user') && $registrationRequest->email) {
                $existingUser = User::where('email', $registrationRequest->email)->first();

                if (!$existingUser) {
                    $password = Str::random(10);
                    $user = User::create([
                        'name' => $registrationRequest->contact_name . ' ' . $registrationRequest->contact_surname,
                        'email' => $registrationRequest->email,
                        'password' => Hash::make($password),
                        'is_active' => true,
                    ]);

                    // Assign portal role if exists
                    $portalRole = \Spatie\Permission\Models\Role::where('name', 'portal')->first();
                    if ($portalRole) {
                        $user->assignRole($portalRole);
                    }

                    // Associate user with school
                    if (method_exists($school, 'admins')) {
                        $school->admins()->attach($user->id);
                    }

                    ActivityLog::log('created', 'users', $user, [
                        'source' => 'registration_request',
                        'temporary_password' => $password,
                    ]);

                    $user->temp_password = $password;
                }
            }

            // 4. Update registration request status
            $registrationRequest->update([
                'status' => 'approved',
                'admin_notes' => trim(
                    ($registrationRequest->admin_notes ? $registrationRequest->admin_notes . "\n" : '') .
                    '—— ' . now()->format('d.m.Y H:i') . ' ——' . "\n" .
                    __('admin.converted_to_school') . ': ' . $school->name .
                    ($license ? ' | ' . __('admin.license') . ' #' . $license->id : '') .
                    ($user ? ' | ' . __('admin.user') . ': ' . $user->email : '')
                ),
            ]);

            ActivityLog::log('converted_to_school', 'registration_requests', $registrationRequest, [], [
                'school_id' => $school->id,
                'license_id' => $license?->id,
                'user_id' => $user?->id ?? null,
            ]);

            return compact('school', 'license', 'user');
        });

        $msg = __('admin.request_converted_success');
        if (isset($result['user']) && $result['user'] && isset($result['user']->temp_password)) {
            $msg .= ' ' . __('admin.temp_password') . ': ' . $result['user']->temp_password;
        }

        return redirect()->route('admin.schools.show', $result['school'])
            ->with('success', $msg);
    }
}

