<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'tipe',
        'tanggal_pengajuan',
        'tanggal_pinjam',
        'tanggal_kembali_rencana',
        'tanggal_kembali_realisasi',
        'penanggung_jawab',
        'kegiatan',
        'status',
        'laboran_id',
        'status_pengembalian',
        'catatan_pengembalian',
        'is_insidentil',
        'peminjam_insidentil',
        'jurusan',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'tanggal_pengajuan' => 'datetime',
        'tanggal_pinjam' => 'datetime',
        'tanggal_kembali_rencana' => 'datetime',
        'tanggal_kembali_realisasi' => 'datetime',
    ];

    /**
     * Get the user who made the transaction.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the details for the transaction.
     */
    public function details()
    {
        return $this->hasMany(TransactionDetail::class);
    }

    /**
     * Get the laboran who verified the transaction.
     */
    public function laboran()
    {
        return $this->belongsTo(User::class, 'laboran_id');
    }
}
