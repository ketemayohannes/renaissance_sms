<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StudentAccountController extends Controller
{
    public function createStudentUser(Student $student)
    {
        // Check if student already has user
        if ($student->user_id) {
            return back()->with('error', 'Student already has a portal account.');
        }

        // Check for email
        if (empty($student->email)) {
            // Option: Generate a dummy email or fail?
            // For now, let's require an email or generate one based on ID.
            // Let's generate one if missing: ID@school.com (Example)
            // Better behavior: Require email update first or use generated.
            // Let's use generated if empty: studentID@domain
            $email = $student->admission_number.'@renaissance.edu';
            // return back()->with('error', 'Student must have an email address to create a portal account.');
        } else {
            $email = $student->email;
        }

        // Check if user exists with this email
        $existingUser = User::where('email', $email)->first();

        $password = null;

        DB::transaction(function () use ($student, $email, $existingUser, &$password) {
            if ($existingUser) {
                // Link to existing user
                $student->update(['user_id' => $existingUser->id]);

                // Ensure role
                if (! $existingUser->hasRole('Student')) {
                    $existingUser->assignRole('Student');
                }
            } else {
                // Create new user
                $password = Str::random(10);

                $user = User::create([
                    'name' => $student->full_name,
                    'email' => $email,
                    'password' => Hash::make($password),
                    'temp_password' => $password,
                ]);

                $user->assignRole('Student');
                $student->update(['user_id' => $user->id]);
            }
        });

        if ($password) {
            return back()->with('success', 'Portal account created. Password: '.$password);
        } else {
            return back()->with('success', 'Portal account linked successfully.');
        }
    }

    public function resetStudentPassword(Student $student)
    {
        if (! $student->user_id || ! $student->user) {
            return back()->with('error', 'Student does not have a portal account.');
        }

        $password = Str::random(10);
        $student->user->update([
            'password' => Hash::make($password),
            'temp_password' => $password,
        ]);

        return back()->with('success', 'Password reset successfully. New Password: '.$password);
    }
}
