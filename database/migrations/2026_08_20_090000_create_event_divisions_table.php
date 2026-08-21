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
        Schema::create('event_divisions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->string('sub_title')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        $now = now();
        $eventDivisions = [
            ['title' => 'Administration', 'sub_title' => 'Operasional dan pengelolaan dokumen'],
            ['title' => 'Graphic Design', 'sub_title' => 'Materi visual dan aset digital'],
            ['title' => '3D Event Design', 'sub_title' => 'Pemodelan 3D untuk kebutuhan acara'],
            ['title' => 'Documentation', 'sub_title' => 'Video, foto, dan aset audiovisual acara'],
            ['title' => 'Publication & Technology', 'sub_title' => 'Digital publication and IT operational support'],
            ['title' => 'Video Content', 'sub_title' => 'Produksi dan editing konten video acara'],
        ];

        DB::table('event_divisions')->insert(
            array_map(static fn (array $eventDivision): array => [
                'id' => (string) Str::uuid(),
                'title' => $eventDivision['title'],
                'sub_title' => $eventDivision['sub_title'],
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ], $eventDivisions)
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_divisions');
    }
};
