<?php
$html = file_get_contents('resources/views/dashboard-superadmin.blade.php');

// 1. Remove the `<div x-show="historyTab === 'alat'"><div class="overflow-x-auto">` wrapper in kelola_akun
$html = preg_replace('/<div x-show="historyTab === \'alat\'">(<div class="overflow-x-auto">.*?<\/table>\s*<\/div>)/s', '$1', $html, 1);

// 2. Remove the second HUGE copy entirely.
// It starts at `<div x-show="historyTab === 'bahan'" style="display:none;"><div class="overflow-x-auto">`
$startOfSecondCopy = strpos($html, '<div x-show="historyTab === \'bahan\'" style="display:none;"><div class="overflow-x-auto">');
if ($startOfSecondCopy !== false) {
    // End is right before `<!-- TAB RUANGAN -->` which is inside the second copy.
    // Let's find `<!-- TAB RUANGAN -->` AFTER $startOfSecondCopy
    $endOfSecondCopy = strpos($html, '<!-- TAB RUANGAN', $startOfSecondCopy);
    if ($endOfSecondCopy !== false) {
        $html = substr($html, 0, $startOfSecondCopy) . substr($html, $endOfSecondCopy);
    }
}

// 3. Remove the extra `<!-- TAB RUANGAN -->` and its content that got left over?
// Wait, `check_dom2.php` showed TWO `historyTab === 'ruangan'`!
// 1439: <div x-show="historyTab === 'ruangan'"
// 1499: <div x-show="historyTab === 'ruangan'"
// Let's just find the very last one and remove it if it's duplicated.
// Actually, let's just find the first `riwayat_transaksi` and keep it, and delete EVERYTHING after its end!
// Where does `riwayat_transaksi` end? It ends at `</div>` before `</div> </div> </x-app-layout>`.
$appLayoutEnd = strpos($html, '</x-app-layout>');
// Let's just do this: The file got corrupted, I can just grab the exact strings.

// Let's grab:
$part1 = substr($html, 0, strpos($html, '<!-- TAB 3: BERSIHKAN RIWAYAT -->'));

// Now we need the CLEAN riwayat_transaksi. 
// We can reconstruct it!
$riwayat_transaksi = '
                    <!-- TAB 3: BERSIHKAN RIWAYAT -->
                    <div x-show="activeTab === \'riwayat_transaksi\'" class="space-y-6" style="display: none;" x-transition x-data="{ historyTab: \'alat\' }">
                        
                        <!-- Horizontal Menus for Bersihkan Riwayat -->
                        <div class="flex border-b border-gray-200 mb-2 gap-4">
                            <button @click="historyTab = \'alat\'" :class="historyTab === \'alat\' ? \'border-teal-500 text-teal-600\' : \'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300\'" class="px-1 py-2 border-b-2 font-semibold text-sm">Riwayat Peminjaman Alat</button>
                            <button @click="historyTab = \'bahan\'" :class="historyTab === \'bahan\' ? \'border-teal-500 text-teal-600\' : \'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300\'" class="px-1 py-2 border-b-2 font-semibold text-sm">Riwayat Permintaan Bahan</button>
                            <button @click="historyTab = \'ruangan\'" :class="historyTab === \'ruangan\' ? \'border-teal-500 text-teal-600\' : \'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300\'" class="px-1 py-2 border-b-2 font-semibold text-sm">Riwayat Peminjaman Ruangan</button>
                        </div>
                        
                        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900">Penghapusan Riwayat Transaksi</h3>
                                    <p class="text-xs text-gray-500">Sebagai superadmin, Anda dapat menghapus data log transaksi untuk merapikan riwayat **tanpa** memengaruhi ataupun mengembalikan stok inventaris.</p>
                                    <div class="mt-3">
                                        <input 
                                            type="text" 
                                            x-model="searchHistory" 
                                            placeholder="Cari nomor transaksi (e.g. TX-00005) atau nama peminjam..." 
                                            class="text-xs border-gray-300 rounded-md focus:ring-teal-500 focus:border-teal-500 py-1.5 px-3 w-full sm:w-80 shadow-sm"
                                        />
                                    </div>
                                </div>
                            </div>
';

// Find the ORIGINAL table (which is inside the FIRST copy of riwayat_transaksi before duplication)
// Wait, the original table for riwayat_transaksi was NEVER modified because the regex matched kelola_akun!
// So it is inside the file somewhere. We can find it by searching for `$allTransactionsForDeletion`.
preg_match('/<div class="overflow-x-auto">\s*<table class="min-w-full divide-y divide-gray-200 text-xs">.*?@forelse\(\$allTransactionsForDeletion as \$tx\).*?<\/table>\s*<\/div>\s*<\/div>/s', $html, $matchesTx);

if (!empty($matchesTx)) {
    $baseTxTable = $matchesTx[0];
    // Split into alat and bahan
    $alatTable = str_replace('@forelse($allTransactionsForDeletion as $tx)', '@forelse($allTransactionsForDeletion->where(\'tipe\', \'peminjaman_alat\') as $tx)', $baseTxTable);
    $alatTable = '<div x-show="historyTab === \'alat\'" x-transition>' . $alatTable . '</div>';

    $bahanTable = str_replace('@forelse($allTransactionsForDeletion as $tx)', '@forelse($allTransactionsForDeletion->where(\'tipe\', \'permintaan_bahan\') as $tx)', $baseTxTable);
    $bahanTable = '<div x-show="historyTab === \'bahan\'" style="display:none;" x-transition>' . $bahanTable . '</div>';

    $riwayat_transaksi .= "\n" . $alatTable . "\n" . $bahanTable . "\n";
} else {
    echo "Could not find base Tx Table!\n";
}

// Find Ruangan table
preg_match('/<!-- TAB RUANGAN -->.*?@if\(\$allRoomBookingsForDeletion->isEmpty\(\)\).*?<!-- End Tab Ruangan -->/s', $html, $matchesRoom);
if (!empty($matchesRoom)) {
    $roomTable = $matchesRoom[0];
    // Ensure x-show is historyTab === \'ruangan\'
    $roomTable = preg_replace('/<div x-show="historyTab === \'ruangan\'" x-transition style="display:none;">/', '<div x-show="historyTab === \'ruangan\'" x-transition style="display:none;">', $roomTable);
    $riwayat_transaksi .= "\n" . $roomTable . "\n";
} else {
    echo "Could not find Room Table!\n";
}

$riwayat_transaksi .= '
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>';

file_put_contents('resources/views/dashboard-superadmin.blade.php', $part1 . $riwayat_transaksi);
echo "Rebuilt completely!\n";
