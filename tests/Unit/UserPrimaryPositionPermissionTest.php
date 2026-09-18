<?php

namespace Tests\Unit;

use App\Models\Employee;
use App\Models\EmployeeDeployment;
use App\Models\Permission;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Collection;
use Tests\TestCase;

class UserPrimaryPositionPermissionTest extends TestCase
{
    public function test_additional_position_permission_does_not_grant_access_when_primary_lacks_it(): void
    {
        $staffPosition = $this->position('staff', 'Staff', true);
        $administratorPosition = $this->position('administrator', 'Administrator', false, ['view-authorization']);

        $user = $this->userWithPositions($staffPosition, collect([$staffPosition, $administratorPosition]));

        $this->assertFalse($user->hasAnyPositionPermission(['view-authorization']));
    }

    public function test_primary_position_permission_grants_access(): void
    {
        $staffPosition = $this->position('staff', 'Staff', false);
        $administratorPosition = $this->position('administrator', 'Administrator', true, ['view-authorization']);

        $user = $this->userWithPositions($administratorPosition, collect([$staffPosition, $administratorPosition]));

        $this->assertTrue($user->hasAnyPositionPermission(['view-authorization']));
    }

    public function test_current_position_is_fallback_when_primary_pivot_is_missing(): void
    {
        $administratorPosition = $this->position('administrator', 'Administrator', false, ['view-authorization']);

        $user = $this->userWithPositions($administratorPosition, collect());

        $this->assertTrue($user->hasAnyPositionPermission(['view-authorization']));
    }

    /**
     * @param  Collection<int, Position>  $positions
     */
    private function userWithPositions(Position $currentPosition, Collection $positions): User
    {
        $deployment = new EmployeeDeployment([
            'current_position_id' => $currentPosition->id,
        ]);
        $deployment->setRelation('position', $currentPosition);
        $deployment->setRelation('positions', $positions);

        $employee = new Employee;
        $employee->setRelation('deployment', $deployment);

        $user = new User;
        $user->setRelation('employee', $employee);
        $user->setRelation('roles', collect());

        return $user;
    }

    /**
     * @param  array<int, string>  $permissionNames
     */
    private function position(string $id, string $name, bool $isPrimary, array $permissionNames = []): Position
    {
        $position = new Position([
            'id' => $id,
            'name' => $name,
        ]);

        $position->setRelation(
            'permissions',
            collect($permissionNames)
                ->map(fn (string $permissionName): Permission => new Permission([
                    'uuid' => $permissionName,
                    'name' => $permissionName,
                    'guard_name' => 'web',
                ]))
                ->values()
        );

        $pivot = new Pivot;
        $pivot->forceFill(['is_primary' => $isPrimary]);
        $position->setRelation('pivot', $pivot);

        return $position;
    }
}
