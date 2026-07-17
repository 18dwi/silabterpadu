<?php
$path = 'C:/laragon/www/silab-keperawatan/resources/views/dashboard-laboran.blade.php';
$content = file_get_contents($path);

// Replace for 'alat' / 'bahan' (replace all occurrences)
$content = str_replace(
    'title="Unduh Rekap Excel (MS Excel Compatible)"' . "\r\n" . '                                        <svg class="-ml-1 mr-2 h-4 w-4 text-emerald-600"',
    'title="Unduh Rekap Excel (MS Excel Compatible)">' . "\r\n" . '                                        <svg class="-ml-1 mr-2 h-4 w-4 text-emerald-600"',
    $content
);

$content = str_replace(
    'title="Unduh Rekap Excel (MS Excel Compatible)"' . "\n" . '                                        <svg class="-ml-1 mr-2 h-4 w-4 text-emerald-600"',
    'title="Unduh Rekap Excel (MS Excel Compatible)">' . "\n" . '                                        <svg class="-ml-1 mr-2 h-4 w-4 text-emerald-600"',
    $content
);

file_put_contents($path, $content);
echo "Fix Excel Icon PHP: Done!\n";
?>
