<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use App\Models\ActivityLog;
use App\Models\Language;
use App\Models\Setting;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

/**
 * @OA\Tag(name="General", description="General endpoints (roles, languages, settings, activity)")
 */
class GeneralController extends Controller
{
    use ApiResponse;

    /** @OA\Get(path="/api/v1/roles", tags={"General"}, summary="List roles", security={{"bearerAuth":{}},{"apiKeyAuth":{}}}, @OA\Response(response=200, description="Role list")) */
    public function roles()
    {
        $roles = Role::orderBy('name')->get()->map(fn($r) => [
            'id' => $r->id,
            'name' => $r->name,
            'permissions' => $r->permissions->pluck('name'),
        ]);
        return $this->success($roles);
    }

    /** @OA\Get(path="/api/v1/languages", tags={"General"}, summary="List active languages", @OA\Response(response=200, description="Language list")) */
    public function languages()
    {
        $languages = Language::active()->ordered()->get()->map(fn($l) => [
            'code' => $l->code,
            'name' => $l->name,
            'native_name' => $l->native_name,
            'direction' => $l->direction,
            'is_default' => $l->is_default,
        ]);
        return $this->success($languages);
    }

    /** @OA\Get(path="/api/v1/settings/public", tags={"General"}, summary="Get public settings", @OA\Response(response=200, description="Public settings")) */
    public function publicSettings()
    {
        $settings = Setting::whereIn('group', ['general', 'appearance'])
            ->get()
            ->mapWithKeys(fn($s) => [$s->group . '.' . $s->key => $s->typed_value]);
        return $this->success($settings);
    }

    /** @OA\Get(path="/api/v1/activity-logs", tags={"General"}, summary="List activity logs", security={{"bearerAuth":{}},{"apiKeyAuth":{}}}, @OA\Parameter(name="module", in="query", @OA\Schema(type="string")), @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")), @OA\Response(response=200, description="Log list")) */
    public function activityLogs(Request $request)
    {
        $query = ActivityLog::with('actor')->latest('created_at');

        if ($request->filled('module')) {
            $query->forModule($request->input('module'));
        }
        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }

        $paginator = $query->paginate($request->input('per_page', 20));

        $data = collect($paginator->items())->map(fn($l) => [
            'id' => $l->id,
            'action' => $l->action,
            'module' => $l->module,
            'actor' => $l->actor ? ['id' => $l->actor->id, 'name' => $l->actor->name ?? ''] : null,
            'ip_address' => $l->ip_address,
            'properties' => $l->properties,
            'created_at' => $l->created_at?->toIso8601String(),
        ]);

        return $this->paginated($paginator);
    }
}
