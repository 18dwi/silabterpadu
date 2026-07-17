<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('room_bookings', function (Blueprint $table) {
            $table->boolean('is_insidentil')->default(false)->after('jurusan');
            $table->string('peminjam_insidentil', 200)->nullable()->after('is_insidentil');
            $table->string('institusi_insidentil', 200)->nullable()->after('peminjam_insidentil');
        });
    }

    public function down(): void
    {
        Schema::table('room_bookings', function (Blueprint $table) {
            $table->dropColumn(['is_insidentil', 'peminjam_insidentil', 'institusi_insidentil']);
        });
    }
};
