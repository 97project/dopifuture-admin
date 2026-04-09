<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Models\User;
use App\Models\ActivityLog;
use App\Models\ApiKey;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Application;
use App\Services\AvatarService;
use App\Services\ApiKeyService;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct(
        protected AvatarService $avatarService,
        protected ApiKeyService $apiKeyService
    ) {
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $query = User::with('roles');

        if ($request->filled('search')) {
            $query->search($request->input('search'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('role')) {
            $query->role($request->input('role'));
        }

        $sortField = $request->input('sort', 'created_at');
        $sortDir = $request->input('direction', 'desc');
        $allowedSorts = ['name', 'email', 'status', 'created_at', 'last_login_at'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDir === 'asc' ? 'asc' : 'desc');
        }

        $users = $query->paginate($request->input('per_page', 15))->withQueryString();
        $roles = Role::orderBy('name')->get();

        return view('admin.users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $this->authorize('create', User::class);

        $roles = Role::orderBy('name')->get();
        return view('admin.users.create', compact('roles'));
    }

    public function store(UserStoreRequest $request)
    {
        $this->authorize('create', User::class);

        $data = $request->validated();
        $plainPassword = $data['password'];

        // Check if a soft-deleted user with same email exists → restore
        $trashedUser = User::withTrashed()->where('email', $data['email'])->whereNotNull('deleted_at')->first();
        $createData = collect($data)->except(['roles', 'avatar', 'password_confirmation', 'send_credentials'])->toArray();

        if ($trashedUser) {
            $trashedUser->restore();
            $trashedUser->update($createData);
            $user = $trashedUser;
        } else {
            $user = User::create($createData);
        }

        if ($request->filled('roles')) {
            $roleNames = \Spatie\Permission\Models\Role::whereIn('id', (array) $request->input('roles'))->pluck('name')->toArray();
            $user->syncRoles($roleNames);
        }

        if ($request->hasFile('avatar')) {
            $this->avatarService->upload($user, $request->file('avatar'));
        }

        // Hoş geldin e-postası gönder
        if ($request->boolean('send_credentials', true)) {
            try {
                \Illuminate\Support\Facades\Mail::to($user->email)
                    ->send(new \App\Mail\WelcomeCredentials($user, $plainPassword));
            } catch (\Throwable $e) {
                \Log::channel('daily')->error('[UserController] Hoş geldin maili gönderilemedi', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return redirect()->route('admin.users.index')
            ->with('success', __('admin.user_created'));
    }

    public function show(Request $request, User $user)
    {
        $this->authorize('view', $user);

        $tab = $request->input('tab', 'profile');
        $user->load('roles', 'permissions');

        $data = [
            'user' => $user,
            'tab' => $tab,
            'stats' => [
                'sessions' => $user->tokens()->count(),
                'devices' => $user->devices()->count(),
                'apiKeys' => $user->apiKeys()->count(),
                'logs' => ActivityLog::forActor(User::class, $user->id)->count(),
            ],
        ];

        switch ($tab) {
            case 'profile':
                $user->load('schools', 'classes');
                break;
            case 'security':
                $data['tokens'] = $user->tokens()->latest()->paginate(10, ['*'], 'tokens_page');
                break;
            case 'roles':
                $data['allRoles'] = Role::orderBy('name')->get();
                break;
            case 'api_keys':
                $data['apiKeys'] = $user->apiKeys()->latest()->paginate(10, ['*'], 'keys_page');
                break;
            case 'devices':
                $data['devices'] = $user->devices()->latest()->paginate(10, ['*'], 'devices_page');
                break;
            case 'schools':
                $data['schools'] = $user->schools()->with('classes')->get();
                break;
            case 'classes':
                $data['classes'] = $user->classes()->with(['school', 'students', 'teachers'])->get();
                break;
            case 'audit':
                $actorLogs = ActivityLog::forActor(User::class, $user->id)
                    ->latest('created_at');
                $subjectLogs = ActivityLog::forSubject(User::class, $user->id)
                    ->latest('created_at');

                $logType = $request->input('log_type', 'actor');
                $data['activityLogs'] = ($logType === 'subject' ? $subjectLogs : $actorLogs)
                    ->paginate(15, ['*'], 'logs_page');
                $data['logType'] = $logType;
                break;
            default:
                // app_ prefix'li tab'lar → uygulama-özel veri
                if (str_starts_with($tab, 'app_')) {
                    $slug = str_replace('app_', '', $tab);
                    $app = $user->applications()->where('slug', $slug)->first();
                    $data['currentApp'] = $app;
                    $data['appSlug'] = $slug;
                    $data['remoteUser'] = null;
                    $data['sessions'] = [];
                    $data['connectorReady'] = false;

                    if ($app) {
                        $connector = $app->resolveConnector();
                        $data['connectorReady'] = $connector && $connector::isReady();

                        if ($data['connectorReady']) {
                            try {
                                $data['remoteUser'] = $connector->getUser($user);

                                // Vega uygulamaları için oturum verileri
                                if ($connector instanceof \App\Connectors\VegaConnector && $data['remoteUser']) {
                                    $vegaId = $data['remoteUser']['id'] ?? null;
                                    if ($vegaId) {
                                        $sessionType = match ($slug) {
                                            'way-ai-coach' => 'lecturer',
                                            'role-galaxy' => 'simulator',
                                            default => 'all',
                                        };
                                        $result = $connector->getUserSessions($vegaId, $sessionType);
                                        $data['sessions'] = $result['sessions'] ?? [];
                                    }
                                }
                            } catch (\Throwable $e) {
                                \Log::channel('daily')->error('[UserController] App tab veri hatası', [
                                    'userId' => $user->id,
                                    'slug' => $slug,
                                    'error' => $e->getMessage(),
                                ]);
                            }
                        }
                    }
                }
                break;
        }

        return view('admin.users.show', $data);
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);

        $roles = Role::orderBy('name')->get();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(UserUpdateRequest $request, User $user)
    {
        $this->authorize('update', $user);

        $data = $request->validated();

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update(collect($data)->except(['roles', 'avatar', 'password_confirmation'])->toArray());

        if ($request->has('roles')) {
            $roleNames = \Spatie\Permission\Models\Role::whereIn('id', (array) $request->input('roles'))->pluck('name')->toArray();
            $user->syncRoles($roleNames);
        }

        if ($request->hasFile('avatar')) {
            $this->avatarService->upload($user, $request->file('avatar'));
        }

        return redirect()->route('admin.users.show', $user)
            ->with('success', __('admin.user_updated'));
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        if ($user->id === auth()->id()) {
            return back()->with('error', __('admin.cannot_delete_self'));
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', __('admin.user_deleted'));
    }

    public function bulkAction(Request $request)
    {
        $this->authorize('bulkAction', User::class);

        $request->validate([
            'action' => 'required|in:delete,activate,deactivate',
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:users,id',
        ]);

        $ids = array_diff($request->input('ids'), [auth()->id()]);

        switch ($request->input('action')) {
            case 'delete':
                User::whereIn('id', $ids)->delete();
                break;
            case 'activate':
                User::whereIn('id', $ids)->update(['status' => 'active']);
                break;
            case 'deactivate':
                User::whereIn('id', $ids)->update(['status' => 'inactive']);
                break;
        }

        ActivityLog::log('bulk_action', 'users', null, [
            'action' => $request->input('action'),
            'count' => count($ids),
        ]);

        return back()->with('success', __('admin.bulk_action_completed'));
    }
    /* ─── API Key Management ──────────────────── */

    public function storeApiKey(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $request->validate([
            'name' => 'required|string|max:255',
            'abilities' => 'nullable|string',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $abilities = $request->input('abilities')
            ? array_map('trim', explode(',', $request->input('abilities')))
            : ['*'];

        $result = $this->apiKeyService->generate(
            $user->id,
            $request->input('name'),
            $abilities,
            ['expires_at' => $request->input('expires_at')]
        );

        return redirect()->route('admin.users.show', ['user' => $user, 'tab' => 'api_keys'])
            ->with('success', __('admin.api_key_created') . ' Key: ' . $result['plain_key']);
    }

    public function toggleApiKey(Request $request, User $user, ApiKey $apiKey)
    {
        $this->authorize('update', $user);

        if ($apiKey->user_id !== $user->id) {
            abort(403);
        }

        $apiKey->update(['is_active' => !$apiKey->is_active]);

        return redirect()->route('admin.users.show', ['user' => $user, 'tab' => 'api_keys'])
            ->with('success', $apiKey->is_active ? __('admin.activated') : __('admin.deactivated'));
    }

    public function destroyApiKey(Request $request, User $user, ApiKey $apiKey)
    {
        $this->authorize('update', $user);

        if ($apiKey->user_id !== $user->id) {
            abort(403);
        }

        $this->apiKeyService->delete($apiKey);

        return redirect()->route('admin.users.show', ['user' => $user, 'tab' => 'api_keys'])
            ->with('success', __('admin.api_key_deleted'));
    }
}
