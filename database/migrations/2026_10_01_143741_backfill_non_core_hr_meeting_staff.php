<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const NON_CORE_STAFF_NAME_PATTERNS = [
        'arya widi nugroho',
        'hilmi ulwan',
        'yusuf eriansyah',
        'yusuf heriansyah',
        'yusurf heriansyah',
        'yususrf heriansyah',
        'abi rikhardo',
        'dimas rafi putra',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('employees')->update([
            'is_core_staff' => true,
            'updated_at' => now(),
        ]);

        $this->updateCoreStaffFlag(false);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->updateCoreStaffFlag(true);
    }

    private function updateCoreStaffFlag(bool $isCoreStaff): void
    {
        DB::table('employees')
            ->whereIn('id', function ($query): void {
                $query
                    ->select('employee_id')
                    ->from('employee_profiles')
                    ->where(function ($query): void {
                        foreach (self::NON_CORE_STAFF_NAME_PATTERNS as $namePattern) {
                            $query->orWhereRaw('LOWER(TRIM(name)) LIKE ?', ['%'.$namePattern.'%']);
                        }
                    });
            })
            ->update([
                'is_core_staff' => $isCoreStaff,
                'updated_at' => now(),
            ]);
    }
};
