<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Inventaris {{ ucfirst($kategori) }} - Si-Lab Keperawatan</title>
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
        .badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        .badge-teal {
            background-color: #ccfbf1;
            color: #0f766e;
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
        // Convert Poltekkes logo to Base64 to ensure DOMPDF loads it offline
        $logoPath = public_path('images/logo_poltekkes.png');
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoBase64 = base64_encode(file_get_contents($logoPath));
        }
    @endphp

    <!-- HEADER TABLE -->
    <table class="header-table">
        <tr>
            <td class="header-logo">
                @if($logoBase64)
                    <img src="data:image/png;base64,{{ $logoBase64 }}" style="height: 50px;" alt="Logo Poltekkes" />
                @endif
            </td>
            <td class="header-text">
                <div class="header-title">Si-Lab Keperawatan</div>
                <div class="header-subtitle">Poltekkes Kemenkes Jakarta I</div>
                <div style="font-size: 8px; color: #6b7280; margin-top: 1px;">Jl. Wijaya Kusuma No. 47 Cilandak, Jakarta Selatan</div>
            </td>
        </tr>
    </table>

    <!-- DOCUMENT TITLE -->
    <div class="doc-title">Laporan Rekapitulasi Realtime Inventaris {{ ucfirst($kategori) }}</div>
    <div class="doc-meta">Tanggal Cetak: {{ now()->translatedFormat('d F Y, H:i') }} WIB | Dicetak Oleh: {{ Auth::user()->name }}</div>

    <!-- INVENTORY TABLE -->
    <table class="data-table">
        <thead>
            @if($kategori === 'alat')
                <tr>
                    <th style="width: 12%;">Kode Alat</th>
                    <th style="width: 25%;">Nama Alat</th>
                    <th style="width: 18%;">Merk / Type</th>
                    <th style="width: 8%;">Total</th>
                    <th style="width: 8%;">Baik</th>
                    <th style="width: 8%;">Perbaikan</th>
                    <th style="width: 8%;">Rusak</th>
                    <th style="width: 13%;">Ketersediaan</th>
                </tr>
            @else
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
            @endif
        </thead>
        <tbody>
            @forelse($items as $item)
                @if($kategori === 'alat')
                    <tr>
                        <td class="text-center font-bold" style="color: #4b5563;">{{ $item->kode_barang }}</td>
                        <td class="text-left" style="font-weight: bold; color: #111827;">{{ $item->nama_barang }}</td>
                        <td class="text-left">{{ $item->merk_tipe ?: '-' }}</td>
                        <td class="text-center">{{ $item->stok_total }}</td>
                        <td class="text-center" style="color: #059669; font-weight: bold;">{{ $item->jumlah_baik }}</td>
                        <td class="text-center" style="color: #d97706;">{{ $item->jumlah_perbaikan }}</td>
                        <td class="text-center" style="color: #dc2626;">{{ $item->jumlah_rusak }}</td>
                        <td class="text-center font-bold" style="background-color: #f0fdfa;">
                            <span class="badge badge-teal">{{ $item->stok_tersedia }} {{ $item->satuan }}</span>
                        </td>
                    </tr>
                @else
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
                @endif
            @empty
                <tr>
                    <td colspan="{{ $kategori === 'alat' ? 8 : 11 }}" class="text-center" style="color: #9ca3af; padding: 20px;">Tidak ada data inventaris.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer-text">
        Dokumen Laporan Realtime ini dihasilkan secara otomatis oleh sistem Si-Lab Keperawatan Poltekkes Kemenkes Jakarta I.
    </div>

</body>
</html>
