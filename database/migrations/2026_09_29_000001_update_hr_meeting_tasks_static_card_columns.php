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
        if (! Schema::hasColumn('hr_meeting_tasks', 'category')) {
            Schema::table('hr_meeting_tasks', function (Blueprint $table) {
                $table->string('category')->default('others')->after('hr_meeting_id');
            });
        }

        if (! Schema::hasColumn('hr_meeting_tasks', 'project_task_id')) {
            Schema::table('hr_meeting_tasks', function (Blueprint $table) {
                $table->foreignUuid('project_task_id')->nullable()->after('category')->constrained('project_tasks', 'id')->nullOnDelete();
            });
        }

        if (
            Schema::hasColumn('hr_meeting_tasks', 'status')
            && ! Schema::hasIndex('hr_meeting_tasks', 'hr_meeting_tasks_meeting_category_status_index')
        ) {
            Schema::table('hr_meeting_tasks', function (Blueprint $table) {
                $table->index(['hr_meeting_id', 'category', 'status'], 'hr_meeting_tasks_meeting_category_status_index');
            });
        }

        if (Schema::hasColumn('hr_meeting_tasks', 'event_division_id')) {
            Schema::table('hr_meeting_tasks', function (Blueprint $table) {
                $table->dropForeign(['event_division_id']);
            });

            if (Schema::hasIndex('hr_meeting_tasks', 'hr_meeting_tasks_division_status_index')) {
                Schema::table('hr_meeting_tasks', function (Blueprint $table) {
                    $table->dropIndex('hr_meeting_tasks_division_status_index');
                });
            }

            Schema::table('hr_meeting_tasks', function (Blueprint $table) {
                $table->dropColumn('event_division_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('hr_meeting_tasks', 'event_division_id')) {
            Schema::table('hr_meeting_tasks', function (Blueprint $table) {
                $table->foreignUuid('event_division_id')->nullable()->after('hr_meeting_id')->constrained('event_divisions', 'id')->nullOnDelete();
            });
        }

        if (
            Schema::hasColumn('hr_meeting_tasks', 'status')
            && Schema::hasColumn('hr_meeting_tasks', 'event_division_id')
            && ! Schema::hasIndex('hr_meeting_tasks', 'hr_meeting_tasks_division_status_index')
        ) {
            Schema::table('hr_meeting_tasks', function (Blueprint $table) {
                $table->index(['event_division_id', 'status'], 'hr_meeting_tasks_division_status_index');
            });
        }

        if (Schema::hasIndex('hr_meeting_tasks', 'hr_meeting_tasks_meeting_category_status_index')) {
            Schema::table('hr_meeting_tasks', function (Blueprint $table) {
                $table->dropIndex('hr_meeting_tasks_meeting_category_status_index');
            });
        }

        if (Schema::hasColumn('hr_meeting_tasks', 'project_task_id')) {
            Schema::table('hr_meeting_tasks', function (Blueprint $table) {
                $table->dropConstrainedForeignId('project_task_id');
            });
        }

        if (Schema::hasColumn('hr_meeting_tasks', 'category')) {
            Schema::table('hr_meeting_tasks', function (Blueprint $table) {
                $table->dropColumn('category');
            });
        }
    }
};
