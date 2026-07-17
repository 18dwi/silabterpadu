<!DOCTYPE html>
<html>
<head>
    <title>Rekap Penggunaan Ruangan</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-center { text-align: center; }
        .mb-2 { margin-bottom: 10px; }
    </style>
</head>
<body>
    <h2 class="text-center mb-2">REKAPITULASI PENGGUNAAN RUANGAN</h2>
    <p class="text-center mb-2">
        Periode: {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d M Y') : '-' }} s/d {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d M Y') : '-' }}
        <br>Jurusan: {{ ucfirst(str_replace('_', ' ', $jurusan)) }}
    </p>

    <table>
        <thead>
            <tr>
                <th class="text-center">No</th>
                <th>Kode Ruangan</th>
                <th>Nama Ruangan</th>
                <th>Lokasi</th>
                <th class="text-center">Total Mahasiswa</th>
                <th class="text-center">Total Jam</th>
            </tr>
        </thead>
        <tbody>
            @foreach($roomUsages as $index => $ru)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $ru['room']->kode_ruangan }}</td>
                <td>{{ $ru['room']->nama_ruangan }}</td>
                <td>{{ $ru['room']->lokasi ?? '-' }}</td>
                <td class="text-center">{{ $ru['total_mahasiswa'] }} org</td>
                <td class="text-center">{{ number_format($ru['total_jam'], 1, ',', '.') }} jam</td>
            </tr>
            @endforeach
            @if($roomUsages->isEmpty())
            <tr>
                <td colspan="6" class="text-center">Tidak ada data penggunaan ruangan pada periode ini.</td>
            </tr>
            @endif
        </tbody>
    </table>
</body>
</html>