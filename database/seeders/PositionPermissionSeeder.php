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
            ['section' => 'Siap', 'label' => 'Business Email', 'permission' => 'view-business-email'],
            ['section' => 'HR Management', 'label' => 'Admin Attendance', 'permission' => 'view-admin-attendance'],
            ['section' => 'HR Management', 'label' => 'Email Management', 'permission' => 'view-email-management'],
            ['section' => 'HR Management', 'label' => 'PIC', 'permission' => 'view-pic-attendance'],
            ['section' => 'HR Management', 'label' => 'Director', 'permission' => 'view-director-attendance'],
            ['section' => 'HR Management', 'label' => 'Organization', 'permission' => 'view-organization'],
            ['section' => 'HR Management', 'label' => 'Authorization', 'permission' => 'view-authorization'],
            ['section' => 'HR Management', 'label' => 'Employee Database', 'permission' => 'view-employee-database'],
            ['section' => 'HR Management', 'label' => 'Talent Acquisition', 'permission' => 'view-talent-acquisition'],
            ['section' => 'Setting', 'label' => 'Setting', 'permission' => 'view-settings'],
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
            'view-business-email',
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
            'view-business-email',
            'view-organization',
            'view-authorization',
            'view-employee-database',
            'view-talent-acquisition',
            'view-settings',
            'view-director-attendance',
        ];

        $positionPermissions = [
            Position::KEY_ADMINISTRATOR => $allPermissionsWithoutPic,
            Position::KEY_CHIEF_OPERATING_OFFICER => $directorPermissions,
            Position::KEY_DIRECTOR => $directorPermissions,
            Position::KEY_FINANCE_ADMINISTRATION_COORDINATOR => [
                'view-dashboard',
                'view-calendar',
                'view-attendance',
                'view-timesheet-reporting',
                'view-meeting',
                'view-employee-services',
            ],
            Position::KEY_ACCOUNTING_TAXATION => [
                'view-dashboard',
                'view-calendar',
                'view-attendance',
                'view-timesheet-reporting',
                'view-meeting',
                'view-employee-services',
            ],
            Position::KEY_OPERATIONS_COORDINATOR => $baseStaffPermissions,
            Position::KEY_SUPERVISOR => array_merge($baseStaffPermissions, ['view-pic-attendance']),
            Position::KEY_INTERIOR_DESIGN => $baseStaffPermissions,
            Position::KEY_ARCHITECTURE_DESIGN => $baseStaffPermissions,
            Position::KEY_WEB_DEVELOPER => $baseStaffPermissions,
            Position::KEY_DOCUMENTATION_EVENT_EDITOR_VIDEO => $baseStaffPermissions,
            Position::KEY_DIGITAL_MARKETING => $baseStaffPermissions,
            Position::KEY_GRAPHIC_DESIGN => array_merge($baseStaffPermissions, ['view-talent-acquisition']),
            Position::KEY_BRANDING_DESIGNER => array_merge($baseStaffPermissions, ['view-talent-acquisition']),
            Position::KEY_DRIVER => $baseStaffPermissions,
        ];

        Position::query()
            ->whereSystemKey(Position::KEY_SUPER_ADMINISTRATOR)
            ->get()
            ->each(static fn (Position $position): mixed => $position->permissions()->detach());

        Position::query()
            ->whereIn('system_key', array_keys($positionPermissions))
            ->get()
            ->each(function (Position $position) use ($positionPermissions, $permissions): void {
                $permissionIds = collect($positionPermissions[$position->system_key] ?? [])
                    ->map(static fn (string $permissionName): ?string => $permissions->get($permissionName)?->uuid)
                    ->filter()
                    ->values()
                    ->all();

                $position->permissions()->sync($permissionIds);
            });
    }
}
