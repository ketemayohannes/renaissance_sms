<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Section;
use App\Models\Department;
use App\Observers\SectionObserver;
use App\Observers\DepartmentObserver;

class ObserverServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Section::observe(SectionObserver::class);
        Department::observe(DepartmentObserver::class);
    }
}
