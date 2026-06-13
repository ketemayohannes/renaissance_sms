<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SessionTimeout
{
    /**
     * Handle an incoming request.
     *
     * Check if the user has been inactive for longer than the session lifetime.
     * If so, log them out and redirect to login.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $timeout = config('session.lifetime') * 60; // Convert minutes to seconds
            $lastActivity = session('last_activity_time');

            if ($lastActivity && (time() - $lastActivity) > $timeout) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->with('status', 'Your session has expired due to inactivity. Please log in again.');
            }

            // Update last activity timestamp on every request
            session(['last_activity_time' => time()]);
        }

        return $next($request);
    }
}
