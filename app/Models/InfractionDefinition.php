<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class InfractionDefinition extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'code',
        'tier',
        'description',
        'default_penalty',
        'requires_parent_notification',
        'is_active',
        'display_order',
        'created_by',
    ];

    protected $casts = [
        'requires_parent_notification' => 'boolean',
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    /* ── Relationships ── */

    public function disciplinaryRecords()
    {
        return $this->hasMany(DisciplinaryRecord::class);
    }

    public function escalationRules()
    {
        return $this->hasMany(EscalationRule::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /* ── Scopes ── */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByTier($query, string $tier)
    {
        return $query->where('tier', $tier);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('name');
    }

    /* ── Accessors ── */

    public function getDisplayTierAttribute(): string
    {
        return match ($this->tier) {
            'minor'    => 'Minor',
            'moderate' => 'Moderate',
            'critical' => 'Critical',
            default    => ucfirst($this->tier),
        };
    }

    public function getTierColorAttribute(): string
    {
        return match ($this->tier) {
            'minor'    => 'emerald',
            'moderate' => 'amber',
            'critical' => 'rose',
            default    => 'slate',
        };
    }

    /* ── Static Helpers ── */

    public static function tiers(): array
    {
        return [
            'minor'    => 'Minor',
            'moderate' => 'Moderate',
            'critical' => 'Critical',
        ];
    }
}
