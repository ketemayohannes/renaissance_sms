<?php

namespace App\Services;

use App\Models\ClassPeriod;
use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Models\LeaveRequest;
use App\Models\TeacherAssignment;
use App\Models\Timetable;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Builds the HR availability board: for each staff member on a given date, their
 * attendance/leave status plus (for teaching staff) that day's timetable and free periods.
 *
 * Status precedence per employee:
 *   1. Explicit employee_attendance row for the date (HR daily register)
 *   2. Approved LeaveRequest covering the date
 *   3. Employee.status == 'on_leave' (static HR flag, undated)
 *   4. 'no_record'
 *
 * Free-period honesty guards (a wrong "free" is worse than no answer):
 *   - Weekends: no school day, no load/free computation at all.
 *   - Staff with no TeacherAssignments: teaching load "not applicable".
 *   - Teachers WITH assignments but ZERO timetable rows for the year: "timetable not
 *     entered" — never reported as fully free. The timetable is still sparsely
 *     populated, so absence of rows usually means "not entered yet", not "free all day".
 */
class StaffAvailabilityService
{
    /**
     * @param  Collection<int, Employee>  $employees  already filtered/scoped by the caller
     */
    public function build(Collection $employees, Carbon $date): array
    {
        $dateString = $date->toDateString();
        $dayOfWeek = (int) $date->format('N'); // 1=Mon .. 7=Sun
        $isSchoolDay = $dayOfWeek <= 5;

        $periods = ClassPeriod::orderBy('sort_order')->get();
        $teachingPeriods = $periods->where('is_break', false)->values();

        $employeeIds = $employees->pluck('id');
        $userIds = $employees->pluck('user_id')->filter();

        // ---- Status inputs (three bulk queries, no per-employee queries) ----
        $attendanceRows = EmployeeAttendance::forDate($dateString)
            ->whereIn('employee_id', $employeeIds)
            ->get()
            ->keyBy('employee_id');

        $leaveRows = LeaveRequest::approved()
            ->covering($dateString)
            ->whereIn('employee_id', $employeeIds)
            ->get()
            ->keyBy('employee_id');

        // ---- Teaching inputs ----
        // Which users have ANY assignment this year (distinguishes non-teaching staff
        // from teachers), and which have ANY timetable rows (distinguishes "free" from
        // "timetable not entered").
        $assignmentUserIds = TeacherAssignment::withoutGlobalScopes()
            ->whereIn('teacher_id', $userIds)
            ->distinct()
            ->pluck('teacher_id')
            ->flip();

        $allTimetableRows = Timetable::with(['classPeriod', 'teacherAssignment.subject', 'teacherAssignment.section.gradeLevel'])
            ->whereHas('teacherAssignment', fn ($q) => $q->withoutGlobalScopes()->whereIn('teacher_id', $userIds))
            ->get();

        $timetableUserIds = $allTimetableRows
            ->map(fn ($row) => $row->teacherAssignment->teacher_id)
            ->flip();

        $todayRowsByUser = $allTimetableRows
            ->where('day_of_week', $dayOfWeek)
            ->groupBy(fn ($row) => $row->teacherAssignment->teacher_id);

        // ---- Assemble ----
        $board = $employees->map(function (Employee $employee) use (
            $attendanceRows, $leaveRows, $assignmentUserIds, $timetableUserIds,
            $todayRowsByUser, $teachingPeriods, $isSchoolDay
        ) {
            return [
                'employee' => $employee,
                'status' => $this->resolveStatus($employee, $attendanceRows, $leaveRows),
                'teaching' => $this->resolveTeaching(
                    $employee, $assignmentUserIds, $timetableUserIds,
                    $todayRowsByUser, $teachingPeriods, $isSchoolDay
                ),
            ];
        });

        return [
            'board' => $board,
            'periods' => $teachingPeriods,
            'isSchoolDay' => $isSchoolDay,
            'currentPeriodId' => $this->currentPeriodId($periods, $date),
        ];
    }

    private function resolveStatus(Employee $employee, Collection $attendanceRows, Collection $leaveRows): array
    {
        if ($attendance = $attendanceRows->get($employee->id)) {
            return [
                'kind' => $attendance->status, // present|absent|late|half_day|on_leave
                'source' => 'register',
                'check_in' => $attendance->check_in,
                'check_out' => $attendance->check_out,
                'detail' => $attendance->remarks,
            ];
        }

        if ($leave = $leaveRows->get($employee->id)) {
            return [
                'kind' => 'on_leave',
                'source' => 'leave_request',
                'check_in' => null,
                'check_out' => null,
                'detail' => ucfirst($leave->leave_type) . ' leave until ' . $leave->end_date->format('M j'),
            ];
        }

        if ($employee->status === 'on_leave') {
            return [
                'kind' => 'on_leave',
                'source' => 'employee_flag',
                'check_in' => null,
                'check_out' => null,
                'detail' => 'Marked on leave in HR profile (no dated leave request)',
            ];
        }

        return [
            'kind' => 'no_record',
            'source' => null,
            'check_in' => null,
            'check_out' => null,
            'detail' => null,
        ];
    }

    private function resolveTeaching(
        Employee $employee,
        Collection $assignmentUserIds,
        Collection $timetableUserIds,
        Collection $todayRowsByUser,
        Collection $teachingPeriods,
        bool $isSchoolDay
    ): array {
        if (! $employee->user_id || ! $assignmentUserIds->has($employee->user_id)) {
            return ['kind' => 'non_teaching'];
        }

        if (! $isSchoolDay) {
            return ['kind' => 'no_school_day'];
        }

        if (! $timetableUserIds->has($employee->user_id)) {
            return ['kind' => 'timetable_not_entered'];
        }

        $busy = collect($todayRowsByUser->get($employee->user_id, collect()))
            ->keyBy('class_period_id')
            ->map(fn (Timetable $row) => [
                'subject' => $row->teacherAssignment->subject->name ?? 'N/A',
                'section' => trim(($row->teacherAssignment->section->gradeLevel->name ?? '') . ' ' . ($row->teacherAssignment->section->name ?? '')),
                'room' => $row->room_number,
            ]);

        $slots = $teachingPeriods->map(fn (ClassPeriod $period) => [
            'period' => $period,
            'busy' => $busy->get($period->id), // null = free
        ]);

        return [
            'kind' => 'scheduled',
            'slots' => $slots,
            'busy_count' => $busy->count(),
            'free_count' => $teachingPeriods->count() - $busy->count(),
        ];
    }

    /**
     * The period (teaching or break) whose time window contains "now" — only meaningful
     * when viewing today; the controller passes the requested date.
     */
    private function currentPeriodId(Collection $periods, Carbon $date): ?int
    {
        if (! $date->isToday()) {
            return null;
        }

        $now = Carbon::now()->format('H:i:s');

        foreach ($periods as $period) {
            $start = $period->start_time?->format('H:i:s');
            $end = $period->end_time?->format('H:i:s');
            if ($start && $end && $now >= $start && $now < $end) {
                return $period->id;
            }
        }

        return null;
    }
}
