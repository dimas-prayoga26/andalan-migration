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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_user')->constrained('users')->cascadeOnDelete();
            $table->enum('domisili', ['Yogyakarta', 'Jakarta']);
            $table->date('tanggal');
            $table->time('jam_masuk');
            $table->time('jam_keluar');
            $table->enum('status', ['Terlambat', 'Hadir']);
        });

        Schema::create('attendances_overtime', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_user')->constrained('users')->cascadeOnDelete();
            $table->date('tanggal_lembur');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->text('keterangan');
            $table->text('catatan')->nullable();
            $table->enum('status_persetujuan', ['Pending', 'Ditolak', 'Disetujui'])->default('Pending');
        });

        Schema::create('attendances_leave', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_user')->constrained('users')->cascadeOnDelete();
            $table->date('tanggal_awal');
            $table->date('tanggal_akhir');
            $table->text('alasan');
            $table->enum('status_persetujuan', ['Pending', 'Ditolak', 'Disetujui'])->default('Pending');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('attendances_leave');
        Schema::dropIfExists('attendances_overtime');
    }
};
