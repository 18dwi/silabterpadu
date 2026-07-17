<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Formulir Peminjaman Ruangan - Silab Terpadu {{ ucwords(str_replace('_', ' ', $booking->jurusan)) }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; color: #333; line-height: 1.4; margin: 0; padding: 0; }
        .header-table { width: 100%; border-collapse: collapse; border-bottom: 2px solid #0d9488; padding-bottom: 10px; margin-bottom: 20px; }
        .header-logo { width: 80px; text-align: left; vertical-align: middle; }
        .header-text { text-align: left; vertical-align: middle; padding-left: 15px; }
        .header-title { font-size: 16px; font-weight: bold; color: #0f766e; margin: 0; }
        .header-subtitle { font-size: 11px; color: #4b5563; margin: 2px 0 0 0; text-transform: uppercase; letter-spacing: 0.5px; }
        .doc-title { text-align: center; font-size: 13px; font-weight: bold; color: #1f2937; text-transform: uppercase; margin-bottom: 20px; letter-spacing: 1px; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table td { padding: 5px 0; vertical-align: top; }
        .info-label { width: 28%; font-weight: bold; color: #4b5563; }
        .info-value { width: 72%; color: #111827; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .items-table th { background-color: #f0fdfa; border: 1px solid #99f6e4; padding: 8px; font-weight: bold; text-align: center; color: #0f766e; }
        .items-table td { border: 1px solid #e5e7eb; padding: 8px; text-align: center; }
        .items-table td.text-left { text-align: left; }
        .sign-table { width: 100%; border-collapse: collapse; margin-top: 40px; }
        .sign-cell { width: 50%; text-align: center; vertical-align: top; }
        .sign-title { font-weight: bold; color: #4b5563; margin-bottom: 10px; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: bold; }
        .badge-pending { background: #fef9c3; color: #854d0e; }
        .badge-disetujui { background: #d1fae5; color: #065f46; }
        .badge-ditolak { background: #fee2e2; color: #991b1b; }
        .footer { text-align: center; margin-top: 30px; font-size: 9px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 8px; }
    </style>
</head>
<body>
@php
    $logoPath = public_path('images/logo_poltekkes.png');
    $logoBase64 = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : '';

    $kodeBooking = 'RB-' . str_pad($booking->id, 5, '0', STR_PAD_LEFT);
    $namaPeminjam = $booking->user ? $booking->user->name : '-';
    $namaLaboran  = $booking->laboran ? $booking->laboran->name : 'Laboran Silab';
    $qrData       = $verifyUrl;

    $qrApiUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=' . urlencode($qrData);
    $qrBase64 = '';
    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $qrApiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $qrImg = curl_exec($ch);
        curl_close($ch);
        if ($qrImg) $qrBase64 = base64_encode($qrImg);
    } catch (\Exception $e) {}

    $statusColor = match($booking->status) {
        'disetujui' => '#059669',
        'ditolak'   => '#dc2626',
        default     => '#d97706',
    };
@endphp

<!-- HEADER -->
<table class="header-table">
    <tr>
        <td class="header-logo">
            @if($logoBase64)
                <img src="data:image/png;base64,{{ $logoBase64 }}" style="height: 60px;" alt="Logo">
            @endif
        </td>
        <td class="header-text">
            <div class="header-title">Silab Terpadu {{ ucwords(str_replace('_', ' ', $booking->jurusan)) }}</div>
            <div class="header-subtitle">Poltekkes Kemenkes Jakarta I</div>
            <div style="font-size: 9px; color: #6b7280; margin-top: 2px;">Jl. Wijaya Kusuma No. 47 Cilandak, Jakarta Selatan</div>
        </td>
    </tr>
</table>

<!-- DOC TITLE -->
<div class="doc-title">Formulir Peminjaman Ruangan Laboratorium</div>

<!-- INFO TABLE -->
<table class="info-table">
    <tr>
        <td class="info-label">No. Peminjaman</td>
        <td class="info-value">: {{ $kodeBooking }}</td>
    </tr>
    <tr>
        <td class="info-label">Peminjam</td>
        <td class="info-value">
            @if($booking->is_insidentil)
                : {{ $booking->peminjam_insidentil }} (Eksternal / {{ $booking->institusi_insidentil ?? '-' }})
            @else
                : {{ $namaPeminjam }} (NIM: {{ $booking->user?->nomor_induk ?? '-' }})
            @endif
        </td>
    </tr>
    <tr>
        <td class="info-label">Jurusan</td>
        <td class="info-value">: {{ ucwords(str_replace('_', ' ', $booking->jurusan)) }}</td>
    </tr>
    <tr>
        <td class="info-label">Tujuan Penggunaan</td>
        <td class="info-value">: {{ $booking->tujuan_penggunaan }}</td>
    </tr>
    <tr>
        <td class="info-label">Jumlah Mahasiswa</td>
        <td class="info-value">: {{ $booking->jumlah_mahasiswa }} orang</td>
    </tr>
    <tr>
        <td class="info-label">Tanggal Pengajuan</td>
        <td class="info-value">: {{ $booking->tanggal_pengajuan->format('d-m-Y H:i') }}</td>
    </tr>
    <tr>
        <td class="info-label">Status</td>
        <td class="info-value" style="font-weight: bold; color: {{ $statusColor }};">: {{ strtoupper($booking->status) }}</td>
    </tr>
    @if($booking->catatan_laboran)
    <tr>
        <td class="info-label" style="color: #dc2626;">Catatan Laboran</td>
        <td class="info-value" style="color: #dc2626; font-style: italic;">: {{ $booking->catatan_laboran }}</td>
    </tr>
    @endif
</table>

<!-- ROOM ITEMS TABLE -->
<table class="items-table">
    <thead>
        <tr>
            <th style="width: 6%">No</th>
            <th style="width: 18%">Kode Ruangan</th>
            <th style="width: 26%">Nama Ruangan</th>
            <th style="width: 12%">Kapasitas</th>
            <th style="width: 19%">Tanggal Mulai–Selesai</th>
            <th style="width: 19%">Waktu Mulai–Selesai</th>
        </tr>
    </thead>
    <tbody>
        @foreach($booking->items as $i => $item)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $item->room?->kode_ruangan ?? '-' }}</td>
            <td class="text-left" style="padding-left: 10px;">{{ $item->room?->nama_ruangan ?? '-' }}</td>
            <td>{{ $item->room?->kapasitas ?? '-' }} org</td>
            <td>
                {{ \Carbon\Carbon::parse($item->tanggal_mulai)->format('d-m-Y') }}
                @if($item->tanggal_mulai != $item->tanggal_selesai)
                    <br>s.d. {{ \Carbon\Carbon::parse($item->tanggal_selesai)->format('d-m-Y') }}
                @endif
            </td>
            <td>{{ $item->waktu_mulai }} – {{ $item->waktu_selesai }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<!-- SIGNATURE SECTION -->
<table class="sign-table">
    <tr>
        <td class="sign-cell">
            @if($booking->is_insidentil)
                <div class="sign-title">Peminjam (Eksternal)</div>
                <div style="margin-top: 55px; font-weight: bold;">( {{ $booking->peminjam_insidentil }} )</div>
                <div style="font-size: 9px; color: #4b5563;">{{ $booking->institusi_insidentil ?? 'Pihak Luar/Umum' }}</div>
            @else
                <div class="sign-title">Peminjam (Mahasiswa)</div>
                <div style="margin-top: 55px; font-weight: bold;">( {{ $namaPeminjam }} )</div>
                <div style="font-size: 9px; color: #4b5563;">NIM. {{ $booking->user?->nomor_induk ?? '-' }}</div>
            @endif
        </td>
        <td class="sign-cell">
            <div class="sign-title">Menyetujui (Laboran)</div>
            <div style="margin: 8px 0;">
                @if($qrBase64)
                    <img src="data:image/png;base64,{{ $qrBase64 }}" style="width: 90px; height: 90px;" alt="QR Verifikasi" />
                @else
                    <img src="{{ $qrApiUrl }}" style="width: 90px; height: 90px;" alt="QR Verifikasi" />
                @endif
            </div>
            <div style="font-weight: bold; color: #0f766e; font-size: 9px;">TANDA TANGAN ELEKTRONIK</div>
            <div style="font-size: 8px; color: #6b7280; margin-bottom: 5px;">Scan QR untuk verifikasi keaslian dokumen</div>
            <div style="font-weight: bold; margin-top: 8px;">( {{ $namaLaboran }} )</div>
            <div style="font-size: 9px; color: #4b5563;">{{ $booking->laboran ? 'NIP. ' . ($booking->laboran->nomor_induk ?? '-') : 'Laboran Silab Terpadu' }}</div>
        </td>
    </tr>
</table>

<div class="footer">
    Dokumen ini digenerate secara otomatis oleh Sistem Silab Terpadu Poltekkes Jakarta I &nbsp;|&nbsp; {{ now()->format('d-m-Y H:i') }}
</div>
</body>
</html>
