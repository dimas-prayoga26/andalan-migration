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
        if (! Schema::hasTable('employee_deployment_positions')) {
            return;
        }

        if (! Schema::hasColumn('employee_deployment_positions', 'sort_order')) {
            Schema::table('employee_deployment_positions', function (Blueprint $table): void {
                $table->unsignedSmallInteger('sort_order')->default(0)->after('is_primary');
            });
        }

        DB::table('employee_deployment_positions')
            ->select(['employee_deployment_id'])
            ->distinct()
            ->orderBy('employee_deployment_id')
            ->get()
            ->each(function (object $deployment): void {
                DB::table('employee_deployment_positions')
                    ->select(['employee_deployment_id', 'position_id'])
                    ->where('employee_deployment_id', $deployment->employee_deployment_id)
                    ->orderByDesc('is_primary')
                    ->orderBy('created_at')
                    ->orderBy('position_id')
                    ->get()
                    ->each(function (object $position, int $index): void {
                        DB::table('employee_deployment_positions')
                            ->where('employee_deployment_id', $position->employee_deployment_id)
                            ->where('position_id', $position->position_id)
                            ->update(['sort_order' => $index]);
                    });
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('employee_deployment_positions') || ! Schema::hasColumn('employee_deployment_positions', 'sort_order')) {
            return;
        }

        Schema::table('employee_deployment_positions', function (Blueprint $table): void {
            $table->dropColumn('sort_order');
        });
    }
};
