<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReportPolicy
{
    use HandlesAuthorization;

    /**
     * Perform pre-authorization checks.
     * Super Admin has unrestricted access.
     * Admin permissions are granted by Super Admin.
     * Principal has full access to reports.
     */
    public function before(User $user, string $ability): ?bool
    {
        // Super Admin has unrestricted access
        if ($user->hasRole(['Super Admin', 'IT / System Admin', 'Registrar', 'General Manager'])) {
            return true;
        }

        // Principal has full access to reports
        if ($user->hasRole('Principal')) {
            return true;
        }

        // Admin permissions are set by Super Admin - fall through
        return null;
    }

    /**
     * Determine whether the user can view academic reports.
     */
    public function viewAcademicReports(User $user): bool
    {
        return $user->hasAnyRole(['Admin', 'Teacher', 'Registrar', 'Counselor']);
    }

    /**
     * Determine whether the user can generate report cards.
     */
    public function generateReportCards(User $user): bool
    {
        return $user->hasAnyRole(['Admin', 'Registrar']);
    }

    /**
     * Determine whether the user can view attendance reports.
     */
    public function viewAttendanceReports(User $user): bool
    {
        return $user->hasAnyRole(['Teacher', 'Registrar', 'Counselor']);
    }

    /**
     * Determine whether the user can view grade matrix.
     */
    public function viewGradeMatrix(User $user): bool
    {
        return $user->hasAnyRole(['Teacher', 'Registrar', 'Counselor']);
    }

    /**
     * Determine whether the user can export reports.
     */
    public function exportReports(User $user): bool
    {
        return $user->hasAnyRole(['Admin', 'Registrar']);
    }

    /**
     * Determine whether the user can configure report settings.
     */
    public function configureSettings(User $user): bool
    {
        // Admin can configure, Super Admin handled by before()
        return $user->hasRole('Admin');
    }

    /**
     * Determine whether the user can view subject analysis.
     */
    public function viewSubjectAnalysis(User $user): bool
    {
        return $user->hasAnyRole(['Teacher', 'Registrar', 'Counselor']);
    }

    /**
     * Determine if user can bulk print report cards.
     */
    public function bulkPrint(User $user): bool
    {
        return $user->hasAnyRole(['Admin', 'Registrar']);
    }
}
