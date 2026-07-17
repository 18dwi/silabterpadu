<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'kode_barang',
        'nama_barang',
        'merk_tipe',
        'satuan',
        'kategori',
        'stok_total',
        'stok_tersedia',
        'jumlah_baik',
        'jumlah_perbaikan',
        'jumlah_rusak',
        'jumlah_rusak_ringan',
        'jumlah_rusak_sedang',
        'jumlah_rusak_berat',
        'status',
        'lokasi_rak',
        'tahun_kedatangan',
        'tahun_datang',
        'tanggal_expired',
        'tindak_lanjut',
        'jurusan',
        'stok_awal',
        'bahan_masuk',
        'tanggal_masuk',
        'bahan_keluar',
        'tanggal_keluar',
    ];

    public function transactionDetails()
    {
        return $this->hasMany(TransactionDetail::class);
    }

    public function stockEntries()
    {
        return $this->hasMany(StockEntry::class);
    }

    /**
     * Get dynamic quantity of items currently checked out (disetujui).
     */
    public function getJumlahDipinjamAttribute()
    {
        return $this->transactionDetails()
            ->whereHas('transaction', function ($q) {
                $q->where('status', 'disetujui');
            })
            ->sum('jumlah_diminta');
    }

    /**
     * Check if the item is expired.
     */
    public function getIsExpiredAttribute()
    {
        if (!$this->tanggal_expired) {
            return false;
        }
        return \Carbon\Carbon::parse($this->tanggal_expired)->startOfDay()->isPast();
    }

    /**
     * Check if the item is expiring soon (within 30 days).
     */
    public function getIsExpiringSoonAttribute()
    {
        if (!$this->tanggal_expired || $this->is_expired) {
            return false;
        }
        return \Carbon\Carbon::parse($this->tanggal_expired)->diffInDays(now()) <= 30;
    }

    /**
     * Get dynamic stock calculation from stock card logic.
     */
    public function getDynamicStockAttribute()
    {
        if ($this->kategori !== 'bahan') {
            return $this->stok_tersedia;
        }

        $history = [];
        $stokAwal = (int)($this->stok_awal ?? 0);
        $history[] = [
            'tipe' => 'stok_awal',
            'qty' => $stokAwal,
            'raw_date' => $this->created_at ? $this->created_at->toDateTimeString() : '0000-00-00 00:00:00',
        ];

        $bahanMasuk = (int)($this->bahan_masuk ?? 0);
        if ($bahanMasuk > 0) {
            $history[] = [
                'tipe' => 'bahan_masuk',
                'qty' => $bahanMasuk,
                'raw_date' => $this->created_at ? $this->created_at->copy()->addSecond()->toDateTimeString() : '0000-00-00 00:00:01',
            ];
        }

        $entries = \DB::table('stock_entries')
            ->where('item_id', $this->id)
            ->select('jumlah_masuk', 'tanggal_masuk', 'created_at')
            ->get();

        foreach ($entries as $entry) {
            try {
                $rawDate = \Carbon\Carbon::createFromFormat('d-m-Y', $entry->tanggal_masuk)->startOfDay()->toDateTimeString();
            } catch (\Exception $e) {
                $rawDate = $entry->created_at;
            }
            $history[] = [
                'tipe' => 'bahan_masuk_tambahan',
                'qty' => (int)$entry->jumlah_masuk,
                'raw_date' => $rawDate,
            ];
        }

        $exits = \DB::table('transaction_details')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->where('transaction_details.item_id', $this->id)
            ->where('transactions.status', 'selesai')
            ->select('transaction_details.jumlah_diminta', 'transactions.updated_at')
            ->get();

        foreach ($exits as $exit) {
            $history[] = [
                'tipe' => 'transaksi_keluar',
                'qty' => (int)$exit->jumlah_diminta,
                'raw_date' => $exit->updated_at,
            ];
        }

        usort($history, function($a, $b) {
            return strcmp($a['raw_date'], $b['raw_date']);
        });

        $runningStock = 0;
        foreach ($history as $row) {
            if ($row['tipe'] === 'stok_awal') {
                $runningStock = $row['qty'];
            } elseif ($row['tipe'] === 'bahan_masuk' || $row['tipe'] === 'bahan_masuk_tambahan') {
                $runningStock += $row['qty'];
            } elseif ($row['tipe'] === 'transaksi_keluar') {
                $runningStock -= $row['qty'];
            }
        }

        return $runningStock;
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted()
    {
        static::saved(function ($item) {
            // Prevent recursive loop if the update was received from Google Sheets webhook
            if (!request()->is('api/sheets-webhook') && !request()->is('*/sheets-webhook')) {
                try {
                    $syncService = new \App\Services\GoogleSheetsSyncService();
                    $syncService->syncItemToSheet($item);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Google Sheets Sync failed on save: " . $e->getMessage());
                }
            }
        });
    }
}
