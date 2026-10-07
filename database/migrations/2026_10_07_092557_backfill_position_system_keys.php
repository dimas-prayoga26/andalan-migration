<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->positionSystemKeys() as $name => $metadata) {
            DB::table('positions')
                ->where('name', $name)
                ->update([
                    'system_key' => $metadata['system_key'],
                    'is_protected' => $metadata['is_protected'],
                    'updated_at' => now(),
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('positions')
            ->whereIn('system_key', collect($this->positionSystemKeys())->pluck('system_key')->all())
            ->update([
                'system_key' => null,
                'is_protected' => false,
                'updated_at' => now(),
            ]);
    }

    /**
     * @return array<string, array{system_key: string, is_protected: bool}>
     */
    private function positionSystemKeys(): array
    {
        return [
            'Super Administrator' => ['system_key' => 'super_administrator', 'is_protected' => true],
            'Administrator' => ['system_key' => 'administrator', 'is_protected' => true],
            'Commissioner Independent' => ['system_key' => 'commissioner_independent', 'is_protected' => false],
            'Commissioner' => ['system_key' => 'commissioner', 'is_protected' => false],
            'Chief Operating Officer' => ['system_key' => 'chief_operating_officer', 'is_protected' => false],
            'Director' => ['system_key' => 'director', 'is_protected' => true],
            'Supervisor' => ['system_key' => 'supervisor', 'is_protected' => true],
            'Legal Officer & Partnership' => ['system_key' => 'legal_officer_partnership', 'is_protected' => false],
            'Finance and Administration Coordinator' => ['system_key' => 'finance_administration_coordinator', 'is_protected' => false],
            'Accounting and Taxation' => ['system_key' => 'accounting_taxation', 'is_protected' => false],
            'Operations Coordinator' => ['system_key' => 'operations_coordinator', 'is_protected' => false],
            'Interior Design' => ['system_key' => 'interior_design', 'is_protected' => false],
            'Architecture Design' => ['system_key' => 'architecture_design', 'is_protected' => false],
            'Web Developer' => ['system_key' => 'web_developer', 'is_protected' => false],
            'Documentation Event and Editor Video' => ['system_key' => 'documentation_event_editor_video', 'is_protected' => false],
            'Graphic Design' => ['system_key' => 'graphic_design', 'is_protected' => false],
            'Branding Designer' => ['system_key' => 'branding_designer', 'is_protected' => false],
            'Driver' => ['system_key' => 'driver', 'is_protected' => false],
            'Executive Assistant' => ['system_key' => 'executive_assistant', 'is_protected' => false],
        ];
    }
};
