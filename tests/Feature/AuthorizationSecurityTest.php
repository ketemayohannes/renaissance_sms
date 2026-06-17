<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\AcademicActivity;
use App\Models\TeacherAssignment;
use App\Models\Section;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\Notice;
use App\Models\StudentGuardian;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AuthorizationSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Seed roles and permissions
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    /**
     * Create a user with a specific role.
     */
    private function createUserWithRole(string $roleName): User
    {
        $user = User::factory()->create();
        $user->assignRole($roleName);
        return $user;
    }

    // ─── ADMIN PORTAL ACCESS TESTS ──────────────────────────────────────────

    /**
     * @dataProvider nonAdminRolesProvider
     */
    public function test_non_admin_roles_cannot_access_admin_dashboard(string $role): void
    {
        $user = $this->createUserWithRole($role);

        $response = $this->actingAs($user)->get('/admin/dashboard');

        $response->assertStatus(403);
    }

    public static function nonAdminRolesProvider(): array
    {
        return [
            'Teacher' => ['Teacher'],
            'Assistant Teacher' => ['Assistant Teacher'],
            'Homeroom Teacher' => ['Homeroom Teacher'],
            'Department Head' => ['Department Head'],
            'Secretary' => ['Secretary'],
            'School Nurse' => ['School Nurse'],
            'Senior Finance Officer' => ['Senior Finance Officer'],
            'Junior Finance Officer' => ['Junior Finance Officer'],
            'HR Manager' => ['HR Manager'],
            'Librarian' => ['Librarian'],
            'Student' => ['Student'],
            'Parent' => ['Parent'],
        ];
    }

    /**
     * @dataProvider adminRolesProvider
     */
    public function test_admin_roles_can_access_admin_dashboard(string $role): void
    {
        $user = $this->createUserWithRole($role);

        $response = $this->actingAs($user)->get('/admin/dashboard');

        $response->assertStatus(200);
    }

    public static function adminRolesProvider(): array
    {
        return [
            'Super Admin' => ['Super Admin'],
            'Principal' => ['Principal'],
            'Vice Principal' => ['Vice Principal'],
            'Supervisor' => ['Supervisor'],
        ];
    }

    // ─── ADMIN NOTICES (CRITICAL: the original exploit) ─────────────────────

    public function test_teacher_cannot_access_admin_notices(): void
    {
        $user = $this->createUserWithRole('Teacher');

        $response = $this->actingAs($user)->get('/admin/notices');

        $response->assertStatus(403);
    }

    public function test_super_admin_can_access_admin_notices(): void
    {
        $user = $this->createUserWithRole('Super Admin');

        $response = $this->actingAs($user)->get('/admin/notices');

        $response->assertStatus(200);
    }

    // ─── ADMIN STUDENT ROUTES ───────────────────────────────────────────────

    public function test_teacher_cannot_access_admin_students(): void
    {
        $user = $this->createUserWithRole('Teacher');

        $response = $this->actingAs($user)->get('/admin/students');

        $response->assertStatus(403);
    }

    // ─── ADMIN EMPLOYEE ROUTES ──────────────────────────────────────────────

    public function test_teacher_cannot_access_admin_employees(): void
    {
        $user = $this->createUserWithRole('Teacher');

        $response = $this->actingAs($user)->get('/admin/employees');

        $response->assertStatus(403);
    }

    // ─── ADMIN PROMOTIONS ───────────────────────────────────────────────────

    public function test_teacher_cannot_access_admin_promotions(): void
    {
        $user = $this->createUserWithRole('Teacher');

        $response = $this->actingAs($user)->get('/admin/promotions');

        $response->assertStatus(403);
    }

    // ─── TEACHER PORTAL ACCESS TESTS ────────────────────────────────────────

    public function test_teacher_can_access_teacher_dashboard(): void
    {
        $user = $this->createUserWithRole('Teacher');

        $response = $this->actingAs($user)->get('/teacher/dashboard');

        // Should not be 403
        $this->assertNotEquals(403, $response->status());
    }

    public function test_student_cannot_access_teacher_portal(): void
    {
        $user = $this->createUserWithRole('Student');

        $response = $this->actingAs($user)->get('/teacher/dashboard');

        $response->assertStatus(403);
    }

    public function test_parent_cannot_access_teacher_portal(): void
    {
        $user = $this->createUserWithRole('Parent');

        $response = $this->actingAs($user)->get('/teacher/dashboard');

        $response->assertStatus(403);
    }

    // ─── STUDENT PORTAL ACCESS TESTS ────────────────────────────────────────

    public function test_teacher_cannot_access_student_portal(): void
    {
        $user = $this->createUserWithRole('Teacher');

        $response = $this->actingAs($user)->get('/student/dashboard');

        $response->assertStatus(403);
    }

    // ─── PARENT PORTAL ACCESS TESTS ─────────────────────────────────────────

    public function test_teacher_cannot_access_parent_portal(): void
    {
        $user = $this->createUserWithRole('Teacher');

        $response = $this->actingAs($user)->get('/parent/dashboard');

        $response->assertStatus(403);
    }

    // ─── CROSS-PORTAL COMPREHENSIVE TEST ────────────────────────────────────

    /**
     * Ensure no non-admin role can access any critical admin endpoint.
     *
     * @dataProvider criticalAdminEndpointsProvider
     */
    public function test_teacher_cannot_access_critical_admin_endpoints(string $endpoint): void
    {
        $user = $this->createUserWithRole('Teacher');

        $response = $this->actingAs($user)->get($endpoint);

        $response->assertStatus(403);
    }

    public static function criticalAdminEndpointsProvider(): array
    {
        return [
            'admin dashboard' => ['/admin/dashboard'],
            'admin notices' => ['/admin/notices'],
            'admin notices create' => ['/admin/notices/create'],
            'admin students' => ['/admin/students'],
            'admin employees' => ['/admin/employees'],
            'admin promotions' => ['/admin/promotions'],
            'admin audit logs' => ['/admin/audit-logs'],
            'admin attendance' => ['/admin/attendance'],
            'admin timetable' => ['/admin/timetable'],
            'admin disciplinary' => ['/admin/disciplinary'],
            'admin gradebook' => ['/admin/gradebook'],
            'admin finance fees' => ['/admin/finance/fees'],
            'admin finance payroll' => ['/admin/finance/payroll'],
            'admin exams' => ['/admin/exams'],
            'admin section-grades' => ['/admin/section-grades'],
            'admin report-cards settings' => ['/admin/report-cards/settings'],
            'admin academic-reports' => ['/admin/academic-reports'],
            'admin result-analysis' => ['/admin/result-analysis'],
            'admin id-card-settings' => ['/admin/id-card-settings'],
        ];
    }

    // ─── IDOR TESTS ─────────────────────────────────────────────────────────

    public function test_student_cannot_access_other_sections_activities_or_exams(): void
    {
        // Setup academic structure
        $academicYear = AcademicYear::factory()->active()->create();
        $gradeLevel = GradeLevel::factory()->create();
        
        // Setup Section A and Student A
        $sectionA = Section::factory()->create(['grade_level_id' => $gradeLevel->id, 'academic_year_id' => $academicYear->id]);
        $studentAUser = $this->createUserWithRole('Student');
        $studentA = Student::factory()->create(['user_id' => $studentAUser->id]);
        $studentA->sections()->attach($sectionA->id, [
            'academic_year_id' => $academicYear->id,
            'enrollment_date' => now(),
            'status' => 'active',
        ]);

        // Setup Section B and Teacher Assignment B and Activity B
        $sectionB = Section::factory()->create(['grade_level_id' => $gradeLevel->id, 'academic_year_id' => $academicYear->id]);
        $teacherUser = $this->createUserWithRole('Teacher');
        $teacherAssignmentB = TeacherAssignment::create([
            'teacher_id' => $teacherUser->id,
            'section_id' => $sectionB->id,
            'subject_id' => \App\Models\Subject::factory()->create()->id,
            'academic_year_id' => $academicYear->id,
        ]);
        $activityB = AcademicActivity::create([
            'teacher_assignment_id' => $teacherAssignmentB->id,
            'type' => 'assignment',
            'title' => 'Sec B Activity',
            'due_date' => now()->addDays(2),
            'is_published' => true,
            'created_by' => $teacherUser->id,
            'division_id' => $gradeLevel->division_id,
            'academic_year_id' => $academicYear->id,
        ]);

        // Student A tries to view Activity B
        $response = $this->actingAs($studentAUser)->get("/student/activities/{$activityB->id}");
        $response->assertStatus(403);

        // Student A tries to submit Activity B
        $response = $this->actingAs($studentAUser)->post("/student/activities/{$activityB->id}/submit", [
            'attachments' => []
        ]);
        $response->assertStatus(403);

        // Student A tries to take Exam B
        $response = $this->actingAs($studentAUser)->get("/student/activities/{$activityB->id}/exam");
        $response->assertStatus(403);

        // Student A tries to submit Exam B
        $response = $this->actingAs($studentAUser)->post("/student/activities/{$activityB->id}/exam", [
            'answers' => []
        ]);
        $response->assertStatus(403);
    }

    public function test_parent_cannot_view_non_parent_notices(): void
    {
        $adminUser = $this->createUserWithRole('Super Admin');
        
        // Notice targeted to Teacher
        $notice = Notice::create([
            'title' => 'Teacher Only Notice',
            'content' => 'Meeting at 3 PM today.',
            'posted_by' => $adminUser->id,
            'publish_date' => now(),
            'target_audience' => 'Teacher',
            'is_active' => true,
        ]);

        $parentUser = $this->createUserWithRole('Parent');

        // Parent tries to view show page of notice
        $response = $this->actingAs($parentUser)->get("/parent/notices/{$notice->id}");
        
        // It redirects back to parent notices index with an error session message
        $response->assertRedirect(route('parent.notices.index'));
        $response->assertSessionHas('error');
    }
}
