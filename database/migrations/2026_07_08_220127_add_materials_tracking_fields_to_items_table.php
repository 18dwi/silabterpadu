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
        Schema::table('items', function (Blueprint $table) {
            $table->integer('stok_awal')->default(0)->nullable()->after('stok_total');
            $table->integer('bahan_masuk')->default(0)->nullable()->after('stok_awal');
            $table->string('tanggal_masuk')->nullable()->after('bahan_masuk');
            $table->integer('bahan_keluar')->default(0)->nullable()->after('tanggal_masuk');
            $table->string('tanggal_keluar')->nullable()->after('bahan_keluar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn(['stok_awal', 'bahan_masuk', 'tanggal_masuk', 'bahan_keluar', 'tanggal_keluar']);
        });
    }
};
