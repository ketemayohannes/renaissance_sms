<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    protected $teacherService;

    public function __construct(\App\Services\TeacherService $teacherService)
    {
        $this->teacherService = $teacherService;
    }

    public function index()
    {
        $metrics = $this->teacherService->getDashboardMetrics(Auth::user());
        
        return view('teacher.dashboard', compact('metrics'));
    }
}
