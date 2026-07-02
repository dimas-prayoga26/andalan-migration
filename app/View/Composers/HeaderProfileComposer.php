<?php

namespace App\View\Composers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class HeaderProfileComposer
{
    public function compose(View $view): void
    {
        $headerData = [
            'headerUserName' => '-',
            'headerUserPositionLabel' => '-',
            'headerUserAvatarUrl' => asset('assets/default_user.jpg'),
        ];

        $authenticatedUserId = Auth::id();
        $authenticatedUser = is_string($authenticatedUserId) || is_int($authenticatedUserId)
            ? User::query()
                ->select(['id', 'username', 'email'])
                ->with([
                    'employee:id,user_id',
                    'employee.profile:id,employee_id,name,profile_picture_path',
                    'employee.deployment:id,employee_id,current_position_id',
                    'employee.deployment.position:id,name',
                ])
                ->find($authenticatedUserId)
            : null;

        if ($authenticatedUser instanceof User) {
            $employeeName = trim((string) ($authenticatedUser->employee?->profile?->name ?? ''));
            $username = trim((string) $authenticatedUser->username);
            $emailName = trim((string) explode('@', (string) $authenticatedUser->email)[0]);

            $headerData['headerUserName'] = $employeeName !== ''
                ? $employeeName
                : ($username !== '' ? $username : ($emailName !== '' ? $emailName : '-'));

            $primaryPositionName = trim((string) ($authenticatedUser->employee?->deployment?->position?->name ?? ''));
            $headerData['headerUserPositionLabel'] = $primaryPositionName !== '' ? $primaryPositionName : '-';
            $headerData['headerUserAvatarUrl'] = $this->avatarUrl(
                $authenticatedUser->employee?->profile?->profile_picture_path,
            );
        }

        $view->with($headerData);
    }

    private function avatarUrl(mixed $profilePicturePath): string
    {
        $defaultAvatarUrl = asset('assets/default_user.jpg');
        $profilePicturePath = trim((string) $profilePicturePath);

        if ($profilePicturePath === '') {
            return $defaultAvatarUrl;
        }

        if (Str::startsWith($profilePicturePath, ['http://', 'https://'])) {
            return $profilePicturePath;
        }

        $publicPath = ltrim($profilePicturePath, '/');

        return File::exists(public_path($publicPath)) ? asset($publicPath) : $defaultAvatarUrl;
    }
}
