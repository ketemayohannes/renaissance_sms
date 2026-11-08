<?php

/*
|--------------------------------------------------------------------------
| Role management feature map
|--------------------------------------------------------------------------
|
| How the Roles page (Admin › Security) groups permissions and explains what each one
| unlocks. Each permission here is the one its pages actually check (route middleware
| and the sidebar link), so ticking it on a role gives that role the pages listed.
|
| Super Admin and IT / System Admin always have everything. Every other role —
| Registrar and General Manager included — has only what is ticked for it.
|
| A permission that exists in the database but is not listed here is shown under
| "Not used by any page yet": ticking it has no effect until a page checks it.
| When you add a page, gate it with a permission and describe it here.
|
*/

return [

    'groups' => [

        'Students & Families' => [
            'view students' => 'See students, enrollments, graduates, ID cards, parents/guardians and disciplinary records.',
            'create students' => 'Add students, by form or spreadsheet import.',
            'edit students' => 'Edit students and guardians: transfers, withdrawals, electives, documents, discipline records and settings, portal accounts and password resets.',
            'delete students' => 'Delete students (one at a time or in bulk).',
            'promote students' => 'Run end-of-year promotion and enroll promoted students.',
        ],

        'School Setup' => [
            'manage divisions' => 'Divisions.',
            'manage grade levels' => 'Grade levels, academic years and terms.',
            'manage sections' => 'Sections (including import and bulk create) and the timetable.',
            'manage subjects' => 'Subjects, curriculum order and which subjects each grade takes.',
        ],

        'Gradebook & Assessment' => [
            'view subject gradebook' => 'Open the subject gradebook and export it.',
            'edit subject gradebook' => 'Enter, save and import marks in the subject gradebook.',
            'view master gradebook' => 'Open the master (section) gradebook, exports and low-performance lists.',
            'edit master gradebook' => 'Enter and import master-gradebook marks and auto-calculate semesters.',
            'configure assessments' => 'Assessment types, assessment templates and grade components.',
            'enter marks' => 'Activities and online exams: create them, write questions, grade submissions.',
            'publish results' => 'Approve or delete exam papers that teachers submit.',
        ],

        'Reports' => [
            'view marks' => 'Academic reports (and their settings), result analysis, general reports and exam paper reviews.',
            'generate report cards' => 'Report card settings and grade scales, report-card details, printing and ZIP exports.',
        ],

        'Attendance' => [
            'manage attendance' => 'Take and review student attendance from the admin register.',
        ],

        'Staff (HR)' => [
            'view employees' => 'See the staff list and profiles, and download staff documents.',
            'manage employees' => 'Add, edit and delete staff, import staff, reset staff passwords, activate/deactivate staff, delete staff documents, and assign teachers to sections.',
            'manage staff attendance' => 'Daily staff attendance register.',
            'manage leave requests' => 'Approve or reject staff leave requests.',
            'view staff availability' => 'Who is in today, teaching loads and free periods.',
            'request leave' => 'Teacher portal: request leave for yourself.',
        ],

        'Inventory' => [
            'view inventory' => 'Inventory dashboard, items and assets, reports and purchase lists.',
            'manage inventory' => 'Add and edit items and assets, stock in/out, fulfil requests.',
            'request inventory' => 'Submit item and purchase requests.',
            'approve inventory requests' => 'Decide item requests and give first approval to purchases.',
            'approve inventory purchases' => 'Give the final decision on purchase requests.',
        ],

        'Library' => [
            'view library' => 'Browse the catalogue and download digital books.',
            'manage books' => 'Add and edit books and upload digital copies.',
            'issue books' => 'Check books out to borrowers.',
            'return books' => 'Check returned books back in.',
        ],

        'Communication' => [
            'manage notice board' => 'Create, edit and delete notices.',
        ],

        'Finance & Health' => [
            'view fees' => 'Fee management page (not built yet).',
            'view payroll' => 'Payroll page (not built yet).',
            'view health' => 'Health records page (not built yet).',
        ],

        'System' => [
            'manage roles' => 'Roles & permissions, the audit log and ID card settings. Whoever has this can change any role, so treat it as full access.',
        ],

    ],

    // Heading for permissions that exist but are not listed above.
    'unused_group' => 'Not used by any page yet',

];
