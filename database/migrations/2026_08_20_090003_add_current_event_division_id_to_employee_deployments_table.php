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
        Schema::table('employee_deployments', function (Blueprint $table) {
            $table->foreignUuid('current_event_division_id')->nullable()->after('current_department_id')->constrained('event_divisions', 'id')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_deployments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('current_event_division_id');
        });
    }
};
