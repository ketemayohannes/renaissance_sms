<?php

namespace App\Models;

use App\Services\AcademicCalendarService;
use App\Support\EthiopianCalendar;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * A school-wide calendar activity (exam window, revision week, break, holiday,
 * milestone…). Subject-level exams with rooms and times are NOT stored here;
 * they reference these windows.
 */
class AcademicEvent extends Model
{
    use Auditable;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'academic_year_id', 'term_id', 'academic_week_id', 'category', 'title', 'description',
        'start_date', 'end_date', 'is_instructional', 'priority', 'status', 'source', 'source_key',
        'source_text', 'needs_review', 'review_note', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_instructional' => 'boolean',
        'needs_review' => 'boolean',
        'priority' => 'integer',
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

    public function week()
    {
        return $this->belongsTo(AcademicWeek::class, 'academic_week_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /** Events overlapping [start, end] (inclusive). */
    public function scopeOverlapping(Builder $query, $start, $end): Builder
    {
        return $query->whereDate('start_date', '<=', EthiopianCalendar::date($end)->toDateString())
            ->whereDate('end_date', '>=', EthiopianCalendar::date($start)->toDateString());
    }

    public function scopeInCategories(Builder $query, array $categories): Builder
    {
        return $query->whereIn('category', $categories);
    }

    public static function categories(): array
    {
        return config('academic_calendar.categories', []);
    }

    public function categoryConfig(): array
    {
        return self::categories()[$this->category] ?? self::categories()['other'];
    }

    public function isExam(): bool
    {
        return (bool) ($this->categoryConfig()['exam'] ?? false);
    }

    /** True when school is closed on these days (break, holiday, closure). */
    public function closesSchool(): bool
    {
        return (bool) ($this->categoryConfig()['closes_school'] ?? false);
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function getEthiopianRangeAttribute(): string
    {
        return EthiopianCalendar::formatRange($this->start_date, $this->end_date);
    }
}
