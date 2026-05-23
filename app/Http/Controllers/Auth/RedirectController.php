<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // 1. Administration & High-Level Roles
        if ($user->hasRole(['Super Admin', 'Principal', 'IT / System Admin', 'Registrar', 'General Manager', 'Vice Principal', 'Supervisor'])) {
            return redirect()->route('admin.dashboard');
        }

        // 2. Teaching Staff
        if ($user->hasRole(['Teacher', 'Assistant Teacher'])) {
            return redirect()->route('teacher.dashboard');
        }

        // 3. Students
        if ($user->hasRole('Student')) {
            return redirect()->route('student.dashboard');
        }

        // 4. Parents (Future)
        if ($user->hasRole('Parent')) {
            return redirect()->route('parent.dashboard');
        }

        // 5. Staff / Other Employees (Future)
        if ($user->hasRole(['HR Manager', 'Senior Finance Officer', 'Junior Finance Officer', 'Librarian', 'School Nurse', 'Inventory Manager'])) {
             // return redirect()->route('employee.dashboard');
        }

        // Default Fallback
        return view('dashboard');
    }
}
