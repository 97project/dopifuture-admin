<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\SchoolClass;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Support\Facades\DB;

/**
 * Portal-side reporting controller.
 * Access is scoped by role via PortalRole middleware.
 */
class PortalReportController extends Controller
{
    public function __construct(private ReportService $reportService)
    {
    }

    /**
     * Reports overview dashboard.
     */
    public function index()
    {
        $user = auth()->user();
        $data = ['user' => $user];

        // Determine school IDs based on role
        if ($user->hasAnyRole(['school-admin', 'school-principal'])) {
            $schoolIds = $user->schools()->pluck('schools.id');
            $data['overview'] = $this->reportService->getSchoolOverviewStats($schoolIds);
            $data['apps'] = Application::active()->ordered()->get();
        } elseif ($user->hasRole('teacher')) {
            // Teacher sees class-level overview
            $data['myClasses'] = $user->classes()
                ->with('school')
                ->withCount('students')
                ->get();
            $data['apps'] = Application::active()->ordered()->get();
        } elseif ($user->hasRole('student')) {
            // Student sees personal report
            $data['studentReport'] = $this->reportService->getStudentReport($user);
        }

        return view('portal.reports.index', $data);
    }

    /**
     * Per-application detailed report.
     */
    public function appReport(Application $app)
    {
        $user = auth()->user();
        $userIds = $this->getScopedUserIds($user);

        $data = $this->reportService->getAppReport($app, $userIds);
        $data['user'] = $user;

        return view('portal.reports.app', $data);
    }

    /**
     * Student detailed report (all apps).
     */
    public function studentReport(User $student)
    {
        $authUser = auth()->user();

        // Authorization checks
        if ($authUser->hasRole('student') && $authUser->id !== $student->id) {
            abort(403);
        }

        if ($authUser->hasRole('teacher')) {
            $classIds = $authUser->classes()->pluck('school_classes.id');
            $studentIds = DB::table('class_student')->whereIn('school_class_id', $classIds)->pluck('user_id');
            if (!$studentIds->contains($student->id)) {
                abort(403);
            }
        }

        $student->load(['roles', 'schools', 'classes.school', 'applications']);
        $reportData = $this->reportService->getStudentReport($student);

        return view('portal.reports.student', [
            'student' => $student,
            'reportData' => $reportData,
            'apps' => Application::active()->ordered()->get(),
        ]);
    }

    /**
     * Class report (optionally filtered by app).
     */
    public function classReport(SchoolClass $class, ?Application $app = null)
    {
        $user = auth()->user();

        // Teacher must be assigned to the class
        if ($user->hasRole('teacher')) {
            if (!$user->classes()->where('school_classes.id', $class->id)->exists()) {
                abort(403);
            }
        }

        $class->load(['school', 'students', 'teachers']);
        $reportData = $this->reportService->getClassReport($class, $app);

        return view('portal.reports.class', [
            'class' => $class,
            'reportData' => $reportData,
            'selectedApp' => $app,
            'apps' => Application::active()->ordered()->get(),
        ]);
    }

    /* ── Helpers ─────────────────────────────────────── */

    private function getScopedUserIds(User $user): \Illuminate\Support\Collection
    {
        if ($user->hasAnyRole(['school-admin', 'school-principal'])) {
            $schoolIds = $user->schools()->pluck('schools.id');
            return DB::table('school_user')
                ->whereIn('school_id', $schoolIds)
                ->pluck('user_id')
                ->unique();
        }

        if ($user->hasRole('teacher')) {
            $classIds = $user->classes()->pluck('school_classes.id');
            return DB::table('class_student')
                ->whereIn('school_class_id', $classIds)
                ->pluck('user_id')
                ->unique();
        }

        // Student
        return collect([$user->id]);
    }
}
