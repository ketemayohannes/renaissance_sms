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

        // 5. HR Manager: lands on the staff availability board (their portal home).
        if ($user->hasRole('HR Manager')) {
            return redirect()->route('admin.hr.availability.index');
        }

        // 6. Inventory Manager: lands on the inventory dashboard (their portal home).
        if ($user->hasRole('Inventory Manager')) {
            return redirect()->route('admin.inventory.dashboard');
        }

        // 7. Librarian: lands on the library catalog (their portal home).
        if ($user->hasRole('Librarian')) {
            return redirect()->route('admin.library.index');
        }

        // 8. Staff / Other Employees (Future)
        if ($user->hasRole(['Senior Finance Officer', 'Junior Finance Officer', 'School Nurse'])) {
             // return redirect()->route('employee.dashboard');
        }

        // Default Fallback
        return view('dashboard');
    }
}
