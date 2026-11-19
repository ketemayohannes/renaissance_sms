<?php

use App\Http\Middleware\ForcePasswordChange;
use App\Http\Middleware\NoCache;
use App\Http\Middleware\SessionTimeout;
use App\Http\Middleware\VerifyParentAccessToStudent;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->web(append: [
            SessionTimeout::class,
            ForcePasswordChange::class,
            NoCache::class,
        ]);
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'parent_access' => VerifyParentAccessToStudent::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Background fetch()/XHR calls (notification bell, chat polling, search
        // boxes...) that hit an expired session must get a 401, not a redirect.
        // A redirect would make Laravel remember that API URL as the "intended"
        // page, so after logging in the user lands on raw JSON instead of the
        // page they were on. Only real page navigations are sent to login.
        $exceptions->renderable(function (AuthenticationException $e, Request $request) {
            $fetchMode = $request->headers->get('Sec-Fetch-Mode');

            if ($request->expectsJson() || ($fetchMode !== null && $fetchMode !== 'navigate')) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
        });

        $exceptions->renderable(function (HttpException $e, Request $request) {
            if ($e->getStatusCode() === 419) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'message' => 'Your session has expired. Please log in again.',
                        'redirect' => route('login'),
                    ], 419);
                }

                return redirect()->route('login')
                    ->with('status', 'Your session has expired. Please log in again.');
            }

            if ($e->getStatusCode() === 403) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'message' => 'You do not have permission to access this page.',
                    ], 403);
                }

                if (app()->runningUnitTests()) {
                    return null;
                }

                $redirectTo = auth()->check() ? route('dashboard') : route('login');

                if ($request->hasSession()) {
                    $request->session()->flash('error', 'You do not have permission to access that page.');
                    $request->session()->save();
                }

                return redirect()->to($redirectTo);
            }
        });
    })->create();
