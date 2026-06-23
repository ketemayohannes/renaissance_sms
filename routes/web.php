<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;



Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Auth\RedirectController::class, 'index'])
        ->middleware(['verified'])
        ->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Mandatory Password Change
    Route::get('/auth/change-password', [App\Http\Controllers\Auth\PasswordChangeController::class, 'show'])->name('auth.change-password');
    Route::post('/auth/change-password', [App\Http\Controllers\Auth\PasswordChangeController::class, 'update'])->name('auth.change-password.update');

    // Student Routes
    Route::middleware(['role:Student'])->prefix('student')->name('student.')->group(function () {
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
    Route::middleware(['role:Super Admin|Principal|Vice Principal|Supervisor|IT / System Admin|Registrar|General Manager'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/search', [App\Http\Controllers\Admin\GlobalSearchController::class, 'search'])->name('global-search');
        
        // Role Management
        Route::resource('roles', App\Http\Controllers\Admin\RoleController::class)->middleware('permission:manage roles');
        
        // Audit Logs
        Route::get('audit-logs', [App\Http\Controllers\Admin\AuditLogController::class, 'index'])->name('audit-logs.index')->middleware('permission:manage roles');

        // Notice Board
        Route::resource('notices', App\Http\Controllers\Admin\NoticeController::class)->middleware('permission:manage notice board');

        // Messaging
        Route::get('messages', [App\Http\Controllers\Admin\MessagingController::class, 'index'])->name('messages.index');
        Route::get('messages/create', [App\Http\Controllers\Admin\MessagingController::class, 'create'])->name('messages.create');
        Route::post('messages', [App\Http\Controllers\Admin\MessagingController::class, 'store'])->name('messages.store');
        Route::get('messages/{conversation}', [App\Http\Controllers\Admin\MessagingController::class, 'show'])->name('messages.show');
        Route::post('messages/{conversation}/reply', [App\Http\Controllers\Admin\MessagingController::class, 'reply'])->name('messages.reply');
        Route::get('messages/unread-count', [App\Http\Controllers\Admin\MessagingController::class, 'unreadCount'])->name('messages.unread-count');
        
        // Academic Structure
        Route::resource('divisions', App\Http\Controllers\Admin\DivisionController::class)->except(['show'])->middleware('permission:manage divisions');
        Route::resource('grade-levels', App\Http\Controllers\Admin\GradeLevelController::class)->except(['show'])->middleware('permission:manage grade levels');
        // Section Import (permission-gated)
        Route::get('sections/import', [App\Http\Controllers\Admin\SectionController::class, 'import'])->name('sections.import')->middleware('permission:manage sections');
        Route::post('sections/import', [App\Http\Controllers\Admin\SectionController::class, 'upload'])->name('sections.upload')->middleware('permission:manage sections');
        Route::get('sections/download-template', [App\Http\Controllers\Admin\SectionController::class, 'downloadTemplate'])->name('sections.download-template')->middleware('permission:manage sections');
        // Bulk Create Sections (permission-gated)
        Route::get('sections/bulk-create', [App\Http\Controllers\Admin\SectionController::class, 'bulkCreate'])->name('sections.bulk-create')->middleware('permission:manage sections');
        Route::post('sections/bulk-create', [App\Http\Controllers\Admin\SectionController::class, 'bulkStore'])->name('sections.bulk-store')->middleware('permission:manage sections');

        Route::resource('sections', App\Http\Controllers\Admin\SectionController::class)->except(['show'])->middleware('permission:manage sections');
        
        // Subject Ordering (permission-gated)
        Route::get('subjects/reorder', [App\Http\Controllers\Admin\SubjectController::class, 'reorder'])->name('subjects.reorder')->middleware('permission:manage subjects');
        Route::post('subjects/reorder', [App\Http\Controllers\Admin\SubjectController::class, 'updateOrder'])->name('subjects.update-order')->middleware('permission:manage subjects');
        
        // Subject Import (permission-gated)
        Route::get('subjects/import', [App\Http\Controllers\Admin\SubjectController::class, 'import'])->name('subjects.import')->middleware('permission:manage subjects');
        Route::post('subjects/upload', [App\Http\Controllers\Admin\SubjectController::class, 'upload'])->name('subjects.upload')->middleware('permission:manage subjects');
        Route::get('subjects/template', [App\Http\Controllers\Admin\SubjectController::class, 'downloadTemplate'])->name('subjects.template')->middleware('permission:manage subjects');
        
        Route::resource('subjects', App\Http\Controllers\Admin\SubjectController::class)->except(['show'])->middleware('permission:manage subjects');
        
        // Subject Assignment (permission-gated)
        Route::get('subject-assignments/bulk-assign', [App\Http\Controllers\Admin\SubjectAssignmentController::class, 'bulkAssignForm'])->name('subject-assignments.bulk-assign')->middleware('permission:manage subjects');
        Route::post('subject-assignments/bulk-assign', [App\Http\Controllers\Admin\SubjectAssignmentController::class, 'storeBulkAssign'])->name('subject-assignments.bulk-assign.store')->middleware('permission:manage subjects');
        
        Route::get('subject-assignments', [App\Http\Controllers\Admin\SubjectAssignmentController::class, 'index'])->name('subject-assignments.index')->middleware('permission:manage subjects');
        

        Route::get('subject-assignments/{grade_level}/edit', [App\Http\Controllers\Admin\SubjectAssignmentController::class, 'edit'])->name('subject-assignments.edit')->middleware('permission:manage subjects');
        Route::put('subject-assignments/{grade_level}', [App\Http\Controllers\Admin\SubjectAssignmentController::class, 'update'])->name('subject-assignments.update')->middleware('permission:manage subjects');

        Route::resource('academic-years', App\Http\Controllers\Admin\AcademicYearController::class)->except(['show'])->middleware('permission:manage grade levels');
        Route::resource('terms', App\Http\Controllers\Admin\TermController::class)->except(['show'])->middleware('permission:manage grade levels');
        Route::get('terms/quarters/{academicYear}', [App\Http\Controllers\Admin\TermController::class, 'getQuarters'])->name('terms.get-quarters');

        
        // Student Management (permission-gated)
        Route::get('students/export', [App\Http\Controllers\Admin\StudentController::class, 'export'])->name('students.export')->middleware('permission:view students');
        Route::get('students/import', [App\Http\Controllers\Admin\StudentController::class, 'import'])->name('students.import')->middleware('permission:create students');
        Route::post('students/import', [App\Http\Controllers\Admin\StudentController::class, 'upload'])->name('students.upload')->middleware('permission:create students');
        Route::get('students/download-template', [App\Http\Controllers\Admin\StudentController::class, 'downloadTemplate'])->name('students.download-template')->middleware('permission:create students');
        Route::post('students/{student}/toggle-block', [App\Http\Controllers\Admin\StudentController::class, 'toggleBlock'])->name('students.toggle-block')->middleware('permission:edit students');
        Route::get('students/{student}/transfer', [App\Http\Controllers\Admin\StudentController::class, 'transferForm'])->name('students.transfer')->middleware('permission:edit students');
        Route::post('students/{student}/transfer', [App\Http\Controllers\Admin\StudentController::class, 'transfer'])->name('students.transfer.store')->middleware('permission:edit students');
        Route::get('students/{student}/assign-electives', [App\Http\Controllers\Admin\StudentController::class, 'assignElectivesForm'])->name('students.assign-electives')->middleware('permission:edit students');
        Route::post('students/{student}/assign-electives', [App\Http\Controllers\Admin\StudentController::class, 'storeElectives'])->name('students.assign-electives.store')->middleware('permission:edit students');
        Route::post('students/{student}/siblings', [App\Http\Controllers\Admin\StudentController::class, 'linkSibling'])->name('students.siblings.link')->middleware('permission:edit students');
        Route::delete('students/{student}/siblings/{sibling}', [App\Http\Controllers\Admin\StudentController::class, 'unlinkSibling'])->name('students.siblings.unlink')->middleware('permission:edit students');
        Route::post('students/bulk-destroy', [App\Http\Controllers\Admin\StudentController::class, 'bulkDestroy'])->name('students.bulk-destroy')->middleware('permission:delete students');
        Route::post('students/bulk-deactivate', [App\Http\Controllers\Admin\StudentController::class, 'bulkDeactivate'])->name('students.bulk-deactivate')->middleware('permission:edit students');
        Route::post('students/bulk-transfer', [App\Http\Controllers\Admin\StudentController::class, 'bulkTransfer'])->name('students.bulk-transfer')->middleware('permission:edit students');
        Route::get('students/{student}/withdraw', [App\Http\Controllers\Admin\StudentController::class, 'withdrawForm'])->name('students.withdraw')->middleware('permission:edit students');
        Route::post('students/{student}/withdraw', [App\Http\Controllers\Admin\StudentController::class, 'processWithdrawal'])->name('students.withdraw.store')->middleware('permission:edit students');
        Route::get('students/{student}/status-history', [App\Http\Controllers\Admin\StudentController::class, 'statusHistory'])->name('students.status-history')->middleware('permission:view students');
        Route::get('id-cards', [App\Http\Controllers\Admin\StudentController::class, 'idCardsIndex'])->name('id-cards.index')->middleware('permission:view students');
        
        // Student Document Routes (permission-gated)
        Route::post('students/{student}/document', [App\Http\Controllers\Admin\StudentController::class, 'storeDocument'])->name('students.store-document')->middleware('permission:edit students');
        Route::delete('students/{student}/document/{document}', [App\Http\Controllers\Admin\StudentController::class, 'deleteDocument'])->name('students.delete-document')->middleware('permission:edit students');
        Route::post('students/{id}/restore', [App\Http\Controllers\Admin\StudentController::class, 'restore'])->name('students.restore')->middleware('permission:edit students');
        Route::post('students/bulk-id-cards', [App\Http\Controllers\Admin\StudentController::class, 'bulkIdCardsSelected'])->name('students.bulk-id-cards-selected')->middleware('permission:view students');
        
        // Electives Bulk Assign (permission-gated)
        Route::get('electives/bulk-assign', [App\Http\Controllers\Admin\ElectiveController::class, 'bulkAssignForm'])->name('electives.bulk-assign')->middleware('permission:edit students');
        Route::post('electives/bulk-assign', [App\Http\Controllers\Admin\ElectiveController::class, 'storeBulkAssign'])->name('electives.bulk-assign.store')->middleware('permission:edit students');
        Route::get('electives/subjects', [App\Http\Controllers\Admin\ElectiveController::class, 'getSubjects'])->name('electives.get-subjects')->middleware('permission:edit students');
        Route::get('electives/sections', [App\Http\Controllers\Admin\ElectiveController::class, 'getSections'])->name('electives.get-sections')->middleware('permission:edit students');
        Route::get('electives/students', [App\Http\Controllers\Admin\ElectiveController::class, 'getStudents'])->name('electives.get-students')->middleware('permission:edit students');

        // Gradebook Routes (permission-gated)
        Route::get('gradebook/export-template', [App\Http\Controllers\Admin\GradebookController::class, 'exportTemplate'])->name('gradebook.export-template')->middleware('permission:view subject gradebook');
        Route::post('gradebook/import', [App\Http\Controllers\Admin\GradebookController::class, 'import'])->name('gradebook.import')->middleware('permission:edit subject gradebook');
        Route::get('gradebook', [App\Http\Controllers\Admin\GradebookController::class, 'index'])->name('gradebook.index')->middleware('permission:view subject gradebook');
        Route::get('gradebook/entry', [App\Http\Controllers\Admin\GradebookController::class, 'entry'])->name('gradebook.entry')->middleware('permission:view subject gradebook');
        Route::get('gradebook/marksheet', [App\Http\Controllers\Admin\GradebookController::class, 'marksheet'])->name('gradebook.marksheet')->middleware('permission:view subject gradebook');
        Route::post('gradebook/store', [App\Http\Controllers\Admin\GradebookController::class, 'store'])->name('gradebook.store')->middleware('permission:edit subject gradebook');

        // Section Grade Entry Routes (permission-gated)
        Route::get('section-grades', [App\Http\Controllers\Admin\SectionGradeController::class, 'index'])->name('section-grades.index')->middleware('permission:view master gradebook');
        Route::get('section-grades/entry', [App\Http\Controllers\Admin\SectionGradeController::class, 'entry'])->name('section-grades.entry')->middleware('permission:view master gradebook');
        Route::post('section-grades/store', [App\Http\Controllers\Admin\SectionGradeController::class, 'store'])->name('section-grades.store')->middleware('permission:edit master gradebook');
        Route::get('section-grades/export', [App\Http\Controllers\Admin\SectionGradeController::class, 'export'])->name('section-grades.export')->middleware('permission:view master gradebook');
        Route::get('section-grades/export-data', [App\Http\Controllers\Admin\SectionGradeController::class, 'exportData'])->name('section-grades.export-data')->middleware('permission:view master gradebook');
        Route::get('section-grades/export-low-performance', [App\Http\Controllers\Admin\SectionGradeController::class, 'exportLowPerformance'])->name('section-grades.export-low-performance')->middleware('permission:view master gradebook');
        Route::get('section-grades/download-low-performance-report', [App\Http\Controllers\Admin\SectionGradeController::class, 'downloadLowPerformanceReport'])->name('section-grades.download-low-performance-report')->middleware('permission:view master gradebook');
        Route::post('section-grades/import', [App\Http\Controllers\Admin\SectionGradeController::class, 'import'])->name('section-grades.import')->middleware('permission:edit master gradebook');
        Route::post('section-grades/calculate', [App\Http\Controllers\Admin\SectionGradeController::class, 'calculateSemester'])->name('section-grades.calculate')->middleware('permission:edit master gradebook');
        
        // Assessment System Routes (permission-gated)
        Route::resource('assessment-types', App\Http\Controllers\Admin\AssessmentTypeController::class)->middleware('permission:configure assessments');
        Route::post('assessment-templates/bulk-destroy', [App\Http\Controllers\Admin\AssessmentTemplateController::class, 'bulkDestroy'])->name('assessment-templates.bulk-destroy')->middleware('permission:configure assessments');
        Route::post('assessment-templates/destroy-by-term', [App\Http\Controllers\Admin\AssessmentTemplateController::class, 'destroyByTerm'])->name('assessment-templates.destroy-by-term')->middleware('permission:configure assessments');
        Route::get('assessment-templates/reorder', [App\Http\Controllers\Admin\AssessmentTemplateController::class, 'reorder'])->name('assessment-templates.reorder')->middleware('permission:configure assessments');
        Route::post('assessment-templates/reorder', [App\Http\Controllers\Admin\AssessmentTemplateController::class, 'updateOrder'])->name('assessment-templates.update-order')->middleware('permission:configure assessments');
        Route::resource('assessment-templates', App\Http\Controllers\Admin\AssessmentTemplateController::class)->middleware('permission:configure assessments');
        Route::resource('grade-components', App\Http\Controllers\Admin\GradeComponentController::class)->middleware('permission:configure assessments');
        Route::get('grade-components/get-components', [App\Http\Controllers\Admin\GradeComponentController::class, 'getComponents'])->name('grade-components.get-components')->middleware('permission:configure assessments');

        // ID Card Settings (permission-gated)
        Route::get('id-card-settings', [App\Http\Controllers\Admin\IdCardSettingController::class, 'index'])->name('id-card-settings.index')->middleware('permission:manage roles');
        Route::put('id-card-settings', [App\Http\Controllers\Admin\IdCardSettingController::class, 'update'])->name('id-card-settings.update')->middleware('permission:manage roles');

        // Communication Settings (role-gated to Super Admin)
        Route::get('settings/communication', [App\Http\Controllers\Admin\CommunicationSettingController::class, 'index'])->name('settings.communication.index')->middleware('role:Super Admin');
        Route::post('settings/communication', [App\Http\Controllers\Admin\CommunicationSettingController::class, 'update'])->name('settings.communication.update')->middleware('role:Super Admin');
        Route::post('settings/communication/test-sms', [App\Http\Controllers\Admin\CommunicationSettingController::class, 'testSms'])->name('settings.communication.test-sms')->middleware('role:Super Admin');
        Route::post('settings/communication/test-email', [App\Http\Controllers\Admin\CommunicationSettingController::class, 'testEmail'])->name('settings.communication.test-email')->middleware('role:Super Admin');


        // Gradebook AJAX Routes (permission-gated)
        Route::get('gradebook/get-sections', [App\Http\Controllers\Admin\GradebookController::class, 'getSections'])->name('gradebook.get-sections')->middleware('permission:view subject gradebook');
        Route::get('gradebook/get-subjects', [App\Http\Controllers\Admin\GradebookController::class, 'getSubjects'])->name('gradebook.get-subjects')->middleware('permission:view subject gradebook');
        Route::get('gradebook/get-terms', [App\Http\Controllers\Admin\GradebookController::class, 'getTerms'])->name('gradebook.get-terms')->middleware('permission:view subject gradebook');

        // Report Cards (permission-gated)
        Route::get('report-cards/settings', [App\Http\Controllers\Admin\ReportCardController::class, 'settings'])->name('report-cards.settings')->middleware('permission:generate report cards');
        Route::post('report-cards/settings', [App\Http\Controllers\Admin\ReportCardController::class, 'updateSettings'])->name('report-cards.update-settings')->middleware('permission:generate report cards');
        Route::get('report-cards/yearly-settings', [App\Http\Controllers\Admin\ReportCardController::class, 'yearlySettings'])->name('report-cards.yearly-settings')->middleware('permission:generate report cards');
        Route::post('report-cards/yearly-settings', [App\Http\Controllers\Admin\ReportCardController::class, 'updateYearlySettings'])->name('report-cards.update-yearly-settings')->middleware('permission:generate report cards');

        // Academic Reports (permission-gated)
        Route::get('academic-reports/settings', [App\Http\Controllers\Admin\AcademicReportController::class, 'settings'])->name('academic-reports.settings')->middleware('permission:view marks');
        Route::post('academic-reports/settings', [App\Http\Controllers\Admin\AcademicReportController::class, 'updateSettings'])->name('academic-reports.settings.update')->middleware('permission:view marks');
        Route::get('academic-reports', [App\Http\Controllers\Admin\AcademicReportController::class, 'index'])->name('academic-reports.index')->middleware('permission:view marks');
        Route::get('academic-reports/show', [App\Http\Controllers\Admin\AcademicReportController::class, 'show'])->name('academic-reports.show')->middleware('permission:view marks');
        Route::get('academic-reports/subject-analysis', [App\Http\Controllers\Admin\AcademicReportController::class, 'subjectAnalysis'])->name('academic-reports.subject-analysis')->middleware('permission:view marks');
        Route::get('academic-reports/grade-matrix', [App\Http\Controllers\Admin\AcademicReportController::class, 'gradeMatrix'])->name('academic-reports.grade-matrix')->middleware('permission:view marks');
        Route::get('academic-reports/grade-matrix/pdf', [App\Http\Controllers\Admin\AcademicReportController::class, 'gradeMatrixPdf'])->name('academic-reports.grade-matrix.pdf')->middleware('permission:view marks');
        Route::get('academic-reports/matrix-reorder', [App\Http\Controllers\Admin\AcademicReportController::class, 'matrixReorder'])->name('academic-reports.matrix-reorder')->middleware('permission:view marks');
        Route::post('academic-reports/matrix-reorder', [App\Http\Controllers\Admin\AcademicReportController::class, 'updateMatrixOrder'])->name('academic-reports.matrix-reorder.update')->middleware('permission:view marks');
        Route::post('academic-reports/recalculate', [App\Http\Controllers\Admin\AcademicReportController::class, 'recalculate'])->name('academic-reports.recalculate')->middleware('permission:view marks');
        
        // Result Analysis (permission-gated)
        Route::get('result-analysis', [App\Http\Controllers\Admin\ResultAnalysisController::class, 'index'])->name('result-analysis.index')->middleware('permission:view marks');
        Route::get('result-analysis/{assignment}', [App\Http\Controllers\Admin\ResultAnalysisController::class, 'show'])->name('result-analysis.show')->middleware('permission:view marks');

        Route::get('section-grades/{section}/report-card-details', [App\Http\Controllers\Admin\ReportCardController::class, 'entry'])->name('section-grades.report-card-entry')->middleware('permission:generate report cards');
        Route::post('section-grades/{section}/report-card-details', [App\Http\Controllers\Admin\ReportCardController::class, 'storeEntry'])->name('section-grades.store-report-card-entry')->middleware('permission:generate report cards');
        
        Route::get('section-grades/{section}/report-card/bulk-print', [App\Http\Controllers\Admin\ReportCardController::class, 'bulkPrint'])->name('section-grades.bulk-print-report-cards')->middleware('permission:generate report cards');
        
        Route::get('students/{student}/report-card/pdf', [App\Http\Controllers\Admin\ReportCardController::class, 'generatePdf'])->name('report-cards.pdf')->middleware('permission:generate report cards');

        // Background Exports (permission-gated)
        Route::get('report-cards/exports', [App\Http\Controllers\Admin\ReportCardController::class, 'exports'])->name('report-cards.exports')->middleware('permission:generate report cards');
        Route::get('report-cards/export-low-performance', [App\Http\Controllers\Admin\ReportCardController::class, 'exportLowPerformance'])->name('report-cards.export-low-performance')->middleware('permission:generate report cards');
        Route::get('section-grades/{section}/report-card/bulk-export', [App\Http\Controllers\Admin\ReportCardController::class, 'bulkExport'])->name('section-grades.bulk-export-report-cards')->middleware('permission:generate report cards');
        Route::get('report-cards/exports/{exportRequest}/download', [App\Http\Controllers\Admin\ReportCardController::class, 'downloadExport'])->name('report-cards.download-export')->middleware('permission:generate report cards');
        Route::delete('report-cards/exports/{exportRequest}', [App\Http\Controllers\Admin\ReportCardController::class, 'destroyExport'])->name('report-cards.destroy-export')->middleware('permission:generate report cards');

        // Academic Activities Module (permission-gated)
        Route::get('activities/get-templates', [App\Http\Controllers\Admin\AcademicActivityController::class, 'getTemplates'])->name('activities.get-templates')->middleware('permission:enter marks');
        Route::get('activities/{activity}/evaluate', [App\Http\Controllers\Admin\AcademicActivityController::class, 'evaluate'])->name('activities.evaluate')->middleware('permission:enter marks');
        Route::post('activities/{activity}/evaluate', [App\Http\Controllers\Admin\AcademicActivityController::class, 'storeEvaluation'])->name('activities.evaluate.store')->middleware('permission:enter marks');
        Route::get('activities/{activity}/questions', [App\Http\Controllers\Admin\AcademicActivityController::class, 'manageQuestions'])->name('activities.questions')->middleware('permission:enter marks');
        Route::post('activities/{activity}/questions', [App\Http\Controllers\Admin\AcademicActivityController::class, 'storeQuestions'])->name('activities.questions.store')->middleware('permission:enter marks');
        Route::resource('activities', App\Http\Controllers\Admin\AcademicActivityController::class)->middleware('permission:enter marks');

        // Attendance Management (permission-gated)
        Route::get('attendance', [App\Http\Controllers\Admin\AttendanceController::class, 'index'])->name('attendance.index')->middleware('permission:manage attendance');
        Route::get('attendance/register', [App\Http\Controllers\Admin\AttendanceController::class, 'register'])->name('attendance.register')->middleware('permission:manage attendance');
        Route::post('attendance/store', [App\Http\Controllers\Admin\AttendanceController::class, 'store'])->name('attendance.store')->middleware('permission:manage attendance');
        Route::get('attendance/report', [App\Http\Controllers\Admin\AttendanceController::class, 'report'])->name('attendance.report')->middleware('permission:manage attendance');
        
        // Timetable Management (permission-gated)
        Route::get('timetable', [App\Http\Controllers\Admin\TimetableController::class, 'index'])->name('timetable.index')->middleware('permission:manage sections');
        Route::get('timetable/builder', [App\Http\Controllers\Admin\TimetableController::class, 'builder'])->name('timetable.builder')->middleware('permission:manage sections');
        Route::post('timetable/store', [App\Http\Controllers\Admin\TimetableController::class, 'store'])->name('timetable.store')->middleware('permission:manage sections');

        // Promotion Management (permission-gated)
        Route::get('promotions', [App\Http\Controllers\Admin\PromotionController::class, 'index'])->name('promotions.index')->middleware('permission:promote students');
        Route::post('promotions/rules', [App\Http\Controllers\Admin\PromotionController::class, 'storeRule'])->name('promotions.store-rule')->middleware('permission:promote students');
        Route::delete('promotions/rules/{promotionRule}', [App\Http\Controllers\Admin\PromotionController::class, 'deleteRule'])->name('promotions.delete-rule')->middleware('permission:promote students');
        Route::get('promotions/process', [App\Http\Controllers\Admin\PromotionController::class, 'processForm'])->name('promotions.process')->middleware('permission:promote students');
        Route::post('promotions/preview', [App\Http\Controllers\Admin\PromotionController::class, 'preview'])->name('promotions.preview')->middleware('permission:promote students');
        Route::post('promotions/execute', [App\Http\Controllers\Admin\PromotionController::class, 'execute'])->name('promotions.execute')->middleware('permission:promote students');
        Route::get('promotions/history', [App\Http\Controllers\Admin\PromotionController::class, 'history'])->name('promotions.history')->middleware('permission:promote students');
        Route::post('promotions/history/{studentPromotion}/enroll', [App\Http\Controllers\Admin\PromotionController::class, 'enrollStudent'])->name('promotions.enroll')->middleware('permission:promote students');
        Route::post('promotions/history/{studentPromotion}/reverse', [App\Http\Controllers\Admin\PromotionController::class, 'reversePromotion'])->name('promotions.reverse')->middleware('permission:promote students');
        Route::post('promotions/history/bulk-enroll', [App\Http\Controllers\Admin\PromotionController::class, 'bulkEnroll'])->name('promotions.bulk-enroll')->middleware('permission:promote students');
        Route::post('promotions/history/bulk-reverse', [App\Http\Controllers\Admin\PromotionController::class, 'bulkReverse'])->name('promotions.bulk-reverse')->middleware('permission:promote students');

        // Disciplinary Records (permission-gated)
        Route::get('disciplinary', [App\Http\Controllers\Admin\DisciplinaryController::class, 'index'])->name('disciplinary.index')->middleware('permission:view students');
        Route::get('disciplinary/create/{student?}', [App\Http\Controllers\Admin\DisciplinaryController::class, 'create'])->name('disciplinary.create')->middleware('permission:edit students');
        Route::post('disciplinary', [App\Http\Controllers\Admin\DisciplinaryController::class, 'store'])->name('disciplinary.store')->middleware('permission:edit students');
        Route::get('disciplinary/{disciplinary}', [App\Http\Controllers\Admin\DisciplinaryController::class, 'show'])->name('disciplinary.show')->middleware('permission:view students');
        Route::put('disciplinary/{disciplinary}', [App\Http\Controllers\Admin\DisciplinaryController::class, 'update'])->name('disciplinary.update')->middleware('permission:edit students');
        Route::get('students/{student}/disciplinary', [App\Http\Controllers\Admin\DisciplinaryController::class, 'studentRecords'])->name('students.disciplinary')->middleware('permission:view students');

        // Discipline Settings (permission-gated)
        Route::get('discipline-settings', [App\Http\Controllers\Admin\InfractionDefinitionController::class, 'index'])->name('discipline-settings.index')->middleware('permission:edit students');
        Route::post('discipline-settings/infractions', [App\Http\Controllers\Admin\InfractionDefinitionController::class, 'store'])->name('discipline-settings.infractions.store')->middleware('permission:edit students');
        Route::put('discipline-settings/infractions/{definition}', [App\Http\Controllers\Admin\InfractionDefinitionController::class, 'update'])->name('discipline-settings.infractions.update')->middleware('permission:edit students');
        Route::post('discipline-settings/infractions/{definition}/toggle', [App\Http\Controllers\Admin\InfractionDefinitionController::class, 'toggleActive'])->name('discipline-settings.infractions.toggle')->middleware('permission:edit students');
        Route::post('discipline-settings/rules', [App\Http\Controllers\Admin\EscalationRuleController::class, 'store'])->name('discipline-settings.rules.store')->middleware('permission:edit students');
        Route::put('discipline-settings/rules/{rule}', [App\Http\Controllers\Admin\EscalationRuleController::class, 'update'])->name('discipline-settings.rules.update')->middleware('permission:edit students');
        Route::post('discipline-settings/rules/{rule}/toggle', [App\Http\Controllers\Admin\EscalationRuleController::class, 'toggleActive'])->name('discipline-settings.rules.toggle')->middleware('permission:edit students');

        // Student ID Cards (permission-gated)
        Route::get('students/{student}/id-card', [App\Http\Controllers\Admin\StudentController::class, 'generateIdCard'])->name('students.id-card')->middleware('permission:view students');
        Route::get('sections/{section}/id-cards', [App\Http\Controllers\Admin\StudentController::class, 'bulkIdCards'])->name('sections.bulk-id-cards')->middleware('permission:view students');
        Route::post('guardians/{guardian}/create-user', [App\Http\Controllers\Admin\StudentController::class, 'createGuardianUser'])->name('guardians.create-user')->middleware('permission:edit students');
        Route::post('students/{student}/create-user', [App\Http\Controllers\Admin\StudentController::class, 'createStudentUser'])->name('students.create-user')->middleware('permission:edit students');
        Route::post('students/{student}/reset-password', [App\Http\Controllers\Admin\StudentController::class, 'resetStudentPassword'])->name('students.reset-password')->middleware('permission:edit students');

        // Human Resources (permission-gated)
        Route::get('employees/import', [App\Http\Controllers\Admin\EmployeeController::class, 'import'])->name('employees.import')->middleware('permission:manage employees');
        Route::get('employees/templates/academic', [App\Http\Controllers\Admin\EmployeeController::class, 'downloadAcademicTemplate'])->name('employees.academic.template')->middleware('permission:manage employees');
        Route::get('employees/templates/administrative', [App\Http\Controllers\Admin\EmployeeController::class, 'downloadAdministrativeTemplate'])->name('employees.administrative.template')->middleware('permission:manage employees');
        Route::post('employees/import', [App\Http\Controllers\Admin\EmployeeController::class, 'upload'])->name('employees.upload')->middleware('permission:manage employees');
        Route::post('employees/{employee}/reset-password', [App\Http\Controllers\Admin\EmployeeController::class, 'resetPassword'])->name('employees.reset-password')->middleware('permission:manage employees');
        Route::post('employees/{employee}/toggle-status', [App\Http\Controllers\Admin\EmployeeController::class, 'toggleStatus'])->name('employees.toggle-status')->middleware('permission:manage employees');
        
        // Document Management
        Route::get('employees/documents/{document}/download', [App\Http\Controllers\Admin\EmployeeController::class, 'downloadDocument'])->name('employees.documents.download')->middleware('permission:view employees');
        Route::get('employees/documents/{document}/delete', [App\Http\Controllers\Admin\EmployeeController::class, 'deleteDocument'])->name('employees.documents.delete')->middleware('permission:manage employees');

        Route::resource('employees', App\Http\Controllers\Admin\EmployeeController::class)->middleware('permission:view employees');
        
        Route::resource('teacher-assignments', App\Http\Controllers\Admin\TeacherAssignmentController::class)->middleware('permission:manage employees');

        // Parent Management (permission-gated)
        Route::get('guardians/search', [App\Http\Controllers\Admin\GuardianController::class, 'search'])->name('guardians.search')->middleware('permission:view students');
        Route::post('guardians/{guardian}/create-account', [App\Http\Controllers\Admin\GuardianController::class, 'createUser'])->name('guardians.create-account')->middleware('permission:edit students');
        Route::post('guardians/{guardian}/reset-password', [App\Http\Controllers\Admin\GuardianController::class, 'resetPassword'])->name('guardians.reset-password')->middleware('permission:edit students');
        Route::resource('guardians', App\Http\Controllers\Admin\GuardianController::class)->middleware('permission:view students');

        Route::patch('students/{student}/quick-update', [App\Http\Controllers\Admin\StudentController::class, 'quickUpdate'])->name('students.quick-update')->middleware('permission:edit students');
        Route::resource('students', App\Http\Controllers\Admin\StudentController::class)->middleware('permission:view students');

        // Finance & Operations Portals (permission-gated)
        Route::prefix('finance')->name('finance.')->group(function() {
            Route::get('fees', [App\Http\Controllers\Admin\FinanceController::class, 'fees'])->name('fees')->middleware('permission:view fees');
            Route::get('payroll', [App\Http\Controllers\Admin\FinanceController::class, 'payroll'])->name('payroll')->middleware('permission:view payroll');
        });

        // Exam Paper Review (permission-gated)
        Route::get('exams', [App\Http\Controllers\Admin\ExamReviewController::class, 'index'])->name('exams.index')->middleware('permission:view marks');
        Route::get('exams/{exam}', [App\Http\Controllers\Admin\ExamReviewController::class, 'show'])->name('exams.show')->middleware('permission:view marks');
        Route::post('exams/{exam}/review', [App\Http\Controllers\Admin\ExamReviewController::class, 'review'])->name('exams.review')->middleware('permission:publish results');
        Route::delete('exams/{exam}', [App\Http\Controllers\Admin\ExamReviewController::class, 'destroy'])->name('exams.destroy')->middleware('permission:publish results');
        Route::get('exams/{exam}/download', [App\Http\Controllers\Teacher\ExamPaperController::class, 'download'])->name('exams.download')->middleware('permission:view marks');

        // Specialized Portals
        Route::prefix('portals')->name('portals.')->group(function() {
            Route::get('health', [App\Http\Controllers\Admin\PortalController::class, 'health'])->name('health');
            Route::get('inventory', [App\Http\Controllers\Admin\PortalController::class, 'inventory'])->name('inventory');
        });
    });

        // Parent Portal Routes
    Route::middleware(['auth', 'role:Parent'])->prefix('parent')->name('parent.')->group(function () {
        // Dashboard
        Route::get('/dashboard', [App\Http\Controllers\Parent\DashboardController::class, 'index'])->name('dashboard');

        // Settings & General Communications
        Route::get('/profile', [App\Http\Controllers\Parent\ProfileController::class, 'show'])->name('profile');
        Route::patch('/preferences', [App\Http\Controllers\Parent\ProfileController::class, 'updatePreferences'])->name('preferences.update');
        Route::put('/password', [App\Http\Controllers\Parent\ProfileController::class, 'updatePassword'])->name('password.update');
        Route::get('/notices', [App\Http\Controllers\Parent\CommunicationController::class, 'notices'])->name('notices.index');
        Route::get('/notices/{notice}', [App\Http\Controllers\Parent\CommunicationController::class, 'showNotice'])->name('notices.show');

        // Messaging (replaces old contact form — redirect kept for backwards compat)
        Route::get('/messages', [App\Http\Controllers\Parent\MessagingController::class, 'index'])->name('messages.index');
        Route::get('/messages/create', [App\Http\Controllers\Parent\MessagingController::class, 'create'])->name('messages.create');
        Route::post('/messages', [App\Http\Controllers\Parent\MessagingController::class, 'store'])->name('messages.store');
        Route::get('/messages/{conversation}', [App\Http\Controllers\Parent\MessagingController::class, 'show'])->name('messages.show');
        Route::post('/messages/{conversation}/reply', [App\Http\Controllers\Parent\MessagingController::class, 'reply'])->name('messages.reply');
        Route::get('/messages/unread-count', [App\Http\Controllers\Parent\MessagingController::class, 'unreadCount'])->name('messages.unread-count');
        // Legacy contact routes — redirect to new messaging UI
        Route::get('/contact-teacher', fn() => redirect()->route('parent.messages.create'))->name('contact.form');
        Route::post('/contact-teacher', fn() => redirect()->route('parent.messages.create'))->name('contact.send');
        Route::get('/search', function (Illuminate\Http\Request $request) {
            $q = $request->query('q', '');
            if (strlen($q) < 2) {
                return response()->json([]);
            }
            $user = auth()->user();
            $results = [];
            foreach ($user->linked_students as $child) {
                if (stripos($child->full_name, $q) !== false || stripos($child->student_id, $q) !== false) {
                    $results[] = [
                        'type' => 'Student',
                        'title' => $child->full_name,
                        'subtitle' => 'Grade ' . ($child->gradeLevel->name ?? 'N/A') . ' • Section ' . ($child->section->name ?? 'N/A'),
                        'url' => route('parent.student.dashboard', $child),
                    ];
                }
            }
            return response()->json($results);
        })->name('search');

        // Child‑scoped routes (protected by custom middleware)
        Route::middleware(['parent_access'])->prefix('student/{student}')->name('student.')->group(function () {
            Route::get('/dashboard', [App\Http\Controllers\Parent\ChildPortalController::class, 'dashboard'])->name('dashboard');
            Route::get('/grades', [App\Http\Controllers\Parent\ChildPortalController::class, 'grades'])->name('grades.index');
            Route::get('/grades/download', [App\Http\Controllers\Parent\ChildPortalController::class, 'downloadReport'])->name('grades.download');
            Route::get('/attendance', [App\Http\Controllers\Parent\ChildPortalController::class, 'attendance'])->name('attendance.index');
            Route::get('/conduct', [App\Http\Controllers\Parent\ChildPortalController::class, 'conduct'])->name('conduct.index');
            Route::get('/info', [App\Http\Controllers\Parent\ChildPortalController::class, 'info'])->name('info.show');
        });
    });
    // Teacher Portal Routes
    Route::middleware(['auth', 'role:Teacher|Assistant Teacher'])->prefix('teacher')->name('teacher.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Teacher\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/chart-data', [App\Http\Controllers\Teacher\DashboardController::class, 'getChartData'])->name('dashboard.chart-data');
        Route::get('/dashboard/efficiency-data', [App\Http\Controllers\Teacher\DashboardController::class, 'getEfficiencyData'])->name('dashboard.efficiency-data');

        // Notices
        Route::get('/notices', [App\Http\Controllers\Teacher\NoticeController::class, 'index'])->name('notices.index');
        Route::get('/notices/{notice}', [App\Http\Controllers\Teacher\NoticeController::class, 'show'])->name('notices.show');

        // Messaging
        Route::get('/messages', [App\Http\Controllers\Teacher\MessagingController::class, 'index'])->name('messages.index');
        Route::get('/messages/{conversation}', [App\Http\Controllers\Teacher\MessagingController::class, 'show'])->name('messages.show');
        Route::post('/messages/{conversation}/reply', [App\Http\Controllers\Teacher\MessagingController::class, 'reply'])->name('messages.reply');
        Route::get('/messages/unread-count', [App\Http\Controllers\Teacher\MessagingController::class, 'unreadCount'])->name('messages.unread-count');

        Route::resource('exams', App\Http\Controllers\Teacher\ExamPaperController::class);
        Route::get('exams/{exam}/download', [App\Http\Controllers\Teacher\ExamPaperController::class, 'download'])->name('exams.download');

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

        // Reports
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/result-analysis', [App\Http\Controllers\Teacher\ResultAnalysisController::class, 'index'])->name('result-analysis');
            Route::get('/result-analysis/{assignment}', [App\Http\Controllers\Teacher\ResultAnalysisController::class, 'show'])->name('result-analysis.show');
            Route::post('/result-analysis/{assignment}', [App\Http\Controllers\Teacher\ResultAnalysisController::class, 'store'])->name('result-analysis.store');
            Route::get('/result-analysis/{assignment}/print', [App\Http\Controllers\Teacher\ResultAnalysisController::class, 'print'])->name('result-analysis.print');
        });
    });
});

require __DIR__.'/auth.php';

// ─── Shared: Notification API (all authenticated users) ─────────────────────
Route::middleware('auth')->prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/',            [App\Http\Controllers\NotificationController::class, 'index'])->name('index');
    Route::get('/count',       [App\Http\Controllers\NotificationController::class, 'count'])->name('count');
    Route::get('/latest',      [App\Http\Controllers\NotificationController::class, 'latest'])->name('latest');
    Route::post('/mark-all',   [App\Http\Controllers\NotificationController::class, 'markAllRead'])->name('mark-all');
    Route::delete('/clear-all', [App\Http\Controllers\NotificationController::class, 'clearAll'])->name('clear-all');
    Route::get('/{id}/read',   [App\Http\Controllers\NotificationController::class, 'markRead'])->name('read');
});

// ─── Shared: Conversation API (all authenticated users) ─────────────────────
Route::middleware('auth')->prefix('conversations')->name('conversations.')->group(function () {
    Route::get('/{conversation}/messages', [App\Http\Controllers\NotificationController::class, 'fetchMessages'])->name('messages');
    Route::post('/{conversation}/reply',   [App\Http\Controllers\NotificationController::class, 'ajaxReply'])->name('reply');
});
