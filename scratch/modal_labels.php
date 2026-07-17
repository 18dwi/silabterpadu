<?php
$laboran = file_get_contents('resources/views/dashboard-laboran.blade.php');

$replacements = [
    'Catat Peminjaman Ruangan Insidentil (Eksternal)' => 'Form Peminjaman oleh Laboran',
    'placeholder="Contoh: Dr. Budi Santoso"' => 'placeholder="Contoh: Budi (Pegawai) / Dr. John (Eksternal)"',
    '<label class="block text-xs font-bold text-gray-700 mb-1">Institusi Asal <span class="text-red-500">*</span></label>' => '<label class="block text-xs font-bold text-gray-700 mb-1">Institusi / Bagian <span class="text-red-500">*</span></label>',
    'placeholder="Contoh: Universitas Indonesia"' => 'placeholder="Contoh: Kepegawaian Poltekkes / Universitas ABC"',
    // The previous script already replaced button names, but I should double check:
    'Simpan & Setujui' => 'Simpan & Setujui', // maybe leave this as is
];

foreach ($replacements as $old => $new) {
    $laboran = str_replace($old, $new, $laboran);
}

// Let's also find the "Nama Peminjam Eksternal / Insidentil" label
$laboran = str_replace(
    '<label class="block text-xs font-bold text-gray-700 mb-1">Nama Peminjam Eksternal <span class="text-red-500">*</span></label>',
    '<label class="block text-xs font-bold text-gray-700 mb-1">Nama Peminjam <span class="text-red-500">*</span></label>',
    $laboran
);

file_put_contents('resources/views/dashboard-laboran.blade.php', $laboran);
echo "Modal labels updated!\n";
