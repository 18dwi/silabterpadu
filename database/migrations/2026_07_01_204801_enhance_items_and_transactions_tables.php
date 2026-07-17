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
            $table->integer('jumlah_rusak_ringan')->default(0)->after('jumlah_baik');
            $table->integer('jumlah_rusak_sedang')->default(0)->after('jumlah_rusak_ringan');
            $table->integer('jumlah_rusak_berat')->default(0)->after('jumlah_rusak_sedang');
            $table->string('tahun_kedatangan')->nullable()->after('status');
            $table->enum('tindak_lanjut', ['kalibrasi', 'perawatan', 'penghapusan', 'perbaikan'])->nullable()->after('tahun_kedatangan');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
            $table->boolean('is_insidentil')->default(false)->after('laboran_id');
            $table->string('peminjam_insidentil')->nullable()->after('is_insidentil');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn(['jumlah_rusak_ringan', 'jumlah_rusak_sedang', 'jumlah_rusak_berat', 'tahun_kedatangan', 'tindak_lanjut']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
            $table->dropColumn(['is_insidentil', 'peminjam_insidentil']);
        });
    }
};
