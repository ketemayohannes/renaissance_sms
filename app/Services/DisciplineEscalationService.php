<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\DisciplinaryRecord;
use App\Models\EscalationRule;
use App\Models\InfractionDefinition;
use App\Models\Student;

class DisciplineEscalationService
{
    /**
     * Evaluate escalation rules after a new DisciplinaryRecord is created.
     * Returns the matched EscalationRule if one was applied, or null.
     */
    public function evaluateEscalation(DisciplinaryRecord $record): ?EscalationRule
    {
        $infraction = $record->infractionDefinition;

        if (! $infraction) {
            return null;
        }

        // Load matching rules: specific-infraction rules first, then tier-level fallbacks
        $rules = EscalationRule::active()
            ->where(function ($q) use ($infraction) {
                $q->where('infraction_definition_id', $infraction->id)
                  ->orWhere(function ($q2) use ($infraction) {
                      $q2->whereNull('infraction_definition_id')
                         ->where('tier', $infraction->tier);
                  });
            })
            ->orderByRaw("CASE WHEN infraction_definition_id IS NOT NULL THEN 0 ELSE 1 END")
            ->orderBy('occurrence_threshold', 'desc') // highest threshold first
            ->get();

        if ($rules->isEmpty()) {
            return null;
        }

        $academicYear = AcademicYear::where('is_active', true)->first();

        foreach ($rules as $rule) {
            $count = $this->countPriorOccurrences(
                $record->student,
                $infraction,
                $rule->time_window_days,
                $academicYear
            );

            // Count includes the current record
            if ($count >= $rule->occurrence_threshold) {
                $this->applyEscalation($record, $rule);
                return $rule;
            }
        }

        return null;
    }

    /**
     * Count how many times this student has had this infraction (or tier) within the window.
     */
    private function countPriorOccurrences(
        Student $student,
        InfractionDefinition $infraction,
        ?int $windowDays,
        ?AcademicYear $academicYear
    ): int {
        $query = DisciplinaryRecord::where('student_id', $student->id)
            ->where('infraction_definition_id', $infraction->id);

        if ($windowDays) {
            // Rolling time window
            $query->where('incident_date', '>=', now()->subDays($windowDays));
        } elseif ($academicYear) {
            // Current academic year scope
            $query->where('academic_year_id', $academicYear->id);
        }

        return $query->count();
    }

    /**
     * Apply the escalation action to the disciplinary record.
     */
    private function applyEscalation(DisciplinaryRecord $record, EscalationRule $rule): void
    {
        $updates = [
            'escalation_rule_id'        => $rule->id,
            'escalation_action_applied' => $rule->escalation_action,
        ];

        // Auto-escalate status
        if (in_array($rule->escalation_action, ['suspension', 'expulsion_referral'])) {
            $updates['status'] = 'escalated';
        }

        // Auto-flag parent notification
        if ($rule->auto_notify_parent) {
            $updates['notify_parent'] = true;
        }

        $record->update($updates);
    }
}
