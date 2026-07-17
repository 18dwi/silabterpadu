<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_ruangan',
        'nama_ruangan',
        'kapasitas',
        'lokasi',
        'deskripsi',
        'jurusan',
        'status',
    ];

    public function bookingItems()
    {
        return $this->hasMany(RoomBookingItem::class);
    }

    /**
     * Check if the room is available for a given date-time range.
     */
    public function isAvailableFor($tanggal_mulai, $tanggal_selesai, $waktu_mulai, $waktu_selesai, $excludeBookingId = null): bool
    {
        $query = RoomBookingItem::where('room_id', $this->id)
            ->whereHas('booking', function ($q) use ($excludeBookingId) {
                $q->whereIn('status', ['pending', 'disetujui']);
                if ($excludeBookingId) {
                    $q->where('id', '!=', $excludeBookingId);
                }
            })
            ->where(function ($q) use ($tanggal_mulai, $tanggal_selesai, $waktu_mulai, $waktu_selesai) {
                // Date range overlaps
                $q->where('tanggal_mulai', '<=', $tanggal_selesai)
                  ->where('tanggal_selesai', '>=', $tanggal_mulai)
                  // Time range overlaps
                  ->where('waktu_mulai', '<', $waktu_selesai)
                  ->where('waktu_selesai', '>', $waktu_mulai);
            });

        return $query->count() === 0;
    }
}
