<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;



Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', [App\Http\Controllers\Auth\RedirectController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Mandatory Password Change
    Route::get('/auth/change-password', [App\Http\Controllers\Auth\PasswordChangeController::class, 'show'])->name('auth.change-password');
    Route::post('/auth/change-password', [App\Http\Controllers\Auth\PasswordChangeController::class, 'update'])->name('auth.change-password.update');

    // Student Routes
    Route::middleware(['role:Student', 'force.password.change'])->prefix('student')->name('student.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Student\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/grades', [App\Http\Controllers\Student\GradeController::class, 'index'])->name('grades.index');
        Route::get('/grades/download', [App\Http\Controllers\Student\GradeController::class, 'downloadReport'])->name('grades.download');
        Route::get('/profile', [App\Http\Controllers\Student\ProfileController::class, 'show'])->name('profile');
        Route::put('/password', [App\Http\Controllers\Student\ProfileController::class, 'updatePassword'])->name('password.update');

        // Academic Activities
        Route::get('/activities', [App\Http\Controllers\Student\ActivityController::class, 'index'])->name('activities.index');
        Route::get('/activities/{activity}', [App\Http\Controllers\Student\ActivityController::class, 'show'])->name('activities.show');
        Route::post('/activities/{activity}/submit', [App\Http\Controllers\Student\ActivityController::class, 'submit'])->name('activities.submit');
        Route::get('/activities/{activity}/exam', [App\Http\Controllers\Student\ActivityController::class, 'takeExam'])->name('activities.exam');
        Route::post('/activities/{activity}/exam', [App\Http\Controllers\Student\ActivityController::class, 'submitExam'])->name('activities.exam.submit');
    });

    // Admin Routes
    Route::middleware(['role_or_permission:Super Admin|view students|view employees|view fees', 'force.password.change'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/search', [App\Http\Controllers\Admin\GlobalSearchController::class, 'search'])->name('global-search');
        
        // Role Management
        Route::resource('roles', App\Http\Controllers\Admin\RoleController::class)->middleware('permission:manage roles');
        
        // Audit Logs
        Route::get('audit-logs', [App\Http\Controllers\Admin\AuditLogController::class, 'index'])->name('audit-logs.index');
        
        // Academic Structure
        Route::resource('divisions', App\Http\Controllers\Admin\DivisionController::class)->except(['show']);
        Route::resource('grade-levels', App\Http\Controllers\Admin\GradeLevelController::class)->except(['show']);
        // Section Import
        Route::get('sections/import', [App\Http\Controllers\Admin\SectionController::class, 'import'])->name('sections.import');
        Route::post('sections/import', [App\Http\Controllers\Admin\SectionController::class, 'upload'])->name('sections.upload');
        Route::get('sections/download-template', [App\Http\Controllers\Admin\SectionController::class, 'downloadTemplate'])->name('sections.download-template');
        // Bulk Create Sections
        Route::get('sections/bulk-create', [App\Http\Controllers\Admin\SectionController::class, 'bulkCreate'])->name('sections.bulk-create');
        Route::post('sections/bulk-create', [App\Http\Controllers\Admin\SectionController::class, 'bulkStore'])->name('sections.bulk-store');

        Route::resource('sections', App\Http\Controllers\Admin\SectionController::class)->except(['show']);
        
        // Subject Ordering
        Route::get('subjects/reorder', [App\Http\Controllers\Admin\SubjectController::class, 'reorder'])->name('subjects.reorder');
        Route::post('subjects/reorder', [App\Http\Controllers\Admin\SubjectController::class, 'updateOrder'])->name('subjects.update-order');
        
        // Subject Import
        Route::get('subjects/import', [App\Http\Controllers\Admin\SubjectController::class, 'import'])->name('subjects.import');
        Route::post('subjects/upload', [App\Http\Controllers\Admin\SubjectController::class, 'upload'])->name('subjects.upload');
        Route::get('subjects/template', [App\Http\Controllers\Admin\SubjectController::class, 'downloadTemplate'])->name('subjects.template');
        
        Route::resource('subjects', App\Http\Controllers\Admin\SubjectController::class)->except(['show']);
        
        // Subject Assignment
        Route::get('subject-assignments/bulk-assign', [App\Http\Controllers\Admin\SubjectAssignmentController::class, 'bulkAssignForm'])->name('subject-assignments.bulk-assign');
        Route::post('subject-assignments/bulk-assign', [App\Http\Controllers\Admin\SubjectAssignmentController::class, 'storeBulkAssign'])->name('subject-assignments.bulk-assign.store');
        
        Route::get('subject-assignments', [App\Http\Controllers\Admin\SubjectAssignmentController::class, 'index'])->name('subject-assignments.index');
        

        Route::get('subject-assignments/{grade_level}/edit', [App\Http\Controllers\Admin\SubjectAssignmentController::class, 'edit'])->name('subject-assignments.edit');
        Route::put('subject-assignments/{grade_level}', [App\Http\Controllers\Admin\SubjectAssignmentController::class, 'update'])->name('subject-assignments.update');

        Route::resource('academic-years', App\Http\Controllers\Admin\AcademicYearController::class)->except(['show']);
        Route::resource('terms', App\Http\Controllers\Admin\TermController::class)->except(['show']);
        Route::get('terms/quarters/{academicYear}', [App\Http\Controllers\Admin\TermController::class, 'getQuarters'])->name('terms.get-quarters');

        
        // Student Management
        Route::get('students/export', [App\Http\Controllers\Admin\StudentController::class, 'export'])->name('students.export');
        Route::get('students/import', [App\Http\Controllers\Admin\StudentController::class, 'import'])->name('students.import');
        Route::post('students/import', [App\Http\Controllers\Admin\StudentController::class, 'upload'])->name('students.upload');
        Route::get('students/download-template', [App\Http\Controllers\Admin\StudentController::class, 'downloadTemplate'])->name('students.download-template');
        Route::post('students/{student}/toggle-block', [App\Http\Controllers\Admin\StudentController::class, 'toggleBlock'])->name('students.toggle-block');
        Route::get('students/{student}/transfer', [App\Http\Controllers\Admin\StudentController::class, 'transferForm'])->name('students.transfer');
        Route::post('students/{student}/transfer', [App\Http\Controllers\Admin\StudentController::class, 'transfer'])->name('students.transfer.store');
        Route::get('students/{student}/assign-electives', [App\Http\Controllers\Admin\StudentController::class, 'assignElectivesForm'])->name('students.assign-electives');
        Route::post('students/{student}/assign-electives', [App\Http\Controllers\Admin\StudentController::class, 'storeElectives'])->name('students.assign-electives.store');
        Route::post('students/{student}/siblings', [App\Http\Controllers\Admin\StudentController::class, 'linkSibling'])->name('students.siblings.link');
        Route::delete('students/{student}/siblings/{sibling}', [App\Http\Controllers\Admin\StudentController::class, 'unlinkSibling'])->name('students.siblings.unlink');
        Route::post('students/bulk-destroy', [App\Http\Controllers\Admin\StudentController::class, 'bulkDestroy'])->name('students.bulk-destroy');
        Route::get('students/{student}/withdraw', [App\Http\Controllers\Admin\StudentController::class, 'withdrawForm'])->name('students.withdraw');
        Route::post('students/{student}/withdraw', [App\Http\Controllers\Admin\StudentController::class, 'processWithdrawal'])->name('students.withdraw.store');
        Route::get('students/{student}/status-history', [App\Http\Controllers\Admin\StudentController::class, 'statusHistory'])->name('students.status-history');
        Route::get('id-cards', [App\Http\Controllers\Admin\StudentController::class, 'idCardsIndex'])->name('id-cards.index');
        
        // New Student Routes
        Route::post('students/{student}/document', [App\Http\Controllers\Admin\StudentController::class, 'storeDocument'])->name('students.store-document');
        Route::delete('students/{student}/document/{document}', [App\Http\Controllers\Admin\StudentController::class, 'deleteDocument'])->name('students.delete-document');
        Route::post('students/{id}/restore', [App\Http\Controllers\Admin\StudentController::class, 'restore'])->name('students.restore');
        Route::post('students/bulk-id-cards', [App\Http\Controllers\Admin\StudentController::class, 'bulkIdCardsSelected'])->name('students.bulk-id-cards-selected');
        
        // Electives Bulk Assign
        Route::get('electives/bulk-assign', [App\Http\Controllers\Admin\ElectiveController::class, 'bulkAssignForm'])->name('electives.bulk-assign');
        Route::post('electives/bulk-assign', [App\Http\Controllers\Admin\ElectiveController::class, 'storeBulkAssign'])->name('electives.bulk-assign.store');
        Route::get('electives/subjects', [App\Http\Controllers\Admin\ElectiveController::class, 'getSubjects'])->name('electives.get-subjects');
        Route::get('electives/sections', [App\Http\Controllers\Admin\ElectiveController::class, 'getSections'])->name('electives.get-sections');
        Route::get('electives/students', [App\Http\Controllers\Admin\ElectiveController::class, 'getStudents'])->name('electives.get-students');

        // Gradebook Routes
        Route::get('gradebook/export-template', [App\Http\Controllers\Admin\GradebookController::class, 'exportTemplate'])->name('gradebook.export-template');
        Route::post('gradebook/import', [App\Http\Controllers\Admin\GradebookController::class, 'import'])->name('gradebook.import');
        Route::get('gradebook', [App\Http\Controllers\Admin\GradebookController::class, 'index'])->name('gradebook.index');
        Route::get('gradebook/entry', [App\Http\Controllers\Admin\GradebookController::class, 'entry'])->name('gradebook.entry');
        Route::post('gradebook/store', [App\Http\Controllers\Admin\GradebookController::class, 'store'])->name('gradebook.store');

        // Section Grade Entry Routes
        Route::get('section-grades', [App\Http\Controllers\Admin\SectionGradeController::class, 'index'])->name('section-grades.index');
        Route::get('section-grades/entry', [App\Http\Controllers\Admin\SectionGradeController::class, 'entry'])->name('section-grades.entry');
        Route::post('section-grades/store', [App\Http\Controllers\Admin\SectionGradeController::class, 'store'])->name('section-grades.store');
        Route::get('section-grades/export', [App\Http\Controllers\Admin\SectionGradeController::class, 'export'])->name('section-grades.export');
        Route::post('section-grades/import', [App\Http\Controllers\Admin\SectionGradeController::class, 'import'])->name('section-grades.import');
        Route::post('section-grades/calculate', [App\Http\Controllers\Admin\SectionGradeController::class, 'calculateSemester'])->name('section-grades.calculate');
        
        // Assessment System Routes
        Route::resource('assessment-types', App\Http\Controllers\Admin\AssessmentTypeController::class);
        Route::post('assessment-templates/bulk-destroy', [App\Http\Controllers\Admin\AssessmentTemplateController::class, 'bulkDestroy'])->name('assessment-templates.bulk-destroy');
        Route::post('assessment-templates/destroy-by-term', [App\Http\Controllers\Admin\AssessmentTemplateController::class, 'destroyByTerm'])->name('assessment-templates.destroy-by-term');
        Route::get('assessment-templates/reorder', [App\Http\Controllers\Admin\AssessmentTemplateController::class, 'reorder'])->name('assessment-templates.reorder');
        Route::post('assessment-templates/reorder', [App\Http\Controllers\Admin\AssessmentTemplateController::class, 'updateOrder'])->name('assessment-templates.update-order');
        Route::resource('assessment-templates', App\Http\Controllers\Admin\AssessmentTemplateController::class);
        Route::resource('grade-components', App\Http\Controllers\Admin\GradeComponentController::class);
        Route::get('grade-components/get-components', [App\Http\Controllers\Admin\GradeComponentController::class, 'getComponents'])->name('grade-components.get-components');

        // ID Card Settings
        Route::get('id-card-settings', [App\Http\Controllers\Admin\IdCardSettingController::class, 'index'])->name('id-card-settings.index');
        Route::put('id-card-settings', [App\Http\Controllers\Admin\IdCardSettingController::class, 'update'])->name('id-card-settings.update');


        // Gradebook AJAX Routes
        Route::get('gradebook/get-sections', [App\Http\Controllers\Admin\GradebookController::class, 'getSections'])->name('gradebook.get-sections');
        Route::get('gradebook/get-subjects', [App\Http\Controllers\Admin\GradebookController::class, 'getSubjects'])->name('gradebook.get-subjects');
        Route::get('gradebook/get-terms', [App\Http\Controllers\Admin\GradebookController::class, 'getTerms'])->name('gradebook.get-terms');

        // Report Cards
        Route::get('report-cards/settings', [App\Http\Controllers\Admin\ReportCardController::class, 'settings'])->name('report-cards.settings');
        Route::post('report-cards/settings', [App\Http\Controllers\Admin\ReportCardController::class, 'updateSettings'])->name('report-cards.update-settings');
        Route::get('report-cards/yearly-settings', [App\Http\Controllers\Admin\ReportCardController::class, 'yearlySettings'])->name('report-cards.yearly-settings');
        Route::post('report-cards/yearly-settings', [App\Http\Controllers\Admin\ReportCardController::class, 'updateYearlySettings'])->name('report-cards.update-yearly-settings');

        // Academic Reports
        Route::get('academic-reports/settings', [App\Http\Controllers\Admin\AcademicReportController::class, 'settings'])->name('academic-reports.settings');
        Route::post('academic-reports/settings', [App\Http\Controllers\Admin\AcademicReportController::class, 'updateSettings'])->name('academic-reports.settings.update');
        Route::get('academic-reports', [App\Http\Controllers\Admin\AcademicReportController::class, 'index'])->name('academic-reports.index');
        Route::get('academic-reports/show', [App\Http\Controllers\Admin\AcademicReportController::class, 'show'])->name('academic-reports.show');
        Route::get('academic-reports/subject-analysis', [App\Http\Controllers\Admin\AcademicReportController::class, 'subjectAnalysis'])->name('academic-reports.subject-analysis');
        Route::get('academic-reports/grade-matrix', [App\Http\Controllers\Admin\AcademicReportController::class, 'gradeMatrix'])->name('academic-reports.grade-matrix');
        Route::get('academic-reports/grade-matrix/pdf', [App\Http\Controllers\Admin\AcademicReportController::class, 'gradeMatrixPdf'])->name('academic-reports.grade-matrix.pdf');
        Route::get('academic-reports/matrix-reorder', [App\Http\Controllers\Admin\AcademicReportController::class, 'matrixReorder'])->name('academic-reports.matrix-reorder');
        Route::post('academic-reports/matrix-reorder', [App\Http\Controllers\Admin\AcademicReportController::class, 'updateMatrixOrder'])->name('academic-reports.matrix-reorder.update');
        Route::post('academic-reports/recalculate', [App\Http\Controllers\Admin\AcademicReportController::class, 'recalculate'])->name('academic-reports.recalculate');

        Route::get('section-grades/{section}/report-card-details', [App\Http\Controllers\Admin\ReportCardController::class, 'entry'])->name('section-grades.report-card-entry');
        Route::post('section-grades/{section}/report-card-details', [App\Http\Controllers\Admin\ReportCardController::class, 'storeEntry'])->name('section-grades.store-report-card-entry');
        
        Route::get('section-grades/{section}/report-card/bulk-print', [App\Http\Controllers\Admin\ReportCardController::class, 'bulkPrint'])->name('section-grades.bulk-print-report-cards');
        
        Route::get('students/{student}/report-card/pdf', [App\Http\Controllers\Admin\ReportCardController::class, 'generatePdf'])->name('report-cards.pdf');

        // Background Exports
        Route::get('report-cards/exports', [App\Http\Controllers\Admin\ReportCardController::class, 'exports'])->name('report-cards.exports');
        Route::get('section-grades/{section}/report-card/bulk-export', [App\Http\Controllers\Admin\ReportCardController::class, 'bulkExport'])->name('section-grades.bulk-export-report-cards');
        Route::get('report-cards/exports/{exportRequest}/download', [App\Http\Controllers\Admin\ReportCardController::class, 'downloadExport'])->name('report-cards.download-export');

        // Academic Activities Module
        Route::get('activities/get-templates', [App\Http\Controllers\Admin\AcademicActivityController::class, 'getTemplates'])->name('activities.get-templates');
        Route::get('activities/{activity}/evaluate', [App\Http\Controllers\Admin\AcademicActivityController::class, 'evaluate'])->name('activities.evaluate');
        Route::post('activities/{activity}/evaluate', [App\Http\Controllers\Admin\AcademicActivityController::class, 'storeEvaluation'])->name('activities.evaluate.store');
        Route::get('activities/{activity}/questions', [App\Http\Controllers\Admin\AcademicActivityController::class, 'manageQuestions'])->name('activities.questions');
        Route::post('activities/{activity}/questions', [App\Http\Controllers\Admin\AcademicActivityController::class, 'storeQuestions'])->name('activities.questions.store');
        Route::resource('activities', App\Http\Controllers\Admin\AcademicActivityController::class);

        // Attendance Management
        Route::get('attendance', [App\Http\Controllers\Admin\AttendanceController::class, 'index'])->name('attendance.index');
        Route::get('attendance/register', [App\Http\Controllers\Admin\AttendanceController::class, 'register'])->name('attendance.register');
        Route::post('attendance/store', [App\Http\Controllers\Admin\AttendanceController::class, 'store'])->name('attendance.store');
        Route::get('attendance/report', [App\Http\Controllers\Admin\AttendanceController::class, 'report'])->name('attendance.report');
        
        // Timetable Management
        Route::get('timetable', [App\Http\Controllers\Admin\TimetableController::class, 'index'])->name('timetable.index');
        Route::get('timetable/builder', [App\Http\Controllers\Admin\TimetableController::class, 'builder'])->name('timetable.builder');
        Route::post('timetable/store', [App\Http\Controllers\Admin\TimetableController::class, 'store'])->name('timetable.store');

        // Promotion Management
        Route::get('promotions', [App\Http\Controllers\Admin\PromotionController::class, 'index'])->name('promotions.index');
        Route::post('promotions/rules', [App\Http\Controllers\Admin\PromotionController::class, 'storeRule'])->name('promotions.store-rule');
        Route::delete('promotions/rules/{promotionRule}', [App\Http\Controllers\Admin\PromotionController::class, 'deleteRule'])->name('promotions.delete-rule');
        Route::get('promotions/process', [App\Http\Controllers\Admin\PromotionController::class, 'processForm'])->name('promotions.process');
        Route::post('promotions/preview', [App\Http\Controllers\Admin\PromotionController::class, 'preview'])->name('promotions.preview');
        Route::post('promotions/execute', [App\Http\Controllers\Admin\PromotionController::class, 'execute'])->name('promotions.execute');
        Route::get('promotions/history', [App\Http\Controllers\Admin\PromotionController::class, 'history'])->name('promotions.history');

        // Disciplinary Records
        Route::get('disciplinary', [App\Http\Controllers\Admin\DisciplinaryController::class, 'index'])->name('disciplinary.index');
        Route::get('disciplinary/create/{student?}', [App\Http\Controllers\Admin\DisciplinaryController::class, 'create'])->name('disciplinary.create');
        Route::post('disciplinary', [App\Http\Controllers\Admin\DisciplinaryController::class, 'store'])->name('disciplinary.store');
        Route::get('disciplinary/{disciplinary}', [App\Http\Controllers\Admin\DisciplinaryController::class, 'show'])->name('disciplinary.show');
        Route::put('disciplinary/{disciplinary}', [App\Http\Controllers\Admin\DisciplinaryController::class, 'update'])->name('disciplinary.update');
        Route::get('students/{student}/disciplinary', [App\Http\Controllers\Admin\DisciplinaryController::class, 'studentRecords'])->name('students.disciplinary');

        // Student ID Cards
        Route::get('students/{student}/id-card', [App\Http\Controllers\Admin\StudentController::class, 'generateIdCard'])->name('students.id-card');
        Route::get('sections/{section}/id-cards', [App\Http\Controllers\Admin\StudentController::class, 'bulkIdCards'])->name('sections.bulk-id-cards');
        Route::post('guardians/{guardian}/create-user', [App\Http\Controllers\Admin\StudentController::class, 'createGuardianUser'])->name('guardians.create-user');
        Route::post('students/{student}/create-user', [App\Http\Controllers\Admin\StudentController::class, 'createStudentUser'])->name('students.create-user');
        Route::post('students/{student}/reset-password', [App\Http\Controllers\Admin\StudentController::class, 'resetStudentPassword'])->name('students.reset-password');

        // Human Resources
        Route::get('employees/import', [App\Http\Controllers\Admin\EmployeeController::class, 'import'])->name('employees.import');
        Route::get('employees/templates/academic', [App\Http\Controllers\Admin\EmployeeController::class, 'downloadAcademicTemplate'])->name('employees.academic.template');
        Route::get('employees/templates/administrative', [App\Http\Controllers\Admin\EmployeeController::class, 'downloadAdministrativeTemplate'])->name('employees.administrative.template');
        Route::post('employees/import', [App\Http\Controllers\Admin\EmployeeController::class, 'upload'])->name('employees.upload');
        Route::post('employees/{employee}/reset-password', [App\Http\Controllers\Admin\EmployeeController::class, 'resetPassword'])->name('employees.reset-password');
        Route::post('employees/{employee}/toggle-status', [App\Http\Controllers\Admin\EmployeeController::class, 'toggleStatus'])->name('employees.toggle-status');
        
        // Document Management
        Route::get('employees/documents/{document}/download', [App\Http\Controllers\Admin\EmployeeController::class, 'downloadDocument'])->name('employees.documents.download');
        Route::get('employees/documents/{document}/delete', [App\Http\Controllers\Admin\EmployeeController::class, 'deleteDocument'])->name('employees.documents.delete');

        Route::resource('employees', App\Http\Controllers\Admin\EmployeeController::class);
        
        Route::resource('teacher-assignments', App\Http\Controllers\Admin\TeacherAssignmentController::class);

        // Parent Management
        Route::get('guardians/search', [App\Http\Controllers\Admin\GuardianController::class, 'search'])->name('guardians.search');
        Route::post('guardians/{guardian}/create-account', [App\Http\Controllers\Admin\GuardianController::class, 'createUser'])->name('guardians.create-account');
        Route::post('guardians/{guardian}/reset-password', [App\Http\Controllers\Admin\GuardianController::class, 'resetPassword'])->name('guardians.reset-password');
        Route::resource('guardians', App\Http\Controllers\Admin\GuardianController::class);

        Route::resource('students', App\Http\Controllers\Admin\StudentController::class);

        // Finance & Operations Portals
        Route::prefix('finance')->name('finance.')->group(function() {
            Route::get('fees', [App\Http\Controllers\Admin\FinanceController::class, 'fees'])->name('fees');
            Route::get('payroll', [App\Http\Controllers\Admin\FinanceController::class, 'payroll'])->name('payroll');
        });

        // Specialized Portals
        Route::prefix('portals')->name('portals.')->group(function() {
            Route::get('health', [App\Http\Controllers\Admin\PortalController::class, 'health'])->name('health');
            Route::get('inventory', [App\Http\Controllers\Admin\PortalController::class, 'inventory'])->name('inventory');
        });
    });

    // Teacher Portal Routes
    Route::middleware(['auth', 'role_or_permission:Teacher|enter marks', 'force.password.change'])->prefix('teacher')->name('teacher.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Teacher\DashboardController::class, 'index'])->name('dashboard');
        
        // My Classes
        Route::get('/classes', [App\Http\Controllers\Teacher\ClassController::class, 'index'])->name('classes.index');
        Route::get('/classes/{id}', [App\Http\Controllers\Teacher\ClassController::class, 'show'])->name('classes.show');

        // Gradebook
        Route::get('/gradebook/{assignment}', [\App\Http\Controllers\Teacher\GradebookController::class, 'entry'])->name('gradebook.entry');
        Route::get('/gradebook/{assignment}/export', [\App\Http\Controllers\Teacher\GradebookController::class, 'export'])->name('gradebook.export');
        Route::get('/gradebook/{assignment}/marksheet', [\App\Http\Controllers\Teacher\GradebookController::class, 'marksheet'])->name('gradebook.marksheet');
        Route::post('/gradebook/{assignment}/import', [\App\Http\Controllers\Teacher\GradebookController::class, 'import'])->name('gradebook.import');
        Route::post('/gradebook/{assignment}', [\App\Http\Controllers\Teacher\GradebookController::class, 'store'])->name('gradebook.store');

        // My Schedule
        Route::get('/schedule', [\App\Http\Controllers\Teacher\ScheduleController::class, 'index'])->name('schedule.index');

        // Homeroom Management
        Route::prefix('homeroom')->name('homeroom.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Teacher\HomeroomController::class, 'index'])->name('index');
            Route::get('/attendance', [\App\Http\Controllers\Teacher\HomeroomController::class, 'attendance'])->name('attendance');
            Route::post('/attendance', [\App\Http\Controllers\Teacher\HomeroomController::class, 'storeAttendance'])->name('attendance.store');
            Route::get('/behavior', [\App\Http\Controllers\Teacher\HomeroomController::class, 'behavior'])->name('behavior');
            Route::post('/behavior', [\App\Http\Controllers\Teacher\HomeroomController::class, 'storeBehavior'])->name('behavior.store');
        });

        // Department Head Oversight
        Route::prefix('department')->name('department.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Teacher\DepartmentController::class, 'index'])->name('index');
            Route::get('/{id}', [\App\Http\Controllers\Teacher\DepartmentController::class, 'show'])->name('show');
        });
    });
});

require __DIR__.'/auth.php';
