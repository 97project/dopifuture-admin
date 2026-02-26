<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\ActivityLog;
use App\Http\Requests\SchoolStoreRequest;
use App\Http\Requests\SchoolUpdateRequest;
use Illuminate\Http\Request;

class SchoolController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', School::class);

        $query = School::withCount(['users', 'classes', 'licenses', 'students', 'teachers']);

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // City filter
        if ($city = $request->input('city')) {
            $query->where('city', $city);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('is_active', $request->boolean('status'));
        }

        // Sorting
        $sort = $request->input('sort', 'created_at');
        $dir = $request->input('dir', 'desc');
        $allowedSorts = ['name', 'city', 'users_count', 'classes_count', 'created_at'];
        if (in_array($sort, $allowedSorts)) {
            $query->orderBy($sort, $dir === 'asc' ? 'asc' : 'desc');
        }

        $schools = $query->paginate(20)->withQueryString();
        $cities = School::whereNotNull('city')->distinct()->pluck('city')->sort();

        // Stats
        $stats = [
            'total' => School::count(),
            'active' => School::active()->count(),
            'inactive' => School::where('is_active', false)->count(),
            'total_students' => \DB::table('school_user')->where('role', 'student')->count(),
        ];

        return view('admin.schools.index', compact('schools', 'cities', 'stats'));
    }

    public function create()
    {
        $this->authorize('create', School::class);
        return view('admin.schools.create');
    }

    public function store(SchoolStoreRequest $request)
    {
        $school = School::create([
            'name' => ['tr' => $request->input('name_tr'), 'en' => $request->input('name_en')],
            'country' => $request->input('country'),
            'city' => $request->input('city'),
            'address' => $request->input('address'),
            'phone' => $request->input('phone'),
            'email' => $request->input('email'),
            'website' => $request->input('website'),
            'is_active' => $request->boolean('is_active'),
        ]);

        ActivityLog::log('created', 'schools', $school);

        return redirect()->route('admin.schools.index')
            ->with('success', __('admin.school_created'));
    }

    public function show(School $school)
    {
        $this->authorize('view', $school);

        $school->load(['classes.teachers', 'classes.students', 'admins', 'principals', 'licenses']);

        // Uygulama sync istatistikleri — bu okuldaki öğrencilerin uygulama durumu
        $studentIds = $school->students->pluck('id')->toArray();
        $appSyncStats = [];
        if (count($studentIds) > 0) {
            $appSyncStats = \App\Models\Application::active()
                ->withCount([
                    'users as school_total' => fn($q) => $q->whereIn('users.id', $studentIds),
                    'users as school_synced' => fn($q) => $q->whereIn('users.id', $studentIds)->where('application_user.sync_status', 'synced'),
                    'users as school_failed' => fn($q) => $q->whereIn('users.id', $studentIds)->where('application_user.sync_status', 'failed'),
                ])
                ->having('school_total', '>', 0)
                ->get();
        }

        return view('admin.schools.show', compact('school', 'appSyncStats'));
    }

    public function edit(School $school)
    {
        $this->authorize('update', $school);

        $rawName = $school->getRawOriginal('name');
        $nameData = is_string($rawName) ? json_decode($rawName, true) : $rawName;

        return view('admin.schools.edit', compact('school', 'nameData'));
    }

    public function update(SchoolUpdateRequest $request, School $school)
    {

        $school->update([
            'name' => ['tr' => $request->input('name_tr'), 'en' => $request->input('name_en')],
            'country' => $request->input('country'),
            'city' => $request->input('city'),
            'address' => $request->input('address'),
            'phone' => $request->input('phone'),
            'email' => $request->input('email'),
            'website' => $request->input('website'),
            'is_active' => $request->boolean('is_active'),
        ]);

        ActivityLog::log('updated', 'schools', $school);

        return redirect()->route('admin.schools.index')
            ->with('success', __('admin.school_updated'));
    }

    public function destroy(School $school)
    {
        $this->authorize('delete', $school);
        ActivityLog::log('deleted', 'schools', $school);
        $school->delete();

        return redirect()->route('admin.schools.index')
            ->with('success', __('admin.school_deleted'));
    }
}
