<?php

namespace App\View\Composers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProjectManagementProfileComposer
{
    /**
     * @return array<string, mixed>
     */
    private function defaultProfileData(): array
    {
        return [
            'profilePicturePath' => null,
            'profilePositionName' => '-',
            'profileAddressSummary' => '-',
            'profileBusinessEmail' => '-',
            'profileDisplayName' => '-',
        ];
    }

    public function compose(View $view): void
    {
        $profileData = $this->defaultProfileData();
        $authenticatedUserId = Auth::id();

        $authenticatedUser = is_string($authenticatedUserId) || is_int($authenticatedUserId)
            ? User::query()
                ->select(['id', 'email', 'username'])
                ->with([
                    'userProfile:id,user_id,profile_picture',
                    'employee:id,user_id',
                    'employee.profile:id,employee_id,name',
                    'employee.deployment:id,employee_id,current_position_id',
                    'employee.deployment.position:id,name',
                    'employee.deployment.positions:id,name',
                    'employee.latestAddress' => static function ($query): void {
                        $query->select([
                            'employee_addresses.id',
                            'employee_addresses.employee_id',
                            'employee_addresses.village',
                            'employee_addresses.subdistrict',
                            'employee_addresses.created_at',
                        ]);
                    },
                ])
                ->find($authenticatedUserId)
            : null;

        if ($authenticatedUser instanceof User) {
            $profilePicturePath = $authenticatedUser->userProfile?->profile_picture;
            if (is_string($profilePicturePath) && trim($profilePicturePath) !== '') {
                $profileData['profilePicturePath'] = trim($profilePicturePath);
            }

            $profileData['profileBusinessEmail'] = is_string($authenticatedUser->email) && trim($authenticatedUser->email) !== ''
                ? trim($authenticatedUser->email)
                : '-';

            $displayName = $authenticatedUser->employee?->profile?->name;
            if (! is_string($displayName) || trim($displayName) === '') {
                $displayName = $authenticatedUser->username;
            }

            if (is_string($displayName) && trim($displayName) !== '') {
                $profileData['profileDisplayName'] = trim($displayName);
            }

            $positionName = collect([$authenticatedUser->employee?->deployment?->position?->name])
                ->merge($authenticatedUser->employee?->deployment?->positions?->pluck('name') ?? [])
                ->map(fn (mixed $positionName): string => trim((string) $positionName))
                ->filter()
                ->unique()
                ->implode(', ');
            if ($positionName !== '') {
                $profileData['profilePositionName'] = $positionName;
            }

            $latestAddress = $authenticatedUser->employee?->latestAddress;
            $villageName = is_string($latestAddress?->village) ? trim($latestAddress->village) : '';
            $subdistrictName = is_string($latestAddress?->subdistrict) ? trim($latestAddress->subdistrict) : '';

            if ($villageName !== '' && $subdistrictName !== '') {
                $profileData['profileAddressSummary'] = $villageName.', '.$subdistrictName;
            } elseif ($villageName !== '') {
                $profileData['profileAddressSummary'] = $villageName;
            } elseif ($subdistrictName !== '') {
                $profileData['profileAddressSummary'] = $subdistrictName;
            }
        }

        $view->with($profileData);
    }
}
