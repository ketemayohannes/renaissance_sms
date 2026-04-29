<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Auth;

class PasswordChangeController extends Controller
{
    /**
     * Show the mandatory password change form.
     */
    public function show()
    {
        // Only allow if they actually have a temp password
        if (!Auth::user()->temp_password) {
            return redirect()->route('dashboard');
        }

        return view('auth.force-change-password');
    }

    /**
     * Update the user's password and clear the temp flag.
     */
    public function update(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()->min(8)],
        ]);

        $user = Auth::user();
        
        // Update password and clear temp flag
        $user->update([
            'password' => Hash::make($request->password),
            'temp_password' => null, // Clear the temporary password flag
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Your password has been successfully updated. You now have full access.');
    }
}
