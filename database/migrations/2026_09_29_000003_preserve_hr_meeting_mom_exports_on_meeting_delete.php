<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('hr_meeting_mom_exports') || ! Schema::hasColumn('hr_meeting_mom_exports', 'hr_meeting_id')) {
            return;
        }

        DB::statement('ALTER TABLE hr_meeting_mom_exports DROP FOREIGN KEY hr_meeting_mom_exports_hr_meeting_id_foreign');
        DB::statement('ALTER TABLE hr_meeting_mom_exports MODIFY hr_meeting_id CHAR(36) NULL');
        DB::statement('ALTER TABLE hr_meeting_mom_exports ADD CONSTRAINT hr_meeting_mom_exports_hr_meeting_id_foreign FOREIGN KEY (hr_meeting_id) REFERENCES hr_meetings(id) ON DELETE SET NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('hr_meeting_mom_exports') || ! Schema::hasColumn('hr_meeting_mom_exports', 'hr_meeting_id')) {
            return;
        }

        DB::statement('ALTER TABLE hr_meeting_mom_exports DROP FOREIGN KEY hr_meeting_mom_exports_hr_meeting_id_foreign');
        DB::statement('ALTER TABLE hr_meeting_mom_exports MODIFY hr_meeting_id CHAR(36) NOT NULL');
        DB::statement('ALTER TABLE hr_meeting_mom_exports ADD CONSTRAINT hr_meeting_mom_exports_hr_meeting_id_foreign FOREIGN KEY (hr_meeting_id) REFERENCES hr_meetings(id) ON DELETE CASCADE');
    }
};
