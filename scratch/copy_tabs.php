<?php
$mahasiswa = file_get_contents('resources/views/dashboard-mahasiswa.blade.php');
$laboran = file_get_contents('resources/views/dashboard-laboran.blade.php');

// Extract Filter Bar & Tabs from Mahasiswa
preg_match('/\{\{-- FILTER BAR --\}\}.*?\{\{-- TAB: RIWAYAT SAYA --\}\}/s', $mahasiswa, $matches);
if (empty($matches)) {
    echo "Could not extract from Mahasiswa\n";
    exit(1);
}
$extractedContent = $matches[0];
$extractedContent = str_replace('{{-- TAB: RIWAYAT SAYA --}}', '', $extractedContent);
// Remove the addRoomToForm button
$extractedContent = preg_replace('/<button type="button" @click="addRoomToForm.*?<\/button>/s', '', $extractedContent);

// In Laboran, we need to append the new tab buttons to the horizontal menu
$tabButtons = '
                            <button @click="roomActiveTab = \'tersedia\'" :class="roomActiveTab === \'tersedia\' ? \'border-b-2 border-teal-700 text-teal-800 font-bold\' : \'text-gray-500 hover:text-gray-700\'" class="px-5 py-2.5 text-sm font-medium transition whitespace-nowrap">
                                🏫 Ruangan Tersedia
                                <span class="ml-1 text-xs bg-teal-100 text-teal-800 px-1.5 py-0.5 rounded-full font-bold">{{ $availableRooms->count() }}</span>
                            </button>
                            <button @click="roomActiveTab = \'digunakan\'" :class="roomActiveTab === \'digunakan\' ? \'border-b-2 border-amber-500 text-amber-700 font-bold\' : \'text-gray-500 hover:text-gray-700\'" class="px-5 py-2.5 text-sm font-medium transition whitespace-nowrap">
                                🔒 Ruangan Digunakan
                                <span class="ml-1 text-xs bg-amber-100 text-amber-800 px-1.5 py-0.5 rounded-full font-bold">{{ $usedRoomItems->count() }}</span>
                            </button>
';
$laboran = str_replace('</button>
                        </div>

                        {{-- TAB: PENDING --}}', '</button>' . $tabButtons . '
                        </div>

                        {{-- TAB: PENDING --}}', $laboran);

// Append the extracted content at the end of the Peminjaman Ruangan view
$targetMarker = '                        </div>

                    </div>

        {{-- ====================== ROOM BOOKING MODALS ====================== --}}';

$replacement = '                        </div>

' . $extractedContent . '

                    </div>

        {{-- ====================== ROOM BOOKING MODALS ====================== --}}';

$laboran = str_replace($targetMarker, $replacement, $laboran);

file_put_contents('resources/views/dashboard-laboran.blade.php', $laboran);
echo "Done!\n";
