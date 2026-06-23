<?php

namespace App\Helpers;

use App\Models\ReportCardSetting;

class KindergartenGradeHelper
{
    /**
     * Get the configured grade scales, sorted by min score descending.
     */
    public static function getGradeScales(): array
    {
        $settings = ReportCardSetting::first();
        if ($settings && !empty($settings->grade_scales)) {
            // Ensure they are sorted by min descending
            return collect($settings->grade_scales)
                ->sortByDesc(fn($scale) => (float)$scale['min'])
                ->values()
                ->toArray();
        }

        return [
            ['min' => 90, 'grade' => 'A', 'label' => 'Excellent'],
            ['min' => 80, 'grade' => 'B', 'label' => 'Very Good'],
            ['min' => 70, 'grade' => 'C', 'label' => 'Satisfactory'],
            ['min' => 60, 'grade' => 'D', 'label' => 'Fair'],
            ['min' => 0,  'grade' => 'F', 'label' => 'Poor'],
        ];
    }

    /**
     * Check if a section/gradeLevel belongs to Kindergarten division.
     */
    public static function isKindergarten($context): bool
    {
        if (!$context) {
            return false;
        }

        if ($context instanceof \App\Models\Section) {
            $gradeLevel = $context->gradeLevel;
        } elseif ($context instanceof \App\Models\GradeLevel) {
            $gradeLevel = $context;
        } else {
            return false;
        }

        return $gradeLevel && $gradeLevel->division_id == 1;
    }

    /**
     * Convert a numeric score to a letter grade.
     */
    public static function toGrade($score): string
    {
        if ($score === null || $score === '') {
            return '-';
        }

        $numericScore = (float) $score;
        $scales = self::getGradeScales();

        foreach ($scales as $scale) {
            if ($numericScore >= (float)$scale['min']) {
                return $scale['grade'];
            }
        }

        return 'F'; // Default fallback
    }
}
