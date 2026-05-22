<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        if (!$user->hasRole('Student') || !$user->student) {
            return redirect()->route('dashboard');
        }

        $student = $user->student;
        $student->load([
            'guardians',
            'medicalInfo',
            'transportation',
            'enrollments.section.gradeLevel.division',
            'enrollments.academicYear'
        ]);
        
        return view('student.profile.show', compact('student', 'user'));
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password updated successfully.');
    }
}
