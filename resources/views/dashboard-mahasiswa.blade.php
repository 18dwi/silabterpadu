<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dashboard Mahasiswa - Si-Lab ') . ucwords(str_replace('_', ' ', Auth::user()->jurusan)) }}
            </h2>
            <span class="text-sm bg-teal-50 text-teal-700 font-medium px-3 py-1 rounded-full border border-teal-200">
                NIM: {{ Auth::user()->nomor_induk }}
            </span>
        </div>
    </x-slot>

    <div class="py-10 bg-gray-50 min-h-screen" 
         x-data="{ 
             activeTab: localStorage.getItem('mahasiswaActiveTab') || 'mulai_peminjaman', 
             activeFormTipe: 'peminjaman_alat',
             formTglPinjam: '',
             searchAlat: '',
             searchBahan: '',
             packagesList: [],
             itemsList: [],
             allItems: {{ $items->toJson() }},
             allPackages: {{ $packages->toJson() }},
             getFinalItems() {
                 let list = [];
                 this.packagesList.forEach(pkg => {
                     if (!pkg.package_id) return;
                     let dbPkg = this.allPackages.find(p => p.id == pkg.package_id);
                     if (!dbPkg) return;
                     dbPkg.items.forEach(pi => {
                         if ((this.activeFormTipe === 'peminjaman_alat' && pi.item.kategori === 'alat') ||
                             (this.activeFormTipe === 'permintaan_bahan' && pi.item.kategori === 'bahan')) {
                             list.push({
                                 item_id: pi.item_id,
                                 jumlah_diminta: pi.jumlah * pkg.package_qty,
                                 package_id: pkg.package_id,
                                 package_qty: pkg.package_qty
                             });
                         }
                     });
                 });
                 this.itemsList.forEach(item => {
                     if (!item.item_id) return;
                     list.push({
                         item_id: item.item_id,
                         jumlah_diminta: item.jumlah_diminta,
                         package_id: '',
                         package_qty: ''
                     });
                 });
                 return list;
             },
             getItemTotalRequested(itemId) {
                 let total = 0;
                 this.getFinalItems().forEach(fItem => {
                     if (fItem.item_id == itemId) {
                         total += parseInt(fItem.jumlah_diminta) || 0;
                     }
                 });
                 return total;
             },
             hasStockError() {
                 let hasError = false;
                 let uniqueItemIds = [...new Set(this.getFinalItems().map(f => f.item_id))];
                 uniqueItemIds.forEach(id => {
                     let dbItem = this.allItems.find(i => i.id == id);
                     if (dbItem) {
                         let totalReq = this.getItemTotalRequested(id);
                         if (totalReq > dbItem.stok_tersedia) {
                             hasError = true;
                         }
                     }
                 });
                 return hasError;
             },
             roomActiveTab: 'tersedia',
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
         }"
         x-init="$watch('activeTab', val => localStorage.setItem('mahasiswaActiveTab', val))">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="bg-teal-50 border-l-4 border-teal-500 p-4 mb-6 rounded-md shadow-sm">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-teal-600" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-teal-800">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-md shadow-sm">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-600" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800">{{ $errors->first() }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if($certificate)
                <div class="bg-gradient-to-r from-teal-500 to-indigo-600 rounded-xl shadow-lg border border-teal-100/50 p-6 mb-6 text-white flex flex-col md:flex-row items-center justify-between gap-4 transform transition hover:scale-[1.01]">
                    <div class="flex items-center gap-4">
                        <div class="bg-white/10 p-3.5 rounded-full border border-white/20">
                            <span class="text-3xl">🎓</span>
                        </div>
                        <div>
                            <h3 class="text-base font-bold tracking-wide">Surat Bebas Laboratorium Anda Telah Terbit!</h3>
                            <p class="text-xs text-teal-55 mt-1 max-w-xl">Selamat! Surat keterangan Bebas Laboratorium dengan nomor <strong class="underline">{{ $certificate->nomor_surat }}</strong> telah diterbitkan secara resmi pada tanggal <strong>{{ $certificate->tanggal_terbit->translatedFormat('d M Y') }}</strong> oleh <strong>{{ $certificate->laboran ? $certificate->laboran->name : 'Pengelola Laboratorium' }}</strong> sebagai syarat sidang akhir / wisuda Anda.</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 w-full md:w-auto justify-end">
                        <a 
                            href="{{ route('bebas-lab.pdf', $certificate->id) }}?action=preview" 
                            target="_blank"
                            class="flex-1 md:flex-initial text-center px-4 py-2 text-xs font-bold bg-white/10 hover:bg-white/20 border border-white/35 rounded-md transition shadow-sm whitespace-nowrap"
                        >
                            👁 Lihat Surat
                        </a>
                        <a 
                            href="{{ route('bebas-lab.pdf', $certificate->id) }}?action=download" 
                            class="flex-1 md:flex-initial text-center px-4 py-2 text-xs font-bold bg-white text-teal-800 hover:bg-teal-50 rounded-md transition shadow-md font-semibold whitespace-nowrap"
                        >
                            📥 Unduh PDF
                        </a>
                    </div>
                </div>
            @endif

            <div class="flex flex-row gap-4 md:gap-8" style="display: flex !important; flex-direction: row !important; flex-wrap: nowrap !important; align-items: flex-start;">
                
                <!-- SIDEBAR VERTICAL (Left Side) -->
                <div class="w-48 md:w-64 flex-shrink-0" style="flex: 0 0 auto; width: 16rem;">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 flex flex-col items-center text-center sticky top-4 max-h-[calc(100vh-2rem)] overflow-y-auto">
                        <span class="text-[8px] text-gray-300 absolute top-1 left-1">v3</span>
                        <!-- Logo & Title Area -->
                        <div class="flex flex-col items-center py-1 mb-3">
                            <img src="{{ asset('images/logo_poltekkes.png') }}" class="w-14 h-14 object-contain mb-2" alt="Logo Poltekkes">
                            <h3 class="text-xs font-bold text-teal-800 tracking-wide text-center">Silab Terpadu</h3>
                            <p class="text-[9px] text-gray-400 font-semibold uppercase tracking-wider mt-1">Poltekkes Jakarta I</p>
                        </div>

                            <!-- Main Sidebar Menus -->
                            <div class="space-y-1.5 w-full">
                                <!-- 1. Peminjaman Ruangan Option -->
                                <button @click="activeTab = 'peminjaman_ruangan'"
                                    :class="activeTab === 'peminjaman_ruangan' ? 'bg-teal-600 text-white font-semibold shadow-md ring-1 ring-teal-500' : 'bg-white text-gray-700 hover:bg-teal-50 border border-gray-100 font-medium'"
                                    class="w-full text-left px-3 py-2.5 rounded-lg text-xs transition-all duration-150 flex items-center justify-between shadow-sm"
                                >
                                    <span class="flex items-center gap-2.5">
                                        <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        Peminjaman Ruangan
                                    </span>
                                </button>

                                <!-- 2. Peminjaman Alat/Bahan Option (was: Mulai Peminjaman) -->
                                <button 
                                    @click="activeTab = 'mulai_peminjaman'"
                                    :class="activeTab === 'mulai_peminjaman' ? 'bg-teal-600 text-white font-semibold shadow-md ring-1 ring-teal-500' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-100 font-medium'"
                                    class="w-full text-left px-3 py-2.5 rounded-lg text-xs transition-all duration-150 flex items-center justify-between shadow-sm"
                                >
                                    <span class="flex items-center gap-2.5">
                                        <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                        Peminjaman Alat/Bahan
                                    </span>
                                </button>

                                <!-- 3. Stok Alat Option -->
                                <button 
                                    @click="activeTab = 'stok_alat'"
                                    :class="activeTab === 'stok_alat' ? 'bg-teal-600 text-white font-semibold shadow-md ring-1 ring-teal-500' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-100 font-medium'"
                                    class="w-full text-left px-3 py-2.5 rounded-lg text-xs transition-all duration-150 flex items-center justify-between shadow-sm"
                                >
                                    <span class="flex items-center gap-2.5">
                                        <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                                        </svg>
                                        Stok Alat
                                    </span>
                                </button>

                                <!-- 4. Stok Bahan Option -->
                                <button 
                                    @click="activeTab = 'stok_bahan'"
                                    :class="activeTab === 'stok_bahan' ? 'bg-teal-600 text-white font-semibold shadow-md ring-1 ring-teal-500' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-100 font-medium'"
                                    class="w-full text-left px-3 py-2.5 rounded-lg text-xs transition-all duration-150 flex items-center justify-between shadow-sm"
                                >
                                    <span class="flex items-center gap-2.5">
                                        <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                        </svg>
                                        Stok Bahan
                                    </span>
                                </button>
                            </div>

                    </div>
                </div>

                <!-- CONTENT AREA (Right Side) -->
                <div class="flex-grow w-full" style="flex: 1 1 0%; min-width: 0; max-width: 100%;">
                    
                    <!-- TAB 1: MULAI PEMINJAMAN (FULL WIDTH PAGE) -->
                    <div x-show="activeTab === 'mulai_peminjaman'" class="space-y-8" x-transition>
                        
                        <!-- Horizontal sub-menu for lending type -->
                        <div class="flex flex-wrap gap-2 mb-6 bg-gray-200/40 p-1.5 rounded-md max-w-max border border-gray-200">
                            <button 
                                @click="activeFormTipe = 'peminjaman_alat'; itemsList = []"
                                :class="activeFormTipe === 'peminjaman_alat' ? 'bg-teal-600 text-white font-semibold shadow-sm' : 'bg-white text-gray-650 hover:bg-white/80'"
                                class="px-5 py-2 rounded-md text-xs transition-all duration-150"
                            >
                                Pinjam Alat
                            </button>
                            <button 
                                @click="activeFormTipe = 'permintaan_bahan'; itemsList = []"
                                :class="activeFormTipe === 'permintaan_bahan' ? 'bg-teal-600 text-white font-semibold shadow-sm' : 'bg-white text-gray-650 hover:bg-white/80'"
                                class="px-5 py-2 rounded-md text-xs transition-all duration-150"
                            >
                                Minta Bahan
                            </button>
                        </div>

                        <!-- Full Width Form Card -->
                        <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100">
                            <div class="p-6">
                                @if($certificate)
                                    <div class="text-center py-12 px-6">
                                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-red-50 text-red-500 mb-4 border border-red-100 text-3xl">
                                            🛑
                                        </div>
                                        <h3 class="text-base font-bold text-gray-900">Akses Peminjaman Ditutup</h3>
                                        <p class="mt-2 text-xs text-gray-500 max-w-md mx-auto">Anda tidak dapat melakukan peminjaman alat atau permintaan bahan karena Surat Bebas Laboratorium Anda telah resmi diterbitkan.</p>
                                    </div>
                                @else
                                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                        Form Pengajuan <span x-text="activeFormTipe === 'peminjaman_alat' ? 'Peminjaman Alat' : 'Permintaan Bahan'"></span>
                                    </h3>
                                    <p class="text-sm text-gray-500 mb-6">Silakan lengkapi detail pengajuan di bawah ini. Pastikan dosen penanggung jawab kegiatan telah menyetujui rencana Anda.</p>

                                    <form method="POST" action="{{ route('transactions.store') }}" class="space-y-6">
                                    @csrf
                                    <!-- Hidden type input binded to state -->
                                    <input type="hidden" name="tipe" :value="activeFormTipe" />

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <!-- Penanggung Jawab -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Dosen Penanggung Jawab Mata Kuliah/Kegiatan</label>
                                            <input 
                                                type="text" 
                                                name="penanggung_jawab" 
                                                required 
                                                placeholder="Contoh: Ns. Hartono, M.Kep"
                                                class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:border-teal-500 focus:ring-teal-500"
                                            />
                                        </div>

                                        <!-- Nama Mata Kuliah / Kegiatan -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Mata Kuliah / Kegiatan Praktikum</label>
                                            <input 
                                                type="text" 
                                                name="kegiatan" 
                                                required 
                                                placeholder="Contoh: Praktikum Keperawatan Medikal Bedah II"
                                                class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:border-teal-500 focus:ring-teal-500"
                                            />
                                        </div>
                                    </div>

                                    <!-- Date Fields for Tool Lending / Material Request -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1" x-text="activeFormTipe === 'peminjaman_alat' ? 'Tanggal Pinjam Alat' : 'Tanggal Penggunaan Bahan'"></label>
                                            <input 
                                                type="datetime-local" 
                                                name="tanggal_pinjam"
                                                x-model="formTglPinjam"
                                                required
                                                class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:border-teal-500 focus:ring-teal-500"
                                            />
                                        </div>
                                        <div x-show="activeFormTipe === 'peminjaman_alat'" x-transition>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Rencana Tanggal Pengembalian Alat</label>
                                            <input 
                                                type="datetime-local" 
                                                name="tanggal_kembali_rencana" 
                                                :min="formTglPinjam"
                                                :required="activeFormTipe === 'peminjaman_alat'"
                                                class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:border-teal-500 focus:ring-teal-500"
                                            />
                                        </div>
                                    </div>

                                    <!-- SECTION 1: PACKAGES REQUEST (OPTIONAL) -->
                                    <div class="p-5 bg-teal-50/20 border border-teal-100 rounded-lg space-y-4">
                                        <div class="flex justify-between items-center pb-2 border-b border-teal-100/50">
                                            <div>
                                                <h4 class="text-sm font-semibold text-teal-900">Peminjaman Paket Alat & Bahan (Opsional)</h4>
                                                <p class="text-[10px] text-gray-500">Pilih paket siap pakai dan jumlah paket yang dibutuhkan.</p>
                                            </div>
                                            <button 
                                                type="button" 
                                                @click="packagesList.push({ package_id: '', package_qty: 1, search: '' })"
                                                class="text-xs text-teal-700 hover:text-teal-950 font-bold bg-teal-100/50 hover:bg-teal-100 px-3 py-1.5 rounded transition duration-150"
                                            >
                                                + Tambah Paket
                                            </button>
                                        </div>

                                        <div class="space-y-4">
                                            <template x-for="(pkg, pIdx) in packagesList" :key="'pkg-'+pIdx">
                                                <div class="p-4 bg-white rounded-lg border border-teal-100/60 shadow-sm relative">
                                                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-center">
                                                        <!-- Package choice -->
                                                        <div class="sm:col-span-6" x-data="{ isOpen: false }">
                                                             <label class="block text-[10px] font-bold text-teal-800 uppercase mb-1">Pilih Paket</label>
                                                             <div class="relative">
                                                                 <input 
                                                                     type="text" 
                                                                     x-model="pkg.search"
                                                                     @focus="isOpen = true; $el.select()"
                                                                     @click.away="setTimeout(() => { isOpen = false; if (pkg.package_id) { let match = allPackages.find(p => p.id == pkg.package_id); if (match) pkg.search = match.nama_paket; } else { pkg.search = ''; } }, 200)"
                                                                     placeholder="Ketik nama paket untuk mencari..." 
                                                                     class="w-full text-xs border-gray-300 rounded-md focus:ring-teal-500 focus:border-teal-500 py-2 px-3"
                                                                 />
                                                                 <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-500">
                                                                     <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                                     </svg>
                                                                 </span>
                                                                 <div x-show="isOpen" 
                                                                      class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-md shadow-lg" style="max-height: 200px; overflow-y: auto;"
                                                                      x-transition>
                                                                     <div class="py-1">
                                                                         <div @click="pkg.package_id = ''; pkg.search = ''; isOpen = false"
                                                                              class="cursor-pointer hover:bg-slate-100 px-3 py-2 text-xs text-gray-500">
                                                                             -- Pilih Paket --
                                                                         </div>
                                                                         <template x-for="dbPkg in allPackages.filter(p => { let isMatch = false; if (activeFormTipe === 'peminjaman_alat') { isMatch = p.items.some(pi => pi.item && pi.item.kategori === 'alat'); } else if (activeFormTipe === 'permintaan_bahan') { isMatch = p.items.some(pi => pi.item && pi.item.kategori === 'bahan'); } if (!isMatch) return false; return !pkg.search || p.nama_paket.toLowerCase().includes(pkg.search.toLowerCase()) || (p.deskripsi && p.deskripsi.toLowerCase().includes(pkg.search.toLowerCase())); })" :key="dbPkg.id">
                                                                             <div @click="pkg.package_id = dbPkg.id; pkg.search = dbPkg.nama_paket; isOpen = false"
                                                                                  class="cursor-pointer hover:bg-teal-50 hover:text-teal-900 px-3 py-2 text-xs text-gray-700"
                                                                                  :class="pkg.package_id == dbPkg.id ? 'bg-teal-50 text-teal-900 font-bold' : ''"
                                                                                  x-text="dbPkg.nama_paket">
                                                                             </div>
                                                                         </template>
                                                                     </div>
                                                                 </div>
                                                             </div>
                                                         </div>

                                                        <!-- Package Qty -->
                                                        <div class="sm:col-span-4">
                                                            <label class="block text-[10px] font-bold text-teal-800 uppercase mb-1">Jumlah Paket</label>
                                                            <input 
                                                                type="number" 
                                                                x-model="pkg.package_qty"
                                                                min="1"
                                                                class="w-full text-xs border-gray-300 rounded focus:ring-teal-500 focus:border-teal-500"
                                                                required
                                                            />
                                                        </div>

                                                        <!-- Remove package button -->
                                                        <div class="sm:col-span-2 flex justify-end">
                                                            <button 
                                                                type="button" 
                                                                @click="packagesList.splice(pIdx, 1)"
                                                                class="inline-flex items-center px-3 py-1.5 border border-red-200 text-xs font-semibold rounded text-red-650 bg-white hover:bg-red-50 transition duration-150"
                                                            >
                                                                Hapus
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <!-- Package Items multiplied info list -->
                                                    <div class="mt-3 text-[11px] text-gray-500 border-t border-dashed border-gray-200 pt-2" x-show="pkg.package_id">
                                                        <strong class="text-teal-900">Isi Paket (Jumlah Pengali x <span x-text="pkg.package_qty || 1"></span>):</strong>
                                                        <ul class="list-disc list-inside mt-1.5 space-y-1">
                                                            <template x-for="pi in allPackages.find(p => p.id == pkg.package_id)?.items || []">
                                                                <li x-show="(activeFormTipe === 'peminjaman_alat' && pi.item.kategori === 'alat') || (activeFormTipe === 'permintaan_bahan' && pi.item.kategori === 'bahan')" class="text-xs">
                                                                    <span x-text="pi.item.nama_barang" class="font-medium text-gray-700"></span>: 
                                                                    <span class="font-bold text-gray-900" x-text="(pi.jumlah * (pkg.package_qty || 1))"></span> 
                                                                    <span x-text="pi.item.satuan" class="italic text-gray-500"></span>
                                                                    <span class="text-red-500 font-bold ml-1.5" x-show="getItemTotalRequested(pi.item_id) > pi.item.stok_tersedia">
                                                                        ⚠️ Melebihi stok (Tersedia: <span x-text="pi.item.stok_tersedia"></span>)
                                                                    </span>
                                                                </li>
                                                            </template>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- SECTION 2: INDIVIDUAL ITEMS (OPTIONAL) -->
                                    <div class="p-5 bg-gray-50/50 border border-gray-200 rounded-lg space-y-4">
                                        <div class="flex justify-between items-center pb-2 border-b border-gray-200">
                                            <div>
                                                <h4 class="text-sm font-semibold text-gray-900">Peminjaman Barang Satuan (Opsional)</h4>
                                                <p class="text-[10px] text-gray-500">Tambahkan barang medis eceran atau satuan di luar paket.</p>
                                            </div>
                                            <button 
                                                type="button" 
                                                @click="itemsList.push({ item_id: '', jumlah_diminta: 1, search: '' })"
                                                class="text-xs text-teal-650 hover:text-teal-850 font-bold bg-white hover:bg-slate-50 border border-slate-200 px-3 py-1.5 rounded transition duration-150"
                                            >
                                                + Tambah Barang Satuan
                                            </button>
                                        </div>

                                        <div class="space-y-3">
                                            <template x-for="(item, index) in itemsList" :key="'satuan-'+index">
                                                <div class="p-4 bg-white rounded-lg border border-gray-250/60 shadow-sm relative">
                                                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-center">
                                                        <!-- Dropdown Item Selection with search bar -->
                                                         <div class="sm:col-span-7" x-data="{ isOpen: false }">
                                                             <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Pilih Barang</label>
                                                             <div class="relative">
                                                                 <input 
                                                                     type="text" 
                                                                     x-model="item.search"
                                                                     @focus="isOpen = true; $el.select()"
                                                                     @click.away="setTimeout(() => { isOpen = false; if (item.item_id) { let match = allItems.find(i => i.id == item.item_id); if (match) item.search = match.nama_barang + ' (' + match.stok_tersedia + ' ' + match.satuan + ')'; } else { item.search = ''; } }, 200)"
                                                                     placeholder="Ketik nama atau kode barang..." 
                                                                     class="w-full text-xs border-gray-300 rounded-md focus:ring-teal-500 focus:border-teal-500 py-2 px-3"
                                                                 />
                                                                 <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-500">
                                                                     <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                                     </svg>
                                                                 </span>
                                                                 <div x-show="isOpen" 
                                                                      class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-md shadow-lg" style="max-height: 200px; overflow-y: auto;"
                                                                      x-transition>
                                                                     <div class="py-1">
                                                                         <div @click="item.item_id = ''; item.search = ''; isOpen = false"
                                                                              class="cursor-pointer hover:bg-slate-100 px-3 py-2 text-xs text-gray-500">
                                                                             -- Pilih Barang --
                                                                         </div>
                                                                         <template x-for="dbItem in allItems.filter(i => ((activeFormTipe === 'peminjaman_alat' && i.kategori === 'alat') || (activeFormTipe === 'permintaan_bahan' && i.kategori === 'bahan')) && (!item.search || i.nama_barang.toLowerCase().includes(item.search.toLowerCase()) || i.kode_barang.toLowerCase().includes(item.search.toLowerCase())))" :key="dbItem.id">
                                                                             <div @click="if (dbItem.stok_tersedia > 0 && dbItem.status !== 'rusak') { item.item_id = dbItem.id; item.search = dbItem.nama_barang + ' (' + dbItem.stok_tersedia + ' ' + dbItem.satuan + ')'; isOpen = false; }"
                                                                                  class="cursor-pointer px-3 py-2 text-xs"
                                                                                  :class="dbItem.stok_tersedia <= 0 || dbItem.status === 'rusak' ? 'text-gray-300 cursor-not-allowed bg-gray-50' : (item.item_id == dbItem.id ? 'bg-teal-50 text-teal-900 font-bold' : 'text-gray-700 hover:bg-teal-50 hover:text-teal-900')"
                                                                                  x-text="dbItem.nama_barang + ' (' + dbItem.stok_tersedia + ' ' + dbItem.satuan + ')'">
                                                                             </div>
                                                                         </template>
                                                                     </div>
                                                                 </div>
                                                             </div>
                                                         </div>

                                                        <!-- Quantity input -->
                                                        <div class="sm:col-span-3">
                                                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Jumlah</label>
                                                            <input 
                                                                type="number" 
                                                                x-model="item.jumlah_diminta"
                                                                min="1" 
                                                                class="w-full text-xs border-gray-300 rounded-md focus:ring-teal-500 focus:border-teal-500"
                                                                required
                                                            />
                                                        </div>

                                                        <!-- Remove button -->
                                                        <div class="sm:col-span-2 flex justify-end">
                                                            <button 
                                                                type="button" 
                                                                @click="itemsList.splice(index, 1)"
                                                                class="inline-flex items-center px-3 py-1.5 border border-red-200 text-xs font-semibold rounded text-red-650 bg-white hover:bg-red-50 transition duration-150"
                                                            >
                                                                Hapus
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <!-- Realtime Stock Warning -->
                                                    <template x-if="item.item_id && getItemTotalRequested(item.item_id) > (allItems.find(i => i.id == item.item_id)?.stok_tersedia || 0)">
                                                        <p class="text-[9px] text-red-500 font-bold mt-2 leading-tight">
                                                            ⚠️ Melebihi stok (Maks: <span x-text="allItems.find(i => i.id == item.item_id)?.stok_tersedia"></span>, Total Diajukan: <span x-text="getItemTotalRequested(item.item_id)"></span>)
                                                        </p>
                                                    </template>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- HIDDEN payload data inputs computed from packagesList and itemsList -->
                                    <div class="hidden">
                                        <template x-for="(fItem, idx) in getFinalItems()" :key="'final-'+idx">
                                            <div>
                                                <input type="hidden" :name="'items['+idx+'][item_id]'" :value="fItem.item_id" />
                                                <input type="hidden" :name="'items['+idx+'][jumlah_diminta]'" :value="fItem.jumlah_diminta" />
                                                <input type="hidden" :name="'items['+idx+'][package_id]'" :value="fItem.package_id" />
                                                <input type="hidden" :name="'items['+idx+'][package_qty]'" :value="fItem.package_qty" />
                                            </div>
                                        </template>
                                    </div>

                                    <div class="pt-4 flex justify-end">
                                        <button 
                                            type="submit" 
                                            :disabled="getFinalItems().length === 0 || hasStockError()"
                                            :class="(getFinalItems().length === 0 || hasStockError()) ? 'bg-gray-400 cursor-not-allowed opacity-50' : 'bg-teal-600 hover:bg-teal-700 active:bg-teal-800'"
                                            class="inline-flex justify-center items-center px-5 py-2.5 border border-transparent rounded-md font-semibold text-sm text-white transition duration-150 shadow-sm"
                                        >
                                            Kirim Form Pengajuan
                                        </button>
                                    </div>
                                </form>
                                @endif
                            </div>
                        </div>

                        <!-- Full Width Transaction History Table -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">Riwayat Pengajuan & Transaksi</h3>
                                <p class="text-sm text-gray-500 mb-6">Berikut adalah status riwayat peminjaman alat medis atau permintaan bahan praktikum Anda.</p>

                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr class="text-xs font-semibold text-gray-500 uppercase tracking-wider text-center">
                                                <th class="px-4 py-3 text-left">No. Transaksi</th>
                                                <th class="px-4 py-3 text-left">Tipe</th>
                                                <th class="px-4 py-3 text-left">Item (Kuantitas)</th>
                                                <th class="px-4 py-3">Jadwal Pinjam</th>
                                                <th class="px-4 py-3">Status</th>
                                                <th class="px-4 py-3">Tanggal Realisasi Kembali</th>
                                                <th class="px-4 py-3">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-100 text-xs text-center">
                                            @if($certificate)
                                                <tr class="bg-teal-50/40 hover:bg-teal-50/70 transition-colors duration-155 border-b-2 border-teal-200">
                                                    <td class="px-4 py-4 text-left font-bold text-teal-700">
                                                        🎓 {{ $certificate->nomor_surat }}
                                                    </td>
                                                    <td class="px-4 py-4 text-left">
                                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-teal-600 text-white border border-teal-550">
                                                            Bebas Lab
                                                        </span>
                                                    </td>
                                                    <td class="px-4 py-4 text-left font-semibold text-teal-900">
                                                        Syarat Kelulusan / Sidang Akhir
                                                    </td>
                                                    <td class="px-4 py-4 text-gray-400 font-medium font-sans">
                                                        -
                                                    </td>
                                                    <td class="px-4 py-4">
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                            Terbit / Aktif
                                                        </span>
                                                    </td>
                                                    <td class="px-4 py-4 text-gray-400 font-medium font-sans">
                                                        -
                                                    </td>
                                                    <td class="px-4 py-4 whitespace-nowrap font-medium flex items-center justify-center gap-1.5">
                                                        <a 
                                                            href="{{ route('bebas-lab.pdf', $certificate->id) }}?action=preview" 
                                                            target="_blank"
                                                            class="inline-flex items-center px-2.5 py-1 text-[10px] font-bold border border-teal-600 rounded text-teal-700 bg-teal-50 hover:bg-teal-100 transition duration-150 shadow-sm"
                                                            title="Lihat Terlebih Dahulu"
                                                        >
                                                            👁 Lihat
                                                        </a>
                                                        <a 
                                                            href="{{ route('bebas-lab.pdf', $certificate->id) }}?action=download" 
                                                            class="inline-flex items-center px-2.5 py-1 text-[10px] font-bold border border-teal-500 rounded text-white bg-teal-600 hover:bg-teal-700 transition duration-150 shadow-sm"
                                                            title="Unduh PDF"
                                                        >
                                                            Unduh
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endif
                                            @forelse($transactions as $tx)
                                                <tr class="hover:bg-slate-50/40 transition-colors duration-150">
                                                    <td class="px-4 py-4 text-left font-bold text-teal-700">
                                                        TX-{{ str_pad($tx->id, 5, '0', STR_PAD_LEFT) }}
                                                    </td>
                                                    <td class="px-4 py-4 text-left">
                                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $tx->tipe === 'peminjaman_alat' ? 'bg-indigo-50 text-indigo-700 border border-indigo-150' : 'bg-orange-50 text-orange-700 border border-orange-150' }}">
                                                            {{ $tx->tipe === 'peminjaman_alat' ? 'Pinjam Alat' : 'Minta Bahan' }}
                                                        </span>
                                                    </td>
                                                    <td class="px-4 py-4 text-left">
                                                        <div class="space-y-2">
                                                            @php 
                                                                $groupedDetails = $tx->details->groupBy('package_id');
                                                            @endphp
                                                            @foreach($groupedDetails as $pkgId => $details)
                                                                @if($pkgId)
                                                                    @php 
                                                                        $firstDetail = $details->first();
                                                                        $packageName = $firstDetail->package ? $firstDetail->package->nama_paket : 'Paket Terhapus';
                                                                        $packageQty = $firstDetail->package_qty;
                                                                    @endphp
                                                                    <div class="p-1.5 bg-teal-50/40 rounded border border-teal-100 text-[10px] space-y-0.5">
                                                                        <div class="font-bold text-teal-800">🎁 {{ $packageName }} ({{ $packageQty }} set)</div>
                                                                        @foreach($details as $det)
                                                                            <div class="text-slate-600 pl-2 flex justify-between gap-2">
                                                                                <span>- {{ $det->item->nama_barang }}</span>
                                                                                <span class="font-bold text-slate-800">{{ $det->jumlah_diminta }} {{ $det->item->satuan }}</span>
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                @else
                                                                    <div class="p-1.5 bg-gray-50/50 rounded border border-gray-150 text-[10px] space-y-0.5">
                                                                        <div class="font-bold text-gray-550">📦 Barang Satuan</div>
                                                                        @foreach($details as $det)
                                                                            <div class="text-slate-600 pl-2 flex justify-between gap-2">
                                                                                <span>- {{ $det->item->nama_barang }}</span>
                                                                                <span class="font-bold text-slate-800">{{ $det->jumlah_diminta }} {{ $det->item->satuan }}</span>
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                @endif
                                                            @endforeach
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-4 text-center">
                                                        @if($tx->tipe === 'peminjaman_alat')
                                                            <div class="text-xs text-slate-700 text-left bg-slate-50 p-2 rounded border border-slate-100">
                                                                <div><strong>Tgl Mulai:</strong> {{ $tx->tanggal_pinjam ? $tx->tanggal_pinjam->format('d-m-Y') : '-' }} <span class="text-slate-400">|</span> <strong>Waktu:</strong> {{ $tx->tanggal_pinjam ? $tx->tanggal_pinjam->format('H:i') : '-' }}</div>
                                                                <div class="mt-1"><strong>Tgl Selesai:</strong> {{ $tx->tanggal_kembali_rencana ? $tx->tanggal_kembali_rencana->format('d-m-Y') : '-' }} <span class="text-slate-400">|</span> <strong>Waktu:</strong> {{ $tx->tanggal_kembali_rencana ? $tx->tanggal_kembali_rencana->format('H:i') : '-' }}</div>
                                                            </div>
                                                        @else
                                                            <div class="text-xs text-slate-700 text-left bg-slate-50 p-2 rounded border border-slate-100">
                                                                <div><strong>Tgl Mulai:</strong> {{ $tx->tanggal_pinjam ? $tx->tanggal_pinjam->format('d-m-Y') : '-' }} <span class="text-slate-400">|</span> <strong>Waktu:</strong> {{ $tx->tanggal_pinjam ? $tx->tanggal_pinjam->format('H:i') : '-' }}</div>
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td class="px-4 py-4 whitespace-nowrap">
                                                        @if($tx->status_pengembalian === 'belum_selesai')
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300 shadow-sm animate-pulse">
                                                                Pengembalian Belum Selesai
                                                            </span>
                                                        @elseif($tx->status === 'pending')
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                                Pending
                                                            </span>
                                                        @elseif($tx->status === 'disetujui')
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                                                Disetujui / Aktif
                                                            </span>
                                                        @elseif($tx->status === 'ditangguhkan')
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-yellow-50 text-yellow-700 border border-yellow-250">
                                                                Ditangguhkan
                                                            </span>
                                                        @elseif($tx->status === 'ditolak')
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                                Ditolak
                                                            </span>
                                                        @else
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                                Selesai
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td class="px-4 py-4 text-slate-600 font-medium">
                                                        {{ $tx->tanggal_kembali_realisasi ? $tx->tanggal_kembali_realisasi->format('d-m-Y H:i') : '-' }}
                                                    </td>
                                                    <td class="px-4 py-4 whitespace-nowrap font-medium flex items-center justify-center gap-1.5">
                                                        @if(in_array($tx->status, ['disetujui', 'selesai', 'ditolak', 'ditangguhkan']))
                                                            <div class="flex items-center justify-center gap-1.5">
                                                                <a 
                                                                    href="{{ route('transactions.pdf', $tx->id) }}?action=preview" 
                                                                    target="_blank"
                                                                    class="inline-flex items-center px-2 py-1 text-[10px] font-bold border border-teal-600 rounded text-teal-700 bg-teal-50 hover:bg-teal-100 transition duration-150 shadow-sm"
                                                                    title="Lihat Terlebih Dahulu"
                                                                >
                                                                    👁 Lihat
                                                                </a>
                                                                <a 
                                                                    href="{{ route('transactions.pdf', $tx->id) }}?action=download" 
                                                                    class="inline-flex items-center px-2 py-1 text-[10px] font-bold border border-teal-500 rounded text-white bg-teal-600 hover:bg-teal-700 transition duration-150 shadow-sm"
                                                                    title="Unduh PDF"
                                                                >
                                                                    📥 Unduh
                                                                </a>
                                                            </div>
                                                        @else
                                                            <span class="text-gray-400 font-medium">-</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="px-6 py-10 text-center text-gray-400 text-sm">Anda belum memiliki riwayat pengajuan transaksi.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- TAB 2: STOK ALAT (FULL WIDTH CATALOG) -->
                    <div x-show="activeTab === 'stok_alat'" class="space-y-6" x-transition>
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100 p-6">
                            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Stok Ketersediaan Alat</h3>
                                    <p class="text-sm text-gray-500">Daftar alat praktikum laboratorium medis yang tersedia untuk dipinjam.</p>
                                </div>
                                <div class="w-full sm:w-64">
                                    <input 
                                        type="text" 
                                        x-model="searchAlat" 
                                        placeholder="Cari alat medis..." 
                                        class="w-full text-xs px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-teal-500 focus:border-teal-500"
                                    />
                                </div>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr class="text-xs font-semibold text-gray-500 uppercase tracking-wider text-center">
                                            <th class="px-4 py-3 text-left">Kode Alat</th>
                                            <th class="px-4 py-3 text-left">Nama Alat</th>
                                            <th class="px-4 py-3">Merk/Type</th>
                                            <th class="px-4 py-3">Jumlah Tersedia Untuk Dipinjam</th>
                                            <th class="px-4 py-3">Lokasi Rak</th>
                                            <th class="px-4 py-3">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-150 text-xs text-center">
                                        @forelse($items->where('kategori', 'alat') as $item)
                                            <tr 
                                                x-show="searchAlat === '' || '{{ strtolower($item->nama_barang) }}'.includes(searchAlat.toLowerCase()) || '{{ strtolower($item->kode_barang) }}'.includes(searchAlat.toLowerCase())"
                                                class="hover:bg-gray-50/50 transition-colors duration-150"
                                            >
                                                <td class="px-4 py-4 text-left font-bold text-gray-800">{{ $item->kode_barang }}</td>
                                                <td class="px-4 py-4 text-left font-medium text-gray-950">{{ $item->nama_barang }}</td>
                                                <td class="px-4 py-4 text-gray-600">{{ $item->merk_tipe ?: '-' }}</td>
                                                <td class="px-4 py-4 font-bold text-teal-700 bg-teal-50/50 rounded-sm">
                                                    {{ $item->stok_tersedia }} <span class="text-gray-400 font-normal">/ {{ $item->stok_total }} {{ $item->satuan }}</span>
                                                </td>
                                                <td class="px-4 py-4 text-gray-500">{{ $item->lokasi_rak }}</td>
                                                <td class="px-4 py-4">
                                                    @if($item->stok_tersedia > 0 && $item->status !== 'rusak')
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-green-50 text-green-700 border border-green-200">
                                                            Tersedia
                                                        </span>
                                                    @elseif($item->status === 'rusak')
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-red-50 text-red-700 border border-red-200">
                                                            Rusak/Maintenance
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                            Dipinjam Habis
                                                        </span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="px-6 py-10 text-center text-gray-400 text-sm">Tidak ada stok alat tersedia.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 3: STOK BAHAN (FULL WIDTH CATALOG) -->
                    <div x-show="activeTab === 'stok_bahan'" class="space-y-6" x-transition>
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100 p-6">
                            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Stok Ketersediaan Bahan</h3>
                                    <p class="text-sm text-gray-500">Daftar bahan medis habis pakai yang tersedia untuk praktikum.</p>
                                </div>
                                <div class="w-full sm:w-64">
                                    <input 
                                        type="text" 
                                        x-model="searchBahan" 
                                        placeholder="Cari bahan praktikum..." 
                                        class="w-full text-xs px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-teal-500 focus:border-teal-500"
                                    />
                                </div>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr class="text-xs font-semibold text-gray-500 uppercase tracking-wider text-center">
                                            <th class="px-4 py-3 text-left">Kode Bahan</th>
                                            <th class="px-4 py-3 text-left">Nama Bahan</th>
                                            <th class="px-4 py-3">Merk/Type</th>
                                            <th class="px-4 py-3">Status</th>
                                            <th class="px-4 py-3">Lokasi Rak</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-150 text-xs text-center">
                                        @forelse($items->where('kategori', 'bahan') as $item)
                                            <tr 
                                                x-show="searchBahan === '' || '{{ strtolower($item->nama_barang) }}'.includes(searchBahan.toLowerCase()) || '{{ strtolower($item->kode_barang) }}'.includes(searchBahan.toLowerCase())"
                                                class="hover:bg-gray-50/50 transition-colors duration-150"
                                            >
                                                <td class="px-4 py-4 text-left font-bold text-gray-800">{{ $item->kode_barang }}</td>
                                                <td class="px-4 py-4 text-left font-medium text-gray-950">{{ $item->nama_barang }}</td>
                                                <td class="px-4 py-4 text-gray-600">{{ $item->merk_tipe ?: '-' }}</td>
                                                <td class="px-4 py-4">
                                                    @if($item->dynamic_stock > 0)
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-green-50 text-green-700 border border-green-200">
                                                            Tersedia
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-red-50 text-red-700 border border-red-200">
                                                             Habis
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-4 text-gray-500">{{ $item->lokasi_rak }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="px-6 py-10 text-center text-gray-400 text-sm">Tidak ada stok bahan tersedia.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 4: PEMINJAMAN RUANGAN VIEW -->
                    <div x-show="activeTab === 'peminjaman_ruangan'" class="space-y-6" style="display: none;" x-transition>
                        


                        <div class="flex items-center justify-between mb-5">
                            <div>
                                <h1 class="text-xl font-bold text-gray-900 font-sans font-bold">Peminjaman Ruangan</h1>
                                <p class="text-xs text-gray-500 mt-1">Pilih ruangan yang ingin dipinjam dan atur jadwal penggunaan.</p>
                            </div>
                            <button @click="showBookingForm = !showBookingForm"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-teal-700 hover:bg-teal-800 text-white rounded-lg text-sm font-semibold shadow-sm transition font-semibold">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Buat Peminjaman
                            </button>
                        </div>

                        {{-- BOOKING FORM --}}
                        <div x-show="showBookingForm" x-transition class="bg-white border border-gray-200 rounded-xl shadow-sm mb-6 overflow-hidden" style="display:none;">
                            <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-teal-50 to-white flex items-center justify-between">
                                <div>
                                    <h2 class="text-sm font-bold text-gray-900 font-sans font-bold">Form Peminjaman Ruangan</h2>
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
                                        <button type="button" @click="addRoomRow()" class="text-xs text-teal-700 hover:text-teal-900 font-bold flex items-center gap-1 border border-teal-300 rounded px-2 py-1 hover:bg-teal-50 transition">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                            Tambah Ruangan
                                        </button>
                                    </div>
                                    <div class="space-y-3" id="room-rows">
                                        <template x-for="(row, idx) in roomRows" :key="idx">
                                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                                <div class="flex items-center justify-between mb-3">
                                                    <span class="text-xs font-bold text-teal-700 font-bold">Ruangan #<span x-text="idx + 1"></span></span>
                                                    <button type="button" x-show="roomRows.length > 1" @click="removeRoomRow(idx)" class="text-red-400 hover:text-red-600 text-xs font-semibold">
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
                                    <button type="submit" class="px-5 py-2 text-sm bg-teal-700 hover:bg-teal-800 text-white rounded-lg font-semibold shadow-sm transition font-semibold">
                                        Ajukan Peminjaman
                                    </button>
                                </div>
                            </form>
                        </div>

                        {{-- HORIZONTAL TAB: Ruangan Tersedia / Digunakan --}}
                        <div class="flex border-b border-gray-200 mb-5 gap-1">
                            <button @click="roomActiveTab = 'tersedia'" :class="roomActiveTab === 'tersedia' ? 'border-b-2 border-teal-700 text-teal-800 font-bold' : 'text-gray-500 hover:text-gray-700'" class="px-5 py-2.5 text-sm font-medium transition">
                                🏫 Ruangan Tersedia
                                <span class="ml-1 text-xs bg-teal-100 text-teal-800 px-1.5 py-0.5 rounded-full font-bold">{{ $availableRooms->count() }}</span>
                            </button>
                            <button @click="roomActiveTab = 'digunakan'" :class="roomActiveTab === 'digunakan' ? 'border-b-2 border-amber-500 text-amber-700 font-bold' : 'text-gray-500 hover:text-gray-700'" class="px-5 py-2.5 text-sm font-medium transition">
                                🔒 Ruangan Digunakan
                                <span class="ml-1 text-xs bg-amber-100 text-amber-800 px-1.5 py-0.5 rounded-full font-bold">{{ $usedRoomItems->count() }}</span>
                            </button>
                            <button @click="roomActiveTab = 'riwayat'" :class="roomActiveTab === 'riwayat' ? 'border-b-2 border-blue-500 text-blue-700 font-bold' : 'text-gray-500 hover:text-gray-700'" class="px-5 py-2.5 text-sm font-medium transition">
                                📋 Riwayat Saya
                                <span class="ml-1 text-xs bg-blue-100 text-blue-800 px-1.5 py-0.5 rounded-full font-bold">{{ $myBookings->count() }}</span>
                            </button>
                        </div>

                        {{-- FILTER BAR --}}
                        <div x-show="roomActiveTab !== 'riwayat'" class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 mb-5">
                            <form method="GET" action="{{ route('dashboard') }}" class="flex flex-wrap gap-3 items-end">
                                <input type="hidden" name="activeTab" value="peminjaman_ruangan" />
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
                                    <button type="submit" class="px-4 py-2 text-xs bg-teal-700 hover:bg-teal-800 text-white rounded-lg font-semibold transition font-semibold">Filter</button>
                                    <a href="{{ route('dashboard') }}?activeTab=peminjaman_ruangan" class="px-3 py-2 text-xs border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 font-medium">Reset</a>
                                </div>
                            </form>
                        </div>

                        {{-- TAB: RUANGAN TERSEDIA --}}
                        <div x-show="roomActiveTab === 'tersedia'" x-transition>
                            @if($availableRooms->isEmpty())
                                <div class="bg-white rounded-xl border border-gray-200 py-16 text-center shadow-sm">
                                    <div class="text-4xl mb-3 font-sans">🏫</div>
                                    <p class="text-gray-500 font-semibold font-sans">Tidak ada ruangan tersedia</p>
                                    <p class="text-xs text-gray-400 mt-1 font-sans">Coba filter tanggal lain atau hubungi laboran.</p>
                                </div>
                            @else
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($availableRooms as $room)
                                <div class="bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition overflow-hidden">
                                    <div class="bg-gradient-to-br from-teal-600 to-teal-800 px-5 py-4 text-white">
                                        <div class="flex items-start justify-between">
                                            <div>
                                                <p class="text-xs font-bold uppercase tracking-widest text-teal-200 font-sans">{{ $room->kode_ruangan }}</p>
                                                <h3 class="text-sm font-bold mt-0.5 font-sans">{{ $room->nama_ruangan }}</h3>
                                            </div>
                                            <span class="bg-teal-500/50 text-white text-xs px-2 py-0.5 rounded-full font-semibold font-sans">Tersedia</span>
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
                                        <p class="text-xs text-gray-400 mt-1 font-sans">{{ Str::limit($room->deskripsi, 70) }}</p>
                                        @endif
                                        <button type="button" @click="addRoomToForm({{ $room->id }}, '{{ addslashes($room->nama_ruangan) }}')"
                                            class="mt-2 w-full text-xs py-1.5 px-3 bg-teal-50 hover:bg-teal-100 text-teal-800 rounded-lg font-semibold border border-teal-200 transition font-sans">
                                            + Tambah ke Peminjaman
                                        </button>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @endif
                        </div>

                        {{-- TAB: RUANGAN DIGUNAKAN --}}
                        <div x-show="roomActiveTab === 'digunakan'" x-transition style="display:none;">
                            @if($usedRoomItems->isEmpty())
                                <div class="bg-white rounded-xl border border-gray-200 py-16 text-center shadow-sm">
                                    <div class="text-4xl mb-3 font-sans">✅</div>
                                    <p class="text-gray-500 font-semibold font-sans">Tidak ada ruangan yang sedang digunakan</p>
                                    <p class="text-xs text-gray-400 mt-1 font-sans">Pada rentang waktu yang dipilih, semua ruangan tersedia.</p>
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
                                                    <p class="text-xs text-amber-700 font-bold uppercase font-sans">{{ $item->room?->kode_ruangan }}</p>
                                                    <h3 class="text-sm font-bold text-gray-900 font-sans font-bold">{{ $item->room?->nama_ruangan }}</h3>
                                                    <p class="text-xs text-gray-550 mt-0.5 font-sans">Kapasitas: {{ $item->room?->kapasitas }} orang &nbsp;|&nbsp; {{ $item->room?->lokasi }}</p>
                                                </div>
                                                <span class="text-xs px-2 py-0.5 rounded-full font-semibold font-sans {{ $item->booking->status === 'disetujui' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                    {{ strtoupper($item->booking->status) }}
                                                </span>
                                            </div>
                                            <div class="mt-2 flex flex-wrap gap-3 text-xs text-gray-600">
                                                <span>📅 {{ \Carbon\Carbon::parse($item->tanggal_mulai)->format('d-m-Y') }} s.d. {{ \Carbon\Carbon::parse($item->tanggal_selesai)->format('d-m-Y') }}</span>
                                                <span>⏰ {{ $item->waktu_mulai }} – {{ $item->waktu_selesai }}</span>
                                                <span>👤 {{ $item->booking->is_insidentil ? $item->booking->peminjam_insidentil : $item->booking->user?->name }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @endif
                        </div>

                        {{-- TAB: RIWAYAT SAYA --}}
                        <div x-show="roomActiveTab === 'riwayat'" x-transition style="display:none;">
                            @if($myBookings->isEmpty())
                                <div class="bg-white rounded-xl border border-gray-200 py-16 text-center shadow-sm">
                                    <div class="text-4xl mb-3 font-sans">📋</div>
                                    <p class="text-gray-500 font-semibold font-sans">Belum ada riwayat peminjaman ruangan</p>
                                </div>
                            @else
                            <div class="space-y-4">
                                @foreach($myBookings as $booking)
                                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                                    <div class="px-5 py-4 border-b border-gray-100 flex items-start justify-between">
                                        <div>
                                            <span class="text-xs font-bold text-gray-500 font-sans">RB-{{ str_pad($booking->id, 5, '0', STR_PAD_LEFT) }}</span>
                                            <p class="text-sm font-bold text-gray-900 mt-0.5 font-sans font-bold">{{ $booking->tujuan_penggunaan }}</p>
                                            <p class="text-xs text-gray-550 font-sans">Diajukan: {{ $booking->tanggal_pengajuan->format('d-m-Y H:i') }} &nbsp;|&nbsp; {{ $booking->jumlah_mahasiswa }} mahasiswa</p>
                                        </div>
                                        <div class="flex items-center gap-2 flex-shrink-0">
                                            <span class="text-xs px-2 py-0.5 rounded-full font-semibold font-sans
                                                {{ $booking->status === 'disetujui' ? 'bg-green-100 text-green-800' :
                                                   ($booking->status === 'ditolak' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                                {{ strtoupper($booking->status) }}
                                            </span>
                                            @if($booking->status === 'disetujui')
                                            <a href="{{ route('room-bookings.pdf', $booking->id) }}?action=preview" target="_blank" class="text-xs px-2 py-1 bg-teal-50 text-teal-700 rounded border border-teal-200 font-semibold hover:bg-teal-100 transition font-sans">
                                                PDF
                                            </a>
                                            @endif
                                            @if($booking->status === 'pending')
                                            <form method="POST" action="{{ route('room-bookings.destroy', $booking->id) }}" onsubmit="return confirm('Batalkan peminjaman ini?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-xs px-2 py-1 bg-red-50 text-red-700 rounded border border-red-200 font-semibold hover:bg-red-150 transition font-sans">Batal</button>
                                            </form>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="px-5 py-3 space-y-2">
                                        @foreach($booking->items as $item)
                                        <div class="flex flex-wrap gap-2 text-xs text-gray-600 bg-gray-50 rounded-lg px-3 py-1.5 border border-gray-150">
                                            <span class="font-semibold text-teal-800">{{ $item->room?->nama_ruangan ?? '-' }}</span>
                                            <span>📅 {{ \Carbon\Carbon::parse($item->tanggal_mulai)->format('d-m-Y') }} s.d. {{ \Carbon\Carbon::parse($item->tanggal_selesai)->format('d-m-Y') }}</span>
                                            <span>⏰ {{ $item->waktu_mulai }} – {{ $item->waktu_selesai }}</span>
                                        </div>
                                        @endforeach
                                        @if($booking->catatan_laboran)
                                        <p class="text-xs text-red-600 font-semibold italic px-1 font-sans">Catatan Laboran: {{ $booking->catatan_laboran }}</p>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @endif
                        </div>

                    </div>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>

<x-transaction-edit-modal :items="$items" :packages="$packages" />
