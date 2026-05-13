<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('status')->default('active');
            $table->timestamps();
        });

        $now = now();
        $departmentNames = [
            'Superuser',
            'Administrator',
            'Board of Directors',
            'Administration, Finance and Legal',
            'Operations',
            'Project Planning and Development',
            'Information and Communications Technology',
            'Marketing and Promotion',
        ];

        DB::table('departments')->insert(
            array_map(static fn (string $departmentName): array => [
                'id' => (string) Str::uuid(),
                'name' => $departmentName,
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ], $departmentNames)
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
