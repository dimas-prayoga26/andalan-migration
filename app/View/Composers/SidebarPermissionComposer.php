<?php

namespace App\View\Composers;

use App\Models\User;
use Illuminate\Contracts\View\View;

class SidebarPermissionComposer
{
    public function compose(View $view): void
    {
        $user = auth()->user();
        $canViewAllMenus = false;

        if ($user instanceof User) {
            $canViewAllMenus = $user->hasRole('superuser');
        }

        $view->with('canViewSidebarMenu', static function (string $permissionName) use ($user, $canViewAllMenus): bool {
            return ($canViewAllMenus && ! in_array($permissionName, ['view-pic-attendance', 'view-director-attendance'], true))
                || ($user instanceof User && $user->hasAnyPositionPermission([$permissionName]));
        });
    }
}
