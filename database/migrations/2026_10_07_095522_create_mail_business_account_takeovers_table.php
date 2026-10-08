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
        Schema::create('mail_business_account_takeovers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mail_access_account_id')
                ->constrained('mail_access_accounts', indexName: 'mail_bus_takeovers_account_fk')
                ->cascadeOnDelete();
            $table->foreignUuid('source_employee_id')
                ->nullable()
                ->constrained('employees', 'id', 'mail_bus_takeovers_source_emp_fk')
                ->nullOnDelete();
            $table->foreignUuid('target_employee_id')
                ->constrained('employees', 'id', 'mail_bus_takeovers_target_emp_fk')
                ->cascadeOnDelete();
            $table->foreignUuid('assigned_by_employee_id')
                ->nullable()
                ->constrained('employees', 'id', 'mail_bus_takeovers_assigned_emp_fk')
                ->nullOnDelete();
            $table->foreignUuid('revoked_by_employee_id')
                ->nullable()
                ->constrained('employees', 'id', 'mail_bus_takeovers_revoked_emp_fk')
                ->nullOnDelete();
            $table->boolean('can_read')->default(true)->index();
            $table->timestamp('starts_at')->nullable()->index();
            $table->timestamp('ends_at')->nullable()->index();
            $table->timestamp('revoked_at')->nullable()->index();
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->index(['target_employee_id', 'can_read', 'revoked_at'], 'mail_business_account_takeovers_target_read_revoked_index');
            $table->index(['mail_access_account_id', 'can_read', 'revoked_at'], 'mail_business_account_takeovers_account_read_revoked_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mail_business_account_takeovers');
    }
};
