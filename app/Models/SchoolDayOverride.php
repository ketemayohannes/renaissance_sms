<?php

namespace App\Models;

use App\Services\AcademicCalendarService;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

/**
 * Explicit per-date decision that beats the weekly pattern and events,
 * e.g. a Saturday make-up day (true) or a one-day emergency closure (false).
 */
class SchoolDayOverride extends Model
{
    use Auditable;

    protected $fillable = ['academic_year_id', 'date', 'is_school_day', 'reason', 'created_by'];

    protected $casts = [
        'date' => 'date',
        'is_school_day' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => AcademicCalendarService::flushCache());
        static::deleted(fn () => AcademicCalendarService::flushCache());
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
