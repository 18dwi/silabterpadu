<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('kode_ruangan', 30)->unique();
            $table->string('nama_ruangan', 100);
            $table->integer('kapasitas')->default(1);
            $table->string('lokasi', 150)->nullable();
            $table->text('deskripsi')->nullable();
            $table->string('jurusan', 100);
            $table->enum('status', ['tersedia', 'nonaktif'])->default('tersedia');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
