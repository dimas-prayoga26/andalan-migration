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
    public function test_first_secondary_position_permission_grants_access_when_primary_lacks_it(): void
    {
        $staffPosition = $this->position('staff', 'Staff', true, 0);
        $administratorPosition = $this->position('administrator', 'Administrator', false, 1, ['view-authorization']);

        $user = $this->userWithPositions($staffPosition, collect([$staffPosition, $administratorPosition]));

        $this->assertTrue($user->hasAnyPositionPermission(['view-authorization']));
    }

    public function test_third_position_permission_does_not_grant_access(): void
    {
        $staffPosition = $this->position('staff', 'Staff', true, 0);
        $supervisorPosition = $this->position('supervisor', 'Supervisor', false, 1);
        $administratorPosition = $this->position('administrator', 'Administrator', false, 2, ['view-authorization']);

        $user = $this->userWithPositions($staffPosition, collect([$staffPosition, $supervisorPosition, $administratorPosition]));

        $this->assertFalse($user->hasAnyPositionPermission(['view-authorization']));
    }

    public function test_primary_position_permission_grants_access(): void
    {
        $staffPosition = $this->position('staff', 'Staff', false, 1);
        $administratorPosition = $this->position('administrator', 'Administrator', true, 0, ['view-authorization']);

        $user = $this->userWithPositions($administratorPosition, collect([$staffPosition, $administratorPosition]));

        $this->assertTrue($user->hasAnyPositionPermission(['view-authorization']));
    }

    public function test_current_position_is_fallback_when_primary_pivot_is_missing(): void
    {
        $administratorPosition = $this->position('administrator', 'Administrator', false, 0, ['view-authorization']);

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
    private function position(string $id, string $name, bool $isPrimary, int $sortOrder = 0, array $permissionNames = []): Position
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
        $pivot->forceFill([
            'is_primary' => $isPrimary,
            'sort_order' => $sortOrder,
        ]);
        $position->setRelation('pivot', $pivot);

        return $position;
    }
}
