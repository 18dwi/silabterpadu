<?php
require __DIR__.'/../'/vendor/autoload.php';
$app = require_once __DIR__.'/../'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$now = now()->format('Y-m-d H:i:s');
$activeBookings = \App\Models\RoomBooking::where('status', 'disetujui')->whereHas('items', function($q) use ($now) {
    $q->whereRaw("CONCAT(tanggal_selesai, ' ', waktu_selesai) >= ?", [$now]);
})->get();

$historyBookings = \App\Models\RoomBooking::where('status', 'disetujui')->whereDoesntHave('items', function($q) use ($now) {
    $q->whereRaw("CONCAT(tanggal_selesai, ' ', waktu_selesai) >= ?", [$now]);
})->get();

echo 'Active: ' . $activeBookings->count() . PHP_EOL;
echo 'History: ' . $historyBookings->count() . PHP_EOL;
