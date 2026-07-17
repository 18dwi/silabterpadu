<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Peminjaman Ruangan — Silab Terpadu</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 min-h-screen font-sans antialiased" x-data="roomLaboranApp()">

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
    @if($errors->any())
    <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm font-semibold">
        {{ $errors->first() }}
    </div>
    @endif

    <div class="flex flex-col md:flex-row gap-8">
        {{-- SIDEBAR VERTICAL (Left Side) --}}
        <div class="w-full md:w-64 flex-shrink-0">
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5 flex flex-col items-center text-center space-y-4 sticky top-6">
                <!-- Logo & Title Area -->
                <div class="flex flex-col items-center py-2">
                    <img src="{{ asset('images/logo_poltekkes.png') }}" class="w-20 h-20 object-contain mb-3 bg-teal-50/10 p-1.5 rounded-full border border-teal-100/50 shadow-sm" alt="Logo Poltekkes">
                    <h3 class="text-sm font-bold text-teal-800 tracking-wide leading-tight">Si-Lab Keperawatan</h3>
                    <p class="text-[9px] text-gray-400 font-bold uppercase tracking-widest mt-1.5">Poltekkes Jakarta I</p>
                </div>

                <!-- Sidebar Main Menus (Vertical Options) -->
                <div class="w-full space-y-2.5 pt-4 border-t border-gray-100">
                    <!-- Inventaris Alat -->
                    <a href="{{ route('dashboard') }}?tab=inventaris_alat" class="w-full text-left px-3 py-2.5 rounded-lg text-xs font-semibold transition-all duration-150 flex items-center gap-2.5 shadow-sm bg-white text-gray-700 hover:bg-slate-50 border border-slate-200">
                        <svg class="h-4.5 w-4.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        Inventaris Alat
                    </a>
                    <!-- Inventaris Bahan -->
                    <a href="{{ route('dashboard') }}?tab=inventaris_bahan" class="w-full text-left px-3 py-2.5 rounded-lg text-xs font-semibold transition-all duration-150 flex items-center gap-2.5 shadow-sm bg-white text-gray-700 hover:bg-slate-50 border border-slate-200">
                        <svg class="h-4.5 w-4.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                        </svg>
                        Inventaris Bahan
                    </a>
                    <!-- Paket Praktikum -->
                    <a href="{{ route('dashboard') }}?tab=paket_list" class="w-full text-left px-3 py-2.5 rounded-lg text-xs font-semibold transition-all duration-150 flex items-center gap-2.5 shadow-sm bg-white text-gray-700 hover:bg-slate-50 border border-slate-200">
                        <svg class="h-4.5 w-4.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        Paket Praktikum
                    </a>
                    <!-- Peminjaman & Permintaan -->
                    <a href="{{ route('dashboard') }}?tab=verifikasi" class="w-full text-left px-3 py-2.5 rounded-lg text-xs font-semibold transition-all duration-150 flex items-center gap-2.5 shadow-sm bg-white text-gray-700 hover:bg-slate-50 border border-slate-200">
                        <svg class="h-4.5 w-4.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Peminjaman & Permintaan
                    </a>
                    <!-- Bebas Laboratorium -->
                    <a href="{{ route('dashboard') }}?tab=bebas_lab" class="w-full text-left px-3 py-2.5 rounded-lg text-xs font-semibold transition-all duration-150 flex items-center gap-2.5 shadow-sm bg-white text-gray-700 hover:bg-slate-50 border border-slate-200">
                        <svg class="h-4.5 w-4.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
                        </svg>
                        Bebas Laboratorium
                    </a>
                    <!-- Peminjaman Ruangan (Active) -->
                    <a href="{{ route('laboran.room-bookings.index') }}" class="w-full text-left px-3 py-2.5 rounded-lg text-xs font-semibold transition-all duration-150 flex items-center gap-2.5 shadow-md bg-teal-600 text-white ring-1 ring-teal-500">
                        <svg class="h-4.5 w-4.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        Peminjaman Ruangan
                    </a>
                </div>
            </div>
        </div>

        {{-- MAIN CONTENT AREA (Right Side) --}}
        <div class="flex-grow min-w-0">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Peminjaman Ruangan</h1>
                    <p class="text-xs text-gray-500 mt-1">Verifikasi permohonan, pantau jadwal penggunaan, dan kelola ruangan.</p>
                </div>
                <div class="flex items-center gap-2">
                    {{-- Tambah Pinjam Khusus Button --}}
                    <button @click="showAddInsidentil = true" class="inline-flex items-center gap-2 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-xs font-semibold shadow-sm transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Tambah Pinjam Khusus
                    </button>
                </div>
            </div>

            {{-- 3 PRIMARY HORIZONTAL TABS --}}
            <div class="flex border-b border-gray-200 mb-6 gap-1 overflow-x-auto">
                <button @click="activeTab = 'manajemen'" :class="activeTab === 'manajemen' ? 'border-b-2 border-teal-700 text-teal-800 font-bold' : 'text-gray-500 hover:text-gray-700'" class="px-5 py-2.5 text-sm font-medium transition whitespace-nowrap">
                    ⚙️ Manajemen Peminjaman Ruangan
                </button>
                <button @click="activeTab = 'digunakan'" :class="activeTab === 'digunakan' ? 'border-b-2 border-amber-600 text-amber-700 font-bold' : 'text-gray-500 hover:text-gray-700'" class="px-5 py-2.5 text-sm font-medium transition whitespace-nowrap">
                    🔒 Ruangan Sedang Digunakan
                    <span class="ml-1 text-xs bg-amber-100 text-amber-800 px-1.5 py-0.5 rounded-full font-bold">{{ $usedRoomItems->count() }}</span>
                </button>
                <button @click="activeTab = 'tersedia'" :class="activeTab === 'tersedia' ? 'border-b-2 border-green-600 text-green-700 font-bold' : 'text-gray-500 hover:text-gray-700'" class="px-5 py-2.5 text-sm font-medium transition whitespace-nowrap">
                    🏫 Ruangan Tersedia
                    <span class="ml-1 text-xs bg-green-100 text-green-800 px-1.5 py-0.5 rounded-full font-bold">{{ $availableRooms->count() }}</span>
                </button>
            </div>

            {{-- CONTENT: TAB MANAJEMEN --}}
            <div x-show="activeTab === 'manajemen'" x-transition>
                
                {{-- 5 SUB-TABS (Horizontal Menus under Manajemen) --}}
                <div class="flex flex-wrap gap-2 bg-gray-200/40 p-1.5 rounded-lg border border-gray-200 mb-6">
                    <button @click="activeSubTab = 'verifikasi'" :class="activeSubTab === 'verifikasi' ? 'bg-teal-600 text-white font-semibold shadow-sm' : 'bg-white text-gray-700 hover:bg-slate-50 border border-slate-200'" class="px-3 py-2 rounded-md text-xs transition-all duration-150 flex items-center gap-1.5">
                        ⏳ Verifikasi Pending
                        <span class="text-[9px] bg-red-100 text-red-800 px-1.5 py-0.5 rounded-full font-bold">{{ $pendingBookings->count() }}</span>
                    </button>
                    <button @click="activeSubTab = 'disetujui'" :class="activeSubTab === 'disetujui' ? 'bg-teal-600 text-white font-semibold shadow-sm' : 'bg-white text-gray-700 hover:bg-slate-50 border border-slate-200'" class="px-3 py-2 rounded-md text-xs transition-all duration-150 flex items-center gap-1.5">
                        ✅ Ruangan Disetujui (Aktif)
                        <span class="text-[9px] bg-green-100 text-green-800 px-1.5 py-0.5 rounded-full font-bold">{{ $approvedBookings->count() }}</span>
                    </button>
                    <button @click="activeSubTab = 'ditolak'" :class="activeSubTab === 'ditolak' ? 'bg-teal-600 text-white font-semibold shadow-sm' : 'bg-white text-gray-700 hover:bg-slate-50 border border-slate-200'" class="px-3 py-2 rounded-md text-xs transition-all duration-150 flex items-center gap-1.5">
                        ✗ Ditolak / Ditangguhkan
                        <span class="text-[9px] bg-gray-100 text-gray-800 px-1.5 py-0.5 rounded-full font-bold">{{ $rejectedBookings->count() }}</span>
                    </button>
                    <button @click="activeSubTab = 'riwayat'" :class="activeSubTab === 'riwayat' ? 'bg-teal-600 text-white font-semibold shadow-sm' : 'bg-white text-gray-700 hover:bg-slate-50 border border-slate-200'" class="px-3 py-2 rounded-md text-xs transition-all duration-150 flex items-center gap-1.5">
                        📋 Riwayat Peminjaman Ruangan
                        <span class="text-[9px] bg-blue-100 text-blue-800 px-1.5 py-0.5 rounded-full font-bold">{{ $historyBookings->count() }}</span>
                    </button>
                    <button @click="activeSubTab = 'master'" :class="activeSubTab === 'master' ? 'bg-teal-600 text-white font-semibold shadow-sm' : 'bg-white text-gray-700 hover:bg-slate-50 border border-slate-200'" class="px-3 py-2 rounded-md text-xs transition-all duration-150 flex items-center gap-1.5">
                        🏢 Master Ruangan
                        <span class="text-[9px] bg-purple-100 text-purple-800 px-1.5 py-0.5 rounded-full font-bold">{{ $rooms->count() }}</span>
                    </button>
                </div>

                {{-- SUB-TAB: VERIFIKASI PENDING --}}
                <div x-show="activeSubTab === 'verifikasi'" x-transition>
                    @if($pendingBookings->isEmpty())
                        <div class="bg-white rounded-xl border border-gray-200 py-16 text-center shadow-sm">
                            <div class="text-4xl mb-3">✅</div>
                            <p class="text-gray-500 font-semibold">Tidak ada permohonan yang menunggu verifikasi</p>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($pendingBookings as $booking)
                            <div class="bg-white rounded-xl border border-yellow-200 shadow-sm overflow-hidden">
                                <div class="px-5 py-4 border-b border-gray-100 flex items-start justify-between flex-wrap gap-2">
                                    <div>
                                        <span class="text-xs text-gray-500 font-bold">RB-{{ str_pad($booking->id, 5, '0', STR_PAD_LEFT) }}</span>
                                        <h3 class="text-sm font-bold text-gray-900 mt-0.5">{{ $booking->tujuan_penggunaan }}</h3>
                                        <p class="text-xs text-gray-500">
                                            Peminjam: <strong>{{ $booking->is_insidentil ? $booking->peminjam_insidentil : ($booking->user?->name ?? 'Mahasiswa') }}</strong>
                                            @if(!$booking->is_insidentil && $booking->user?->nomor_induk) ({{ $booking->user?->nomor_induk }}) @endif
                                            @if($booking->is_insidentil) (Institusi: {{ $booking->institusi_insidentil ?? '-' }}) @endif
                                            &nbsp;|&nbsp; {{ $booking->jumlah_mahasiswa }} orang
                                            &nbsp;|&nbsp; Diajukan: {{ $booking->tanggal_pengajuan->format('d-m-Y H:i') }}
                                        </p>
                                    </div>
                                    <span class="text-xs bg-yellow-100 text-yellow-800 px-2 py-0.5 rounded-full font-bold flex-shrink-0">PENDING VERIFIKASI</span>
                                </div>
                                <div class="px-5 py-3 space-y-2">
                                    @foreach($booking->items as $item)
                                    <div class="flex flex-wrap gap-2 text-xs text-gray-600 bg-gray-50 rounded-lg px-3 py-2">
                                        <span class="font-semibold text-teal-800">{{ $item->room?->nama_ruangan ?? 'Ruangan Terhapus' }}</span>
                                        <span>📅 {{ \Carbon\Carbon::parse($item->tanggal_mulai)->format('d-m-Y') }} s.d. {{ \Carbon\Carbon::parse($item->tanggal_selesai)->format('d-m-Y') }}</span>
                                        <span>⏰ {{ substr($item->waktu_mulai, 0, 5) }} – {{ substr($item->waktu_selesai, 0, 5) }}</span>
                                    </div>
                                    @endforeach
                                </div>
                                <div class="px-5 py-3 border-t border-gray-100 flex items-center justify-between flex-wrap gap-2">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        {{-- Approve Form --}}
                                        <form method="POST" action="{{ route('room-bookings.approve', $booking->id) }}" onsubmit="return confirm('Setujui peminjaman ini?')">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold shadow-sm transition">
                                                Setujui
                                            </button>
                                        </form>
                                        {{-- Edit Button --}}
                                        <button @click="openEditModal({{ $booking->id }}, {{ $booking->jumlah_mahasiswa }}, {{ json_encode($booking->tujuan_penggunaan) }}, {{ $booking->items->map(fn($i) => ['room_id' => $i->room_id, 'tanggal_mulai' => $i->tanggal_mulai->format('Y-m-d'), 'tanggal_selesai' => $i->tanggal_selesai->format('Y-m-d'), 'waktu_mulai' => substr($i->waktu_mulai, 0, 5), 'waktu_selesai' => substr($i->waktu_selesai, 0, 5)]) }})"
                                            class="inline-flex items-center gap-1.5 px-3 py-2 text-xs border border-teal-300 text-teal-700 rounded-lg font-semibold hover:bg-teal-50 transition">
                                            Edit
                                        </button>
                                        {{-- Tolak Button --}}
                                        <button @click="openRejectModal({{ $booking->id }})" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs border border-red-300 text-red-700 rounded-lg font-semibold hover:bg-red-50 transition">
                                            Tolak
                                        </button>
                                    </div>
                                    {{-- Hapus (Delete) Form --}}
                                    <form method="POST" action="{{ route('laboran.room-bookings.destroy', $booking->id) }}" onsubmit="return confirm('Hapus peminjaman ruangan yang diajukan ini secara permanen?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs text-red-600 hover:text-red-800 font-semibold transition">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- SUB-TAB: RUANGAN DISETUJUI --}}
                <div x-show="activeSubTab === 'disetujui'" x-transition style="display:none;">
                    @if($approvedBookings->isEmpty())
                        <div class="bg-white rounded-xl border border-gray-200 py-16 text-center shadow-sm">
                            <div class="text-4xl mb-3">✅</div>
                            <p class="text-gray-500 font-semibold">Tidak ada peminjaman ruangan aktif yang disetujui</p>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($approvedBookings as $booking)
                            <div class="bg-white rounded-xl border border-green-200 shadow-sm overflow-hidden">
                                <div class="px-5 py-4 border-b border-gray-100 flex items-start justify-between flex-wrap gap-2">
                                    <div>
                                        <span class="text-xs text-gray-500 font-bold">RB-{{ str_pad($booking->id, 5, '0', STR_PAD_LEFT) }}</span>
                                        <h3 class="text-sm font-bold text-gray-900 mt-0.5">{{ $booking->tujuan_penggunaan }}</h3>
                                        <p class="text-xs text-gray-500">
                                            Peminjam: <strong>{{ $booking->is_insidentil ? $booking->peminjam_insidentil : ($booking->user?->name ?? 'Mahasiswa') }}</strong>
                                            @if(!$booking->is_insidentil && $booking->user?->nomor_induk) ({{ $booking->user?->nomor_induk }}) @endif
                                            @if($booking->is_insidentil) (Institusi: {{ $booking->institusi_insidentil ?? '-' }}) @endif
                                            &nbsp;|&nbsp; {{ $booking->jumlah_mahasiswa }} orang
                                            &nbsp;|&nbsp; Disetujui oleh: {{ $booking->laboran?->name ?? '-' }}
                                        </p>
                                    </div>
                                    <span class="text-xs bg-green-100 text-green-800 px-2 py-0.5 rounded-full font-bold flex-shrink-0">DISETUJUI</span>
                                </div>
                                <div class="px-5 py-3 space-y-2">
                                    @foreach($booking->items as $item)
                                    <div class="flex flex-wrap gap-2 text-xs text-gray-600 bg-gray-50 rounded-lg px-3 py-2">
                                        <span class="font-semibold text-teal-800">{{ $item->room?->nama_ruangan ?? 'Ruangan Terhapus' }}</span>
                                        <span>📅 {{ \Carbon\Carbon::parse($item->tanggal_mulai)->format('d-m-Y') }} s.d. {{ \Carbon\Carbon::parse($item->tanggal_selesai)->format('d-m-Y') }}</span>
                                        <span>⏰ {{ substr($item->waktu_mulai, 0, 5) }} – {{ substr($item->waktu_selesai, 0, 5) }}</span>
                                    </div>
                                    @endforeach
                                </div>
                                <div class="px-5 py-3 border-t border-gray-100 flex items-center justify-between flex-wrap gap-2">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        {{-- Edit Button --}}
                                        <button @click="openEditModal({{ $booking->id }}, {{ $booking->jumlah_mahasiswa }}, {{ json_encode($booking->tujuan_penggunaan) }}, {{ $booking->items->map(fn($i) => ['room_id' => $i->room_id, 'tanggal_mulai' => $i->tanggal_mulai->format('Y-m-d'), 'tanggal_selesai' => $i->tanggal_selesai->format('Y-m-d'), 'waktu_mulai' => substr($i->waktu_mulai, 0, 5), 'waktu_selesai' => substr($i->waktu_selesai, 0, 5)]) }})"
                                            class="inline-flex items-center gap-1.5 px-3 py-2 text-xs border border-teal-300 text-teal-700 rounded-lg font-semibold hover:bg-teal-50 transition">
                                            Edit
                                        </button>
                                        {{-- Cetak PDF --}}
                                        <a href="{{ route('room-bookings.pdf', $booking->id) }}?action=preview" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs border border-teal-300 text-teal-700 rounded-lg font-semibold hover:bg-teal-50 transition">
                                            Cetak PDF
                                        </a>
                                    </div>
                                    {{-- Hapus (Delete) Form --}}
                                    <form method="POST" action="{{ route('laboran.room-bookings.destroy', $booking->id) }}" onsubmit="return confirm('Hapus peminjaman ruangan yang disetujui ini secara permanen?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs text-red-600 hover:text-red-800 font-semibold transition">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- SUB-TAB: DITOLAK / DITANGGUHKAN --}}
                <div x-show="activeSubTab === 'ditolak'" x-transition style="display:none;">
                    @if($rejectedBookings->isEmpty())
                        <div class="bg-white rounded-xl border border-gray-200 py-16 text-center shadow-sm">
                            <p class="text-gray-500 font-semibold">Tidak ada peminjaman ruangan yang ditolak atau ditangguhkan</p>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($rejectedBookings as $booking)
                            <div class="bg-white rounded-xl border border-red-200 shadow-sm overflow-hidden">
                                <div class="px-5 py-4 border-b border-gray-100 flex items-start justify-between flex-wrap gap-2">
                                    <div>
                                        <span class="text-xs text-gray-500 font-bold">RB-{{ str_pad($booking->id, 5, '0', STR_PAD_LEFT) }}</span>
                                        <h3 class="text-sm font-bold text-gray-900 mt-0.5">{{ $booking->tujuan_penggunaan }}</h3>
                                        <p class="text-xs text-gray-500">
                                            Peminjam: <strong>{{ $booking->is_insidentil ? $booking->peminjam_insidentil : ($booking->user?->name ?? 'Mahasiswa') }}</strong>
                                            &nbsp;|&nbsp; Ditolak/Ditangguhkan oleh: {{ $booking->laboran?->name ?? '-' }}
                                        </p>
                                        @if($booking->catatan_laboran)
                                        <p class="text-xs text-red-600 font-semibold italic mt-1.5">Alasan: {{ $booking->catatan_laboran }}</p>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs bg-red-100 text-red-800 px-2 py-0.5 rounded-full font-bold uppercase">{{ $booking->status }}</span>
                                        <a href="{{ route('room-bookings.pdf', $booking->id) }}?action=preview" target="_blank" class="text-xs px-2 py-1 bg-teal-50 text-teal-700 rounded border border-teal-200 font-semibold hover:bg-teal-100 transition">
                                            PDF
                                        </a>
                                    </div>
                                </div>
                                <div class="px-5 py-3 space-y-2">
                                    @foreach($booking->items as $item)
                                    <div class="flex flex-wrap gap-2 text-xs text-gray-600 bg-gray-50 rounded-lg px-3 py-2">
                                        <span class="font-semibold text-teal-800">{{ $item->room?->nama_ruangan ?? 'Ruangan Terhapus' }}</span>
                                        <span>📅 {{ \Carbon\Carbon::parse($item->tanggal_mulai)->format('d-m-Y') }} s.d. {{ \Carbon\Carbon::parse($item->tanggal_selesai)->format('d-m-Y') }}</span>
                                        <span>⏰ {{ substr($item->waktu_mulai, 0, 5) }} – {{ substr($item->waktu_selesai, 0, 5) }}</span>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- SUB-TAB: RIWAYAT PEMINJAMAN RUANGAN --}}
                <div x-show="activeSubTab === 'riwayat'" x-transition style="display:none;">
                    @if($historyBookings->isEmpty())
                        <div class="bg-white rounded-xl border border-gray-200 py-16 text-center shadow-sm">
                            <p class="text-gray-500 font-semibold">Belum ada riwayat peminjaman ruangan (selesai)</p>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($historyBookings as $booking)
                            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                                <div class="px-5 py-4 border-b border-gray-100 flex items-start justify-between flex-wrap gap-2">
                                    <div>
                                        <span class="text-xs text-gray-500 font-bold">RB-{{ str_pad($booking->id, 5, '0', STR_PAD_LEFT) }}</span>
                                        <h3 class="text-sm font-bold text-gray-900 mt-0.5">{{ $booking->tujuan_penggunaan }}</h3>
                                        <p class="text-xs text-gray-500">
                                            Peminjam: <strong>{{ $booking->is_insidentil ? $booking->peminjam_insidentil : ($booking->user?->name ?? 'Mahasiswa') }}</strong>
                                            &nbsp;|&nbsp; {{ $booking->jumlah_mahasiswa }} orang
                                            &nbsp;|&nbsp; Disetujui oleh: {{ $booking->laboran?->name ?? '-' }}
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs bg-gray-100 text-gray-800 px-2 py-0.5 rounded-full font-bold">SELESAI (RIWAYAT)</span>
                                        <a href="{{ route('room-bookings.pdf', $booking->id) }}?action=preview" target="_blank" class="text-xs px-2 py-1 bg-teal-50 text-teal-700 rounded border border-teal-200 font-semibold hover:bg-teal-100 transition">
                                            PDF
                                        </a>
                                    </div>
                                </div>
                                <div class="px-5 py-3 space-y-2">
                                    @foreach($booking->items as $item)
                                    <div class="flex flex-wrap gap-2 text-xs text-gray-600 bg-gray-50 rounded-lg px-3 py-2">
                                        <span class="font-semibold text-teal-800">{{ $item->room?->nama_ruangan ?? 'Ruangan Terhapus' }}</span>
                                        <span>📅 {{ \Carbon\Carbon::parse($item->tanggal_mulai)->format('d-m-Y') }} s.d. {{ \Carbon\Carbon::parse($item->tanggal_selesai)->format('d-m-Y') }}</span>
                                        <span>⏰ {{ substr($item->waktu_mulai, 0, 5) }} – {{ substr($item->waktu_selesai, 0, 5) }}</span>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- SUB-TAB: MASTER RUANGAN --}}
                <div x-show="activeSubTab === 'master'" x-transition style="display:none;">
                    
                    {{-- Tambah Ruangan Form Card --}}
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6">
                        <h2 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-1.5">
                            🏢 Tambah Ruangan Baru
                        </h2>
                        <form method="POST" action="{{ route('rooms.store') }}" class="space-y-4">
                            @csrf
                            <input type="hidden" name="status" value="tersedia">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1">Kode Ruangan <span class="text-red-500">*</span></label>
                                    <input type="text" name="kode_ruangan" required placeholder="Contoh: LAB-A" class="w-full text-xs border-gray-300 rounded-lg focus:ring-teal-500 py-2 px-3">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1">Nama Ruangan <span class="text-red-500">*</span></label>
                                    <input type="text" name="nama_ruangan" required placeholder="Contoh: Lab Keperawatan Anak" class="w-full text-xs border-gray-300 rounded-lg focus:ring-teal-500 py-2 px-3">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1">Kapasitas (orang) <span class="text-red-500">*</span></label>
                                    <input type="number" name="kapasitas" min="1" required value="20" class="w-full text-xs border-gray-300 rounded-lg focus:ring-teal-500 py-2 px-3">
                                </div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1">Lokasi / Gedung (opsional)</label>
                                    <input type="text" name="lokasi" placeholder="Contoh: Gedung Rektorat Lantai 2" class="w-full text-xs border-gray-300 rounded-lg focus:ring-teal-500 py-2 px-3">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1">Deskripsi / Fasilitas (opsional)</label>
                                    <input type="text" name="deskripsi" placeholder="Contoh: AC, Proyektor, 10 Bed Pasien" class="w-full text-xs border-gray-300 rounded-lg focus:ring-teal-500 py-2 px-3">
                                </div>
                            </div>
                            <div class="flex justify-end pt-2">
                                <button type="submit" class="px-5 py-2 text-xs bg-teal-700 hover:bg-teal-800 text-white rounded-lg font-semibold shadow-sm transition">
                                    Simpan Ruangan
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- Rooms List Table --}}
                    @if($rooms->isEmpty())
                        <div class="bg-white rounded-xl border border-gray-200 py-16 text-center shadow-sm">
                            <p class="text-gray-500 font-semibold">Belum ada data ruangan</p>
                        </div>
                    @else
                        <div class="overflow-x-auto bg-white rounded-xl border border-gray-200 shadow-sm">
                            <table class="min-w-full text-sm">
                                <thead class="bg-gray-50 text-gray-600 text-xs uppercase font-bold border-b border-gray-200">
                                    <tr>
                                        <th class="px-4 py-3 text-left">Kode</th>
                                        <th class="px-4 py-3 text-left">Nama Ruangan</th>
                                        <th class="px-4 py-3 text-center">Kapasitas</th>
                                        <th class="px-4 py-3 text-left">Lokasi</th>
                                        <th class="px-4 py-3 text-center">Status</th>
                                        <th class="px-4 py-3 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($rooms as $room)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-4 py-3 font-bold text-teal-800 text-xs">{{ $room->kode_ruangan }}</td>
                                        <td class="px-4 py-3 font-semibold text-gray-900">{{ $room->nama_ruangan }}</td>
                                        <td class="px-4 py-3 text-center text-gray-600">{{ $room->kapasitas }} org</td>
                                        <td class="px-4 py-3 text-xs text-gray-500">{{ $room->lokasi ?? '-' }}</td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="text-xs px-2 py-0.5 rounded-full font-semibold {{ $room->status === 'tersedia' ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-600' }}">
                                                {{ ucfirst($room->status) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <div class="flex items-center justify-center gap-2">
                                                <button @click="openEditRoom({{ $room->id }}, '{{ addslashes($room->kode_ruangan) }}', '{{ addslashes($room->nama_ruangan) }}', {{ $room->kapasitas }}, '{{ addslashes($room->lokasi ?? '') }}', '{{ addslashes($room->deskripsi ?? '') }}', '{{ $room->status }}')"
                                                    class="text-xs px-2 py-1 border border-teal-300 text-teal-700 rounded font-semibold hover:bg-teal-50 transition">Edit</button>
                                                <form method="POST" action="{{ route('rooms.destroy', $room->id) }}" onsubmit="return confirm('Hapus ruangan ini? Semua data terkait peminjaman ruangan ini juga akan terhapus!')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="text-xs px-2 py-1 border border-red-300 text-red-600 rounded font-semibold hover:bg-red-50 transition">Hapus</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

            </div>

            {{-- CONTENT: TAB RUANGAN SEDANG DIGUNAKAN --}}
            <div x-show="activeTab === 'digunakan'" x-transition style="display:none;">
                {{-- FILTER BAR --}}
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 mb-6">
                    <form method="GET" action="{{ route('laboran.room-bookings.index') }}" class="flex flex-wrap gap-3 items-end">
                        <input type="hidden" name="active_tab" value="digunakan">
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
                            <a href="{{ route('laboran.room-bookings.index') }}" class="px-3 py-2 text-xs border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 font-medium">Reset</a>
                        </div>
                    </form>
                </div>

                @if($usedRoomItems->isEmpty())
                    <div class="bg-white rounded-xl border border-gray-200 py-16 text-center shadow-sm">
                        <div class="text-4xl mb-3">✅</div>
                        <p class="text-gray-500 font-semibold">Tidak ada ruangan yang sedang digunakan</p>
                        <p class="text-xs text-gray-400 mt-1">Pada rentang waktu yang dipilih, semua ruangan tersedia.</p>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach($usedRoomItems as $item)
                        <div class="bg-white rounded-xl border border-amber-200 shadow-sm overflow-hidden">
                            <div class="flex items-stretch">
                                <div class="bg-amber-500 w-1.5 flex-shrink-0"></div>
                                <div class="flex-1 px-5 py-4">
                                    <div class="flex items-start justify-between flex-wrap gap-2">
                                        <div>
                                            <span class="text-xs text-amber-700 font-bold uppercase">{{ $item->room?->kode_ruangan }}</span>
                                            <h3 class="text-sm font-bold text-gray-900 mt-0.5">{{ $item->room?->nama_ruangan }}</h3>
                                            <p class="text-xs text-gray-500 mt-0.5">Kapasitas: {{ $item->room?->kapasitas }} orang &nbsp;|&nbsp; {{ $item->room?->lokasi }}</p>
                                        </div>
                                        <span class="text-xs px-2 py-0.5 rounded-full font-semibold {{ $item->booking->status === 'disetujui' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ strtoupper($item->booking->status) }}
                                        </span>
                                    </div>
                                    <div class="mt-3 flex flex-wrap gap-3 text-xs text-gray-600 bg-gray-50 p-2.5 rounded-lg">
                                        <span>📅 {{ \Carbon\Carbon::parse($item->tanggal_mulai)->format('d-m-Y') }} s.d. {{ \Carbon\Carbon::parse($item->tanggal_selesai)->format('d-m-Y') }}</span>
                                        <span>⏰ {{ substr($item->waktu_mulai, 0, 5) }} – {{ substr($item->waktu_selesai, 0, 5) }}</span>
                                        <span>👤 Peminjam: <strong>{{ $item->booking->is_insidentil ? $item->booking->peminjam_insidentil : ($item->booking->user?->name ?? 'Mahasiswa') }}</strong></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- CONTENT: TAB RUANGAN TERSEDIA --}}
            <div x-show="activeTab === 'tersedia'" x-transition style="display:none;">
                {{-- FILTER BAR --}}
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 mb-6">
                    <form method="GET" action="{{ route('laboran.room-bookings.index') }}" class="flex flex-wrap gap-3 items-end">
                        <input type="hidden" name="active_tab" value="tersedia">
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
                            <a href="{{ route('laboran.room-bookings.index') }}" class="px-3 py-2 text-xs border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 font-medium">Reset</a>
                        </div>
                    </form>
                </div>

                @if($availableRooms->isEmpty())
                    <div class="bg-white rounded-xl border border-gray-200 py-16 text-center shadow-sm">
                        <div class="text-4xl mb-3">🏫</div>
                        <p class="text-gray-500 font-semibold">Tidak ada ruangan tersedia</p>
                        <p class="text-xs text-gray-400 mt-1">Coba filter tanggal/waktu lain atau hubungi administrator.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($availableRooms as $room)
                        <div class="bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition overflow-hidden flex flex-col justify-between">
                            <div>
                                <div class="bg-gradient-to-br from-teal-600 to-teal-800 px-5 py-4 text-white">
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <p class="text-xs font-bold uppercase tracking-widest text-teal-200">{{ $room->kode_ruangan }}</p>
                                            <h3 class="text-sm font-bold mt-0.5">{{ $room->nama_ruangan }}</h3>
                                        </div>
                                        <span class="bg-teal-500/50 text-white text-[10px] px-2 py-0.5 rounded-full font-semibold">Tersedia</span>
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
                                    <p class="text-xs text-gray-400 mt-1 line-clamp-2">{{ $room->deskripsi }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="px-5 pb-4">
                                <button type="button" @click="openAddPinjamKhususWithRoom({{ $room->id }})"
                                    class="w-full text-xs py-2 px-3 bg-teal-50 hover:bg-teal-100 text-teal-800 rounded-lg font-semibold border border-teal-200 transition">
                                    + Tambah ke Peminjaman Khusus
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>

{{-- MODALS SECTION --}}

{{-- Modal 1: Tambah Pinjam Khusus (Insidentil) --}}
<div x-show="showAddInsidentil" class="fixed inset-0 z-50 overflow-y-auto" style="display:none;" x-transition>
    <div class="flex items-center justify-center min-h-screen px-4 py-6">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-60" @click="showAddInsidentil = false"></div>
        <div class="relative bg-white rounded-xl shadow-xl p-6 w-full max-w-2xl z-10">
            <div class="flex justify-between items-center mb-4 pb-3 border-b border-gray-100">
                <h3 class="text-base font-bold text-gray-900">Tambah Peminjaman Ruangan Khusus (Insidentil)</h3>
                <button @click="showAddInsidentil = false" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <form method="POST" action="{{ route('room-bookings.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="is_insidentil" value="1">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Nama Peminjam <span class="text-red-500">*</span></label>
                        <input type="text" name="peminjam_insidentil" required placeholder="Contoh: Dr. Herman Sp.A" class="w-full text-xs border-gray-300 rounded-lg focus:ring-teal-500 py-2 px-3">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Asal Institusi Peminjam <span class="text-red-500">*</span></label>
                        <input type="text" name="institusi_insidentil" required placeholder="Contoh: Prodi D3 Kebidanan Poltekkes" class="w-full text-xs border-gray-300 rounded-lg focus:ring-teal-500 py-2 px-3">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-gray-700 mb-1">Tujuan Peminjaman <span class="text-red-500">*</span></label>
                        <textarea name="tujuan_penggunaan" required rows="2" placeholder="Contoh: Rapat Koordinasi dan Asesmen Kurikulum Baru" class="w-full text-xs border-gray-300 rounded-lg focus:ring-teal-500 py-2 px-3"></textarea>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs font-bold text-gray-700">Daftar Ruangan yang Dipinjam</label>
                        <button type="button" @click="addInsidentilRow()" class="text-xs text-teal-750 border border-teal-350 hover:bg-teal-50 rounded px-2 py-1 font-semibold flex items-center gap-1 transition">
                            + Tambah Ruangan
                        </button>
                    </div>
                    <template x-for="(row, idx) in insidentilRooms" :key="idx">
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-3">
                            <div class="flex justify-between mb-2 items-center">
                                <span class="text-xs font-bold text-amber-700">Ruangan #<span x-text="idx+1"></span></span>
                                <button type="button" x-show="insidentilRooms.length > 1" @click="removeInsidentilRow(idx)" class="text-red-400 hover:text-red-600 text-xs font-bold">Hapus</button>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                <div class="sm:col-span-2 lg:col-span-1">
                                    <label class="block text-xs text-gray-600 mb-1">Pilih Ruangan <span class="text-red-500">*</span></label>
                                    <select :name="`rooms[${idx}][room_id]`" x-model="row.room_id" required class="w-full text-xs border-gray-300 rounded-lg py-2 px-2 focus:ring-teal-500">
                                        <option value="">-- Pilih Ruangan --</option>
                                        @foreach($rooms as $r)
                                        <option value="{{ $r->id }}">{{ $r->nama_ruangan }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-600 mb-1">Tanggal Mulai <span class="text-red-500">*</span></label>
                                    <input type="date" :name="`rooms[${idx}][tanggal_mulai]`" x-model="row.tanggal_mulai" required class="w-full text-xs border-gray-300 rounded-lg py-1.5 px-2 focus:ring-teal-500">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-600 mb-1">Tanggal Selesai <span class="text-red-500">*</span></label>
                                    <input type="date" :name="`rooms[${idx}][tanggal_selesai]`" x-model="row.tanggal_selesai" required class="w-full text-xs border-gray-300 rounded-lg py-1.5 px-2 focus:ring-teal-500">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-600 mb-1">Waktu Mulai <span class="text-red-500">*</span></label>
                                    <input type="time" :name="`rooms[${idx}][waktu_mulai]`" x-model="row.waktu_mulai" required class="w-full text-xs border-gray-300 rounded-lg py-1.5 px-2 focus:ring-teal-500">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-600 mb-1">Waktu Selesai <span class="text-red-500">*</span></label>
                                    <input type="time" :name="`rooms[${idx}][waktu_selesai]`" x-model="row.waktu_selesai" required class="w-full text-xs border-gray-300 rounded-lg py-1.5 px-2 focus:ring-teal-500">
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showAddInsidentil = false" class="px-4 py-2 text-xs text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 font-medium">Batal</button>
                    <button type="submit" class="px-5 py-2 text-xs bg-amber-600 hover:bg-amber-700 text-white rounded-lg font-semibold shadow-sm transition">Simpan & Setujui</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal 2: Edit Peminjaman (Laboran) --}}
<div x-show="showEditBooking" class="fixed inset-0 z-50 overflow-y-auto" style="display:none;" x-transition>
    <div class="flex items-center justify-center min-h-screen px-4 py-6">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-60" @click="showEditBooking = false"></div>
        <div class="relative bg-white rounded-xl shadow-xl p-6 w-full max-w-2xl z-10">
            <div class="flex justify-between items-center mb-4 pb-3 border-b border-gray-100">
                <h3 class="text-base font-bold text-gray-900">Edit Peminjaman Ruangan</h3>
                <button @click="showEditBooking = false" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <form method="POST" :action="`/laboran/room-bookings/${editBooking.id}`" class="space-y-4">
                @csrf @method('PUT')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-gray-700 mb-1">Tujuan Penggunaan / Peminjaman</label>
                        <textarea name="tujuan_penggunaan" x-model="editBooking.tujuan_penggunaan" required rows="2" class="w-full text-xs border-gray-300 rounded-lg focus:ring-teal-500 py-2 px-3"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Jumlah Peserta / Mahasiswa</label>
                        <input type="number" name="jumlah_mahasiswa" x-model="editBooking.jumlah_mahasiswa" min="1" required class="w-full text-xs border-gray-300 rounded-lg focus:ring-teal-500 py-2 px-3">
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs font-bold text-gray-700">Daftar Ruangan</label>
                        <button type="button" @click="addEditBookingRow()" class="text-xs text-teal-700 border border-teal-300 rounded px-2 py-1 hover:bg-teal-50 font-semibold">+ Tambah Ruangan</button>
                    </div>
                    <template x-for="(row, idx) in editBooking.rooms" :key="idx">
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-3">
                            <div class="flex justify-between mb-2 items-center">
                                <span class="text-xs font-bold text-teal-700">Ruangan #<span x-text="idx+1"></span></span>
                                <button type="button" @click="editBooking.rooms.splice(idx,1)" class="text-red-400 hover:text-red-600 text-xs font-bold">Hapus</button>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                <div class="sm:col-span-2 lg:col-span-1">
                                    <label class="block text-xs text-gray-600 mb-1">Pilih Ruangan</label>
                                    <select :name="`rooms[${idx}][room_id]`" x-model="row.room_id" required class="w-full text-xs border-gray-300 rounded-lg py-2 px-2 focus:ring-teal-500">
                                        <option value="">-- Pilih Ruangan --</option>
                                        @foreach($rooms as $r)
                                        <option value="{{ $r->id }}">{{ $r->nama_ruangan }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-600 mb-1">Tanggal Mulai</label>
                                    <input type="date" :name="`rooms[${idx}][tanggal_mulai]`" x-model="row.tanggal_mulai" required class="w-full text-xs border-gray-300 rounded-lg py-1.5 px-2 focus:ring-teal-500">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-600 mb-1">Tanggal Selesai</label>
                                    <input type="date" :name="`rooms[${idx}][tanggal_selesai]`" x-model="row.tanggal_selesai" required class="w-full text-xs border-gray-300 rounded-lg py-1.5 px-2 focus:ring-teal-500">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-600 mb-1">Waktu Mulai</label>
                                    <input type="time" :name="`rooms[${idx}][waktu_mulai]`" x-model="row.waktu_mulai" required class="w-full text-xs border-gray-300 rounded-lg py-1.5 px-2 focus:ring-teal-500">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-600 mb-1">Waktu Selesai</label>
                                    <input type="time" :name="`rooms[${idx}][waktu_selesai]`" x-model="row.waktu_selesai" required class="w-full text-xs border-gray-300 rounded-lg py-1.5 px-2 focus:ring-teal-500">
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showEditBooking = false" class="px-4 py-2 text-xs text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 font-medium">Batal</button>
                    <button type="submit" class="px-5 py-2 text-xs bg-teal-700 hover:bg-teal-800 text-white rounded-lg font-semibold shadow-sm transition">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal 3: Edit Master Ruangan --}}
<div x-show="showEditRoom" class="fixed inset-0 z-50 overflow-y-auto" style="display:none;" x-transition>
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-60" @click="showEditRoom = false"></div>
        <div class="relative bg-white rounded-xl shadow-xl p-6 w-full max-w-lg z-10">
            <div class="flex justify-between items-center mb-4 pb-3 border-b border-gray-100">
                <h3 class="text-base font-bold text-gray-900">Edit Ruangan</h3>
                <button @click="showEditRoom = false" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <form method="POST" :action="`/rooms/${editRoom.id}`" class="space-y-4">
                @csrf @method('PUT')
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Kode Ruangan <span class="text-red-500">*</span></label>
                        <input type="text" name="kode_ruangan" x-model="editRoom.kode_ruangan" required class="w-full text-xs border-gray-300 rounded-lg focus:ring-teal-500 py-2 px-3">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Kapasitas <span class="text-red-500">*</span></label>
                        <input type="number" name="kapasitas" x-model="editRoom.kapasitas" min="1" required class="w-full text-xs border-gray-300 rounded-lg focus:ring-teal-500 py-2 px-3">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Nama Ruangan <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_ruangan" x-model="editRoom.nama_ruangan" required class="w-full text-xs border-gray-300 rounded-lg focus:ring-teal-500 py-2 px-3">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Lokasi / Gedung</label>
                    <input type="text" name="lokasi" x-model="editRoom.lokasi" class="w-full text-xs border-gray-300 rounded-lg focus:ring-teal-500 py-2 px-3">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Deskripsi / Fasilitas</label>
                    <textarea name="deskripsi" x-model="editRoom.deskripsi" rows="2" class="w-full text-xs border-gray-300 rounded-lg focus:ring-teal-500 py-2 px-3"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Status</label>
                    <select name="status" x-model="editRoom.status" class="w-full text-xs border-gray-300 rounded-lg focus:ring-teal-500 py-2 px-3">
                        <option value="tersedia">Tersedia</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showEditRoom = false" class="px-4 py-2 text-xs text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 font-medium">Batal</button>
                    <button type="submit" class="px-5 py-2 text-xs bg-teal-700 hover:bg-teal-800 text-white rounded-lg font-semibold shadow-sm transition">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal 4: Tolak Peminjaman --}}
<div x-show="showRejectModal" class="fixed inset-0 z-50 overflow-y-auto" style="display:none;" x-transition>
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-60" @click="showRejectModal = false"></div>
        <div class="relative bg-white rounded-xl shadow-xl p-6 w-full max-w-md z-10">
            <h3 class="text-base font-bold text-gray-900 mb-4">Tolak Peminjaman Ruangan</h3>
            <form method="POST" :action="`/laboran/room-bookings/${rejectBookingId}/reject`" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Alasan / Catatan Penolakan</label>
                    <textarea name="catatan_laboran" rows="3" required placeholder="Berikan alasan penolakan kepada mahasiswa..." class="w-full text-xs border-gray-300 rounded-lg focus:ring-red-400 py-2 px-3"></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" @click="showRejectModal = false" class="px-4 py-2 text-xs border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 font-medium">Batal</button>
                    <button type="submit" class="px-5 py-2 text-xs bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold shadow-sm transition">Tolak Peminjaman</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function roomLaboranApp() {
    return {
        // Tab states
        activeTab: (new URLSearchParams(window.location.search)).get('active_tab') || 'manajemen',
        activeSubTab: 'verifikasi',

        // Modal triggers
        showAddRoom: false,
        showEditRoom: false,
        showEditBooking: false,
        showRejectModal: false,
        showAddInsidentil: false,
        rejectBookingId: null,

        // Data bindings
        editRoom: { id: '', kode_ruangan: '', nama_ruangan: '', kapasitas: 1, lokasi: '', deskripsi: '', status: 'tersedia' },
        editBooking: { id: '', tujuan_penggunaan: '', jumlah_mahasiswa: 1, rooms: [] },
        insidentilRooms: [{ room_id: '', tanggal_mulai: '', tanggal_selesai: '', waktu_mulai: '', waktu_selesai: '' }],

        openEditRoom(id, kode, nama, kapasitas, lokasi, deskripsi, status) {
            this.editRoom = { id, kode_ruangan: kode, nama_ruangan: nama, kapasitas, lokasi, deskripsi, status };
            this.showEditRoom = true;
        },

        openEditModal(id, jumlah, tujuan, rooms) {
            this.editBooking = { id, tujuan_penggunaan: tujuan, jumlah_mahasiswa: jumlah, rooms: JSON.parse(JSON.stringify(rooms)) };
            this.showEditBooking = true;
        },

        addEditBookingRow() {
            this.editBooking.rooms.push({ room_id: '', tanggal_mulai: '', tanggal_selesai: '', waktu_mulai: '', waktu_selesai: '' });
        },

        addInsidentilRow() {
            this.insidentilRooms.push({ room_id: '', tanggal_mulai: '', tanggal_selesai: '', waktu_mulai: '', waktu_selesai: '' });
        },

        removeInsidentilRow(idx) {
            if (this.insidentilRooms.length > 1) this.insidentilRooms.splice(idx, 1);
        },

        openRejectModal(id) {
            this.rejectBookingId = id;
            this.showRejectModal = true;
        },

        openAddPinjamKhususWithRoom(roomId) {
            this.insidentilRooms = [{ room_id: roomId, tanggal_mulai: '', tanggal_selesai: '', waktu_mulai: '', waktu_selesai: '' }];
            this.showAddInsidentil = true;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }
}
</script>
</body>
</html>
