<?php
$file = 'resources/views/dashboard-superadmin.blade.php';
$html = file_get_contents($file);

// Find the Alat table and wrap it
$alatStart = '<div class="overflow-x-auto">';
$alatTableStart = strpos($html, $alatStart, strpos($html, 'historyTab = \'alat\''));
// Wait, the Alat table is right after searchHistory div.
// Let's replace the EXACT `<div class="overflow-x-auto">` that comes after `searchHistory`.
$searchMarker = 'x-model="searchHistory"';
$searchPos = strpos($html, $searchMarker);
$overflowPos = strpos($html, '<div class="overflow-x-auto">', $searchPos);

$html = substr_replace($html, '<div x-show="historyTab === \'alat\'" x-transition style="display:none;">' . "\n" . '                            <div class="overflow-x-auto">', $overflowPos, strlen('<div class="overflow-x-auto">'));

// Now we need to close the `historyTab === 'alat'` div after the table closes.
// The alat table ends at `</table>\n                            </div>`.
$tableEndMarker = '</table>
                            </div>';
$tableEndPos = strpos($html, $tableEndMarker, $overflowPos);
$afterTableEnd = $tableEndPos + strlen($tableEndMarker);

// Add closing div and then insert the Bahan and Ruangan tables!
$insert = '
                        </div>

                        <div x-show="historyTab === \'bahan\'" x-transition style="display:none;">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 text-xs">
                                    <thead class="bg-gray-50 text-gray-500 font-semibold uppercase">
                                        <tr>
                                            <th class="px-4 py-3 text-left">ID Transaksi</th>
                                            <th class="px-4 py-3 text-left">Peminjam</th>
                                            <th class="px-4 py-3 text-left">Tanggal</th>
                                            <th class="px-4 py-3 text-left">Bahan</th>
                                            <th class="px-4 py-3 text-center">Status</th>
                                            <th class="px-4 py-3 text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-150 bg-white">
                                        @forelse($allTransactionsForDeletion->where(\'tipe\', \'permintaan_bahan\') as $tx)
                                            @php
                                                $borrowerName = $tx->is_insidentil ? $tx->peminjam_insidentil : ($tx->user ? $tx->user->name : \'Akun Dihapus\');
                                                $txCode = \'TX-\' . str_pad($tx->id, 5, \'0\', STR_PAD_LEFT);
                                            @endphp
                                            <tr x-show="searchHistory === \'\' || \'{{ strtolower($txCode) }}\'.includes(searchHistory.toLowerCase()) || \'{{ strtolower(addslashes($borrowerName)) }}\'.includes(searchHistory.toLowerCase())" class="hover:bg-gray-50/50 transition-colors">
                                                <td class="px-4 py-3 font-bold text-teal-700">{{ $txCode }}</td>
                                                <td class="px-4 py-3">
                                                    @if($tx->is_insidentil)
                                                        <span class="font-semibold text-gray-800">{{ $tx->peminjam_insidentil }}</span>
                                                        <span class="block text-[9px] text-gray-400">Insidentil (Non-Mahasiswa)</span>
                                                    @else
                                                        <span class="font-semibold text-gray-800">{{ $tx->user ? $tx->user->name : \'Akun Dihapus\' }}</span>
                                                        <span class="block text-[9px] text-gray-400">NIM: {{ $tx->user ? $tx->user->nomor_induk : \'-\' }}</span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 text-gray-500">{{ $tx->tanggal_pengajuan->format(\'d M Y, H:i\') }}</td>
                                                <td class="px-4 py-3">
                                                    <ul class="list-disc list-inside space-y-0.5 text-[10px]">
                                                        @foreach($tx->details as $d)
                                                            <li>{{ $d->item->nama_barang }} ({{ $d->jumlah_diminta }} {{ $d->item->satuan }})</li>
                                                        @endforeach
                                                    </ul>
                                                </td>
                                                <td class="px-4 py-3 text-center">
                                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase {{ $tx->status === \'selesai\' ? \'bg-green-50 text-green-750\' : ($tx->status === \'disetujui\' ? \'bg-blue-50 text-blue-700\' : \'bg-amber-50 text-amber-700\') }}">
                                                        {{ $tx->status }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 text-center">
                                                    <form method="POST" action="/superadmin/transactions/{{ $tx->id }}" class="inline" onsubmit="return confirm(\'Hapus riwayat transaksi ini secara permanen?\')">
                                                        @csrf
                                                        @method(\'DELETE\')
                                                        <button type="submit" class="bg-red-50 text-red-600 border border-red-150 px-2.5 py-1 rounded text-[10px] font-bold hover:bg-red-100 transition duration-150">Hapus Log</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="px-4 py-8 text-center text-gray-400">Tidak ada riwayat permintaan bahan ditemukan.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div x-show="historyTab === \'ruangan\'" x-transition style="display:none;">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 text-xs">
                                    <thead class="bg-gray-50 text-gray-500 font-semibold uppercase">
                                        <tr>
                                            <th class="px-4 py-3 text-left">ID Booking</th>
                                            <th class="px-4 py-3 text-left">Peminjam</th>
                                            <th class="px-4 py-3 text-left">Ruangan</th>
                                            <th class="px-4 py-3 text-left">Waktu Penggunaan</th>
                                            <th class="px-4 py-3 text-center">Status</th>
                                            <th class="px-4 py-3 text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-150 bg-white">
                                        @forelse($allRoomBookingsForDeletion as $rb)
                                            @php
                                                $borrowerName = $rb->is_insidentil ? $rb->peminjam_insidentil : ($rb->user ? $rb->user->name : \'Akun Dihapus\');
                                                $rbCode = \'RM-\' . str_pad($rb->id, 5, \'0\', STR_PAD_LEFT);
                                            @endphp
                                            <tr x-show="searchHistory === \'\' || \'{{ strtolower($rbCode) }}\'.includes(searchHistory.toLowerCase()) || \'{{ strtolower(addslashes($borrowerName)) }}\'.includes(searchHistory.toLowerCase())" class="hover:bg-gray-50/50 transition-colors">
                                                <td class="px-4 py-3 font-bold text-teal-700">{{ $rbCode }}</td>
                                                <td class="px-4 py-3">
                                                    @if($rb->is_insidentil)
                                                        <span class="font-semibold text-gray-800">{{ $rb->peminjam_insidentil }}</span>
                                                        <span class="block text-[9px] text-gray-400">Insidentil (Non-Mahasiswa)</span>
                                                    @else
                                                        <span class="font-semibold text-gray-800">{{ $rb->user ? $rb->user->name : \'Akun Dihapus\' }}</span>
                                                        <span class="block text-[9px] text-gray-400">NIM: {{ $rb->user ? $rb->user->nomor_induk : \'-\' }}</span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3">
                                                    <ul class="list-disc list-inside space-y-0.5 text-[10px]">
                                                        @foreach($rb->items as $item)
                                                            <li>{{ $item->room->nama_ruangan }} ({{ $item->room->kode_ruangan }})</li>
                                                        @endforeach
                                                    </ul>
                                                </td>
                                                <td class="px-4 py-3 text-gray-500">
                                                    {{ \\Carbon\\Carbon::parse($rb->tanggal_mulai)->format(\'d M Y\') }} s/d {{ \\Carbon\\Carbon::parse($rb->tanggal_selesai)->format(\'d M Y\') }}<br>
                                                    {{ \\Carbon\\Carbon::parse($rb->waktu_mulai)->format(\'H:i\') }} - {{ \\Carbon\\Carbon::parse($rb->waktu_selesai)->format(\'H:i\') }}
                                                </td>
                                                <td class="px-4 py-3 text-center">
                                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase {{ $rb->status === \'selesai\' ? \'bg-green-50 text-green-750\' : ($rb->status === \'disetujui\' ? \'bg-blue-50 text-blue-700\' : \'bg-amber-50 text-amber-700\') }}">
                                                        {{ $rb->status }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 text-center">
                                                    <form method="POST" action="{{ route(\'superadmin.room-bookings.destroy\', $rb->id) }}" class="inline" onsubmit="return confirm(\'Hapus riwayat peminjaman ruangan ini secara permanen?\')">
                                                        @csrf
                                                        @method(\'DELETE\')
                                                        <button type="submit" class="bg-red-50 text-red-600 border border-red-150 px-2.5 py-1 rounded text-[10px] font-bold hover:bg-red-100 transition duration-150">Hapus Log</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="px-4 py-8 text-center text-gray-400">Tidak ada riwayat peminjaman ruangan ditemukan.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
';

$html = substr_replace($html, $insert, $afterTableEnd, 0);

file_put_contents($file, $html);
echo "Added missing tables back.\n";
