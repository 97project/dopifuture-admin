<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Connectors\MissionWayConnector;
use App\Connectors\WayStartupConnector;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    /* ═══════════════════════════════════════════════════════
     *  Görev Yönetimi — Mission Way + Way Startup
     * ═══════════════════════════════════════════════════════ */

    /**
     * Tüm assignment'ları listele (her iki connector'dan).
     */
    public function index()
    {
        $mwAssignments = [];
        $wsAssignments = [];

        // Mission Way assignments
        try {
            $mwConnector = new MissionWayConnector();
            $mwAssignments = $mwConnector->getAssignments() ?? [];
        } catch (\Throwable $e) {
            \Log::channel('daily')->warning('[Assignment] MissionWay assignments alınamadı', ['error' => $e->getMessage()]);
        }

        // Way Startup assignments
        try {
            $wsConnector = new WayStartupConnector();
            $wsAssignments = $wsConnector->getAssignments() ?? [];
        } catch (\Throwable $e) {
            \Log::channel('daily')->warning('[Assignment] WayStartup assignments alınamadı', ['error' => $e->getMessage()]);
        }

        return view('admin.assignments.index', compact('mwAssignments', 'wsAssignments'));
    }

    /**
     * Yeni assignment oluşturma formu.
     */
    public function create(Request $request)
    {
        $platform = $request->input('platform', 'way_startup');

        // Simülasyon listesini çek (platform'a göre)
        $simulations = [];
        $members = [];

        try {
            if ($platform === 'mission_way') {
                $connector = new MissionWayConnector();
                $simulations = $connector->getSimulations() ?? [];
                // Players as members
                $members = $connector->getPlayers() ?? [];
            } else {
                $connector = new WayStartupConnector();
                $simulations = $connector->getSimulations() ?? [];
                $members = $connector->getMembers() ?? [];
            }
        } catch (\Throwable $e) {
            \Log::channel('daily')->warning('[Assignment] Create form veri alınamadı', ['error' => $e->getMessage()]);
        }

        return view('admin.assignments.create', compact('platform', 'simulations', 'members'));
    }

    /**
     * Assignment oluştur (POST).
     */
    public function store(Request $request)
    {
        $request->validate([
            'platform'      => 'required|in:mission_way,way_startup',
            'simulationId'  => 'required|integer',
            'name'          => 'required|string|max:255',
            'memberIds'     => 'required|array|min:1',
            'memberIds.*'   => 'string',
            'description'   => 'nullable|string|max:1000',
            'dueDate'       => 'nullable|date',
        ]);

        $data = [
            'simulationId' => (int) $request->input('simulationId'),
            'name'         => $request->input('name'),
            'memberIds'    => $request->input('memberIds'),
        ];

        if ($request->filled('description')) {
            $data['description'] = $request->input('description');
        }
        if ($request->filled('dueDate')) {
            $data['dueDate'] = \Carbon\Carbon::parse($request->input('dueDate'))->toISOString();
        }

        try {
            if ($request->input('platform') === 'mission_way') {
                $connector = new MissionWayConnector();
            } else {
                $connector = new WayStartupConnector();
            }

            $response = $connector->createAssignment($data);

            if ($response->successful()) {
                return redirect()->route('admin.assignments.index')
                    ->with('success', 'Görev başarıyla oluşturuldu.');
            }

            $errorBody = $response->json('message', 'Bilinmeyen hata');
            return back()->withInput()
                ->with('error', "Görev oluşturulamadı: {$errorBody} (HTTP {$response->status()})");

        } catch (\Throwable $e) {
            return back()->withInput()
                ->with('error', 'Görev oluşturma hatası: ' . $e->getMessage());
        }
    }

    /**
     * Assignment'tan üye çıkar (DELETE).
     */
    public function removeMember(Request $request, int $assignmentId, int $memberId)
    {
        $platform = $request->input('platform', 'way_startup');

        try {
            if ($platform === 'mission_way') {
                $connector = new MissionWayConnector();
            } else {
                $connector = new WayStartupConnector();
            }

            $response = $connector->removeAssignmentMember($assignmentId, $memberId);

            if ($response->successful()) {
                return back()->with('success', 'Üye görevden çıkarıldı.');
            }

            return back()->with('error', "Üye çıkarılamadı (HTTP {$response->status()})");

        } catch (\Throwable $e) {
            return back()->with('error', 'İşlem hatası: ' . $e->getMessage());
        }
    }
}
