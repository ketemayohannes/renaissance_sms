<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class VerifyParentAccessToStudent
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $student = $request->route('student');
        if ($student) {
            $studentId = is_numeric($student) ? $student : $student->id;
            $user = Auth::user();
            // Guard against missing user (should be auth protected already)
            if (!$user) {
                abort(403, 'Unauthenticated');
            }
            $hasAccess = $user->guardianProfiles()
                ->where('student_id', $studentId)
                ->exists();
            if (! $hasAccess) {
                abort(403, 'Unauthorized access to student record.');
            }
        }

        return $next($request);
    }
}
