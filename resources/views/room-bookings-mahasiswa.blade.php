<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peminjaman Ruangan — Silab Terpadu</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 min-h-screen font-sans antialiased" x-data="roomBookingApp()">

{{-- NAVBAR --}}
<nav class="bg-white border-b border-gray-100 shadow-sm sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2 text-teal-700 hover:text-teal-900 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                <span class="text-sm font-semibold">Dashboard</span>
            </a>
            <span class="text-gray-300">/</span>
            <span class="text-sm font-bold text-gray-800">Peminjaman Ruangan</span>
        </div>
        <div class="text-xs text-gray-500 font-semibold">{{ Auth::user()->name }}</div>
    </div>
</nav>

<div class="max-w-7xl mx-auto px-4 py-6">

    {{-- FLASH MESSAGES --}}
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm font-semibold flex items-center gap-2">
            <svg class="w-4 h-4 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if($errors->has('rooms'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm font-semibold">
            {{ $errors->first('rooms') }}
        </div>
    @endif

    <div class="flex gap-6">
        {{-- SIDEBAR --}}
        <div class="w-full md:w-64 flex-shrink-0 hidden md:block">
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 sticky top-4">
                <div class="flex flex-col items-center mb-3 pb-3 border-b border-gray-100">
                    <img src="{{ asset('images/logo_poltekkes.png') }}" class="w-14 h-14 object-contain mb-2" alt="Logo">
                    <span class="text-xs font-bold text-teal-800 text-center">Silab Terpadu</span>
                    <span class="text-[9px] text-gray-400 uppercase tracking-wider mt-0.5">Poltekkes Jakarta I</span>
                </div>
                <nav class="space-y-1.5 pt-1 text-xs">
                    <a href="{{ route('dashboard') }}" class="w-full text-left px-3 py-2.5 rounded-lg text-xs transition-all duration-150 flex items-center gap-2.5 shadow-sm bg-white text-gray-700 hover:bg-slate-50 border border-slate-200 font-medium">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        Dashboard
                    </a>
                    <a href="{{ route('room-bookings.index') }}" class="w-full text-left px-3 py-2.5 rounded-lg text-xs transition-all duration-150 flex items-center gap-2.5 shadow-md bg-teal-600 text-white font-semibold ring-1 ring-teal-500">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        Peminjaman Ruangan
                    </a>
                </nav>
            </div>
        </div>

        {{-- MAIN CONTENT --}}
        <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Peminjaman Ruangan</h1>
                    <p class="text-xs text-gray-500 mt-1">Pilih ruangan yang ingin dipinjam dan atur jadwal penggunaan.</p>
                </div>
                <button @click="showBookingForm = !showBookingForm"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-teal-700 hover:bg-teal-800 text-white rounded-lg text-sm font-semibold shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Buat Peminjaman
                </button>
            </div>

            {{-- BOOKING FORM --}}
            <div x-show="showBookingForm" x-transition class="bg-white border border-gray-200 rounded-xl shadow-sm mb-6 overflow-hidden" style="display:none;">
                <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-teal-50 to-white flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-gray-900">Form Peminjaman Ruangan</h2>
                        <p class="text-xs text-gray-500">Dapat memilih lebih dari satu ruangan dengan jadwal berbeda-beda.</p>
                    </div>
                    <button @click="showBookingForm = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form method="POST" action="{{ route('room-bookings.store') }}" class="p-6 space-y-5">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-gray-700 mb-1">Tujuan Penggunaan Ruangan <span class="text-red-500">*</span></label>
                            <textarea name="tujuan_penggunaan" rows="2" required placeholder="Contoh: Praktikum Keperawatan Dasar Semester 4" class="w-full text-sm border-gray-300 rounded-lg focus:ring-teal-500 focus:border-teal-500 py-2 px-3">{{ old('tujuan_penggunaan') }}</textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Jumlah Mahasiswa <span class="text-red-500">*</span></label>
                            <input type="number" name="jumlah_mahasiswa" min="1" required value="{{ old('jumlah_mahasiswa', 1) }}" class="w-full text-sm border-gray-300 rounded-lg focus:ring-teal-500 py-2 px-3">
                        </div>
                    </div>

                    {{-- Dynamic Room List --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-xs font-bold text-gray-700">Daftar Ruangan yang Dipinjam <span class="text-red-500">*</span></label>
                            <button type="button" @click="addRoomRow()" class="text-xs text-teal-700 hover:text-teal-900 font-semibold flex items-center gap-1 border border-teal-300 rounded px-2 py-1 hover:bg-teal-50 transition">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Tambah Ruangan
                            </button>
                        </div>
                        <div class="space-y-3" id="room-rows">
                            <template x-for="(row, idx) in roomRows" :key="idx">
                                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <span class="text-xs font-bold text-teal-700">Ruangan #<span x-text="idx + 1"></span></span>
                                        <button type="button" x-if="roomRows.length > 1" @click="removeRoomRow(idx)" class="text-red-400 hover:text-red-600 text-xs font-semibold">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                        <div class="sm:col-span-2 lg:col-span-1">
                                            <label class="block text-xs font-semibold text-gray-600 mb-1">Pilih Ruangan <span class="text-red-500">*</span></label>
                                            <select :name="`rooms[${idx}][room_id]`" x-model="row.room_id" required @change="checkRoomAvailability(idx)" class="w-full text-sm border-gray-300 rounded-lg focus:ring-teal-500 py-1.5 px-2">
                                                <option value="">-- Pilih Ruangan --</option>
                                                @foreach($rooms as $room)
                                                <option value="{{ $room->id }}">{{ $room->nama_ruangan }} (Kap. {{ $room->kapasitas }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-600 mb-1">Tanggal Mulai <span class="text-red-500">*</span></label>
                                            <input type="date" :name="`rooms[${idx}][tanggal_mulai]`" x-model="row.tanggal_mulai" required @change="checkRoomAvailability(idx)" class="w-full text-sm border-gray-300 rounded-lg focus:ring-teal-500 py-1.5 px-2">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-600 mb-1">Tanggal Selesai <span class="text-red-500">*</span></label>
                                            <input type="date" :name="`rooms[${idx}][tanggal_selesai]`" x-model="row.tanggal_selesai" required @change="checkRoomAvailability(idx)" class="w-full text-sm border-gray-300 rounded-lg focus:ring-teal-500 py-1.5 px-2">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-600 mb-1">Waktu Mulai <span class="text-red-500">*</span></label>
                                            <input type="time" :name="`rooms[${idx}][waktu_mulai]`" x-model="row.waktu_mulai" required @change="checkRoomAvailability(idx)" class="w-full text-sm border-gray-300 rounded-lg focus:ring-teal-500 py-1.5 px-2">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-600 mb-1">Waktu Selesai <span class="text-red-500">*</span></label>
                                            <input type="time" :name="`rooms[${idx}][waktu_selesai]`" x-model="row.waktu_selesai" required @change="checkRoomAvailability(idx)" class="w-full text-sm border-gray-300 rounded-lg focus:ring-teal-500 py-1.5 px-2">
                                        </div>
                                    </div>
                                    {{-- Availability Indicator --}}
                                    <div x-show="row.availabilityChecked" class="mt-2 text-xs font-semibold flex items-center gap-1" :class="row.isAvailable ? 'text-green-700' : 'text-red-700'">
                                        <template x-if="row.isAvailable"><span>✓ Ruangan tersedia pada waktu ini</span></template>
                                        <template x-if="!row.isAvailable"><span>✗ Ruangan sudah dipesan pada waktu ini</span></template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
                        <button type="button" @click="showBookingForm = false" class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 font-medium">Batal</button>
                        <button type="submit" class="px-5 py-2 text-sm bg-teal-700 hover:bg-teal-800 text-white rounded-lg font-semibold shadow-sm transition">
                            Ajukan Peminjaman
                        </button>
                    </div>
                </form>
            </div>

            {{-- HORIZONTAL TAB: Ruangan Tersedia / Digunakan --}}
            <div class="flex border-b border-gray-200 mb-5 gap-1">
                <button @click="activeTab = 'tersedia'" :class="activeTab === 'tersedia' ? 'border-b-2 border-teal-700 text-teal-800 font-bold' : 'text-gray-500 hover:text-gray-700'" class="px-5 py-2.5 text-sm font-medium transition">
                    🏫 Ruangan Tersedia
                    <span class="ml-1 text-xs bg-teal-100 text-teal-800 px-1.5 py-0.5 rounded-full font-bold">{{ $availableRooms->count() }}</span>
                </button>
                <button @click="activeTab = 'digunakan'" :class="activeTab === 'digunakan' ? 'border-b-2 border-amber-500 text-amber-700 font-bold' : 'text-gray-500 hover:text-gray-700'" class="px-5 py-2.5 text-sm font-medium transition">
                    🔒 Ruangan Digunakan
                    <span class="ml-1 text-xs bg-amber-100 text-amber-800 px-1.5 py-0.5 rounded-full font-bold">{{ $usedRoomItems->count() }}</span>
                </button>
                <button @click="activeTab = 'riwayat'" :class="activeTab === 'riwayat' ? 'border-b-2 border-blue-500 text-blue-700 font-bold' : 'text-gray-500 hover:text-gray-700'" class="px-5 py-2.5 text-sm font-medium transition">
                    📋 Riwayat Saya
                    <span class="ml-1 text-xs bg-blue-100 text-blue-800 px-1.5 py-0.5 rounded-full font-bold">{{ $myBookings->count() }}</span>
                </button>
            </div>

            {{-- FILTER BAR --}}
            <div x-show="activeTab !== 'riwayat'" class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 mb-5">
                <form method="GET" action="{{ route('room-bookings.index') }}" class="flex flex-wrap gap-3 items-end">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Tanggal Mulai</label>
                        <input type="date" name="filter_tanggal_mulai" value="{{ $filterTanggalMulai }}" class="text-xs border-gray-300 rounded-lg focus:ring-teal-500 py-1.5 px-2">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Tanggal Selesai</label>
                        <input type="date" name="filter_tanggal_selesai" value="{{ $filterTanggalSelesai }}" class="text-xs border-gray-300 rounded-lg focus:ring-teal-500 py-1.5 px-2">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Waktu Mulai</label>
                        <input type="time" name="filter_waktu_mulai" value="{{ $filterWaktuMulai }}" class="text-xs border-gray-300 rounded-lg focus:ring-teal-500 py-1.5 px-2">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Waktu Selesai</label>
                        <input type="time" name="filter_waktu_selesai" value="{{ $filterWaktuSelesai }}" class="text-xs border-gray-300 rounded-lg focus:ring-teal-500 py-1.5 px-2">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="px-4 py-2 text-xs bg-teal-700 hover:bg-teal-800 text-white rounded-lg font-semibold transition">Filter</button>
                        <a href="{{ route('room-bookings.index') }}" class="px-3 py-2 text-xs border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 font-medium">Reset</a>
                    </div>
                </form>
            </div>

            {{-- TAB: RUANGAN TERSEDIA --}}
            <div x-show="activeTab === 'tersedia'" x-transition>
                @if($availableRooms->isEmpty())
                    <div class="bg-white rounded-xl border border-gray-200 py-16 text-center shadow-sm">
                        <div class="text-4xl mb-3">🏫</div>
                        <p class="text-gray-500 font-semibold">Tidak ada ruangan tersedia</p>
                        <p class="text-xs text-gray-400 mt-1">Coba filter tanggal lain atau hubungi laboran.</p>
                    </div>
                @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($availableRooms as $room)
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition overflow-hidden">
                        <div class="bg-gradient-to-br from-teal-600 to-teal-800 px-5 py-4 text-white">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-widest text-teal-200">{{ $room->kode_ruangan }}</p>
                                    <h3 class="text-sm font-bold mt-0.5">{{ $room->nama_ruangan }}</h3>
                                </div>
                                <span class="bg-teal-500/50 text-white text-xs px-2 py-0.5 rounded-full font-semibold">Tersedia</span>
                            </div>
                        </div>
                        <div class="px-5 py-4 space-y-2">
                            <div class="flex items-center gap-2 text-xs text-gray-600">
                                <svg class="w-3.5 h-3.5 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                Kapasitas: <strong>{{ $room->kapasitas }} orang</strong>
                            </div>
                            @if($room->lokasi)
                            <div class="flex items-center gap-2 text-xs text-gray-600">
                                <svg class="w-3.5 h-3.5 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                {{ $room->lokasi }}
                            </div>
                            @endif
                            @if($room->deskripsi)
                            <p class="text-xs text-gray-400 mt-1">{{ Str::limit($room->deskripsi, 70) }}</p>
                            @endif
                            <button type="button" @click="addRoomToForm({{ $room->id }}, '{{ addslashes($room->nama_ruangan) }}')"
                                class="mt-2 w-full text-xs py-1.5 px-3 bg-teal-50 hover:bg-teal-100 text-teal-800 rounded-lg font-semibold border border-teal-200 transition">
                                + Tambah ke Peminjaman
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- TAB: RUANGAN DIGUNAKAN --}}
            <div x-show="activeTab === 'digunakan'" x-transition style="display:none;">
                @if($usedRoomItems->isEmpty())
                    <div class="bg-white rounded-xl border border-gray-200 py-16 text-center shadow-sm">
                        <div class="text-4xl mb-3">✅</div>
                        <p class="text-gray-500 font-semibold">Tidak ada ruangan yang sedang digunakan</p>
                        <p class="text-xs text-gray-400 mt-1">Pada rentang waktu yang dipilih, semua ruangan tersedia.</p>
                    </div>
                @else
                <div class="space-y-3">
                    @foreach($usedRoomItems as $item)
                    <div class="bg-white rounded-xl border border-amber-200 shadow-sm overflow-hidden">
                        <div class="flex items-stretch">
                            <div class="bg-amber-500 w-1.5 flex-shrink-0"></div>
                            <div class="flex-1 px-5 py-4">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <p class="text-xs text-amber-700 font-bold uppercase">{{ $item->room?->kode_ruangan }}</p>
                                        <h3 class="text-sm font-bold text-gray-900">{{ $item->room?->nama_ruangan }}</h3>
                                        <p class="text-xs text-gray-500 mt-0.5">Kapasitas: {{ $item->room?->kapasitas }} orang &nbsp;|&nbsp; {{ $item->room?->lokasi }}</p>
                                    </div>
                                    <span class="text-xs px-2 py-0.5 rounded-full font-semibold {{ $item->booking->status === 'disetujui' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ strtoupper($item->booking->status) }}
                                    </span>
                                </div>
                                <div class="mt-2 flex flex-wrap gap-3 text-xs text-gray-600">
                                    <span>📅 {{ \Carbon\Carbon::parse($item->tanggal_mulai)->format('d-m-Y') }} s.d. {{ \Carbon\Carbon::parse($item->tanggal_selesai)->format('d-m-Y') }}</span>
                                    <span>⏰ {{ $item->waktu_mulai }} – {{ $item->waktu_selesai }}</span>
                                    <span>👤 {{ $item->booking->user?->name }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- TAB: RIWAYAT SAYA --}}
            <div x-show="activeTab === 'riwayat'" x-transition style="display:none;">
                @if($myBookings->isEmpty())
                    <div class="bg-white rounded-xl border border-gray-200 py-16 text-center shadow-sm">
                        <div class="text-4xl mb-3">📋</div>
                        <p class="text-gray-500 font-semibold">Belum ada riwayat peminjaman ruangan</p>
                    </div>
                @else
                <div class="space-y-4">
                    @foreach($myBookings as $booking)
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100 flex items-start justify-between">
                            <div>
                                <span class="text-xs font-bold text-gray-500">RB-{{ str_pad($booking->id, 5, '0', STR_PAD_LEFT) }}</span>
                                <p class="text-sm font-bold text-gray-900 mt-0.5">{{ $booking->tujuan_penggunaan }}</p>
                                <p class="text-xs text-gray-500">Diajukan: {{ $booking->tanggal_pengajuan->format('d-m-Y H:i') }} &nbsp;|&nbsp; {{ $booking->jumlah_mahasiswa }} mahasiswa</p>
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <span class="text-xs px-2 py-0.5 rounded-full font-semibold
                                    {{ $booking->status === 'disetujui' ? 'bg-green-100 text-green-800' :
                                       ($booking->status === 'ditolak' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                    {{ strtoupper($booking->status) }}
                                </span>
                                @if($booking->status === 'disetujui')
                                <a href="{{ route('room-bookings.pdf', $booking->id) }}?action=preview" target="_blank" class="text-xs px-2 py-1 bg-teal-50 text-teal-700 rounded border border-teal-200 font-semibold hover:bg-teal-100 transition">
                                    PDF
                                </a>
                                @endif
                                @if($booking->status === 'pending')
                                <form method="POST" action="{{ route('room-bookings.destroy', $booking->id) }}" onsubmit="return confirm('Batalkan peminjaman ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-xs px-2 py-1 bg-red-50 text-red-700 rounded border border-red-200 font-semibold hover:bg-red-100 transition">Batal</button>
                                </form>
                                @endif
                            </div>
                        </div>
                        <div class="px-5 py-3 space-y-2">
                            @foreach($booking->items as $item)
                            <div class="flex flex-wrap gap-2 text-xs text-gray-600 bg-gray-50 rounded-lg px-3 py-2">
                                <span class="font-semibold text-teal-800">{{ $item->room?->nama_ruangan ?? '-' }}</span>
                                <span>📅 {{ \Carbon\Carbon::parse($item->tanggal_mulai)->format('d-m-Y') }} s.d. {{ \Carbon\Carbon::parse($item->tanggal_selesai)->format('d-m-Y') }}</span>
                                <span>⏰ {{ $item->waktu_mulai }} – {{ $item->waktu_selesai }}</span>
                            </div>
                            @endforeach
                            @if($booking->catatan_laboran)
                            <p class="text-xs text-red-600 font-semibold italic px-1">Catatan Laboran: {{ $booking->catatan_laboran }}</p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

        </div>{{-- End main content --}}
    </div>{{-- End flex --}}
</div>{{-- End container --}}

<script>
function roomBookingApp() {
    return {
        activeTab: 'tersedia',
        showBookingForm: {{ $errors->has('rooms') || old('tujuan_penggunaan') ? 'true' : 'false' }},
        roomRows: [{ room_id: '', tanggal_mulai: '', tanggal_selesai: '', waktu_mulai: '', waktu_selesai: '', availabilityChecked: false, isAvailable: true }],

        addRoomRow() {
            this.roomRows.push({ room_id: '', tanggal_mulai: '', tanggal_selesai: '', waktu_mulai: '', waktu_selesai: '', availabilityChecked: false, isAvailable: true });
        },

        removeRoomRow(idx) {
            if (this.roomRows.length > 1) this.roomRows.splice(idx, 1);
        },

        addRoomToForm(roomId, roomName) {
            this.showBookingForm = true;
            // Find an empty slot or add new
            const empty = this.roomRows.findIndex(r => !r.room_id);
            if (empty >= 0) {
                this.roomRows[empty].room_id = roomId;
            } else {
                this.roomRows.push({ room_id: roomId, tanggal_mulai: '', tanggal_selesai: '', waktu_mulai: '', waktu_selesai: '', availabilityChecked: false, isAvailable: true });
            }
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        checkRoomAvailability(idx) {
            const row = this.roomRows[idx];
            if (!row.room_id || !row.tanggal_mulai || !row.tanggal_selesai || !row.waktu_mulai || !row.waktu_selesai) return;

            fetch(`{{ route('room-bookings.check') }}?room_id=${row.room_id}&tanggal_mulai=${row.tanggal_mulai}&tanggal_selesai=${row.tanggal_selesai}&waktu_mulai=${row.waktu_mulai}&waktu_selesai=${row.waktu_selesai}`)
                .then(r => r.json())
                .then(data => {
                    this.roomRows[idx].isAvailable = data.available;
                    this.roomRows[idx].availabilityChecked = true;
                });
        }
    }
}
</script>
</body>
</html>
