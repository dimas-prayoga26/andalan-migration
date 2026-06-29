<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if ($this->projectIdIsNullable()) {
            return;
        }

        Schema::table('project_tasks', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->uuid('project_id')->nullable()->change();
            $table->foreign('project_id')->references('id')->on('projects')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! $this->projectIdIsNullable()) {
            return;
        }

        Schema::table('project_tasks', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->uuid('project_id')->nullable(false)->change();
            $table->foreign('project_id')->references('id')->on('projects')->cascadeOnDelete();
        });
    }

    private function projectIdIsNullable(): bool
    {
        return DB::table('information_schema.COLUMNS')
            ->where('TABLE_SCHEMA', config('database.connections.mysql.database'))
            ->where('TABLE_NAME', 'project_tasks')
            ->where('COLUMN_NAME', 'project_id')
            ->value('IS_NULLABLE') === 'YES';
    }
};
