<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Position;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

class PositionPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        DB::transaction(function (): void {
            $permissions = collect($this->menuPermissionData())
                ->pluck('permission')
                ->filter()
                ->unique()
                ->mapWithKeys(static function (string $permissionName): array {
                    $permission = Permission::query()->firstOrCreate([
                        'name' => $permissionName,
                        'guard_name' => 'web',
                    ]);

                    return [$permissionName => $permission];
                });

            $this->syncRolePermissions($permissions->keys()->all());
            $this->syncPositionPermissions($permissions);
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * @return array<int, array{section: string, label: string, permission: string}>
     */
    private function menuPermissionData(): array
    {
        return [
            ['section' => 'Main', 'label' => 'Dashboard', 'permission' => 'view-dashboard'],
            ['section' => 'Main', 'label' => 'Activity Calendar', 'permission' => 'view-calendar'],
            ['section' => 'Siap', 'label' => 'Attendance', 'permission' => 'view-attendance'],
            ['section' => 'Siap', 'label' => 'Timesheet & Reporting', 'permission' => 'view-timesheet-reporting'],
            ['section' => 'Siap', 'label' => 'Zoom Meeting', 'permission' => 'view-meeting'],
            ['section' => 'HR Management', 'label' => 'Admin Attendance', 'permission' => 'view-admin-attendance'],
            ['section' => 'HR Management', 'label' => 'PIC', 'permission' => 'view-pic-attendance'],
            ['section' => 'HR Management', 'label' => 'Director', 'permission' => 'view-director-attendance'],
            ['section' => 'HR Management', 'label' => 'Organization', 'permission' => 'view-organization'],
            ['section' => 'HR Management', 'label' => 'Authorization', 'permission' => 'view-authorization'],
            ['section' => 'HR Management', 'label' => 'Employee Database', 'permission' => 'view-employee-database'],
            ['section' => 'HR Management', 'label' => 'Talent Acquisition', 'permission' => 'view-talent-acquisition'],
            ['section' => 'Finance Management', 'label' => 'Payroll', 'permission' => 'view-payroll'],
            ['section' => 'Finance Management', 'label' => 'Employee Services', 'permission' => 'view-employee-services'],
        ];
    }

    /**
     * @param  array<int, string>  $permissionNames
     */
    private function syncRolePermissions(array $permissionNames): void
    {
        Role::query()
            ->where('name', 'superuser')
            ->first()
            ?->syncPermissions($permissionNames);

        Role::query()
            ->where('name', 'Board of Directors')
            ->first()
            ?->syncPermissions([
                'view-dashboard',
                'view-calendar',
                'view-attendance',
                'view-timesheet-reporting',
                'view-meeting',
                'view-director-attendance',
                'view-organization',
                'view-authorization',
                'view-employee-database',
                'view-talent-acquisition',
            ]);

        Role::query()
            ->where('name', 'Staff')
            ->first()
            ?->syncPermissions([
                'view-dashboard',
                'view-calendar',
                'view-attendance',
                'view-timesheet-reporting',
                'view-meeting',
            ]);
    }

    /**
     * @param  Collection<string, Permission>  $permissions
     */
    private function syncPositionPermissions(Collection $permissions): void
    {
        $baseStaffPermissions = [
            'view-dashboard',
            'view-calendar',
            'view-attendance',
            'view-timesheet-reporting',
            'view-meeting',
        ];

        $allPermissionsWithoutPic = $permissions
            ->keys()
            ->reject(static fn (string $permissionName): bool => in_array($permissionName, ['view-pic-attendance', 'view-director-attendance'], true))
            ->values()
            ->all();

        $directorPermissions = [
            'view-dashboard',
            'view-calendar',
            'view-attendance',
            'view-timesheet-reporting',
            'view-meeting',
            'view-organization',
            'view-authorization',
            'view-employee-database',
            'view-talent-acquisition',
            'view-director-attendance',
        ];

        $positionPermissions = [
            'Administrator' => $allPermissionsWithoutPic,
            'Chief Operating Officer' => $directorPermissions,
            'Director' => $directorPermissions,
            'Finance and Administration Coordinator' => [
                'view-dashboard',
                'view-calendar',
                'view-attendance',
                'view-timesheet-reporting',
                'view-meeting',
                'view-employee-services',
            ],
            'Accounting and Taxation' => [
                'view-dashboard',
                'view-calendar',
                'view-attendance',
                'view-timesheet-reporting',
                'view-meeting',
                'view-employee-services',
            ],
            'Operations Coordinator' => $baseStaffPermissions,
            'Supervisor' => array_merge($baseStaffPermissions, ['view-pic-attendance']),
            'Interior Design' => $baseStaffPermissions,
            'Architecture Design' => $baseStaffPermissions,
            'Web Developer' => $baseStaffPermissions,
            'Documentation Event and Editor Video' => $baseStaffPermissions,
            'Graphic Design' => array_merge($baseStaffPermissions, ['view-talent-acquisition']),
            'Branding Designer' => array_merge($baseStaffPermissions, ['view-talent-acquisition']),
        ];

        Position::query()
            ->where('name', 'Super Administrator')
            ->get()
            ->each(static fn (Position $position): mixed => $position->permissions()->detach());

        Position::query()
            ->whereIn('name', array_keys($positionPermissions))
            ->get()
            ->each(function (Position $position) use ($positionPermissions, $permissions): void {
                $permissionIds = collect($positionPermissions[$position->name] ?? [])
                    ->map(static fn (string $permissionName): ?string => $permissions->get($permissionName)?->uuid)
                    ->filter()
                    ->values()
                    ->all();

                $position->permissions()->sync($permissionIds);
            });
    }
}
