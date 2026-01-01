<?php

namespace App\Providers;

use App\Models\Student;
use App\Policies\StudentPolicy;
use App\Policies\ReportPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register Policies
        Gate::policy(Student::class, StudentPolicy::class);

        // Register Gates for Reports (non-model based)
        Gate::define('view-academic-reports', [ReportPolicy::class, 'viewAcademicReports']);
        Gate::define('generate-report-cards', [ReportPolicy::class, 'generateReportCards']);
        Gate::define('view-attendance-reports', [ReportPolicy::class, 'viewAttendanceReports']);
        Gate::define('view-grade-matrix', [ReportPolicy::class, 'viewGradeMatrix']);
        Gate::define('export-reports', [ReportPolicy::class, 'exportReports']);
        Gate::define('configure-report-settings', [ReportPolicy::class, 'configureSettings']);
        Gate::define('view-subject-analysis', [ReportPolicy::class, 'viewSubjectAnalysis']);
        Gate::define('bulk-print-reports', [ReportPolicy::class, 'bulkPrint']);


    }
}
