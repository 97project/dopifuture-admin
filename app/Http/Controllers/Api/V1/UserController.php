<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use App\Models\User;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Services\AvatarService;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="Users", description="User management endpoints")
 */
class UserController extends Controller
{
    use ApiResponse;

    public function __construct(protected AvatarService $avatarService)
    {
    }

    /**
     * @OA\Get(
     *     path="/api/v1/users",
     *     tags={"Users"},
     *     summary="List users with pagination",
     *     security={{"bearerAuth":{}},{"apiKeyAuth":{}}},
     *     @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="status", in="query", @OA\Schema(type="string", enum={"active","inactive","banned"})),
     *     @OA\Parameter(name="role", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="sort", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="direction", in="query", @OA\Schema(type="string", enum={"asc","desc"})),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="lang", in="query", @OA\Schema(type="string", enum={"tr","en"})),
     *     @OA\Response(response=200, description="User list"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function index(Request $request)
    {
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

        $paginator = $query->paginate($request->input('per_page', 15));

        $data = collect($paginator->items())->map(fn($u) => $this->formatUser($u));

        return $this->success($data, [
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/users",
     *     tags={"Users"},
     *     summary="Create a new user",
     *     security={{"bearerAuth":{}},{"apiKeyAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"name","email","password","password_confirmation"},
     *         @OA\Property(property="name", type="string"),
     *         @OA\Property(property="surname", type="string"),
     *         @OA\Property(property="email", type="string"),
     *         @OA\Property(property="phone", type="string"),
     *         @OA\Property(property="password", type="string"),
     *         @OA\Property(property="password_confirmation", type="string"),
     *         @OA\Property(property="locale", type="string"),
     *         @OA\Property(property="status", type="string"),
     *         @OA\Property(property="roles", type="array", @OA\Items(type="integer"))
     *     )),
     *     @OA\Response(response=201, description="User created"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(UserStoreRequest $request)
    {
        $data = $request->validated();
        $user = User::create(collect($data)->except(['roles', 'avatar', 'password_confirmation'])->toArray());

        if ($request->filled('roles')) {
            $user->syncRoles($request->input('roles'));
        }

        return $this->created($this->formatUser($user->fresh('roles')));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/users/{id}",
     *     tags={"Users"},
     *     summary="Get user details",
     *     security={{"bearerAuth":{}},{"apiKeyAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="User details"),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
    public function show(User $user)
    {
        $user->load('roles', 'permissions');
        return $this->success($this->formatUser($user));
    }

    /**
     * @OA\Put(
     *     path="/api/v1/users/{id}",
     *     tags={"Users"},
     *     summary="Update a user",
     *     security={{"bearerAuth":{}},{"apiKeyAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         @OA\Property(property="name", type="string"),
     *         @OA\Property(property="email", type="string"),
     *         @OA\Property(property="status", type="string")
     *     )),
     *     @OA\Response(response=200, description="User updated"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(UserUpdateRequest $request, User $user)
    {
        $data = $request->validated();

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update(collect($data)->except(['roles', 'avatar', 'password_confirmation'])->toArray());

        if ($request->has('roles')) {
            $user->syncRoles($request->input('roles'));
        }

        return $this->success($this->formatUser($user->fresh('roles')));
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/users/{id}",
     *     tags={"Users"},
     *     summary="Delete a user",
     *     security={{"bearerAuth":{}},{"apiKeyAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="User deleted"),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
    public function destroy(User $user)
    {
        $user->delete();
        return $this->success(null, ['message' => __('api.user_deleted')]);
    }

    protected function formatUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'surname' => $user->surname,
            'full_name' => $user->full_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'locale' => $user->locale,
            'timezone' => $user->timezone,
            'status' => $user->status,
            'dark_mode' => $user->dark_mode,
            'avatar_url' => $user->avatar_url,
            'has_2fa' => $user->hasTwoFactorEnabled(),
            'roles' => $user->getRoleNames(),
            'email_verified_at' => $user->email_verified_at?->toIso8601String(),
            'last_login_at' => $user->last_login_at?->toIso8601String(),
            'created_at' => $user->created_at?->toIso8601String(),
        ];
    }
}
