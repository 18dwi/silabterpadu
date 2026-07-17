<?php
$content = file_get_contents('resources/views/dashboard-superadmin.blade.php');

/* 1. Sidebar Renaming and Reordering */
// Change activeTab names and texts

// The current order:
// 'laporan_rekap' (Laporan & Statistik)
// 'kelola_akun' (Kelola Akun Pengguna)
// 'rekap_bahan' (Rekapitulasi Bahan)
// 'rekap_ruangan' (Rekapitulasi Penggunaan Ruangan)
// 'riwayat_hapus' (Riwayat Transaksi (Hapus))

// The new desired order:
// 1. Rekap Penggunaan Ruangan (activeTab = 'rekap_ruangan')
// 2. Rekap Penggunaan Alat (activeTab = 'laporan_rekap') -> previously "Laporan & Statistik"
// 3. Rekap Penggunaan Bahan (activeTab = 'rekap_bahan')
// 4. Kelola Akun Pengguna (activeTab = 'kelola_akun')
// 5. Bersihkan Riwayat (activeTab = 'riwayat_hapus')

// First, rename "Laporan & Statistik" to "Rekap Penggunaan Alat"
$content = str_replace('Laporan & Statistik', 'Rekap Penggunaan Alat', $content);
$content = str_replace('Rekapitulasi Bahan', 'Rekap Penggunaan Bahan', $content);
$content = str_replace('Rekapitulasi Penggunaan Ruangan', 'Rekap Penggunaan Ruangan', $content);
$content = str_replace('Riwayat Transaksi (Hapus)', 'Bersihkan Riwayat', $content);

// We need to reorder the sidebar buttons. Let's extract the Sidebar Menus div.
preg_match('/<!-- Sidebar Menus -->.*?<\/div>\s*<\/div>\s*<!-- MAIN CONTENT/s', $content, $sidebarMatches);
if (!empty($sidebarMatches)) {
    $sidebar = $sidebarMatches[0];
    
    // Extract individual buttons
    preg_match('/<!-- Tab: Laporan.*?<\/button>/s', $sidebar, $btnAlat);
    preg_match('/<!-- Tab: Kelola.*?<\/button>/s', $sidebar, $btnAkun);
    preg_match('/<!-- Tab: Rekapitulasi Bahan.*?<\/button>/s', $sidebar, $btnBahan);
    preg_match('/<!-- Tab: Rekapitulasi Penggunaan Ruangan.*?<\/button>/s', $sidebar, $btnRuangan);
    preg_match('/<!-- Tab: Riwayat Transaksi \(Hapus\).*?<\/button>/s', $sidebar, $btnRiwayat);
    
    // Decrease padding/font size of the buttons to fit in 1 page.
    // They are currently py-2.5 or py-3.
    $btnAlat = str_replace('py-2.5', 'py-1.5', str_replace('text-xs', 'text-[11px]', $btnAlat[0]));
    $btnAkun = str_replace('py-2.5', 'py-1.5', str_replace('text-xs', 'text-[11px]', $btnAkun[0]));
    $btnBahan = str_replace('py-2.5', 'py-1.5', str_replace('text-xs', 'text-[11px]', $btnBahan[0]));
    $btnRuangan = str_replace('py-2.5', 'py-1.5', str_replace('text-xs', 'text-[11px]', $btnRuangan[0]));
    $btnRiwayat = str_replace('py-2.5', 'py-1.5', str_replace('text-xs', 'text-[11px]', $btnRiwayat[0]));
    
    // Change activeTab class styling to be smaller padding
    
    $newSidebar = "<!-- Sidebar Menus -->
                        <div class=\"w-full space-y-1 pt-3 border-t border-gray-100\">
                            " . $btnRuangan . "
                            " . $btnAlat . "
                            " . $btnBahan . "
                            " . $btnAkun . "
                            " . $btnRiwayat . "
                        </div>
                    </div>
                </div>
                <!-- MAIN CONTENT";
    
    $content = str_replace($sidebarMatches[0], $newSidebar, $content);
}

// Ensure the default activeTab is 'rekap_ruangan'
$content = str_replace("activeTab: localStorage.getItem('superadminActiveTab') || 'laporan_rekap'", "activeTab: localStorage.getItem('superadminActiveTab') || 'rekap_ruangan'", $content);

/* 2. Move "Stok Bahan Hampir Habis" from Alat to Bahan */
preg_match('/<!-- LOW STOCK MATERIALS -->.*?<\/div>\s*<\/div>\s*<\/div>\s*<\/div>\s*<!-- TAB: KELOLA AKUN PENGGUNA -->/s', $content, $lowStockMatches);
if (!empty($lowStockMatches)) {
    // The match grabs the Low Stock section up to the next tab.
    // Actually, let's grab precisely the Low Stock section
    preg_match('/<!-- LOW STOCK MATERIALS -->.*?(?=<!-- TAB: KELOLA AKUN PENGGUNA -->)/s', $content, $lowStockOnly);
    if (!empty($lowStockOnly)) {
        $lowStockCode = $lowStockOnly[0];
        
        // Remove from current location
        $content = str_replace($lowStockCode, '', $content);
        
        // Find Rekapitulasi Bahan TAB and append inside it.
        // It ends at `<!-- TAB: REKAPITULASI PENGGUNAAN RUANGAN -->`
        $targetBahan = '<!-- TAB: REKAPITULASI PENGGUNAAN RUANGAN -->';
        $content = str_replace($targetBahan, "\n" . $lowStockCode . "\n                    " . $targetBahan, $content);
    }
}

/* 3. Horizontal Tabs for "Bersihkan Riwayat" */
// We need to add an x-data inside the Bersihkan Riwayat tab.
// Find the Bersihkan Riwayat Tab
$riwayatTabStart = '<!-- TAB: RIWAYAT TRANSAKSI (HAPUS) -->';
$riwayatTabNew = '<!-- TAB: BERSIHKAN RIWAYAT -->
                    <div x-show="activeTab === \'riwayat_hapus\'" class="space-y-6" style="display: none;" x-transition x-data="{ riwayatActiveTab: \'alat_bahan\' }">
                        
                        <div class="flex items-center justify-between mb-2">
                            <div>
                                <h1 class="text-xl font-bold text-gray-900 font-sans">Bersihkan Riwayat</h1>
                                <p class="text-xs text-gray-500 mt-1">Hapus riwayat lama (alat, bahan, ruangan) untuk mengosongkan ruang.</p>
                            </div>
                        </div>

                        <!-- Horizontal Tabs for Bersihkan Riwayat -->
                        <div class="flex border-b border-gray-200 mb-5 gap-1 overflow-x-auto">
                            <button @click="riwayatActiveTab = \'alat_bahan\'" :class="riwayatActiveTab === \'alat_bahan\' ? \'border-b-2 border-indigo-600 text-indigo-700 font-bold\' : \'text-gray-500 hover:text-gray-700\'" class="px-5 py-2.5 text-sm font-medium transition whitespace-nowrap">
                                Riwayat Alat & Bahan
                            </button>
                            <button @click="riwayatActiveTab = \'ruangan\'" :class="riwayatActiveTab === \'ruangan\' ? \'border-b-2 border-teal-600 text-teal-700 font-bold\' : \'text-gray-500 hover:text-gray-700\'" class="px-5 py-2.5 text-sm font-medium transition whitespace-nowrap">
                                Riwayat Ruangan
                            </button>
                        </div>
                        
                        <!-- TAB ALAT & BAHAN -->
                        <div x-show="riwayatActiveTab === \'alat_bahan\'" x-transition>
';

// Replace the start of the Riwayat tab
$content = preg_replace('/<!-- TAB: RIWAYAT TRANSAKSI \(HAPUS\) -->\s*<div x-show="activeTab === \'riwayat_hapus\'"[^>]*>[\s]*<div class="flex items-center justify-between mb-5">.*?<\/div>/s', $riwayatTabNew, $content);

// The end of the Riwayat tab needs to close the alat_bahan div and add the ruangan div.
// The end of the view file is where this tab ends.
// We look for the last closing divs in the file before scripts/modals (if any).
// Wait, the end of the file is just `</div></div></div></div></x-app-layout>`.
// So we can append the Riwayat Ruangan tab before the last 3 closing divs.
$ruanganTab = '
                        </div> <!-- End Tab Alat & Bahan -->

                        <!-- TAB RUANGAN -->
                        <div x-show="riwayatActiveTab === \'ruangan\'" x-transition style="display:none;">
                            @if($allRoomBookingsForDeletion->isEmpty())
                                <div class="bg-white rounded-xl border border-gray-200 py-16 text-center shadow-sm">
                                    <div class="text-4xl mb-3 font-sans">📭</div>
                                    <p class="text-gray-500 font-semibold font-sans">Belum ada riwayat peminjaman ruangan</p>
                                </div>
                            @else
                                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full text-sm">
                                            <thead class="bg-gray-50 text-gray-600 text-xs uppercase font-bold border-b border-gray-200">
                                                <tr>
                                                    <th class="px-4 py-3 text-left">Peminjam</th>
                                                    <th class="px-4 py-3 text-left">Ruangan & Waktu</th>
                                                    <th class="px-4 py-3 text-center">Status</th>
                                                    <th class="px-4 py-3 text-center">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100">
                                                @foreach($allRoomBookingsForDeletion as $rb)
                                                <tr class="hover:bg-gray-50 transition">
                                                    <td class="px-4 py-3">
                                                        <div class="font-bold text-gray-900">{{ $rb->is_insidentil ? $rb->peminjam_insidentil : ($rb->user?->name ?? \'-\') }}</div>
                                                        <div class="text-[10px] text-gray-500">{{ $rb->is_insidentil ? \'Eksternal\' : \'Mahasiswa\' }}</div>
                                                    </td>
                                                    <td class="px-4 py-3 text-xs text-gray-600">
                                                        @foreach($rb->items as $item)
                                                            <div>
                                                                <span class="font-bold text-teal-700">{{ $item->room->nama_ruangan }}</span>
                                                                ({{ Carbon\Carbon::parse($item->tanggal_mulai)->format(\'d M Y\') }} - {{ Carbon\Carbon::parse($item->tanggal_selesai)->format(\'d M Y\') }})
                                                            </div>
                                                        @endforeach
                                                    </td>
                                                    <td class="px-4 py-3 text-center">
                                                        <span class="text-[10px] px-2 py-0.5 rounded-full font-bold {{ $rb->status === \'disetujui\' ? \'bg-green-100 text-green-800\' : ($rb->status === \'ditolak\' ? \'bg-red-100 text-red-800\' : \'bg-yellow-100 text-yellow-800\') }}">
                                                            {{ strtoupper($rb->status) }}
                                                        </span>
                                                    </td>
                                                    <td class="px-4 py-3 text-center">
                                                        <form action="{{ route(\'room-bookings.destroy\', $rb->id) }}" method="POST" onsubmit="return confirm(\'Yakin ingin menghapus permanen riwayat ruangan ini?\');">
                                                            @csrf
                                                            @method(\'DELETE\')
                                                            <button type="submit" class="text-xs px-2 py-1 bg-red-50 text-red-600 rounded border border-red-200 font-semibold hover:bg-red-100 transition">
                                                                Hapus
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif
                        </div> <!-- End Tab Ruangan -->
';

// Insert before the last `</div>` of the tab content wrapper.
// The file ends with:
//                     </div> <!-- End TAB RIWAYAT -->
//                 </div> <!-- End Col-span -->
//             </div> <!-- End Grid -->
//         </div> <!-- End Max-w -->
//     </div> <!-- End x-data -->
// </x-app-layout>

$content = str_replace('</x-app-layout>', $ruanganTab . '
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>', $content);
// Wait! If I just prepend to </x-app-layout>, I will have extra closing tags.
// Let me just replace the exact end of the file.
preg_match('/(                    <\/div>\s*<\/div>\s*<\/div>\s*<\/div>\s*<\/div>\s*<\/x-app-layout>)$/s', $content, $endMatch);
if (!empty($endMatch)) {
    $newEnd = $ruanganTab . "\n" . $endMatch[1];
    $content = str_replace($endMatch[1], $newEnd, $content);
} else {
    // try a looser match
    preg_match('/(<\/div>\s*<\/div>\s*<\/div>\s*<\/div>\s*<\/x-app-layout>)$/s', $content, $endMatch2);
    if (!empty($endMatch2)) {
        $newEnd = $ruanganTab . "\n" . $endMatch2[1];
        $content = str_replace($endMatch2[1], $newEnd, $content);
    }
}

file_put_contents('resources/views/dashboard-superadmin.blade.php', $content);
echo "Sidebar and layout updated!\n";
