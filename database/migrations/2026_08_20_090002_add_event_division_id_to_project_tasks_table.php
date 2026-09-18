<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('project_tasks', function (Blueprint $table) {
            $table->foreignUuid('event_division_id')->nullable()->after('project_id')->constrained('event_divisions', 'id')->nullOnDelete();
            $table->index(['event_division_id', 'status'], 'project_tasks_event_division_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_tasks', function (Blueprint $table) {
            $table->dropIndex('project_tasks_event_division_status_index');
            $table->dropConstrainedForeignId('event_division_id');
        });
    }
};
