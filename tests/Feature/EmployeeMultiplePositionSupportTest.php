<?php

namespace Tests\Feature;

use Database\Seeders\LegacySqlUserSeeder;
use ReflectionMethod;
use Tests\TestCase;

class EmployeeMultiplePositionSupportTest extends TestCase
{
    public function test_legacy_join_date_is_preserved_while_placeholder_resignation_date_is_cleared(): void
    {
        $seeder = new LegacySqlUserSeeder;
        $normalizeDate = new ReflectionMethod($seeder, 'normalizeDate');
        $normalizeJoinDate = new ReflectionMethod($seeder, 'normalizeJoinDate');
        $normalizeResignationDate = new ReflectionMethod($seeder, 'normalizeResignationDate');
        $legacyResignationDate = new ReflectionMethod($seeder, 'legacyResignationDate');

        $this->assertNull($normalizeDate->invoke($seeder, '0000-00-00'));
        $this->assertSame('1970-01-01', $normalizeDate->invoke($seeder, '1970-01-01'));
        $this->assertSame('2024-06-19', $normalizeJoinDate->invoke($seeder, [
            'start_date' => '1970-01-01',
            'created_at' => '2024-06-19 08:49:02',
        ]));
        $this->assertSame('2025-04-28', $normalizeJoinDate->invoke($seeder, [
            'start_date' => '2023-01-01',
            'created_at' => '2025-04-28 09:52:02',
        ]));
        $this->assertNull($normalizeResignationDate->invoke($seeder, '1970-01-01'));
        $this->assertSame('2026-12-31', $normalizeResignationDate->invoke($seeder, '2026-12-31'));
        $this->assertNull($legacyResignationDate->invoke($seeder, [
            'email' => 'active@example.com',
            'status' => 1,
            'end_date' => '2023-12-31',
        ]));
        $this->assertSame('2023-12-31', $legacyResignationDate->invoke($seeder, [
            'email' => 'inactive@example.com',
            'status' => 0,
            'end_date' => '2023-12-31',
        ]));
    }

    public function test_multiple_position_schema_and_relations_are_registered(): void
    {
        $migration = file_get_contents(database_path('migrations/2026_06_30_114352_create_employee_deployment_positions_table.php'));
        $deploymentModel = file_get_contents(app_path('Models/EmployeeDeployment.php'));
        $positionModel = file_get_contents(app_path('Models/Position.php'));

        $this->assertIsString($migration);
        $this->assertStringContainsString("Schema::create('employee_deployment_positions'", $migration);
        $this->assertStringContainsString('employee_deployment_id', $migration);
        $this->assertStringContainsString('position_id', $migration);
        $this->assertStringContainsString('is_primary', $migration);

        $this->assertIsString($deploymentModel);
        $this->assertStringContainsString('function positions(): BelongsToMany', $deploymentModel);
        $this->assertStringContainsString("'employee_deployment_positions'", $deploymentModel);

        $this->assertIsString($positionModel);
        $this->assertStringContainsString('function deployments(): BelongsToMany', $positionModel);
    }

    public function test_position_permission_seeder_registers_administrator_super_administrator_and_coo_rules(): void
    {
        $positionSeeder = file_get_contents(database_path('seeders/PositionSeeder.php'));
        $permissionSeeder = file_get_contents(database_path('seeders/PositionPermissionSeeder.php'));
        $positionMigration = file_get_contents(database_path('migrations/2026_05_04_080748_create_positions_table.php'));

        $this->assertIsString($positionSeeder);
        $this->assertStringContainsString("'Super Administrator'", $positionSeeder);
        $this->assertStringContainsString("'Administrator'", $positionSeeder);
        $this->assertStringNotContainsString("'System Administrator'", $positionSeeder);

        $this->assertIsString($permissionSeeder);
        $this->assertStringContainsString("->where('name', 'Super Administrator')", $permissionSeeder);
        $this->assertStringContainsString('->permissions()->detach()', $permissionSeeder);
        $this->assertStringNotContainsString("'Super Administrator' => \$allPermissions", $permissionSeeder);
        $this->assertStringContainsString("'Administrator' => \$allPermissionsWithoutPic", $permissionSeeder);
        $this->assertStringContainsString("'Chief Operating Officer' => \$directorPermissions", $permissionSeeder);
        $this->assertStringContainsString('?->syncPermissions($permissionNames)', $permissionSeeder);

        $this->assertIsString($positionMigration);
        $this->assertStringContainsString("string('name', 191)->unique('positions_name_unique')", $positionMigration);
    }

    public function test_position_permissions_read_primary_and_additional_positions(): void
    {
        $userModel = file_get_contents(app_path('Models/User.php'));
        $middleware = file_get_contents(app_path('Http/Middleware/EnsurePositionPermission.php'));
        $sidebarComposer = file_get_contents(app_path('View/Composers/SidebarPermissionComposer.php'));

        $this->assertIsString($userModel);
        $this->assertStringContainsString("if (\$this->hasRole('superuser'))", $userModel);
        $this->assertStringContainsString("'employee.deployment.position.permissions:uuid,name'", $userModel);
        $this->assertStringContainsString("'employee.deployment.positions.permissions:uuid,name'", $userModel);
        $this->assertStringContainsString('$deployment->positions', $userModel);

        $this->assertIsString($middleware);
        $this->assertStringContainsString('hasAnyPositionPermission($permissionNames)', $middleware);

        $this->assertIsString($sidebarComposer);
        $this->assertStringContainsString('return $canViewAllMenus', $sidebarComposer);
        $this->assertStringContainsString('hasAnyPositionPermission([$permissionName])', $sidebarComposer);
    }

    public function test_authorization_form_and_sync_support_multiple_positions(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/AuthorizationController.php'));
        $form = file_get_contents(resource_path('views/authorization/form.blade.php'));
        $show = file_get_contents(resource_path('views/authorization/show.blade.php'));

        $this->assertIsString($controller);
        $this->assertStringContainsString("'current_position_ids' => ['array']", $controller);
        $this->assertStringContainsString('syncDeploymentPositions(', $controller);
        $this->assertStringContainsString('$this->positionNamesFor($user->employee)', $controller);

        $this->assertIsString($form);
        $this->assertStringContainsString('name="current_position_ids[]"', $form);
        $this->assertStringContainsString('multiple', $form);

        $this->assertIsString($show);
        $this->assertStringContainsString('$positionLabel', $show);
    }

    public function test_super_administrator_is_not_assignable_from_access_menu_editor(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/AuthorizationController.php'));

        $this->assertIsString($controller);
        $this->assertStringContainsString("->where('name', '<>', 'Super Administrator')", $controller);
        $this->assertStringContainsString('->intersect($assignablePositionIds)', $controller);
    }

    public function test_leave_grouping_and_seeders_no_longer_depend_only_on_current_position_id(): void
    {
        $adminLeaveController = file_get_contents(app_path('Http/Controllers/AdminAttendance/AttendanceLeaveController.php'));
        $picLeaveController = file_get_contents(app_path('Http/Controllers/PicAttendance/PicAttendanceLeaveController.php'));
        $userSeeder = file_get_contents(database_path('seeders/UserSeeder.php'));
        $legacySeeder = file_get_contents(database_path('seeders/LegacySqlUserSeeder.php'));
        $niskalaSeeder = file_get_contents(database_path('seeders/NiskalaMultiPicLeaveSeeder.php'));
        $databaseSeeder = file_get_contents(database_path('seeders/DatabaseSeeder.php'));

        $this->assertIsString($adminLeaveController);
        $this->assertStringContainsString("'deployment.positions:id,name'", $adminLeaveController);
        $this->assertStringContainsString('->flatMap(function (Employee $employee): Collection', $adminLeaveController);
        $this->assertStringNotContainsString('->groupBy(fn (Employee $employee): string => (string) $employee->deployment?->current_position_id)', $adminLeaveController);

        $this->assertIsString($picLeaveController);
        $this->assertStringContainsString("'deployment.positions:id,name'", $picLeaveController);
        $this->assertStringContainsString('->flatMap(function (Employee $employee): Collection', $picLeaveController);
        $this->assertStringNotContainsString('->groupBy(fn (Employee $employee): string => (string) $employee->deployment?->current_position_id)', $picLeaveController);

        $this->assertIsString($userSeeder);
        $this->assertStringContainsString('employee_deployment_positions', $userSeeder);
        $this->assertStringContainsString('syncDeploymentPosition(', $userSeeder);

        $this->assertIsString($legacySeeder);
        $this->assertStringContainsString('employee_deployment_positions', $legacySeeder);
        $this->assertStringContainsString('syncDeploymentPosition(', $legacySeeder);
        $this->assertStringContainsString("'System Administrator' => 'Administrator'", $legacySeeder);
        $this->assertStringContainsString('private const LEGACY_PLACEHOLDER_ADMIN_EMAILS', $legacySeeder);
        $this->assertStringContainsString("'superadmin@andalanbersama.com'", $legacySeeder);

        $this->assertIsString($niskalaSeeder);
        $this->assertStringContainsString("employeeByEmail('diktanamira@gmail.com')", $niskalaSeeder);
        $this->assertStringContainsString("employeeByEmail('halloerlin@gmail.com')", $niskalaSeeder);
        $this->assertStringContainsString("employeeByEmail('leonieputri7@gmail.com')", $niskalaSeeder);
        $this->assertGreaterThanOrEqual(2, substr_count($niskalaSeeder, "'additional_position_names' => ['Administrator', 'Accounting and Taxation']"));
        $this->assertStringContainsString("'additional_position_names' => ['Administrator', 'Accounting and Taxation']", $niskalaSeeder);
        $this->assertStringContainsString('seedPendingSupervisorReviewLeaveRequest($mevia', $niskalaSeeder);
        $this->assertStringContainsString('seedPendingSupervisorReviewLeaveRequest($erlin', $niskalaSeeder);
        $this->assertStringContainsString('NiskalaMultiPicLeaveSeeder::class', $databaseSeeder);
        $this->assertStringContainsString("'halloerlin@gmail.com' => ['Administrator', 'Accounting and Taxation']", $legacySeeder);
        $this->assertStringContainsString("'diktanamira@gmail.com' => ['Administrator', 'Accounting and Taxation']", $legacySeeder);
        $this->assertStringContainsString("'leonieputri7@gmail.com' => ['Administrator', 'Supervisor']", $legacySeeder);
        $this->assertStringContainsString("'msyafiq.dev@gmail.com' => ['Supervisor']", $legacySeeder);
        $this->assertStringContainsString("'rexy@andalanbersama.com' => ['Director', 'Supervisor']", $legacySeeder);
        $this->assertStringContainsString("'fuadmfahrudin@gmail.com' => ['Director', 'Supervisor']", $legacySeeder);
        $this->assertStringContainsString("'fahmil@andalanbersama.com' => ['Director', 'Supervisor']", $legacySeeder);
        $this->assertStringContainsString("'lukman@rnbmanagement.com' => ['Supervisor']", $legacySeeder);
        $this->assertStringContainsString("'rully.priyatno@andalanbersama.com'", $legacySeeder);
        $this->assertStringContainsString("'hilmi.ulwan@andalanbersama.com'", $legacySeeder);
        $this->assertStringContainsString("'adik@andalanbersama.com'", $legacySeeder);
    }

    public function test_explicit_pic_assignment_mapping_is_registered(): void
    {
        $picSeeder = file_get_contents(database_path('seeders/EmployeePicAssignmentSeeder.php'));

        $this->assertIsString($picSeeder);
        $this->assertStringContainsString("'lukman@rnbmanagement.com' =>", $picSeeder);
        $this->assertStringContainsString("'rully.priyatno@andalanbersama.com'", $picSeeder);
        $this->assertStringContainsString("'hilmi.ulwan@andalanbersama.com'", $picSeeder);
        $this->assertStringContainsString("'leonieputri7@gmail.com' =>", $picSeeder);
        $this->assertSame(2, substr_count($picSeeder, "'leonieputri7@gmail.com'"));
        $this->assertStringContainsString("'diktanamira@gmail.com'", $picSeeder);
        $this->assertStringContainsString("'halloerlin@gmail.com'", $picSeeder);
        $this->assertStringNotContainsString('$supervisorEmployeeId === $staffEmployeeId', $picSeeder);
        $this->assertStringNotContainsString('syncActivePicAssignments($leonie, [$leonie], $now)', file_get_contents(database_path('seeders/NiskalaMultiPicLeaveSeeder.php')));
        $this->assertStringContainsString("'msyafiq.dev@gmail.com' =>", $picSeeder);
        $this->assertStringContainsString("'syarifhidayatullah.040203@gmail.com'", $picSeeder);
        $this->assertStringContainsString("'rifkafebriza456@gmail.com'", $picSeeder);
        $this->assertStringContainsString("'dimas.prayoga260403@gmail.com'", $picSeeder);
        $this->assertStringContainsString("'rexy@andalanbersama.com' =>", $picSeeder);
        $this->assertStringContainsString("'arumkusumawati98@gmail.com'", $picSeeder);
        $this->assertStringContainsString("'dedystwn.interior@gmail.com'", $picSeeder);
        $this->assertStringContainsString("'fahmil@andalanbersama.com' =>", $picSeeder);
        $this->assertStringContainsString("'aryapardomuan@gmail.com'", $picSeeder);
        $this->assertStringContainsString("'abasyamanyusuf1999@gmail.com'", $picSeeder);
        $this->assertStringContainsString("'aarissubakti@gmail.com'", $picSeeder);
        $this->assertStringContainsString("'airarizqi22@gmail.com'", $picSeeder);
        $this->assertStringContainsString("'fuadmfahrudin@gmail.com' =>", $picSeeder);
    }

    public function test_leonie_mevia_and_erlin_are_not_super_administrators(): void
    {
        $legacySeeder = file_get_contents(database_path('seeders/LegacySqlUserSeeder.php'));
        $niskalaSeeder = file_get_contents(database_path('seeders/NiskalaMultiPicLeaveSeeder.php'));

        $this->assertIsString($legacySeeder);
        $this->assertStringContainsString("'username' => 'superadmin'", $legacySeeder);
        $this->assertStringContainsString("\$user->syncRoles(['superuser']);", $legacySeeder);
        $this->assertStringNotContainsString("'admin@andalanbersama.com' => 'Administrator'", $legacySeeder);
        $this->assertStringNotContainsString("'admin@andalanbersama.com' => ['Administrator']", $legacySeeder);
        $this->assertStringNotContainsString("'admin@andalanbersama.com' => 'Super Administrator'", $legacySeeder);
        $this->assertStringNotContainsString("'admin@andalanbersama.com' => ['Super Administrator']", $legacySeeder);
        $this->assertStringNotContainsString("'diktanamira@gmail.com' => ['Super Administrator']", $legacySeeder);
        $this->assertStringNotContainsString("'leonieputri7@gmail.com' => ['Super Administrator']", $legacySeeder);
        $this->assertStringNotContainsString("'halloerlin@gmail.com' => ['Super Administrator']", $legacySeeder);

        $this->assertIsString($niskalaSeeder);
        $this->assertStringNotContainsString("'additional_position_names' => ['Super Administrator']", $niskalaSeeder);
        $this->assertStringNotContainsString('Admin RNB 2', $niskalaSeeder);
        $this->assertStringNotContainsString('admin3b@gmail.com', $niskalaSeeder);
    }
}
