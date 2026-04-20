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
            
            // Skip scope for roles that require global visibility or non-logged in users
            if (!$user || $user->hasAnyRole(['Super Admin', 'IT / System Admin', 'Registrar', 'General Manager', 'HR Manager', 'Senior Finance Officer', 'Librarian']) || app()->runningInConsole()) {
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
                        $builder->whereHas('enrollments.section.gradeLevel', fn($q) => $q->where('division_id', $divisionId));
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
                        $builder->whereHas('student.enrollments.section.gradeLevel', fn($q) => $q->where('division_id', $divisionId));
                        break;
                    case 'student_attendance':
                        $builder->whereHas('student.enrollments.section.gradeLevel', fn($q) => $q->where('division_id', $divisionId));
                        break;
                }
            }
        });
    }
}
