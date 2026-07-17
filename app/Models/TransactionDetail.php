<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionDetail extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'transaction_id',
        'package_id',
        'package_qty',
        'item_id',
        'jumlah_diminta',
    ];

    /**
     * Get the package associated with this detail.
     */
    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * Get the transaction that owns this detail.
     */
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Get the item associated with this detail.
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
