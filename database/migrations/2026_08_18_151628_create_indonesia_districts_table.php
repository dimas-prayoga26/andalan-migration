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
        Schema::create('indonesia_districts', function (Blueprint $table) {
            $table->id();
            $table->char('code', 7)->unique();
            $table->char('city_code', 4);
            $table->string('name');
            $table->text('meta')->nullable();
            $table->timestamps();

            $table->foreign('city_code')
                ->references('code')
                ->on('indonesia_cities')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('indonesia_districts');
    }
};
