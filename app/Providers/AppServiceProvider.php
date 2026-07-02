<?php

namespace App\Providers;

use App\View\Composers\AttendanceProfileComposer;
use App\View\Composers\HeaderProfileComposer;
use App\View\Composers\ProjectManagementProfileComposer;
use App\View\Composers\SidebarPermissionComposer;
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
            'staff_attendance.overview.index',
            'staff_attendance.layouts.profile-header',
            'staff_attendance.components.card-analytics',
        ], AttendanceProfileComposer::class);

        View::composer('layouts.sidebar', SidebarPermissionComposer::class);
        View::composer('layouts.header', HeaderProfileComposer::class);
        View::composer('project_management.layouts.profile-header', ProjectManagementProfileComposer::class);
    }
}
