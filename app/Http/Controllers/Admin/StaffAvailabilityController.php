<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Services\StaffAvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StaffAvailabilityController extends Controller
{
    public function index(Request $request, StaffAvailabilityService $service)
    {
        try {
            $date = Carbon::parse($request->get('date', 'today'));
        } catch (\Throwable) {
            $date = Carbon::today();
        }

        // Employee's division scope applies naturally here: HR Manager (bypassing role)
        // sees the whole school; a Principal sees their division's staff.
        $employees = Employee::where('status', '!=', 'resigned')
            ->where('status', '!=', 'terminated')
            ->when($request->filled('department'), fn ($q) => $q->where('department', $request->department))
            ->when($request->filled('designation'), fn ($q) => $q->where('designation', $request->designation))
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where(function ($w) use ($request) {
                    $w->where('first_name', 'like', "%{$request->search}%")
                        ->orWhere('last_name', 'like', "%{$request->search}%")
                        ->orWhere('employee_id', 'like', "%{$request->search}%");
                });
            })
            ->orderBy('first_name')
            ->get();

        $availability = $service->build($employees, $date);

        $departments = Employee::whereNotNull('department')->distinct()->orderBy('department')->pluck('department');
        $designations = Employee::distinct()->orderBy('designation')->pluck('designation');

        return view('admin.hr.availability.index', array_merge($availability, [
            'date' => $date,
            'departments' => $departments,
            'designations' => $designations,
        ]));
    }
}
