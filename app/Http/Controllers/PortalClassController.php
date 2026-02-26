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
        return view('portal.classes.show', compact('class'));
    }

    public function create()
    {
        $this->guardClassManagement();
        $schools = $this->getAvailableSchools();
        return view('portal.classes.form', ['class' => new SchoolClass, 'schools' => $schools]);
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
        return view('portal.classes.form', compact('class', 'schools'));
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

    private function getAvailableSchools()
    {
        return auth()->user()->schools()->get();
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
