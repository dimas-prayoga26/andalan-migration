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
        Schema::table('employee_profiles', function (Blueprint $table) {
            $table->string('shirt_size')->nullable()->after('blood_type');
            $table->string('pants_size')->nullable()->after('shirt_size');
            $table->string('shoe_size')->nullable()->after('pants_size');
            $table->text('medical_history')->nullable()->after('shoe_size');
            $table->text('allergy_notes')->nullable()->after('medical_history');
            $table->string('personal_email')->nullable()->after('profile_picture_path');
            $table->string('linkedin_url')->nullable()->after('personal_email');
            $table->string('portfolio_url')->nullable()->after('linkedin_url');
            $table->json('languages')->nullable()->after('portfolio_url');
            $table->json('hard_skills')->nullable()->after('languages');
            $table->json('soft_skills')->nullable()->after('hard_skills');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'shirt_size',
                'pants_size',
                'shoe_size',
                'medical_history',
                'allergy_notes',
                'personal_email',
                'linkedin_url',
                'portfolio_url',
                'languages',
                'hard_skills',
                'soft_skills',
            ]);
        });
    }
};
