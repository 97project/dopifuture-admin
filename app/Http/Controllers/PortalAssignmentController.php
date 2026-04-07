<?php

namespace App\Http\Controllers;

use App\Connectors\MissionWayConnector;
use App\Connectors\WayStartupConnector;
use App\Models\MissionWay\MwPlayer;
use App\Models\MissionWay\RefSimulation;
use App\Models\WsMember;
use App\Models\WsSimulation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Handles assignment creation for Mission WAY and Way Startup.
 * Accessible by: admin, teacher, school_admin roles.
 * Routes: portal.assignments.mw.store, portal.assignments.ws.store
 */
class PortalAssignmentController extends Controller
{
    /**
     * MW: Create a new assignment via backend API.
     * POST /reports/mission-way/assignments
     *
     * API: POST /v1/assignments (way-backend)
     * Body: { simulationId, name, memberIds[], description?, dueDate? }
     */
    public function storeMwAssignment(Request $request): RedirectResponse
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['admin', 'super-admin', 'teacher', 'school-admin', 'school-principal'])) {
            abort(403, 'Bu işlem için yetkiniz yok.');
        }

        $validated = $request->validate([
            'simulation_id' => 'required|integer',
            'user_ids'      => 'required|array|min:1',
            'user_ids.*'    => 'exists:users,id',
            'deadline'      => 'nullable|date|after:now',
        ]);

        try {
            // Step 1: Resolve panel user_ids → local MW player IDs
            $localPlayerIds = MwPlayer::whereIn('user_id', $validated['user_ids'])
                ->pluck('id')
                ->toArray();

            if (empty($localPlayerIds)) {
                return redirect()->back()->withErrors(['user_ids' => 'Seçili öğrencilerin Mission WAY hesabı bulunamadı.']);
            }

            // Step 2: Fetch ALL API players (paginated) and build playerId → userId map
            $connector = app(MissionWayConnector::class);
            $playerIdToUserId = [];
            $page = 1;
            do {
                $resp = $connector->getPlayers(['page' => $page, 'limit' => 100]);
                $batch = $resp['data'] ?? $resp;
                if (!is_array($batch) || empty($batch)) break;
                foreach ($batch as $p) {
                    if (isset($p['id'], $p['userId'])) {
                        $playerIdToUserId[(int) $p['id']] = (int) $p['userId'];
                    }
                }
                $page++;
            } while (count($batch) >= 100);

            // Step 3: Map local player IDs → backend userIds
            $backendUserIds = [];
            foreach ($localPlayerIds as $pid) {
                if (isset($playerIdToUserId[$pid])) {
                    $backendUserIds[] = $playerIdToUserId[$pid];
                }
            }

            if (empty($backendUserIds)) {
                return redirect()->back()->withErrors(['user_ids' => 'Seçili öğrencilerin backend hesabı eşleştirilemedi.']);
            }

            // Step 4: Build API payload (simulationId, userIds[], deadline?)
            $data = [
                'simulationId' => (int) $validated['simulation_id'],
                'userIds'      => $backendUserIds,
            ];
            if (!empty($validated['deadline'])) {
                $data['deadline'] = \Carbon\Carbon::parse($validated['deadline'])->toISOString();
            }

            Log::info('[PortalAssignment] MW creating assignment', $data);
            $response = $connector->createAssignment($data);

            if ($response->successful()) {
                return redirect()->back()->with('success', 'Mission WAY görevi başarıyla atandı.');
            }

            $errorMsg = $response->json('message') ?? $response->body();
            if (is_array($errorMsg)) {
                $errorMsg = json_encode($errorMsg, JSON_UNESCAPED_UNICODE);
            }
            return redirect()->back()->withErrors(['api' => "Görev oluşturulamadı: {$errorMsg} (HTTP {$response->status()})"]);

        } catch (\Throwable $e) {
            Log::error('[PortalAssignment] MW store error', [
                'error' => $e->getMessage(),
                'file'  => $e->getFile() . ':' . $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->withErrors(['api' => 'Görev atanırken hata oluştu: ' . $e->getMessage()]);
        }
    }

    /**
     * WS: Create a new assignment via backend API.
     * POST /reports/way-startup/assignments
     *
     * API: POST /startup/assignments (way-backend)
     * Body: { simulationId, name, memberIds[], description?, dueDate? }
     */
    public function storeWsAssignment(Request $request): RedirectResponse
    {
        // Yetki kontrolü: admin, teacher veya school_admin
        $user = auth()->user();
        if (!$user->hasAnyRole(['admin', 'super-admin', 'teacher', 'school-admin', 'school-principal'])) {
            abort(403, 'Bu işlem için yetkiniz yok.');
        }

        $validated = $request->validate([
            'simulation_id' => 'required|integer',
            'name'          => 'required|string|max:255',
            'user_ids'      => 'required|array|min:1',
            'user_ids.*'    => 'exists:users,id',
            'description'   => 'nullable|string|max:1000',
            'due_date'      => 'nullable|date|after:now',
        ]);

        try {
            // Resolve portal user_ids → WS member_ids
            $memberIds = WsMember::whereIn('user_id', $validated['user_ids'])
                ->pluck('external_id')
                ->map(fn($id) => (int) $id)
                ->toArray();

            if (empty($memberIds)) {
                return redirect()->back()->withErrors(['user_ids' => 'Seçili öğrencilerin Way Startup hesabı bulunamadı.']);
            }

            $connector = app(WayStartupConnector::class);

            $data = [
                'simulationId' => (int) $validated['simulation_id'],
                'name'         => $validated['name'],
                'memberIds'    => $memberIds,
            ];

            if (!empty($validated['description'])) {
                $data['description'] = $validated['description'];
            }
            if (!empty($validated['due_date'])) {
                $data['dueDate'] = \Carbon\Carbon::parse($validated['due_date'])->toISOString();
            }

            Log::info('[PortalAssignment] WS creating assignment', $data);
            $response = $connector->createAssignment($data);

            if ($response->successful()) {
                return redirect()->back()->with('success', 'Way Startup projesi başarıyla atandı.');
            }

            $errorMsg = $response->json('message') ?? $response->body();
            if (is_array($errorMsg)) {
                $errorMsg = json_encode($errorMsg, JSON_UNESCAPED_UNICODE);
            }
            return redirect()->back()->withErrors(['api' => "Proje oluşturulamadı: {$errorMsg} (HTTP {$response->status()})"]);

        } catch (\Throwable $e) {
            Log::error('[PortalAssignment] WS store error', [
                'error' => $e->getMessage(),
                'file'  => $e->getFile() . ':' . $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->withErrors(['api' => 'Proje atanırken hata oluştu: ' . $e->getMessage()]);
        }
    }

    /**
     * MW: Remove a member from an assignment.
     * DELETE /reports/mission-way/assignments/{id}/members/{memberId}
     */
    public function removeMwMember(Request $request, int $assignmentId, int $memberId): RedirectResponse
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['admin', 'super-admin', 'teacher', 'school-admin', 'school-principal'])) {
            abort(403, 'Bu işlem için yetkiniz yok.');
        }

        try {
            $connector = app(MissionWayConnector::class);
            $response = $connector->removeAssignmentMember($assignmentId, $memberId);

            if ($response->successful()) {
                return redirect()->back()->with('success', 'Üye görevden çıkarıldı.');
            }
            return redirect()->back()->with('error', "Üye çıkarılamadı (HTTP {$response->status()})");
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'İşlem hatası: ' . $e->getMessage());
        }
    }

    /**
     * WS: Remove a member from an assignment.
     * DELETE /reports/way-startup/assignments/{id}/members/{memberId}
     */
    public function removeWsMember(Request $request, int $assignmentId, int $memberId): RedirectResponse
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['admin', 'super-admin', 'teacher', 'school-admin', 'school-principal'])) {
            abort(403, 'Bu işlem için yetkiniz yok.');
        }

        try {
            $connector = app(WayStartupConnector::class);
            $response = $connector->removeAssignmentMember($assignmentId, $memberId);

            if ($response->successful()) {
                return redirect()->back()->with('success', 'Üye projeden çıkarıldı.');
            }
            return redirect()->back()->with('error', "Üye çıkarılamadı (HTTP {$response->status()})");
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'İşlem hatası: ' . $e->getMessage());
        }
    }
}
