<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\ActivityLog;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Role::class);

        $roles = Role::withCount('users', 'permissions')
            ->when($request->filled('search'), fn($q) => $q->where('name', 'like', '%' . $request->input('search') . '%'))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => Role::count(),
            'total_permissions' => Permission::count(),
            'total_assigned_users' => \DB::table('model_has_roles')->count(),
        ];

        return view('admin.roles.index', compact('roles', 'stats'));
    }

    public function show(Role $role)
    {
        Gate::authorize('view', $role);

        $role->loadCount(['users', 'permissions']);
        $permissions = $role->permissions->groupBy(fn($p) => explode('.', $p->name)[0]);
        $users = $role->users()->paginate(20);

        return view('admin.roles.show', compact('role', 'permissions', 'users'));
    }

    public function create()
    {
        Gate::authorize('create', Role::class);

        $permissions = Permission::orderBy('name')->get()->groupBy(fn($p) => explode('.', $p->name)[0]);
        return view('admin.roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Role::class);

        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'integer|exists:permissions,id',
        ]);

        $role = Role::create(['name' => $request->input('name'), 'guard_name' => 'web']);

        if ($request->filled('permissions')) {
            $role->syncPermissions($request->input('permissions'));
        }

        ActivityLog::log('created', 'roles', $role, ['name' => $role->name]);

        return redirect()->route('admin.roles.index')
            ->with('success', __('admin.role_created'));
    }

    public function edit(Role $role)
    {
        Gate::authorize('update', $role);

        $permissions = Permission::orderBy('name')->get()->groupBy(fn($p) => explode('.', $p->name)[0]);
        $rolePermissions = $role->permissions->pluck('id')->toArray();
        return view('admin.roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    public function update(Request $request, Role $role)
    {
        Gate::authorize('update', $role);

        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'integer|exists:permissions,id',
        ]);

        $role->update(['name' => $request->input('name')]);
        $role->syncPermissions($request->input('permissions', []));

        ActivityLog::log('updated', 'roles', $role, ['name' => $role->name]);

        return redirect()->route('admin.roles.index')
            ->with('success', __('admin.role_updated'));
    }

    public function destroy(Role $role)
    {
        Gate::authorize('delete', $role);

        if ($role->users()->count() > 0) {
            return back()->with('error', __('admin.role_has_users'));
        }

        ActivityLog::log('deleted', 'roles', $role, ['name' => $role->name]);
        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', __('admin.role_deleted'));
    }

    public function syncPermissions()
    {
        Gate::authorize('syncPermissions', Role::class);

        \Artisan::call('permissions:sync');
        $output = \Artisan::output();

        ActivityLog::log('permissions_synced', 'roles', null, ['output' => $output]);

        return back()->with('success', __('admin.permissions_synced'));
    }
}
