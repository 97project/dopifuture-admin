<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\School;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', SchoolClass::class);

        $query = SchoolClass::with('school')
            ->withCount(['teachers', 'students']);

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }
        if ($schoolId = $request->input('school_id')) {
            $query->where('school_id', $schoolId);
        }
        if ($grade = $request->input('grade_level')) {
            $query->where('grade_level', $grade);
        }
        if ($year = $request->input('academic_year')) {
            $query->where('academic_year', $year);
        }

        $sort = $request->input('sort', 'created_at');
        $dir = $request->input('dir', 'desc');
        $allowed = ['name', 'students_count', 'teachers_count', 'created_at'];
        if (in_array($sort, $allowed)) {
            $query->orderBy($sort, $dir === 'asc' ? 'asc' : 'desc');
        }

        $classes = $query->paginate(20)->withQueryString();
        $schools = School::active()->get();
        $grades = SchoolClass::whereNotNull('grade_level')->distinct()->pluck('grade_level')->sort();
        $years = SchoolClass::whereNotNull('academic_year')->distinct()->pluck('academic_year')->sortDesc();

        $stats = [
            'total' => SchoolClass::count(),
            'total_students' => \DB::table('class_user')->where('role', 'student')->count(),
            'total_teachers' => \DB::table('class_user')->where('role', 'teacher')->count(),
        ];

        return view('admin.classes.index', compact('classes', 'schools', 'grades', 'years', 'stats'));
    }

    public function show(SchoolClass $class)
    {
        $this->authorize('view', $class);
        $class->load(['school', 'students', 'teachers']);
        return view('admin.classes.show', compact('class'));
    }

    public function create()
    {
        $this->authorize('create', SchoolClass::class);
        $schools = School::active()->get();
        return view('admin.classes.create', compact('schools'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', SchoolClass::class);

        $request->validate([
            'school_id' => 'required|exists:schools,id',
            'name' => 'required|string|max:100',
            'grade_level' => 'nullable|string|max:20',
            'academic_year' => 'nullable|string|max:10',
            'is_active' => 'nullable|boolean',
        ]);

        $class = SchoolClass::create([
            'school_id' => $request->input('school_id'),
            'name' => $request->input('name'),
            'grade_level' => $request->input('grade_level'),
            'academic_year' => $request->input('academic_year'),
            'is_active' => $request->boolean('is_active'),
        ]);

        ActivityLog::log('created', 'classes', $class);

        return redirect()->route('admin.classes.index')
            ->with('success', __('admin.class_created'));
    }

    public function edit(SchoolClass $class)
    {
        $this->authorize('update', $class);
        $schools = School::active()->get();
        return view('admin.classes.edit', compact('class', 'schools'));
    }

    public function update(Request $request, SchoolClass $class)
    {
        $this->authorize('update', $class);

        $request->validate([
            'school_id' => 'required|exists:schools,id',
            'name' => 'required|string|max:100',
            'grade_level' => 'nullable|string|max:20',
            'academic_year' => 'nullable|string|max:10',
            'is_active' => 'nullable|boolean',
        ]);

        $class->update([
            'school_id' => $request->input('school_id'),
            'name' => $request->input('name'),
            'grade_level' => $request->input('grade_level'),
            'academic_year' => $request->input('academic_year'),
            'is_active' => $request->boolean('is_active'),
        ]);

        ActivityLog::log('updated', 'classes', $class);

        return redirect()->route('admin.classes.index')
            ->with('success', __('admin.class_updated'));
    }

    public function destroy(SchoolClass $class)
    {
        $this->authorize('delete', $class);
        ActivityLog::log('deleted', 'classes', $class);
        $class->delete();

        return redirect()->route('admin.classes.index')
            ->with('success', __('admin.class_deleted'));
    }
}
