<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BebasLabCertificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'laboran_id',
        'nomor_surat',
        'tanggal_terbit',
        'jurusan',
    ];

    protected $casts = [
        'tanggal_terbit' => 'date',
    ];

    /**
     * Get the student (user) who owns this certificate.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the laboran staff who issued this certificate.
     */
    public function laboran()
    {
        return $this->belongsTo(User::class, 'laboran_id');
    }
}
