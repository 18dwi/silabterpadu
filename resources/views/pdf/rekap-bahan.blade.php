<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekapitulasi Permintaan Bahan - Si-Lab</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px;
            color: #333333;
            line-height: 1.5;
            margin: 0;
            padding: 10px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #0d9488;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header-logo {
            width: 70px;
            text-align: left;
            vertical-align: middle;
        }
        .header-text {
            text-align: left;
            vertical-align: middle;
            padding-left: 10px;
        }
        .header-title {
            font-size: 15px;
            font-weight: bold;
            color: #0f766e;
            margin: 0;
        }
        .header-subtitle {
            font-size: 10px;
            color: #4b5563;
            margin: 2px 0 0 0;
            text-transform: uppercase;
        }
        .doc-title {
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            color: #1f2937;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .doc-meta {
            text-align: center;
            font-size: 8px;
            color: #6b7280;
            margin-bottom: 15px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .data-table th {
            background-color: #f3f4f6;
            color: #374151;
            font-weight: bold;
            border: 1px solid #d1d5db;
            padding: 6px 8px;
            text-align: center;
            font-size: 9px;
            text-transform: uppercase;
        }
        .data-table td {
            border: 1px solid #e5e7eb;
            padding: 6px 8px;
            vertical-align: middle;
        }
        .data-table tr:nth-child(even) td {
            background-color: #f9fafb;
        }
        .text-left {
            text-align: left;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .footer-text {
            margin-top: 30px;
            font-size: 8px;
            color: #9ca3af;
            text-align: right;
        }
    </style>
</head>
<body>

    @php
        $logoPath = public_path('images/logo_poltekkes.png');
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoBase64 = base64_encode(file_get_contents($logoPath));
        }
        
        $deptLabel = $jurusan === 'semua' ? 'Semua Jurusan' : ucwords(str_replace('_', ' ', $jurusan));
    @endphp

    <table class="header-table">
        <tr>
            <td class="header-logo">
                @if($logoBase64)
                    <img src="data:image/png;base64,{{ $logoBase64 }}" style="height: 50px;" alt="Logo Poltekkes" />
                @endif
            </td>
            <td class="header-text">
                <div class="header-title">Si-Lab Terpadu - {{ $deptLabel }}</div>
                <div class="header-subtitle">Poltekkes Kemenkes Jakarta I</div>
                <div style="font-size: 8px; color: #6b7280; margin-top: 1px;">Jl. Wijaya Kusuma No. 47 Cilandak, Jakarta Selatan</div>
            </td>
        </tr>
    </table>

    <div class="doc-title">Laporan Rekapitulasi Penggunaan Bahan Habis Pakai</div>
    <div class="doc-meta">
        Periode: {{ $startDate ? Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') : 'Awal' }} s/d {{ $endDate ? Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') : 'Akhir' }} 
        | Cetak: {{ now()->translatedFormat('d F Y, H:i') }} WIB 
        | Oleh: {{ Auth::user()->name }}
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 10%;">Kode Bahan</th>
                <th style="width: 18%;">Nama Bahan</th>
                <th style="width: 12%;">Merk / Type</th>
                <th style="width: 6%;">Satuan</th>
                <th style="width: 8%;">Lokasi Rak</th>
                <th style="width: 7%;">Stok Awal</th>
                <th style="width: 6%;">Masuk</th>
                <th style="width: 9%;">Tgl Masuk</th>
                <th style="width: 6%;">Keluar</th>
                <th style="width: 9%;">Tgl Keluar</th>
                <th style="width: 9%;">Stok Terakhir</th>
            </tr>
        </thead>
        <tbody>
            @forelse($materialRecap as $item)
                @php
                    $incomingMap = [];
                    if (($item->bahan_masuk ?? 0) > 0) {
                        $tglM = $item->tanggal_masuk ? \Carbon\Carbon::parse($item->tanggal_masuk)->translatedFormat('F Y') : ($item->created_at ? $item->created_at->translatedFormat('F Y') : now()->translatedFormat('F Y'));
                        $incomingMap[$tglM] = $item->bahan_masuk;
                    }
                    
                    $outgoingTrans = \DB::table('transaction_details')
                        ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
                        ->where('transaction_details.item_id', $item->id)
                        ->where('transactions.tipe', 'permintaan_bahan')
                        ->whereIn('transactions.status', ['selesai', 'disetujui'])
                        ->select('transaction_details.jumlah_diminta', 'transactions.tanggal_pengajuan')
                        ->get();
                        
                    $outgoingMap = [];
                    foreach ($outgoingTrans as $ot) {
                        $date = \Carbon\Carbon::parse($ot->tanggal_pengajuan);
                        $monthYear = $date->translatedFormat('F Y');
                        if (!isset($outgoingMap[$monthYear])) {
                            $outgoingMap[$monthYear] = 0;
                        }
                        $outgoingMap[$monthYear] += $ot->jumlah_diminta;
                    }
                    if (empty($outgoingMap) && ($item->bahan_keluar ?? 0) > 0) {
                        $tglK = $item->tanggal_keluar ? \Carbon\Carbon::parse($item->tanggal_keluar)->translatedFormat('F Y') : now()->translatedFormat('F Y');
                        $outgoingMap[$tglK] = $item->bahan_keluar;
                    }

                    $incomingStrings = [];
                    foreach ($incomingMap as $my => $qty) {
                        $incomingStrings[] = "{$qty} ({$my})";
                    }
                    $masukVal = implode('<br>', $incomingStrings) ?: '0';
                    $tglMasukVal = implode('<br>', array_keys($incomingMap)) ?: '-';

                    $outgoingStrings = [];
                    foreach ($outgoingMap as $my => $qty) {
                        $outgoingStrings[] = "{$qty} ({$my})";
                    }
                    $keluarVal = implode('<br>', $outgoingStrings) ?: '0';
                    $tglKeluarVal = implode('<br>', array_keys($outgoingMap)) ?: '-';
                @endphp
                <tr>
                    <td class="text-center font-bold" style="color: #4b5563;">{{ $item->kode_barang }}</td>
                    <td class="text-left" style="font-weight: bold; color: #111827;">{{ $item->nama_barang }}</td>
                    <td class="text-left">{{ $item->merk_tipe ?: '-' }}</td>
                    <td class="text-center font-semibold">{{ $item->satuan }}</td>
                    <td class="text-center">{{ $item->lokasi_rak ?: '-' }}</td>
                    <td class="text-center">{{ $item->stok_awal ?? 0 }}</td>
                    <td class="text-center" style="color: #059669; font-size: 8px;">{!! $masukVal !!}</td>
                    <td class="text-center" style="color: #059669; font-size: 8px;">{!! $tglMasukVal !!}</td>
                    <td class="text-center" style="color: #dc2626; font-size: 8px;">{!! $keluarVal !!}</td>
                    <td class="text-center" style="color: #dc2626; font-size: 8px;">{!! $tglKeluarVal !!}</td>
                    <td class="text-center font-bold" style="background-color: #f0fdfa; color: #0f766e;">{{ $item->dynamic_stock }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center" style="color: #9ca3af; padding: 20px;">Tidak ada data inventaris bahan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer-text">
        Dokumen Laporan Rekapitulasi Bahan ini dihasilkan secara otomatis oleh sistem Si-Lab Terpadu.
    </div>

</body>
</html>
