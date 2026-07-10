<?php

namespace App\Providers;

use App\Support\Branding\HostBrandingResolver;
use App\View\Composers\AttendanceProfileComposer;
use App\View\Composers\HeaderProfileComposer;
use App\View\Composers\ProjectManagementProfileComposer;
use App\View\Composers\SidebarPermissionComposer;
use Illuminate\Auth\SessionGuard;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Support\Facades\Auth;
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
        $webGuard = Auth::guard('web');
        if ($webGuard instanceof SessionGuard) {
            $webGuard->setRememberDuration((int) config('auth.remember_me_lifetime', 10080));
        }

        View::composer([
            'staff_attendance.overview.index',
            'staff_attendance.layouts.profile-header',
            'staff_attendance.components.card-analytics',
        ], AttendanceProfileComposer::class);

        View::composer('layouts.sidebar', SidebarPermissionComposer::class);
        View::composer('layouts.header', HeaderProfileComposer::class);
        View::composer('project_management.layouts.profile-header', ProjectManagementProfileComposer::class);
        View::composer([
            'layouts.main',
            'layouts.mainhead',
            'auth.login',
        ], function (ViewContract $view): void {
            $brand = app(HostBrandingResolver::class)->resolve();

            $view->with([
                'brandName' => $brand['name'],
                'brandLogoPath' => $brand['logo_path'],
                'brandLogoUrl' => $brand['logo_url'],
            ]);
        });
    }
}
