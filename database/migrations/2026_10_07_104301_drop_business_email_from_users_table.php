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
        if (! Schema::hasColumn('users', 'business_email')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_business_email_unique');
            $table->dropColumn('business_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'business_email')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('business_email')->nullable()->unique()->after('email');
        });
    }
};
