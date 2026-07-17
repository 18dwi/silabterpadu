<?php
$html = file_get_contents('resources/views/dashboard-superadmin.blade.php');

// We know the duplicate starts at `<div x-show="historyTab === 'alat'"><div class="overflow-x-auto">` inside kelola_akun.
// Let's remove the wrapper from the FIRST kelola_akun table.
$html = preg_replace('/<div x-show="historyTab === \'alat\'">(<div class="overflow-x-auto">.*?<thead class="bg-gray-50 text-gray-500 font-semibold uppercase">\s*<tr>\s*<th class="px-4 py-3 text-left">Nama<\/th>.*?<\/table>\s*<\/div>)/s', '$1', $html, 1);

// Now the first copy of kelola_akun table is restored.
// The rest of the first copy continues to `rekap_ruangan` (line 507) and `riwayat_transaksi` (line 597).
// At line 967, `<div x-show="historyTab === 'bahan'" style="display:none;">` starts the SECOND HUGE COPY.
// We should DELETE this entire second huge copy!
// Wait, the second huge copy goes all the way to `<!-- TAB RUANGAN</div> -->` or something?
// Let's find the exact string to delete.

$startOfSecondCopy = strpos($html, '<div x-show="historyTab === \'bahan\'" style="display:none;"><div class="overflow-x-auto">');
if ($startOfSecondCopy !== false) {
    // Find the end of the second copy. It ends right before `<!-- TAB RUANGAN -->` which is followed by `<div x-show="historyTab === 'ruangan'`
    // Actually, in the second copy, the regex `<!-- TAB RUANGAN` was modified? No, it matched up to `<!-- TAB RUANGAN`.
    // Let's find the `<!-- TAB RUANGAN` that comes AFTER the second copy.
    $endOfSecondCopy = strpos($html, '<!-- TAB RUANGAN', $startOfSecondCopy);
    
    if ($endOfSecondCopy !== false) {
        // Delete the second copy
        $html = substr($html, 0, $startOfSecondCopy) . substr($html, $endOfSecondCopy);
    }
}

// Now we need to fix `riwayat_transaksi`.
// In the first copy, the `riwayat_transaksi` table (which should have been split into alat and bahan) was NOT split, because the regex matched `kelola_akun`'s table instead!
// Wait, if the regex matched `kelola_akun`'s table, then `riwayat_transaksi`'s table is STILL INTACT as the original `riwayat_transaksi` table!
// Let's check if the `riwayat_transaksi` table is intact.
file_put_contents('scratch/repaired.blade.php', $html);
echo "Repaired file written to scratch/repaired.blade.php\n";
