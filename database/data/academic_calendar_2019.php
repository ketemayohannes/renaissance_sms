<?php

/*
|--------------------------------------------------------------------------
| Official academic calendar — Renaissance School, 2019 E.C. (2026/2027 G.C.)
|--------------------------------------------------------------------------
| Transcribed from "RENAISSANCE SCHOOL ACADEMIC CALENDAR OF 2019E.C" (.docx).
| Dates are kept in the ORIGINAL Ethiopian form [month, day]; the importer
| converts them with andegna/calender. Week numbers restart in every quarter
| table of the source (auto-numbered lists), and 'text' is the source wording.
|
| Ethiopian months: 1 Meskerem, 2 Tikimt, 3 Hidar, 4 Tahsas, 5 Tir, 6 Yekatit,
| 7 Megabit, 8 Miyazia, 9 Ginbot, 10 Sene, 11 Hamle, 12 Nehase, 13 Pagume.
|
| 'kind' is our classification of the row's activity (see config/academic_calendar.php).
| 'milestone' adds a one-day school milestone on the first day of the week.
| Nothing here is corrected: questionable rows are flagged in 'review'.
*/

return [
    'ethiopian_year' => 2019,
    'name' => '2019 E.C. (2026/2027)',
    'source_document' => 'RENAISSANCE SCHOOL ACADEMIC CALENDAR OF 2019E.C.docx',

    'semesters' => [
        // The source prints the 2nd-quarter page (including the break week) under "FIRST SEMESTER".
        1 => ['name' => 'Semester 1', 'quarters' => [1, 2]],
        2 => ['name' => 'Semester 2', 'quarters' => [3, 4]],
    ],

    'quarters' => [
        1 => ['semester' => 1, 'weeks' => [
            ['no' => 1, 'start' => [1, 4], 'end' => [1, 8], 'days' => 5, 'label' => 'Mes. 4 – Mes. 8', 'text' => 'Meskerem 4/2019 E.C School will be opened', 'kind' => 'regular_class',
                'milestone' => ['title' => 'School Opens', 'date' => [1, 4]]],
            ['no' => 2, 'start' => [1, 11], 'end' => [1, 15], 'days' => 5, 'label' => 'Mes. 11 – Mes. 15', 'text' => 'Regular class & continuous Assessment', 'kind' => 'continuous_assessment'],
            ['no' => 3, 'start' => [1, 18], 'end' => [1, 22], 'days' => 5, 'label' => 'Mes. 18 – Mes. 22', 'text' => 'Regular class & continuous Assessment', 'kind' => 'continuous_assessment'],
            ['no' => 4, 'start' => [1, 25], 'end' => [1, 29], 'days' => 5, 'label' => 'Mes. 25 – Mes. 29', 'text' => 'Regular class & continuous Assessment', 'kind' => 'continuous_assessment'],
            ['no' => 5, 'start' => [2, 2], 'end' => [2, 6], 'days' => 5, 'label' => 'Tik. 2 – Tik. 6', 'text' => 'Regular class and Project Work', 'kind' => 'project_work'],
            ['no' => 6, 'start' => [2, 9], 'end' => [2, 13], 'days' => 5, 'label' => 'Tik. 9 – Tik. 13', 'text' => 'Regular class and continuous Assessment', 'kind' => 'continuous_assessment'],
            ['no' => 7, 'start' => [2, 16], 'end' => [2, 20], 'days' => 5, 'label' => 'Tik. 16 – Tik. 20', 'text' => 'Regular class and Mid Exam', 'kind' => 'midterm_exam'],
            ['no' => 8, 'start' => [2, 23], 'end' => [2, 27], 'days' => 5, 'label' => 'Tik. 23 – Tik. 27', 'text' => 'Regular class & Worksheet', 'kind' => 'worksheet'],
            ['no' => 9, 'start' => [2, 30], 'end' => [3, 4], 'days' => 5, 'label' => 'Tik. 30 – Hid. 4', 'text' => 'Revision week', 'kind' => 'revision'],
            ['no' => 10, 'start' => [3, 7], 'end' => [3, 11], 'days' => 5, 'label' => 'Hid. 7 – Hid. 11', 'text' => '1st quarter Final exam', 'kind' => 'final_exam'],
        ]],

        2 => ['semester' => 1, 'weeks' => [
            ['no' => 1, 'start' => [3, 14], 'end' => [3, 18], 'days' => 5, 'label' => 'Hid. 14 – Hid. 18', 'text' => 'Hidar 14/2019 E.C beginning of 2nd quarter regular class.', 'kind' => 'regular_class',
                'milestone' => ['title' => '2nd Quarter Begins', 'date' => [3, 14]]],
            ['no' => 2, 'start' => [3, 21], 'end' => [3, 25], 'days' => 5, 'label' => 'Hid. 21 – Hid. 25', 'text' => 'Regular class & continuous Assessment', 'kind' => 'continuous_assessment'],
            ['no' => 3, 'start' => [3, 28], 'end' => [4, 2], 'days' => 5, 'label' => 'Hid. 28 – Tah. 2', 'text' => 'Regular class & continuous Assessment', 'kind' => 'continuous_assessment'],
            ['no' => 4, 'start' => [4, 5], 'end' => [4, 9], 'days' => 5, 'label' => 'Tah. 5 – Tah. 9', 'text' => 'Regular class & Project Work', 'kind' => 'project_work'],
            ['no' => 5, 'start' => [4, 12], 'end' => [4, 16], 'days' => 5, 'label' => 'Tah. 12 – Tah. 16', 'text' => 'Regular class and continuous Assessment', 'kind' => 'continuous_assessment'],
            ['no' => 6, 'start' => [4, 19], 'end' => [4, 23], 'days' => 5, 'label' => 'Tah. 19 – Tah. 23', 'text' => 'Regular class and Mid Exam', 'kind' => 'midterm_exam'],
            ['no' => 7, 'start' => [4, 26], 'end' => [4, 30], 'days' => 4, 'label' => 'Tah. 26 – Tah. 30', 'text' => 'Regular class and continuous Assessment', 'kind' => 'continuous_assessment'],
            ['no' => 8, 'start' => [5, 3], 'end' => [5, 7], 'days' => 5, 'label' => 'Tir. 3 – Tir. 7', 'text' => 'Regular class and Work sheet', 'kind' => 'worksheet'],
            ['no' => 9, 'start' => [5, 10], 'end' => [5, 14], 'days' => 4, 'label' => 'Tir. 10 – Tir. 14', 'text' => 'Revision week', 'kind' => 'revision'],
            ['no' => 10, 'start' => [5, 17], 'end' => [5, 21], 'days' => 5, 'label' => 'Tir. 17 – Tir. 21', 'text' => '2nd quarter Final Exam', 'kind' => 'final_exam'],
            ['no' => 11, 'start' => [5, 24], 'end' => [5, 28], 'days' => 5, 'label' => 'Tir. 24 – Tir. 28', 'text' => 'First semester break', 'kind' => 'semester_break', 'week_type' => 'break'],
        ]],

        3 => ['semester' => 2, 'weeks' => [
            ['no' => 1, 'start' => [6, 1], 'end' => [6, 5], 'days' => 5, 'label' => 'Yek. 1 – Yek. 5', 'text' => 'Yekatit 1/2019E.C beginning of 3rd quarter regular class.', 'kind' => 'regular_class',
                'milestone' => ['title' => '3rd Quarter Begins', 'date' => [6, 1]]],
            ['no' => 2, 'start' => [6, 8], 'end' => [6, 12], 'days' => 5, 'label' => 'Yek. 8 – Yek. 12', 'text' => 'Regular class & continuous Assessment', 'kind' => 'continuous_assessment'],
            ['no' => 3, 'start' => [6, 15], 'end' => [6, 19], 'days' => 5, 'label' => 'Yek. 15 – Yek. 19', 'text' => 'Regular class & continuous Assessment', 'kind' => 'continuous_assessment'],
            ['no' => 4, 'start' => [6, 22], 'end' => [6, 26], 'days' => 4, 'label' => 'Yek. 22 – Yek. 26', 'text' => 'Regular class & project work', 'kind' => 'project_work'],
            ['no' => 5, 'start' => [6, 29], 'end' => [7, 3], 'days' => 4, 'label' => 'Yek. 29 – Meg. 3', 'text' => 'Regular class and continuous Assessment', 'kind' => 'continuous_assessment',
                'review' => 'The source lists 4 working days, but no fixed-date public holiday falls in this week. The closed day is most likely Eid al-Fitr (lunar; expected around 9–10 March 2027), or possibly International Women\'s Day (Yekatit 29 = 8 March). Add the holiday once the date is confirmed.'],
            ['no' => 6, 'start' => [7, 6], 'end' => [7, 10], 'days' => 5, 'label' => 'Meg. 6 – Meg. 10', 'text' => 'Regular class and Mid Exam', 'kind' => 'midterm_exam'],
            ['no' => 7, 'start' => [7, 13], 'end' => [7, 17], 'days' => 5, 'label' => 'Meg. 13 – Meg. 17', 'text' => 'Regular class and continuous Assessment', 'kind' => 'continuous_assessment'],
            ['no' => 8, 'start' => [7, 20], 'end' => [7, 24], 'days' => 5, 'label' => 'Meg. 20 – Meg. 24', 'text' => 'Regular class & Worksheet', 'kind' => 'worksheet'],
            ['no' => 9, 'start' => [7, 27], 'end' => [8, 1], 'days' => 5, 'label' => 'Meg. 27 – Miy. 1', 'text' => 'Revision Week', 'kind' => 'revision'],
            ['no' => 10, 'start' => [8, 4], 'end' => [8, 8], 'days' => 5, 'label' => 'Miy. 4 – Miy. 8', 'text' => '3rd quarter Final Exam', 'kind' => 'final_exam'],
        ]],

        4 => ['semester' => 2, 'weeks' => [
            ['no' => 1, 'start' => [8, 11], 'end' => [8, 15], 'days' => 5, 'label' => 'Miy. 11 – Miy. 15', 'text' => 'Miyazia 11/2019 E.C beginning of 4th Quarter Regular class.', 'kind' => 'regular_class',
                'milestone' => ['title' => '4th Quarter Begins', 'date' => [8, 11]]],
            ['no' => 2, 'start' => [8, 18], 'end' => [8, 22], 'days' => 4, 'label' => 'Miy. 18 – Miy. 22', 'text' => 'Regular class & continuous Assessment', 'kind' => 'continuous_assessment'],
            ['no' => 3, 'start' => [8, 25], 'end' => [8, 29], 'days' => 4, 'label' => 'Miy. 25 – Miy. 29', 'text' => 'Regular class & continuous Assessment', 'kind' => 'continuous_assessment'],
            ['no' => 4, 'start' => [9, 2], 'end' => [9, 6], 'days' => 5, 'label' => 'Gin. 2 – Gin. 6', 'text' => 'Regular class & project work', 'kind' => 'project_work'],
            ['no' => 5, 'start' => [9, 9], 'end' => [9, 13], 'days' => 5, 'label' => 'Gin. 9 – Gin. 13', 'text' => 'Regular class & continuous Assessment', 'kind' => 'continuous_assessment',
                'review' => 'Eid al-Adha (lunar) is expected around 16–17 May 2027. If it is observed on Monday 17 May (Ginbot 9), this week has 4 working days, not the 5 listed.'],
            ['no' => 6, 'start' => [9, 16], 'end' => [9, 20], 'days' => 5, 'label' => 'Gin. 16 – Gin. 20', 'text' => 'Regular class and Mid Exam', 'kind' => 'midterm_exam',
                'review' => 'Ginbot 20 (Friday 28 May 2027, Derg Downfall Day) is a national public holiday, but the source lists 5 working days and a mid exam this week. Confirm whether school is open that day.'],
            ['no' => 7, 'start' => [9, 23], 'end' => [9, 27], 'days' => 5, 'label' => 'Gin. 23 – Gin. 27', 'text' => 'Regular class and continuous Assessment', 'kind' => 'continuous_assessment'],
            ['no' => 8, 'start' => [9, 30], 'end' => [10, 4], 'days' => 5, 'label' => 'Gin. 30 – Sen. 4', 'text' => 'Regular class & Worksheet', 'kind' => 'worksheet'],
            ['no' => 9, 'start' => [10, 7], 'end' => [10, 11], 'days' => 5, 'label' => 'Sen. 7 – Sen. 11', 'text' => 'Revision Week', 'kind' => 'revision'],
            ['no' => 10, 'start' => [10, 14], 'end' => [10, 18], 'days' => 5, 'label' => 'Sen. 14 – Sen. 18', 'text' => '4th Quarter Final Exam', 'kind' => 'final_exam'],
        ]],
    ],

    // Single-day school events listed outside the weekly rows.
    'events' => [
        ['key' => 'parents-day', 'title' => "Parent's Day & School Closing Program", 'date' => [10, 27], 'label' => 'Sen. 27/2019',
            'text' => "Parent's day & school closing program", 'category' => 'parent_meeting', 'priority' => 2, 'instructional' => false,
            'review' => 'Sene 27, 2019 E.C. is Sunday 4 July 2027. Confirm the program is intended for a Sunday.'],
    ],

    // The source gives only working-day COUNTS. These fixed-date national holidays
    // fall inside the weeks listed with 4 working days and account for them exactly.
    // They are imported as inferred (flagged for review) — not taken from the source.
    'inferred_holidays' => [
        ['key' => 'genna', 'title' => 'Ethiopian Christmas (Genna)', 'date' => [4, 29]],
        ['key' => 'timket', 'title' => 'Epiphany (Timket)', 'date' => [5, 11]],
        ['key' => 'adwa', 'title' => 'Adwa Victory Day', 'date' => [6, 23]],
        ['key' => 'good-friday', 'title' => 'Good Friday (Siklet)', 'date' => [8, 22]],
        ['key' => 'patriots', 'title' => "Patriots' Victory Day", 'date' => [8, 27]],
    ],
];
