<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Laporan Transaksi Lab - {{ $jurusan === 'semua' ? 'Semua Jurusan' : ucwords(str_replace('_', ' ', $jurusan)) }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            line-height: 1.5;
            padding: 20px;
            background-color: #fff;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
            color: #111;
            text-transform: uppercase;
        }
        .header p {
            margin: 5px 0 0 0;
            font-size: 13px;
            color: #666;
            font-weight: bold;
        }
        .meta-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            font-size: 13px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px 10px;
            text-align: left;
            font-size: 12px;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #fcfcfc;
        }
        .summary-box {
            display: flex;
            justify-content: flex-end;
            gap: 40px;
            font-size: 13px;
            font-weight: bold;
            margin-top: 20px;
            padding: 12px;
            border: 1px solid #ddd;
            background-color: #fafafa;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 10px;
            font-weight: bold;
            border-radius: 3px;
            border: 1px solid #ccc;
            text-transform: uppercase;
        }
        ul {
            margin: 0;
            padding-left: 15px;
        }
        @media print {
            body {
                padding: 0;
            }
            .no-print {
                display: none;
            }
            table {
                page-break-inside: auto;
            }
            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Rekapitulasi Transaksi</h1>
        <p>Laboratorium {{ $jurusan === 'semua' ? 'Semua Jurusan' : ucwords(str_replace('_', ' ', $jurusan)) }}</p>
    </div>

    <div class="meta-info">
        <div>
            <strong>Periode Laporan:</strong> 
            {{ $startDate ? \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') : '-' }} s/d {{ $endDate ? \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') : '-' }}
        </div>
        <div>
            <strong>Tanggal Cetak:</strong> {{ now()->translatedFormat('d F Y, H:i') }} WIB
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">No</th>
                <th style="width: 15%;">Tanggal</th>
                <th style="width: 20%;">Pengaju (NIM/NIDN)</th>
                <th style="width: 15%;">Tipe Transaksi</th>
                <th style="width: 30%;">Detail Item (Jumlah)</th>
                <th style="width: 15%; text-align: center;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reportTransactions as $index => $tx)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ $tx->tanggal_pengajuan->format('d M Y, H:i') }}</td>
                    <td>
                        @if($tx->is_insidentil)
                            <strong>{{ $tx->peminjam_insidentil }}</strong><br>
                            <span style="color: #666; font-size: 10px;">Insidentil (Non-Mahasiswa)</span>
                        @else
                            <strong>{{ $tx->user ? $tx->user->name : 'Akun Dihapus' }}</strong><br>
                            NIM/NIDN: {{ $tx->user ? $tx->user->nomor_induk : '-' }}
                        @endif
                    </td>
                    <td>
                        {{ $tx->tipe === 'peminjaman_alat' ? 'Pinjam Alat' : 'Minta Bahan' }}
                    </td>
                    <td>
                        <ul>
                            @foreach($tx->details as $det)
                                <li>{{ $det->item->nama_barang }} ({{ $det->jumlah_diminta }} unit)</li>
                            @endforeach
                        </ul>
                    </td>
                    <td style="text-align: center;">
                        <span class="badge">
                            {{ $tx->status }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #999;">Tidak ada data transaksi ditemukan pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary-box">
        <div>Total Transaksi: {{ $reportTransactions->count() }}</div>
        <div>Total Barang Keluar: {{ $totalItemsIssued }} unit</div>
    </div>

    <div style="text-align: center; margin-top: 40px;" class="no-print">
        <button onclick="window.print()" style="padding: 8px 16px; font-weight: bold; background-color: #0d9488; color: white; border: none; border-radius: 4px; cursor: pointer;">
            Cetak Laporan
        </button>
        <button onclick="window.close()" style="padding: 8px 16px; font-weight: bold; background-color: #64748b; color: white; border: none; border-radius: 4px; cursor: pointer; margin-left: 10px;">
            Tutup Halaman
        </button>
    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
