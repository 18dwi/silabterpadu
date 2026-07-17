<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Formulir Peminjaman/Permintaan - Si-Lab {{ ucwords(str_replace('_', ' ', $transaction->jurusan)) }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #333333;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #0d9488;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header-logo {
            width: 80px;
            text-align: left;
            vertical-align: middle;
        }
        .header-text {
            text-align: left;
            vertical-align: middle;
            padding-left: 15px;
        }
        .header-title {
            font-size: 16px;
            font-weight: bold;
            color: #0f766e;
            margin: 0;
        }
        .header-subtitle {
            font-size: 11px;
            color: #4b5563;
            margin: 2px 0 0 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .doc-title {
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            color: #1f2937;
            text-transform: uppercase;
            margin-bottom: 20px;
            letter-spacing: 1px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .info-table td {
            padding: 5px 0;
            vertical-align: top;
        }
        .info-label {
            width: 25%;
            font-weight: bold;
            color: #4b5563;
        }
        .info-value {
            width: 75%;
            color: #111827;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items-table th {
            background-color: #f3f4f6;
            border: 1px solid #e5e7eb;
            padding: 8px;
            font-weight: bold;
            text-align: center;
            color: #374151;
        }
        .items-table td {
            border: 1px solid #e5e7eb;
            padding: 8px;
            text-align: center;
        }
        .items-table td.text-left {
            text-align: left;
        }
        .sign-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 40px;
        }
        .sign-cell {
            width: 50%;
            text-align: center;
            vertical-align: top;
        }
        .sign-title {
            font-weight: bold;
            color: #4b5563;
            margin-bottom: 10px;
        }
        .qr-container {
            margin: 10px 0;
            text-align: center;
        }
        .qr-placeholder {
            border: 2px dashed #0d9488;
            padding: 8px;
            width: 100px;
            margin: 0 auto;
            border-radius: 4px;
            background-color: #f0fdfa;
        }
        .qr-placeholder-title {
            font-size: 8px;
            font-weight: bold;
            color: #0f766e;
            text-transform: uppercase;
        }
        .qr-placeholder-text {
            font-size: 6px;
            color: #6b7280;
            margin-top: 4px;
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

        // Convert QR code to Base64 to ensure DOMPDF loads it offline
        $kodeTx = 'TX-' . str_pad($transaction->id, 5, '0', STR_PAD_LEFT);
        $namaPeminjam = $transaction->is_insidentil ? $transaction->peminjam_insidentil : ($transaction->user ? $transaction->user->name : 'Non-Mahasiswa');
        $namaVerifikator = $transaction->laboran ? $transaction->laboran->name : 'Laboran Si-Lab';
        $qrPayload = $kodeTx . '_' . $namaPeminjam . '_' . $namaVerifikator;
        
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=' . urlencode($qrPayload);
        $qrBase64 = '';
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $qrUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            $qrImg = curl_exec($ch);
            curl_close($ch);
            if ($qrImg) {
                $qrBase64 = base64_encode($qrImg);
            }
        } catch (\Exception $e) {}
    @endphp

    <!-- HEADER TABLE -->
    <table class="header-table">
        <tr>
            <td class="header-logo">
                @if($logoBase64)
                    <img src="data:image/png;base64,{{ $logoBase64 }}" style="height: 60px;" alt="Logo Poltekkes" />
                @endif
            </td>
            <td class="header-text">
                <div class="header-title">Si-Lab {{ ucwords(str_replace('_', ' ', $transaction->jurusan)) }}</div>
                <div class="header-subtitle">Poltekkes Kemenkes Jakarta I</div>
                <div style="font-size: 9px; color: #6b7280; margin-top: 2px;">Jl. Wijaya Kusuma No. 47 Cilandak, Jakarta Selatan</div>
            </td>
        </tr>
    </table>

    <!-- DOCUMENT TITLE -->
    <div class="doc-title">
        Formulir Persetujuan {{ $transaction->tipe === 'peminjaman_alat' ? 'Peminjaman Alat' : 'Permintaan Bahan' }}
    </div>

    <!-- TRANSACTION METADATA -->
    <table class="info-table">
        <tr>
            <td class="info-label">No. Transaksi</td>
            <td class="info-value">: TX-{{ str_pad($transaction->id, 5, '0', STR_PAD_LEFT) }}</td>
        </tr>
        <tr>
            <td class="info-label">Peminjam</td>
            <td class="info-value">
                @if($transaction->is_insidentil)
                    : {{ $transaction->peminjam_insidentil }} <span style="color: #6b7280; font-size: 10px;">(Peminjaman Insidentil)</span>
                @else
                    : {{ $transaction->user->name }} (NIM: {{ $transaction->user->nomor_induk }})
                @endif
            </td>
        </tr>
        <tr>
            <td class="info-label">Jurusan</td>
            <td class="info-value">: {{ ucwords(str_replace('_', ' ', $transaction->jurusan)) }}</td>
        </tr>
        <tr>
            <td class="info-label">Dosen Penanggung Jawab</td>
            <td class="info-value">: {{ $transaction->penanggung_jawab }}</td>
        </tr>
        <tr>
            <td class="info-label">Mata Kuliah / Kegiatan</td>
            <td class="info-value">: {{ $transaction->kegiatan }}</td>
        </tr>
        <tr>
            <td class="info-label">Tanggal Pengajuan</td>
            <td class="info-value">: {{ $transaction->tanggal_pengajuan->format('d-m-Y H:i') }}</td>
        </tr>
        @if($transaction->tipe === 'peminjaman_alat')
            <tr>
                <td class="info-label">Tanggal Pinjam</td>
                <td class="info-value">: {{ $transaction->tanggal_pinjam ? $transaction->tanggal_pinjam->format('d-m-Y H:i') : '-' }}</td>
            </tr>
            <tr>
                <td class="info-label">Rencana Kembali</td>
                <td class="info-value">: {{ $transaction->tanggal_kembali_rencana ? $transaction->tanggal_kembali_rencana->format('d-m-Y H:i') : '-' }}</td>
            </tr>
            @if($transaction->status === 'selesai')
                <tr>
                    <td class="info-label">Realisasi Kembali</td>
                    <td class="info-value">: {{ $transaction->tanggal_kembali_realisasi ? $transaction->tanggal_kembali_realisasi->format('d-m-Y H:i') : '-' }}</td>
                </tr>
            @endif
        @endif
        <tr>
            <td class="info-label">Status Verifikasi</td>
            <td class="info-value" style="font-weight: bold; color: {{ $transaction->status === 'disetujui' || $transaction->status === 'selesai' ? '#0d9488' : ($transaction->status === 'ditolak' ? '#dc2626' : '#d97706') }};">
                : {{ strtoupper($transaction->status) }}
            </td>
        </tr>
        @if(in_array($transaction->status, ['ditolak', 'ditangguhkan']) && $transaction->catatan_laboran)
            <tr>
                <td class="info-label" style="color: #dc2626; font-weight: bold;">Alasan / Catatan</td>
                <td class="info-value" style="font-style: italic; color: #dc2626; font-weight: bold;">: {{ $transaction->catatan_laboran }}</td>
            </tr>
        @endif
        @if($transaction->status_pengembalian)
            <tr>
                <td class="info-label">Status Pengembalian</td>
                <td class="info-value" style="font-weight: bold; color: {{ $transaction->status_pengembalian === 'selesai' ? '#0d9488' : '#d97706' }};">
                    : {{ $transaction->status_pengembalian === 'selesai' ? 'SELESAI (KEMBALI UTUH)' : 'BELUM SELESAI (KEMBALI DENGAN CATATAN)' }}
                </td>
            </tr>
            @if($transaction->catatan_pengembalian)
                <tr>
                    <td class="info-label">Catatan Pengembalian</td>
                    <td class="info-value" style="font-style: italic; color: #dc2626;">: {{ $transaction->catatan_pengembalian }}</td>
                </tr>
            @endif
        @endif
    </table>

    <!-- DETAILS TABLE -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 10%">No</th>
                <th style="width: 25%">Kode Barang</th>
                <th style="width: 45%">Nama Barang</th>
                <th style="width: 20%">Jumlah Disetujui</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $groupedDetails = $transaction->details->groupBy('package_id');
                $rowNum = 1;
            @endphp
            @foreach($groupedDetails as $pkgId => $details)
                @if($pkgId)
                    @php 
                        $firstDetail = $details->first();
                        $packageName = $firstDetail->package ? $firstDetail->package->nama_paket : 'Paket Terhapus';
                        $packageQty = $firstDetail->package_qty;
                    @endphp
                    <tr>
                        <td colspan="4" style="background-color: #f0fdfa; font-weight: bold; text-align: left; color: #0f766e; padding: 6px 8px; border: 1px solid #e5e7eb;">
                            🎁 Paket: {{ $packageName }} ({{ $packageQty }} set)
                        </td>
                    </tr>
                    @foreach($details as $detail)
                        <tr>
                            <td>{{ $rowNum++ }}</td>
                            <td>{{ $detail->item->kode_barang }}</td>
                            <td class="text-left" style="padding-left: 15px;">{{ $detail->item->nama_barang }}</td>
                            <td>{{ $detail->jumlah_diminta }} {{ $detail->item->satuan }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="4" style="background-color: #f9fafb; font-weight: bold; text-align: left; color: #4b5563; padding: 6px 8px; border: 1px solid #e5e7eb;">
                            📦 Barang Satuan (Bukan Paket)
                        </td>
                    </tr>
                    @foreach($details as $detail)
                        <tr>
                            <td>{{ $rowNum++ }}</td>
                            <td>{{ $detail->item->kode_barang }}</td>
                            <td class="text-left" style="padding-left: 15px;">{{ $detail->item->nama_barang }}</td>
                            <td>{{ $detail->jumlah_diminta }} {{ $detail->item->satuan }}</td>
                        </tr>
                    @endforeach
                @endif
            @endforeach
        </tbody>
    </table>

    <!-- SIGNATURE SECTION -->
    <table class="sign-table">
        <tr>
            <td class="sign-cell">
                @if($transaction->is_insidentil)
                    <div class="sign-title">Penerima (Insidentil)</div>
                    <div style="margin-top: 50px; font-weight: bold;">( {{ $transaction->peminjam_insidentil }} )</div>
                    <div>Peminjam Non-Mahasiswa</div>
                @else
                    <div class="sign-title">Penerima (Mahasiswa)</div>
                    <div style="margin-top: 50px; font-weight: bold;">( {{ $transaction->user ? $transaction->user->name : 'Non-Mahasiswa' }} )</div>
                    <div>NIM. {{ $transaction->user ? $transaction->user->nomor_induk : '-' }}</div>
                @endif
            </td>
            <td class="sign-cell">
                <div class="sign-title">Menyetujui (Laboran)</div>
                <div class="qr-container">
                    @if($qrBase64)
                        <img src="data:image/png;base64,{{ $qrBase64 }}" style="width: 90px; height: 90px;" alt="Tanda Tangan QR Code" />
                    @else
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data={{ urlencode($qrPayload) }}" style="width: 90px; height: 90px;" alt="Tanda Tangan QR Code" />
                    @endif
                </div>
                <div style="font-weight: bold; color: #0f766e; font-size: 9px; margin-top: 5px;">TANDA TANGAN ELEKTRONIK</div>
                <div style="font-size: 8px; color: #6b7280; margin-bottom: 5px;">Dinyatakan Sah secara Sistem</div>
                <div style="font-weight: bold; margin-top: 10px;">( {{ $transaction->laboran ? $transaction->laboran->name : 'Laboran Si-Lab' }} )</div>
                <div style="font-size: 9px; color: #4b5563;">NIP. {{ $transaction->laboran ? $transaction->laboran->nomor_induk : '-' }}</div>
            </td>
        </tr>
    </table>

</body>
</html>
