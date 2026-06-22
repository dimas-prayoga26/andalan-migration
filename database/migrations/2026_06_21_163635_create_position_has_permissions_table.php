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
        Schema::create('position_has_permissions', function (Blueprint $table) {
            $table->foreignUuid('position_id')->constrained('positions', 'id')->cascadeOnDelete();
            $table->foreignUuid('permission_id')->constrained('permissions', 'uuid')->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['position_id', 'permission_id'], 'position_has_permissions_primary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('position_has_permissions');
    }
};
