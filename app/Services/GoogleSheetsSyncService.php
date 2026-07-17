<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleSheetsSyncService
{
    /**
     * Push item updates dynamically to Google Sheets.
     *
     * @param \App\Models\Item $item
     * @return bool
     */
    public function syncItemToSheet($item)
    {
        $webAppUrl = env('GOOGLE_SHEET_WEBAPP_URL');
        if (empty($webAppUrl)) {
            Log::info("Google Sheets Sync: GOOGLE_SHEET_WEBAPP_URL is not set in .env file. Skipping sync for item '{$item->kode_barang}' (Simulated local storage update only).");
            return false;
        }

        try {
            // Send payload to Google Sheets Apps Script Web App URL
            $response = Http::timeout(4)->post($webAppUrl, [
                'action' => 'update_item',
                'kode_barang' => $item->kode_barang,
                'nama_barang' => $item->nama_barang,
                'kategori' => $item->kategori,
                'merk_tipe' => $item->merk_tipe,
                'stok_total' => (int)$item->stok_total,
                'stok_tersedia' => (int)$item->stok_tersedia,
                'jumlah_baik' => (int)$item->jumlah_baik,
                'jumlah_perbaikan' => (int)$item->jumlah_perbaikan,
                'jumlah_rusak' => (int)$item->jumlah_rusak,
                'lokasi_rak' => $item->lokasi_rak,
                'tahun_datang' => $item->tahun_datang,
                'tanggal_expired' => $item->tanggal_expired,
            ]);

            if ($response->successful()) {
                Log::info("Google Sheets Sync: Item '{$item->kode_barang}' successfully synchronized to Google Sheet.");
                return true;
            } else {
                Log::warning("Google Sheets Sync: Failed to sync item '{$item->kode_barang}'. Status code: " . $response->status() . " Response: " . $response->body());
                return false;
            }
        } catch (\Exception $e) {
            Log::error("Google Sheets Sync connection error: " . $e->getMessage());
            return false;
        }
    }
}
