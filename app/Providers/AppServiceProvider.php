<?php

namespace App\Providers;

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
        \App\Models\UnitTask::observe(\App\Observers\UnitTaskObserver::class);
        \App\Models\UnitStage::observe(\App\Observers\UnitStageObserver::class);
    }
}
