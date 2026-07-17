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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('laboran_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('tipe', ['peminjaman_alat', 'permintaan_bahan']);
            $table->dateTime('tanggal_pengajuan');
            $table->dateTime('tanggal_pinjam')->nullable();
            $table->dateTime('tanggal_kembali_rencana')->nullable();
            $table->dateTime('tanggal_kembali_realisasi')->nullable();
            $table->string('penanggung_jawab');
            $table->string('kegiatan');
            $table->enum('status', ['pending', 'disetujui', 'ditolak', 'selesai']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
