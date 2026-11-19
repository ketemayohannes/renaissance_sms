<?php

namespace App\Models;

use App\Services\AcademicCalendarService;
use App\Support\EthiopianCalendar;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'ethiopian_year',
        'start_date',
        'end_date',
        'working_weekdays',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'ethiopian_year' => 'integer',
        'working_weekdays' => 'array',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => AcademicCalendarService::flushCache());
        static::deleted(fn () => AcademicCalendarService::flushCache());
    }

    /**
     * Scope a query to only include active academic years.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function terms()
    {
        return $this->hasMany(Term::class);
    }

    public function academicWeeks()
    {
        return $this->hasMany(AcademicWeek::class)->orderBy('sequence');
    }

    public function academicEvents()
    {
        return $this->hasMany(AcademicEvent::class);
    }

    public function schoolDayOverrides()
    {
        return $this->hasMany(SchoolDayOverride::class);
    }

    /** ISO weekdays (1 = Mon … 7 = Sun) that are normally working days. */
    public function workingWeekdays(): array
    {
        return $this->working_weekdays ?: config('academic_calendar.default_working_weekdays', [1, 2, 3, 4, 5]);
    }

    /** e.g. "2019 E.C. / 2026–2027 G.C." */
    public function getCalendarLabelAttribute(): string
    {
        return EthiopianCalendar::academicYearLabel($this->ethiopian_year, $this->start_date, $this->end_date) ?: $this->name;
    }
}
