<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', ActivityLog::class);

        $query = ActivityLog::with('actor', 'subject')->latest('created_at');

        if ($request->filled('search')) {
            $query->search($request->input('search'));
        }
        if ($request->filled('module')) {
            $query->forModule($request->input('module'));
        }
        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->input('date_to') . ' 23:59:59');
        }

        $logs = $query->paginate(20)->withQueryString();
        $modules = ActivityLog::distinct()->pluck('module')->filter();
        $actions = ActivityLog::distinct()->pluck('action');

        $stats = [
            'today' => ActivityLog::where('created_at', '>=', now()->startOfDay())->count(),
            'week' => ActivityLog::where('created_at', '>=', now()->subWeek())->count(),
            'month' => ActivityLog::where('created_at', '>=', now()->subMonth())->count(),
            'total' => ActivityLog::count(),
        ];

        return view('admin.activity-logs.index', compact('logs', 'modules', 'actions', 'stats'));
    }

    public function export(Request $request)
    {
        $this->authorize('export', ActivityLog::class);

        $query = ActivityLog::with('actor')->latest('created_at');

        if ($request->filled('module')) {
            $query->forModule($request->input('module'));
        }
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->input('date_to') . ' 23:59:59');
        }

        $logs = $query->limit(5000)->get();

        $csv = "ID,Action,Module,Actor,IP,Date\n";
        foreach ($logs as $log) {
            $actorName = $log->actor?->name ?? 'System';
            $csv .= "\"{$log->id}\",\"{$log->action}\",\"{$log->module}\",\"{$actorName}\",\"{$log->ip_address}\",\"{$log->created_at}\"\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=activity_logs_' . date('Y-m-d') . '.csv',
        ]);
    }
}
