<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\ClassPeriod;
use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Models\GradeLevel;
use App\Models\Division;
use App\Models\LeaveRequest;
use App\Models\Section;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use App\Models\Timetable;
use App\Models\User;
use App\Services\StaffAvailabilityService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StaffHrModuleTest extends TestCase
{
    use RefreshDatabase;

    protected User $hrUser;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['manage staff attendance', 'manage leave requests', 'request leave', 'view staff availability'] as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $hrRole = Role::firstOrCreate(['name' => 'HR Manager']);
        $hrRole->givePermissionTo(['manage staff attendance', 'manage leave requests', 'view staff availability']);

        $teacherRole = Role::firstOrCreate(['name' => 'Teacher']);
        $teacherRole->givePermissionTo(['request leave']);

        $this->hrUser = User::factory()->create();
        $this->hrUser->assignRole('HR Manager');
    }

    /** @test */
    public function employee_attendance_model_resolves_the_singular_table_name(): void
    {
        // Regression: without protected $table the model queried the nonexistent
        // plural 'employee_attendances' and threw.
        $this->assertSame(0, EmployeeAttendance::count());

        $employee = Employee::factory()->create();
        EmployeeAttendance::create([
            'employee_id' => $employee->id,
            'attendance_date' => today(),
            'status' => 'present',
        ]);

        $this->assertSame(1, EmployeeAttendance::forDate(today()->toDateString())->count());
    }

    /** @test */
    public function teacher_can_submit_and_withdraw_a_leave_request(): void
    {
        $teacherUser = User::factory()->create();
        $teacherUser->assignRole('Teacher');
        $employee = Employee::factory()->create(['user_id' => $teacherUser->id]);

        $response = $this->actingAs($teacherUser)->post(route('teacher.leave.store'), [
            'leave_type' => 'sick',
            'start_date' => now()->addWeekday()->toDateString(),
            'end_date' => now()->addWeekdays(3)->toDateString(),
            'reason' => 'Medical appointment',
        ]);

        $response->assertSessionHas('success');
        $request = LeaveRequest::where('employee_id', $employee->id)->first();
        $this->assertNotNull($request);
        $this->assertSame('pending', $request->status);
        $this->assertGreaterThan(0, $request->total_days);

        // Overlapping second request is rejected.
        $this->actingAs($teacherUser)->post(route('teacher.leave.store'), [
            'leave_type' => 'casual',
            'start_date' => now()->addWeekday()->toDateString(),
            'end_date' => now()->addWeekdays(2)->toDateString(),
            'reason' => 'Overlap attempt',
        ])->assertSessionHas('error');
        $this->assertSame(1, LeaveRequest::where('employee_id', $employee->id)->count());

        // Withdraw while pending.
        $this->actingAs($teacherUser)->delete(route('teacher.leave.cancel', $request))
            ->assertSessionHas('success');
        $this->assertSame(0, LeaveRequest::count());
    }

    /** @test */
    public function hr_can_approve_a_leave_request_and_teacher_cannot_access_hr_screens(): void
    {
        $employee = Employee::factory()->create();
        $request = LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type' => 'annual',
            'start_date' => now()->addWeek(),
            'end_date' => now()->addWeeks(2),
            'total_days' => 5,
            'reason' => 'Vacation',
            'status' => 'pending',
        ]);

        $this->actingAs($this->hrUser)
            ->post(route('admin.hr.leave-requests.approve', $request), ['approval_remarks' => 'Enjoy'])
            ->assertSessionHas('success');

        $request->refresh();
        $this->assertSame('approved', $request->status);
        $this->assertSame($this->hrUser->id, $request->approved_by);

        // Deciding twice is blocked.
        $this->actingAs($this->hrUser)
            ->post(route('admin.hr.leave-requests.reject', $request))
            ->assertSessionHas('error');
        $this->assertSame('approved', $request->fresh()->status);

        // A teacher (holds student-attendance-adjacent perms, not staff HR ones)
        // must not reach any HR screen.
        $teacherUser = User::factory()->create();
        $teacherUser->assignRole('Teacher');
        $this->actingAs($teacherUser)->get(route('admin.hr.leave-requests.index'))->assertForbidden();
        $this->actingAs($teacherUser)->get(route('admin.hr.staff-attendance.index'))->assertForbidden();
        $this->actingAs($teacherUser)->get(route('admin.hr.availability.index'))->assertForbidden();
    }

    /** @test */
    public function attendance_register_saves_and_respects_approved_leave(): void
    {
        $present = Employee::factory()->create();
        $onLeave = Employee::factory()->create();

        LeaveRequest::create([
            'employee_id' => $onLeave->id,
            'leave_type' => 'sick',
            'start_date' => today()->subDay(),
            'end_date' => today()->addDay(),
            'total_days' => 3,
            'reason' => 'Sick',
            'status' => 'approved',
        ]);

        $this->actingAs($this->hrUser)->post(route('admin.hr.staff-attendance.store'), [
            'attendance_date' => today()->toDateString(),
            'entries' => [
                $present->id => ['status' => 'present', 'check_in' => '08:15'],
                // Attempt to override the on-leave employee — must be ignored.
                $onLeave->id => ['status' => 'present'],
            ],
        ])->assertSessionHas('success');

        $this->assertSame('present', EmployeeAttendance::where('employee_id', $present->id)->first()->status);
        $this->assertNull(EmployeeAttendance::where('employee_id', $onLeave->id)->first(), 'Approved leave must not be overwritten by the register.');
    }

    /** @test */
    public function availability_service_resolves_status_precedence_and_free_periods(): void
    {
        // Use a fixed Monday so day_of_week logic is deterministic.
        Carbon::setTestNow(Carbon::parse('next monday')->setTime(9, 0));
        $monday = Carbon::today();

        $division = Division::factory()->create();
        $year = AcademicYear::factory()->active()->create();
        $gradeLevel = GradeLevel::factory()->create(['division_id' => $division->id]);
        $section = Section::factory()->create(['grade_level_id' => $gradeLevel->id, 'academic_year_id' => $year->id]);
        $subject = Subject::factory()->create();

        $p1 = ClassPeriod::create(['name' => 'Period 1', 'start_time' => '08:30', 'end_time' => '09:15', 'is_break' => false, 'sort_order' => 1]);
        $p2 = ClassPeriod::create(['name' => 'Period 2', 'start_time' => '09:15', 'end_time' => '10:00', 'is_break' => false, 'sort_order' => 2]);
        ClassPeriod::create(['name' => 'Break', 'start_time' => '10:00', 'end_time' => '10:30', 'is_break' => true, 'sort_order' => 3]);

        // Teacher A: assignment + timetable row in Period 1 => P1 busy, P2 free.
        $teacherA = Employee::factory()->create();
        $assignmentA = TeacherAssignment::create([
            'teacher_id' => $teacherA->user_id,
            'section_id' => $section->id,
            'subject_id' => $subject->id,
            'academic_year_id' => $year->id,
        ]);
        Timetable::create([
            'academic_year_id' => $year->id,
            'section_id' => $section->id,
            'class_period_id' => $p1->id,
            'day_of_week' => 1,
            'teacher_assignment_id' => $assignmentA->id,
        ]);

        // Teacher B: has an assignment but NO timetable rows => must NOT read as free.
        $teacherB = Employee::factory()->create();
        TeacherAssignment::create([
            'teacher_id' => $teacherB->user_id,
            'section_id' => $section->id,
            'subject_id' => $subject->id,
            'academic_year_id' => $year->id,
        ]);

        // Guard: no assignments at all => non-teaching.
        $guard = Employee::factory()->nonTeaching()->create();

        // Statuses: A has an explicit register row (wins over everything);
        // B has an approved leave; guard has nothing.
        EmployeeAttendance::create([
            'employee_id' => $teacherA->id,
            'attendance_date' => $monday,
            'status' => 'late',
            'check_in' => '09:05',
        ]);
        LeaveRequest::create([
            'employee_id' => $teacherB->id,
            'leave_type' => 'annual',
            'start_date' => $monday->copy()->subDay(),
            'end_date' => $monday->copy()->addDay(),
            'total_days' => 3,
            'reason' => 'Trip',
            'status' => 'approved',
        ]);

        $service = app(StaffAvailabilityService::class);
        $result = $service->build(collect([$teacherA, $teacherB, $guard]), $monday);
        $board = $result['board']->keyBy(fn ($row) => $row['employee']->id);

        // Teacher A: register row wins; P1 busy, P2 free; break excluded from slots.
        $rowA = $board->get($teacherA->id);
        $this->assertSame('late', $rowA['status']['kind']);
        $this->assertSame('register', $rowA['status']['source']);
        $this->assertSame('scheduled', $rowA['teaching']['kind']);
        $this->assertSame(1, $rowA['teaching']['busy_count']);
        $this->assertSame(1, $rowA['teaching']['free_count']);
        $this->assertCount(2, $rowA['teaching']['slots']);

        // Teacher B: leave-request status; sparse-timetable guard.
        $rowB = $board->get($teacherB->id);
        $this->assertSame('on_leave', $rowB['status']['kind']);
        $this->assertSame('leave_request', $rowB['status']['source']);
        $this->assertSame('timetable_not_entered', $rowB['teaching']['kind']);

        // Guard: non-teaching, no record.
        $rowGuard = $board->get($guard->id);
        $this->assertSame('no_record', $rowGuard['status']['kind']);
        $this->assertSame('non_teaching', $rowGuard['teaching']['kind']);

        // Weekend: no load computation at all.
        $sunday = $monday->copy()->addDays(6);
        $weekend = $service->build(collect([$teacherA]), $sunday);
        $this->assertFalse($weekend['isSchoolDay']);
        $this->assertSame('no_school_day', $weekend['board']->first()['teaching']['kind']);

        Carbon::setTestNow();
    }

    /** @test */
    public function availability_page_renders_for_hr(): void
    {
        Employee::factory()->count(3)->create();

        $response = $this->actingAs($this->hrUser)->get(route('admin.hr.availability.index'));

        $response->assertOk();
        $response->assertSee('Staff Availability');
    }
}
