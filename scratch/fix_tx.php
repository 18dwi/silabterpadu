<?php
// 1. TransactionController.php
$path = 'app/Http/Controllers/TransactionController.php';
$content = file_get_contents($path);
$content = str_replace(
    "'tanggal_pengajuan' => \$request->tipe === 'permintaan_bahan' ? \$request->tanggal_pinjam : now(),",
    "'tanggal_pengajuan' => now(),",
    $content
);
file_put_contents($path, $content);

// 2. DashboardController.php
$path = 'app/Http/Controllers/DashboardController.php';
$content = file_get_contents($path);
$content = str_replace(
    "->orderBy('tanggal_pengajuan', 'desc')",
    "->orderBy('created_at', 'desc')",
    $content
);
file_put_contents($path, $content);

// 3. dashboard-laboran.blade.php
$path = 'resources/views/dashboard-laboran.blade.php';
$content = file_get_contents($path);

// Find the date display in transaction cards and add Tanggal Penggunaan for bahan
// Let's use a regex to find all <p> tags with 'Tanggal Pengajuan:' and append 'Tanggal Penggunaan' if it's bahan.
$pattern = '/(<p class="text-xs text-gray-500 font-medium">.*?Tanggal Pengajuan:.*?{{ \$tx->tanggal_pengajuan->format\(\'d-m-Y H:i\'\) }}.*?<\/p>)/s';
$replacement = '$1
                                                      @if($tx->tipe === \'permintaan_bahan\' && $tx->tanggal_pinjam)
                                                      <p class="text-xs text-indigo-600 font-bold mt-1">
                                                          📅 Tanggal Penggunaan: {{ \Carbon\Carbon::parse($tx->tanggal_pinjam)->format(\'d-m-Y\') }}
                                                      </p>
                                                      @elseif($tx->tipe === \'peminjaman_alat\' && $tx->tanggal_pinjam)
                                                      <p class="text-xs text-blue-600 font-bold mt-1">
                                                          📅 Tanggal Pinjam: {{ \Carbon\Carbon::parse($tx->tanggal_pinjam)->format(\'d-m-Y\') }}
                                                      </p>
                                                      @endif';
$content = preg_replace($pattern, $replacement, $content);

file_put_contents($path, $content);
echo "Changes applied!\n";
