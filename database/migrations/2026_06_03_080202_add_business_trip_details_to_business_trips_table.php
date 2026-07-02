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
        Schema::table('business_trips', function (Blueprint $table) {
            $table->string('request_number', 50)->nullable()->unique();
            $table->foreignUuid('supervisor_employee_id')->nullable()->constrained('employees', 'id')->nullOnDelete();
            $table->string('province_destination')->nullable();
            $table->string('trip_type', 50)->nullable();
            $table->string('transportation_arrangement', 50)->nullable();
            $table->string('accommodation_arrangement', 50)->nullable();
            $table->string('transportation_mode', 50)->nullable();
            $table->date('departure_date')->nullable();
            $table->string('departure_time_window', 50)->nullable();
            $table->date('check_in_date')->nullable();
            $table->date('check_out_date')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();

            $table->index(['start_date', 'end_date'], 'business_trips_date_range_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_trips', function (Blueprint $table) {
            $table->dropIndex('business_trips_date_range_index');
            $table->dropUnique('business_trips_request_number_unique');
            $table->dropForeign(['supervisor_employee_id']);
            $table->dropColumn([
                'request_number',
                'supervisor_employee_id',
                'province_destination',
                'trip_type',
                'transportation_arrangement',
                'accommodation_arrangement',
                'transportation_mode',
                'departure_date',
                'departure_time_window',
                'check_in_date',
                'check_out_date',
                'submitted_at',
                'approved_at',
                'rejected_at',
            ]);
        });
    }
};
