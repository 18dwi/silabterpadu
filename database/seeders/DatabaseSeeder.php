<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed Users
        \App\Models\User::create([
            'name' => 'Ultra Admin',
            'email' => 'ultraadmin@example.com',
            'nomor_induk' => '000000',
            'role' => 'ultraadmin',
            'password' => bcrypt('password'),
            'jurusan' => 'keperawatan', // default/fallback
        ]);

        \App\Models\User::create([
            'name' => 'Super Admin Keperawatan',
            'email' => 'superadmin@example.com',
            'nomor_induk' => '999999',
            'role' => 'superadmin',
            'password' => bcrypt('password'),
            'jurusan' => 'keperawatan',
        ]);

        \App\Models\User::create([
            'name' => 'Laboran Lab',
            'email' => 'laboran@example.com',
            'nomor_induk' => '123456',
            'role' => 'laboran',
            'password' => bcrypt('password'),
            'jurusan' => 'keperawatan',
        ]);

        \App\Models\User::create([
            'name' => 'Mahasiswa Keperawatan',
            'email' => 'mahasiswa@example.com',
            'nomor_induk' => '789012',
            'role' => 'mahasiswa',
            'password' => bcrypt('password'),
            'jurusan' => 'keperawatan',
        ]);

        // Seed Items (Tools & Materials)
        \App\Models\Item::create([
            'kode_barang' => 'ALT-001',
            'nama_barang' => 'Stetoskop Littmann',
            'merk_tipe' => 'Classic III',
            'kategori' => 'alat',
            'stok_total' => 10,
            'stok_tersedia' => 10,
            'jumlah_baik' => 10,
            'jumlah_perbaikan' => 0,
            'jumlah_rusak' => 0,
            'status' => 'tersedia',
            'lokasi_rak' => 'Rak A-1',
        ]);

        \App\Models\Item::create([
            'kode_barang' => 'ALT-002',
            'nama_barang' => 'Tensimeter Digital Omron',
            'merk_tipe' => 'HEM-7156',
            'kategori' => 'alat',
            'stok_total' => 8,
            'stok_tersedia' => 8,
            'jumlah_baik' => 8,
            'jumlah_perbaikan' => 0,
            'jumlah_rusak' => 0,
            'status' => 'tersedia',
            'lokasi_rak' => 'Rak A-2',
        ]);

        \App\Models\Item::create([
            'kode_barang' => 'ALT-003',
            'nama_barang' => 'Termometer Infrared',
            'merk_tipe' => 'Lotus',
            'kategori' => 'alat',
            'stok_total' => 12,
            'stok_tersedia' => 12,
            'jumlah_baik' => 11,
            'jumlah_perbaikan' => 1,
            'jumlah_rusak' => 0,
            'status' => 'tersedia',
            'lokasi_rak' => 'Rak A-3',
        ]);

        \App\Models\Item::create([
            'kode_barang' => 'BHN-001',
            'nama_barang' => 'Spuit / Jarum Suntik 3cc',
            'merk_tipe' => 'Terumo',
            'kategori' => 'bahan',
            'stok_total' => 150,
            'stok_tersedia' => 150,
            'jumlah_baik' => 0,
            'jumlah_perbaikan' => 0,
            'jumlah_rusak' => 0,
            'status' => 'tersedia',
            'lokasi_rak' => 'Rak B-1',
        ]);

        \App\Models\Item::create([
            'kode_barang' => 'BHN-002',
            'nama_barang' => 'Kasa Steril 10x10',
            'merk_tipe' => 'OneMed',
            'kategori' => 'bahan',
            'stok_total' => 200,
            'stok_tersedia' => 200,
            'jumlah_baik' => 0,
            'jumlah_perbaikan' => 0,
            'jumlah_rusak' => 0,
            'status' => 'tersedia',
            'lokasi_rak' => 'Rak B-2',
        ]);

        \App\Models\Item::create([
            'kode_barang' => 'BHN-003',
            'nama_barang' => 'Alcohol Swab',
            'merk_tipe' => 'OneMed',
            'kategori' => 'bahan',
            'stok_total' => 500,
            'stok_tersedia' => 500,
            'jumlah_baik' => 0,
            'jumlah_perbaikan' => 0,
            'jumlah_rusak' => 0,
            'status' => 'tersedia',
            'lokasi_rak' => 'Rak B-3',
        ]);
    }
}
