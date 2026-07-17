<?php
$path = 'resources/views/dashboard-superadmin.blade.php';
$content = file_get_contents($path);

// 1. Move `</div> </div>` that breaks the layout
// We will search for:
$searchLayout = '                    </div>
                    </div>

                    <!-- TAB REKAPITULASI PENGGUNAAN RUANGAN -->';

$replaceLayout = '                    <!-- TAB REKAPITULASI PENGGUNAAN RUANGAN -->';
$content = str_replace($searchLayout, $replaceLayout, $content);

// 2. Add Reset Button to Rekap Bahan
$searchBahan = '<div class="flex items-end">
                                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded text-xs font-semibold text-white bg-teal-600 hover:bg-teal-700 transition duration-150 shadow-sm">
                                        Filter Rekap Bahan
                                    </button>
                                </div>';
$replaceBahan = '<div class="flex items-end gap-2 w-full">
                                    <a href="{{ route(\'dashboard\', [\'active_tab\' => \'rekap_bahan\']) }}" class="w-1/3 inline-flex justify-center items-center px-4 py-2 border border-gray-300 rounded text-xs font-semibold text-gray-600 bg-white hover:bg-gray-50 transition duration-150 shadow-sm">
                                        Reset
                                    </a>
                                    <button type="submit" class="w-2/3 inline-flex justify-center items-center px-4 py-2 border border-transparent rounded text-xs font-semibold text-white bg-teal-600 hover:bg-teal-700 transition duration-150 shadow-sm">
                                        Filter
                                    </button>
                                </div>';
$content = str_replace($searchBahan, $replaceBahan, $content);

// 3. Horizontal Menus for Bersihkan Riwayat
// In "TAB 3: BERSIHKAN RIWAYAT", we change the root div to include x-data="{ historyTab: 'alat' }"
// Note that `activeTab === 'riwayat_transaksi'` already has an x-show
$searchHistoryTab = '<div x-show="activeTab === \'riwayat_transaksi\'" class="space-y-6" style="display: none;" x-transition>';
$replaceHistoryTab = '<div x-show="activeTab === \'riwayat_transaksi\'" class="space-y-6" style="display: none;" x-transition x-data="{ historyTab: \'alat\' }">
                        
                        <!-- Horizontal Menus for Bersihkan Riwayat -->
                        <div class="flex border-b border-gray-200 mb-2 gap-4">
                            <button @click="historyTab = \'alat\'" :class="historyTab === \'alat\' ? \'border-teal-500 text-teal-600\' : \'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300\'" class="px-1 py-2 border-b-2 font-semibold text-sm">Riwayat Peminjaman Alat</button>
                            <button @click="historyTab = \'bahan\'" :class="historyTab === \'bahan\' ? \'border-teal-500 text-teal-600\' : \'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300\'" class="px-1 py-2 border-b-2 font-semibold text-sm">Riwayat Permintaan Bahan</button>
                            <button @click="historyTab = \'ruangan\'" :class="historyTab === \'ruangan\' ? \'border-teal-500 text-teal-600\' : \'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300\'" class="px-1 py-2 border-b-2 font-semibold text-sm">Riwayat Peminjaman Ruangan</button>
                        </div>';
$content = str_replace($searchHistoryTab, $replaceHistoryTab, $content);

// Now wrap the alat & bahan table in a div that only shows for alat OR bahan, based on filter.
// Wait! We have $allTransactionsForDeletion. We can just use Alpine to hide/show rows!
// Or we can just create two tables. Wait, since it's already one table and filtered via Blade?
// No, the table loops through $allTransactionsForDeletion.
// We can just modify the `x-show` on the `<tr>`!
// Wait, for Ruangan, we ALREADY have a separate tab `riwayatActiveTab === 'ruangan'`.
// Let's remove the old tab logic `riwayatActiveTab` that I created before!

// In the previous request, I might have added `<div x-data="{ riwayatActiveTab: 'alat' }">` ?
// Let's see if we can find `riwayatActiveTab`
$content = preg_replace('/<div class="flex border-b border-gray-200 mb-4">\s*<button @click="riwayatActiveTab = \'alat_bahan\'".*?<\/div>\s*<!-- TAB ALAT & BAHAN -->\s*<div x-show="riwayatActiveTab === \'alat_bahan\'" x-transition>/s', '', $content);
$content = str_replace('</div> <!-- End Tab Alat & Bahan -->', '', $content);

$content = preg_replace('/<!-- TAB RUANGAN -->\s*<div x-show="riwayatActiveTab === \'ruangan\'" x-transition style="display:none;">/s', '<!-- TAB RUANGAN -->
                          <div x-show="historyTab === \'ruangan\'" x-transition style="display:none;">', $content);
$content = str_replace('</div> <!-- End Tab Ruangan -->', '', $content);

// Now for the Alat and Bahan tables, we can either duplicate the table or just filter the rows.
// Let's just duplicate the table for simplicity and robustness.
// Wait, the table starts with `<div class="overflow-x-auto">` and ends with `</table></div></div>` (the table wrapper).
// Let's find that block.
preg_match('/<div class="overflow-x-auto">\s*<table class="min-w-full divide-y divide-gray-200 text-xs">.*?<\/table>\s*<\/div>\s*<\/div>\s*(?:@endif)?\s*(<!-- TAB RUANGAN|$)/s', $content, $matches);

if(!empty($matches)){
    $tableBlock = $matches[0];
    
    // Create Alat Table
    $alatTable = str_replace('@forelse($allTransactionsForDeletion as $tx)', '@forelse($allTransactionsForDeletion->where(\'tipe\', \'peminjaman_alat\') as $tx)', $tableBlock);
    $alatTable = '<div x-show="historyTab === \'alat\'">' . $alatTable . '</div>';
    
    // Create Bahan Table
    $bahanTable = str_replace('@forelse($allTransactionsForDeletion as $tx)', '@forelse($allTransactionsForDeletion->where(\'tipe\', \'permintaan_bahan\') as $tx)', $tableBlock);
    $bahanTable = '<div x-show="historyTab === \'bahan\'" style="display:none;">' . $bahanTable . '</div>';
    
    // Replace the original table with BOTH
    $content = str_replace($tableBlock, $alatTable . "\n" . $bahanTable, $content);
}

// Add the missing `</div> </div>` at the very end of riwayat_transaksi
// riwayat_transaksi ends where `activeTab === 'riwayat_transaksi'` ends.
// Let's just add it right before `</x-app-layout>` or right before `<script>`
// Wait, in blade it's before `</x-app-layout>`
$content = str_replace("</x-app-layout>", "                    </div>\n                    </div>\n</x-app-layout>", $content);


file_put_contents($path, $content);
echo "Changes applied successfully!\n";
