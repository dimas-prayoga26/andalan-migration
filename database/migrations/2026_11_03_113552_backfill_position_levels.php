<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Positions not matched here stay core_staff; HR can adjust any of them in Setting > Position.
     */
    public function up(): void
    {
        foreach ($this->levelSystemKeys() as $level => $systemKeys) {
            DB::table('positions')
                ->whereIn('system_key', $systemKeys)
                ->update([
                    'level' => $level,
                    'updated_at' => now(),
                ]);
        }

        DB::table('positions')
            ->whereNull('system_key')
            ->whereIn(DB::raw('LOWER(TRIM(name))'), $this->nonCoreStaffNames())
            ->update([
                'level' => 'non_core_staff',
                'updated_at' => now(),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('positions')->update(['level' => 'core_staff']);
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function levelSystemKeys(): array
    {
        return [
            'administrator' => ['administrator'],
            'director' => ['director', 'chief_operating_officer', 'commissioner', 'commissioner_independent'],
            'supervisor' => ['supervisor'],
            'non_core_staff' => ['driver', 'executive_assistant'],
        ];
    }

    /**
     * Production positions without a system key that do not take part in team work such as weekly meetings.
     *
     * @return array<int, string>
     */
    private function nonCoreStaffNames(): array
    {
        return ['driver', 'executive assistant', 'crew', 'barista'];
    }
};
