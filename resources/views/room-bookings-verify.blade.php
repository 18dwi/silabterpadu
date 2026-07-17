<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Peminjaman Ruangan</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: linear-gradient(135deg, #f0fdfa 0%, #e0f2fe 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .card { background: white; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.12); max-width: 520px; width: 100%; overflow: hidden; }
        .card-header { background: linear-gradient(135deg, #0d9488, #0f766e); padding: 28px 32px; text-align: center; }
        .card-header h1 { color: white; font-size: 20px; font-weight: 700; }
        .card-header p { color: #99f6e4; font-size: 13px; margin-top: 4px; }
        .badge-ok { background: #d1fae5; color: #065f46; padding: 10px 24px; border-radius: 50px; font-weight: 700; font-size: 15px; display: inline-flex; align-items: center; gap: 8px; }
        .badge-reject { background: #fee2e2; color: #991b1b; padding: 10px 24px; border-radius: 50px; font-weight: 700; font-size: 15px; display: inline-flex; align-items: center; gap: 8px; }
        .badge-pending { background: #fef9c3; color: #854d0e; padding: 10px 24px; border-radius: 50px; font-weight: 700; font-size: 15px; display: inline-flex; align-items: center; gap: 8px; }
        .card-body { padding: 28px 32px; }
        .status-row { text-align: center; margin-bottom: 24px; }
        .info-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f3f4f6; font-size: 13px; }
        .info-row:last-child { border-bottom: none; }
        .info-label { color: #6b7280; font-weight: 600; }
        .info-value { color: #111827; font-weight: 500; text-align: right; max-width: 60%; }
        .rooms-title { font-size: 13px; font-weight: 700; color: #0f766e; margin: 20px 0 10px; text-transform: uppercase; letter-spacing: 0.5px; }
        .room-item { background: #f0fdfa; border: 1px solid #99f6e4; border-radius: 8px; padding: 10px 14px; margin-bottom: 8px; }
        .room-name { font-weight: 700; color: #0f766e; font-size: 13px; }
        .room-detail { font-size: 11px; color: #6b7280; margin-top: 2px; }
        .footer { text-align: center; padding: 16px; background: #f9fafb; font-size: 11px; color: #9ca3af; }
    </style>
</head>
<body>
<div class="card">
    <div class="card-header">
        <h1>🏫 Verifikasi Peminjaman Ruangan</h1>
        <p>Silab Terpadu Poltekkes Jakarta I</p>
    </div>
    <div class="card-body">
        <div class="status-row">
            @if($booking->status === 'disetujui')
                <span class="badge-ok">✓ DOKUMEN SAH — DISETUJUI</span>
            @elseif($booking->status === 'ditolak')
                <span class="badge-reject">✗ DITOLAK</span>
            @else
                <span class="badge-pending">⏳ MENUNGGU VERIFIKASI</span>
            @endif
        </div>

        <div class="info-row">
            <span class="info-label">No. Peminjaman</span>
            <span class="info-value">RB-{{ str_pad($booking->id, 5, '0', STR_PAD_LEFT) }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Peminjam</span>
            <span class="info-value">{{ $booking->user?->name ?? '-' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">NIM</span>
            <span class="info-value">{{ $booking->user?->nomor_induk ?? '-' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Tujuan</span>
            <span class="info-value">{{ $booking->tujuan_penggunaan }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Jumlah Mahasiswa</span>
            <span class="info-value">{{ $booking->jumlah_mahasiswa }} orang</span>
        </div>
        @if($booking->laboran)
        <div class="info-row">
            <span class="info-label">Diverifikasi oleh</span>
            <span class="info-value">{{ $booking->laboran->name }}</span>
        </div>
        @endif

        <div class="rooms-title">Daftar Ruangan yang Dipinjam</div>
        @foreach($booking->items as $item)
        <div class="room-item">
            <div class="room-name">{{ $item->room?->nama_ruangan ?? '-' }} ({{ $item->room?->kode_ruangan }})</div>
            <div class="room-detail">
                Lokasi: {{ $item->room?->lokasi ?? '-' }} &nbsp;|&nbsp; Kapasitas: {{ $item->room?->kapasitas ?? '-' }} orang
            </div>
            <div class="room-detail">
                📅 {{ \Carbon\Carbon::parse($item->tanggal_mulai)->format('d-m-Y') }} s.d. {{ \Carbon\Carbon::parse($item->tanggal_selesai)->format('d-m-Y') }}
                &nbsp;|&nbsp; ⏰ {{ $item->waktu_mulai }} – {{ $item->waktu_selesai }}
            </div>
        </div>
        @endforeach
    </div>
    <div class="footer">
        Diverifikasi pada {{ now()->format('d-m-Y H:i') }} &nbsp;|&nbsp; Silab Terpadu Poltekkes Jakarta I
    </div>
</div>
</body>
</html>
