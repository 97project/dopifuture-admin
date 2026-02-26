<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\School;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Support\Facades\DB;

/**
 * Admin-side reporting controller — more detailed than portal.
 */
class ReportController extends Controller
{
    public function __construct(private ReportService $reportService)
    {
    }

    /**
     * System-wide reports dashboard.
     */
    public function index()
    {
        $allSchoolIds = School::active()->pluck('id');
        $overview = $this->reportService->getSchoolOverviewStats($allSchoolIds);
        $apps = Application::active()->ordered()->get();
        $schools = School::active()
            ->withCount(['users', 'classes', 'licenses'])
            ->orderByDesc('users_count')
            ->get();

        return view('admin.reports.index', compact('overview', 'apps', 'schools'));
    }

    /**
     * Per-application detailed report (all schools).
     */
    public function appReport(Application $app)
    {
        $allUserIds = $app->users()->pluck('users.id');
        $data = $this->reportService->getAppReport($app, $allUserIds);

        return view('admin.reports.app', $data);
    }

    /**
     * Per-school detailed report.
     */
    public function schoolReport(School $school)
    {
        $schoolIds = collect([$school->id]);
        $overview = $this->reportService->getSchoolOverviewStats($schoolIds);
        $apps = Application::active()->ordered()->get();

        $school->load(['users', 'classes.students', 'licenses.purchases']);

        return view('admin.reports.school', compact('school', 'overview', 'apps'));
    }

    /**
     * Admin-level student detailed report.
     */
    public function studentReport(User $student)
    {
        $student->load(['roles', 'schools', 'classes.school', 'applications']);
        $reportData = $this->reportService->getStudentReport($student);
        $apps = Application::active()->ordered()->get();

        return view('admin.reports.student', compact('student', 'reportData', 'apps'));
    }
}
