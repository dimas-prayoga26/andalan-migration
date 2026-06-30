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
            Schema::create('employee_deployment_positions', function (Blueprint $table): void {
                $table->foreignUuid('employee_deployment_id')->constrained('employee_deployments', 'id')->cascadeOnDelete();
                $table->foreignUuid('position_id')->constrained('positions', 'id')->cascadeOnDelete();
                $table->boolean('is_primary')->default(false);
                $table->string('status')->default('active');
                $table->date('started_at')->nullable();
                $table->date('ended_at')->nullable();
                $table->timestamps();

                $table->primary(['employee_deployment_id', 'position_id'], 'employee_deployment_positions_primary');
                $table->index(['position_id', 'status'], 'employee_deployment_positions_position_status_index');
            });
        }

        $now = now();

        DB::table('employee_deployments')
            ->select(['id', 'current_position_id', 'join_date', 'resignation_date'])
            ->whereNotNull('current_position_id')
            ->orderBy('id')
            ->get()
            ->each(function (object $deployment) use ($now): void {
                DB::table('employee_deployment_positions')->updateOrInsert(
                    [
                        'employee_deployment_id' => $deployment->id,
                        'position_id' => $deployment->current_position_id,
                    ],
                    [
                        'is_primary' => true,
                        'status' => 'active',
                        'started_at' => $deployment->join_date,
                        'ended_at' => $deployment->resignation_date,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ],
                );
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_deployment_positions');
    }
};
