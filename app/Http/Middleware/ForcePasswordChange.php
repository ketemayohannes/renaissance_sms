<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && !is_null($user->getRawOriginal('temp_password')) && $user->getRawOriginal('temp_password') !== '') {
            if (!$request->routeIs('auth.change-password') && 
                !$request->routeIs('auth.change-password.update') && 
                !$request->routeIs('logout')) {
                
                return redirect()->route('auth.change-password')
                    ->with('warning', 'For security reasons, you must change your default password before proceeding.');
            }
        }

        return $next($request);
    }
}
