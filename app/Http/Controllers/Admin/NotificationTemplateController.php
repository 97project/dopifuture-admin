<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationTemplate;
use Illuminate\Http\Request;

class NotificationTemplateController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', NotificationTemplate::class);

        $templates = NotificationTemplate::latest()->paginate(20);
        return view('admin.notifications.templates.index', compact('templates'));
    }

    public function create()
    {
        $this->authorize('create', NotificationTemplate::class);

        return view('admin.notifications.templates.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', NotificationTemplate::class);

        $request->validate([
            'key' => 'required|string|max:100|unique:notification_templates,key|regex:/^[a-z0-9_]+$/',
            'title' => 'required|array',
            'title.tr' => 'required|string|max:200',
            'title.en' => 'required|string|max:200',
            'body' => 'required|array',
            'body.tr' => 'required|string|max:2000',
            'body.en' => 'required|string|max:2000',
            'channels' => 'required|array',
            'channels.*' => 'in:database,fcm,mail',
            'is_active' => 'nullable|boolean',
        ]);

        NotificationTemplate::create([
            'key' => $request->input('key'),
            'title' => $request->input('title'),
            'body' => $request->input('body'),
            'channels' => $request->input('channels'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.notification-templates.index')
            ->with('success', __('admin.template_created'));
    }

    public function edit(NotificationTemplate $notificationTemplate)
    {
        $this->authorize('update', $notificationTemplate);

        return view('admin.notifications.templates.edit', ['template' => $notificationTemplate]);
    }

    public function update(Request $request, NotificationTemplate $notificationTemplate)
    {
        $this->authorize('update', $notificationTemplate);

        $request->validate([
            'key' => 'required|string|max:100|regex:/^[a-z0-9_]+$/|unique:notification_templates,key,' . $notificationTemplate->id,
            'title' => 'required|array',
            'title.tr' => 'required|string|max:200',
            'title.en' => 'required|string|max:200',
            'body' => 'required|array',
            'body.tr' => 'required|string|max:2000',
            'body.en' => 'required|string|max:2000',
            'channels' => 'required|array',
            'channels.*' => 'in:database,fcm,mail',
            'is_active' => 'nullable|boolean',
        ]);

        $notificationTemplate->update([
            'key' => $request->input('key'),
            'title' => $request->input('title'),
            'body' => $request->input('body'),
            'channels' => $request->input('channels'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.notification-templates.index')
            ->with('success', __('admin.template_updated'));
    }

    public function destroy(NotificationTemplate $notificationTemplate)
    {
        $this->authorize('delete', $notificationTemplate);

        $notificationTemplate->delete();

        return redirect()->route('admin.notification-templates.index')
            ->with('success', __('admin.template_deleted'));
    }
}
