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
            $table->integer('tahun_datang')->nullable()->after('status');
            $table->date('tanggal_expired')->nullable()->after('tahun_datang');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('program_studi')->nullable()->after('jurusan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn(['tahun_datang', 'tanggal_expired']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('program_studi');
        });
    }
};
