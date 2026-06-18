<?php

namespace App\Providers;

use App\View\Composers\AttendanceProfileComposer;
use Illuminate\Support\Facades\View;
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
        View::composer([
            'attendance.layouts.profile-header',
            'attendance.components.card-analytics',
        ], AttendanceProfileComposer::class);
    }
}
