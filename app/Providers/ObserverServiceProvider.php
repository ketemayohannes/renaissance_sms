<?php

namespace App\Providers;

use App\Models\Department;
use App\Models\Section;
use App\Observers\DepartmentObserver;
use App\Observers\SectionObserver;
use Illuminate\Support\ServiceProvider;

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
