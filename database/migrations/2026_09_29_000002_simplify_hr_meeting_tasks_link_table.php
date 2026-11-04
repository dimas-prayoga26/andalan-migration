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
        if (! Schema::hasIndex('hr_meeting_tasks', 'hr_meeting_tasks_meeting_category_index')) {
            Schema::table('hr_meeting_tasks', function (Blueprint $table) {
                $table->index(['hr_meeting_id', 'category'], 'hr_meeting_tasks_meeting_category_index');
            });
        }

        if (Schema::hasIndex('hr_meeting_tasks', 'hr_meeting_tasks_meeting_category_status_index')) {
            Schema::table('hr_meeting_tasks', function (Blueprint $table) {
                $table->dropIndex('hr_meeting_tasks_meeting_category_status_index');
            });
        }

        if (Schema::hasIndex('hr_meeting_tasks', 'hr_meeting_tasks_meeting_status_index')) {
            Schema::table('hr_meeting_tasks', function (Blueprint $table) {
                $table->dropIndex('hr_meeting_tasks_meeting_status_index');
            });
        }

        if (Schema::hasColumn('hr_meeting_tasks', 'assigned_to')) {
            Schema::table('hr_meeting_tasks', function (Blueprint $table) {
                $table->dropForeign(['assigned_to']);
            });
        }

        $columns = collect(['assigned_to', 'title', 'description', 'due_date', 'status', 'completed_at'])
            ->filter(fn (string $column): bool => Schema::hasColumn('hr_meeting_tasks', $column))
            ->values()
            ->all();

        if ($columns !== []) {
            Schema::table('hr_meeting_tasks', function (Blueprint $table) use ($columns): void {
                $table->dropColumn($columns);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasIndex('hr_meeting_tasks', 'hr_meeting_tasks_meeting_category_index')) {
            Schema::table('hr_meeting_tasks', function (Blueprint $table) {
                $table->dropIndex('hr_meeting_tasks_meeting_category_index');
            });
        }

        Schema::table('hr_meeting_tasks', function (Blueprint $table) {
            if (! Schema::hasColumn('hr_meeting_tasks', 'assigned_to')) {
                $table->foreignUuid('assigned_to')->nullable()->after('project_task_id')->constrained('employees', 'id')->nullOnDelete();
            }

            if (! Schema::hasColumn('hr_meeting_tasks', 'title')) {
                $table->string('title')->nullable()->after('assigned_to');
            }

            if (! Schema::hasColumn('hr_meeting_tasks', 'description')) {
                $table->text('description')->nullable()->after('title');
            }

            if (! Schema::hasColumn('hr_meeting_tasks', 'due_date')) {
                $table->date('due_date')->nullable()->after('description');
            }

            if (! Schema::hasColumn('hr_meeting_tasks', 'status')) {
                $table->string('status')->default('pending')->after('due_date');
            }

            if (! Schema::hasColumn('hr_meeting_tasks', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('status');
            }
        });

        if (! Schema::hasIndex('hr_meeting_tasks', 'hr_meeting_tasks_meeting_status_index')) {
            Schema::table('hr_meeting_tasks', function (Blueprint $table) {
                $table->index(['hr_meeting_id', 'status'], 'hr_meeting_tasks_meeting_status_index');
            });
        }
    }
};
