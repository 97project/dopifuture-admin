<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Application::class);

        $query = Application::withCount('users');

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%");
        }
        if ($request->filled('status')) {
            $query->where('is_active', $request->boolean('status'));
        }

        $applications = $query->ordered()->paginate(20)->withQueryString();

        $stats = [
            'total' => Application::count(),
            'active' => Application::active()->count(),
            'total_users' => \DB::table('application_user')->count(),
        ];

        return view('admin.applications.index', compact('applications', 'stats'));
    }

    public function show(Application $application)
    {
        $this->authorize('view', $application);
        $application->loadCount('users');
        $application->load('users');
        return view('admin.applications.show', compact('application'));
    }

    public function create()
    {
        $this->authorize('create', Application::class);
        return view('admin.applications.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Application::class);

        $request->validate([
            'slug' => 'required|string|max:100|unique:applications,slug|regex:/^[a-z0-9\-]+$/',
            'name_tr' => 'required|string|max:100',
            'name_en' => 'required|string|max:100',
            'description_tr' => 'nullable|string|max:500',
            'description_en' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
            'connector_class' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $application = Application::create([
            'slug' => $request->input('slug'),
            'name' => ['tr' => $request->input('name_tr'), 'en' => $request->input('name_en')],
            'description' => ['tr' => $request->input('description_tr', ''), 'en' => $request->input('description_en', '')],
            'icon' => $request->input('icon'),
            'color' => $request->input('color'),
            'connector_class' => $request->input('connector_class'),
            'sort_order' => $request->input('sort_order', 0),
            'is_active' => $request->boolean('is_active'),
        ]);

        ActivityLog::log('created', 'applications', $application);

        return redirect()->route('admin.applications.index')
            ->with('success', __('admin.application_created'));
    }

    public function edit(Application $application)
    {
        $this->authorize('update', $application);

        // Get raw JSON values for form
        $rawName = $application->getRawOriginal('name');
        $rawDesc = $application->getRawOriginal('description');
        $nameData = is_string($rawName) ? json_decode($rawName, true) : $rawName;
        $descData = is_string($rawDesc) ? json_decode($rawDesc, true) : $rawDesc;

        return view('admin.applications.edit', compact('application', 'nameData', 'descData'));
    }

    public function update(Request $request, Application $application)
    {
        $this->authorize('update', $application);

        $request->validate([
            'slug' => 'required|string|max:100|regex:/^[a-z0-9\-]+$/|unique:applications,slug,' . $application->id,
            'name_tr' => 'required|string|max:100',
            'name_en' => 'required|string|max:100',
            'description_tr' => 'nullable|string|max:500',
            'description_en' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
            'connector_class' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $application->update([
            'slug' => $request->input('slug'),
            'name' => ['tr' => $request->input('name_tr'), 'en' => $request->input('name_en')],
            'description' => ['tr' => $request->input('description_tr', ''), 'en' => $request->input('description_en', '')],
            'icon' => $request->input('icon'),
            'color' => $request->input('color'),
            'connector_class' => $request->input('connector_class'),
            'sort_order' => $request->input('sort_order', 0),
            'is_active' => $request->boolean('is_active'),
        ]);

        ActivityLog::log('updated', 'applications', $application);

        return redirect()->route('admin.applications.index')
            ->with('success', __('admin.application_updated'));
    }

    public function destroy(Application $application)
    {
        $this->authorize('delete', $application);

        ActivityLog::log('deleted', 'applications', $application);
        $application->delete();

        return redirect()->route('admin.applications.index')
            ->with('success', __('admin.application_deleted'));
    }
}
