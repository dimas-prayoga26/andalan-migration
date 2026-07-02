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
        Schema::table('leave_request_histories', function (Blueprint $table) {
            $table->enum('to_status', ['pending', 'approved', 'rejected', 'refused', 'complete'])
                ->nullable()
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('leave_request_histories')
            ->where('to_status', 'complete')
            ->update(['to_status' => 'approved']);

        Schema::table('leave_request_histories', function (Blueprint $table) {
            $table->enum('to_status', ['pending', 'approved', 'rejected', 'refused'])
                ->nullable()
                ->change();
        });
    }
};
