<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        // Placeholder for future data widgets
        // $myClasses = Auth::user()->teacherAssignments ...
        
        return view('teacher.dashboard');
    }
}
