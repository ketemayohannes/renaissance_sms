<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DisciplinaryRecord extends Model
{
    protected $fillable = [
        'student_id',
        'academic_year_id',
        'infraction_definition_id',
        'escalation_rule_id',
        'escalation_action_applied',
        'incident_date',
        'description',
        'action_taken',
        'reported_by',
        'handled_by',
        'status',
        'resolution_date',
        'resolution_notes',
        'notify_parent',
    ];

    protected $casts = [
        'incident_date'   => 'date',
        'resolution_date' => 'date',
        'notify_parent'   => 'boolean',
    ];

    /* ── Relationships ── */

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function infractionDefinition()
    {
        return $this->belongsTo(InfractionDefinition::class);
    }

    public function escalationRule()
    {
        return $this->belongsTo(EscalationRule::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function handler()
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    /* ── Accessors ── */

    /**
     * The human-readable infraction name.
     */
    public function getInfractionNameAttribute(): string
    {
        return $this->infractionDefinition?->name ?? 'Unknown';
    }

    /**
     * The tier (severity) from the linked infraction definition.
     */
    public function getTierAttribute(): string
    {
        return $this->infractionDefinition?->tier ?? 'minor';
    }

    /**
     * Whether an escalation was triggered for this record.
     */
    public function getWasEscalatedAttribute(): bool
    {
        return $this->escalation_action_applied !== null;
    }
}
