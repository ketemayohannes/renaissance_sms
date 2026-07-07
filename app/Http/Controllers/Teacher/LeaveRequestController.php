<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveRequestController extends Controller
{
    /**
     * "My Leave": the staff member's own request history + submission form.
     */
    public function index()
    {
        $employee = $this->currentEmployee();
        if (! $employee) {
            return redirect()->route('teacher.dashboard')
                ->with('error', 'No employee record is linked to your account. Please contact HR.');
        }

        $requests = LeaveRequest::where('employee_id', $employee->id)
            ->with('approver')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('teacher.leave.index', [
            'requests' => $requests,
            'leaveTypes' => LeaveRequest::TYPES,
        ]);
    }

    public function store(Request $request)
    {
        $employee = $this->currentEmployee();
        if (! $employee) {
            return back()->with('error', 'No employee record is linked to your account. Please contact HR.');
        }

        $validated = $request->validate([
            'leave_type' => 'required|in:' . implode(',', LeaveRequest::TYPES),
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
        ]);

        $start = Carbon::parse($validated['start_date']);
        $end = Carbon::parse($validated['end_date']);

        $totalDays = LeaveRequest::weekdayCount($start, $end);
        if ($totalDays === 0) {
            return back()->withInput()->with('error', 'The selected range contains no school days (Mon–Fri).');
        }

        // No second pending/approved request overlapping the same dates.
        $overlap = LeaveRequest::where('employee_id', $employee->id)
            ->whereIn('status', ['pending', 'approved'])
            ->whereDate('start_date', '<=', $end)
            ->whereDate('end_date', '>=', $start)
            ->exists();

        if ($overlap) {
            return back()->withInput()->with('error', 'You already have a pending or approved leave request overlapping these dates.');
        }

        LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type' => $validated['leave_type'],
            'start_date' => $start,
            'end_date' => $end,
            'total_days' => $totalDays,
            'reason' => $validated['reason'],
            'status' => 'pending',
        ]);

        return back()->with('success', 'Leave request submitted for approval.');
    }

    /**
     * A requester may withdraw their own request only while it is still pending.
     */
    public function cancel(LeaveRequest $leaveRequest)
    {
        $employee = $this->currentEmployee();

        if (! $employee || $leaveRequest->employee_id !== $employee->id) {
            abort(403);
        }

        if ($leaveRequest->status !== 'pending') {
            return back()->with('error', 'Only pending requests can be withdrawn.');
        }

        $leaveRequest->delete();

        return back()->with('success', 'Leave request withdrawn.');
    }

    private function currentEmployee(): ?Employee
    {
        return Employee::withoutGlobalScopes()->where('user_id', Auth::id())->first();
    }
}
