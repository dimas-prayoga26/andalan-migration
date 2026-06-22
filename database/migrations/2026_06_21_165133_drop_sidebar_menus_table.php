<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('sidebar_menus');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op: sidebar_menus is intentionally removed from the authorization flow.
    }
};
