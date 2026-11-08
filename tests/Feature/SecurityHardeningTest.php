<?php

namespace Tests\Feature;

use App\Models\AcademicActivity;
use App\Models\AcademicYear;
use App\Models\ActivityAttachment;
use App\Models\ActivitySubmission;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\ExamQuestion;
use App\Models\GradeLevel;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\StudentGuardian;
use App\Models\StudentTermRecord;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use App\Models\Term;
use App\Models\User;
use App\Services\EmployeeService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Regression tests for the authorization holes closed in the security pass: each one is
 * a server-side check that was missing, so a crafted request could do what the UI hides.
 */
class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $this->seed(RolePermissionSeeder::class);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function employeeWithRole(string $role): Employee
    {
        $employee = Employee::factory()->create();
        $employee->user->assignRole($role);

        return $employee;
    }

    /** A section in the active year with one actively enrolled student. */
    private function sectionWithStudent(AcademicYear $year, GradeLevel $grade, ?User $homeroomTeacher = null): array
    {
        $section = Section::factory()->create([
            'grade_level_id' => $grade->id,
            'academic_year_id' => $year->id,
            'homeroom_teacher_id' => $homeroomTeacher?->id,
        ]);
        $student = Student::factory()->create(['user_id' => $this->userWithRole('Student')->id]);
        $student->sections()->attach($section->id, [
            'academic_year_id' => $year->id,
            'enrollment_date' => now(),
            'status' => 'active',
        ]);

        return [$section, $student];
    }

    // ─── 1. Staff records: writes need "manage employees"; privileged roles protected ──

    public function test_principal_with_only_view_employees_cannot_create_edit_or_delete_staff(): void
    {
        $principal = $this->userWithRole('Principal');
        $target = $this->employeeWithRole('Teacher');

        $this->actingAs($principal)->get('/admin/employees/create')->assertForbidden();
        $this->actingAs($principal)->post('/admin/employees', ['role' => 'Super Admin'])->assertForbidden();
        $this->actingAs($principal)->get("/admin/employees/{$target->id}/edit")->assertForbidden();
        $this->actingAs($principal)->put("/admin/employees/{$target->id}", ['role' => 'Super Admin'])->assertForbidden();
        $this->actingAs($principal)->delete("/admin/employees/{$target->id}")->assertForbidden();

        $this->assertFalse($target->user->fresh()->hasRole('Super Admin'));
        $this->assertNotNull($target->fresh());
    }

    public function test_only_privileged_users_can_hand_out_privileged_roles(): void
    {
        $service = app(EmployeeService::class);

        // A Registrar passes every permission check (Gate::before in AuthServiceProvider)
        // but must still not be able to mint a Super Admin.
        $registrar = $this->userWithRole('Registrar');
        $registrar->givePermissionTo('manage employees');
        $this->actingAs($registrar);

        try {
            $service->ensureCanAssignRole('Super Admin');
            $this->fail('A non-privileged user was allowed to assign Super Admin.');
        } catch (AuthorizationException) {
            // expected
        }
        $service->ensureCanAssignRole('Teacher'); // ordinary roles are fine

        // …and the privileged roles are not offered in the staff form.
        $offered = collect($service->getRolesByCategory())->flatten()->all();
        $this->assertNotContains('Super Admin', $offered);
        $this->assertNotContains('IT / System Admin', $offered);

        // A Super Admin can.
        $this->actingAs($this->userWithRole('Super Admin'));
        $service->ensureCanAssignRole('Super Admin');
        $this->assertTrue(true);
    }

    public function test_non_privileged_staff_manager_cannot_take_over_a_super_admin_account(): void
    {
        $registrar = $this->userWithRole('Registrar');
        $registrar->givePermissionTo(['manage employees', 'view employees']);
        $superAdmin = $this->employeeWithRole('Super Admin');
        $originalPassword = $superAdmin->user->password;

        $this->actingAs($registrar)->post("/admin/employees/{$superAdmin->id}/reset-password")->assertForbidden();
        $this->actingAs($registrar)->post("/admin/employees/{$superAdmin->id}/toggle-status")->assertForbidden();
        $this->actingAs($registrar)->delete("/admin/employees/{$superAdmin->id}");

        $this->assertSame($originalPassword, $superAdmin->user->fresh()->password);
        $this->assertSame('active', $superAdmin->fresh()->status);
        $this->assertNotNull($superAdmin->fresh(), 'Super Admin employee must not be deleted.');

        $this->actingAs($registrar);
        $this->expectException(AuthorizationException::class);
        app(EmployeeService::class)->updateEmployee($superAdmin->fresh(), ['email' => 'attacker@example.com']);
    }

    // ─── 2. Students and guardians: writes need the matching permission ────────────

    public function test_supervisor_with_only_view_students_cannot_change_students_or_guardians(): void
    {
        $supervisor = $this->userWithRole('Supervisor');
        $student = Student::factory()->create();
        $guardian = StudentGuardian::create([
            'student_id' => $student->id,
            'guardian_type' => 'primary',
            'first_name' => 'Abebe',
            'father_name' => 'Kebede',
            'grandfather_name' => 'Tessema',
            'relationship' => 'Father',
            'phone' => '0911000000',
        ]);

        $this->actingAs($supervisor)->get('/admin/students')->assertOk();
        $this->actingAs($supervisor)->get('/admin/students/create')->assertForbidden();
        $this->actingAs($supervisor)->post('/admin/students', [])->assertForbidden();
        $this->actingAs($supervisor)->get("/admin/students/{$student->id}/edit")->assertForbidden();
        $this->actingAs($supervisor)->put("/admin/students/{$student->id}", [])->assertForbidden();
        $this->actingAs($supervisor)->delete("/admin/students/{$student->id}")->assertForbidden();

        $this->actingAs($supervisor)->post('/admin/guardians', [])->assertForbidden();
        $this->actingAs($supervisor)->put("/admin/guardians/{$guardian->id}", [])->assertForbidden();
        $this->actingAs($supervisor)->delete("/admin/guardians/{$guardian->id}")->assertForbidden();

        $this->assertNotNull($student->fresh());
        $this->assertNotNull($guardian->fresh());
    }

    // ─── 3. Online exams: published only, one attempt ──────────────────────────────

    private function examForStudent(bool $published = true): array
    {
        $year = AcademicYear::factory()->active()->create();
        $grade = GradeLevel::factory()->create();
        [$section, $student] = $this->sectionWithStudent($year, $grade);
        $teacher = $this->userWithRole('Teacher');
        $assignment = TeacherAssignment::create([
            'teacher_id' => $teacher->id,
            'section_id' => $section->id,
            'subject_id' => Subject::factory()->create()->id,
            'academic_year_id' => $year->id,
        ]);
        $exam = AcademicActivity::create([
            'teacher_assignment_id' => $assignment->id,
            'type' => 'exam',
            'title' => 'Quiz 1',
            'due_date' => now()->addDay(),
            'max_score' => 10,
            'is_published' => $published,
            'created_by' => $teacher->id,
            'division_id' => $grade->division_id,
            'academic_year_id' => $year->id,
        ]);
        $question = ExamQuestion::create([
            'academic_activity_id' => $exam->id,
            'question_text' => '2 + 2 = ?',
            'type' => 'mcq',
            'options' => ['3', '4'],
            'correct_answer' => '4',
            'points' => 10,
            'order' => 1,
        ]);

        return [$exam, $question, $student];
    }

    public function test_student_cannot_retake_a_submitted_exam_to_change_the_score(): void
    {
        [$exam, $question, $student] = $this->examForStudent();
        $studentUser = $student->user;

        $this->actingAs($studentUser)->get("/student/activities/{$exam->id}/exam")->assertOk();
        $this->actingAs($studentUser)->post("/student/activities/{$exam->id}/exam", [
            'answers' => [$question->id => '3'], // wrong
        ])->assertRedirect();

        $submission = ActivitySubmission::where('academic_activity_id', $exam->id)->firstOrFail();
        $this->assertEquals(0, (float) $submission->score);

        // Second attempt with the right answer is refused and changes nothing.
        $this->actingAs($studentUser)->get("/student/activities/{$exam->id}/exam")
            ->assertRedirect(route('student.activities.show', $exam));
        $this->actingAs($studentUser)->post("/student/activities/{$exam->id}/exam", [
            'answers' => [$question->id => '4'],
        ])->assertRedirect(route('student.activities.show', $exam));

        $this->assertEquals(0, (float) $submission->fresh()->score);
        $this->assertSame(1, ActivitySubmission::where('academic_activity_id', $exam->id)->count());
    }

    public function test_student_cannot_open_or_submit_an_unpublished_exam(): void
    {
        [$exam, $question, $student] = $this->examForStudent(published: false);

        $this->actingAs($student->user)->get("/student/activities/{$exam->id}")->assertNotFound();
        $this->actingAs($student->user)->get("/student/activities/{$exam->id}/exam")->assertNotFound();
        $this->actingAs($student->user)->post("/student/activities/{$exam->id}/exam", [
            'answers' => [$question->id => '4'],
        ])->assertNotFound();

        $this->assertSame(0, ActivitySubmission::count());
    }

    // ─── 4/6. Homeroom attendance and conduct: own section only ────────────────────

    public function test_homeroom_teacher_cannot_take_attendance_for_another_section(): void
    {
        Notification::fake();
        $year = AcademicYear::factory()->active()->create();
        $grade = GradeLevel::factory()->create();
        $teacher = $this->userWithRole('Teacher');
        [$ownSection, $ownStudent] = $this->sectionWithStudent($year, $grade, $teacher);
        [$otherSection, $otherStudent] = $this->sectionWithStudent($year, $grade);

        // Another section entirely → refused.
        $this->actingAs($teacher)->post('/teacher/homeroom/attendance', [
            'section_id' => $otherSection->id,
            'attendance_date' => now()->toDateString(),
            'attendance' => [$otherStudent->id => 'absent'],
        ])->assertForbidden();

        // Own section, but smuggling in a student from another class → that row is ignored.
        $this->actingAs($teacher)->post('/teacher/homeroom/attendance', [
            'section_id' => $ownSection->id,
            'attendance_date' => now()->toDateString(),
            'attendance' => [$ownStudent->id => 'present', $otherStudent->id => 'absent'],
        ])->assertRedirect();

        $this->assertTrue(StudentAttendance::where('student_id', $ownStudent->id)->exists());
        $this->assertFalse(StudentAttendance::where('student_id', $otherStudent->id)->exists());
        Notification::assertNothingSent();
    }

    public function test_homeroom_teacher_cannot_write_conduct_for_students_outside_their_class(): void
    {
        $year = AcademicYear::factory()->active()->create();
        $grade = GradeLevel::factory()->create();
        $term = Term::factory()->create(['academic_year_id' => $year->id, 'is_grading_open' => true]);
        $teacher = $this->userWithRole('Teacher');
        [, $ownStudent] = $this->sectionWithStudent($year, $grade, $teacher);
        [, $otherStudent] = $this->sectionWithStudent($year, $grade);

        $this->actingAs($teacher)->post('/teacher/homeroom/behavior', [
            'academic_year_id' => $year->id,
            'term_id' => $term->id,
            'records' => [
                $ownStudent->id => ['conduct' => 'A', 'absent' => 1, 'comment' => 'Good'],
                $otherStudent->id => ['conduct' => 'C', 'absent' => 30, 'comment' => 'Tampered'],
            ],
        ]);

        $this->assertSame('A', StudentTermRecord::where('student_id', $ownStudent->id)->value('conduct_grade'));
        $this->assertFalse(StudentTermRecord::where('student_id', $otherStudent->id)->exists());
    }

    // ─── 5. Student uploads: allow-listed types, private disk ──────────────────────

    public function test_student_uploads_are_type_checked_and_kept_off_the_public_disk(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        [$exam, , $student] = $this->examForStudent();
        $exam->update(['type' => 'assignment']);

        $this->actingAs($student->user)->post("/student/activities/{$exam->id}/submit", [
            'attachments' => [UploadedFile::fake()->createWithContent('shell.php', '<?php system($_GET["c"]);')],
        ])->assertSessionHasErrors('attachments.0');
        $this->assertSame(0, ActivityAttachment::count());

        $this->actingAs($student->user)->post("/student/activities/{$exam->id}/submit", [
            'attachments' => [UploadedFile::fake()->create('essay.pdf', 20, 'application/pdf')],
        ])->assertSessionHasNoErrors();

        $path = ActivityAttachment::firstOrFail()->file_path;
        Storage::disk('local')->assertExists($path);
        Storage::disk('public')->assertMissing($path);
    }

    // ─── 7. Legacy public files moved to the private disk ──────────────────────────

    public function test_legacy_public_files_are_moved_to_the_private_disk(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        $employee = Employee::factory()->create();
        $path = "employees/documents/{$employee->employee_id}/contract.pdf";
        Storage::disk('public')->put($path, 'contract body');
        EmployeeDocument::create([
            'employee_id' => $employee->id,
            'type' => 'contract',
            'name' => 'contract.pdf',
            'file_path' => $path,
            'file_extension' => 'pdf',
            'file_size' => 13,
        ]);

        $this->artisan('storage:privatize-legacy', ['--dry-run' => true])->assertSuccessful();
        Storage::disk('public')->assertExists($path);

        $this->artisan('storage:privatize-legacy')->assertSuccessful();
        Storage::disk('public')->assertMissing($path);
        $this->assertSame('contract body', Storage::disk('local')->get($path));
    }

    // ─── 8. Deleting a staff document needs a real DELETE request ──────────────────

    public function test_staff_document_cannot_be_deleted_by_a_plain_link(): void
    {
        Storage::fake('local');
        $admin = $this->userWithRole('Super Admin');
        $employee = Employee::factory()->create();
        Storage::disk('local')->put('employees/documents/x/id.pdf', 'id');
        $document = EmployeeDocument::create([
            'employee_id' => $employee->id,
            'type' => 'id',
            'name' => 'id.pdf',
            'file_path' => 'employees/documents/x/id.pdf',
            'file_extension' => 'pdf',
            'file_size' => 2,
        ]);

        $this->actingAs($admin)->get("/admin/employees/documents/{$document->id}/delete")->assertNotFound();
        $this->assertNotNull($document->fresh());

        $this->actingAs($admin)->delete("/admin/employees/documents/{$document->id}")->assertRedirect();
        $this->assertNull($document->fresh());
        Storage::disk('local')->assertMissing('employees/documents/x/id.pdf');
    }
}
