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
        Schema::table('applicants', function (Blueprint $table): void {
            $table->text('expected_salary')->nullable()->change();
            $table->text('cv')->nullable()->change();
            $table->text('photo')->nullable()->change();
        });

        Schema::table('applicant_educations', function (Blueprint $table): void {
            $table->text('educational_level')->nullable()->change();
            $table->text('institution')->nullable()->change();
            $table->text('gpa')->nullable()->change();
            $table->text('department')->nullable()->change();
            $table->text('start_period')->nullable()->change();
            $table->text('graduate_period')->nullable()->change();
        });

        Schema::table('applicant_work_experiences', function (Blueprint $table): void {
            $table->text('company_name')->nullable()->change();
            $table->text('role')->nullable()->change();
            $table->text('company_location')->nullable()->change();
            $table->text('start_period')->nullable()->change();
            $table->text('end_period')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applicants', function (Blueprint $table): void {
            $table->string('expected_salary')->nullable()->change();
            $table->string('cv')->nullable()->change();
            $table->string('photo')->nullable()->change();
        });

        Schema::table('applicant_educations', function (Blueprint $table): void {
            $table->string('educational_level')->nullable()->change();
            $table->string('institution')->nullable()->change();
            $table->string('gpa')->nullable()->change();
            $table->string('department')->nullable()->change();
            $table->string('start_period')->nullable()->change();
            $table->string('graduate_period')->nullable()->change();
        });

        Schema::table('applicant_work_experiences', function (Blueprint $table): void {
            $table->string('company_name')->nullable()->change();
            $table->string('role')->nullable()->change();
            $table->string('company_location')->nullable()->change();
            $table->string('start_period')->nullable()->change();
            $table->string('end_period')->nullable()->change();
        });
    }
};
