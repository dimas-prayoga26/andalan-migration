<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    private const LEGACY_SUPERUSER_ROLES = ['superUser', 'superuser'];

    private const STAFF_ROLE = 'Staff';

    private const LEGACY_BOD_ROLE = 'Board of Directors';

    private const SUPER_USER_DEPARTMENT = 'Super User';

    private const LEGACY_SUPERUSER_DEPARTMENT = 'Superuser';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = now();

        if (Schema::hasTable('roles')) {
            $staffRoleId = $this->ensureRole(self::STAFF_ROLE, $now);

            foreach (self::LEGACY_SUPERUSER_ROLES as $legacySuperuserRole) {
                $this->moveRoleMembers($legacySuperuserRole, $staffRoleId);
                $this->deleteRole($legacySuperuserRole);
            }

            $this->moveRoleMembers(self::LEGACY_BOD_ROLE, $staffRoleId);
            $this->deleteRole(self::LEGACY_BOD_ROLE);
        }

        $this->renameSuperuserDepartment($now);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $now = now();

        if (Schema::hasTable('roles')) {
            $this->ensureRole(self::LEGACY_BOD_ROLE, $now);
        }

        if (Schema::hasTable('departments')) {
            DB::table('departments')
                ->where('name', self::SUPER_USER_DEPARTMENT)
                ->update([
                    'name' => self::LEGACY_SUPERUSER_DEPARTMENT,
                    'updated_at' => $now,
                ]);
        }

        if (Schema::hasTable('meta_data_divisions')) {
            DB::table('meta_data_divisions')
                ->where('name', self::SUPER_USER_DEPARTMENT)
                ->update([
                    'name' => self::LEGACY_SUPERUSER_DEPARTMENT,
                    'updated_at' => $now,
                ]);
        }
    }

    private function ensureRole(string $name, mixed $now): string
    {
        $roleId = DB::table('roles')
            ->where('name', $name)
            ->where('guard_name', 'web')
            ->value('uuid');

        if (is_string($roleId) && trim($roleId) !== '') {
            return $roleId;
        }

        $roleId = (string) Str::uuid();

        DB::table('roles')->insert([
            'uuid' => $roleId,
            'name' => $name,
            'guard_name' => 'web',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return $roleId;
    }

    private function moveRoleMembers(string $fromRoleName, string $toRoleId): void
    {
        $fromRoleId = DB::table('roles')
            ->where('name', $fromRoleName)
            ->where('guard_name', 'web')
            ->value('uuid');

        if (! is_string($fromRoleId) || trim($fromRoleId) === '') {
            return;
        }

        $this->moveRoleAssignments($fromRoleId, $toRoleId);
    }

    private function moveRoleAssignments(string $fromRoleId, string $toRoleId): void
    {
        if (Schema::hasTable('model_has_roles')) {
            DB::table('model_has_roles')
                ->where('role_id', $fromRoleId)
                ->get()
                ->each(function (object $roleAssignment) use ($toRoleId): void {
                    $payload = [
                        'role_id' => $toRoleId,
                        'model_type' => $roleAssignment->model_type,
                        'model_uuid' => $roleAssignment->model_uuid,
                    ];
                    $teamKey = (string) config('permission.column_names.team_foreign_key', 'team_id');

                    if (property_exists($roleAssignment, $teamKey)) {
                        $payload[$teamKey] = $roleAssignment->{$teamKey};
                    }

                    DB::table('model_has_roles')->insertOrIgnore($payload);
                });

            DB::table('model_has_roles')
                ->where('role_id', $fromRoleId)
                ->delete();
        }

        if (Schema::hasTable('role_has_permissions')) {
            DB::table('role_has_permissions')
                ->where('role_id', $fromRoleId)
                ->delete();
        }
    }

    private function deleteRole(string $roleName): void
    {
        $roleId = DB::table('roles')
            ->where('name', $roleName)
            ->where('guard_name', 'web')
            ->value('uuid');

        if (is_string($roleId) && trim($roleId) !== '') {
            $this->deleteRoleById($roleId);
        }
    }

    private function deleteRoleById(string $roleId): void
    {
        if (Schema::hasTable('model_has_roles')) {
            DB::table('model_has_roles')->where('role_id', $roleId)->delete();
        }

        if (Schema::hasTable('role_has_permissions')) {
            DB::table('role_has_permissions')->where('role_id', $roleId)->delete();
        }

        DB::table('roles')->where('uuid', $roleId)->delete();
    }

    private function renameSuperuserDepartment(mixed $now): void
    {
        if (Schema::hasTable('departments')) {
            $targetDepartmentId = DB::table('departments')
                ->where('name', self::SUPER_USER_DEPARTMENT)
                ->value('id');
            $legacyDepartmentId = DB::table('departments')
                ->where('name', self::LEGACY_SUPERUSER_DEPARTMENT)
                ->value('id');

            if (is_string($targetDepartmentId) && trim($targetDepartmentId) !== '' && is_string($legacyDepartmentId) && trim($legacyDepartmentId) !== '') {
                if (Schema::hasTable('employee_deployments')) {
                    DB::table('employee_deployments')
                        ->where('current_department_id', $legacyDepartmentId)
                        ->update([
                            'current_department_id' => $targetDepartmentId,
                            'updated_at' => $now,
                        ]);
                }

                if (Schema::hasTable('project_departments')) {
                    DB::table('project_departments')
                        ->where('department_id', $legacyDepartmentId)
                        ->get(['id', 'project_id'])
                        ->each(function (object $projectDepartment) use ($targetDepartmentId, $now): void {
                            $targetExists = DB::table('project_departments')
                                ->where('project_id', $projectDepartment->project_id)
                                ->where('department_id', $targetDepartmentId)
                                ->exists();

                            if ($targetExists) {
                                DB::table('project_departments')
                                    ->where('id', $projectDepartment->id)
                                    ->delete();

                                return;
                            }

                            DB::table('project_departments')
                                ->where('id', $projectDepartment->id)
                                ->update([
                                    'department_id' => $targetDepartmentId,
                                    'updated_at' => $now,
                                ]);
                        });
                }

                DB::table('departments')
                    ->where('id', $legacyDepartmentId)
                    ->delete();
            } else {
                DB::table('departments')
                    ->where('name', self::LEGACY_SUPERUSER_DEPARTMENT)
                    ->update([
                        'name' => self::SUPER_USER_DEPARTMENT,
                        'updated_at' => $now,
                    ]);
            }
        }

        if (Schema::hasTable('meta_data_divisions')) {
            $updatedRows = DB::table('meta_data_divisions')
                ->where('name', self::LEGACY_SUPERUSER_DEPARTMENT)
                ->update([
                    'name' => self::SUPER_USER_DEPARTMENT,
                    'updated_at' => $now,
                ]);

            if ($updatedRows === 0 && ! DB::table('meta_data_divisions')->where('name', self::SUPER_USER_DEPARTMENT)->exists()) {
                DB::table('meta_data_divisions')->insert([
                    'name' => self::SUPER_USER_DEPARTMENT,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
};
