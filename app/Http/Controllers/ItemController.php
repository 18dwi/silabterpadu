<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    /**
    public function store(Request $request)
    {
        $request->validate([
            'kode_barang' => 'required|string|max:255|unique:items,kode_barang',
            'nama_barang' => 'required|string|max:255',
            'merk_tipe' => 'nullable|string|max:255',
            'kategori' => 'required|in:alat,bahan',
            'stok_total' => 'required_if:kategori,bahan|nullable|integer|min:0',
            'stok_tersedia' => 'nullable|integer|min:0',
            'jumlah_baik' => 'required_if:kategori,alat|nullable|integer|min:0',
            'jumlah_rusak_ringan' => 'nullable|integer|min:0',
            'jumlah_rusak_sedang' => 'nullable|integer|min:0',
            'jumlah_rusak_berat' => 'nullable|integer|min:0',
            'lokasi_rak' => 'required|string|max:255',
            'satuan' => 'required|string|max:255',
            'tahun_kedatangan' => 'nullable|string|max:255',
            'tahun_datang' => 'nullable|integer',
            'tanggal_expired' => 'nullable|date',
            'tindak_lanjut' => 'nullable|in:kalibrasi,perawatan,penghapusan,perbaikan',
            'stok_awal' => 'nullable|integer|min:0',
        ]);

        if ($request->kategori === 'alat') {
            $baik = (int)$request->jumlah_baik;
            $rusak_ringan = (int)$request->jumlah_rusak_ringan;
            $rusak_sedang = (int)$request->jumlah_rusak_sedang;
            $rusak_berat = (int)$request->jumlah_rusak_berat;
            
            $perbaikan = $rusak_ringan + $rusak_sedang;
            $rusak = $rusak_berat;
            
            $stokTotal = $baik + $rusak_ringan + $rusak_sedang + $rusak_berat;
            $stokTersedia = $request->stok_tersedia ?? $baik;
        } else {
            $stokTotal = (int)$request->stok_total;
            $stokTersedia = $request->stok_tersedia ?? $stokTotal;
            $baik = 0;
            $rusak_ringan = 0;
            $rusak_sedang = 0;
            $rusak_berat = 0;
            $perbaikan = 0;
            $rusak = 0;
        }

        Item::create([
            'kode_barang' => $request->kode_barang,
            'nama_barang' => $request->nama_barang,
            'merk_tipe' => $request->merk_tipe,
            'kategori' => $request->kategori,
            'stok_total' => $stokTotal,
            'stok_tersedia' => $stokTersedia,
            'jumlah_baik' => $baik,
            'jumlah_perbaikan' => $perbaikan,
            'jumlah_rusak' => $rusak,
            'jumlah_rusak_ringan' => $rusak_ringan,
            'jumlah_rusak_sedang' => $rusak_sedang,
            'jumlah_rusak_berat' => $rusak_berat,
            'status' => $stokTersedia <= 0 ? 'dipinjam' : ($rusak >= $stokTotal && $stokTotal > 0 ? 'rusak' : 'tersedia'),
            'lokasi_rak' => $request->lokasi_rak,
            'satuan' => $request->satuan,
            'tahun_kedatangan' => $request->tahun_kedatangan,
            'tahun_datang' => $request->tahun_datang,
            'tanggal_expired' => $request->tanggal_expired,
            'tindak_lanjut' => $request->tindak_lanjut,
            'jurusan' => auth()->user()->jurusan,
            'stok_awal' => $request->kategori === 'bahan' ? ((int)$request->input('stok_awal', 0)) : null,
        ]);

        return redirect()->back()->with('success', 'Barang berhasil ditambahkan ke inventaris.');
    }

    /**
     * Update the specified item.
     */
    public function update(Request $request, $id)
    {
        $item = Item::findOrFail($id);

        $request->validate([
            'kode_barang' => 'required|string|max:255|unique:items,kode_barang,' . $item->id,
            'nama_barang' => 'required|string|max:255',
            'merk_tipe' => 'nullable|string|max:255',
            'kategori' => 'required|in:alat,bahan',
            'stok_total' => 'nullable|integer|min:0',
            'stok_tersedia' => 'nullable|integer|min:0',
            'jumlah_baik' => 'required_if:kategori,alat|nullable|integer|min:0',
            'jumlah_rusak_ringan' => 'nullable|integer|min:0',
            'jumlah_rusak_sedang' => 'nullable|integer|min:0',
            'jumlah_rusak_berat' => 'nullable|integer|min:0',
            'lokasi_rak' => 'required|string|max:255',
            'satuan' => 'required|string|max:255',
            'tahun_kedatangan' => 'nullable|string|max:255',
            'tahun_datang' => 'nullable|integer',
            'tanggal_expired' => 'nullable|date',
            'tindak_lanjut' => 'nullable|in:kalibrasi,perawatan,penghapusan,perbaikan',
            'stok_awal' => 'nullable|integer|min:0',
            'bahan_masuk' => 'nullable|integer|min:0',
            'bahan_keluar' => 'nullable|integer|min:0',
            'tanggal_masuk' => 'nullable|date',
            'tanggal_keluar' => 'nullable|date',
        ]);

        if ($request->kategori === 'alat') {
            $baik = (int)$request->jumlah_baik;
            $rusak_ringan = (int)$request->jumlah_rusak_ringan;
            $rusak_sedang = (int)$request->jumlah_rusak_sedang;
            $rusak_berat = (int)$request->jumlah_rusak_berat;
            
            $perbaikan = $rusak_ringan + $rusak_sedang;
            $rusak = $rusak_berat;
            
            $stokTotal = $baik + $rusak_ringan + $rusak_sedang + $rusak_berat;
            $stokTersedia = $request->stok_tersedia;
        } else {
            $stokAwal = (int)$request->input('stok_awal', 0);
            $bahanMasuk = (int)$request->input('bahan_masuk', 0);
            $bahanKeluar = (int)$request->input('bahan_keluar', 0);
            $stokTotal = $stokAwal + $bahanMasuk - $bahanKeluar;
            $stokTersedia = $stokTotal;
            $baik = 0;
            $rusak_ringan = 0;
            $rusak_sedang = 0;
            $rusak_berat = 0;
            $perbaikan = 0;
            $rusak = 0;
        }

        $updateData = [
            'kode_barang' => $request->kode_barang,
            'nama_barang' => $request->nama_barang,
            'merk_tipe' => $request->merk_tipe,
            'kategori' => $request->kategori,
            'stok_total' => $stokTotal,
            'stok_tersedia' => $stokTersedia,
            'jumlah_baik' => $baik,
            'jumlah_perbaikan' => $perbaikan,
            'jumlah_rusak' => $rusak,
            'jumlah_rusak_ringan' => $rusak_ringan,
            'jumlah_rusak_sedang' => $rusak_sedang,
            'jumlah_rusak_berat' => $rusak_berat,
            'status' => $stokTersedia <= 0 ? 'dipinjam' : ($rusak >= $stokTotal && $stokTotal > 0 ? 'rusak' : 'tersedia'),
            'lokasi_rak' => $request->lokasi_rak,
            'satuan' => $request->satuan,
            'tahun_kedatangan' => $request->tahun_kedatangan,
            'tahun_datang' => $request->tahun_datang,
            'tanggal_expired' => $request->tanggal_expired,
            'tindak_lanjut' => $request->tindak_lanjut,
        ];

        if ($request->kategori === 'bahan') {
            $updateData['stok_awal'] = $stokAwal;
            $updateData['bahan_masuk'] = $bahanMasuk;
            $updateData['bahan_keluar'] = $bahanKeluar;
            if ($request->filled('tanggal_masuk')) {
                $updateData['tanggal_masuk'] = $request->tanggal_masuk;
            }
            if ($request->filled('tanggal_keluar')) {
                $updateData['tanggal_keluar'] = $request->tanggal_keluar;
            }
        }

        $item->update($updateData);

        return redirect()->back()->with('success', 'Barang berhasil diperbarui.');
    }

    /**
     * Remove the specified item.
     */
    public function destroy($id)
    {
        $item = Item::findOrFail($id);
        $item->delete();

        return redirect()->back()->with('success', 'Barang berhasil dihapus.');
    }

    /**
     * Import items from a public Google Sheets link exported as CSV.
     */
    public function importGoogleSheet(Request $request)
    {
        $request->validate([
            'sheet_url' => 'required|url',
            'kategori' => 'required|in:alat,bahan',
        ]);

        $url = $request->input('sheet_url');
        $importKategori = $request->input('kategori');

        // Extract spreadsheet ID
        if (preg_match('/\/d\/([a-zA-Z0-9-_]+)/', $url, $matches)) {
            $spreadsheetId = $matches[1];
            $csvUrl = "https://docs.google.com/spreadsheets/d/{$spreadsheetId}/export?format=csv";
        } else {
            return redirect()->back()->with('error', 'URL Google Sheet tidak valid. Pastikan format URL benar.');
        }

        try {
            // Setup a context with user agent to prevent download blocks
            $options = [
                'http' => [
                    'header' => "User-Agent: PHP\r\n"
                ]
            ];
            $context = stream_context_create($options);
            $csvData = file_get_contents($csvUrl, false, $context);
            if ($csvData === false) {
                throw new \Exception("Gagal mengambil data dari Google Sheet. Pastikan Sheet diatur Publik (Siapa saja yang memiliki link dapat melihat).");
            }

            $rows = array_map('str_getcsv', explode("\n", $csvData));
            $header = array_shift($rows);

            $count = 0;
            \Illuminate\Support\Facades\DB::transaction(function () use ($rows, $importKategori, &$count) {
                // Inline helper functions for resilient parsing
                $parseNumber = function($val) {
                    $clean = trim($val);
                    if ($clean === '' || $clean === '-') return 0;
                    if (is_numeric($clean)) return (int)$clean;
                    if (preg_match('/^\d+/', $clean, $matches)) {
                        return (int)$matches[0];
                    }
                    return 0;
                };

                $parseDate = function($val) {
                    $clean = trim($val);
                    if ($clean === '' || $clean === '-') return null;
                    try {
                        return \Carbon\Carbon::parse($clean)->format('Y-m-d');
                    } catch (\Exception $e) {
                        return null;
                    }
                };

                $importedCodes = [];
                foreach ($rows as $row) {
                    if (count($row) < 3 || empty($row[0])) continue;

                    $kode = trim($row[0]);
                    
                    if ($importKategori === 'alat') {
                        $nama = !empty(trim($row[1])) ? trim($row[1]) : '-';
                        
                        $merk = (isset($row[3]) && trim($row[3]) !== '') ? trim($row[3]) : '-';
                        $total = isset($row[4]) ? $parseNumber($row[4]) : 0;
                        
                        $tersedia = isset($row[5]) && $row[5] !== '' ? $parseNumber($row[5]) : $total;
                        $baik = isset($row[6]) && $row[6] !== '' ? $parseNumber($row[6]) : $total;
                        
                        $rusak_ringan = isset($row[7]) && $row[7] !== '' ? $parseNumber($row[7]) : 0;
                        $rusak_sedang = isset($row[8]) && $row[8] !== '' ? $parseNumber($row[8]) : 0;
                        $rusak_berat = isset($row[9]) && $row[9] !== '' ? $parseNumber($row[9]) : 0;
                        
                        $perbaikan = $rusak_ringan + $rusak_sedang;
                        $rusak = $rusak_berat;

                        $lokasi = (isset($row[10]) && trim($row[10]) !== '') ? trim($row[10]) : '-';
                        $satuan = (isset($row[11]) && trim($row[11]) !== '') ? trim($row[11]) : '-';
                        $tahun = isset($row[12]) && trim($row[12]) !== '' ? trim($row[12]) : '-';
                        $tindak = isset($row[13]) && trim($row[13]) !== '' ? strtolower(trim($row[13])) : '-';
                        if ($tindak !== '-' && !in_array($tindak, ['kalibrasi', 'perawatan', 'penghapusan', 'perbaikan'])) {
                            $tindak = '-';
                        }

                        Item::updateOrCreate(
                            ['kode_barang' => $kode],
                            [
                                'nama_barang' => $nama,
                                'kategori' => 'alat',
                                'merk_tipe' => $merk,
                                'stok_total' => $total,
                                'stok_tersedia' => $tersedia,
                                'jumlah_baik' => $baik,
                                'jumlah_perbaikan' => $perbaikan,
                                'jumlah_rusak' => $rusak,
                                'jumlah_rusak_ringan' => $rusak_ringan,
                                'jumlah_rusak_sedang' => $rusak_sedang,
                                'jumlah_rusak_berat' => $rusak_berat,
                                'status' => $tersedia <= 0 ? 'dipinjam' : ($rusak >= $total && $total > 0 ? 'rusak' : 'tersedia'),
                                'lokasi_rak' => $lokasi,
                                'satuan' => $satuan,
                                'tahun_kedatangan' => $tahun,
                                'tindak_lanjut' => $tindak,
                                'jurusan' => auth()->user()->jurusan,
                            ]
                        );
                    } else { // bahan
                        $nama = !empty(trim($row[1])) ? trim($row[1]) : '-';
                        $merk = (isset($row[2]) && trim($row[2]) !== '') ? trim($row[2]) : '-';
                        $satuan = (isset($row[3]) && trim($row[3]) !== '') ? trim($row[3]) : '-';
                        $lokasi = (isset($row[4]) && trim($row[4]) !== '') ? trim($row[4]) : '-';
                        
                        $stok_awal = isset($row[5]) && $row[5] !== '' ? $parseNumber($row[5]) : 0;
                        $bahan_masuk = isset($row[6]) && $row[6] !== '' ? $parseNumber($row[6]) : 0;
                        $tanggal_masuk_raw = isset($row[7]) ? trim($row[7]) : '';
                        $tanggal_masuk = $parseDate($tanggal_masuk_raw) ?: ($tanggal_masuk_raw !== '' ? $tanggal_masuk_raw : null);
                        
                        $bahan_keluar = isset($row[8]) && $row[8] !== '' ? $parseNumber($row[8]) : 0;
                        $tanggal_keluar_raw = isset($row[9]) ? trim($row[9]) : '';
                        $tanggal_keluar = $parseDate($tanggal_keluar_raw) ?: ($tanggal_keluar_raw !== '' ? $tanggal_keluar_raw : null);

                        // Extract year from Tanggal Bahan Masuk
                        $tahun_datang = null;
                        if (!empty($tanggal_masuk)) {
                            if (preg_match('/(\d{4})/', $tanggal_masuk, $yrMatches)) {
                                $tahun_datang = (int)$yrMatches[1];
                            }
                        }
                        
                        $total = isset($row[10]) ? $parseNumber($row[10]) : 0;
                        $tersedia = $total;
                        $tanggal_expired_raw = isset($row[11]) ? trim($row[11]) : '';
                        $tanggal_expired = $parseDate($tanggal_expired_raw) ?: ($tanggal_expired_raw !== '' ? $tanggal_expired_raw : null);

                        Item::updateOrCreate(
                            ['kode_barang' => $kode],
                            [
                                'nama_barang' => $nama,
                                'kategori' => 'bahan',
                                'merk_tipe' => $merk,
                                'stok_total' => $total,
                                'stok_tersedia' => $tersedia,
                                'jumlah_baik' => 0,
                                'jumlah_perbaikan' => 0,
                                'jumlah_rusak' => 0,
                                'status' => $tersedia <= 0 ? 'dipinjam' : 'tersedia',
                                'lokasi_rak' => $lokasi,
                                'satuan' => $satuan,
                                'tahun_datang' => $tahun_datang,
                                'tanggal_expired' => $tanggal_expired,
                                'jurusan' => auth()->user()->jurusan,
                                'stok_awal' => $stok_awal,
                                'bahan_masuk' => $bahan_masuk,
                                'tanggal_masuk' => $tanggal_masuk,
                                'bahan_keluar' => $bahan_keluar,
                                'tanggal_keluar' => $tanggal_keluar,
                            ]
                        );
                    }
                    $importedCodes[] = $kode;
                    $count++;
                }

                if (count($importedCodes) > 0) {
                    Item::where('jurusan', auth()->user()->jurusan)
                        ->where('kategori', $importKategori)
                        ->whereNotIn('kode_barang', $importedCodes)
                        ->delete();
                }
            });

            $labelKategori = $importKategori === 'alat' ? 'alat' : 'bahan';
            return redirect()->back()->with('success', "Berhasil mengimpor {$count} {$labelKategori} dari Google Sheet.");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengimpor Google Sheet: ' . $e->getMessage());
        }
    }

    /**
     * Update the status of the item (e.g. mark as maintenance/rusak).
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:tersedia,dipinjam,rusak',
        ]);

        $item = Item::findOrFail($id);
        $item->update([
            'status' => $request->status,
        ]);

        return redirect()->back()->with('success', 'Status barang berhasil diubah.');
    }

    /**
     * Handle incoming webhook updates from Google Sheets in real-time.
     */
    public function handleSheetsWebhook(Request $request)
    {
        $validated = $request->validate([
            'kode_barang' => 'required|string',
            'nama_barang' => 'sometimes|string',
            'kategori' => 'sometimes|string',
            'merk_tipe' => 'sometimes|string',
            'satuan' => 'sometimes|string',
            'stok_total' => 'sometimes|integer',
            'stok_tersedia' => 'sometimes|integer',
            'jumlah_baik' => 'sometimes|integer',
            'jumlah_perbaikan' => 'sometimes|integer',
            'jumlah_rusak' => 'sometimes|integer',
            'jumlah_rusak_ringan' => 'sometimes|integer',
            'jumlah_rusak_sedang' => 'sometimes|integer',
            'jumlah_rusak_berat' => 'sometimes|integer',
            'lokasi_rak' => 'sometimes|string',
            'tahun_kedatangan' => 'sometimes|string',
            'tahun_datang' => 'sometimes|integer',
            'tanggal_expired' => 'sometimes|date',
            'tindak_lanjut' => 'sometimes|string',
        ]);

        $item = Item::where('kode_barang', $validated['kode_barang'])->first();

        if (!$item) {
            $item = new Item();
            $item->kode_barang = $validated['kode_barang'];
        }

        // Fill provided fields
        foreach ($validated as $key => $value) {
            if ($key !== 'kode_barang') {
                $item->$key = $value;
            }
        }

        if (!isset($validated['stok_tersedia']) && isset($validated['stok_total'])) {
            $item->stok_tersedia = $validated['stok_total'];
        }

        $item->save();

        return response()->json([
            'message' => 'Database synchronized from Google Sheets successfully.',
            'item' => $item
        ]);
    }

    public function stockCard($id)
    {
        $item = Item::findOrFail($id);
        
        $history = [];
        
        // 1. Initial Stock (Stok Awal)
        $stokAwal = (int)($item->stok_awal ?? 0);
        $createdAt = $item->created_at ? $item->created_at->format('d-m-Y') : '-';
        
        $history[] = [
            'tipe' => 'stok_awal',
            'masuk_qty' => $stokAwal,
            'masuk_date' => $createdAt,
            'keluar_qty' => '-',
            'keluar_date' => '-',
            'raw_date' => $item->created_at ? $item->created_at->toDateTimeString() : '0000-00-00 00:00:00',
        ];
        
        // 2. Incoming Stock additions (Bahan Masuk)
        $bahanMasuk = (int)($item->bahan_masuk ?? 0);
        $tanggalMasuk = $item->tanggal_masuk ? \Carbon\Carbon::parse($item->tanggal_masuk)->format('d-m-Y') : '-';
        if ($bahanMasuk > 0) {
            $history[] = [
                'tipe' => 'bahan_masuk',
                'masuk_qty' => $bahanMasuk,
                'masuk_date' => $tanggalMasuk,
                'keluar_qty' => '-',
                'keluar_date' => '-',
                'raw_date' => $item->created_at ? $item->created_at->copy()->addSecond()->toDateTimeString() : '0000-00-00 00:00:01',
            ];
        }

        // 2b. Additional Incoming Stock (Stock Entries Table)
        $entries = \DB::table('stock_entries')
            ->where('item_id', $item->id)
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
                'masuk_qty' => (int)$entry->jumlah_masuk,
                'masuk_date' => $entry->tanggal_masuk,
                'keluar_qty' => '-',
                'keluar_date' => '-',
                'raw_date' => $rawDate,
            ];
        }
        
        // 3. Transactions (Exits)
        $exits = \DB::table('transaction_details')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->where('transaction_details.item_id', $item->id)
            ->where('transactions.status', 'selesai')
            ->select('transaction_details.jumlah_diminta', 'transactions.updated_at')
            ->orderBy('transactions.updated_at', 'asc')
            ->get();
            
        foreach ($exits as $exit) {
            $exitDate = $exit->updated_at ? \Carbon\Carbon::parse($exit->updated_at)->format('d-m-Y H:i') : '-';
            $history[] = [
                'tipe' => 'transaksi_keluar',
                'masuk_qty' => '-',
                'masuk_date' => '-',
                'keluar_qty' => (int)$exit->jumlah_diminta,
                'keluar_date' => $exitDate,
                'raw_date' => $exit->updated_at,
            ];
        }
        
        // Sort history chronologically
        usort($history, function($a, $b) {
            return strcmp($a['raw_date'], $b['raw_date']);
        });
        
        // Calculate running stock
        $runningStock = 0;
        foreach ($history as &$row) {
            if ($row['tipe'] === 'stok_awal') {
                $runningStock = $row['masuk_qty'];
            } elseif ($row['tipe'] === 'bahan_masuk' || $row['tipe'] === 'bahan_masuk_tambahan') {
                $runningStock += $row['masuk_qty'];
            } elseif ($row['tipe'] === 'transaksi_keluar') {
                $runningStock -= $row['keluar_qty'];
            }
            $row['stok_terakhir'] = $runningStock;
        }
        
        return response()->json([
            'item_id' => $item->id,
            'item_name' => $item->nama_barang,
            'kode_barang' => $item->kode_barang,
            'stok_awal' => $item->stok_awal,
            'bahan_masuk' => $item->bahan_masuk,
            'tanggal_masuk' => $item->tanggal_masuk,
            'bahan_keluar' => $item->bahan_keluar,
            'tanggal_keluar' => $item->tanggal_keluar,
            'history' => $history
        ]);
    }

    public function updateStockCard(Request $request, $id)
    {
        $item = Item::findOrFail($id);
        
        $action = $request->input('action', 'edit_stok');
        
        if ($action === 'tambah_stok') {
            $request->validate([
                'tambah_jumlah' => 'required|integer|min:1',
                'tambah_tanggal' => 'required|string|max:255',
            ]);
            
            \App\Models\StockEntry::create([
                'item_id' => $item->id,
                'jumlah_masuk' => $request->input('tambah_jumlah'),
                'tanggal_masuk' => $request->input('tambah_tanggal'),
            ]);
            
            $newStok = $item->dynamic_stock;
            $item->update([
                'stok_total' => $newStok,
                'stok_tersedia' => $newStok,
                'status' => $newStok <= 0 ? 'dipinjam' : 'tersedia',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tambahan stok barang masuk berhasil dicatat.',
                'stok_total' => $newStok
            ]);
        }
        
        $request->validate([
            'stok_awal' => 'nullable|integer|min:0',
            'bahan_masuk' => 'nullable|integer|min:0',
            'tanggal_masuk' => 'nullable|string|max:255',
            'bahan_keluar' => 'nullable|integer|min:0',
            'tanggal_keluar' => 'nullable|string|max:255',
        ]);
        
        $stokAwal = $request->has('stok_awal') ? (int)$request->input('stok_awal') : (int)($item->stok_awal ?? 0);
        $bahanMasuk = $request->has('bahan_masuk') ? (int)$request->input('bahan_masuk') : (int)($item->bahan_masuk ?? 0);
        $bahanKeluar = $request->has('bahan_keluar') ? (int)$request->input('bahan_keluar') : (int)($item->bahan_keluar ?? 0);
        
        $item->update([
            'stok_awal' => $stokAwal,
            'bahan_masuk' => $bahanMasuk,
            'tanggal_masuk' => $request->tanggal_masuk,
            'bahan_keluar' => $bahanKeluar,
            'tanggal_keluar' => $request->tanggal_keluar,
        ]);
        
        $newStok = $item->dynamic_stock;
        $item->update([
            'stok_total' => $newStok,
            'stok_tersedia' => $newStok,
            'status' => $newStok <= 0 ? 'dipinjam' : 'tersedia',
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Kartu stok berhasil diperbarui.',
            'stok_total' => $newStok
        ]);
    }
}
