<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;

/**
 * CachedData - Centralized caching for frequently-accessed static data.
 * 
 * This helper dramatically improves performance by caching reference data
 * that rarely changes (grade levels, subjects, terms, etc).
 * 
 * Cache is automatically invalidated after 1 hour or when relevant
 * models are created/updated/deleted (if observer hooks are added).
 */
class CachedData
{
    private const TTL = 3600; // 1 hour

    private static function getDivisionKey()
    {
        if (app()->runningInConsole()) return 'global';
        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user) return 'global';
        
        if ($user->hasAnyRole(['Super Admin', 'IT / System Admin', 'Registrar', 'General Manager', 'HR Manager', 'Senior Finance Officer', 'Librarian'])) {
            return 'global';
        }
        
        $employee = \App\Models\Employee::withoutGlobalScopes()->where('user_id', $user->id)->first();
        return $employee && $employee->division_id ? $employee->division_id : 'global';
    }

    public static function gradeLevels()
    {
        $key = 'cached_grade_levels_' . self::getDivisionKey();
        return Cache::remember($key, self::TTL, function() {
            return \App\Models\GradeLevel::orderBy('sort_order')->get();
        });
    }

    public static function subjects()
    {
        return Cache::remember('cached_subjects', self::TTL, function() {
            return \App\Models\Subject::with('gradeLevels')->where('is_active', true)->orderBy('name')->get();
        });
    }

    public static function allSubjects()
    {
        return Cache::remember('cached_all_subjects', self::TTL, function() {
            return \App\Models\Subject::with('gradeLevels')->orderBy('name')->get();
        });
    }

    public static function terms()
    {
        return Cache::remember('cached_terms', self::TTL, function() {
            return \App\Models\Term::orderBy('start_date', 'desc')->get();
        });
    }

    public static function academicYears()
    {
        return Cache::remember('cached_academic_years', self::TTL, function() {
            return \App\Models\AcademicYear::orderBy('start_date', 'desc')->get();
        });
    }

    public static function activeAcademicYear()
    {
        return Cache::remember('cached_active_academic_year', self::TTL, function() {
            return \App\Models\AcademicYear::where('is_active', true)->first();
        });
    }

    public static function sections()
    {
        $key = 'cached_sections_' . self::getDivisionKey();
        return Cache::remember($key, self::TTL, function() {
            return \App\Models\Section::with('gradeLevel')->where('is_active', true)->get();
        });
    }

    public static function sectionsGrouped()
    {
        $key = 'cached_sections_grouped_' . self::getDivisionKey();
        return Cache::remember($key, self::TTL, function() {
            return \App\Models\Section::with('gradeLevel')->get()->groupBy(function($section) {
                return $section->gradeLevel ? $section->gradeLevel->name : 'Unassigned';
            });
        });
    }

    public static function assessmentTypes()
    {
        return Cache::remember('cached_assessment_types', self::TTL, function() {
            return \App\Models\AssessmentType::orderBy('name')->get();
        });
    }

    public static function divisions()
    {
        return Cache::remember('cached_divisions', self::TTL, function() {
            return \App\Models\Division::orderBy('name')->get();
        });
    }

    public static function permissions()
    {
        return Cache::remember('cached_permissions', self::TTL, function() {
            return \Spatie\Permission\Models\Permission::all()->groupBy(function($data) {
                return explode('.', $data->name)[0] ?? 'other';
            });
        });
    }

    public static function flush()
    {
        // Global keys
        Cache::forget('cached_grade_levels_global');
        Cache::forget('cached_sections_global');
        Cache::forget('cached_sections_grouped_global');
        
        // Division-specific keys
        try {
            $divisions = \App\Models\Division::all();
            foreach ($divisions as $division) {
                Cache::forget('cached_grade_levels_' . $division->id);
                Cache::forget('cached_sections_' . $division->id);
                Cache::forget('cached_sections_grouped_' . $division->id);
            }
        } catch (\Exception $e) {
            // Ignore if tables don't exist yet
        }

        // Old keys (for safety)
        Cache::forget('cached_grade_levels');
        Cache::forget('cached_sections');
        Cache::forget('cached_sections_grouped');

        Cache::forget('cached_subjects');
        Cache::forget('cached_all_subjects');
        Cache::forget('cached_terms');
        Cache::forget('cached_academic_years');
        Cache::forget('cached_active_academic_year');
        Cache::forget('cached_assessment_types');
        Cache::forget('cached_divisions');
        Cache::forget('cached_permissions');
        Cache::forget('all_grade_levels');
        Cache::forget('all_sections_grouped');
        Cache::forget('employee_stats_summary');
    }

    /**
     * Alias for flush() to prevent missing method errors.
     */
    public static function clearAll()
    {
        self::flush();
    }

    /**
     * Clear employee-related cached data.
     */
    public static function flushEmployeeCache()
    {
        Cache::forget('employee_stats_summary');
        \Illuminate\Support\Facades\Log::info('Employee cache flushed');
    }

    public static function employeeStats()
    {
        return Cache::remember('employee_stats_summary', 3600, function() {
            return [
                'total' => \App\Models\Employee::count(),
                'active' => \App\Models\Employee::where('status', 'active')->count(),
                'teachers' => \App\Models\Employee::where('designation', 'Teacher')->count(),
            ];
        });
    }
}
