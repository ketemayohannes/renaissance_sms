<?php

use App\Http\Controllers\Admin\AcademicActivityController;
use App\Http\Controllers\Admin\AcademicReportController;
use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\AssessmentTemplateController;
use App\Http\Controllers\Admin\AssessmentTypeController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\DisciplinaryController;
use App\Http\Controllers\Admin\DivisionController;
use App\Http\Controllers\Admin\ElectiveController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\EscalationRuleController;
use App\Http\Controllers\Admin\ExamReviewController;
use App\Http\Controllers\Admin\FinanceController;
use App\Http\Controllers\Admin\GeneralSettingController;
use App\Http\Controllers\Admin\GlobalSearchController;
use App\Http\Controllers\Admin\GradebookController;
use App\Http\Controllers\Admin\GradeComponentController;
use App\Http\Controllers\Admin\GradeLevelController;
use App\Http\Controllers\Admin\GraduatesController;
use App\Http\Controllers\Admin\GuardianController;
use App\Http\Controllers\Admin\IdCardSettingController;
use App\Http\Controllers\Admin\InfractionDefinitionController;
use App\Http\Controllers\Admin\InventoryAssetController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\InventoryItemController;
use App\Http\Controllers\Admin\InventoryPurchaseRequestController;
use App\Http\Controllers\Admin\InventoryReportController;
use App\Http\Controllers\Admin\InventoryRequestController;
use App\Http\Controllers\Admin\InventoryStockController;
use App\Http\Controllers\Admin\LeaveRequestController;
use App\Http\Controllers\Admin\LibraryBookController;
use App\Http\Controllers\Admin\LibraryBorrowingController;
use App\Http\Controllers\Admin\MessagingController;
use App\Http\Controllers\Admin\NoticeController;
use App\Http\Controllers\Admin\PortalController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\ReportCardController;
use App\Http\Controllers\Admin\ReportsController;
use App\Http\Controllers\Admin\ResultAnalysisController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SectionController;
use App\Http\Controllers\Admin\SectionGradeController;
use App\Http\Controllers\Admin\StaffAttendanceController;
use App\Http\Controllers\Admin\StaffAvailabilityController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\SubjectAssignmentController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\TeacherAssignmentController;
use App\Http\Controllers\Admin\TermController;
use App\Http\Controllers\Admin\TimetableController;
use App\Http\Controllers\Auth\PasswordChangeController;
use App\Http\Controllers\Auth\RedirectController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Parent\ChildPortalController;
use App\Http\Controllers\Parent\CommunicationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Student\ActivityController;
use App\Http\Controllers\Student\DashboardController;
use App\Http\Controllers\Student\GradeController;
use App\Http\Controllers\Student\LibraryController;
use App\Http\Controllers\Teacher\ClassController;
use App\Http\Controllers\Teacher\DepartmentController;
use App\Http\Controllers\Teacher\ExamPaperController;
use App\Http\Controllers\Teacher\HomeroomController;
use App\Http\Controllers\Teacher\ScheduleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [RedirectController::class, 'index'])
        ->middleware(['verified'])
        ->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Mandatory Password Change
    Route::get('/auth/change-password', [PasswordChangeController::class, 'show'])->name('auth.change-password');
    Route::post('/auth/change-password', [PasswordChangeController::class, 'update'])->name('auth.change-password.update');

    // Student Routes
    Route::middleware(['role:Student'])->prefix('student')->name('student.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/grades', [GradeController::class, 'index'])->name('grades.index');
        Route::get('/grades/download', [GradeController::class, 'downloadReport'])->name('grades.download');
        Route::get('/profile', [App\Http\Controllers\Student\ProfileController::class, 'show'])->name('profile');
        Route::put('/password', [App\Http\Controllers\Student\ProfileController::class, 'updatePassword'])->name('password.update');

        // Academic Activities
        Route::get('/activities', [ActivityController::class, 'index'])->name('activities.index');
        Route::get('/activities/{activity}', [ActivityController::class, 'show'])->name('activities.show');
        Route::post('/activities/{activity}/submit', [ActivityController::class, 'submit'])->name('activities.submit');
        Route::get('/activities/{activity}/exam', [ActivityController::class, 'takeExam'])->name('activities.exam');
        Route::post('/activities/{activity}/exam', [ActivityController::class, 'submitExam'])->name('activities.exam.submit');

        // Library (read-only browse + digital downloads + personal loans)
        Route::middleware('permission:view library')->prefix('library')->name('library.')->group(function () {
            Route::get('/', [LibraryController::class, 'index'])->name('index');
            Route::get('/my-borrowings', [LibraryController::class, 'myBorrowings'])->name('my-borrowings');
            Route::get('/{book}', [LibraryController::class, 'show'])->name('show');
            Route::get('/{book}/download', [LibraryController::class, 'download'])->name('download');
        });
    });

    // Staff HR Routes (own group so HR Manager can be admitted WITHOUT joining the main
    // admin group above/below, which contains permission-ungated routes like dashboard,
    // settings, and messaging. Every route here carries its own permission gate; the
    // shared 'admin.' name/prefix keeps the admin layout and sidebar links working.)
    Route::middleware(['role:Super Admin|Principal|Vice Principal|Supervisor|IT / System Admin|Registrar|General Manager|HR Manager'])->prefix('admin')->name('admin.')->group(function () {
        // Staff Leave Requests (HR / Principal approval)
        Route::get('hr/leave-requests', [LeaveRequestController::class, 'index'])->name('hr.leave-requests.index')->middleware('permission:manage leave requests');
        Route::post('hr/leave-requests', [LeaveRequestController::class, 'store'])->name('hr.leave-requests.store')->middleware('permission:manage leave requests');
        Route::post('hr/leave-requests/{leaveRequest}/approve', [LeaveRequestController::class, 'approve'])->name('hr.leave-requests.approve')->middleware('permission:manage leave requests');
        Route::post('hr/leave-requests/{leaveRequest}/reject', [LeaveRequestController::class, 'reject'])->name('hr.leave-requests.reject')->middleware('permission:manage leave requests');

        // Staff Daily Attendance Register (HR-entered)
        Route::get('hr/staff-attendance', [StaffAttendanceController::class, 'index'])->name('hr.staff-attendance.index')->middleware('permission:manage staff attendance');
        Route::post('hr/staff-attendance', [StaffAttendanceController::class, 'store'])->name('hr.staff-attendance.store')->middleware('permission:manage staff attendance');

        // Staff Availability (today status + teaching load / free periods)
        Route::get('hr/availability', [StaffAvailabilityController::class, 'index'])->name('hr.availability.index')->middleware('permission:view staff availability');
    });

    // Inventory Routes (own group so Inventory Manager can be admitted without joining
    // the main admin group — same reasoning as the Staff HR group above).
    Route::middleware(['role:Super Admin|Principal|Vice Principal|Supervisor|IT / System Admin|Registrar|General Manager|Inventory Manager'])->prefix('admin')->name('admin.')->group(function () {
        // Read-only (view inventory): dashboard, catalog, ledgers, reports
        Route::middleware('permission:view inventory')->group(function () {
            Route::get('inventory', [InventoryController::class, 'dashboard'])->name('inventory.dashboard');
            Route::get('inventory/items', [InventoryItemController::class, 'index'])->name('inventory.items.index');
            Route::get('inventory/items/{item}', [InventoryItemController::class, 'show'])->name('inventory.items.show');
            Route::get('inventory/reports', [InventoryReportController::class, 'index'])->name('inventory.reports.index');
            Route::get('inventory/reports/{report}/pdf', [InventoryReportController::class, 'pdf'])->name('inventory.reports.pdf');
        });

        // Inventory requests — submit (any requester), approve (Principal), fulfil (IM)
        Route::middleware('permission:request inventory')->group(function () {
            Route::get('inventory/my-requests', [InventoryRequestController::class, 'myRequests'])->name('inventory.my-requests.index');
            Route::post('inventory/my-requests/item', [InventoryRequestController::class, 'storeItem'])->name('inventory.my-requests.item.store');
            Route::delete('inventory/my-requests/item/{itemRequest}', [InventoryRequestController::class, 'cancelItem'])->name('inventory.my-requests.item.cancel');
            Route::post('inventory/my-requests/purchase', [InventoryPurchaseRequestController::class, 'store'])->name('inventory.my-requests.purchase.store');
            Route::delete('inventory/my-requests/purchase/{purchaseRequest}', [InventoryPurchaseRequestController::class, 'cancel'])->name('inventory.my-requests.purchase.cancel');
        });

        Route::middleware('permission:approve inventory requests')->group(function () {
            Route::get('inventory/requests', [InventoryRequestController::class, 'index'])->name('inventory.requests.index');
            Route::post('inventory/requests/{itemRequest}/approve', [InventoryRequestController::class, 'approve'])->name('inventory.requests.approve');
            Route::post('inventory/requests/{itemRequest}/reject', [InventoryRequestController::class, 'reject'])->name('inventory.requests.reject');
            Route::post('inventory/purchases/{purchaseRequest}/principal', [InventoryPurchaseRequestController::class, 'principalDecision'])->name('inventory.purchases.principal');
        });

        // Purchase queues + lists — visible to any inventory viewer; GM decision gated separately.
        Route::middleware('permission:view inventory')->group(function () {
            Route::get('inventory/purchases', [InventoryPurchaseRequestController::class, 'index'])->name('inventory.purchases.index');
            Route::get('inventory/purchases/list', [InventoryPurchaseRequestController::class, 'purchaseList'])->name('inventory.purchases.list');
            Route::get('inventory/purchases/declined', [InventoryPurchaseRequestController::class, 'declineList'])->name('inventory.purchases.declined');
        });

        Route::post('inventory/purchases/{purchaseRequest}/gm', [InventoryPurchaseRequestController::class, 'gmDecision'])->name('inventory.purchases.gm')->middleware('permission:approve inventory purchases');

        // Write (manage inventory): CRUD, asset lifecycle, stock movements, fulfilment
        Route::middleware('permission:manage inventory')->group(function () {
            Route::get('inventory/requests/fulfilment', [InventoryRequestController::class, 'fulfilment'])->name('inventory.requests.fulfilment');
            Route::post('inventory/requests/{itemRequest}/fulfil', [InventoryRequestController::class, 'fulfil'])->name('inventory.requests.fulfil');
            Route::post('inventory/categories', [InventoryItemController::class, 'storeCategory'])->name('inventory.categories.store');
            Route::post('inventory/items', [InventoryItemController::class, 'store'])->name('inventory.items.store');
            Route::put('inventory/items/{item}', [InventoryItemController::class, 'update'])->name('inventory.items.update');
            Route::post('inventory/items/{item}/assets', [InventoryAssetController::class, 'store'])->name('inventory.assets.store');
            Route::put('inventory/assets/{asset}', [InventoryAssetController::class, 'update'])->name('inventory.assets.update');
            Route::post('inventory/assets/{asset}/assign', [InventoryAssetController::class, 'assign'])->name('inventory.assets.assign');
            Route::post('inventory/assets/{asset}/return', [InventoryAssetController::class, 'returnAsset'])->name('inventory.assets.return');
            Route::post('inventory/assets/{asset}/status', [InventoryAssetController::class, 'setStatus'])->name('inventory.assets.status');
            Route::post('inventory/items/{item}/stock-in', [InventoryStockController::class, 'stockIn'])->name('inventory.stock.in');
            Route::post('inventory/items/{item}/stock-out', [InventoryStockController::class, 'stockOut'])->name('inventory.stock.out');
        });
    });

    // Library Routes (own group so the Librarian role can be admitted without joining the
    // main admin group, which contains permission-ungated screens like the dashboard).
    Route::middleware(['role:Super Admin|Principal|Vice Principal|Supervisor|IT / System Admin|Registrar|General Manager|Librarian'])->prefix('admin')->name('admin.')->group(function () {
        // Read-only catalog + digital downloads (view library)
        Route::middleware('permission:view library')->group(function () {
            Route::get('library', [LibraryBookController::class, 'index'])->name('library.index');
            Route::get('library/books/{book}', [LibraryBookController::class, 'show'])->name('library.show');
            Route::get('library/books/{book}/download', [LibraryBookController::class, 'download'])->name('library.download');
        });

        // Manage the catalog — add/edit books, upload digital files (manage books)
        Route::middleware('permission:manage books')->group(function () {
            Route::post('library/books', [LibraryBookController::class, 'store'])->name('library.store');
            Route::put('library/books/{book}', [LibraryBookController::class, 'update'])->name('library.update');
        });

        // Check-out / check-in for physical copies (issue books / return books)
        Route::post('library/books/{book}/check-out', [LibraryBorrowingController::class, 'checkOut'])->name('library.check-out')->middleware('permission:issue books');
        Route::post('library/borrowings/{borrowing}/check-in', [LibraryBorrowingController::class, 'checkIn'])->name('library.check-in')->middleware('permission:return books');
    });

    // Admin Routes
    Route::middleware(['role:Super Admin|Principal|Vice Principal|Supervisor|IT / System Admin|Registrar|General Manager'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/search', [GlobalSearchController::class, 'search'])->name('global-search');

        // Role Management
        Route::resource('roles', RoleController::class)->middleware('permission:manage roles');

        // Audit Logs
        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index')->middleware('permission:manage roles');

        // Notice Board
        Route::resource('notices', NoticeController::class)->middleware('permission:manage notice board');

        // Messaging
        Route::get('messages', [MessagingController::class, 'index'])->name('messages.index');
        Route::get('messages/create', [MessagingController::class, 'create'])->name('messages.create');
        Route::post('messages', [MessagingController::class, 'store'])->name('messages.store');
        Route::get('messages/{conversation}', [MessagingController::class, 'show'])->name('messages.show');
        Route::post('messages/{conversation}/reply', [MessagingController::class, 'reply'])->name('messages.reply');
        Route::get('messages/unread-count', [MessagingController::class, 'unreadCount'])->name('messages.unread-count');

        // Academic Structure
        Route::resource('divisions', DivisionController::class)->except(['show'])->middleware('permission:manage divisions');
        Route::resource('grade-levels', GradeLevelController::class)->except(['show'])->middleware('permission:manage grade levels');
        // Section Import (permission-gated)
        Route::get('sections/import', [SectionController::class, 'import'])->name('sections.import')->middleware('permission:manage sections');
        Route::post('sections/import', [SectionController::class, 'upload'])->name('sections.upload')->middleware('permission:manage sections');
        Route::get('sections/download-template', [SectionController::class, 'downloadTemplate'])->name('sections.download-template')->middleware('permission:manage sections');
        // Bulk Create Sections (permission-gated)
        Route::get('sections/bulk-create', [SectionController::class, 'bulkCreate'])->name('sections.bulk-create')->middleware('permission:manage sections');
        Route::post('sections/bulk-create', [SectionController::class, 'bulkStore'])->name('sections.bulk-store')->middleware('permission:manage sections');

        Route::resource('sections', SectionController::class)->except(['show'])->middleware('permission:manage sections');

        // Subject Ordering (permission-gated)
        Route::get('subjects/reorder', [SubjectController::class, 'reorder'])->name('subjects.reorder')->middleware('permission:manage subjects');
        Route::post('subjects/reorder', [SubjectController::class, 'updateOrder'])->name('subjects.update-order')->middleware('permission:manage subjects');

        // Subject Import (permission-gated)
        Route::get('subjects/import', [SubjectController::class, 'import'])->name('subjects.import')->middleware('permission:manage subjects');
        Route::post('subjects/upload', [SubjectController::class, 'upload'])->name('subjects.upload')->middleware('permission:manage subjects');
        Route::get('subjects/template', [SubjectController::class, 'downloadTemplate'])->name('subjects.template')->middleware('permission:manage subjects');

        Route::resource('subjects', SubjectController::class)->except(['show'])->middleware('permission:manage subjects');

        // Subject Assignment (permission-gated)
        Route::get('subject-assignments/bulk-assign', [SubjectAssignmentController::class, 'bulkAssignForm'])->name('subject-assignments.bulk-assign')->middleware('permission:manage subjects');
        Route::post('subject-assignments/bulk-assign', [SubjectAssignmentController::class, 'storeBulkAssign'])->name('subject-assignments.bulk-assign.store')->middleware('permission:manage subjects');

        Route::get('subject-assignments', [SubjectAssignmentController::class, 'index'])->name('subject-assignments.index')->middleware('permission:manage subjects');

        Route::get('subject-assignments/{grade_level}/edit', [SubjectAssignmentController::class, 'edit'])->name('subject-assignments.edit')->middleware('permission:manage subjects');
        Route::put('subject-assignments/{grade_level}', [SubjectAssignmentController::class, 'update'])->name('subject-assignments.update')->middleware('permission:manage subjects');

        Route::resource('academic-years', AcademicYearController::class)->except(['show'])->middleware('permission:manage grade levels');
        Route::resource('terms', TermController::class)->except(['show'])->middleware('permission:manage grade levels');
        Route::get('terms/quarters/{academicYear}', [TermController::class, 'getQuarters'])->name('terms.get-quarters');

        // Graduates Page
        Route::get('graduates', [GraduatesController::class, 'index'])->name('graduates.index')->middleware('permission:view students');

        // Student Management (permission-gated)
        Route::get('students/enrollments', [StudentController::class, 'enrollmentsIndex'])->name('students.enrollments')->middleware('permission:view students');
        Route::get('students/export', [StudentController::class, 'export'])->name('students.export')->middleware('permission:view students');

        Route::get('students/import', [StudentController::class, 'import'])->name('students.import')->middleware('permission:create students');
        Route::post('students/import', [StudentController::class, 'upload'])->name('students.upload')->middleware('permission:create students');
        Route::get('students/download-template', [StudentController::class, 'downloadTemplate'])->name('students.download-template')->middleware('permission:create students');
        Route::get('students/download-quick-template', [StudentController::class, 'downloadQuickTemplate'])->name('students.download-quick-template')->middleware('permission:create students');
        Route::post('students/quick-upload', [StudentController::class, 'quickUpload'])->name('students.quick-upload')->middleware('permission:create students');
        Route::post('students/{student}/toggle-block', [StudentController::class, 'toggleBlock'])->name('students.toggle-block')->middleware('permission:edit students');
        Route::get('students/{student}/transfer', [StudentController::class, 'transferForm'])->name('students.transfer')->middleware('permission:edit students');
        Route::post('students/{student}/transfer', [StudentController::class, 'transfer'])->name('students.transfer.store')->middleware('permission:edit students');
        Route::get('students/{student}/assign-electives', [StudentController::class, 'assignElectivesForm'])->name('students.assign-electives')->middleware('permission:edit students');
        Route::post('students/{student}/assign-electives', [StudentController::class, 'storeElectives'])->name('students.assign-electives.store')->middleware('permission:edit students');
        Route::post('students/{student}/siblings', [StudentController::class, 'linkSibling'])->name('students.siblings.link')->middleware('permission:edit students');
        Route::delete('students/{student}/siblings/{sibling}', [StudentController::class, 'unlinkSibling'])->name('students.siblings.unlink')->middleware('permission:edit students');
        Route::post('students/bulk-destroy', [StudentController::class, 'bulkDestroy'])->name('students.bulk-destroy')->middleware('permission:delete students');
        Route::post('students/bulk-deactivate', [StudentController::class, 'bulkDeactivate'])->name('students.bulk-deactivate')->middleware('permission:edit students');
        Route::post('students/bulk-transfer', [StudentController::class, 'bulkTransfer'])->name('students.bulk-transfer')->middleware('permission:edit students');
        Route::get('students/{student}/withdraw', [StudentController::class, 'withdrawForm'])->name('students.withdraw')->middleware('permission:edit students');
        Route::post('students/{student}/withdraw', [StudentController::class, 'processWithdrawal'])->name('students.withdraw.store')->middleware('permission:edit students');
        Route::get('students/{student}/status-history', [StudentController::class, 'statusHistory'])->name('students.status-history')->middleware('permission:view students');
        Route::get('id-cards', [StudentController::class, 'idCardsIndex'])->name('id-cards.index')->middleware('permission:view students');

        // Student Document Routes (permission-gated)
        Route::post('students/{student}/document', [StudentController::class, 'storeDocument'])->name('students.store-document')->middleware('permission:edit students');
        Route::get('students/{student}/document/{document}/download', [StudentController::class, 'downloadDocument'])->name('students.download-document')->middleware('permission:view students');
        Route::delete('students/{student}/document/{document}', [StudentController::class, 'deleteDocument'])->name('students.delete-document')->middleware('permission:edit students');
        Route::post('students/{id}/restore', [StudentController::class, 'restore'])->name('students.restore')->middleware('permission:edit students');
        Route::post('students/bulk-id-cards', [StudentController::class, 'bulkIdCardsSelected'])->name('students.bulk-id-cards-selected')->middleware('permission:view students');

        // Electives Bulk Assign (permission-gated)
        Route::get('electives/bulk-assign', [ElectiveController::class, 'bulkAssignForm'])->name('electives.bulk-assign')->middleware('permission:edit students');
        Route::post('electives/bulk-assign', [ElectiveController::class, 'storeBulkAssign'])->name('electives.bulk-assign.store')->middleware('permission:edit students');
        Route::get('electives/subjects', [ElectiveController::class, 'getSubjects'])->name('electives.get-subjects')->middleware('permission:edit students');
        Route::get('electives/sections', [ElectiveController::class, 'getSections'])->name('electives.get-sections')->middleware('permission:edit students');
        Route::get('electives/students', [ElectiveController::class, 'getStudents'])->name('electives.get-students')->middleware('permission:edit students');

        // Gradebook Routes (permission-gated)
        Route::get('gradebook/export-template', [GradebookController::class, 'exportTemplate'])->name('gradebook.export-template')->middleware('permission:view subject gradebook');
        Route::post('gradebook/import', [GradebookController::class, 'import'])->name('gradebook.import')->middleware('permission:edit subject gradebook');
        Route::get('gradebook', [GradebookController::class, 'index'])->name('gradebook.index')->middleware('permission:view subject gradebook');
        Route::get('gradebook/entry', [GradebookController::class, 'entry'])->name('gradebook.entry')->middleware('permission:view subject gradebook');
        Route::get('gradebook/marksheet', [GradebookController::class, 'marksheet'])->name('gradebook.marksheet')->middleware('permission:view subject gradebook');
        Route::post('gradebook/store', [GradebookController::class, 'store'])->name('gradebook.store')->middleware('permission:edit subject gradebook');

        // Section Grade Entry Routes (permission-gated)
        Route::get('section-grades', [SectionGradeController::class, 'index'])->name('section-grades.index')->middleware('permission:view master gradebook');
        Route::get('section-grades/entry', [SectionGradeController::class, 'entry'])->name('section-grades.entry')->middleware('permission:view master gradebook');
        Route::post('section-grades/store', [SectionGradeController::class, 'store'])->name('section-grades.store')->middleware('permission:edit master gradebook');
        Route::get('section-grades/export', [SectionGradeController::class, 'export'])->name('section-grades.export')->middleware('permission:view master gradebook');
        Route::get('section-grades/export-data', [SectionGradeController::class, 'exportData'])->name('section-grades.export-data')->middleware('permission:view master gradebook');
        Route::get('section-grades/export-low-performance', [SectionGradeController::class, 'exportLowPerformance'])->name('section-grades.export-low-performance')->middleware('permission:view master gradebook');
        Route::get('section-grades/download-low-performance-report', [SectionGradeController::class, 'downloadLowPerformanceReport'])->name('section-grades.download-low-performance-report')->middleware('permission:view master gradebook');
        Route::post('section-grades/import', [SectionGradeController::class, 'import'])->name('section-grades.import')->middleware('permission:edit master gradebook');
        Route::post('section-grades/calculate', [SectionGradeController::class, 'calculateSemester'])->name('section-grades.calculate')->middleware('permission:edit master gradebook');

        // Assessment System Routes (permission-gated)
        Route::resource('assessment-types', AssessmentTypeController::class)->middleware('permission:configure assessments');
        Route::post('assessment-templates/bulk-destroy', [AssessmentTemplateController::class, 'bulkDestroy'])->name('assessment-templates.bulk-destroy')->middleware('permission:configure assessments');
        Route::post('assessment-templates/destroy-by-term', [AssessmentTemplateController::class, 'destroyByTerm'])->name('assessment-templates.destroy-by-term')->middleware('permission:configure assessments');
        Route::get('assessment-templates/reorder', [AssessmentTemplateController::class, 'reorder'])->name('assessment-templates.reorder')->middleware('permission:configure assessments');
        Route::post('assessment-templates/reorder', [AssessmentTemplateController::class, 'updateOrder'])->name('assessment-templates.update-order')->middleware('permission:configure assessments');
        Route::resource('assessment-templates', AssessmentTemplateController::class)->middleware('permission:configure assessments');
        Route::resource('grade-components', GradeComponentController::class)->middleware('permission:configure assessments');
        Route::get('grade-components/get-components', [GradeComponentController::class, 'getComponents'])->name('grade-components.get-components')->middleware('permission:configure assessments');

        // ID Card Settings (permission-gated)
        Route::get('id-card-settings', [IdCardSettingController::class, 'index'])->name('id-card-settings.index')->middleware('permission:manage roles');
        Route::put('id-card-settings', [IdCardSettingController::class, 'update'])->name('id-card-settings.update')->middleware('permission:manage roles');

        // ── General Settings Hub (primary — all config in one place) ──────────────
        Route::prefix('settings/general')->name('settings.general.')->middleware('role:Super Admin')->group(function () {
            Route::get('/', [GeneralSettingController::class, 'index'])->name('index');
            Route::post('/communication', [GeneralSettingController::class, 'updateCommunication'])->name('communication.update');
            Route::post('/events', [GeneralSettingController::class, 'updateEventRouting'])->name('events.update');
            Route::post('/school', [GeneralSettingController::class, 'updateSchool'])->name('school.update');
            Route::post('/test-sms', [GeneralSettingController::class, 'testSms'])->name('test-sms');
            Route::post('/test-email', [GeneralSettingController::class, 'testEmail'])->name('test-email');
            Route::post('/test-connection', [GeneralSettingController::class, 'testConnection'])->name('test-connection');
            Route::post('/clear-cache', [GeneralSettingController::class, 'clearCache'])->name('clear-cache');
        });

        // ── Backward-Compatible Aliases (old communication settings URLs) ─────────
        // These keep any bookmarked or hardcoded links working without a 404.
        Route::get('settings/communication', fn () => redirect()->route('admin.settings.general.index'))->name('settings.communication.index')->middleware('role:Super Admin');
        Route::post('settings/communication', [GeneralSettingController::class, 'updateCommunication'])->name('settings.communication.update')->middleware('role:Super Admin');
        Route::post('settings/communication/test-sms', [GeneralSettingController::class, 'testSms'])->name('settings.communication.test-sms')->middleware('role:Super Admin');
        Route::post('settings/communication/test-email', [GeneralSettingController::class, 'testEmail'])->name('settings.communication.test-email')->middleware('role:Super Admin');

        // Gradebook AJAX Routes (permission-gated)
        Route::get('gradebook/get-sections', [GradebookController::class, 'getSections'])->name('gradebook.get-sections')->middleware('permission:view subject gradebook');
        Route::get('gradebook/get-subjects', [GradebookController::class, 'getSubjects'])->name('gradebook.get-subjects')->middleware('permission:view subject gradebook');
        Route::get('gradebook/get-terms', [GradebookController::class, 'getTerms'])->name('gradebook.get-terms')->middleware('permission:view subject gradebook');

        // Report Cards (permission-gated)
        Route::get('report-cards/settings', [ReportCardController::class, 'settings'])->name('report-cards.settings')->middleware('permission:generate report cards');
        Route::post('report-cards/settings', [ReportCardController::class, 'updateSettings'])->name('report-cards.update-settings')->middleware('permission:generate report cards');
        Route::get('report-cards/yearly-settings', [ReportCardController::class, 'yearlySettings'])->name('report-cards.yearly-settings')->middleware('permission:generate report cards');
        Route::post('report-cards/yearly-settings', [ReportCardController::class, 'updateYearlySettings'])->name('report-cards.update-yearly-settings')->middleware('permission:generate report cards');
        Route::get('report-cards/grade-scales', [ReportCardController::class, 'gradeScales'])->name('report-cards.grade-scales')->middleware('permission:generate report cards');
        Route::post('report-cards/grade-scales', [ReportCardController::class, 'updateGradeScales'])->name('report-cards.update-grade-scales')->middleware('permission:generate report cards');

        // Academic Reports (permission-gated)
        Route::get('academic-reports/settings', [AcademicReportController::class, 'settings'])->name('academic-reports.settings')->middleware('permission:view marks');
        Route::post('academic-reports/settings', [AcademicReportController::class, 'updateSettings'])->name('academic-reports.settings.update')->middleware('permission:view marks');
        Route::get('academic-reports', [AcademicReportController::class, 'index'])->name('academic-reports.index')->middleware('permission:view marks');
        Route::get('academic-reports/show', [AcademicReportController::class, 'show'])->name('academic-reports.show')->middleware('permission:view marks');
        Route::get('academic-reports/subject-analysis', [AcademicReportController::class, 'subjectAnalysis'])->name('academic-reports.subject-analysis')->middleware('permission:view marks');
        Route::get('academic-reports/grade-matrix', [AcademicReportController::class, 'gradeMatrix'])->name('academic-reports.grade-matrix')->middleware('permission:view marks');
        Route::get('academic-reports/grade-matrix/pdf', [AcademicReportController::class, 'gradeMatrixPdf'])->name('academic-reports.grade-matrix.pdf')->middleware('permission:view marks');
        Route::get('academic-reports/matrix-reorder', [AcademicReportController::class, 'matrixReorder'])->name('academic-reports.matrix-reorder')->middleware('permission:view marks');
        Route::post('academic-reports/matrix-reorder', [AcademicReportController::class, 'updateMatrixOrder'])->name('academic-reports.matrix-reorder.update')->middleware('permission:view marks');
        Route::get('academic-reports/category-ranks', [AcademicReportController::class, 'categoryRanks'])->name('academic-reports.category-ranks')->middleware('permission:view marks');
        Route::get('academic-reports/academic-excellence', [AcademicReportController::class, 'academicExcellence'])->name('academic-reports.academic-excellence')->middleware('permission:view marks');
        Route::post('academic-reports/recalculate', [AcademicReportController::class, 'recalculate'])->name('academic-reports.recalculate')->middleware('permission:view marks');

        // Reports Module
        Route::get('reports', [ReportsController::class, 'index'])->name('reports.index')->middleware('permission:view marks');
        Route::get('reports/top3-per-section', [ReportsController::class, 'top3PerSection'])->name('reports.top3-per-section')->middleware('permission:view marks');
        Route::get('reports/academic-excellence', [ReportsController::class, 'academicExcellence'])->name('reports.academic-excellence')->middleware('permission:view marks');
        Route::get('reports/below-75', [ReportsController::class, 'below75'])->name('reports.below-75')->middleware('permission:view marks');

        // Result Analysis (permission-gated)
        Route::get('result-analysis', [ResultAnalysisController::class, 'index'])->name('result-analysis.index')->middleware('permission:view marks');
        Route::get('result-analysis/{assignment}', [ResultAnalysisController::class, 'show'])->name('result-analysis.show')->middleware('permission:view marks');

        Route::get('section-grades/{section}/report-card-details', [ReportCardController::class, 'entry'])->name('section-grades.report-card-entry')->middleware('permission:generate report cards');
        Route::post('section-grades/{section}/report-card-details', [ReportCardController::class, 'storeEntry'])->name('section-grades.store-report-card-entry')->middleware('permission:generate report cards');

        Route::get('section-grades/{section}/report-card/bulk-print', [ReportCardController::class, 'bulkPrint'])->name('section-grades.bulk-print-report-cards')->middleware('permission:generate report cards');

        Route::get('students/{student}/report-card/pdf', [ReportCardController::class, 'generatePdf'])->name('report-cards.pdf')->middleware('permission:generate report cards');

        // Background Exports (permission-gated)
        Route::get('report-cards/exports', [ReportCardController::class, 'exports'])->name('report-cards.exports')->middleware('permission:generate report cards');
        Route::get('report-cards/export-low-performance', [ReportCardController::class, 'exportLowPerformance'])->name('report-cards.export-low-performance')->middleware('permission:generate report cards');
        Route::get('section-grades/{section}/report-card/bulk-export', [ReportCardController::class, 'bulkExport'])->name('section-grades.bulk-export-report-cards')->middleware('permission:generate report cards');
        Route::get('report-cards/exports/{exportRequest}/download', [ReportCardController::class, 'downloadExport'])->name('report-cards.download-export')->middleware('permission:generate report cards');
        Route::delete('report-cards/exports/{exportRequest}', [ReportCardController::class, 'destroyExport'])->name('report-cards.destroy-export')->middleware('permission:generate report cards');

        // Academic Activities Module (permission-gated)
        Route::get('activities/get-templates', [AcademicActivityController::class, 'getTemplates'])->name('activities.get-templates')->middleware('permission:enter marks');
        Route::get('activities/{activity}/evaluate', [AcademicActivityController::class, 'evaluate'])->name('activities.evaluate')->middleware('permission:enter marks');
        Route::post('activities/{activity}/evaluate', [AcademicActivityController::class, 'storeEvaluation'])->name('activities.evaluate.store')->middleware('permission:enter marks');
        Route::get('activities/{activity}/questions', [AcademicActivityController::class, 'manageQuestions'])->name('activities.questions')->middleware('permission:enter marks');
        Route::post('activities/{activity}/questions', [AcademicActivityController::class, 'storeQuestions'])->name('activities.questions.store')->middleware('permission:enter marks');
        Route::resource('activities', AcademicActivityController::class)->middleware('permission:enter marks');

        // Attendance Management (permission-gated)
        Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index')->middleware('permission:manage attendance');
        Route::get('attendance/register', [AttendanceController::class, 'register'])->name('attendance.register')->middleware('permission:manage attendance');
        Route::post('attendance/store', [AttendanceController::class, 'store'])->name('attendance.store')->middleware('permission:manage attendance');
        Route::get('attendance/report', [AttendanceController::class, 'report'])->name('attendance.report')->middleware('permission:manage attendance');

        // Timetable Management (permission-gated)
        Route::get('timetable', [TimetableController::class, 'index'])->name('timetable.index')->middleware('permission:manage sections');
        Route::get('timetable/builder', [TimetableController::class, 'builder'])->name('timetable.builder')->middleware('permission:manage sections');
        Route::post('timetable/store', [TimetableController::class, 'store'])->name('timetable.store')->middleware('permission:manage sections');

        // Promotion Management (permission-gated)
        Route::get('promotions', [PromotionController::class, 'index'])->name('promotions.index')->middleware('permission:promote students');
        Route::post('promotions/rules', [PromotionController::class, 'storeRule'])->name('promotions.store-rule')->middleware('permission:promote students');
        Route::delete('promotions/rules/{promotionRule}', [PromotionController::class, 'deleteRule'])->name('promotions.delete-rule')->middleware('permission:promote students');
        Route::get('promotions/process', [PromotionController::class, 'processForm'])->name('promotions.process')->middleware('permission:promote students');
        Route::post('promotions/preview', [PromotionController::class, 'preview'])->name('promotions.preview')->middleware('permission:promote students');
        Route::post('promotions/execute', [PromotionController::class, 'execute'])->name('promotions.execute')->middleware('permission:promote students');
        Route::get('promotions/history', [PromotionController::class, 'history'])->name('promotions.history')->middleware('permission:promote students');
        Route::post('promotions/history/{studentPromotion}/enroll', [PromotionController::class, 'enrollStudent'])->name('promotions.enroll')->middleware('permission:promote students');
        Route::post('promotions/history/{studentPromotion}/reverse', [PromotionController::class, 'reversePromotion'])->name('promotions.reverse')->middleware('permission:promote students');
        Route::post('promotions/history/bulk-enroll', [PromotionController::class, 'bulkEnroll'])->name('promotions.bulk-enroll')->middleware('permission:promote students');
        Route::post('promotions/history/enroll-all', [PromotionController::class, 'enrollAll'])->name('promotions.enroll-all')->middleware('permission:promote students');
        Route::post('promotions/history/bulk-reverse', [PromotionController::class, 'bulkReverse'])->name('promotions.bulk-reverse')->middleware('permission:promote students');

        // Disciplinary Records (permission-gated)
        Route::get('disciplinary', [DisciplinaryController::class, 'index'])->name('disciplinary.index')->middleware('permission:view students');
        Route::get('disciplinary/create/{student?}', [DisciplinaryController::class, 'create'])->name('disciplinary.create')->middleware('permission:edit students');
        Route::post('disciplinary', [DisciplinaryController::class, 'store'])->name('disciplinary.store')->middleware('permission:edit students');
        Route::get('disciplinary/{disciplinary}', [DisciplinaryController::class, 'show'])->name('disciplinary.show')->middleware('permission:view students');
        Route::put('disciplinary/{disciplinary}', [DisciplinaryController::class, 'update'])->name('disciplinary.update')->middleware('permission:edit students');
        Route::get('students/{student}/disciplinary', [DisciplinaryController::class, 'studentRecords'])->name('students.disciplinary')->middleware('permission:view students');

        // Discipline Settings (permission-gated)
        Route::get('discipline-settings', [InfractionDefinitionController::class, 'index'])->name('discipline-settings.index')->middleware('permission:edit students');
        Route::post('discipline-settings/infractions', [InfractionDefinitionController::class, 'store'])->name('discipline-settings.infractions.store')->middleware('permission:edit students');
        Route::put('discipline-settings/infractions/{definition}', [InfractionDefinitionController::class, 'update'])->name('discipline-settings.infractions.update')->middleware('permission:edit students');
        Route::post('discipline-settings/infractions/{definition}/toggle', [InfractionDefinitionController::class, 'toggleActive'])->name('discipline-settings.infractions.toggle')->middleware('permission:edit students');
        Route::post('discipline-settings/rules', [EscalationRuleController::class, 'store'])->name('discipline-settings.rules.store')->middleware('permission:edit students');
        Route::put('discipline-settings/rules/{rule}', [EscalationRuleController::class, 'update'])->name('discipline-settings.rules.update')->middleware('permission:edit students');
        Route::post('discipline-settings/rules/{rule}/toggle', [EscalationRuleController::class, 'toggleActive'])->name('discipline-settings.rules.toggle')->middleware('permission:edit students');

        // Student ID Cards (permission-gated)
        Route::get('students/{student}/id-card', [StudentController::class, 'generateIdCard'])->name('students.id-card')->middleware('permission:view students');
        Route::get('sections/{section}/id-cards', [StudentController::class, 'bulkIdCards'])->name('sections.bulk-id-cards')->middleware('permission:view students');
        Route::post('guardians/{guardian}/create-user', [StudentController::class, 'createGuardianUser'])->name('guardians.create-user')->middleware('permission:edit students');
        Route::post('students/{student}/create-user', [StudentController::class, 'createStudentUser'])->name('students.create-user')->middleware('permission:edit students');
        Route::post('students/{student}/reset-password', [StudentController::class, 'resetStudentPassword'])->name('students.reset-password')->middleware('permission:edit students');

        // Human Resources (permission-gated)
        Route::get('employees/import', [EmployeeController::class, 'import'])->name('employees.import')->middleware('permission:manage employees');
        Route::get('employees/templates/academic', [EmployeeController::class, 'downloadAcademicTemplate'])->name('employees.academic.template')->middleware('permission:manage employees');
        Route::get('employees/templates/administrative', [EmployeeController::class, 'downloadAdministrativeTemplate'])->name('employees.administrative.template')->middleware('permission:manage employees');
        Route::post('employees/import', [EmployeeController::class, 'upload'])->name('employees.upload')->middleware('permission:manage employees');
        Route::post('employees/{employee}/reset-password', [EmployeeController::class, 'resetPassword'])->name('employees.reset-password')->middleware('permission:manage employees');
        Route::post('employees/{employee}/toggle-status', [EmployeeController::class, 'toggleStatus'])->name('employees.toggle-status')->middleware('permission:manage employees');

        // Document Management
        Route::get('employees/documents/{document}/download', [EmployeeController::class, 'downloadDocument'])->name('employees.documents.download')->middleware('permission:view employees');
        Route::get('employees/documents/{document}/delete', [EmployeeController::class, 'deleteDocument'])->name('employees.documents.delete')->middleware('permission:manage employees');

        Route::resource('employees', EmployeeController::class)->middleware('permission:view employees');

        Route::resource('teacher-assignments', TeacherAssignmentController::class)->middleware('permission:manage employees');

        // Parent Management (permission-gated)
        Route::get('guardians/search', [GuardianController::class, 'search'])->name('guardians.search')->middleware('permission:view students');
        Route::post('guardians/{guardian}/create-account', [GuardianController::class, 'createUser'])->name('guardians.create-account')->middleware('permission:edit students');
        Route::post('guardians/{guardian}/reset-password', [GuardianController::class, 'resetPassword'])->name('guardians.reset-password')->middleware('permission:edit students');
        Route::resource('guardians', GuardianController::class)->middleware('permission:view students');

        Route::patch('students/{student}/quick-update', [StudentController::class, 'quickUpdate'])->name('students.quick-update')->middleware('permission:edit students');
        Route::resource('students', StudentController::class)->middleware('permission:view students');

        // Finance & Operations Portals (permission-gated)
        Route::prefix('finance')->name('finance.')->group(function () {
            Route::get('fees', [FinanceController::class, 'fees'])->name('fees')->middleware('permission:view fees');
            Route::get('payroll', [FinanceController::class, 'payroll'])->name('payroll')->middleware('permission:view payroll');
        });

        // Exam Paper Review (permission-gated)
        Route::get('exams', [ExamReviewController::class, 'index'])->name('exams.index')->middleware('permission:view marks');
        Route::get('exams/{exam}', [ExamReviewController::class, 'show'])->name('exams.show')->middleware('permission:view marks');
        Route::post('exams/{exam}/review', [ExamReviewController::class, 'review'])->name('exams.review')->middleware('permission:publish results');
        Route::delete('exams/{exam}', [ExamReviewController::class, 'destroy'])->name('exams.destroy')->middleware('permission:publish results');
        Route::get('exams/{exam}/download', [ExamPaperController::class, 'download'])->name('exams.download')->middleware('permission:view marks');

        // Specialized Portals
        Route::prefix('portals')->name('portals.')->group(function () {
            Route::get('health', [PortalController::class, 'health'])->name('health');
            Route::get('inventory', [PortalController::class, 'inventory'])->name('inventory');
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
        Route::get('/notices', [CommunicationController::class, 'notices'])->name('notices.index');
        Route::get('/notices/{notice}', [CommunicationController::class, 'showNotice'])->name('notices.show');

        // Messaging (replaces old contact form — redirect kept for backwards compat)
        Route::get('/messages', [App\Http\Controllers\Parent\MessagingController::class, 'index'])->name('messages.index');
        Route::get('/messages/create', [App\Http\Controllers\Parent\MessagingController::class, 'create'])->name('messages.create');
        Route::post('/messages', [App\Http\Controllers\Parent\MessagingController::class, 'store'])->name('messages.store');
        Route::get('/messages/{conversation}', [App\Http\Controllers\Parent\MessagingController::class, 'show'])->name('messages.show');
        Route::post('/messages/{conversation}/reply', [App\Http\Controllers\Parent\MessagingController::class, 'reply'])->name('messages.reply');
        Route::get('/messages/unread-count', [App\Http\Controllers\Parent\MessagingController::class, 'unreadCount'])->name('messages.unread-count');
        // Legacy contact routes — redirect to new messaging UI
        Route::get('/contact-teacher', fn () => redirect()->route('parent.messages.create'))->name('contact.form');
        Route::post('/contact-teacher', fn () => redirect()->route('parent.messages.create'))->name('contact.send');
        Route::get('/search', function (Request $request) {
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
                        'subtitle' => 'Grade '.($child->gradeLevel->name ?? 'N/A').' • Section '.($child->section->name ?? 'N/A'),
                        'url' => route('parent.student.dashboard', $child),
                    ];
                }
            }

            return response()->json($results);
        })->name('search');

        // Child‑scoped routes (protected by custom middleware)
        Route::middleware(['parent_access'])->prefix('student/{student}')->name('student.')->group(function () {
            Route::get('/dashboard', [ChildPortalController::class, 'dashboard'])->name('dashboard');
            Route::get('/grades', [ChildPortalController::class, 'grades'])->name('grades.index');
            Route::get('/grades/download', [ChildPortalController::class, 'downloadReport'])->name('grades.download');
            Route::get('/attendance', [ChildPortalController::class, 'attendance'])->name('attendance.index');
            Route::get('/conduct', [ChildPortalController::class, 'conduct'])->name('conduct.index');
            Route::get('/info', [ChildPortalController::class, 'info'])->name('info.show');
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
        Route::get('/messages/create', [App\Http\Controllers\Teacher\MessagingController::class, 'create'])->name('messages.create');
        Route::post('/messages', [App\Http\Controllers\Teacher\MessagingController::class, 'store'])->name('messages.store');
        Route::get('/messages/{conversation}', [App\Http\Controllers\Teacher\MessagingController::class, 'show'])->name('messages.show');
        Route::post('/messages/{conversation}/reply', [App\Http\Controllers\Teacher\MessagingController::class, 'reply'])->name('messages.reply');
        Route::get('/messages/unread-count', [App\Http\Controllers\Teacher\MessagingController::class, 'unreadCount'])->name('messages.unread-count');

        Route::resource('exams', ExamPaperController::class);
        Route::get('exams/{exam}/download', [ExamPaperController::class, 'download'])->name('exams.download');

        // My Classes
        Route::get('/classes', [ClassController::class, 'index'])->name('classes.index');
        Route::get('/classes/{id}', [ClassController::class, 'show'])->name('classes.show');

        // Gradebook
        Route::get('/gradebook/{assignment}', [App\Http\Controllers\Teacher\GradebookController::class, 'entry'])->name('gradebook.entry');
        Route::get('/gradebook/{assignment}/export', [App\Http\Controllers\Teacher\GradebookController::class, 'export'])->name('gradebook.export');
        Route::get('/gradebook/{assignment}/marksheet', [App\Http\Controllers\Teacher\GradebookController::class, 'marksheet'])->name('gradebook.marksheet');
        Route::post('/gradebook/{assignment}/import', [App\Http\Controllers\Teacher\GradebookController::class, 'import'])->name('gradebook.import');
        Route::post('/gradebook/{assignment}', [App\Http\Controllers\Teacher\GradebookController::class, 'store'])->name('gradebook.store');

        // My Schedule
        Route::get('/schedule', [ScheduleController::class, 'index'])->name('schedule.index');

        // My Leave (self-service requests)
        Route::middleware('permission:request leave')->group(function () {
            Route::get('/leave', [App\Http\Controllers\Teacher\LeaveRequestController::class, 'index'])->name('leave.index');
            Route::post('/leave', [App\Http\Controllers\Teacher\LeaveRequestController::class, 'store'])->name('leave.store');
            Route::delete('/leave/{leaveRequest}', [App\Http\Controllers\Teacher\LeaveRequestController::class, 'cancel'])->name('leave.cancel');
        });

        // Inventory Requests (self-service: item requests + purchase requests)
        Route::middleware('permission:request inventory')->prefix('inventory-requests')->name('inventory-requests.')->group(function () {
            Route::get('/', [App\Http\Controllers\Teacher\InventoryRequestController::class, 'index'])->name('index');
            Route::post('/item', [App\Http\Controllers\Teacher\InventoryRequestController::class, 'storeItem'])->name('item.store');
            Route::delete('/item/{itemRequest}', [App\Http\Controllers\Teacher\InventoryRequestController::class, 'cancelItem'])->name('item.cancel');
            Route::post('/purchase', [App\Http\Controllers\Teacher\InventoryRequestController::class, 'storePurchase'])->name('purchase.store');
            Route::delete('/purchase/{purchaseRequest}', [App\Http\Controllers\Teacher\InventoryRequestController::class, 'cancelPurchase'])->name('purchase.cancel');
        });

        // Library (read-only browse + digital downloads + personal loans)
        Route::middleware('permission:view library')->prefix('library')->name('library.')->group(function () {
            Route::get('/', [App\Http\Controllers\Teacher\LibraryController::class, 'index'])->name('index');
            Route::get('/my-borrowings', [App\Http\Controllers\Teacher\LibraryController::class, 'myBorrowings'])->name('my-borrowings');
            Route::get('/{book}', [App\Http\Controllers\Teacher\LibraryController::class, 'show'])->name('show');
            Route::get('/{book}/download', [App\Http\Controllers\Teacher\LibraryController::class, 'download'])->name('download');
        });

        // Homeroom Management
        Route::prefix('homeroom')->name('homeroom.')->group(function () {
            Route::get('/', [HomeroomController::class, 'index'])->name('index');
            Route::get('/attendance', [HomeroomController::class, 'attendance'])->name('attendance');
            Route::post('/attendance', [HomeroomController::class, 'storeAttendance'])->name('attendance.store');
            Route::get('/behavior', [HomeroomController::class, 'behavior'])->name('behavior');
            Route::post('/behavior', [HomeroomController::class, 'storeBehavior'])->name('behavior.store');
        });

        // Department Head Oversight
        Route::prefix('department')->name('department.')->group(function () {
            Route::get('/', [DepartmentController::class, 'index'])->name('index');
            Route::get('/{id}', [DepartmentController::class, 'show'])->name('show');
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
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::get('/count', [NotificationController::class, 'count'])->name('count');
    Route::get('/latest', [NotificationController::class, 'latest'])->name('latest');
    Route::post('/mark-all', [NotificationController::class, 'markAllRead'])->name('mark-all');
    Route::delete('/clear-all', [NotificationController::class, 'clearAll'])->name('clear-all');
    Route::get('/{id}/read', [NotificationController::class, 'markRead'])->name('read');
});

// ─── Shared: Conversation API (all authenticated users) ─────────────────────
Route::middleware('auth')->prefix('conversations')->name('conversations.')->group(function () {
    Route::get('/{conversation}/messages', [NotificationController::class, 'fetchMessages'])->name('messages');
    Route::post('/{conversation}/reply', [NotificationController::class, 'ajaxReply'])->name('reply');
});
