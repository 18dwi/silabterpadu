<?php
$controllerPath = 'app/Http/Controllers/DashboardController.php';
$content = file_get_contents($controllerPath);

$methods = <<<'PHP'
    public function exportRoomPdf(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $jurusan = $request->input('jurusan', 'semua');

        $roomBookingsQuery = \App\Models\RoomBooking::with('items.room')
            ->where('status', '!=', 'ditolak')
            ->orderBy('tanggal_pengajuan', 'asc');

        if ($jurusan !== 'semua') {
            $roomBookingsQuery->where('jurusan', $jurusan);
        }

        if ($startDate && $endDate) {
            $roomBookingsQuery->whereBetween('tanggal_pengajuan', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ]);
        } else {
            $roomBookingsQuery->whereMonth('tanggal_pengajuan', now()->month)
                              ->whereYear('tanggal_pengajuan', now()->year);
        }

        $roomBookings = $roomBookingsQuery->get();

        $roomUsages = [];
        foreach ($roomBookings as $booking) {
            foreach ($booking->items as $item) {
                $roomId = $item->room_id;
                if (!isset($roomUsages[$roomId])) {
                    $roomUsages[$roomId] = [
                        'room' => $item->room,
                        'total_mahasiswa' => 0,
                        'total_jam' => 0,
                    ];
                }
                
                $roomUsages[$roomId]['total_mahasiswa'] += $booking->jumlah_mahasiswa;
                $roomUsages[$roomId]['total_jam'] += $item->calculateUsageHours();
            }
        }
        
        $roomUsages = collect($roomUsages)->sortByDesc('total_jam')->values();

        // Check if PDF class exists (using DomPDF or similar if installed)
        // Since we don't know the exact PDF library, we'll try to load a view first.
        // Wait! Let's just return a simple view that the browser can print, or use PDF facade.
        // I see other PDF methods in the project. Let's assume PDF::loadView() works.
        if (class_exists('\Barryvdh\DomPDF\Facade\Pdf') || class_exists('\PDF')) {
            $pdf = \PDF::loadView('reports.rooms_pdf', compact('roomUsages', 'startDate', 'endDate', 'jurusan'));
            return $pdf->download('rekap_ruangan_' . date('Ymd_His') . '.pdf');
        }

        // Fallback to HTML view if PDF facade isn't available
        return view('reports.rooms_pdf', compact('roomUsages', 'startDate', 'endDate', 'jurusan'));
    }

    public function exportRoomCsv(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $jurusan = $request->input('jurusan', 'semua');

        $roomBookingsQuery = \App\Models\RoomBooking::with('items.room')
            ->where('status', '!=', 'ditolak')
            ->orderBy('tanggal_pengajuan', 'asc');

        if ($jurusan !== 'semua') {
            $roomBookingsQuery->where('jurusan', $jurusan);
        }

        if ($startDate && $endDate) {
            $roomBookingsQuery->whereBetween('tanggal_pengajuan', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ]);
        } else {
            $roomBookingsQuery->whereMonth('tanggal_pengajuan', now()->month)
                              ->whereYear('tanggal_pengajuan', now()->year);
        }

        $roomBookings = $roomBookingsQuery->get();

        $roomUsages = [];
        foreach ($roomBookings as $booking) {
            foreach ($booking->items as $item) {
                $roomId = $item->room_id;
                if (!isset($roomUsages[$roomId])) {
                    $roomUsages[$roomId] = [
                        'room' => $item->room,
                        'total_mahasiswa' => 0,
                        'total_jam' => 0,
                    ];
                }
                
                $roomUsages[$roomId]['total_mahasiswa'] += $booking->jumlah_mahasiswa;
                $roomUsages[$roomId]['total_jam'] += $item->calculateUsageHours();
            }
        }
        
        $roomUsages = collect($roomUsages)->sortByDesc('total_jam')->values();

        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=rekap_ruangan_" . date('Ymd_His') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array('No', 'Kode Ruangan', 'Nama Ruangan', 'Lokasi', 'Total Mahasiswa Pengguna', 'Total Jam Penggunaan');

        $callback = function() use($roomUsages, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns, ';'); // Use semicolon for Excel standard

            $no = 1;
            foreach ($roomUsages as $ru) {
                $row['No'] = $no++;
                $row['Kode Ruangan'] = $ru['room']->kode_ruangan;
                $row['Nama Ruangan'] = $ru['room']->nama_ruangan;
                $row['Lokasi'] = $ru['room']->lokasi;
                $row['Total Mahasiswa Pengguna'] = $ru['total_mahasiswa'] . ' org';
                $row['Total Jam Penggunaan'] = number_format($ru['total_jam'], 1, ',', '.') . ' jam';

                fputcsv($file, array($row['No'], $row['Kode Ruangan'], $row['Nama Ruangan'], $row['Lokasi'], $row['Total Mahasiswa Pengguna'], $row['Total Jam Penggunaan']), ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
PHP;

// Insert before the last closing brace
$content = preg_replace('/}([\s]*)$/', $methods . "\n}$1", $content);
file_put_contents($controllerPath, $content);
echo "DashboardController updated with room exports!\n";

// Create PDF View
@mkdir('resources/views/reports', 0777, true);
$pdfView = <<<'HTML'
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
HTML;
file_put_contents('resources/views/reports/rooms_pdf.blade.php', $pdfView);
echo "PDF view created!\n";
