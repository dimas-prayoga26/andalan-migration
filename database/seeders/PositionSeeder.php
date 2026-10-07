<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            $now = now();
            $positions = [
                ['id' => 'b83e3e17-6e78-4c60-92b2-83d97119651b', 'name' => 'Super Administrator', 'system_key' => Position::KEY_SUPER_ADMINISTRATOR, 'is_protected' => true],
                ['id' => '937caf48-264e-4cd1-b7e6-9f734f57d8b9', 'name' => 'Administrator', 'system_key' => Position::KEY_ADMINISTRATOR, 'is_protected' => true],
                ['id' => 'af1298b0-da98-4491-b789-71d4fb7efe3a', 'name' => 'Commissioner Independent', 'system_key' => Position::KEY_COMMISSIONER_INDEPENDENT, 'is_protected' => false],
                ['id' => '6e32474e-9535-4553-a56d-b2e3ed54a289', 'name' => 'Commissioner', 'system_key' => Position::KEY_COMMISSIONER, 'is_protected' => false],
                ['id' => '7c3503fe-a7fc-465c-bd8e-0aac4c55358a', 'name' => 'Chief Operating Officer', 'system_key' => Position::KEY_CHIEF_OPERATING_OFFICER, 'is_protected' => false],
                ['id' => '70e774da-1d91-45a0-a90f-6e954e4f5cb2', 'name' => 'Director', 'system_key' => Position::KEY_DIRECTOR, 'is_protected' => true],
                ['id' => '6bc2a25f-f156-4c50-9234-a94072870854', 'name' => 'Supervisor', 'system_key' => Position::KEY_SUPERVISOR, 'is_protected' => true],
                ['id' => 'fd7eb434-5abd-4e75-9400-50f79e68281e', 'name' => 'Legal Officer & Partnership', 'system_key' => Position::KEY_LEGAL_OFFICER_PARTNERSHIP, 'is_protected' => false],
                ['id' => '5da74d77-4d54-4058-ab61-31a5e720790a', 'name' => 'Finance and Administration Coordinator', 'system_key' => Position::KEY_FINANCE_ADMINISTRATION_COORDINATOR, 'is_protected' => false],
                ['id' => '33629e62-ab37-41ea-985e-5daf99a077b8', 'name' => 'Accounting and Taxation', 'system_key' => Position::KEY_ACCOUNTING_TAXATION, 'is_protected' => false],
                ['id' => '818713df-8586-824e-bc63-24cf7f8df01b', 'name' => 'Digital Marketing', 'system_key' => Position::KEY_DIGITAL_MARKETING, 'is_protected' => false],
                ['id' => '57bb6820-448f-4117-8705-65215a7718c7', 'name' => 'Operations Coordinator', 'system_key' => Position::KEY_OPERATIONS_COORDINATOR, 'is_protected' => false],
                ['id' => 'de585fc6-9177-445d-812d-3d4933f49727', 'name' => 'Interior Design', 'system_key' => Position::KEY_INTERIOR_DESIGN, 'is_protected' => false],
                ['id' => 'c11aa57c-bbaa-42c3-8f6c-04e7b7c97c7a', 'name' => 'Architecture Design', 'system_key' => Position::KEY_ARCHITECTURE_DESIGN, 'is_protected' => false],
                ['id' => '64729573-7206-4392-9d37-eb3db711b8e6', 'name' => 'Web Developer', 'system_key' => Position::KEY_WEB_DEVELOPER, 'is_protected' => false],
                ['id' => '6a0d7c4a-0c29-4c3f-bb0a-3efb1cb6788c', 'name' => 'Documentation Event and Editor Video', 'system_key' => Position::KEY_DOCUMENTATION_EVENT_EDITOR_VIDEO, 'is_protected' => false],
                ['id' => 'd7a60d6f-c624-4968-9a3f-a082af1fce54', 'name' => 'Graphic Design', 'system_key' => Position::KEY_GRAPHIC_DESIGN, 'is_protected' => false],
                ['id' => 'fda07fc0-722c-4cd8-aeaa-82556f777a24', 'name' => 'Branding Designer', 'system_key' => Position::KEY_BRANDING_DESIGNER, 'is_protected' => false],
                ['id' => '3eb4e17c-56a0-455c-8d84-55677618cede', 'name' => 'Driver', 'system_key' => Position::KEY_DRIVER, 'is_protected' => false],
            ];

            foreach ($positions as $position) {
                $positionId = DB::table('positions')->where('name', $position['name'])->value('id');

                if (is_string($positionId) && trim($positionId) !== '') {
                    DB::table('positions')
                        ->where('id', $positionId)
                        ->update([
                            'system_key' => $position['system_key'],
                            'is_protected' => $position['is_protected'],
                            'status' => 'active',
                            'updated_at' => $now,
                        ]);

                    continue;
                }

                DB::table('positions')->insert([
                    'id' => $position['id'],
                    'name' => $position['name'],
                    'system_key' => $position['system_key'],
                    'status' => 'active',
                    'is_protected' => $position['is_protected'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        } catch (Throwable $throwable) {
            throw new RuntimeException('PositionSeeder gagal dijalankan.', 0, $throwable);
        }
    }
}
