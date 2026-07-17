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
        // 1. Add satuan to items table
        Schema::table('items', function (Blueprint $table) {
            $table->string('satuan')->default('pcs')->after('merk_tipe');
        });

        // 2. Modify status and add catatan_laboran to transactions table
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('status')->default('pending')->change();
            $table->text('catatan_laboran')->nullable()->after('laboran_id');
        });

        // 3. Create packages table
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('nama_paket');
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });

        // 4. Create package_items table
        Schema::create('package_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('packages')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->integer('jumlah')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_items');
        Schema::dropIfExists('packages');

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('catatan_laboran');
        });

        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('satuan');
        });
    }
};
