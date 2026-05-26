<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class LeaveSubTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            if (! Schema::hasTable('leave_sub_types') || ! Schema::hasTable('leave_types')) {
                return;
            }

            $specialLeaveTypeId = DB::table('leave_types')
                ->where('code', 'SPECIAL')
                ->value('id');

            if (! is_string($specialLeaveTypeId) || trim($specialLeaveTypeId) === '') {
                return;
            }

            $subTypes = [
                [
                    'code' => 'EMPLOYEE_MARRIAGE',
                    'name' => 'Employee\'s marriage (3 days)',
                ],
                [
                    'code' => 'EMPLOYEE_WIFE_GIVING_BIRTH',
                    'name' => 'Employee\'s wife giving birth (3 days)',
                ],
                [
                    'code' => 'DEATH_OF_CLOSE_FAMILY',
                    'name' => 'Death of spouse, child, child-in-law, parent, or parent-in-law (3 days)',
                ],
                [
                    'code' => 'DEATH_OF_SIBLING',
                    'name' => 'Death of sibling or sibling-in-law (1 days)',
                ],
                [
                    'code' => 'MARRIAGE_OF_EMPLOYEE_CHILD',
                    'name' => 'Marriage of employee\'s child (2 days)',
                ],
                [
                    'code' => 'MARRIAGE_OF_EMPLOYEE_SIBLING',
                    'name' => 'Marriage of employee\'s sibling or sibling-in-law (1 days)',
                ],
                [
                    'code' => 'CIRCUMCISION_OF_EMPLOYEE_CHILD',
                    'name' => 'Circumcision of employee\'s child (2 days)',
                ],
                [
                    'code' => 'BAPTISM_OF_EMPLOYEE_CHILD',
                    'name' => 'Baptism of employee\'s child (2 days)',
                ],
                [
                    'code' => 'UNIVERSITY_GRADUATION_OF_CHILD_OR_SPOUSE',
                    'name' => 'Attending the university graduation of employee\'s child or spouse (1 days)',
                ],
            ];

            foreach ($subTypes as $subType) {
                DB::table('leave_sub_types')->updateOrInsert(
                    [
                        'leave_type_id' => $specialLeaveTypeId,
                        'code' => $subType['code'],
                    ],
                    [
                        'id' => (string) Str::uuid(),
                        'name' => $subType['name'],
                        'is_active' => true,
                        'deleted_at' => null,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ],
                );
            }
        } catch (Throwable $throwable) {
            throw new RuntimeException('LeaveSubTypeSeeder gagal dijalankan.', 0, $throwable);
        }
    }
}
