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
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

/**
 * Handles assignment creation for Mission WAY and Way Startup.
 * Routes: portal.assignments.mw.store, portal.assignments.ws.store
 */
class PortalAssignmentController extends Controller
{
    /**
     * MW: Create a new assignment via NestJS API proxy.
     * POST /reports/mission-way/assignments
     */
    public function storeMwAssignment(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'simulation_id' => 'required|exists:ref_simulations,id',
            'user_ids'      => 'required|array|min:1',
            'user_ids.*'    => 'exists:users,id',
            'deadline'      => 'required|date|after:now',
        ]);

        try {
            // Resolve portal user_ids → MW player_ids
            $playerIds = MwPlayer::whereIn('user_id', $validated['user_ids'])
                ->pluck('id')
                ->toArray();

            if (empty($playerIds)) {
                return redirect()->back()->withErrors(['user_ids' => 'Seçili öğrencilerin Mission WAY hesabı bulunamadı.']);
            }

            $connector = app(MissionWayConnector::class);
            $simulation = RefSimulation::findOrFail($validated['simulation_id']);

            $response = $connector->apiPost('/v1/assignments', [
                'simulationId' => $simulation->id,
                'playerIds'    => $playerIds,
                'deadline'     => $validated['deadline'],
            ]);

            if ($response === null) {
                return redirect()->back()->withErrors(['api' => 'NestJS API bağlantısı kurulamadı.']);
            }

            // Trigger incremental harvest for immediate UI update
            Artisan::queue('harvest:app-data', ['--app' => 'mission-way']);

            return redirect()->back()->with('success', 'Görev başarıyla atandı.');
        } catch (\Throwable $e) {
            Log::error('[PortalAssignment] MW store error', ['error' => $e->getMessage()]);
            return redirect()->back()->withErrors(['api' => 'Görev atanırken hata oluştu: ' . $e->getMessage()]);
        }
    }

    /**
     * WS: Create a new assignment via NestJS API proxy.
     * POST /reports/way-startup/assignments
     */
    public function storeWsAssignment(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'simulation_id' => 'required|exists:ws_simulations,id',
            'user_ids'      => 'required|array|min:1',
            'user_ids.*'    => 'exists:users,id',
            'due_date'      => 'required|date|after:now',
        ]);

        try {
            $wsSim = WsSimulation::findOrFail($validated['simulation_id']);

            // Resolve portal user_ids → WS member_ids
            $memberIds = WsMember::whereIn('user_id', $validated['user_ids'])
                ->pluck('external_id')
                ->toArray();

            if (empty($memberIds)) {
                return redirect()->back()->withErrors(['user_ids' => 'Seçili öğrencilerin Way Startup hesabı bulunamadı.']);
            }

            $connector = app(WayStartupConnector::class);

            $response = $connector->apiPost('/v1/startup/assignments', [
                'simulationId' => $wsSim->external_id,
                'memberIds'    => $memberIds,
                'dueDate'      => $validated['due_date'],
            ]);

            if ($response === null) {
                return redirect()->back()->withErrors(['api' => 'NestJS API bağlantısı kurulamadı.']);
            }

            // Trigger incremental harvest
            Artisan::queue('harvest:app-data', ['--app' => 'way-startup']);

            return redirect()->back()->with('success', 'Proje başarıyla atandı.');
        } catch (\Throwable $e) {
            Log::error('[PortalAssignment] WS store error', ['error' => $e->getMessage()]);
            return redirect()->back()->withErrors(['api' => 'Proje atanırken hata oluştu: ' . $e->getMessage()]);
        }
    }
}
