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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('kode_barang')->unique();
            $table->string('nama_barang');
            $table->string('merk_tipe')->nullable();
            $table->enum('kategori', ['alat', 'bahan']);
            $table->integer('stok_total')->default(0);
            $table->integer('stok_tersedia')->default(0);
            $table->integer('jumlah_baik')->default(0);
            $table->integer('jumlah_perbaikan')->default(0);
            $table->integer('jumlah_rusak')->default(0);
            $table->enum('status', ['tersedia', 'dipinjam', 'rusak'])->default('tersedia');
            $table->string('lokasi_rak');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
