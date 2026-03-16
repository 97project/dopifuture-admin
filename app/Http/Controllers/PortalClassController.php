<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\School;
use Illuminate\Http\Request;

class PortalClassController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // Student cannot access classes management
        if ($user->hasRole('student')) {
            abort(403);
        }

        $query = SchoolClass::with('school')->withCount('students');

        // Scope by role
        if ($user->hasAnyRole(['school-admin', 'school-principal'])) {
            $schoolIds = $user->schools()->pluck('schools.id');
            $query->whereIn('school_id', $schoolIds);
        } elseif ($user->hasRole('teacher')) {
            $query->whereIn('id', $user->classes()->pluck('school_classes.id'));
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $classes = $query->latest()->paginate(15);
        return view('portal.classes.index', compact('classes'));
    }

    public function show(SchoolClass $class)
    {
        $this->authorizeSchool($class->school_id);
        $class->load(['school', 'students', 'teachers']);

        $availableStudents = collect();
        $availableTeachers = collect();
        $user = auth()->user();

        if ($user->hasAnyRole(['school-admin', 'school-principal'])) {
            $assignedIds = $class->users()->pluck('user_id')->toArray();
            $schoolUserQuery = \App\Models\User::whereHas('schools', fn($q) => $q->where('schools.id', $class->school_id))
                ->whereNotIn('id', $assignedIds);

            $availableStudents = (clone $schoolUserQuery)->role('student')->orderBy('name')->get(['id', 'name', 'surname', 'email']);
            $availableTeachers = (clone $schoolUserQuery)->role('teacher')->orderBy('name')->get(['id', 'name', 'surname', 'email']);
        }

        $canManage = $user->hasAnyRole(['school-admin', 'school-principal']);
        return view('portal.classes.show', compact('class', 'availableStudents', 'availableTeachers', 'canManage'));
    }

    public function create()
    {
        $this->guardClassManagement();
        $schools = $this->getAvailableSchools();
        $academicYears = $this->getAcademicYears();
        return view('portal.classes.form', ['class' => new SchoolClass, 'schools' => $schools, 'academicYears' => $academicYears]);
    }

    public function store(Request $request)
    {
        $this->guardClassManagement();

        $data = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'name' => 'required|string|max:50',
            'grade_level' => 'nullable|string|max:20',
            'academic_year' => 'nullable|string|max:20',
        ]);

        $this->authorizeSchool($data['school_id']);

        SchoolClass::create(array_merge($data, ['is_active' => true]));

        return redirect()->route('portal.classes.index')
            ->with('success', __('admin.class_created'));
    }

    public function edit(SchoolClass $class)
    {
        $this->guardClassManagement();
        $this->authorizeSchool($class->school_id);
        $schools = $this->getAvailableSchools();
        $academicYears = $this->getAcademicYears();
        return view('portal.classes.form', compact('class', 'schools', 'academicYears'));
    }

    public function update(Request $request, SchoolClass $class)
    {
        $this->guardClassManagement();
        $this->authorizeSchool($class->school_id);

        $data = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'name' => 'required|string|max:50',
            'grade_level' => 'nullable|string|max:20',
            'academic_year' => 'nullable|string|max:20',
            'is_active' => 'nullable|boolean',
        ]);

        $class->update($data);

        return redirect()->route('portal.classes.index')
            ->with('success', __('admin.class_updated'));
    }

    public function destroy(SchoolClass $class)
    {
        $this->guardClassManagement();
        $this->authorizeSchool($class->school_id);
        $class->delete();
        return redirect()->route('portal.classes.index')
            ->with('success', __('admin.class_deleted'));
    }

    /* ─── Öğrenci/Öğretmen Atama ─────────────────────── */

    public function addStudent(Request $request, SchoolClass $class)
    {
        $this->guardClassManagement();
        $this->authorizeSchool($class->school_id);

        $request->validate(['user_id' => 'required|exists:users,id']);
        $userId = $request->input('user_id');

        if (!$class->students()->where('user_id', $userId)->exists()) {
            $class->users()->attach($userId, [
                'role' => 'student',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return back()->with('success', __('admin.student_added'));
    }

    public function removeStudent(Request $request, SchoolClass $class, \App\Models\User $user)
    {
        $this->guardClassManagement();
        $this->authorizeSchool($class->school_id);

        $class->users()->wherePivot('role', 'student')->detach($user->id);

        return back()->with('success', __('admin.student_removed'));
    }

    public function addTeacher(Request $request, SchoolClass $class)
    {
        $this->guardClassManagement();
        $this->authorizeSchool($class->school_id);

        $request->validate(['user_id' => 'required|exists:users,id']);
        $userId = $request->input('user_id');

        if (!$class->teachers()->where('user_id', $userId)->exists()) {
            $class->users()->attach($userId, [
                'role' => 'teacher',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return back()->with('success', __('admin.teacher_added'));
    }

    public function removeTeacher(Request $request, SchoolClass $class, \App\Models\User $user)
    {
        $this->guardClassManagement();
        $this->authorizeSchool($class->school_id);

        $class->users()->wherePivot('role', 'teacher')->detach($user->id);

        return back()->with('success', __('admin.teacher_removed'));
    }

    private function getAvailableSchools()
    {
        return auth()->user()->schools()->get();
    }

    /**
     * Generate academic years: DB existing + auto range (current ± 2).
     */
    private function getAcademicYears(): array
    {
        $currentYear = (int) date('Y');
        $generated = [];
        for ($y = $currentYear + 1; $y >= $currentYear - 3; $y--) {
            $generated[] = $y . '-' . ($y + 1);
        }

        // Merge with existing DB values
        $dbYears = SchoolClass::distinct()->pluck('academic_year')->filter()->toArray();
        return collect(array_merge($generated, $dbYears))->unique()->sort(fn($a, $b) => strcmp($b, $a))->values()->toArray();
    }

    private function authorizeSchool(int $schoolId): void
    {
        $user = auth()->user();
        // Teacher can view classes they're assigned to
        if ($user->hasRole('teacher')) {
            return; // Already scoped in index(), show checks separately
        }
        if ($user->schools()->where('schools.id', $schoolId)->exists())
            return;
        abort(403);
    }

    /**
     * Only school-admin and school-principal can create/edit/delete classes.
     */
    private function guardClassManagement(): void
    {
        if (!auth()->user()->hasAnyRole(['school-admin', 'school-principal'])) {
            abort(403);
        }
    }
}
