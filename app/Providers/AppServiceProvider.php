<?php

namespace App\Providers;

use App\Models\Student;
use App\Policies\StudentPolicy;
use App\Policies\ReportPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Enforce strict mode to prevent N+1 queries during local development
        \Illuminate\Database\Eloquent\Model::preventLazyLoading(! app()->isProduction());
        
        // Force HTTPS in production (Fix mixed content on Render)
        if (config('app.env') === 'production') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}
