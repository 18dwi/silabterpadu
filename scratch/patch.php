<?php
$laboran = file_get_contents('resources/views/dashboard-laboran.blade.php');
$extracted = file_get_contents('scratch/extracted_tabs.html');

$tabButtons = '
                            <button @click="roomActiveTab = \'tersedia\'" :class="roomActiveTab === \'tersedia\' ? \'border-b-2 border-teal-700 text-teal-800 font-bold\' : \'text-gray-500 hover:text-gray-700\'" class="px-5 py-2.5 text-sm font-medium transition whitespace-nowrap">
                                🏫 Ruangan Tersedia
                                <span class="ml-1 text-xs bg-teal-100 text-teal-800 px-1.5 py-0.5 rounded-full font-bold">{{ $availableRooms->count() }}</span>
                            </button>
                            <button @click="roomActiveTab = \'digunakan\'" :class="roomActiveTab === \'digunakan\' ? \'border-b-2 border-amber-500 text-amber-700 font-bold\' : \'text-gray-500 hover:text-gray-700\'" class="px-5 py-2.5 text-sm font-medium transition whitespace-nowrap">
                                🔒 Ruangan Digunakan
                                <span class="ml-1 text-xs bg-amber-100 text-amber-800 px-1.5 py-0.5 rounded-full font-bold">{{ $usedRoomItems->count() }}</span>
                            </button>
                        </div>

                        {{-- TAB: PENDING --}}';

$laboran = preg_replace('/<\/button>\s*<\/div>\s*\{\{-- TAB: PENDING --\}\}/s', '</button>' . $tabButtons, $laboran);

$replacement = "                              @endif\n                          </div>\n\n" . $extracted . "\n                      </div>\n\n        {{-- ====================== ROOM BOOKING MODALS ====================== --}}";

$laboran = preg_replace('/@endif\s*<\/div>\s*<\/div>\s*\{\{-- ====================== ROOM BOOKING MODALS ====================== --\}\}/s', $replacement, $laboran);

file_put_contents('resources/views/dashboard-laboran.blade.php', $laboran);
echo "Patched!\n";
