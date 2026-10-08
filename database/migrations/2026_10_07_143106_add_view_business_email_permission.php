<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private const KEY_ADMINISTRATOR = 'administrator';

    private const KEY_SUPER_ADMINISTRATOR = 'super_administrator';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $now = Carbon::now();
        $permissionId = DB::table('permissions')
            ->where('name', 'view-business-email')
            ->value('uuid');

        if (! is_string($permissionId) || $permissionId === '') {
            $permissionId = (string) Str::uuid();

            DB::table('permissions')->insert([
                'uuid' => $permissionId,
                'name' => 'view-business-email',
                'guard_name' => 'web',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $staffPositionIds = DB::table('positions')
            ->where(function ($query): void {
                $query
                    ->whereNull('system_key')
                    ->orWhereNotIn('system_key', [self::KEY_ADMINISTRATOR, self::KEY_SUPER_ADMINISTRATOR]);
            })
            ->pluck('id');

        $positionPermissionRows = $staffPositionIds
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

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissionId = DB::table('permissions')
            ->where('name', 'view-business-email')
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
