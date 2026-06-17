<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->web(append: [
            \App\Http\Middleware\SessionTimeout::class,
            \App\Http\Middleware\ForcePasswordChange::class,
            \App\Http\Middleware\NoCache::class,
        ]);
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'parent_access' => \App\Http\Middleware\VerifyParentAccessToStudent::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->renderable(function (HttpException $e, Request $request) {
            if ($e->getStatusCode() === 419) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'message' => 'Your session has expired. Please log in again.',
                        'redirect' => route('login')
                    ], 419);
                }

                return redirect()->route('login')
                    ->with('status', 'Your session has expired. Please log in again.');
            }

            if ($e->getStatusCode() === 403) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'message' => 'You do not have permission to access this page.'
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
