<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dashboard Laboran - Si-Lab ') . ucwords(str_replace('_', ' ', Auth::user()->jurusan)) }}
            </h2>
            <span class="text-sm bg-teal-50 text-teal-700 font-medium px-3 py-1 rounded-full border border-teal-200">
                Staff NIDN: {{ Auth::user()->nomor_induk }}
            </span>
        </div>
    </x-slot>

    <div class="py-10 bg-gray-50 min-h-screen" 
         x-data="{ 
             activeTab: (new URLSearchParams(window.location.search)).get('tab') || localStorage.getItem('laboranActiveTab') || 'inventaris_alat', 
             isCreateAlatOpen: false, 
             isCreateBebasLabOpen: false,
             bebasLabUserId: '',
             bebasLabStudentName: '',
             bebasLabNomorSurat: '',
             searchBebasLabStudent: '',
             isCreateBahanOpen: false, 
             isEditOpen: false, 
             isImportOpen: false,
             isReturnModalOpen: false,
             isCreatePackageOpen: false,
             isEditPackageOpen: false,
             expandedVerifikasiUser: null,
             expandedDalamPeminjamanUser: null,
             expandedCatatanUser: null,
             expandedPengembalianUser: null,
             expandedVerifikasiBahanUser: null,
             expandedDalamPeminjamanBahanUser: null,
             expandedRejectSuspendAlatUser: null,
             expandedRejectSuspendBahanUser: null,
             returnTxId: '',
             returnStatus: 'selesai',
             returnCatatan: '',
             searchAlat: '',
             filterAlatStatus: 'semua',
             searchBahan: '',
             filterStartDate: '',
             filterEndDate: '',
             isCreateIncidentalOpen: false,
             incidentalType: 'peminjaman_alat',
             incidentalPackagesList: [],
             incidentalItemsList: [],
             packageItemsList: [],
             allItems: {{ $items->toJson() }},
             allPackages: {{ $packages->toJson() }},
             editPackage: { id: '', nama_paket: '', deskripsi: '', items: [] },
             editItem: { id: '', kode_barang: '', nama_barang: '', merk_tipe: '', satuan: 'pcs', kategori: 'alat', stok_total: 0, stok_tersedia: 0, jumlah_baik: 0, jumlah_perbaikan: 0, jumlah_rusak: 0, lokasi_rak: '' },
             isDateInRange(dateStr) {
                 if (!this.filterStartDate && !this.filterEndDate) return true;
                 let d = dateStr.substring(0, 10);
                 if (this.filterStartDate && d < this.filterStartDate) return false;
                 if (this.filterEndDate && d > this.filterEndDate) return false;
                 return true;
             },
             getFinalIncidentalItems() {
                 let list = [];
                 this.incidentalPackagesList.forEach(pkg => {
                     if (!pkg.package_id) return;
                     let dbPkg = this.allPackages.find(p => p.id == pkg.package_id);
                     if (!dbPkg) return;
                     dbPkg.items.forEach(pi => {
                         if ((this.incidentalType === 'peminjaman_alat' && pi.item.kategori === 'alat') ||
                             (this.incidentalType === 'permintaan_bahan' && pi.item.kategori === 'bahan')) {
                             list.push({
                                 item_id: pi.item_id,
                                 jumlah_diminta: pi.jumlah * pkg.package_qty,
                                 package_id: pkg.package_id,
                                 package_qty: pkg.package_qty
                             });
                         }
                     });
                 });
                 this.incidentalItemsList.forEach(item => {
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
             getIncidentalItemTotalRequested(itemId) {
                 let total = 0;
                 this.getFinalIncidentalItems().forEach(fItem => {
                     if (fItem.item_id == itemId) {
                         total += parseInt(fItem.jumlah_diminta) || 0;
                     }
                  });
                  return total;
             },
             hasIncidentalStockError() {
                 let hasError = false;
                 let uniqueItemIds = [...new Set(this.getFinalIncidentalItems().map(f => f.item_id))];
                 uniqueItemIds.forEach(id => {
                     let dbItem = this.allItems.find(i => i.id == id);
                     if (dbItem) {
                         let totalReq = this.getIncidentalItemTotalRequested(id);
                         if (totalReq > dbItem.stok_tersedia) {
                             hasError = true;
                         }
                     }
                 });
                 return hasError;
             },
             hasVisibleTransactions(dateList) {
                 return dateList.some(d => this.isDateInRange(d));
             }
         }"
         x-init="$watch('activeTab', val => localStorage.setItem('laboranActiveTab', val))">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Success/Error Alerts -->
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

            <div class="flex flex-col md:flex-row gap-8">
                
                <!-- SIDEBAR VERTICAL (Left Side) -->
                <div class="w-full md:w-64 flex-shrink-0">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5 flex flex-col items-center text-center space-y-4 sticky top-6">
                        
                        <!-- Logo & Title Area -->
                        <div class="flex flex-col items-center py-2">
                            <img src="{{ asset('images/logo_poltekkes.png') }}" class="w-20 h-20 object-contain mb-3 bg-teal-50/10 p-1.5 rounded-full border border-teal-100/50 shadow-sm" alt="Logo Poltekkes">
                            <h3 class="text-sm font-bold text-teal-850 text-teal-800 tracking-wide leading-tight">Si-Lab Keperawatan</h3>
                            <p class="text-[9px] text-gray-400 font-bold uppercase tracking-widest mt-1.5">Poltekkes Jakarta I</p>
                        </div>

                        <!-- Sidebar Main Menus (Vertical Options) -->
                        <div class="w-full space-y-2.5 pt-4 border-t border-gray-100">
                            
                            <!-- 5. Inventaris Alat option -->
                            <button 
                                @click="activeTab = 'inventaris_alat'"
                                :class="activeTab === 'inventaris_alat' ? 'bg-teal-600 text-white font-semibold shadow-md ring-1 ring-teal-500' : 'bg-white text-gray-700 hover:bg-slate-50 border border-slate-200'"
                                class="w-full text-left px-3 py-2.5 rounded-lg text-xs transition-all duration-150 flex items-center justify-between shadow-sm"
                            >
                                <span class="flex items-center gap-2.5">
                                    <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                    </svg>
                                    Inventaris Alat
                                </span>
                            </button>
                            <!-- 6. Inventaris Bahan option -->
                            <button 
                                @click="activeTab = 'inventaris_bahan'"
                                :class="activeTab === 'inventaris_bahan' ? 'bg-teal-600 text-white font-semibold shadow-md ring-1 ring-teal-500' : 'bg-white text-gray-700 hover:bg-slate-50 border border-slate-200'"
                                class="w-full text-left px-3 py-2.5 rounded-lg text-xs transition-all duration-150 flex items-center justify-between shadow-sm"
                            >
                                <span class="flex items-center gap-2.5">
                                    <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                                    </svg>
                                    Inventaris Bahan
                                </span>
                            </button>

                            <!-- Paket Alat & Bahan option -->
                            <button 
                                @click="activeTab = 'paket_list'"
                                :class="activeTab.startsWith('paket_') ? 'bg-teal-600 text-white font-semibold shadow-md ring-1 ring-teal-500' : 'bg-white text-gray-700 hover:bg-slate-50 border border-slate-200'"
                                class="w-full text-left px-4 py-3 rounded-lg text-xs transition-all duration-150 flex items-center justify-between shadow-sm"
                            >
                                <span class="flex items-center gap-2.5">
                                    <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                    </svg>
                                    Paket Praktikum
                                </span>
                            </button>

                            <!-- Peminjaman/Permintaan option -->
                            <button 
                                @click="activeTab = 'verifikasi'"
                                :class="(!activeTab.startsWith('inventaris_') && !activeTab.startsWith('paket_') && activeTab !== 'bebas_lab') ? 'bg-teal-600 text-white font-semibold shadow-md ring-1 ring-teal-500' : 'bg-white text-gray-700 hover:bg-slate-50 border border-slate-200'"
                                class="w-full text-left px-4 py-3 rounded-lg text-xs transition-all duration-150 flex items-center justify-between shadow-sm"
                            >
                                <span class="flex items-center gap-2.5">
                                    <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Peminjaman & Permintaan
                                </span>
                                @if($pendingTransactions->count() > 0)
                                    <span class="bg-red-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full" x-show="activeTab.startsWith('inventaris_') || activeTab.startsWith('paket_') || activeTab === 'bebas_lab'">
                                        {{ $pendingTransactions->count() }}
                                    </span>
                                @endif
                            </button>

                            <!-- Bebas Laboratorium option -->
                            <button 
                                @click="activeTab = 'bebas_lab'"
                                :class="activeTab === 'bebas_lab' ? 'bg-teal-600 text-white font-semibold shadow-md ring-1 ring-teal-500' : 'bg-white text-gray-700 hover:bg-slate-50 border border-slate-200'"
                                class="w-full text-left px-4 py-3 rounded-lg text-xs transition-all duration-150 flex items-center justify-between shadow-sm"
                            >
                                <span class="flex items-center gap-2.5">
                                    <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                                    </svg>
                                    Bebas Laboratorium
                                </span>
                                @if($students->filter(fn($s) => !$s->bebasLabCertificate)->count() > 0)
                                    <span class="bg-amber-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full">
                                        {{ $students->filter(fn($s) => !$s->bebasLabCertificate)->count() }}
                                    </span>
                                @endif
                            </button>

                            <!-- Peminjaman Ruangan option -->
                            <a 
                                href="{{ route('laboran.room-bookings.index') }}"
                                class="w-full text-left px-4 py-3 rounded-lg text-xs transition-all duration-150 flex items-center justify-between shadow-sm bg-white text-gray-700 hover:bg-slate-50 border border-slate-200 font-medium"
                            >
                                <span class="flex items-center gap-2.5">
                                    <svg class="h-4.5 w-4.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                    Peminjaman Ruangan
                                </span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- CONTENT AREA (Right Side) -->
                <div class="flex-grow">
                    
                    <!-- SECONDARY HORIZONTAL NAVIGATION TABS (NESTED) -->

                    <!-- Group 2: Submenus for Peminjaman & Permintaan -->
                    <div x-show="!activeTab.startsWith('inventaris_') && !activeTab.startsWith('paket_') && activeTab !== 'bebas_lab'" class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6" x-transition>
                        <div class="flex flex-wrap gap-2 bg-gray-200/40 p-1.5 rounded-md border border-gray-200">
                            <button 
                                @click="activeTab = 'verifikasi'"
                                :class="activeTab === 'verifikasi' ? 'bg-teal-600 text-white font-semibold shadow-sm' : 'bg-white text-gray-700 hover:bg-slate-50 border border-slate-200'"
                                class="px-3 py-2 rounded-md text-xs transition-all duration-150 flex items-center gap-1.5"
                            >
                                <span>Verifikasi</span>
                                @if($pendingTransactions->count() > 0)
                                    <span class="bg-red-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full">
                                        {{ $pendingTransactions->count() }}
                                    </span>
                                @endif
                            </button>
                            <button 
                                @click="activeTab = 'alat_dalam_peminjaman'"
                                :class="activeTab === 'alat_dalam_peminjaman' ? 'bg-teal-600 text-white font-semibold shadow-sm' : 'bg-white text-gray-700 hover:bg-slate-50 border border-slate-200'"
                                class="px-3 py-2 rounded-md text-xs transition-all duration-150 flex items-center gap-1.5"
                            >
                                <span>Alat Dalam Peminjaman</span>
                                @if($alatActiveLoans->count() > 0)
                                    <span class="bg-indigo-600 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full">
                                        {{ $alatActiveLoans->count() }}
                                    </span>
                                @endif
                            </button>
                            <button 
                                @click="activeTab = 'alat_dikembalikan_dengan_catatan'"
                                :class="activeTab === 'alat_dikembalikan_dengan_catatan' ? 'bg-teal-600 text-white font-semibold shadow-sm' : 'bg-white text-gray-700 hover:bg-slate-50 border border-slate-200'"
                                class="px-3 py-2 rounded-md text-xs transition-all duration-150 flex items-center gap-1.5"
                            >
                                <span>Alat Dikembalikan Dengan Catatan</span>
                                @if($alatReturnedWithNotes->count() > 0)
                                    <span class="bg-amber-600 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full">
                                        {{ $alatReturnedWithNotes->count() }}
                                    </span>
                                @endif
                            </button>
                            <button 
                                @click="activeTab = 'alat_dikembalikan_sepenuhnya'"
                                :class="activeTab === 'alat_dikembalikan_sepenuhnya' ? 'bg-teal-600 text-white font-semibold shadow-sm' : 'bg-white text-gray-700 hover:bg-slate-50 border border-slate-200'"
                                class="px-3 py-2 rounded-md text-xs transition-all duration-150 flex items-center gap-1.5"
                            >
                                <span>Alat Dikembalikan Sepenuhnya</span>
                                @if(isset($alatReturnedFully) && $alatReturnedFully->count() > 0)
                                    <span class="bg-emerald-600 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full">
                                        {{ $alatReturnedFully->count() }}
                                    </span>
                                @endif
                            </button>
                            <button 
                                @click="activeTab = 'alat_ditolak_ditangguhkan'"
                                :class="activeTab === 'alat_ditolak_ditangguhkan' ? 'bg-teal-600 text-white font-semibold shadow-sm' : 'bg-white text-gray-700 hover:bg-slate-50 border border-slate-200'"
                                class="px-3 py-2 rounded-md text-xs transition-all duration-150 flex items-center gap-1.5"
                            >
                                <span>Peminjaman Alat Ditolak/Ditangguhkan</span>
                                @if($alatRejectedOrSuspended->count() > 0)
                                    <span class="bg-rose-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full">
                                        {{ $alatRejectedOrSuspended->count() }}
                                    </span>
                                @endif
                            </button>
                            <button 
                                @click="activeTab = 'bahan_disetujui'"
                                :class="activeTab === 'bahan_disetujui' ? 'bg-teal-600 text-white font-semibold shadow-sm' : 'bg-white text-gray-700 hover:bg-slate-50 border border-slate-200'"
                                class="px-3 py-2 rounded-md text-xs transition-all duration-150 flex items-center gap-1.5"
                            >
                                <span>Permintaan Bahan Disetujui</span>
                                @if($bahanApproved->count() > 0)
                                    <span class="bg-blue-600 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full">
                                        {{ $bahanApproved->count() }}
                                    </span>
                                @endif
                            </button>
                            <button 
                                @click="activeTab = 'bahan_ditolak_ditangguhkan'"
                                :class="activeTab === 'bahan_ditolak_ditangguhkan' ? 'bg-teal-600 text-white font-semibold shadow-sm' : 'bg-white text-gray-700 hover:bg-slate-50 border border-slate-200'"
                                class="px-3 py-2 rounded-md text-xs transition-all duration-150 flex items-center gap-1.5"
                            >
                                <span>Permintaan Bahan Ditolak/Ditangguhkan</span>
                                @if($bahanRejectedOrSuspended->count() > 0)
                                    <span class="bg-rose-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full">
                                        {{ $bahanRejectedOrSuspended->count() }}
                                    </span>
                                @endif
                            </button>
                        </div>
                        <button 
                            @click="isCreateIncidentalOpen = true; incidentalPackagesList = []; incidentalItemsList = []"
                            class="px-4 py-2 border border-transparent rounded text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 transition duration-150 shadow-sm flex items-center gap-1.5 self-start lg:self-auto"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Pencatatan Insidentil
                        </button>
                    </div>

                    <!-- GLOBAL DATE RANGE FILTER FOR TRANSACTIONS -->
                    <div x-show="!activeTab.startsWith('inventaris_') && !activeTab.startsWith('paket_') && activeTab !== 'bebas_lab'" class="bg-white rounded-lg p-4 mb-6 shadow-sm border border-gray-100 flex flex-col sm:flex-row gap-4 items-center justify-between" x-transition>
                        <div>
                            <h4 class="text-xs font-bold text-gray-950 uppercase">Filter Tanggal Pengajuan</h4>
                            <p class="text-[10px] text-gray-400">Tampilkan riwayat peminjaman / permintaan berdasarkan range tanggal pengajuan.</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-3">
                            <div class="flex items-center gap-1.5 text-xs text-gray-750">
                                <span>Dari:</span>
                                <input type="date" x-model="filterStartDate" class="border-gray-300 rounded text-xs focus:ring-teal-500 focus:border-teal-500 py-1" />
                            </div>
                            <div class="flex items-center gap-1.5 text-xs text-gray-750">
                                <span>Sampai:</span>
                                <input type="date" x-model="filterEndDate" class="border-gray-300 rounded text-xs focus:ring-teal-500 focus:border-teal-500 py-1" />
                            </div>
                            <button type="button" @click="filterStartDate = ''; filterEndDate = ''" class="text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold px-2.5 py-1 rounded border border-slate-200 transition-colors">Reset</button>
                        </div>
                    </div>

                    <!-- TAB: INVENTARIS ALAT VIEW -->
                    <div x-show="activeTab === 'inventaris_alat'" class="space-y-6">
                        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Inventaris Alat Lab</h3>
                                    <p class="text-sm text-gray-500">Kelola daftar alat medis dan ketersediaan peminjaman.</p>
                                    <div class="flex flex-col sm:flex-row gap-3 mt-3">
                                        <input 
                                            type="text" 
                                            x-model="searchAlat" 
                                            placeholder="Cari nama/kode alat..." 
                                            class="text-xs border-gray-300 rounded-md focus:ring-teal-500 focus:border-teal-500 py-1.5 px-3 w-full sm:w-64"
                                        />
                                        <select 
                                            x-model="filterAlatStatus" 
                                            class="text-xs border-gray-300 rounded-md focus:ring-teal-500 focus:border-teal-500 py-1.5 px-3"
                                        >
                                            <option value="semua">Semua Status</option>
                                            <option value="tersedia">Status: Tersedia</option>
                                            <option value="dipinjam">Status: Dipinjam</option>
                                            <option value="rusak">Status: Rusak</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <a 
                                        href="{{ route('laboran.inventory.export-excel', 'alat') }}"
                                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-semibold rounded-md text-gray-700 bg-white hover:bg-gray-50 transition duration-150 shadow-sm"
                                        title="Unduh Rekap Excel (MS Excel Compatible)"
                                    >
                                        📥 Unduh Excel
                                    </a>
                                    <a 
                                        href="{{ route('laboran.inventory.print-pdf', 'alat') }}?action=preview"
                                        target="_blank"
                                        class="inline-flex items-center px-4 py-2 border border-teal-500 text-sm font-semibold rounded-md text-teal-700 bg-teal-50 hover:bg-teal-100 transition duration-150 shadow-sm"
                                        title="Cetak/Lihat Rekap PDF"
                                    >
                                        👁 Lihat PDF
                                    </a>
                                    <button 
                                        @click="isImportOpen = true"
                                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-semibold rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 transition duration-150 shadow-sm"
                                    >
                                        <svg class="-ml-1 mr-2 h-4 w-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        Impor Google Sheet
                                    </button>
                                    <button 
                                        @click="isCreateAlatOpen = true"
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-semibold rounded-md text-white bg-teal-600 hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 transition duration-150 shadow-sm"
                                    >
                                        <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                        Tambah Inventaris Alat
                                    </button>
                                </div>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr class="text-xs font-semibold text-gray-500 uppercase tracking-wider text-center">
                                            <th class="px-3 py-3 text-left">Kode Alat</th>
                                            <th class="px-3 py-3 text-left">Nama Alat</th>
                                            <th class="px-3 py-3">Merk/Type</th>
                                            <th class="px-3 py-3">Satuan</th>
                                            <th class="px-2 py-3 font-bold text-slate-800">Total</th>
                                            <th class="px-2 py-3 text-green-700">Baik</th>
                                            <th class="px-2 py-3 text-yellow-600">Perbaikan</th>
                                            <th class="px-2 py-3 text-red-600">Rusak</th>
                                            <th class="px-2 py-3 text-indigo-750">Di Pinjam</th>
                                            <th class="px-2 py-3 text-teal-700">Ketersediaan</th>
                                            <th class="px-3 py-3">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-150 text-xs text-center">
                                        @forelse($items->where('kategori', 'alat') as $item)
                                            <tr 
                                                x-show="(searchAlat === '' || '{{ strtolower(addslashes($item->nama_barang)) }}'.includes(searchAlat.toLowerCase()) || '{{ strtolower($item->kode_barang) }}'.includes(searchAlat.toLowerCase())) && (filterAlatStatus === 'semua' || '{{ $item->status }}' === filterAlatStatus)"
                                                class="hover:bg-gray-50/50 transition-colors duration-150"
                                            >
                                                <td class="px-3 py-4 text-left font-bold text-gray-800">{{ $item->kode_barang }}</td>
                                                <td class="px-3 py-4 text-left font-medium text-gray-950">{{ $item->nama_barang }}</td>
                                                <td class="px-3 py-4 text-gray-655">{{ $item->merk_tipe ?: '-' }}</td>
                                                <td class="px-3 py-4 text-gray-600 italic font-semibold">{{ $item->satuan }}</td>
                                                <td class="px-2 py-4 font-bold text-gray-900">{{ $item->stok_total }}</td>
                                                <td class="px-2 py-4 text-green-700 font-semibold">{{ $item->jumlah_baik }}</td>
                                                <td class="px-2 py-4 text-yellow-600 font-semibold">{{ $item->jumlah_perbaikan }}</td>
                                                <td class="px-2 py-4 text-red-600 font-semibold">{{ $item->jumlah_rusak }}</td>
                                                <td class="px-2 py-4 text-indigo-700 font-semibold">{{ $item->jumlah_dipinjam }}</td>
                                                <td class="px-2 py-4 text-teal-700 font-bold bg-teal-50/50 rounded-sm">{{ $item->stok_tersedia }}</td>
                                                <td class="px-3 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                                    <button 
                                                        @click="
                                                            editItem = {
                                                                id: '{{ $item->id }}',
                                                                kode_barang: '{{ $item->kode_barang }}',
                                                                nama_barang: '{{ addslashes($item->nama_barang) }}',
                                                                merk_tipe: '{{ addslashes($item->merk_tipe) }}',
                                                                satuan: '{{ $item->satuan }}',
                                                                kategori: 'alat',
                                                                stok_total: {{ $item->stok_total }},
                                                                stok_tersedia: {{ $item->stok_tersedia }},
                                                                jumlah_baik: {{ $item->jumlah_baik }},
                                                                jumlah_perbaikan: {{ $item->jumlah_perbaikan }},
                                                                jumlah_rusak: {{ $item->jumlah_rusak }},
                                                                lokasi_rak: '{{ addslashes($item->lokasi_rak) }}'
                                                            };
                                                            isEditOpen = true;
                                                        "
                                                        class="text-teal-600 hover:text-teal-900 transition duration-150"
                                                    >
                                                        Edit
                                                    </button>
                                                    <form method="POST" action="{{ route('items.destroy', $item->id) }}" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" onclick="return confirm('Hapus alat ini?')" class="text-red-500 hover:text-red-700 transition duration-150">Hapus</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="10" class="px-6 py-10 text-center text-gray-400 text-sm">Belum ada inventaris alat.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- TAB: INVENTARIS BAHAN VIEW -->
                    <div x-show="activeTab === 'inventaris_bahan'" class="space-y-6">


                        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Inventaris Bahan Lab</h3>
                                    <p class="text-sm text-gray-500">Kelola persediaan bahan praktikum habis pakai.</p>
                                    <div class="flex flex-col sm:flex-row gap-3 mt-3">
                                        <input 
                                            type="text" 
                                            x-model="searchBahan" 
                                            placeholder="Cari nama/kode bahan..." 
                                            class="text-xs border-gray-300 rounded-md focus:ring-teal-500 focus:border-teal-500 py-1.5 px-3 w-full sm:w-64"
                                        />
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <a 
                                        href="{{ route('laboran.inventory.export-excel', 'bahan') }}"
                                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-semibold rounded-md text-gray-700 bg-white hover:bg-gray-50 transition duration-150 shadow-sm"
                                        title="Unduh Rekap Excel (MS Excel Compatible)"
                                    >
                                        📥 Unduh Excel
                                    </a>
                                    <a 
                                        href="{{ route('laboran.inventory.print-pdf', 'bahan') }}?action=preview"
                                        target="_blank"
                                        class="inline-flex items-center px-4 py-2 border border-teal-500 text-sm font-semibold rounded-md text-teal-700 bg-teal-50 hover:bg-teal-100 transition duration-150 shadow-sm"
                                        title="Cetak/Lihat Rekap PDF"
                                    >
                                        👁 Lihat PDF
                                    </a>
                                    <button 
                                        @click="isImportOpen = true"
                                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-semibold rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 transition duration-150 shadow-sm"
                                    >
                                        <svg class="-ml-1 mr-2 h-4 w-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        Impor Google Sheet
                                    </button>
                                    <button 
                                        @click="isCreateBahanOpen = true"
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-semibold rounded-md text-white bg-teal-600 hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 transition duration-150 shadow-sm"
                                    >
                                        <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                        Tambah Inventaris Bahan
                                    </button>
                                </div>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr class="text-xs font-semibold text-gray-500 uppercase tracking-wider text-center">
                                            <th class="px-4 py-3 text-left">Kode Bahan</th>
                                            <th class="px-4 py-3 text-left">Nama Bahan</th>
                                            <th class="px-4 py-3">Merk/Type</th>
                                            <th class="px-4 py-3">Satuan</th>
                                            <th class="px-4 py-3">Stok Bahan Tersedia</th>
                                            <th class="px-4 py-3">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-150 text-xs text-center">
                                        @forelse($items->where('kategori', 'bahan') as $item)
                                            <tr 
                                                x-show="searchBahan === '' || '{{ strtolower(addslashes($item->nama_barang)) }}'.includes(searchBahan.toLowerCase()) || '{{ strtolower($item->kode_barang) }}'.includes(searchBahan.toLowerCase())"
                                                class="hover:bg-gray-50/50 transition-colors duration-150"
                                            >
                                                <td class="px-4 py-4 text-left font-bold text-gray-800">{{ $item->kode_barang }}</td>
                                                <td class="px-4 py-4 text-left font-medium text-gray-950">{{ $item->nama_barang }}</td>
                                                <td class="px-4 py-4 text-gray-650">{{ $item->merk_tipe ?: '-' }}</td>
                                                <td class="px-4 py-4 text-gray-600 italic font-semibold">{{ $item->satuan }}</td>
                                                <td class="px-4 py-4 font-bold text-teal-700 bg-teal-50/50 rounded-sm">{{ $item->stok_tersedia }} / {{ $item->stok_total }}</td>
                                                <td class="px-4 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                                    <button 
                                                        @click="
                                                            editItem = {
                                                                id: '{{ $item->id }}',
                                                                kode_barang: '{{ $item->kode_barang }}',
                                                                nama_barang: '{{ addslashes($item->nama_barang) }}',
                                                                merk_tipe: '{{ addslashes($item->merk_tipe) }}',
                                                                satuan: '{{ $item->satuan }}',
                                                                kategori: 'bahan',
                                                                stok_total: {{ $item->stok_total }},
                                                                stok_tersedia: {{ $item->stok_tersedia }},
                                                                jumlah_baik: 0,
                                                                jumlah_perbaikan: 0,
                                                                jumlah_rusak: 0,
                                                                lokasi_rak: '{{ addslashes($item->lokasi_rak) }}'
                                                            };
                                                            isEditOpen = true;
                                                        "
                                                        class="text-teal-600 hover:text-teal-900 transition duration-150"
                                                    >
                                                        Edit
                                                    </button>
                                                    <form method="POST" action="{{ route('items.destroy', $item->id) }}" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" onclick="return confirm('Hapus bahan ini?')" class="text-red-500 hover:text-red-700 transition duration-150">Hapus</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="px-6 py-10 text-center text-gray-400 text-sm">Belum ada inventaris bahan.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- TAB: PAKET PRAKTIKUM LIST & CRUD -->
                    <div x-show="activeTab === 'paket_list'" class="space-y-6" style="display: none;">
                        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Kelola Paket Alat & Bahan</h3>
                                    <p class="text-sm text-gray-500">Buat paket standar berisi sekelompok alat/bahan untuk mempercepat pengisian form oleh mahasiswa.</p>
                                </div>
                                <button 
                                    @click="
                                        editPackage = { id: '', nama_paket: '', deskripsi: '', items: [] };
                                        packageItemsList = [{ item_id: '', jumlah: 1, search: '' }];
                                        isCreatePackageOpen = true;
                                    "
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-semibold rounded-md text-white bg-teal-600 hover:bg-teal-700 transition duration-150 shadow-sm"
                                >
                                    <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                    Tambah Paket Baru
                                </button>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                @forelse($packages as $pkg)
                                    <div class="border border-gray-200 rounded-lg p-5 bg-gray-50/50 hover:bg-gray-50 transition duration-150 flex flex-col justify-between shadow-sm">
                                        <div>
                                            <div class="flex justify-between items-start">
                                                <h4 class="font-bold text-gray-900 text-sm">{{ $pkg->nama_paket }}</h4>
                                                <div class="flex items-center gap-1.5">
                                                    <button 
                                                        @click="
                                                            editPackage = {
                                                                id: '{{ $pkg->id }}',
                                                                nama_paket: '{{ addslashes($pkg->nama_paket) }}',
                                                                deskripsi: '{{ addslashes($pkg->deskripsi) }}'
                                                            };
                                                            packageItemsList = [
                                                                @foreach($pkg->items as $i)
                                                                    { item_id: '{{ $i->item_id }}', jumlah: {{ $i->jumlah }}, search: '' },
                                                                @endforeach
                                                            ];
                                                            isEditPackageOpen = true;
                                                        "
                                                        class="text-xs text-teal-600 hover:text-teal-900 font-semibold"
                                                    >
                                                        Edit
                                                    </button>
                                                    <span class="text-gray-300">|</span>
                                                    <form method="POST" action="/packages/{{ $pkg->id }}" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" onclick="return confirm('Hapus paket ini?')" class="text-xs text-red-500 hover:text-red-700 font-semibold font-sans">Hapus</button>
                                                    </form>
                                                </div>
                                            </div>
                                            <p class="text-xs text-gray-500 mt-1 mb-4">{{ $pkg->deskripsi ?: 'Tidak ada deskripsi.' }}</p>
                                            
                                            <div class="space-y-1.5 pt-3 border-t border-gray-200/60">
                                                <span class="text-[9px] font-bold text-gray-400 uppercase tracking-wider block">Daftar Barang Paket:</span>
                                                @foreach($pkg->items as $pkgItem)
                                                    <div class="flex justify-between items-center text-xs py-1 border-b border-gray-100 last:border-b-0">
                                                        <span class="font-medium text-gray-700">{{ $pkgItem->item->nama_barang }}</span>
                                                        <span class="text-gray-500 font-bold">{{ $pkgItem->jumlah }} {{ $pkgItem->item->satuan }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="md:col-span-2 text-center py-10 text-gray-400 text-sm border border-dashed border-gray-200 rounded-lg">Belum ada paket praktikum yang ditambahkan.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- TAB: VERIFIKASI VIEW -->
                    <div x-show="activeTab === 'verifikasi'" class="space-y-6">
                        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Verifikasi Pengajuan</h3>
                            <p class="text-sm text-gray-500 mb-6">Persetujuan pengajuan pinjaman alat dan permintaan bahan medis dikelompokkan per-profil mahasiswa.</p>

                            @forelse($pendingTransactions as $userId => $transactions)
                                @php 
                                    $firstTx = $transactions->first(); 
                                    $isInsidentil = $firstTx->is_insidentil;
                                    $borrowerName = $isInsidentil ? $firstTx->peminjam_insidentil : ($firstTx->user ? $firstTx->user->name : 'Akun Terhapus');
                                    $borrowerSub = $isInsidentil ? 'Peminjaman Insidentil (Non-Mahasiswa)' : 'NIM: ' . ($firstTx->user ? $firstTx->user->nomor_induk : '-');
                                @endphp
                                <div x-show="hasVisibleTransactions([ @foreach($transactions as $tx) '{{ $tx->tanggal_pengajuan->format('Y-m-d') }}', @endforeach ])" class="border border-gray-200 rounded-lg mb-4 overflow-hidden shadow-sm bg-white">
                                    <!-- User Header -->
                                    <button 
                                        @click="expandedVerifikasiUser = (expandedVerifikasiUser === '{{ $userId }}') ? null : '{{ $userId }}'"
                                        class="w-full flex items-center justify-between p-4 bg-teal-50/50 hover:bg-teal-50 transition duration-150 text-left border-b border-gray-100"
                                    >
                                        <div>
                                            <h4 class="font-bold text-gray-900 text-sm">{{ $borrowerName }}</h4>
                                            <p class="text-xs text-gray-500">{{ $borrowerSub }} | {{ $transactions->count() }} Pengajuan Pending</p>
                                        </div>
                                        <svg class="h-5 w-5 text-teal-600 transform transition-transform duration-200" :class="expandedVerifikasiUser === '{{ $userId }}' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </button>

                                    <!-- User Transactions list -->
                                    <div x-show="expandedVerifikasiUser === '{{ $userId }}'" class="p-4 space-y-4 bg-white" x-transition>
                                        @foreach($transactions as $tx)
                                            <div x-show="isDateInRange('{{ $tx->tanggal_pengajuan->format('Y-m-d') }}')" class="border border-gray-150 rounded-md p-4 bg-gray-50/30 space-y-3">
                                                <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                                                    <div class="flex items-center gap-2">
                                                        <span class="font-bold text-teal-700 text-xs">TX-{{ str_pad($tx->id, 5, '0', STR_PAD_LEFT) }}</span>
                                                        <span class="text-[10px] text-gray-405 text-gray-400">Diajukan: {{ $tx->tanggal_pengajuan->format('d M Y, H:i') }}</span>
                                                    </div>
                                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full {{ $tx->tipe === 'peminjaman_alat' ? 'bg-indigo-50 text-indigo-700 border border-indigo-100' : 'bg-orange-50 text-orange-700 border border-orange-100' }}">
                                                        {{ $tx->tipe === 'peminjaman_alat' ? 'Peminjaman Alat' : 'Permintaan Bahan' }}
                                                    </span>
                                                </div>

                                                <!-- PJ & Kegiatan info -->
                                                <div class="text-xs text-gray-655 bg-teal-50/30 p-2 rounded border border-teal-50">
                                                    <strong>Kegiatan:</strong> {{ $tx->kegiatan }} <span class="mx-1.5 text-gray-300">|</span> <strong>Dosen PJ:</strong> {{ $tx->penanggung_jawab }}
                                                </div>

                                                @if($tx->tipe === 'peminjaman_alat')
                                                    <div class="grid grid-cols-2 gap-3 text-xs">
                                                        <div><span class="text-gray-400 block">Jadwal Pinjam:</span> <span class="font-medium text-gray-700">{{ $tx->tanggal_pinjam ? $tx->tanggal_pinjam->format('d M Y, H:i') : '-' }}</span></div>
                                                        <div><span class="text-gray-400 block">Rencana Kembali:</span> <span class="font-medium text-gray-700">{{ $tx->tanggal_kembali_rencana ? $tx->tanggal_kembali_rencana->format('d M Y, H:i') : '-' }}</span></div>
                                                    </div>
                                                @endif

                                                <!-- Transaction Details (Editable item and quantity) -->
                                                <div class="space-y-3">
                                                    <span class="text-[10px] font-bold text-gray-450 uppercase tracking-wider block">Daftar Pengajuan Barang:</span>
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
                                                            <div class="p-3 bg-teal-50/30 rounded-lg border border-teal-100 space-y-2">
                                                                <span class="text-[10px] font-bold text-teal-800 uppercase tracking-wider block">🎁 {{ $packageName }} ({{ $packageQty }} set)</span>
                                                                @foreach($details as $detail)
                                                                    <form method="POST" action="{{ route('transactions.update-detail', $detail->id) }}" class="grid grid-cols-1 sm:grid-cols-12 gap-2 items-center bg-white p-2 rounded border border-gray-150">
                                                                        @csrf
                                                                        @method('PUT')
                                                                        <div class="sm:col-span-6">
                                                                            <select name="item_id" onchange="this.form.submit()" class="w-full text-xs border-gray-300 rounded focus:ring-teal-500 focus:border-teal-500 py-1 px-2">
                                                                                @foreach($items->where('kategori', $tx->tipe === 'peminjaman_alat' ? 'alat' : 'bahan') as $dbItem)
                                                                                    <option value="{{ $dbItem->id }}" {{ $dbItem->id === $detail->item_id ? 'selected' : '' }}>
                                                                                        {{ $dbItem->nama_barang }} (Stok: {{ $dbItem->stok_tersedia }})
                                                                                    </option>
                                                                                @endforeach
                                                                            </select>
                                                                        </div>
                                                                        <div class="sm:col-span-3 flex items-center gap-1.5">
                                                                            <span class="text-xs text-gray-400">Qty:</span>
                                                                            <input type="number" name="jumlah_diminta" value="{{ $detail->jumlah_diminta }}" onchange="this.form.submit()" min="1" class="w-full text-xs border-gray-300 rounded focus:ring-teal-500 focus:border-teal-500 py-1 px-1.5" />
                                                                        </div>
                                                                        <div class="sm:col-span-3 flex items-center justify-end gap-1 text-[10px] text-green-600 font-semibold italic bg-green-50/50 px-2.5 py-1 rounded">
                                                                            <span>✔ Tersimpan otomatis</span>
                                                                        </div>
                                                                    </form>
                                                                @endforeach
                                                            </div>
                                                        @else
                                                            <div class="p-3 bg-gray-50/50 rounded-lg border border-gray-250/60 space-y-2">
                                                                <span class="text-[10px] font-bold text-gray-550 uppercase tracking-wider block">📦 Barang Satuan</span>
                                                                @foreach($details as $detail)
                                                                    <form method="POST" action="{{ route('transactions.update-detail', $detail->id) }}" class="grid grid-cols-1 sm:grid-cols-12 gap-2 items-center bg-white p-2 rounded border border-gray-150">
                                                                        @csrf
                                                                        @method('PUT')
                                                                        <div class="sm:col-span-6">
                                                                            <select name="item_id" onchange="this.form.submit()" class="w-full text-xs border-gray-300 rounded focus:ring-teal-500 focus:border-teal-500 py-1 px-2">
                                                                                @foreach($items->where('kategori', $tx->tipe === 'peminjaman_alat' ? 'alat' : 'bahan') as $dbItem)
                                                                                    <option value="{{ $dbItem->id }}" {{ $dbItem->id === $detail->item_id ? 'selected' : '' }}>
                                                                                        {{ $dbItem->nama_barang }} (Stok: {{ $dbItem->stok_tersedia }})
                                                                                    </option>
                                                                                @endforeach
                                                                            </select>
                                                                        </div>
                                                                        <div class="sm:col-span-3 flex items-center gap-1.5">
                                                                            <span class="text-xs text-gray-400">Qty:</span>
                                                                            <input type="number" name="jumlah_diminta" value="{{ $detail->jumlah_diminta }}" onchange="this.form.submit()" min="1" class="w-full text-xs border-gray-300 rounded focus:ring-teal-500 focus:border-teal-500 py-1 px-1.5" />
                                                                        </div>
                                                                        <div class="sm:col-span-3 flex items-center justify-end gap-1 text-[10px] text-green-600 font-semibold italic bg-green-50/50 px-2.5 py-1 rounded">
                                                                            <span>✔ Tersimpan otomatis</span>
                                                                        </div>
                                                                    </form>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>

                                                <!-- Verify options -->
                                                <div class="pt-3 border-t border-gray-100 space-y-3" x-data="{ showFeedback: false, feedbackAction: '', feedbackNote: '' }">
                                                    
                                                    <!-- Actions Bar -->
                                                    <div class="flex justify-end items-center gap-2">
                                                        <button 
                                                            type="button"
                                                            @click="$dispatch('open-edit-tx-modal', {{ json_encode($tx->load(['details.item', 'details.package'])) }})"
                                                            class="px-3 py-1.5 border border-blue-300 text-xs font-semibold rounded text-blue-700 bg-white hover:bg-blue-50 transition duration-150 shadow-sm mr-auto"
                                                        >
                                                            Edit Pengajuan
                                                        </button>
                                                        <button 
                                                            type="button" 
                                                            @click="showFeedback = true; feedbackAction = 'suspend'; feedbackNote = ''"
                                                            class="px-3 py-1.5 border border-amber-300 text-xs font-semibold rounded text-amber-700 bg-white hover:bg-amber-50 transition duration-150 shadow-sm"
                                                        >
                                                            Tangguhkan
                                                        </button>
                                                        
                                                        <button 
                                                            type="button" 
                                                            @click="showFeedback = true; feedbackAction = 'reject'; feedbackNote = ''"
                                                            class="px-3 py-1.5 border border-red-200 text-xs font-semibold rounded text-red-650 bg-white hover:bg-red-50 transition duration-150 shadow-sm"
                                                        >
                                                            Tolak
                                                        </button>

                                                        <form method="POST" action="{{ route('transactions.approve', $tx->id) }}">
                                                            @csrf
                                                            <button type="submit" class="px-3 py-1.5 text-xs font-semibold rounded text-white bg-teal-600 hover:bg-teal-700 transition duration-150 shadow-sm">
                                                                Setujui & Proses
                                                            </button>
                                                        </form>
                                                    </div>

                                                    <!-- Feedback note form -->
                                                    <div x-show="showFeedback" x-transition class="p-3 bg-slate-50 border border-slate-200 rounded-md space-y-2" style="display: none;">
                                                        <label class="block text-[10px] font-bold text-gray-500 uppercase">
                                                            Berikan Catatan <span x-text="feedbackAction === 'suspend' ? 'Penangguhan' : 'Penolakan'"></span>
                                                        </label>
                                                        <form :action="feedbackAction === 'suspend' ? '/transactions/{{ $tx->id }}/suspend' : '/transactions/{{ $tx->id }}/reject'" method="POST" class="space-y-2">
                                                            @csrf
                                                            <textarea 
                                                                name="catatan_laboran" 
                                                                x-model="feedbackNote"
                                                                required
                                                                placeholder="Alasan penangguhan/penolakan (wajib diisi)..." 
                                                                class="w-full text-xs border-gray-300 rounded focus:ring-teal-500 focus:border-teal-500 py-1.5 px-2.5 h-16"
                                                            ></textarea>
                                                            <div class="flex justify-end gap-1.5">
                                                                <button type="button" @click="showFeedback = false" class="px-2.5 py-1 bg-white border border-gray-300 text-[11px] font-semibold text-gray-700 rounded hover:bg-gray-50">Batal</button>
                                                                <button type="submit" class="px-2.5 py-1 bg-teal-600 hover:bg-teal-700 text-white text-[11px] font-semibold rounded shadow-sm">Kirim Status</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-10 text-gray-400 text-sm border border-dashed border-gray-200 rounded-lg">Tidak ada pengajuan yang memerlukan verifikasi.</div>
                            @endforelse
                        </div>
                    </div>

                    <!-- TAB: ALAT DALAM PEMINJAMAN VIEW -->
                    <div x-show="activeTab === 'alat_dalam_peminjaman'" class="space-y-6" style="display: none;">
                        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Alat Dalam Peminjaman</h3>
                            <p class="text-sm text-gray-500 mb-6">Daftar peminjaman alat yang sedang aktif dan sudah disetujui, dikelompokkan per-mahasiswa.</p>

                            @forelse($alatActiveLoans as $userId => $transactions)
                                @php 
                                    $firstTx = $transactions->first(); 
                                    $isInsidentil = $firstTx->is_insidentil;
                                    $borrowerName = $isInsidentil ? $firstTx->peminjam_insidentil : ($firstTx->user ? $firstTx->user->name : 'Akun Terhapus');
                                    $borrowerSub = $isInsidentil ? 'Peminjaman Insidentil (Non-Mahasiswa)' : 'NIM: ' . ($firstTx->user ? $firstTx->user->nomor_induk : '-');
                                @endphp
                                <div x-show="hasVisibleTransactions([ @foreach($transactions as $tx) '{{ $tx->tanggal_pengajuan->format('Y-m-d') }}', @endforeach ])" class="border border-gray-200 rounded-lg mb-4 overflow-hidden shadow-sm bg-white">
                                    <!-- User Header -->
                                    <button 
                                        @click="expandedDalamPeminjamanUser = (expandedDalamPeminjamanUser === '{{ $userId }}') ? null : '{{ $userId }}'"
                                        class="w-full flex items-center justify-between p-4 bg-teal-50/50 hover:bg-teal-50 transition duration-150 text-left border-b border-gray-100"
                                    >
                                        <div>
                                            <h4 class="font-bold text-gray-900 text-sm">{{ $borrowerName }}</h4>
                                            <p class="text-xs text-gray-500">{{ $borrowerSub }} | {{ $transactions->count() }} Transaksi Aktif</p>
                                        </div>
                                        <svg class="h-5 w-5 text-teal-600 transform transition-transform duration-200" :class="expandedDalamPeminjamanUser === '{{ $userId }}' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </button>

                                    <!-- User Transactions List -->
                                    <div x-show="expandedDalamPeminjamanUser === '{{ $userId }}'" class="p-4 space-y-4 bg-white" x-transition>
                                        @foreach($transactions as $tx)
                                            <div x-show="isDateInRange('{{ $tx->tanggal_pengajuan->format('Y-m-d') }}')" class="border border-gray-150 rounded-md p-4 bg-gray-50/30 space-y-3">
                                                <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                                                    <div class="flex items-center gap-2">
                                                        <span class="font-bold text-teal-700 text-xs">TX-{{ str_pad($tx->id, 5, '0', STR_PAD_LEFT) }}</span>
                                                        <span class="text-[10px] text-gray-400">Diajukan: {{ $tx->tanggal_pengajuan->format('d M Y, H:i') }}</span>
                                                    </div>
                                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-blue-50 text-blue-700 border border-blue-100">
                                                        Dipinjam (Aktif)
                                                    </span>
                                                </div>

                                                <!-- PJ & Kegiatan info -->
                                                <div class="text-xs text-gray-655 bg-teal-50/30 p-2 rounded border border-teal-50">
                                                    <strong>Kegiatan:</strong> {{ $tx->kegiatan }} <span class="mx-1.5 text-gray-300">|</span> <strong>Dosen PJ:</strong> {{ $tx->penanggung_jawab }}
                                                </div>

                                                <div class="grid grid-cols-2 gap-3 text-xs">
                                                    <div><span class="text-gray-400 block">Jadwal Pinjam:</span> <span class="font-medium text-gray-700">{{ $tx->tanggal_pinjam ? $tx->tanggal_pinjam->format('d M Y, H:i') : '-' }}</span></div>
                                                    <div><span class="text-gray-550 block text-red-500">Rencana Kembali:</span> <span class="font-medium text-red-650">{{ $tx->tanggal_kembali_rencana ? $tx->tanggal_kembali_rencana->format('d M Y, H:i') : '-' }}</span></div>
                                                </div>

                                                <!-- Transaction Details (Editable even after approval) -->
                                                <div class="space-y-3">
                                                    <span class="text-[10px] font-bold text-gray-450 uppercase tracking-wider block">Daftar Barang (Admin Bisa Menyesuaikan):</span>
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
                                                            <div class="p-3 bg-teal-50/30 rounded-lg border border-teal-100 space-y-2">
                                                                <span class="text-[10px] font-bold text-teal-800 uppercase tracking-wider block">🎁 {{ $packageName }} ({{ $packageQty }} set)</span>
                                                                @foreach($details as $detail)
                                                                    <form method="POST" action="{{ route('transactions.update-detail', $detail->id) }}" class="grid grid-cols-1 sm:grid-cols-12 gap-2 items-center bg-white p-2 rounded border border-gray-150">
                                                                        @csrf
                                                                        @method('PUT')
                                                                        <div class="sm:col-span-6">
                                                                            <select name="item_id" onchange="this.form.submit()" class="w-full text-xs border-gray-300 rounded focus:ring-teal-500 focus:border-teal-500 py-1 px-2">
                                                                                @foreach($items->where('kategori', 'alat') as $dbItem)
                                                                                    <option value="{{ $dbItem->id }}" {{ $dbItem->id === $detail->item_id ? 'selected' : '' }}>
                                                                                        {{ $dbItem->nama_barang }} (Stok: {{ $dbItem->stok_tersedia }})
                                                                                    </option>
                                                                                @endforeach
                                                                            </select>
                                                                        </div>
                                                                        <div class="sm:col-span-3 flex items-center gap-1.5">
                                                                            <span class="text-xs text-gray-400">Qty:</span>
                                                                            <input type="number" name="jumlah_diminta" value="{{ $detail->jumlah_diminta }}" onchange="this.form.submit()" min="1" class="w-full text-xs border-gray-300 rounded focus:ring-teal-500 focus:border-teal-500 py-1 px-1.5" />
                                                                        </div>
                                                                        <div class="sm:col-span-3 flex items-center justify-end gap-1 text-[10px] text-green-600 font-semibold italic bg-green-50/50 px-2.5 py-1 rounded">
                                                                            <span>✔ Tersimpan otomatis</span>
                                                                        </div>
                                                                    </form>
                                                                @endforeach
                                                            </div>
                                                        @else
                                                            <div class="p-3 bg-gray-50/50 rounded-lg border border-gray-250/60 space-y-2">
                                                                <span class="text-[10px] font-bold text-gray-555 uppercase tracking-wider block">📦 Barang Satuan</span>
                                                                @foreach($details as $detail)
                                                                    <form method="POST" action="{{ route('transactions.update-detail', $detail->id) }}" class="grid grid-cols-1 sm:grid-cols-12 gap-2 items-center bg-white p-2 rounded border border-gray-150">
                                                                        @csrf
                                                                        @method('PUT')
                                                                        <div class="sm:col-span-6">
                                                                            <select name="item_id" onchange="this.form.submit()" class="w-full text-xs border-gray-300 rounded focus:ring-teal-500 focus:border-teal-500 py-1 px-2">
                                                                                @foreach($items->where('kategori', 'alat') as $dbItem)
                                                                                    <option value="{{ $dbItem->id }}" {{ $dbItem->id === $detail->item_id ? 'selected' : '' }}>
                                                                                        {{ $dbItem->nama_barang }} (Stok: {{ $dbItem->stok_tersedia }})
                                                                                    </option>
                                                                                @endforeach
                                                                            </select>
                                                                        </div>
                                                                        <div class="sm:col-span-3 flex items-center gap-1.5">
                                                                            <span class="text-xs text-gray-400">Qty:</span>
                                                                            <input type="number" name="jumlah_diminta" value="{{ $detail->jumlah_diminta }}" onchange="this.form.submit()" min="1" class="w-full text-xs border-gray-300 rounded focus:ring-teal-500 focus:border-teal-500 py-1 px-1.5" />
                                                                        </div>
                                                                        <div class="sm:col-span-3 flex items-center justify-end gap-1 text-[10px] text-green-600 font-semibold italic bg-green-50/50 px-2.5 py-1 rounded">
                                                                            <span>✔ Tersimpan otomatis</span>
                                                                        </div>
                                                                    </form>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>

                                                <!-- Action Return & PDF -->
                                                <div class="flex justify-end items-center gap-2 pt-3 border-t border-gray-100">
                                                    <button 
                                                        type="button"
                                                        @click="$dispatch('open-edit-tx-modal', {{ json_encode($tx->load(['details.item', 'details.package'])) }})"
                                                        class="px-4 py-2 border border-blue-300 text-xs font-semibold rounded text-blue-700 bg-white hover:bg-blue-50 transition duration-150 shadow-sm mr-auto"
                                                    >
                                                        Edit Transaksi
                                                    </button>
                                                    <a 
                                                        href="{{ route('transactions.pdf', $tx->id) }}?action=preview" 
                                                        target="_blank"
                                                        class="inline-flex items-center px-4 py-2 text-xs font-semibold rounded border border-teal-600 text-teal-700 bg-teal-50 hover:bg-teal-100 transition duration-150 shadow-sm"
                                                        title="Lihat Terlebih Dahulu"
                                                    >
                                                        👁 Lihat
                                                    </a>
                                                    <a 
                                                        href="{{ route('transactions.pdf', $tx->id) }}?action=download" 
                                                        class="inline-flex items-center px-4 py-2 text-xs font-semibold rounded border border-teal-500 text-white bg-teal-600 hover:bg-teal-700 transition duration-150 shadow-sm"
                                                        title="Unduh PDF"
                                                    >
                                                        Unduh PDF
                                                    </a>
                                                    <button 
                                                        type="button"
                                                        @click="returnTxId = '{{ $tx->id }}'; returnStatus = 'selesai'; returnCatatan = ''; isReturnModalOpen = true;"
                                                        class="px-4 py-2 text-xs font-semibold rounded text-white bg-teal-600 hover:bg-teal-700 transition duration-150 shadow-sm"
                                                    >
                                                        Catat Pengembalian Alat
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-10 text-gray-400 text-sm border border-dashed border-gray-200 rounded-lg">Tidak ada transaksi yang sedang aktif dipinjam.</div>
                            @endforelse
                        </div>
                    </div>

                    <!-- TAB: ALAT DIKEMBALIKAN DENGAN CATATAN VIEW -->
                    <div x-show="activeTab === 'alat_dikembalikan_dengan_catatan'" class="space-y-6" style="display: none;">
                        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Alat Dikembalikan Dengan Catatan (Belum Selesai)</h3>
                            <p class="text-sm text-gray-500 mb-6">Daftar pengembalian alat medis yang belum selesai (kembali sebagian atau bermasalah), dikelompokkan per-profil mahasiswa.</p>

                            @forelse($alatReturnedWithNotes as $userId => $transactions)
                                @php 
                                    $firstTx = $transactions->first(); 
                                    $isInsidentil = $firstTx->is_insidentil;
                                    $borrowerName = $isInsidentil ? $firstTx->peminjam_insidentil : ($firstTx->user ? $firstTx->user->name : 'Akun Terhapus');
                                    $borrowerSub = $isInsidentil ? 'Peminjaman Insidentil (Non-Mahasiswa)' : 'NIM: ' . ($firstTx->user ? $firstTx->user->nomor_induk : '-');
                                @endphp
                                <div x-show="hasVisibleTransactions([ @foreach($transactions as $tx) '{{ $tx->tanggal_pengajuan->format('Y-m-d') }}', @endforeach ])" class="border border-gray-200 rounded-lg mb-4 overflow-hidden shadow-sm bg-white">
                                    <!-- User Header -->
                                    <button 
                                        @click="expandedCatatanUser = (expandedCatatanUser === '{{ $userId }}') ? null : '{{ $userId }}'"
                                        class="w-full flex items-center justify-between p-4 bg-amber-50/50 hover:bg-amber-50 transition duration-150 text-left border-b border-gray-100"
                                    >
                                        <div>
                                            <h4 class="font-bold text-gray-900 text-sm">{{ $borrowerName }}</h4>
                                            <p class="text-xs text-gray-500">{{ $borrowerSub }} | {{ $transactions->count() }} Pengembalian Bermasalah</p>
                                        </div>
                                        <svg class="h-5 w-5 text-amber-600 transform transition-transform duration-200" :class="expandedCatatanUser === '{{ $userId }}' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </button>

                                    <!-- User Transactions List -->
                                    <div x-show="expandedCatatanUser === '{{ $userId }}'" class="p-4 space-y-4 bg-white" x-transition>
                                        @foreach($transactions as $tx)
                                            <div x-show="isDateInRange('{{ $tx->tanggal_pengajuan->format('Y-m-d') }}')" class="border border-amber-100 rounded-md p-4 bg-amber-50/10 space-y-3">
                                                <div class="flex justify-between items-center pb-2 border-b border-amber-100">
                                                    <div class="flex items-center gap-2">
                                                        <span class="font-bold text-amber-805 text-amber-800 text-xs">TX-{{ str_pad($tx->id, 5, '0', STR_PAD_LEFT) }}</span>
                                                        <span class="text-[10px] text-gray-405">Diajukan: {{ $tx->tanggal_pengajuan->format('d M Y, H:i') }}</span>
                                                    </div>
                                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-amber-100 text-amber-800 border border-amber-200">
                                                        Belum Selesai (Catatan)
                                                    </span>
                                                </div>

                                                <!-- Catatan return -->
                                                <div class="p-3 bg-amber-50 rounded border border-amber-200 text-xs text-amber-900">
                                                    <strong>Catatan Kerusakan / Kekurangan:</strong> 
                                                    <p class="mt-1 font-medium italic text-gray-700">"{{ $tx->catatan_pengembalian ?: 'Tidak ada catatan' }}"</p>
                                                </div>

                                                <!-- PJ & Kegiatan info -->
                                                <div class="text-xs text-gray-655 bg-teal-50/30 p-2 rounded border border-teal-50">
                                                    <strong>Kegiatan:</strong> {{ $tx->kegiatan }} <span class="mx-1.5 text-gray-300">|</span> <strong>Dosen PJ:</strong> {{ $tx->penanggung_jawab }}
                                                </div>

                                                <div class="grid grid-cols-2 gap-3 text-xs">
                                                    <div><span class="text-gray-400 block">Jadwal Pinjam:</span> <span class="font-medium text-gray-700">{{ $tx->tanggal_pinjam ? $tx->tanggal_pinjam->format('d M Y, H:i') : '-' }}</span></div>
                                                    <div><span class="text-gray-500 block">Rencana Kembali:</span> <span class="font-medium text-gray-707 text-gray-700">{{ $tx->tanggal_kembali_rencana ? $tx->tanggal_kembali_rencana->format('d M Y, H:i') : '-' }}</span></div>
                                                </div>

                                                <!-- Details list -->
                                                <div class="space-y-2 text-xs">
                                                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-1">Daftar Alat:</span>
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
                                                            <div class="bg-teal-50/30 p-2.5 rounded border border-teal-100 mb-2">
                                                                <span class="text-[10px] font-bold text-teal-800 uppercase tracking-wider block mb-1">🎁 {{ $packageName }} ({{ $packageQty }} set)</span>
                                                                @foreach($details as $det)
                                                                    <div class="flex justify-between items-center py-1 border-b border-teal-50 last:border-b-0 px-1 bg-white/50">
                                                                        <span>{{ $det->item->nama_barang }}</span>
                                                                        <span class="font-bold text-gray-805">{{ $det->jumlah_diminta }} {{ $det->item->satuan }}</span>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @else
                                                            <div class="bg-gray-50/50 p-2.5 rounded border border-gray-200 mb-2">
                                                                <span class="text-[10px] font-bold text-gray-550 uppercase tracking-wider block mb-1">📦 Barang Satuan</span>
                                                                @foreach($details as $det)
                                                                    <div class="flex justify-between items-center py-1 border-b border-gray-100 last:border-b-0 px-1 bg-white/50">
                                                                        <span>{{ $det->item->nama_barang }}</span>
                                                                        <span class="font-bold text-gray-805">{{ $det->jumlah_diminta }} {{ $det->item->satuan }}</span>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>

                                                <!-- Action Return & PDF -->
                                                    <a 
                                                        href="{{ route('transactions.pdf', $tx->id) }}?action=preview" 
                                                        target="_blank"
                                                        class="inline-flex items-center px-4 py-2 text-xs font-semibold rounded border border-teal-600 text-teal-700 bg-teal-50 hover:bg-teal-100 transition duration-150 shadow-sm"
                                                        title="Lihat Terlebih Dahulu"
                                                    >
                                                        👁 Lihat
                                                    </a>
                                                    <a 
                                                        href="{{ route('transactions.pdf', $tx->id) }}?action=download" 
                                                        class="inline-flex items-center px-4 py-2 text-xs font-semibold rounded border border-teal-500 text-white bg-teal-600 hover:bg-teal-700 transition duration-150 shadow-sm"
                                                        title="Unduh PDF"
                                                    >
                                                        Unduh PDF
                                                    </a>
                                                    <button 
                                                        type="button"
                                                        @click="returnTxId = '{{ $tx->id }}'; returnStatus = 'selesai'; returnCatatan = '{{ addslashes($tx->catatan_pengembalian) }}'; isReturnModalOpen = true;"
                                                        class="px-4 py-2 text-xs font-semibold rounded text-white bg-amber-600 hover:bg-amber-700 transition duration-150 shadow-sm"
                                                    >
                                                        Selesaikan Pengembalian
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-10 text-gray-400 text-sm border border-dashed border-gray-200 rounded-lg">Tidak ada transaksi dengan pengembalian bermasalah/dengan catatan.</div>
                            @endforelse
                        </div>
                    </div>

                    <!-- TAB: ALAT DIKEMBALIKAN SEPENUHNYA VIEW -->
                    <div x-show="activeTab === 'alat_dikembalikan_sepenuhnya'" class="space-y-6" style="display: none;">
                        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Riwayat Pengembalian Selesai Sepenuhnya</h3>
                            <p class="text-sm text-gray-500 mb-6">Riwayat pengembalian alat medis yang selesai utuh, dikelompokkan per-profil mahasiswa.</p>

                            @forelse($alatReturnedFully as $userId => $transactions)
                                @php 
                                    $firstTx = $transactions->first(); 
                                    $isInsidentil = $firstTx->is_insidentil;
                                    $borrowerName = $isInsidentil ? $firstTx->peminjam_insidentil : ($firstTx->user ? $firstTx->user->name : 'Akun Terhapus');
                                    $borrowerSub = $isInsidentil ? 'Peminjaman Insidentil (Non-Mahasiswa)' : 'NIM: ' . ($firstTx->user ? $firstTx->user->nomor_induk : '-');
                                @endphp
                                <div x-show="hasVisibleTransactions([ @foreach($transactions as $tx) '{{ $tx->tanggal_pengajuan->format('Y-m-d') }}', @endforeach ])" class="border border-gray-200 rounded-lg mb-4 overflow-hidden shadow-sm bg-white">
                                    <!-- User Header -->
                                    <button 
                                        @click="expandedPengembalianUser = (expandedPengembalianUser === '{{ $userId }}') ? null : '{{ $userId }}'"
                                        class="w-full flex items-center justify-between p-4 bg-teal-50/50 hover:bg-teal-50 transition duration-150 text-left border-b border-gray-100"
                                    >
                                        <div>
                                            <h4 class="font-bold text-gray-900 text-sm">{{ $borrowerName }}</h4>
                                            <p class="text-xs text-gray-500">{{ $borrowerSub }} | {{ $transactions->count() }} Riwayat Transaksi</p>
                                        </div>
                                        <svg class="h-5 w-5 text-teal-600 transform transition-transform duration-200" :class="expandedPengembalianUser === '{{ $userId }}' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </button>

                                    <!-- User Transactions List -->
                                    <div x-show="expandedPengembalianUser === '{{ $userId }}'" class="p-4 space-y-4 bg-white" x-transition>
                                        @foreach($transactions as $tx)
                                            <div x-show="isDateInRange('{{ $tx->tanggal_pengajuan->format('Y-m-d') }}')" class="border border-gray-150 rounded-md p-4 bg-gray-50/20 space-y-3">
                                                <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                                                    <div class="flex items-center gap-2">
                                                        <span class="font-bold text-teal-700 text-xs">TX-{{ str_pad($tx->id, 5, '0', STR_PAD_LEFT) }}</span>
                                                        <span class="text-[10px] text-gray-400">Diajukan: {{ $tx->tanggal_pengajuan->format('d M Y, H:i') }}</span>
                                                    </div>
                                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-green-50 text-green-700 border border-green-150">
                                                        Selesai (Kembali Utuh)
                                                    </span>
                                                </div>

                                                <!-- Catatan return if any -->
                                                @if($tx->catatan_pengembalian)
                                                    <div class="p-2.5 bg-gray-100 border border-gray-200 rounded text-xs text-gray-600">
                                                        <strong>Catatan Pengembalian:</strong> {{ $tx->catatan_pengembalian }}
                                                    </div>
                                                @endif

                                                <!-- PJ & Kegiatan info -->
                                                <div class="text-xs text-gray-655 bg-teal-50/20 p-2 rounded border border-teal-50">
                                                    <strong>Kegiatan:</strong> {{ $tx->kegiatan }} <span class="mx-1.5 text-gray-300">|</span> <strong>Dosen PJ:</strong> {{ $tx->penanggung_jawab }}
                                                </div>

                                                <div class="grid grid-cols-2 gap-3 text-xs text-gray-600">
                                                    <div><span class="text-gray-400 block">Jadwal Pinjam:</span> <span>{{ $tx->tanggal_pinjam ? $tx->tanggal_pinjam->format('d M Y, H:i') : '-' }}</span></div>
                                                    <div><span class="text-gray-450 block">Tanggal Pengembalian Realisasi:</span> <span class="text-teal-700 font-semibold">{{ $tx->tanggal_kembali_realisasi ? $tx->tanggal_kembali_realisasi->format('d M Y, H:i') : '-' }}</span></div>
                                                </div>

                                                <!-- Details list -->
                                                <div class="space-y-2 text-xs">
                                                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-1">Barang Transaksi:</span>
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
                                                            <div class="bg-teal-50/30 p-2.5 rounded border border-teal-100 mb-2">
                                                                <span class="text-[10px] font-bold text-teal-800 uppercase tracking-wider block mb-1">🎁 {{ $packageName }} ({{ $packageQty }} set)</span>
                                                                @foreach($details as $det)
                                                                    <div class="flex justify-between items-center py-1 border-b border-teal-50 last:border-b-0 px-1 bg-white/50">
                                                                        <span>{{ $det->item->nama_barang }}</span>
                                                                        <span class="font-bold text-gray-805">{{ $det->jumlah_diminta }} {{ $det->item->satuan }}</span>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @else
                                                            <div class="bg-gray-50/50 p-2.5 rounded border border-gray-200 mb-2">
                                                                <span class="text-[10px] font-bold text-gray-550 uppercase tracking-wider block mb-1">📦 Barang Satuan</span>
                                                                @foreach($details as $det)
                                                                    <div class="flex justify-between items-center py-1 border-b border-gray-100 last:border-b-0 px-1 bg-white/50">
                                                                        <span>{{ $det->item->nama_barang }}</span>
                                                                        <span class="font-bold text-gray-805">{{ $det->jumlah_diminta }} {{ $det->item->satuan }}</span>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                                <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                                                    <a 
                                                        href="{{ route('transactions.pdf', $tx->id) }}?action=preview" 
                                                        target="_blank"
                                                        class="inline-flex items-center px-4 py-2 text-xs font-semibold rounded border border-teal-600 text-teal-700 bg-teal-50 hover:bg-teal-100 transition duration-150 shadow-sm"
                                                        title="Lihat Terlebih Dahulu"
                                                    >
                                                        👁 Lihat
                                                    </a>
                                                    <a 
                                                        href="{{ route('transactions.pdf', $tx->id) }}?action=download" 
                                                        class="inline-flex items-center px-4 py-2 text-xs font-semibold rounded border border-teal-500 text-white bg-teal-600 hover:bg-teal-700 transition duration-150 shadow-sm"
                                                        title="Unduh PDF"
                                                    >
                                                        Unduh PDF
                                                    </a>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-10 text-gray-400 text-sm border border-dashed border-gray-200 rounded-lg">Belum ada riwayat pengembalian selesai.</div>
                            @endforelse
                        </div>
                    </div>

                    <!-- TAB: PEMINJAMAN ALAT DITOLAK ATAU DITANGGUHKAN -->
                    <div x-show="activeTab === 'alat_ditolak_ditangguhkan'" class="space-y-6" style="display: none;">
                        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Peminjaman Alat Ditolak atau Ditangguhkan</h3>
                            <p class="text-sm text-gray-500 mb-6">Daftar peminjaman alat yang statusnya ditolak atau ditangguhkan. Anda dapat menyetujui kembali transaksi ini.</p>

                            @forelse($alatRejectedOrSuspended as $userId => $transactions)
                                @php 
                                    $firstTx = $transactions->first(); 
                                    $isInsidentil = $firstTx->is_insidentil;
                                    $borrowerName = $isInsidentil ? $firstTx->peminjam_insidentil : ($firstTx->user ? $firstTx->user->name : 'Akun Terhapus');
                                    $borrowerSub = $isInsidentil ? 'Peminjaman Insidentil (Non-Mahasiswa)' : 'NIM: ' . ($firstTx->user ? $firstTx->user->nomor_induk : '-');
                                @endphp
                                <div x-show="hasVisibleTransactions([ @foreach($transactions as $tx) '{{ $tx->tanggal_pengajuan->format('Y-m-d') }}', @endforeach ])" class="border border-gray-200 rounded-lg mb-4 overflow-hidden shadow-sm bg-white">
                                    <!-- User Header -->
                                    <button 
                                        @click="expandedRejectSuspendAlatUser = (expandedRejectSuspendAlatUser === '{{ $userId }}') ? null : '{{ $userId }}'"
                                        class="w-full flex items-center justify-between p-4 bg-red-50/20 hover:bg-red-50/40 transition duration-150 text-left border-b border-gray-100"
                                    >
                                        <div>
                                            <h4 class="font-bold text-gray-900 text-sm">{{ $borrowerName }}</h4>
                                            <p class="text-xs text-gray-500">{{ $borrowerSub }} | {{ $transactions->count() }} Transaksi</p>
                                        </div>
                                        <svg class="h-5 w-5 text-red-600 transform transition-transform duration-200" :class="expandedRejectSuspendAlatUser === '{{ $userId }}' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </button>

                                    <!-- User Transactions List -->
                                    <div x-show="expandedRejectSuspendAlatUser === '{{ $userId }}'" class="p-4 space-y-4 bg-white" x-transition>
                                        @foreach($transactions as $tx)
                                            <div x-show="isDateInRange('{{ $tx->tanggal_pengajuan->format('Y-m-d') }}')" class="border border-gray-150 rounded-md p-4 bg-gray-50 space-y-3">
                                                <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                                                    <div class="flex items-center gap-2">
                                                        <span class="font-bold text-teal-700 text-xs">TX-{{ str_pad($tx->id, 5, '0', STR_PAD_LEFT) }}</span>
                                                        <span class="text-[10px] text-gray-400">Diajukan: {{ $tx->tanggal_pengajuan->format('d M Y, H:i') }}</span>
                                                    </div>
                                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full {{ $tx->status === 'ditangguhkan' ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-red-100 text-red-700 border border-red-200' }}">
                                                        {{ strtoupper($tx->status) }}
                                                    </span>
                                                </div>

                                                @if($tx->catatan_laboran)
                                                    <div class="p-2.5 bg-red-50/50 border border-red-200 rounded text-xs text-red-850">
                                                        <strong>Catatan Laboran:</strong> "{{ $tx->catatan_laboran }}"
                                                    </div>
                                                @endif

                                                <div class="text-xs text-gray-655 bg-teal-50/20 p-2 rounded border border-teal-50">
                                                    <strong>Kegiatan:</strong> {{ $tx->kegiatan }} <span class="mx-1.5 text-gray-300">|</span> <strong>Dosen PJ:</strong> {{ $tx->penanggung_jawab }}
                                                </div>

                                                <!-- Details list -->
                                                <div class="space-y-2 text-xs">
                                                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-1">Barang Transaksi:</span>
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
                                                            <div class="bg-teal-50/30 p-2.5 rounded border border-teal-100 mb-2">
                                                                <span class="text-[10px] font-bold text-teal-800 uppercase tracking-wider block mb-1">🎁 {{ $packageName }} ({{ $packageQty }} set)</span>
                                                                @foreach($details as $det)
                                                                    <div class="flex justify-between items-center py-1 border-b border-teal-50 last:border-b-0 px-1 bg-white/50">
                                                                        <span>{{ $det->item->nama_barang }}</span>
                                                                        <span class="font-bold text-gray-805">{{ $det->jumlah_diminta }} {{ $det->item->satuan }}</span>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @else
                                                            <div class="bg-gray-50/50 p-2.5 rounded border border-gray-200 mb-2">
                                                                <span class="text-[10px] font-bold text-gray-555 uppercase tracking-wider block mb-1">📦 Barang Satuan</span>
                                                                @foreach($details as $det)
                                                                    <div class="flex justify-between items-center py-1 border-b border-gray-100 last:border-b-0 px-1 bg-white/50">
                                                                        <span>{{ $det->item->nama_barang }}</span>
                                                                        <span class="font-bold text-gray-805">{{ $det->jumlah_diminta }} {{ $det->item->satuan }}</span>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>

                                                <div class="flex justify-end items-center gap-2 pt-2 border-t border-gray-100">
                                                    <button 
                                                        type="button"
                                                        @click="$dispatch('open-edit-tx-modal', {{ json_encode($tx->load(['details.item', 'details.package'])) }})"
                                                        class="px-4 py-2 border border-blue-300 text-xs font-semibold rounded text-blue-700 bg-white hover:bg-blue-50 transition duration-150 shadow-sm mr-auto"
                                                    >
                                                        Edit Transaksi
                                                    </button>
                                                    <form method="POST" action="{{ route('transactions.approve', $tx->id) }}">
                                                        @csrf
                                                        <button type="submit" class="px-4 py-2 text-xs font-semibold rounded text-white bg-teal-600 hover:bg-teal-700 transition duration-150 shadow-sm">
                                                            Setujui & Proses Kembali
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-10 text-gray-400 text-sm border border-dashed border-gray-200 rounded-lg">Tidak ada peminjaman alat yang ditolak atau ditangguhkan.</div>
                            @endforelse
                        </div>
                    </div>

                    <!-- TAB: PERMINTAAN BAHAN DISETUJUI VIEW -->
                    <div x-show="activeTab === 'bahan_disetujui'" class="space-y-6" style="display: none;">

                        <!-- LOW STOCK MATERIALS -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 mb-6">
                            <h3 class="text-sm font-bold text-red-600 mb-3 flex items-center gap-1.5">
                                ⚠ Stok Bahan Hampir Habis (< 10)
                            </h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-150 text-xs">
                                    <thead class="bg-gray-50 text-gray-500 font-semibold uppercase">
                                        <tr>
                                            <th class="px-3 py-2 text-left">Kode Bahan</th>
                                            <th class="px-3 py-2 text-left">Nama Bahan</th>
                                            <th class="px-3 py-2 text-center">Stok Tersedia</th>
                                            <th class="px-3 py-2">Lokasi Rak</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @forelse($lowStockMaterials as $mat)
                                            <tr class="bg-red-50/10">
                                                <td class="px-3 py-2.5 font-bold text-gray-800">{{ $mat->kode_barang }}</td>
                                                <td class="px-3 py-2.5 font-medium text-gray-900">{{ $mat->nama_barang }}</td>
                                                <td class="px-3 py-2.5 text-center font-bold text-red-650">{{ $mat->stok_tersedia }} {{ $mat->satuan }}</td>
                                                <td class="px-3 py-2.5 text-center text-gray-500">{{ $mat->lokasi_rak }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="px-3 py-6 text-center text-green-600 font-semibold">✅ Seluruh persediaan bahan habis pakai terpantau aman.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Permintaan Bahan Disetujui (Selesai)</h3>
                            <p class="text-sm text-gray-500 mb-6">Daftar permintaan bahan praktikum yang telah disetujui dan disalurkan, dikelompokkan per-mahasiswa.</p>

                            @forelse($bahanApproved as $userId => $transactions)
                                @php 
                                    $firstTx = $transactions->first(); 
                                    $isInsidentil = $firstTx->is_insidentil;
                                    $borrowerName = $isInsidentil ? $firstTx->peminjam_insidentil : ($firstTx->user ? $firstTx->user->name : 'Akun Terhapus');
                                    $borrowerSub = $isInsidentil ? 'Peminjaman Insidentil (Non-Mahasiswa)' : 'NIM: ' . ($firstTx->user ? $firstTx->user->nomor_induk : '-');
                                @endphp
                                <div x-show="hasVisibleTransactions([ @foreach($transactions as $tx) '{{ $tx->tanggal_pengajuan->format('Y-m-d') }}', @endforeach ])" class="border border-gray-200 rounded-lg mb-4 overflow-hidden shadow-sm bg-white">
                                    <!-- User Header -->
                                    <button 
                                        @click="expandedDalamPeminjamanBahanUser = (expandedDalamPeminjamanBahanUser === '{{ $userId }}') ? null : '{{ $userId }}'"
                                        class="w-full flex items-center justify-between p-4 bg-teal-50/50 hover:bg-teal-50 transition duration-150 text-left border-b border-gray-100"
                                    >
                                        <div>
                                            <h4 class="font-bold text-gray-900 text-sm">{{ $borrowerName }}</h4>
                                            <p class="text-xs text-gray-500">{{ $borrowerSub }} | {{ $transactions->count() }} Permintaan Selesai</p>
                                        </div>
                                        <svg class="h-5 w-5 text-teal-600 transform transition-transform duration-200" :class="expandedDalamPeminjamanBahanUser === '{{ $userId }}' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </button>

                                    <!-- User Transactions List -->
                                    <div x-show="expandedDalamPeminjamanBahanUser === '{{ $userId }}'" class="p-4 space-y-4 bg-white" x-transition>
                                        @foreach($transactions as $tx)
                                            <div x-show="isDateInRange('{{ $tx->tanggal_pengajuan->format('Y-m-d') }}')" class="border border-gray-150 rounded-md p-4 bg-gray-50/20 space-y-3">
                                                <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                                                    <div class="flex items-center gap-2">
                                                        <span class="font-bold text-teal-700 text-xs">TX-{{ str_pad($tx->id, 5, '0', STR_PAD_LEFT) }}</span>
                                                        <span class="text-[10px] text-gray-400">Diajukan: {{ $tx->tanggal_pengajuan->format('d M Y, H:i') }}</span>
                                                    </div>
                                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-green-50 text-green-700 border border-green-150">
                                                        Bahan Disalurkan (Selesai)
                                                    </span>
                                                </div>

                                                <div class="text-xs text-gray-655 bg-teal-50/20 p-2 rounded border border-teal-50">
                                                    <strong>Kegiatan:</strong> {{ $tx->kegiatan }} <span class="mx-1.5 text-gray-300">|</span> <strong>Dosen PJ:</strong> {{ $tx->penanggung_jawab }}
                                                </div>

                                                <!-- Details list -->
                                                <div class="space-y-2 text-xs">
                                                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-1">Bahan Transaksi:</span>
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
                                                            <div class="bg-teal-50/30 p-2.5 rounded border border-teal-100 mb-2">
                                                                <span class="text-[10px] font-bold text-teal-800 uppercase tracking-wider block mb-1">🎁 {{ $packageName }} ({{ $packageQty }} set)</span>
                                                                @foreach($details as $det)
                                                                    <div class="flex justify-between items-center py-1 border-b border-teal-50 last:border-b-0 px-1 bg-white/50">
                                                                        <span>{{ $det->item->nama_barang }}</span>
                                                                        <span class="font-bold text-gray-805">{{ $det->jumlah_diminta }} {{ $det->item->satuan }}</span>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @else
                                                            <div class="bg-gray-50/50 p-2.5 rounded border border-gray-200 mb-2">
                                                                <span class="text-[10px] font-bold text-gray-555 uppercase tracking-wider block mb-1">📦 Barang Satuan</span>
                                                                @foreach($details as $det)
                                                                    <div class="flex justify-between items-center py-1 border-b border-gray-100 last:border-b-0 px-1 bg-white/50">
                                                                        <span>{{ $det->item->nama_barang }}</span>
                                                                        <span class="font-bold text-gray-805">{{ $det->jumlah_diminta }} {{ $det->item->satuan }}</span>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>

                                                <div class="flex justify-end items-center gap-2 pt-2 border-t border-gray-100">
                                                    <button 
                                                        type="button"
                                                        @click="$dispatch('open-edit-tx-modal', {{ json_encode($tx->load(['details.item', 'details.package'])) }})"
                                                        class="px-4 py-2 border border-blue-300 text-xs font-semibold rounded text-blue-700 bg-white hover:bg-blue-50 transition duration-150 shadow-sm mr-auto"
                                                    >
                                                        Edit Permintaan
                                                    </button>
                                                    <a 
                                                        href="{{ route('transactions.pdf', $tx->id) }}?action=preview" 
                                                        target="_blank"
                                                        class="inline-flex items-center px-4 py-2 text-xs font-semibold rounded border border-teal-600 text-teal-700 bg-teal-50 hover:bg-teal-100 transition duration-150 shadow-sm"
                                                        title="Lihat Terlebih Dahulu"
                                                    >
                                                        👁 Lihat
                                                    </a>
                                                    <a 
                                                        href="{{ route('transactions.pdf', $tx->id) }}?action=download" 
                                                        class="inline-flex items-center px-4 py-2 text-xs font-semibold rounded border border-teal-500 text-white bg-teal-600 hover:bg-teal-700 transition duration-150 shadow-sm"
                                                        title="Unduh PDF"
                                                    >
                                                        Unduh PDF
                                                    </a>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-10 text-gray-400 text-sm border border-dashed border-gray-200 rounded-lg">Belum ada permintaan bahan disetujui.</div>
                            @endforelse
                        </div>
                    </div>

                    <!-- TAB: PERMINTAAN BAHAN DITOLAK ATAU DITANGGUHKAN -->
                    <div x-show="activeTab === 'bahan_ditolak_ditangguhkan'" class="space-y-6" style="display: none;">
                        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Permintaan Bahan Ditolak atau Ditangguhkan</h3>
                            <p class="text-sm text-gray-500 mb-6">Daftar permintaan bahan yang statusnya ditolak atau ditangguhkan. Anda dapat menyetujui kembali transaksi ini.</p>

                            @forelse($bahanRejectedOrSuspended as $userId => $transactions)
                                @php 
                                    $firstTx = $transactions->first(); 
                                    $isInsidentil = $firstTx->is_insidentil;
                                    $borrowerName = $isInsidentil ? $firstTx->peminjam_insidentil : ($firstTx->user ? $firstTx->user->name : 'Akun Terhapus');
                                    $borrowerSub = $isInsidentil ? 'Peminjaman Insidentil (Non-Mahasiswa)' : 'NIM: ' . ($firstTx->user ? $firstTx->user->nomor_induk : '-');
                                @endphp
                                <div x-show="hasVisibleTransactions([ @foreach($transactions as $tx) '{{ $tx->tanggal_pengajuan->format('Y-m-d') }}', @endforeach ])" class="border border-gray-200 rounded-lg mb-4 overflow-hidden shadow-sm bg-white">
                                    <!-- User Header -->
                                    <button 
                                        @click="expandedRejectSuspendBahanUser = (expandedRejectSuspendBahanUser === '{{ $userId }}') ? null : '{{ $userId }}'"
                                        class="w-full flex items-center justify-between p-4 bg-red-50/20 hover:bg-red-50/40 transition duration-150 text-left border-b border-gray-100"
                                    >
                                        <div>
                                            <h4 class="font-bold text-gray-900 text-sm">{{ $borrowerName }}</h4>
                                            <p class="text-xs text-gray-500">{{ $borrowerSub }} | {{ $transactions->count() }} Transaksi</p>
                                        </div>
                                        <svg class="h-5 w-5 text-red-600 transform transition-transform duration-200" :class="expandedRejectSuspendBahanUser === '{{ $userId }}' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </button>

                                    <!-- User Transactions List -->
                                    <div x-show="expandedRejectSuspendBahanUser === '{{ $userId }}'" class="p-4 space-y-4 bg-white" x-transition>
                                        @foreach($transactions as $tx)
                                            <div x-show="isDateInRange('{{ $tx->tanggal_pengajuan->format('Y-m-d') }}')" class="border border-gray-150 rounded-md p-4 bg-gray-50 space-y-3">
                                                <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                                                    <div class="flex items-center gap-2">
                                                        <span class="font-bold text-teal-700 text-xs">TX-{{ str_pad($tx->id, 5, '0', STR_PAD_LEFT) }}</span>
                                                        <span class="text-[10px] text-gray-400">Diajukan: {{ $tx->tanggal_pengajuan->format('d M Y, H:i') }}</span>
                                                    </div>
                                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full {{ $tx->status === 'ditangguhkan' ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-red-100 text-red-700 border border-red-200' }}">
                                                        {{ strtoupper($tx->status) }}
                                                    </span>
                                                </div>

                                                @if($tx->catatan_laboran)
                                                    <div class="p-2.5 bg-red-50/50 border border-red-200 rounded text-xs text-red-850">
                                                        <strong>Catatan Laboran:</strong> "{{ $tx->catatan_laboran }}"
                                                    </div>
                                                @endif

                                                <div class="text-xs text-gray-655 bg-teal-50/20 p-2 rounded border border-teal-50">
                                                    <strong>Kegiatan:</strong> {{ $tx->kegiatan }} <span class="mx-1.5 text-gray-300">|</span> <strong>Dosen PJ:</strong> {{ $tx->penanggung_jawab }}
                                                </div>

                                                <!-- Details list -->
                                                <div class="space-y-2 text-xs">
                                                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-1">Barang Transaksi:</span>
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
                                                            <div class="bg-teal-50/30 p-2.5 rounded border border-teal-100 mb-2">
                                                                <span class="text-[10px] font-bold text-teal-800 uppercase tracking-wider block mb-1">🎁 {{ $packageName }} ({{ $packageQty }} set)</span>
                                                                @foreach($details as $det)
                                                                    <div class="flex justify-between items-center py-1 border-b border-teal-50 last:border-b-0 px-1 bg-white/50">
                                                                        <span>{{ $det->item->nama_barang }}</span>
                                                                        <span class="font-bold text-gray-805">{{ $det->jumlah_diminta }} {{ $det->item->satuan }}</span>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @else
                                                            <div class="bg-gray-50/50 p-2.5 rounded border border-gray-200 mb-2">
                                                                <span class="text-[10px] font-bold text-gray-555 uppercase tracking-wider block mb-1">📦 Barang Satuan</span>
                                                                @foreach($details as $det)
                                                                    <div class="flex justify-between items-center py-1 border-b border-gray-100 last:border-b-0 px-1 bg-white/50">
                                                                        <span>{{ $det->item->nama_barang }}</span>
                                                                        <span class="font-bold text-gray-805">{{ $det->jumlah_diminta }} {{ $det->item->satuan }}</span>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>

                                                <div class="flex justify-end items-center gap-2 pt-2 border-t border-gray-100">
                                                    <button 
                                                        type="button"
                                                        @click="$dispatch('open-edit-tx-modal', {{ json_encode($tx->load(['details.item', 'details.package'])) }})"
                                                        class="px-4 py-2 border border-blue-300 text-xs font-semibold rounded text-blue-700 bg-white hover:bg-blue-50 transition duration-150 shadow-sm mr-auto"
                                                    >
                                                        Edit Permintaan
                                                    </button>
                                                    <form method="POST" action="{{ route('transactions.approve', $tx->id) }}">
                                                        @csrf
                                                        <button type="submit" class="px-4 py-2 text-xs font-semibold rounded text-white bg-teal-600 hover:bg-teal-700 transition duration-150 shadow-sm">
                                                            Setujui & Proses Kembali
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-10 text-gray-400 text-sm border border-dashed border-gray-200 rounded-lg">Tidak ada permintaan bahan yang ditolak atau ditangguhkan.</div>
                            @endforelse
                        </div>
                    </div>

                    <!-- TAB: BEBAS LABORATORIUM VIEW -->
                    <div x-show="activeTab === 'bebas_lab'" class="space-y-6" style="display: none;">
                        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-1">🎓 Pembuatan Surat Bebas Laboratorium</h3>
                                <p class="text-xs text-gray-500 mb-4 font-sans">Pilih mahasiswa tingkat akhir yang akan lulus untuk menerbitkan Surat Bebas Laboratorium sebagai syarat administrasi sidang akhir / wisuda.</p>
                            </div>

                            <!-- Search bar nama mahasiswa -->
                            <div class="mb-5">
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Cari Nama / NIM Mahasiswa</label>
                                <input 
                                    type="text" 
                                    x-model="searchBebasLabStudent" 
                                    placeholder="Ketik nama atau NIM mahasiswa..." 
                                    class="text-xs border-gray-300 rounded-md focus:ring-teal-500 focus:border-teal-500 py-1.5 px-3 w-full sm:w-80 shadow-xs"
                                />
                            </div>

                            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                                <!-- LEFT PANEL: DAFTAR MAHASISWA -->
                                <div class="lg:col-span-7 space-y-4">
                                    <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider">Daftar Mahasiswa</h4>
                                    <div class="overflow-x-auto border border-gray-150 rounded-lg">
                                        <table class="min-w-full divide-y divide-gray-200 text-xs">
                                            <thead class="bg-gray-50 text-gray-500 font-semibold uppercase">
                                                <tr>
                                                    <th class="px-4 py-3 text-left">Nama / NIM</th>
                                                    <th class="px-4 py-3 text-center">Status Peminjaman</th>
                                                    <th class="px-4 py-3 text-center">Surat Bebas Lab</th>
                                                    <th class="px-4 py-3 text-center">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-150 bg-white">
                                                @forelse($students as $s)
                                                    @php
                                                        $activeLoans = $s->transactions->whereNotIn('status', ['selesai', 'ditolak'])->count();
                                                        $hasCertificate = $s->bebasLabCertificate !== null;
                                                    @endphp
                                                    <tr 
                                                        x-show="searchBebasLabStudent === '' || '{{ strtolower(addslashes($s->name)) }}'.includes(searchBebasLabStudent.toLowerCase()) || '{{ strtolower($s->nomor_induk) }}'.includes(searchBebasLabStudent.toLowerCase())"
                                                        class="hover:bg-gray-50/50 transition"
                                                    >
                                                        <td class="px-4 py-3">
                                                            <div class="font-bold text-gray-900">{{ $s->name }}</div>
                                                            <div class="text-[10px] text-gray-500">NIM: {{ $s->nomor_induk }}</div>
                                                        </td>
                                                        <td class="px-4 py-3 text-center">
                                                            @if($activeLoans > 0)
                                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-750 border border-amber-200">
                                                                    ⚠️ {{ $activeLoans }} Peminjaman Aktif
                                                                </span>
                                                            @else
                                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                                    ✔ Bersih / Tidak Ada
                                                                </span>
                                                            @endif
                                                        </td>
                                                        <td class="px-4 py-3 text-center font-medium">
                                                            @if($hasCertificate)
                                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-teal-50 text-teal-700 border border-teal-200">
                                                                    Telah Terbit
                                                                </span>
                                                                <div class="text-[9px] text-gray-400 mt-0.5">{{ $s->bebasLabCertificate->nomor_surat }}</div>
                                                            @else
                                                                <span class="text-gray-400 italic">Belum Terbit</span>
                                                            @endif
                                                        </td>
                                                        <td class="px-4 py-3 text-center whitespace-nowrap">
                                                            @if($hasCertificate)
                                                                <div class="flex items-center justify-center gap-1.5">
                                                                    <a 
                                                                        href="{{ route('bebas-lab.pdf', $s->bebasLabCertificate->id) }}?action=preview" 
                                                                        target="_blank"
                                                                        class="px-2 py-1 text-[10px] font-bold text-teal-700 bg-teal-50 border border-teal-150 rounded hover:bg-teal-100 transition shadow-xs"
                                                                    >
                                                                        👁 Lihat
                                                                    </a>
                                                                    <a 
                                                                        href="{{ route('bebas-lab.pdf', $s->bebasLabCertificate->id) }}?action=download" 
                                                                        class="px-2 py-1 text-[10px] font-bold text-white bg-teal-600 border border-teal-500 rounded hover:bg-teal-700 transition shadow-xs"
                                                                    >
                                                                        Unduh
                                                                    </a>
                                                                </div>
                                                            @else
                                                                <button 
                                                                    type="button"
                                                                    @click="
                                                                        bebasLabUserId = '{{ $s->id }}';
                                                                        bebasLabStudentName = '{{ htmlspecialchars($s->name, ENT_QUOTES) }}';
                                                                        bebasLabNomorSurat = 'No: {{ $certificates->count() + 1 }}/BL/LAB-KEP/' + new Date().getFullYear();
                                                                        isCreateBebasLabOpen = true;
                                                                    "
                                                                    @if($activeLoans > 0)
                                                                        disabled
                                                                        title="Mahasiswa masih memiliki peminjaman aktif atau pending"
                                                                        class="px-2.5 py-1 text-[10px] font-bold text-gray-400 bg-gray-100 border border-gray-200 rounded cursor-not-allowed"
                                                                    @else
                                                                        class="px-2.5 py-1 text-[10px] font-bold text-white bg-indigo-600 border border-indigo-500 rounded hover:bg-indigo-750 transition shadow-sm"
                                                                    @endif
                                                                >
                                                                    Terbitkan Surat
                                                                </button>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4" class="px-4 py-8 text-center text-gray-400">Tidak ada mahasiswa terdaftar.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- RIGHT PANEL: SURAT YANG SUDAH TERBIT & BISA DIBATALKAN -->
                                <div class="lg:col-span-5 space-y-4">
                                    <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider">Riwayat Surat Terbit</h4>
                                    <div class="space-y-3 max-h-[480px] overflow-y-auto pr-1">
                                        @forelse($certificates as $cert)
                                            <div class="p-3.5 bg-white border border-gray-150 rounded-lg shadow-xs space-y-2 relative">
                                                <div class="flex justify-between items-start">
                                                    <div>
                                                        <span class="text-[9px] font-bold text-teal-800 uppercase tracking-wider block">🎓 Surat Bebas Lab</span>
                                                        <h5 class="font-bold text-gray-900 text-xs mt-0.5">{{ $cert->user ? $cert->user->name : 'Mahasiswa Dihapus' }}</h5>
                                                        <p class="text-[10px] text-gray-500">NIM: {{ $cert->user ? $cert->user->nomor_induk : '-' }}</p>
                                                    </div>
                                                    <form method="POST" action="/bebas-lab/{{ $cert->id }}" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan/menghapus surat bebas lab untuk mahasiswa ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-500 hover:text-red-700 text-[10px] font-bold border border-red-200 bg-red-50 px-2 py-0.5 rounded transition">
                                                            Batalkan
                                                        </button>
                                                    </form>
                                                </div>
                                                <div class="bg-gray-50/60 p-2 rounded text-[10px] border border-gray-100 flex justify-between">
                                                    <div>
                                                        <span class="text-gray-400 block">Nomor Surat:</span>
                                                        <span class="font-bold text-gray-700">{{ $cert->nomor_surat }}</span>
                                                    </div>
                                                    <div class="text-right">
                                                        <span class="text-gray-400 block">Tanggal Terbit:</span>
                                                        <span class="font-bold text-gray-700">{{ $cert->tanggal_terbit->translatedFormat('d M Y') }}</span>
                                                    </div>
                                                </div>
                                                <div class="flex gap-2">
                                                    <a 
                                                        href="{{ route('bebas-lab.pdf', $cert->id) }}?action=preview" 
                                                        target="_blank"
                                                        class="flex-1 text-center py-1.5 text-[10px] font-bold text-teal-700 bg-teal-50 border border-teal-150 rounded hover:bg-teal-100 transition"
                                                    >
                                                        👁 Pratinjau
                                                    </a>
                                                    <a 
                                                        href="{{ route('bebas-lab.pdf', $cert->id) }}?action=download" 
                                                        class="flex-1 text-center py-1.5 text-[10px] font-bold text-white bg-teal-600 border border-teal-500 rounded hover:bg-teal-700 transition"
                                                    >
                                                        📥 Unduh PDF
                                                    </a>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="p-8 text-center text-gray-400 text-xs border border-dashed border-gray-200 rounded-lg">Belum ada surat bebas lab yang diterbitkan.</div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

        <!-- GOOGLE SHEET IMPORT MODAL -->
        <div x-show="isImportOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-transition>
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="isImportOpen = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg px-6 pt-5 pb-6 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-150">
                    <div class="mb-4 flex justify-between items-center border-b border-gray-100 pb-3">
                        <h4 class="text-lg font-bold text-gray-900">Impor Inventaris via Google Sheet</h4>
                        <button @click="isImportOpen = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('items.import') }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">URL Google Sheet</label>
                            <input type="url" name="sheet_url" required placeholder="https://docs.google.com/spreadsheets/d/.../edit" class="w-full text-sm border-gray-300 rounded-md focus:ring-teal-500 focus:border-teal-500" />
                            <p class="text-xs text-gray-405 mt-1 text-gray-500">Pastikan Spreadsheet disetel ke <strong>Siapa saja yang memiliki link dapat melihat</strong> (Public).</p>
                        </div>

                        <!-- Instructions Alert -->
                        <div class="p-3 bg-teal-50 rounded border border-teal-150 text-xs text-teal-800 space-y-1">
                            <span class="font-bold">Format Kolom Google Sheet:</span>
                            <p>Urutan kolom dari kiri ke kanan (A s.d N / 14 kolom):</p>
                            <ol class="list-decimal list-inside pl-1 text-[11px] space-y-0.5">
                                <li><strong>Kode Barang</strong> (Unique, e.g. ALT-001)</li>
                                <li><strong>Nama Barang</strong> (e.g. Stetoskop)</li>
                                <li><strong>Kategori</strong> (alat atau bahan)</li>
                                <li><strong>Merk / Type</strong></li>
                                <li><strong>Stok Total</strong> (Jumlah Keseluruhan)</li>
                                <li><strong>Stok Tersedia</strong> (Jumlah Ketersediaan)</li>
                                <li><strong>Jumlah Baik</strong> (Hanya untuk alat)</li>
                                <li><strong>Jumlah Rusak Ringan</strong></li>
                                <li><strong>Jumlah Rusak Sedang</strong></li>
                                <li><strong>Jumlah Rusak Berat</strong></li>
                                <li><strong>Lokasi Rak</strong> (e.g. Rak A-3)</li>
                                <li><strong>Satuan</strong> (e.g. pcs, set, unit)</li>
                                <li><strong>Tahun Kedatangan</strong> (e.g. 2024)</li>
                                <li><strong>Tindak Lanjut</strong> (kalibrasi, perawatan, penghapusan, perbaikan)</li>
                            </ol>
                            <p class="text-[10px] text-teal-700 mt-1"><strong>PENTING:</strong> Saat impor dilakukan, data di database akan diselaraskan dengan isi Google Sheet terbaru, dan semua alat/bahan yang tidak tertera dalam file Google Sheet tersebut akan <strong>dihapus secara otomatis</strong> dari aplikasi.</p>
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                            <button type="button" @click="isImportOpen = false" class="px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700 bg-white hover:bg-gray-50 font-medium">Batal</button>
                            <button type="submit" class="px-4 py-2 border border-transparent rounded-md text-sm text-white bg-teal-600 hover:bg-teal-700 font-semibold shadow-sm">Mulai Impor</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- CREATE ALAT MODAL -->
        <div x-show="isCreateAlatOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-transition>
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="isCreateAlatOpen = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg px-6 pt-5 pb-6 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-150">
                    <div class="mb-4 flex justify-between items-center border-b border-gray-100 pb-3">
                        <h4 class="text-lg font-bold text-gray-900 font-sans">Tambah Inventaris Alat</h4>
                        <button @click="isCreateAlatOpen = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('items.store') }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="kategori" value="alat" />

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Kode Alat</label>
                                <input type="text" name="kode_barang" required placeholder="e.g. ALT-001" class="w-full text-sm border-gray-300 rounded-md focus:ring-teal-500 focus:border-teal-500" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Merk/Type</label>
                                <input type="text" name="merk_tipe" placeholder="e.g. Littmann III" class="w-full text-sm border-gray-300 rounded-md focus:ring-teal-500 focus:border-teal-500" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Alat</label>
                            <input type="text" name="nama_barang" required placeholder="e.g. Stetoskop" class="w-full text-sm border-gray-300 rounded-md focus:ring-teal-500 focus:border-teal-500" />
                        </div>

                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-green-700 mb-1">Alat Baik</label>
                                <input type="number" name="jumlah_baik" min="0" required value="0" class="w-full text-sm border-gray-300 rounded-md focus:ring-teal-500 focus:border-teal-500" />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-yellow-600 mb-1">Alat Perbaikan</label>
                                <input type="number" name="jumlah_perbaikan" min="0" required value="0" class="w-full text-sm border-gray-300 rounded-md focus:ring-teal-500 focus:border-teal-500" />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-red-600 mb-1">Alat Rusak</label>
                                <input type="number" name="jumlah_rusak" min="0" required value="0" class="w-full text-sm border-gray-300 rounded-md focus:ring-teal-500 focus:border-teal-500" />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Satuan</label>
                                <input type="text" name="satuan" required placeholder="e.g. pcs, unit, set" value="pcs" class="w-full text-sm border-gray-300 rounded-md focus:ring-teal-500 focus:border-teal-500" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi Rak</label>
                                <input type="text" name="lokasi_rak" required placeholder="Contoh: Rak A-3" class="w-full text-sm border-gray-300 rounded-md focus:ring-teal-500 focus:border-teal-500" />
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                            <button type="button" @click="isCreateAlatOpen = false" class="px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700 bg-white hover:bg-gray-50 font-medium">Batal</button>
                            <button type="submit" class="px-4 py-2 border border-transparent rounded-md text-sm text-white bg-teal-600 hover:bg-teal-700 font-semibold shadow-sm">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- CREATE BAHAN MODAL -->
        <div x-show="isCreateBahanOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-transition>
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="isCreateBahanOpen = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg px-6 pt-5 pb-6 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-150">
                    <div class="mb-4 flex justify-between items-center border-b border-gray-100 pb-3">
                        <h4 class="text-lg font-bold text-gray-900 font-sans">Tambah Inventaris Bahan</h4>
                        <button @click="isCreateBahanOpen = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('items.store') }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="kategori" value="bahan" />

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Kode Bahan</label>
                                <input type="text" name="kode_barang" required placeholder="e.g. BHN-001" class="w-full text-sm border-gray-300 rounded-md focus:ring-teal-500 focus:border-teal-500" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Merk/Type</label>
                                <input type="text" name="merk_tipe" placeholder="e.g. OneMed" class="w-full text-sm border-gray-300 rounded-md focus:ring-teal-500 focus:border-teal-500" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Bahan</label>
                            <input type="text" name="nama_barang" required placeholder="e.g. Kasa Steril" class="w-full text-sm border-gray-305 rounded-md focus:ring-teal-500 focus:border-teal-500" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Stock Bahan Tersedia</label>
                            <input type="number" name="stok_total" min="0" required value="0" class="w-full text-sm border-gray-300 rounded-md focus:ring-teal-500 focus:border-teal-500" />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Satuan</label>
                                <input type="text" name="satuan" required placeholder="e.g. pcs, box, roll" value="pcs" class="w-full text-sm border-gray-300 rounded-md focus:ring-teal-500 focus:border-teal-500" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi Rak</label>
                                <input type="text" name="lokasi_rak" required placeholder="Contoh: Rak B-1" class="w-full text-sm border-gray-300 rounded-md focus:ring-teal-500 focus:border-teal-500" />
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                            <button type="button" @click="isCreateBahanOpen = false" class="px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700 bg-white hover:bg-gray-50 font-medium">Batal</button>
                            <button type="submit" class="px-4 py-2 border border-transparent rounded-md text-sm text-white bg-teal-600 hover:bg-teal-700 font-semibold shadow-sm">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- EDIT MODAL (FOR BOTH ALAT AND BAHAN) -->
        <div x-show="isEditOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-transition>
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="isEditOpen = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg px-6 pt-5 pb-6 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-150">
                    <div class="mb-4 flex justify-between items-center border-b border-gray-100 pb-3">
                        <h4 class="text-lg font-bold text-gray-900 font-sans">Edit Inventaris (<span x-text="editItem.kategori === 'alat' ? 'Alat' : 'Bahan'"></span>)</h4>
                        <button @click="isEditOpen = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <form method="POST" :action="'/items/' + editItem.id" class="space-y-4">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="kategori" x-model="editItem.kategori" />

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Kode Barang</label>
                                <input type="text" name="kode_barang" x-model="editItem.kode_barang" required class="w-full text-sm border-gray-300 rounded-md focus:ring-teal-500 focus:border-teal-500" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Merk/Type</label>
                                <input type="text" name="merk_tipe" x-model="editItem.merk_tipe" class="w-full text-sm border-gray-300 rounded-md focus:ring-teal-500 focus:border-teal-500" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Barang</label>
                            <input type="text" name="nama_barang" x-model="editItem.nama_barang" required class="w-full text-sm border-gray-300 rounded-md focus:ring-teal-500 focus:border-teal-500" />
                        </div>

                        <!-- Shown only for Alat -->
                        <div x-show="editItem.kategori === 'alat'" class="space-y-3">
                            <div class="grid grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-green-700 mb-1">Alat Baik</label>
                                    <input type="number" name="jumlah_baik" x-model="editItem.jumlah_baik" min="0" required class="w-full text-sm border-gray-300 rounded-md focus:ring-teal-500 focus:border-teal-500" />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-yellow-600 mb-1">Alat Perbaikan</label>
                                    <input type="number" name="jumlah_perbaikan" x-model="editItem.jumlah_perbaikan" min="0" required class="w-full text-sm border-gray-300 rounded-md focus:ring-teal-500 focus:border-teal-500" />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-red-600 mb-1">Alat Rusak</label>
                                    <input type="number" name="jumlah_rusak" x-model="editItem.jumlah_rusak" min="0" required class="w-full text-sm border-gray-300 rounded-md focus:ring-teal-500 focus:border-teal-500" />
                                </div>
                            </div>
                        </div>

                        <!-- Shown only for Bahan -->
                        <div x-show="editItem.kategori === 'bahan'">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Keseluruhan (Stok Total)</label>
                                <input type="number" name="stok_total" x-model="editItem.stok_total" min="0" class="w-full text-sm border-gray-300 rounded-md focus:ring-teal-500 focus:border-teal-500" />
                            </div>
                        </div>

                        <!-- Stok Tersedia Input -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Stok Tersedia (Ketersediaan di Pinjam)</label>
                            <input type="number" name="stok_tersedia" x-model="editItem.stok_tersedia" min="0" required class="w-full text-sm border-gray-300 rounded-md focus:ring-teal-500 focus:border-teal-500" />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Satuan</label>
                                <input type="text" name="satuan" x-model="editItem.satuan" required class="w-full text-sm border-gray-300 rounded-md focus:ring-teal-500 focus:border-teal-500" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi Rak</label>
                                <input type="text" name="lokasi_rak" x-model="editItem.lokasi_rak" required class="w-full text-sm border-gray-300 rounded-md focus:ring-teal-500 focus:border-teal-500" />
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                            <button type="button" @click="isEditOpen = false" class="px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700 bg-white hover:bg-gray-50 font-medium">Batal</button>
                            <button type="submit" class="px-4 py-2 border border-transparent rounded-md text-sm text-white bg-teal-600 hover:bg-teal-700 font-semibold shadow-sm">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- RECORD RETURN MODAL -->
        <div x-show="isReturnModalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-transition>
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="isReturnModalOpen = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg px-6 pt-5 pb-6 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-150">
                    <div class="mb-4 flex justify-between items-center border-b border-gray-100 pb-3">
                        <h4 class="text-lg font-bold text-gray-900 font-sans">Catat Pengembalian Alat</h4>
                        <button @click="isReturnModalOpen = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <form method="POST" :action="'/transactions/' + returnTxId + '/return'" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status Pengembalian</label>
                            <select name="status_pengembalian" x-model="returnStatus" class="w-full text-sm border-gray-300 rounded-md focus:ring-teal-500 focus:border-teal-500">
                                <option value="selesai">Selesai (Kembali Utuh)</option>
                                <option value="belum_selesai">Belum Selesai (Kembali dengan Catatan)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan / Keterangan</label>
                            <textarea 
                                name="catatan_pengembalian" 
                                x-model="returnCatatan" 
                                :required="returnStatus === 'belum_selesai'"
                                placeholder="Masukkan keterangan (misal: Kurang 1 stetoskop, termometer rusak, dll.)" 
                                class="w-full text-sm border-gray-300 rounded-md focus:ring-teal-500 focus:border-teal-500 h-24"
                            ></textarea>
                        </div>

                        <div class="flex justify-end gap-2 pt-3 border-t border-gray-100">
                            <button type="button" @click="isReturnModalOpen = false" class="px-4 py-2 border border-gray-300 rounded-md text-xs font-semibold text-gray-700 bg-white hover:bg-gray-50 transition duration-150">Batal</button>
                            <button type="submit" class="px-4 py-2 text-xs font-semibold rounded text-white bg-teal-600 hover:bg-teal-700 transition duration-150 shadow-sm">Simpan Status</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- CREATE PACKAGE MODAL -->
        <div x-show="isCreatePackageOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-transition>
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="isCreatePackageOpen = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg px-6 pt-5 pb-6 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-150">
                    <div class="mb-4 flex justify-between items-center border-b border-gray-100 pb-3">
                        <h4 class="text-lg font-bold text-gray-900 font-sans">Tambah Paket Baru</h4>
                        <button @click="isCreatePackageOpen = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('packages.store') }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Paket</label>
                            <input type="text" name="nama_paket" required placeholder="e.g. Paket Infus, Paket Hecting" class="w-full text-sm border-gray-300 rounded-md focus:ring-teal-500 focus:border-teal-500" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi Paket</label>
                            <textarea name="deskripsi" placeholder="e.g. Digunakan untuk praktikum pemasangan cairan infus..." class="w-full text-sm border-gray-300 rounded-md focus:ring-teal-500 focus:border-teal-500 h-16"></textarea>
                        </div>

                        <!-- Dynamic Package Items list -->
                        <div class="space-y-3 pt-3 border-t border-gray-100">
                            <div class="flex justify-between items-center">
                                <label class="block text-xs font-bold text-gray-700 uppercase">Daftar Barang dalam Paket</label>
                                <button 
                                    type="button" 
                                    @click="packageItemsList.push({ item_id: '', jumlah: 1, search: '' })"
                                    class="text-xs text-teal-600 hover:text-teal-800 font-bold"
                                >
                                    + Tambah Barang
                                </button>
                            </div>

                            <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                                <template x-for="(item, idx) in packageItemsList" :key="idx">
                                    <div class="grid grid-cols-12 gap-2 items-center border-b border-gray-100 pb-2 last:border-0">
                                        <div class="col-span-7">
                                            <input 
                                                type="text" 
                                                x-model="item.search" 
                                                placeholder="Cari barang..." 
                                                class="w-full text-[10px] border-gray-300 rounded focus:ring-teal-500 py-0.5 px-2 mb-1"
                                            />
                                            <select :name="'items['+idx+'][item_id]'" x-model="item.item_id" required class="w-full text-xs border-gray-300 rounded focus:ring-teal-500 focus:border-teal-500 py-1">
                                                <option value="" disabled selected>-- Pilih Barang --</option>
                                                <template x-for="dbItem in allItems.filter(i => !item.search || i.nama_barang.toLowerCase().includes(item.search.toLowerCase()) || i.kode_barang.toLowerCase().includes(item.search.toLowerCase()))" :key="dbItem.id">
                                                    <option :value="dbItem.id" x-text="dbItem.nama_barang + ' (' + dbItem.stok_tersedia + ' ' + dbItem.satuan + ')'"></option>
                                                </template>
                                            </select>
                                        </div>
                                        <div class="col-span-3">
                                            <input type="number" :name="'items['+idx+'][jumlah]'" x-model="item.jumlah" min="1" required class="w-full text-xs border-gray-300 rounded focus:ring-teal-500 focus:border-teal-500 py-1" />
                                        </div>
                                        <div class="col-span-2 flex justify-end">
                                            <button type="button" @click="packageItemsList.splice(idx, 1)" class="text-xs text-red-500 hover:text-red-700 font-bold">Hapus</button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                            <button type="button" @click="isCreatePackageOpen = false" class="px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700 bg-white hover:bg-gray-50 font-medium">Batal</button>
                            <button type="submit" class="px-4 py-2 border border-transparent rounded-md text-sm text-white bg-teal-600 hover:bg-teal-700 font-semibold shadow-sm">Simpan Paket</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- EDIT PACKAGE MODAL -->
        <div x-show="isEditPackageOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-transition>
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="isEditPackageOpen = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg px-6 pt-5 pb-6 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-150">
                    <div class="mb-4 flex justify-between items-center border-b border-gray-100 pb-3">
                        <h4 class="text-lg font-bold text-gray-900 font-sans">Edit Paket</h4>
                        <button @click="isEditPackageOpen = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <form method="POST" :action="'/packages/' + editPackage.id" class="space-y-4">
                        @csrf
                        @method('PUT')
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Paket</label>
                            <input type="text" name="nama_paket" x-model="editPackage.nama_paket" required class="w-full text-sm border-gray-300 rounded-md focus:ring-teal-500 focus:border-teal-500" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi Paket</label>
                            <textarea name="deskripsi" x-model="editPackage.deskripsi" class="w-full text-sm border-gray-300 rounded-md focus:ring-teal-500 focus:border-teal-500 h-16"></textarea>
                        </div>

                        <!-- Dynamic Package Items list -->
                        <div class="space-y-3 pt-3 border-t border-gray-100">
                            <div class="flex justify-between items-center">
                                <label class="block text-xs font-bold text-gray-700 uppercase">Daftar Barang dalam Paket</label>
                                <button 
                                    type="button" 
                                    @click="packageItemsList.push({ item_id: '', jumlah: 1, search: '' })"
                                    class="text-xs text-teal-600 hover:text-teal-800 font-bold"
                                >
                                    + Tambah Barang
                                </button>
                            </div>

                            <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                                <template x-for="(item, idx) in packageItemsList" :key="idx">
                                    <div class="grid grid-cols-12 gap-2 items-center border-b border-gray-100 pb-2 last:border-0">
                                        <div class="col-span-7">
                                            <input 
                                                type="text" 
                                                x-model="item.search" 
                                                placeholder="Cari barang..." 
                                                class="w-full text-[10px] border-gray-300 rounded focus:ring-teal-500 py-0.5 px-2 mb-1"
                                            />
                                            <select :name="'items['+idx+'][item_id]'" x-model="item.item_id" required class="w-full text-xs border-gray-300 rounded focus:ring-teal-500 focus:border-teal-500 py-1">
                                                <option value="" disabled selected>-- Pilih Barang --</option>
                                                <template x-for="dbItem in allItems.filter(i => !item.search || i.nama_barang.toLowerCase().includes(item.search.toLowerCase()) || i.kode_barang.toLowerCase().includes(item.search.toLowerCase()))" :key="dbItem.id">
                                                    <option :value="dbItem.id" x-text="dbItem.nama_barang + ' (' + dbItem.stok_tersedia + ' ' + dbItem.satuan + ')'"></option>
                                                </template>
                                            </select>
                                        </div>
                                        <div class="col-span-3">
                                            <input type="number" :name="'items['+idx+'][jumlah]'" x-model="item.jumlah" min="1" required class="w-full text-xs border-gray-300 rounded focus:ring-teal-500 focus:border-teal-500 py-1" />
                                        </div>
                                        <div class="col-span-2 flex justify-end">
                                            <button type="button" @click="packageItemsList.splice(idx, 1)" class="text-xs text-red-500 hover:text-red-700 font-bold">Hapus</button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                            <button type="button" @click="isEditPackageOpen = false" class="px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700 bg-white hover:bg-gray-50 font-medium">Batal</button>
                            <button type="submit" class="px-4 py-2 border border-transparent rounded-md text-sm text-white bg-teal-600 hover:bg-teal-700 font-semibold shadow-sm">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- INCIDENTAL TRANSACTION MODAL -->
        <div x-show="isCreateIncidentalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-transition:enter="transition ease-out duration-250" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="isCreateIncidentalOpen = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg px-6 pt-5 pb-6 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-150">
                    <div class="mb-4 flex justify-between items-center border-b border-gray-100 pb-3">
                        <h4 class="text-lg font-bold text-gray-900 font-sans">Pencatatan Transaksi Insidentil</h4>
                        <button @click="isCreateIncidentalOpen = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('transactions.store') }}" class="space-y-4 text-xs">
                        @csrf
                        <input type="hidden" name="is_insidentil" value="1" />

                        <div>
                            <label class="block text-gray-700 mb-1 font-semibold">Nama Peminjam / Instansi</label>
                            <input type="text" name="peminjam_insidentil" required placeholder="Contoh: dr. John Doe / Puskesmas Cilandak" class="w-full text-xs border-gray-300 rounded focus:ring-teal-500 focus:border-teal-500" />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-700 mb-1 font-semibold">Tipe Transaksi</label>
                                <select name="tipe" x-model="incidentalType" class="w-full text-xs border-gray-300 rounded focus:ring-teal-500 focus:border-teal-500">
                                    <option value="peminjaman_alat">Peminjaman Alat</option>
                                    <option value="permintaan_bahan">Permintaan Bahan</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-1 font-semibold">Dosen Penanggung Jawab / Pengawas</label>
                                <input type="text" name="penanggung_jawab" required placeholder="Nama pengawas/penanggung jawab" class="w-full text-xs border-gray-300 rounded focus:ring-teal-500 focus:border-teal-500" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-gray-700 mb-1 font-semibold">Mata Kuliah / Kegiatan / Keperluan</label>
                            <textarea name="kegiatan" required placeholder="Tujuan pemakaian alat/bahan..." class="w-full text-xs border-gray-300 rounded focus:ring-teal-500 focus:border-teal-500 h-16"></textarea>
                        </div>

                        <!-- Schedule dates only for tools -->
                        <div class="grid grid-cols-2 gap-4" x-show="incidentalType === 'peminjaman_alat'">
                            <div>
                                <label class="block text-gray-700 mb-1 font-semibold">Tanggal Pinjam</label>
                                <input type="datetime-local" name="tanggal_pinjam" :required="incidentalType === 'peminjaman_alat'" class="w-full text-xs border-gray-300 rounded focus:ring-teal-500 focus:border-teal-500" />
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-1 font-semibold">Rencana Pengembalian</label>
                                <input type="datetime-local" name="tanggal_kembali_rencana" :required="incidentalType === 'peminjaman_alat'" class="w-full text-xs border-gray-300 rounded focus:ring-teal-500 focus:border-teal-500" />
                            </div>
                        </div>

                        <!-- SECTION 1: PACKAGES REQUEST (OPTIONAL) -->
                        <div class="p-3 bg-teal-50/20 border border-teal-100 rounded-lg space-y-3">
                            <div class="flex justify-between items-center pb-1 border-b border-teal-100/50">
                                <div>
                                    <h4 class="text-xs font-semibold text-teal-900">Peminjaman Paket (Opsional)</h4>
                                    <p class="text-[9px] text-gray-500">Pilih paket siap pakai yang dibutuhkan.</p>
                                </div>
                                <button 
                                    type="button" 
                                    @click="incidentalPackagesList.push({ package_id: '', package_qty: 1, search: '' })"
                                    class="text-[10px] text-teal-700 hover:text-teal-950 font-bold bg-teal-100/50 hover:bg-teal-100 px-2 py-1 rounded transition duration-150"
                                >
                                    + Tambah Paket
                                </button>
                            </div>

                            <div class="space-y-3">
                                <template x-for="(pkg, pIdx) in incidentalPackagesList" :key="'pkg-'+pIdx">
                                    <div class="p-3 bg-white rounded border border-teal-100/60 shadow-sm relative space-y-2">
                                        <div class="grid grid-cols-12 gap-2 items-center">
                                            <!-- Package choice -->
                                            <div class="col-span-7">
                                                <input 
                                                    type="text" 
                                                    x-model="pkg.search" 
                                                    placeholder="Ketik nama paket..." 
                                                    class="w-full text-[9px] border-gray-300 rounded focus:ring-teal-500 focus:border-teal-500 py-0.5 px-2 mb-1"
                                                />
                                                <select 
                                                    x-model="pkg.package_id"
                                                    class="w-full text-xs border-gray-300 rounded focus:ring-teal-500 focus:border-teal-500 py-1"
                                                    required
                                                >
                                                    <option value="" disabled selected>-- Pilih Paket --</option>
                                                    <template x-for="dbPkg in allPackages.filter(p => !pkg.search || p.nama_paket.toLowerCase().includes(pkg.search.toLowerCase()))" :key="dbPkg.id">
                                                        <option :value="dbPkg.id" x-text="dbPkg.nama_paket"></option>
                                                    </template>
                                                </select>
                                            </div>

                                            <!-- Package Qty -->
                                            <div class="col-span-3">
                                                <input 
                                                    type="number" 
                                                    x-model="pkg.package_qty"
                                                    min="1"
                                                    placeholder="Qty"
                                                    class="w-full text-xs border-gray-300 rounded focus:ring-teal-500 focus:border-teal-500 py-1"
                                                    required
                                                />
                                            </div>

                                            <!-- Remove package button -->
                                            <div class="col-span-2 flex justify-end">
                                                <button 
                                                    type="button" 
                                                    @click="incidentalPackagesList.splice(pIdx, 1)"
                                                    class="text-xs text-red-550 hover:text-red-750 font-bold bg-transparent border-0"
                                                >
                                                    Hapus
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Package Items multiplied info list -->
                                        <div class="text-[10px] text-gray-500 border-t border-dashed border-gray-200 pt-1.5" x-show="pkg.package_id">
                                            <strong class="text-teal-900">Isi Paket (Pengali x <span x-text="pkg.package_qty || 1"></span>):</strong>
                                            <ul class="list-disc list-inside mt-1 space-y-0.5">
                                                <template x-for="pi in allPackages.find(p => p.id == pkg.package_id)?.items || []">
                                                    <li x-show="(incidentalType === 'peminjaman_alat' && pi.item.kategori === 'alat') || (incidentalType === 'permintaan_bahan' && pi.item.kategori === 'bahan')" class="text-[10px]">
                                                        <span x-text="pi.item.nama_barang" class="font-medium text-gray-700"></span>: 
                                                        <span class="font-bold text-gray-900" x-text="(pi.jumlah * (pkg.package_qty || 1))"></span> 
                                                        <span x-text="pi.item.satuan" class="italic text-gray-400"></span>
                                                        <span class="text-red-500 font-bold ml-1" x-show="getIncidentalItemTotalRequested(pi.item_id) > pi.item.stok_tersedia">
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
                        <div class="p-3 bg-gray-50/50 border border-gray-200 rounded-lg space-y-3">
                            <div class="flex justify-between items-center pb-1 border-b border-gray-200">
                                <div>
                                    <h4 class="text-xs font-semibold text-gray-900">Peminjaman Barang Satuan (Opsional)</h4>
                                    <p class="text-[9px] text-gray-550">Tambahkan barang medis eceran di luar paket.</p>
                                </div>
                                <button 
                                    type="button" 
                                    @click="incidentalItemsList.push({ item_id: '', jumlah_diminta: 1, search: '' })"
                                    class="text-[10px] text-teal-650 hover:text-teal-850 font-bold bg-white border border-gray-250 px-2 py-1 rounded transition duration-150"
                                >
                                    + Tambah Satuan
                                </button>
                            </div>

                            <div class="space-y-3">
                                <template x-for="(item, index) in incidentalItemsList" :key="'satuan-'+index">
                                    <div class="p-3 bg-white rounded border border-gray-200 shadow-sm relative space-y-2">
                                        <div class="grid grid-cols-12 gap-2 items-center">
                                            <!-- Dropdown Item Selection with search bar -->
                                            <div class="col-span-7">
                                                <input 
                                                    type="text" 
                                                    x-model="item.search" 
                                                    placeholder="Ketik nama / kode..." 
                                                    class="w-full text-[9px] border-gray-300 rounded focus:ring-teal-500 focus:border-teal-500 py-0.5 px-2 mb-1"
                                                />
                                                <select 
                                                    x-model="item.item_id"
                                                    class="w-full text-xs border-gray-300 rounded focus:ring-teal-500 focus:border-teal-500 py-1"
                                                    required
                                                >
                                                    <option value="" disabled selected>-- Pilih Barang --</option>
                                                    <template x-for="dbItem in allItems.filter(i => i.kategori === (incidentalType === 'peminjaman_alat' ? 'alat' : 'bahan') && (!item.search || i.nama_barang.toLowerCase().includes(item.search.toLowerCase()) || i.kode_barang.toLowerCase().includes(item.search.toLowerCase())))" :key="dbItem.id">
                                                        <option 
                                                            :value="dbItem.id"
                                                            :disabled="dbItem.stok_tersedia <= 0 || dbItem.status === 'rusak'"
                                                            x-text="dbItem.nama_barang + ' (' + dbItem.stok_tersedia + ' ' + dbItem.satuan + ')'"
                                                        ></option>
                                                    </template>
                                                </select>
                                            </div>

                                            <!-- Quantity input -->
                                            <div class="col-span-3">
                                                <input 
                                                    type="number" 
                                                    x-model="item.jumlah_diminta"
                                                    min="1" 
                                                    placeholder="Qty"
                                                    class="w-full text-xs border-gray-300 rounded focus:ring-teal-500 focus:border-teal-500 py-1"
                                                    required
                                                />
                                            </div>

                                            <!-- Remove button -->
                                            <div class="col-span-2 flex justify-end">
                                                <button 
                                                    type="button" 
                                                    @click="incidentalItemsList.splice(index, 1)"
                                                    class="text-xs text-red-550 hover:text-red-770 font-bold"
                                                >
                                                    Hapus
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Realtime Stock Warning -->
                                        <template x-if="item.item_id && getIncidentalItemTotalRequested(item.item_id) > (allItems.find(i => i.id == item.item_id)?.stok_tersedia || 0)">
                                            <p class="text-[9px] text-red-550 font-bold mt-1 leading-tight">
                                                ⚠️ Melebihi stok (Maks: <span x-text="allItems.find(i => i.id == item.item_id)?.stok_tersedia"></span>, Total Diajukan: <span x-text="getIncidentalItemTotalRequested(item.item_id)"></span>)
                                            </p>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- HIDDEN payload data inputs computed from incidentalPackagesList and incidentalItemsList -->
                        <div class="hidden">
                            <template x-for="(fItem, idx) in getFinalIncidentalItems()" :key="'final-'+idx">
                                <div>
                                    <input type="hidden" :name="'items['+idx+'][item_id]'" :value="fItem.item_id" />
                                    <input type="hidden" :name="'items['+idx+'][jumlah_diminta]'" :value="fItem.jumlah_diminta" />
                                    <input type="hidden" :name="'items['+idx+'][package_id]'" :value="fItem.package_id" />
                                    <input type="hidden" :name="'items['+idx+'][package_qty]'" :value="fItem.package_qty" />
                                </div>
                            </template>
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t border-gray-150">
                            <button type="button" @click="isCreateIncidentalOpen = false" class="px-4 py-2 border border-gray-300 rounded text-gray-700 bg-white hover:bg-gray-50 font-medium">Batal</button>
                            <button 
                                type="submit" 
                                :disabled="getFinalIncidentalItems().length === 0 || hasIncidentalStockError()"
                                :class="(getFinalIncidentalItems().length === 0 || hasIncidentalStockError()) ? 'bg-gray-400 cursor-not-allowed opacity-50' : 'bg-indigo-600 hover:bg-indigo-700'"
                                class="px-4 py-2 rounded text-white font-semibold shadow-sm transition duration-150"
                            >
                                Simpan Transaksi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
                    </div>
                </div>
            </div>
        <!-- CREATE BEBAS LAB MODAL -->
        <div x-show="isCreateBebasLabOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-transition>
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="isCreateBebasLabOpen = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg px-6 pt-5 pb-6 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-gray-150">
                    <div class="mb-4 flex justify-between items-center border-b border-gray-100 pb-3">
                        <h4 class="text-base font-bold text-gray-900 font-sans">Terbitkan Surat Bebas Lab</h4>
                        <button @click="isCreateBebasLabOpen = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('bebas-lab.store') }}" class="space-y-4 text-xs">
                        @csrf
                        <input type="hidden" name="user_id" :value="bebasLabUserId" />

                        <div>
                            <span class="text-gray-450 block mb-0.5 font-medium">Nama Mahasiswa:</span>
                            <div class="text-sm font-bold text-teal-800" x-text="bebasLabStudentName"></div>
                        </div>

                        <div>
                            <label class="block text-gray-700 mb-1 font-semibold">Nomor Surat Resmi</label>
                            <input 
                                type="text" 
                                name="nomor_surat" 
                                x-model="bebasLabNomorSurat" 
                                required 
                                placeholder="Contoh: No: 001/BL/LAB-KEP/2026" 
                                class="w-full text-xs border-gray-300 rounded focus:ring-teal-500 focus:border-teal-500 py-1.5 px-3" 
                            />
                            <p class="text-[9px] text-gray-400 mt-1">Sistem menyarankan nomor berdasarkan total surat terbit saat ini. Anda dapat mengedit format nomor surat ini secara manual.</p>
                        </div>

                        <div class="flex justify-end gap-2 pt-4 border-t border-gray-100">
                            <button type="button" @click="isCreateBebasLabOpen = false" class="px-4 py-2 border border-gray-300 rounded text-gray-700 bg-white hover:bg-gray-50 font-medium">Batal</button>
                            <button type="submit" class="px-4 py-2 rounded text-white bg-indigo-600 hover:bg-indigo-750 font-semibold shadow-sm">Terbitkan & Kirim</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<x-transaction-edit-modal :items="$items" :packages="$packages" />
