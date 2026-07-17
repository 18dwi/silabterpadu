<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('laboran_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('tujuan_penggunaan');
            $table->integer('jumlah_mahasiswa')->default(1);
            $table->enum('status', ['pending', 'disetujui', 'ditolak'])->default('pending');
            $table->text('catatan_laboran')->nullable();
            $table->string('jurusan', 100);
            $table->datetime('tanggal_pengajuan');
            $table->string('qr_token', 100)->nullable()->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_bookings');
    }
};
