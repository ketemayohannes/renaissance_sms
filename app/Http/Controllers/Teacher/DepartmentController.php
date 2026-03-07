<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Services\TeacherService;
use App\Services\DepartmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepartmentController extends Controller
{
    protected $teacherService;
    protected $departmentService;

    public function __construct(TeacherService $teacherService, DepartmentService $departmentService)
    {
        $this->teacherService = $teacherService;
        $this->departmentService = $departmentService;
    }

    /**
     * Display department result analysis dashboard.
     */
    public function index()
    {
        $user = Auth::user();
        $departments = $this->teacherService->getHeadedDepartments($user);

        if ($departments->isEmpty()) {
            return redirect()->route('teacher.dashboard')->with('error', 'Unauthorized access.');
        }

        // For now, we'll show analysis for the first department if they head multiple
        $department = $departments->first();
        $analysis = $this->departmentService->getResultAnalysis($department);

        return view('teacher.department.index', compact('department', 'analysis', 'departments'));
    }

    /**
     * Show analysis for a specific department.
     */
    public function show($id)
    {
        $user = Auth::user();
        $department = \App\Models\Department::findOrFail($id);

        if ($department->head_id !== $user->id) {
            abort(403, 'Unauthorized access.');
        }

        $analysis = $this->departmentService->getResultAnalysis($department);
        $departments = $this->teacherService->getHeadedDepartments($user);

        return view('teacher.department.index', compact('department', 'analysis', 'departments'));
    }
}
