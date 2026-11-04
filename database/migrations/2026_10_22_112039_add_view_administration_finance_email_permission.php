<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private const PERMISSION_NAME = 'view-administration-finance-email';

    private const PREVIOUS_PERMISSION_NAME = 'view-email-management';

    /**
     * Administration & Finance email gets its own menu permission; positions that could open it through
     * Email Management keep that access.
     */
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $now = Carbon::now();
        $permissionId = DB::table('permissions')
            ->where('name', self::PERMISSION_NAME)
            ->value('uuid');

        if (! is_string($permissionId) || $permissionId === '') {
            $permissionId = (string) Str::uuid();

            DB::table('permissions')->insert([
                'uuid' => $permissionId,
                'name' => self::PERMISSION_NAME,
                'guard_name' => 'web',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $previousPermissionId = DB::table('permissions')
            ->where('name', self::PREVIOUS_PERMISSION_NAME)
            ->value('uuid');

        if (is_string($previousPermissionId) && $previousPermissionId !== '') {
            $positionPermissionRows = DB::table('position_has_permissions')
                ->where('permission_id', $previousPermissionId)
                ->pluck('position_id')
                ->unique()
                ->map(static fn (string $positionId): array => [
                    'position_id' => $positionId,
                    'permission_id' => $permissionId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
                ->values()
                ->all();

            if ($positionPermissionRows !== []) {
                DB::table('position_has_permissions')->insertOrIgnore($positionPermissionRows);
            }
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
            ->where('name', self::PERMISSION_NAME)
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
