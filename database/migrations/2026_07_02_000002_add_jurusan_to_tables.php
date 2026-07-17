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
        Schema::table('users', function (Blueprint $table) {
            $table->string('jurusan')->default('keperawatan')->after('role'); // keperawatan, kebidanan, kesehatan_gigi, ortotik_prostetik
        });

        Schema::table('items', function (Blueprint $table) {
            $table->string('jurusan')->default('keperawatan')->after('kategori');
        });

        Schema::table('packages', function (Blueprint $table) {
            $table->string('jurusan')->default('keperawatan')->after('deskripsi');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->string('jurusan')->default('keperawatan')->after('user_id');
        });

        Schema::table('bebas_lab_certificates', function (Blueprint $table) {
            $table->string('jurusan')->default('keperawatan')->after('laboran_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('jurusan');
        });

        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('jurusan');
        });

        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn('jurusan');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('jurusan');
        });

        Schema::table('bebas_lab_certificates', function (Blueprint $table) {
            $table->dropColumn('jurusan');
        });
    }
};
