<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class EscalationRule extends Model
{
    use HasUuids;

    protected $fillable = [
        'infraction_definition_id',
        'tier',
        'occurrence_threshold',
        'time_window_days',
        'escalation_action',
        'escalation_description',
        'auto_notify_parent',
        'is_active',
        'legal_reference',
        'created_by',
    ];

    protected $casts = [
        'occurrence_threshold' => 'integer',
        'time_window_days'     => 'integer',
        'auto_notify_parent'   => 'boolean',
        'is_active'            => 'boolean',
    ];

    /* ── Relationships ── */

    public function infractionDefinition()
    {
        return $this->belongsTo(InfractionDefinition::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function disciplinaryRecords()
    {
        return $this->hasMany(DisciplinaryRecord::class);
    }

    /* ── Scopes ── */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForTier($query, string $tier)
    {
        return $query->where('tier', $tier);
    }

    /* ── Accessors ── */

    public function getActionLabelAttribute(): string
    {
        return static::escalationActions()[$this->escalation_action] ?? ucfirst(str_replace('_', ' ', $this->escalation_action));
    }

    /* ── Static Helpers ── */

    public static function escalationActions(): array
    {
        return [
            'verbal_warning'      => 'Verbal Warning',
            'written_warning'     => 'Written Warning',
            'parent_conference'   => 'Parent Conference',
            'detention'           => 'Detention',
            'community_service'   => 'Community Service',
            'suspension'          => 'Suspension',
            'expulsion_referral'  => 'Expulsion Referral',
        ];
    }
}
