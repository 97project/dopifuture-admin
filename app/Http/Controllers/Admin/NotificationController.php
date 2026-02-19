<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationLog;
use App\Models\NotificationTemplate;
use App\Models\School;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class NotificationController extends Controller
{
    public function __construct(protected NotificationService $notificationService)
    {
    }

    /**
     * Compose tab — send notification form.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', NotificationTemplate::class);

        $templates = NotificationTemplate::active()->latest()->get();
        $users = User::where('status', 'active')->orderBy('name')->get(['id', 'name', 'surname', 'email']);
        $roles = Role::orderBy('name')->get();
        $schools = School::orderBy('name')->get(['id', 'name']);

        return view('admin.notifications.index', compact('templates', 'users', 'roles', 'schools'));
    }

    /**
     * History tab — sent notification logs.
     */
    public function history(Request $request)
    {
        $this->authorize('viewAny', NotificationTemplate::class);

        $query = NotificationLog::with(['sender', 'template'])->latest();

        if ($request->filled('channel')) {
            $query->whereJsonContains('channels', $request->input('channel'));
        }
        if ($request->filled('target_type')) {
            $query->where('target_type', $request->input('target_type'));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $logs = $query->paginate(20)->withQueryString();

        return view('admin.notifications.history', compact('logs'));
    }

    /**
     * Analytics tab — aggregate stats.
     */
    public function analytics(Request $request)
    {
        $this->authorize('viewAny', NotificationTemplate::class);

        $stats = [
            'total_sent' => NotificationLog::count(),
            'total_recipients' => NotificationLog::sum('recipients_count'),
            'total_read' => NotificationLog::sum('read_count'),
            'by_channel' => [
                'database' => NotificationLog::whereJsonContains('channels', 'database')->count(),
                'fcm' => NotificationLog::whereJsonContains('channels', 'fcm')->count(),
                'mail' => NotificationLog::whereJsonContains('channels', 'mail')->count(),
            ],
            'by_target' => [
                'all' => NotificationLog::where('target_type', 'all')->count(),
                'role' => NotificationLog::where('target_type', 'role')->count(),
                'school' => NotificationLog::where('target_type', 'school')->count(),
                'selected' => NotificationLog::where('target_type', 'selected')->count(),
            ],
            'last_7_days' => NotificationLog::where('created_at', '>=', now()->subDays(7))
                ->selectRaw('DATE(created_at) as date, SUM(recipients_count) as total')
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('total', 'date'),
        ];

        $readRate = $stats['total_recipients'] > 0
            ? round(($stats['total_read'] / $stats['total_recipients']) * 100, 1)
            : 0;

        return view('admin.notifications.analytics', compact('stats', 'readRate'));
    }

    /**
     * Send notification — supports all/role/school/selected targeting.
     */
    public function send(Request $request)
    {
        $this->authorize('send', NotificationTemplate::class);

        $request->validate([
            'mode' => 'required|in:template,custom',
            'template_key' => 'required_if:mode,template|nullable|string|exists:notification_templates,key',
            'custom_title' => 'required_if:mode,custom|nullable|string|max:200',
            'custom_body' => 'required_if:mode,custom|nullable|string|max:2000',
            'channels' => 'required|array|min:1',
            'channels.*' => 'in:database,fcm,mail',
            'target' => 'required|in:all,role,school,selected',
            'role_names' => 'required_if:target,role|nullable|array',
            'school_ids' => 'required_if:target,school|nullable|array',
            'user_ids' => 'required_if:target,selected|nullable|array',
            'user_ids.*' => 'integer|exists:users,id',
        ]);

        $target = $request->input('target');
        $channels = $request->input('channels', ['database']);
        $mode = $request->input('mode');

        // Determine title & body
        if ($mode === 'template') {
            $template = NotificationTemplate::where('key', $request->input('template_key'))->first();
            $title = $template->getTranslation('title');
            $body = $template->getTranslation('body');
            $templateId = $template->id;
        } else {
            $title = $request->input('custom_title');
            $body = $request->input('custom_body');
            $templateId = null;
        }

        // Build user query based on target
        $targetData = [];
        $query = User::where('status', 'active');

        switch ($target) {
            case 'role':
                $roleNames = $request->input('role_names', []);
                $query->whereHas('roles', fn($q) => $q->whereIn('name', $roleNames));
                $targetData = $roleNames;
                break;
            case 'school':
                $schoolIds = $request->input('school_ids', []);
                $query->whereIn('school_id', $schoolIds);
                $targetData = School::whereIn('id', $schoolIds)->pluck('name')->toArray();
                break;
            case 'selected':
                $userIds = $request->input('user_ids', []);
                $query->whereIn('id', $userIds);
                $targetData = $userIds;
                break;
            // 'all' — no additional filtering
        }

        $count = 0;
        $pushFcm = in_array('fcm', $channels);
        $query->chunk(100, function ($users) use ($title, $body, $pushFcm, &$count) {
            foreach ($users as $user) {
                $this->notificationService->sendCustom($user, $title, $body, [], $pushFcm);
                $count++;
            }
        });

        // Log notification
        NotificationLog::create([
            'title' => $title,
            'body' => $body,
            'channels' => $channels,
            'target_type' => $target,
            'target_data' => $targetData,
            'recipients_count' => $count,
            'template_id' => $templateId ?? null,
            'sent_by' => auth()->id(),
        ]);

        return back()->with('success', __('admin.notifications_sent', ['count' => $count]));
    }
}
