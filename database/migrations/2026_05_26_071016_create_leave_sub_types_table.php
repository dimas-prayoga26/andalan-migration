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
        Schema::create('leave_sub_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('leave_type_id')->constrained('leave_types', 'id')->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['leave_type_id', 'code'], 'leave_sub_types_leave_type_code_unique');
            $table->unique(['leave_type_id', 'name'], 'leave_sub_types_leave_type_name_unique');
            $table->index(['leave_type_id', 'is_active'], 'leave_sub_types_leave_type_active_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_sub_types');
    }
};
