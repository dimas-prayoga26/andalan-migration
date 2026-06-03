<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class LeaveTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            $leaveTypes = [
                [
                    'code' => 'SICK',
                    'name' => 'Sakit',
                    'accrual_method' => 'yearly',
                    'monthly_accrual_rate' => 0,
                    'is_encashable' => false,
                    'is_active' => true,
                ],
                [
                    'code' => 'SPECIAL',
                    'name' => 'Cuti Khusus',
                    'accrual_method' => 'yearly',
                    'monthly_accrual_rate' => 0,
                    'is_encashable' => false,
                    'is_active' => true,
                ],
                [
                    'code' => 'ANNUAL',
                    'name' => 'Cuti Tahunan',
                    'accrual_method' => 'monthly',
                    'monthly_accrual_rate' => 1,
                    'is_encashable' => false,
                    'is_active' => true,
                ],
                [
                    'code' => 'UNPAID',
                    'name' => 'Unpaid Leave',
                    'accrual_method' => 'none',
                    'monthly_accrual_rate' => 0,
                    'is_encashable' => false,
                    'is_active' => true,
                ],
            ];

            DB::table('leave_types')
                ->whereIn('code', ['BUSINESS_TRIP_CITY', 'BUSINESS_TRIP_OUT_CITY'])
                ->delete();

            foreach ($leaveTypes as $leaveType) {
                $exists = DB::table('leave_types')
                    ->where('code', $leaveType['code'])
                    ->exists();

                if ($exists) {
                    DB::table('leave_types')
                        ->where('code', $leaveType['code'])
                        ->update([
                            'name' => $leaveType['name'],
                            'accrual_method' => $leaveType['accrual_method'],
                            'monthly_accrual_rate' => $leaveType['monthly_accrual_rate'],
                            'is_encashable' => $leaveType['is_encashable'],
                            'is_active' => $leaveType['is_active'],
                        ]);

                    continue;
                }

                DB::table('leave_types')->insert([
                    'id' => (string) Str::uuid(),
                    'code' => $leaveType['code'],
                    'name' => $leaveType['name'],
                    'accrual_method' => $leaveType['accrual_method'],
                    'monthly_accrual_rate' => $leaveType['monthly_accrual_rate'],
                    'is_encashable' => $leaveType['is_encashable'],
                    'is_active' => $leaveType['is_active'],
                ]);
            }
        } catch (Throwable $throwable) {
            throw new RuntimeException('LeaveTypeSeeder gagal dijalankan.', 0, $throwable);
        }
    }
}
