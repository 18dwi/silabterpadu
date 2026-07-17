<?php
$html = file_get_contents('resources/views/dashboard-superadmin.blade.php');
$lines = explode("\n", $html);
$open = 0;
foreach($lines as $i => $line) {
    if (strpos($line, 'flex-grow') !== false) echo "FLEX-GROW START: " . ($i+1) . " DEPTH: $open\n";
    $open += substr_count($line, '<div');
    $open -= substr_count($line, '</div');
    
    if (strpos($line, 'activeTab === \'kelola_akun\'') !== false) echo "KELOLA AKUN: " . ($i+1) . " DEPTH: $open\n";
    if (strpos($line, 'activeTab === \'rekap_ruangan\'') !== false) echo "REKAP RUANGAN: " . ($i+1) . " DEPTH: $open\n";
    if (strpos($line, 'activeTab === \'riwayat_transaksi\'') !== false) echo "RIWAYAT: " . ($i+1) . " DEPTH: $open\n";
}
echo "FINAL DEPTH: $open\n";
