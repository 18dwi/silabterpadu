<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'laboran_id',
        'tujuan_penggunaan',
        'jumlah_mahasiswa',
        'status',
        'catatan_laboran',
        'jurusan',
        'tanggal_pengajuan',
        'qr_token',
        'is_insidentil',
        'peminjam_insidentil',
        'institusi_insidentil',
    ];

    protected $casts = [
        'tanggal_pengajuan' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function laboran()
    {
        return $this->belongsTo(User::class, 'laboran_id');
    }

    public function items()
    {
        return $this->hasMany(RoomBookingItem::class, 'room_booking_id');
    }
}
