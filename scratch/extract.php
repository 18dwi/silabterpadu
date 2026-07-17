<?php
$mahasiswa = file_get_contents('resources/views/dashboard-mahasiswa.blade.php');

preg_match('/\{\{-- FILTER BAR --\}\}.*?\{\{-- TAB: RIWAYAT SAYA --\}\}/s', $mahasiswa, $matches);
if (empty($matches)) {
    echo "Could not extract from Mahasiswa\n";
    exit(1);
}
$extractedContent = $matches[0];
$extractedContent = str_replace('{{-- TAB: RIWAYAT SAYA --}}', '', $extractedContent);
$extractedContent = preg_replace('/<button type="button" @click="addRoomToForm.*?<\/button>/s', '', $extractedContent);

file_put_contents('scratch/extracted_tabs.html', $extractedContent);
echo "Extracted!\n";
