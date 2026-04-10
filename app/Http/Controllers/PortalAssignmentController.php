<?php

namespace App\Http\Controllers;

use App\Connectors\MissionWayConnector;
use App\Connectors\WayStartupConnector;
use App\Models\MissionWay\MwAssignment;
use App\Models\MissionWay\MwAssignmentPlayer;
use App\Models\MissionWay\MwPlayer;
use App\Models\MissionWay\RefSimulation;
use App\Models\User;
use App\Models\WsAssignment;
use App\Models\WsAssignmentMember;
use App\Models\WsMember;
use App\Models\WsSimulation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            'deadline'      => 'nullable|date|after_or_equal:today',
        ]);

        try {
            // Step 1: Get selected students' emails
            $connector = app(MissionWayConnector::class);
            $selectedStudents = User::whereIn('id', $validated['user_ids'])->get(['id', 'email']);

            if ($selectedStudents->isEmpty()) {
                return redirect()->back()->withErrors(['user_ids' => 'Seçili öğrenciler bulunamadı.']);
            }

            // Step 2: Fetch ALL API players and build email → API userId map
            $emailToApiUserId = [];
            $page = 1;
            do {
                $resp = $connector->getPlayers(['page' => $page, 'limit' => 100]);
                $batch = $resp['data'] ?? $resp;
                if (!is_array($batch) || empty($batch)) break;
                foreach ($batch as $p) {
                    if (isset($p['email'], $p['userId'])) {
                        $emailToApiUserId[strtolower($p['email'])] = (int) $p['userId'];
                    }
                }
                $page++;
            } while (count($batch) >= 100);

            // Step 3: Resolve panel students → API userIds via email matching
            $backendUserIds = [];
            $unmapped = [];
            foreach ($selectedStudents as $student) {
                $key = strtolower($student->email);
                if (isset($emailToApiUserId[$key])) {
                    $backendUserIds[] = $emailToApiUserId[$key];
                } else {
                    $unmapped[] = $student->email;
                }
            }

            if (empty($backendUserIds)) {
                return redirect()->back()->withErrors(['user_ids' => 'Seçili öğrencilerin MW backend hesabı eşleştirilemedi.']);
            }
            if (!empty($unmapped)) {
                Log::warning('[PortalAssignment] MW unmapped students', ['emails' => $unmapped]);
            }

            // Step 4: Build API payload
            // API expects: simulationId (int), userIds (int[]), deadline? (ISO 8601)
            // NO name, NO memberIds, NO dueDate
            $data = [
                'simulationId' => (int) $validated['simulation_id'],
                'userIds'      => array_map('intval', $backendUserIds),
            ];
            if (!empty($validated['deadline'])) {
                $data['deadline'] = \Carbon\Carbon::parse($validated['deadline'])->toISOString();
            }

            Log::info('[PortalAssignment] MW creating assignment', $data);
            $response = $connector->createAssignment($data);

            if ($response->successful()) {
                // Sync: re-fetch ALL assignments from API → local DB (single source of truth)
                $this->syncMwAssignmentsFromApi($connector);

                return redirect()->back()->with('success', 'New Mission added successfully.');
            }

            $errorMsg = $response->json('message') ?? $response->body();
            if (is_array($errorMsg)) {
                $errorMsg = json_encode($errorMsg, JSON_UNESCAPED_UNICODE);
            }
            return redirect()->back()->withErrors(['api' => "Could not create mission: {$errorMsg} (HTTP {$response->status()})."]);

        } catch (\Throwable $e) {
            Log::error('[PortalAssignment] MW store error', [
                'error' => $e->getMessage(),
                'file'  => $e->getFile() . ':' . $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->withErrors(['api' => 'Error creating mission: ' . $e->getMessage()]);
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
            'name'          => 'nullable|string|max:255',
            'user_ids'      => 'required|array|min:1',
            'user_ids.*'    => 'exists:users,id',
            'description'   => 'nullable|string|max:1000',
            'due_date'      => 'nullable|date|after_or_equal:today',
        ]);

        // Auto-generate name from simulation if not provided (Figma design removed manual name input)
        if (empty($validated['name'])) {
            $sim = WsSimulation::find($validated['simulation_id']);
            $validated['name'] = ($sim->name ?? 'Assignment') . ' - ' . now()->format('d/m/Y');
        }

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
                // Sync: re-fetch ALL assignments from API → local DB (single source of truth)
                $this->syncWsAssignmentsFromApi($connector);

                return redirect()->back()->with('success', 'New Assignment added successfully.');
            }

            $errorMsg = $response->json('message') ?? $response->body();
            if (is_array($errorMsg)) {
                $errorMsg = json_encode($errorMsg, JSON_UNESCAPED_UNICODE);
            }
            return redirect()->back()->withErrors(['api' => "Could not create assignment: {$errorMsg} (HTTP {$response->status()})."]);

        } catch (\Throwable $e) {
            Log::error('[PortalAssignment] WS store error', [
                'error' => $e->getMessage(),
                'file'  => $e->getFile() . ':' . $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->withErrors(['api' => 'Error creating assignment: ' . $e->getMessage()]);
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

    /* ── API-driven sync helpers ─────────────────────── */

    /**
     * Fetch ALL MW assignments from the API and sync to local DB.
     * Used after creating/deleting assignments to ensure local DB reflects API truth.
     */
    private function syncMwAssignmentsFromApi(MissionWayConnector $connector): void
    {
        try {
            $items = $connector->getAssignments();
            if (!is_array($items)) return;

            foreach ($items as $item) {
                $id = $item['id'] ?? null;
                if (!$id) continue;

                $sessionId = $item['simulationSessionId'] ?? null;
                if ($sessionId && !\App\Models\MissionWay\MwSimulationSession::find($sessionId)) {
                    $sessionId = null;
                }

                $assignment = MwAssignment::updateOrCreate(
                    ['id' => $id],
                    [
                        'simulation_id'          => $item['simulationId'] ?? null,
                        'simulation_session_id'  => $sessionId,
                        'grade'                  => $item['grade'] ?? null,
                        'deadline'               => isset($item['deadline']) ? \Carbon\Carbon::parse($item['deadline']) : null,
                        'status'                 => $item['status'] ?? 'active',
                        'created_by'             => $item['createdBy'] ?? null,
                    ]
                );

                // Sync assignment players
                $players = $item['players'] ?? [];
                if (is_array($players)) {
                    foreach ($players as $ap) {
                        $playerId = $ap['playerId'] ?? $ap['id'] ?? null;
                        if (!$playerId) continue;
                        MwAssignmentPlayer::updateOrCreate(
                            ['assignment_id' => $assignment->id, 'player_id' => $playerId],
                            ['status' => $ap['status'] ?? 'assigned']
                        );
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('[PortalAssignment] MW sync from API failed', ['e' => $e->getMessage()]);
        }
    }

    /**
     * Fetch ALL WS assignments from the API and sync to local DB.
     */
    private function syncWsAssignmentsFromApi(WayStartupConnector $connector): void
    {
        try {
            $items = $connector->getAssignments();
            if (!is_array($items)) return;

            foreach ($items as $item) {
                $id = $item['id'] ?? null;
                if (!$id) continue;

                $assignment = WsAssignment::updateOrCreate(
                    ['id' => $id],
                    [
                        'simulation_id' => $item['simulationId'] ?? null,
                        'name'          => $item['name'] ?? 'Assignment',
                        'description'   => $item['description'] ?? null,
                        'due_date'      => isset($item['dueDate']) ? \Carbon\Carbon::parse($item['dueDate']) : null,
                        'status'        => $item['status'] ?? 'active',
                    ]
                );

                // Sync assignment members
                $members = $item['members'] ?? [];
                if (is_array($members)) {
                    foreach ($members as $am) {
                        $memberId = $am['memberId'] ?? $am['id'] ?? null;
                        if (!$memberId) continue;
                        WsAssignmentMember::updateOrCreate(
                            ['assignment_id' => $assignment->id, 'member_id' => $memberId],
                            ['status' => $am['status'] ?? 'assigned']
                        );
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('[PortalAssignment] WS sync from API failed', ['e' => $e->getMessage()]);
        }
    }
}
