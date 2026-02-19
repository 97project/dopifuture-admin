<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AccountDeletionService;
use Illuminate\Http\Request;

class AccountDeletionController extends Controller
{
    public function __construct(protected AccountDeletionService $deletionService)
    {
    }

    /**
     * List users pending deletion (soft-deleted with status = inactive).
     */
    public function index(Request $request)
    {
        abort_unless(auth()->user()->can('users.delete'), 403);

        $query = User::onlyTrashed()->where('status', 'inactive');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $deletedUsers = $query->latest('deleted_at')->paginate(20)->withQueryString();

        $stats = [
            'total' => User::onlyTrashed()->count(),
            'pending' => User::onlyTrashed()->where('status', 'inactive')->count(),
        ];

        return view('admin.account-deletions.index', compact('deletedUsers', 'stats'));
    }

    /**
     * Permanently delete a soft-deleted user (force delete).
     */
    public function forceDelete(int $id)
    {
        abort_unless(auth()->user()->can('users.delete'), 403);

        $user = User::onlyTrashed()->findOrFail($id);
        $user->forceDelete();

        return back()->with('success', __('admin.user_permanently_deleted'));
    }

    /**
     * Restore a soft-deleted user.
     */
    public function restore(int $id)
    {
        abort_unless(auth()->user()->can('users.edit'), 403);

        $user = User::onlyTrashed()->findOrFail($id);
        $user->update(['status' => 'active']);
        $user->restore();

        return back()->with('success', __('admin.user_restored'));
    }
}
