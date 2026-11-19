<?php

namespace App\Events;

use App\Models\AcademicYear;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Fired after an administrator changes the school calendar (event created, edited,
 * cancelled or deleted; week or school-day override changed; term dates aligned).
 *
 * Integration point for notifications/announcements: register a listener to tell
 * teachers, students and parents about schedule changes. No listener is registered
 * by default, so nothing is sent automatically.
 */
class AcademicCalendarChanged
{
    use Dispatchable;

    /**
     * @param  string  $action  created | updated | cancelled | deleted | aligned
     * @param  array  $before  previous values (empty for created)
     * @param  array  $after  new values (empty for deleted)
     * @param  array  $impacts  conflicts found (see AcademicCalendarChangeService::impactsFor)
     */
    public function __construct(
        public AcademicYear $academicYear,
        public string $subjectType,
        public int $subjectId,
        public string $action,
        public array $before = [],
        public array $after = [],
        public array $impacts = [],
        public ?int $userId = null,
    ) {}
}
