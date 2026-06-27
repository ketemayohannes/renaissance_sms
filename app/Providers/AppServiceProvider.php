<?php

namespace App\Providers;

use App\Models\Student;
use App\Models\Conversation;
use App\Policies\StudentPolicy;
use App\Policies\ReportPolicy;
use App\Policies\ConversationPolicy;
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

        // Register policies
        Gate::policy(Conversation::class, ConversationPolicy::class);
        
        // Load Communication Settings from Database (Cached).
        // Must run in BOTH web AND console (queue worker) contexts so that the
        // mail/SMS config values are available when queued notification jobs run.
        try {
            if (\Schema::hasTable('communication_settings')) {
                $settings = \Cache::rememberForever('communication_settings', function() {
                    return \App\Models\CommunicationSetting::first();
                });
                if ($settings) {
                    $settings->applyConfigurations();
                }
            }
        } catch (\Exception $e) {
            // Prevent failures during initial migrations or artisan commands
            // that run before the table exists (e.g. migrate).
        }

        // Load School Timezone from app_settings (Cached).
        // This ensures all timestamps, scheduled jobs, and audit logs
        // run on the school's local time (default: Africa/Addis_Ababa).
        try {
            if (\Schema::hasTable('app_settings')) {
                $timezone = \Cache::remember('app_settings_timezone', now()->addDay(), function () {
                    return \App\Models\AppSetting::get('school.timezone', 'Africa/Addis_Ababa');
                });
                if ($timezone && in_array($timezone, \DateTimeZone::listIdentifiers())) {
                    config(['app.timezone' => $timezone]);
                    date_default_timezone_set($timezone);
                }
            }
        } catch (\Exception $e) {
            // Graceful degradation — fall back to .env timezone
        }

        // Force HTTPS in production (Disabled for local Docker deployment to avoid ERR_SSL_PROTOCOL_ERROR)
        // if (config('app.env') === 'production') {
        //     \Illuminate\Support\Facades\URL::forceScheme('https');
        // }
    }
}
