<?php
$laboran = file_get_contents('resources/views/dashboard-laboran.blade.php');

// 1. Add roomLevel1Tab to x-data
$laboran = str_replace(
    "roomActiveTab: 'pending',",
    "roomLevel1Tab: localStorage.getItem('laboranRoomLevel1Tab') || 'manajemen',\n               roomActiveTab: 'pending',",
    $laboran
);

// 2. Change Insidentil texts
$laboran = str_replace(
    'Catat Insidentil (Eksternal)',
    'Tambah Peminjaman (Oleh Laboran)',
    $laboran
);
$laboran = str_replace(
    'Peminjaman Insidentil (Eksternal)',
    'Tambah Peminjaman (Oleh Laboran)',
    $laboran
);

// 3. Update the Modal for Insidentil
$laboran = str_replace(
    'Pencatatan Peminjaman Insidentil (Eksternal)',
    'Form Peminjaman oleh Laboran',
    $laboran
);
$laboran = str_replace(
    'Nama Peminjam Eksternal / Insidentil',
    'Nama Peminjam (Eksternal / Pegawai Poltekkes)',
    $laboran
);
$laboran = str_replace(
    'Contoh: John Doe (Universitas ABC) atau kegiatan eksternal',
    'Contoh: Budi (Pegawai Poltekkes) atau John (Universitas ABC)',
    $laboran
);
$laboran = str_replace(
    'Catat Peminjaman Eksternal',
    'Simpan Peminjaman',
    $laboran
);

// 4. Refactor the Tabs
// The existing tabs block starts at `{{-- TABS --}}` and ends right before `{{-- TAB: PENDING --}}`
preg_match('/\{\{-- TABS --\}\}.*?\{\{-- TAB: PENDING --\}\}/s', $laboran, $matches);
if (!empty($matches)) {
    $oldTabs = $matches[0];
    
    // We want to create Level 1 Tabs, then Level 2 Tabs inside 'manajemen'
    // Extract the first 5 buttons from the old tabs to become the Level 2 tabs.
    // The last 2 buttons (Tersedia, Digunakan) are discarded because they are Level 1 now.
    preg_match_all('/<button @click="roomActiveTab = .*?<\/button>/s', $oldTabs, $buttons);
    if (count($buttons[0]) >= 7) {
        $level2Buttons = implode("\n", array_slice($buttons[0], 0, 5));
        
        $newTabsStruct = '{{-- LEVEL 1 TABS --}}
                          <div class="flex border-b border-gray-200 mb-5 gap-1 overflow-x-auto">
                              <button @click="roomLevel1Tab = \'manajemen\'; localStorage.setItem(\'laboranRoomLevel1Tab\', \'manajemen\')" :class="roomLevel1Tab === \'manajemen\' ? \'border-b-2 border-indigo-600 text-indigo-700 font-bold\' : \'text-gray-500 hover:text-gray-700\'" class="px-5 py-2.5 text-sm font-medium transition whitespace-nowrap">
                                  Manajemen Peminjaman Ruangan
                              </button>
                              <button @click="roomLevel1Tab = \'tersedia\'; localStorage.setItem(\'laboranRoomLevel1Tab\', \'tersedia\')" :class="roomLevel1Tab === \'tersedia\' ? \'border-b-2 border-teal-700 text-teal-800 font-bold\' : \'text-gray-500 hover:text-gray-700\'" class="px-5 py-2.5 text-sm font-medium transition whitespace-nowrap">
                                  🏫 Ruangan Tersedia
                              </button>
                              <button @click="roomLevel1Tab = \'dipinjam\'; localStorage.setItem(\'laboranRoomLevel1Tab\', \'dipinjam\')" :class="roomLevel1Tab === \'dipinjam\' ? \'border-b-2 border-amber-500 text-amber-700 font-bold\' : \'text-gray-500 hover:text-gray-700\'" class="px-5 py-2.5 text-sm font-medium transition whitespace-nowrap">
                                  🔒 Ruangan Dipinjam
                              </button>
                          </div>

                          {{-- LEVEL 2 TABS (Only shown if roomLevel1Tab === \'manajemen\') --}}
                          <div x-show="roomLevel1Tab === \'manajemen\'" x-transition>
                              <div class="flex border-b border-gray-100 mb-5 gap-1 overflow-x-auto">
                                  ' . $level2Buttons . '
                              </div>
                              
                              {{-- TAB: PENDING --}}';
                              
        $laboran = str_replace($oldTabs, $newTabsStruct, $laboran);
    }
}

// 5. Wrap the content sections so they respect roomLevel1Tab
// Pending, Disetujui, Insidentil, Ditolak, Master all need to be inside the roomLevel1Tab === 'manajemen' div.
// To do this simply, we will just add `&& roomLevel1Tab === 'manajemen'` to their x-show conditions!
$laboran = str_replace("x-show=\"roomActiveTab === 'pending'\"", "x-show=\"roomLevel1Tab === 'manajemen' && roomActiveTab === 'pending'\"", $laboran);
$laboran = str_replace("x-show=\"roomActiveTab === 'disetujui'\"", "x-show=\"roomLevel1Tab === 'manajemen' && roomActiveTab === 'disetujui'\"", $laboran);
$laboran = str_replace("x-show=\"roomActiveTab === 'insidentil'\"", "x-show=\"roomLevel1Tab === 'manajemen' && roomActiveTab === 'insidentil'\"", $laboran);
$laboran = str_replace("x-show=\"roomActiveTab === 'ditolak'\"", "x-show=\"roomLevel1Tab === 'manajemen' && roomActiveTab === 'ditolak'\"", $laboran);
$laboran = str_replace("x-show=\"roomActiveTab === 'master'\"", "x-show=\"roomLevel1Tab === 'manajemen' && roomActiveTab === 'master'\"", $laboran);

// For Tersedia and Dipinjam, their x-show should just be roomLevel1Tab === 'tersedia' and 'dipinjam' respectively
$laboran = str_replace("x-show=\"roomActiveTab === 'tersedia'\"", "x-show=\"roomLevel1Tab === 'tersedia'\"", $laboran);
$laboran = str_replace("x-show=\"roomActiveTab === 'digunakan'\"", "x-show=\"roomLevel1Tab === 'dipinjam'\"", $laboran);

// Close the wrapper div? Actually I used `&& roomLevel1Tab === ...`, so no need for wrapper divs! I can remove the opening wrapper div I added in Step 4.
$laboran = str_replace('<div x-show="roomLevel1Tab === \'manajemen\'" x-transition>', '', $laboran);

// Let's fix the extra closing tag issue. Wait, I didn't add a closing tag. Since I removed the wrapper, the nesting is perfectly flat, which is better for Alpine.

// Wait, the Filter Bar needs to show when roomLevel1Tab is 'tersedia' or 'dipinjam'? 
// Ah, the filter bar in Mahasiswa is shown when `roomActiveTab !== 'riwayat'`. 
// Let's find the filter bar in Laboran and change its condition.
$laboran = str_replace("x-show=\"roomActiveTab !== 'riwayat'\"", "x-show=\"roomLevel1Tab === 'tersedia' || roomLevel1Tab === 'dipinjam'\"", $laboran);

file_put_contents('resources/views/dashboard-laboran.blade.php', $laboran);
echo "Restructured UI successfully!\n";
