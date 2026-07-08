<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StaffAttendanceController extends Controller
{
    /**
     * Daily register: every active employee for one date, with quick status entry.
     */
    public function index(Request $request)
    {
        $date = $this->resolveDate($request);

        $employees = Employee::where('status', '!=', 'resigned')
            ->where('status', '!=', 'terminated')
            ->orderBy('first_name')
            ->get();

        $records = EmployeeAttendance::forDate($date)
            ->whereIn('employee_id', $employees->pluck('id'))
            ->get()
            ->keyBy('employee_id');

        // Employees with an approved leave covering this date: locked to on_leave in the
        // register (the leave module is the source of truth for leave; the register must
        // not contradict it).
        $onLeaveIds = LeaveRequest::approved()
            ->covering($date)
            ->pluck('employee_id')
            ->flip();

        return view('admin.hr.staff-attendance.index', [
            'date' => $date,
            'employees' => $employees,
            'records' => $records,
            'onLeaveIds' => $onLeaveIds,
            'statuses' => EmployeeAttendance::STATUSES,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'attendance_date' => 'required|date|before_or_equal:today',
            'entries' => 'required|array',
            'entries.*.status' => 'nullable|in:' . implode(',', EmployeeAttendance::STATUSES),
            'entries.*.check_in' => 'nullable|date_format:H:i',
            'entries.*.check_out' => 'nullable|date_format:H:i|after:entries.*.check_in',
            'entries.*.remarks' => 'nullable|string|max:500',
        ]);

        $date = Carbon::parse($request->attendance_date)->toDateString();

        $onLeaveIds = LeaveRequest::approved()->covering($date)->pluck('employee_id')->flip();

        $saved = 0;
        foreach ($request->entries as $employeeId => $entry) {
            // Approved leave wins: ignore any submitted status for those employees.
            if ($onLeaveIds->has((int) $employeeId)) {
                continue;
            }

            if (empty($entry['status'])) {
                continue; // Untouched row — leave whatever exists (or nothing) in place.
            }

            EmployeeAttendance::updateOrCreate(
                ['employee_id' => $employeeId, 'attendance_date' => $date],
                [
                    'status' => $entry['status'],
                    'check_in' => $entry['check_in'] ?? null,
                    'check_out' => $entry['check_out'] ?? null,
                    'remarks' => $entry['remarks'] ?? null,
                ]
            );
            $saved++;
        }

        return back()->with('success', "Attendance saved for {$saved} staff member(s).");
    }

    private function resolveDate(Request $request): string
    {
        try {
            $date = Carbon::parse($request->get('date', 'today'));
        } catch (\Throwable) {
            $date = Carbon::today();
        }

        // The register records what happened, not the future.
        if ($date->isFuture()) {
            $date = Carbon::today();
        }

        return $date->toDateString();
    }
}
