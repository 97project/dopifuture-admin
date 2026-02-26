<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class PortalUserController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = User::with('roles');

        // Scope by role
        if ($user->hasAnyRole(['school-admin', 'school-principal'])) {
            $schoolIds = $user->schools()->pluck('schools.id');
            $userIds = \DB::table('school_user')->whereIn('school_id', $schoolIds)->pluck('user_id');
            $query->whereIn('id', $userIds);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('surname', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%");
            });
        }

        if ($request->filled('role')) {
            $query->role($request->role);
        }

        $users = $query->latest()->paginate(20);
        $roles = Role::pluck('name');
        return view('portal.users.index', compact('users', 'roles'));
    }

    public function show(User $user)
    {
        $user->load(['roles', 'schools', 'classes.school', 'applications']);
        return view('portal.users.show', compact('user'));
    }

    public function create()
    {
        $roles = $this->getAllowedRoles();
        $schools = $this->getAvailableSchools();
        return view('portal.users.form', ['editUser' => new User, 'roles' => $roles, 'schools' => $schools]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:60',
            'surname' => 'nullable|string|max:60',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|string|exists:roles,name',
            'school_id' => 'nullable|exists:schools,id',
        ]);

        $newUser = User::create([
            'name' => $data['name'],
            'surname' => $data['surname'] ?? null,
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'status' => 'active',
            'locale' => 'tr',
            'timezone' => 'Europe/Istanbul',
            'email_verified_at' => now(),
        ]);

        $newUser->assignRole($data['role']);

        // Attach to school
        if (!empty($data['school_id'])) {
            \DB::table('school_user')->insertOrIgnore([
                'school_id' => $data['school_id'],
                'user_id' => $newUser->id,
                'role' => $data['role'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()->route('portal.users.index')
            ->with('success', __('admin.user_created'));
    }

    public function edit(User $user)
    {
        $roles = $this->getAllowedRoles();
        $schools = $this->getAvailableSchools();
        return view('portal.users.form', ['editUser' => $user, 'roles' => $roles, 'schools' => $schools]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:60',
            'surname' => 'nullable|string|max:60',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6',
            'role' => 'required|string|exists:roles,name',
            'status' => 'required|in:active,inactive',
        ]);

        $user->update([
            'name' => $data['name'],
            'surname' => $data['surname'] ?? $user->surname,
            'email' => $data['email'],
            'status' => $data['status'],
        ]);

        if (!empty($data['password'])) {
            $user->update(['password' => Hash::make($data['password'])]);
        }

        $user->syncRoles([$data['role']]);

        return redirect()->route('portal.users.index')
            ->with('success', __('admin.user_updated'));
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('portal.users.index')
            ->with('success', __('admin.user_deleted'));
    }

    private function getAllowedRoles(): \Illuminate\Support\Collection
    {
        $user = auth()->user();
        if ($user->hasAnyRole(['super-admin', 'admin'])) {
            return Role::pluck('name');
        }
        // School-admin can only manage limited roles
        return collect(['teacher', 'student', 'school-principal']);
    }

    private function getAvailableSchools()
    {
        $user = auth()->user();
        if ($user->hasAnyRole(['super-admin', 'admin', 'license-manager'])) {
            return School::active()->get();
        }
        return $user->schools()->get();
    }
}
