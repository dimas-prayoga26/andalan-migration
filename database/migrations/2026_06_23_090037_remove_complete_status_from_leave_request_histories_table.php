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
        DB::table('leave_request_histories')
            ->where('to_status', 'complete')
            ->update(['to_status' => 'approved']);

        DB::table('leave_request_histories')
            ->where('from_status', 'refused')
            ->update(['from_status' => 'rejected']);

        DB::table('leave_request_histories')
            ->where('to_status', 'refused')
            ->update(['to_status' => 'rejected']);

        Schema::table('leave_request_histories', function (Blueprint $table) {
            $table->enum('from_status', ['pending', 'approved', 'rejected'])
                ->nullable()
                ->change();
            $table->enum('to_status', ['pending', 'approved', 'rejected'])
                ->nullable()
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_request_histories', function (Blueprint $table) {
            $table->enum('from_status', ['pending', 'approved', 'rejected', 'refused'])
                ->nullable()
                ->change();
            $table->enum('to_status', ['pending', 'approved', 'rejected', 'refused', 'complete'])
                ->nullable()
                ->change();
        });
    }
};
