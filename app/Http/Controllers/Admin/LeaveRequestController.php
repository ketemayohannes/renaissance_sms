<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Notifications\LeaveRequestDecided;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = LeaveRequest::with(['employee', 'approver'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('leave_type'), fn ($q) => $q->where('leave_type', $request->leave_type))
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->whereHas('employee', function ($eq) use ($request) {
                    $eq->withoutGlobalScopes()
                        ->where(function ($w) use ($request) {
                            $w->where('first_name', 'like', "%{$request->search}%")
                                ->orWhere('last_name', 'like', "%{$request->search}%")
                                ->orWhere('employee_id', 'like', "%{$request->search}%");
                        });
                });
            })
            // Pending first, newest within each status.
            ->orderByRaw("FIELD(status, 'pending', 'approved', 'rejected')")
            ->orderByDesc('created_at');

        $requests = $query->paginate(20)->withQueryString();

        $employees = Employee::where('status', 'active')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'middle_name', 'last_name', 'employee_id']);

        return view('admin.hr.leave-requests.index', [
            'requests' => $requests,
            'employees' => $employees,
            'leaveTypes' => LeaveRequest::TYPES,
            'pendingCount' => LeaveRequest::pending()->count(),
        ]);
    }

    /**
     * HR files a request on behalf of staff without portal access (guards, janitors…).
     * Created as pending — approval stays an explicit, separate act even for HR.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_type' => 'required|in:' . implode(',', LeaveRequest::TYPES),
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
        ]);

        $start = Carbon::parse($validated['start_date']);
        $end = Carbon::parse($validated['end_date']);

        $totalDays = LeaveRequest::weekdayCount($start, $end);
        if ($totalDays === 0) {
            return back()->withInput()->with('error', 'The selected range contains no school days (Mon–Fri).');
        }

        $overlap = LeaveRequest::where('employee_id', $validated['employee_id'])
            ->whereIn('status', ['pending', 'approved'])
            ->whereDate('start_date', '<=', $end)
            ->whereDate('end_date', '>=', $start)
            ->exists();

        if ($overlap) {
            return back()->withInput()->with('error', 'This employee already has a pending or approved leave request overlapping these dates.');
        }

        LeaveRequest::create([
            'employee_id' => $validated['employee_id'],
            'leave_type' => $validated['leave_type'],
            'start_date' => $start,
            'end_date' => $end,
            'total_days' => $totalDays,
            'reason' => $validated['reason'],
            'status' => 'pending',
        ]);

        return back()->with('success', 'Leave request recorded (pending approval).');
    }

    public function approve(Request $request, LeaveRequest $leaveRequest)
    {
        return $this->decide($request, $leaveRequest, 'approved');
    }

    public function reject(Request $request, LeaveRequest $leaveRequest)
    {
        return $this->decide($request, $leaveRequest, 'rejected');
    }

    private function decide(Request $request, LeaveRequest $leaveRequest, string $decision)
    {
        if ($leaveRequest->status !== 'pending') {
            return back()->with('error', 'This request has already been decided.');
        }

        $request->validate(['approval_remarks' => 'nullable|string|max:1000']);

        $leaveRequest->update([
            'status' => $decision,
            'approved_by' => auth()->id(),
            'approval_remarks' => $request->approval_remarks,
        ]);

        // Notify the requester (if their employee record is linked to a login).
        $user = $leaveRequest->employee?->user;
        if ($user) {
            try {
                $user->notify(new LeaveRequestDecided($leaveRequest->fresh()));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Leave decision notification failed: ' . $e->getMessage());
            }
        }

        return back()->with('success', "Leave request {$decision}.");
    }
}
