<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $now = Carbon::now();
        $permissionId = DB::table('permissions')
            ->where('name', 'view-settings')
            ->value('uuid');

        if (! is_string($permissionId) || $permissionId === '') {
            $permissionId = (string) Str::uuid();

            DB::table('permissions')->insert([
                'uuid' => $permissionId,
                'name' => 'view-settings',
                'guard_name' => 'web',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $authorizationPermissionId = DB::table('permissions')
            ->where('name', 'view-authorization')
            ->value('uuid');

        if (! is_string($authorizationPermissionId) || $authorizationPermissionId === '') {
            return;
        }

        $positionPermissionRows = DB::table('position_has_permissions')
            ->where('permission_id', $authorizationPermissionId)
            ->pluck('position_id')
            ->map(static fn (string $positionId): array => [
                'position_id' => $positionId,
                'permission_id' => $permissionId,
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->all();

        if ($positionPermissionRows !== []) {
            DB::table('position_has_permissions')->insertOrIgnore($positionPermissionRows);
        }

        $rolePermissionRows = DB::table('role_has_permissions')
            ->where('permission_id', $authorizationPermissionId)
            ->pluck('role_id')
            ->map(static fn (string $roleId): array => [
                'role_id' => $roleId,
                'permission_id' => $permissionId,
            ])
            ->all();

        if ($rolePermissionRows !== []) {
            DB::table('role_has_permissions')->insertOrIgnore($rolePermissionRows);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissionId = DB::table('permissions')
            ->where('name', 'view-settings')
            ->value('uuid');

        if (! is_string($permissionId) || $permissionId === '') {
            return;
        }

        DB::table('position_has_permissions')->where('permission_id', $permissionId)->delete();
        DB::table('role_has_permissions')->where('permission_id', $permissionId)->delete();
        DB::table('permissions')->where('uuid', $permissionId)->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
