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
        Schema::create('job_vacancies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedInteger('legacy_vacancy_id')->nullable()->unique();
            $table->unsignedInteger('legacy_value')->nullable()->unique();
            $table->string('name');
            $table->string('status')->default('inactive')->index();
            $table->unsignedInteger('legacy_status_value')->nullable();
            $table->timestamp('legacy_created_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('applicants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedInteger('legacy_applicant_id')->unique();
            $table->foreignUuid('job_vacancy_id')->nullable()->constrained('job_vacancies', 'id')->nullOnDelete();
            $table->string('slug')->nullable()->unique();
            $table->string('full_name')->index();
            $table->string('nickname')->nullable();
            $table->string('place_of_birth')->nullable();
            $table->string('date_of_birth')->nullable();
            $table->string('email')->nullable()->index();
            $table->string('phone')->nullable();
            $table->string('gender')->nullable();
            $table->string('marital_status')->nullable();
            $table->text('address')->nullable();
            $table->unsignedInteger('job_applied_legacy_value')->nullable()->index();
            $table->text('expected_salary')->nullable();
            $table->longText('self_resume')->nullable();
            $table->text('portfolio_web_address')->nullable();
            $table->text('cv')->nullable();
            $table->text('photo')->nullable();
            $table->text('agreement')->nullable();
            $table->timestamp('legacy_created_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('applicant_educations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('applicant_id')->constrained('applicants', 'id')->cascadeOnDelete();
            $table->unsignedInteger('sequence')->default(1);
            $table->text('educational_level')->nullable();
            $table->text('institution')->nullable();
            $table->text('gpa')->nullable();
            $table->text('department')->nullable();
            $table->text('start_period')->nullable();
            $table->text('graduate_period')->nullable();
            $table->timestamps();

            $table->unique(['applicant_id', 'sequence']);
        });

        Schema::create('applicant_work_experiences', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('applicant_id')->constrained('applicants', 'id')->cascadeOnDelete();
            $table->unsignedInteger('sequence')->default(1);
            $table->text('company_name')->nullable();
            $table->text('role')->nullable();
            $table->text('company_location')->nullable();
            $table->text('start_period')->nullable();
            $table->text('end_period')->nullable();
            $table->timestamps();

            $table->unique(['applicant_id', 'sequence']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applicant_work_experiences');
        Schema::dropIfExists('applicant_educations');
        Schema::dropIfExists('applicants');
        Schema::dropIfExists('job_vacancies');
    }
};
