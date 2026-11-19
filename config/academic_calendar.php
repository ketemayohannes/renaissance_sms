<?php

/*
|--------------------------------------------------------------------------
| Academic calendar
|--------------------------------------------------------------------------
| Event categories drive colour, school-day semantics and which helper
| (isExamPeriod, isRevisionWeek, isSemesterBreak…) an event counts toward.
|
|  instructional : default for new events — false closes school for teaching
|  closes_school : the day is not a school day (break, holiday, closure)
|  color         : palette key used by the dashboard calendar
*/

return [
    'timezone' => 'Africa/Addis_Ababa',

    // ISO weekdays that are normally working days when a year does not override them.
    'default_working_weekdays' => [1, 2, 3, 4, 5],

    // Days ahead shown in "Upcoming" lists by default.
    'upcoming_days' => 30,

    // Cache lifetime for computed calendar data (seconds). Caches are also
    // flushed whenever a calendar record changes.
    'cache_ttl' => 3600,

    'categories' => [
        'regular_class' => ['label' => 'Regular Classes', 'color' => 'blue', 'instructional' => true, 'closes_school' => false],
        'continuous_assessment' => ['label' => 'Continuous Assessment', 'color' => 'purple', 'instructional' => true, 'closes_school' => false],
        'project_work' => ['label' => 'Project Work', 'color' => 'amber', 'instructional' => true, 'closes_school' => false],
        'worksheet' => ['label' => 'Worksheet', 'color' => 'amber', 'instructional' => true, 'closes_school' => false],
        'midterm_exam' => ['label' => 'Midterm Examination', 'color' => 'red', 'instructional' => true, 'closes_school' => false, 'exam' => true],
        'final_exam' => ['label' => 'Final Examination', 'color' => 'red', 'instructional' => true, 'closes_school' => false, 'exam' => true],
        'revision' => ['label' => 'Revision', 'color' => 'teal', 'instructional' => true, 'closes_school' => false],
        'milestone' => ['label' => 'School Milestone', 'color' => 'green', 'instructional' => true, 'closes_school' => false],
        'parent_meeting' => ['label' => 'Parent Meeting / Event', 'color' => 'green', 'instructional' => false, 'closes_school' => false],
        'semester_break' => ['label' => 'Semester Break', 'color' => 'gray', 'instructional' => false, 'closes_school' => true],
        'holiday' => ['label' => 'Holiday', 'color' => 'gray', 'instructional' => false, 'closes_school' => true],
        'closure' => ['label' => 'School Closure', 'color' => 'gray', 'instructional' => false, 'closes_school' => true],
        'other' => ['label' => 'Other', 'color' => 'gray', 'instructional' => true, 'closes_school' => false],
    ],

    // Categories that represent the week-long backdrop of normal teaching. They are
    // still queryable but the dashboard shows them as a band, not as headline events.
    'background_categories' => ['regular_class', 'continuous_assessment', 'project_work', 'worksheet'],
];
