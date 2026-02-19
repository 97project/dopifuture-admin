<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    /**
     * List all permissions grouped by module.
     */
    public function index()
    {
        $permissions = Permission::orderBy('name')
            ->get()
            ->groupBy(fn($p) => explode('.', $p->name)[0] ?? 'other');

        return view('admin.permissions.index', compact('permissions'));
    }

    /**
     * Update alias and deprecated status for a single permission.
     */
    public function update(Request $request, Permission $permission)
    {
        $request->validate([
            'alias_tr' => 'nullable|string|max:100',
            'alias_en' => 'nullable|string|max:100',
        ]);

        $permission->update([
            'alias_tr' => $request->input('alias_tr'),
            'alias_en' => $request->input('alias_en'),
        ]);

        activity()
            ->performedOn($permission)
            ->causedBy(auth()->user())
            ->withProperties(['alias_tr' => $permission->alias_tr, 'alias_en' => $permission->alias_en])
            ->log('permission_alias_updated');

        return back()->with('success', __('admin.saved'));
    }

    /**
     * Run permissions:sync via web.
     */
    public function sync()
    {
        \Artisan::call('permissions:sync');

        return back()->with('success', __('admin.permissions_synced'));
    }
}
