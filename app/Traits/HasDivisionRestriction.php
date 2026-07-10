<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait HasDivisionRestriction
{
    /**
     * Boot the trait and apply the division scope.
     */
    protected static function bootHasDivisionRestriction()
    {
        static::addGlobalScope('division_access', function (Builder $builder) {
            $user = Auth::user();
            
            // Skip scope for roles that require global visibility or non-logged in users.
            // Real console usage (artisan commands, queue workers, seeders) always bypasses
            // it too — but running the test suite also reports runningInConsole() as true
            // (PHPUnit's process is CLI), so that alone would silently skip the scope for
            // every acting-as-a-restricted-user feature test. Exclude that case explicitly.
            $isRealConsoleUsage = app()->runningInConsole() && !app()->runningUnitTests();

            if (!$user || $user->hasAnyRole(['Super Admin', 'IT / System Admin', 'Registrar', 'General Manager', 'HR Manager', 'Senior Finance Officer', 'Librarian']) || $isRealConsoleUsage) {
                return;
            }

            // Check if user has an employee profile with a division restriction
            // Cache the employee profile per request to avoid redundant queries
            static $employeeCache = null;
            static $lastUserId = null;

            if ($lastUserId !== $user->id) {
                $employeeCache = \App\Models\Employee::withoutGlobalScopes()
                    ->where('user_id', $user->id)
                    ->first();
                $lastUserId = $user->id;
            }

            $employee = $employeeCache;
            
            if ($employee && $employee->division_id) {
                $table = (new static)->getTable();
                $divisionId = $employee->division_id;
                
                switch ($table) {
                    case 'divisions':
                        $builder->where('id', $divisionId);
                        break;
                    case 'grade_levels':
                        $builder->where('division_id', $divisionId);
                        break;
                    case 'sections':
                        $builder->whereHas('gradeLevel', fn($q) => $q->where('division_id', $divisionId));
                        break;
                    case 'students':
                        // A promoted student keeps last year's enrollment open (end_date IS
                        // NULL) alongside this year's, so matching "any" open enrollment can
                        // still find them via the division they've already left. Pin to the
                        // active academic year so visibility follows where they are now.
                        $builder->whereHas('enrollments', function ($q) use ($divisionId) {
                            $q->whereNull('end_date')
                              ->when(
                                  \App\Helpers\CachedData::activeAcademicYear()?->id,
                                  fn ($sq, $yearId) => $sq->where('academic_year_id', $yearId)
                              )
                              ->whereHas('section.gradeLevel', fn($sq) => $sq->where('division_id', $divisionId));
                        });
                        break;
                    case 'employees':
                        // Leadership sees staff in their division (Teachers, etc.)
                        // We also allow them to see themselves obviously
                        $builder->where(function($q) use ($divisionId, $employee) {
                            $q->where('division_id', $divisionId)
                              ->orWhere('id', $employee->id);
                        });
                        break;
                    case 'teacher_assignments':
                        $builder->whereHas('section.gradeLevel', fn($q) => $q->where('division_id', $divisionId));
                        break;
                    case 'student_marks':
                        // Match against the enrollment for the SAME academic year as the mark
                        // itself (not "any" enrollment), so a division's staff keep seeing their
                        // own historical marks for a student who has since moved divisions,
                        // without leaking marks recorded after the move.
                        $builder->whereHas('student.enrollments', function ($q) use ($divisionId) {
                            $q->whereColumn('student_enrollments.academic_year_id', 'student_marks.academic_year_id')
                              ->whereHas('section.gradeLevel', fn($sq) => $sq->where('division_id', $divisionId));
                        });
                        break;
                    case 'student_attendance':
                        $builder->whereHas('student.enrollments', function ($q) use ($divisionId) {
                            $q->where(function ($yq) {
                                  $yq->whereColumn('student_enrollments.academic_year_id', 'student_attendance.academic_year_id')
                                     ->orWhereNull('student_attendance.academic_year_id');
                              })
                              ->whereHas('section.gradeLevel', fn($sq) => $sq->where('division_id', $divisionId));
                        });
                        break;
                }
            }
        });
    }
}
