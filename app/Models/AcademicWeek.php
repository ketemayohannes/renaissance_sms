<?php

namespace App\Models;

use App\Services\AcademicCalendarService;
use App\Support\EthiopianCalendar;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

/**
 * One week of the official academic calendar (instructional or break).
 */
class AcademicWeek extends Model
{
    use Auditable;

    protected $fillable = [
        'academic_year_id', 'term_id', 'week_number', 'sequence', 'start_date', 'end_date',
        'working_days', 'week_type', 'description', 'source_label', 'source_key', 'needs_review', 'review_note',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'needs_review' => 'boolean',
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

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function events()
    {
        return $this->hasMany(AcademicEvent::class);
    }

    public function isBreak(): bool
    {
        return $this->week_type === 'break';
    }

    public function getEthiopianRangeAttribute(): string
    {
        return EthiopianCalendar::formatRange($this->start_date, $this->end_date);
    }
}
