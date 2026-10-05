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
        Schema::table('mail_access_accounts', function (Blueprint $table) {
            $table->string('type', 30)
                ->default('personal')
                ->after('email')
                ->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mail_access_accounts', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
