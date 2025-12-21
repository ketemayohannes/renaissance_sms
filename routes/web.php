<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;



Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    $user = auth()->user();
    
    if ($user->hasRole(['Super Admin', 'Principal'])) {
        return redirect()->route('admin.dashboard');
    }
    
    // For other roles, show default dashboard for now
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin Routes
    Route::middleware(['role:Super Admin|Principal'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
        
        // Role Management
        Route::resource('roles', App\Http\Controllers\Admin\RoleController::class);
        
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
        Route::resource('assessment-templates', App\Http\Controllers\Admin\AssessmentTemplateController::class);
        Route::resource('grade-components', App\Http\Controllers\Admin\GradeComponentController::class);
        Route::get('grade-components/get-components', [App\Http\Controllers\Admin\GradeComponentController::class, 'getComponents'])->name('grade-components.get-components');

        // Gradebook AJAX Routes
        Route::get('gradebook/get-sections', [App\Http\Controllers\Admin\GradebookController::class, 'getSections'])->name('gradebook.get-sections');
        Route::get('gradebook/get-subjects', [App\Http\Controllers\Admin\GradebookController::class, 'getSubjects'])->name('gradebook.get-subjects');
        Route::get('gradebook/get-terms', [App\Http\Controllers\Admin\GradebookController::class, 'getTerms'])->name('gradebook.get-terms');

        // Report Cards
        Route::get('report-cards/settings', [App\Http\Controllers\Admin\ReportCardController::class, 'settings'])->name('report-cards.settings');
        Route::post('report-cards/settings', [App\Http\Controllers\Admin\ReportCardController::class, 'updateSettings'])->name('report-cards.update-settings');

        // Academic Reports
        Route::get('academic-reports/settings', [App\Http\Controllers\Admin\AcademicReportController::class, 'settings'])->name('academic-reports.settings');
        Route::post('academic-reports/settings', [App\Http\Controllers\Admin\AcademicReportController::class, 'updateSettings'])->name('academic-reports.settings.update');
        Route::get('academic-reports', [App\Http\Controllers\Admin\AcademicReportController::class, 'index'])->name('academic-reports.index');
        Route::get('academic-reports/show', [App\Http\Controllers\Admin\AcademicReportController::class, 'show'])->name('academic-reports.show');

        Route::get('section-grades/{section}/report-card-details', [App\Http\Controllers\Admin\ReportCardController::class, 'entry'])->name('section-grades.report-card-entry');
        Route::post('section-grades/{section}/report-card-details', [App\Http\Controllers\Admin\ReportCardController::class, 'storeEntry'])->name('section-grades.store-report-card-entry');
        
        Route::get('section-grades/{section}/report-card/bulk-print', [App\Http\Controllers\Admin\ReportCardController::class, 'bulkPrint'])->name('section-grades.bulk-print-report-cards');
        
        Route::get('students/{student}/report-card/pdf', [App\Http\Controllers\Admin\ReportCardController::class, 'generatePdf'])->name('report-cards.pdf');

        Route::resource('students', App\Http\Controllers\Admin\StudentController::class);
    });
});

require __DIR__.'/auth.php';
