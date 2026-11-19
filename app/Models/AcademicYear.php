<?php

namespace App\Models;

use App\Services\AcademicCalendarService;
use App\Support\EthiopianCalendar;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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
        static::saved(function () {
            AcademicCalendarService::flushCache();
            Cache::forget('cached_active_academic_year');
        });
        static::deleted(function () {
            AcademicCalendarService::flushCache();
            Cache::forget('cached_active_academic_year');
        });

        // A new year starts with the previous year's subject assignments, which can then
        // be changed for the new year alone.
        static::created(fn (AcademicYear $year) => $year->copySubjectAssignmentsFromPreviousYear());
    }

    /**
     * Copy grade ↔ subject assignments from the closest earlier year (or, failing that,
     * the most recent other year). Does nothing if this year already has assignments.
     */
    public function copySubjectAssignmentsFromPreviousYear(): int
    {
        if (DB::table('grade_level_subjects')->where('academic_year_id', $this->id)->exists()) {
            return 0;
        }

        $others = static::where('id', '!=', $this->id)->orderByDesc('start_date')->orderByDesc('id');
        $source = ($this->start_date ? (clone $others)->where('start_date', '<', $this->start_date)->value('id') : null)
            ?? $others->value('id');

        if (! $source) {
            return 0;
        }

        $now = now();
        $rows = DB::table('grade_level_subjects')->where('academic_year_id', $source)->get()
            ->map(fn ($r) => [
                'grade_level_id' => $r->grade_level_id,
                'subject_id' => $r->subject_id,
                'academic_year_id' => $this->id,
                'is_required' => $r->is_required,
                'sort_order' => $r->sort_order,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all();

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('grade_level_subjects')->insert($chunk);
        }

        return count($rows);
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
