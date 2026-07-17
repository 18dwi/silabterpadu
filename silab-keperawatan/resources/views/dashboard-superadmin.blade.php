<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ Auth::user()->role === 'ultraadmin' ? __('Dashboard Ultra Admin - Si-Lab Terpadu') : __('Dashboard Superadmin - Si-Lab ') . ucwords(str_replace('_', ' ', Auth::user()->jurusan)) }}
            </h2>
            <span class="text-sm bg-teal-50 text-teal-700 font-semibold px-3 py-1 rounded-full border border-teal-200 shadow-sm">
                {{ Auth::user()->role === 'ultraadmin' ? 'Ultra Admin Panel' : 'Superadmin Panel' }}
            </span>
        </div>
    </x-slot>

    <div class="py-10 bg-gray-50 min-h-screen" 
         x-data="{ 
             activeTab: localStorage.getItem('superadminActiveTab') || 'laporan_rekap', 
             searchUser: '',
             searchHistory: '',
             isCreateUserOpen: false,
             isEditUserOpen: false,
             isImportUsersOpen: false,
             editUser: { id: '', name: '', nomor_induk: '', email: '', role: 'mahasiswa', jurusan: 'keperawatan', password: '' }
         }"
         x-init="$watch('activeTab', val => localStorage.setItem('superadminActiveTab', val))">
        
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Alerts -->
            @if(session('success'))
                <div class="bg-teal-50 border-l-4 border-teal-500 p-4 mb-6 rounded-md shadow-sm transition duration-150">
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
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-md shadow-sm transition duration-150">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-600" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414-1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
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
                            <h3 class="text-sm font-bold text-teal-800 tracking-wide leading-tight">
                                {{ Auth::user()->role === 'ultraadmin' ? 'Si-Lab Terpadu' : 'Si-Lab ' . ucwords(str_replace('_', ' ', Auth::user()->jurusan)) }}
                            </h3>
                            <p class="text-[9px] text-gray-400 font-bold uppercase tracking-widest mt-1.5">Poltekkes Jakarta I</p>
                        </div>

                        <!-- Sidebar Menus -->
                        <div class="w-full space-y-2.5 pt-4 border-t border-gray-100">
                            
                            <!-- Tab: Laporan & Statistik -->
                            <button 
                                @click="activeTab = 'laporan_rekap'"
                                :class="activeTab === 'laporan_rekap' ? 'bg-teal-600 text-white font-semibold shadow-md ring-1 ring-teal-500' : 'bg-white text-gray-700 hover:bg-slate-50 border border-slate-200'"
                                class="w-full text-left px-4 py-3 rounded-lg text-xs transition-all duration-150 flex items-center gap-2.5 shadow-sm"
                            >
                                <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                                Laporan & Statistik
                            </button>

                            <!-- Tab: Kelola Akun Pengguna -->
                            <button 
                                @click="activeTab = 'kelola_akun'"
                                :class="activeTab === 'kelola_akun' ? 'bg-teal-600 text-white font-semibold shadow-md ring-1 ring-teal-500' : 'bg-white text-gray-700 hover:bg-slate-50 border border-slate-200'"
                                class="w-full text-left px-4 py-3 rounded-lg text-xs transition-all duration-150 flex items-center gap-2.5 shadow-sm"
                            >
                                <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                Kelola Akun Pengguna
                            </button>

                            <!-- Tab: Riwayat Transaksi (Hapus) -->
                            <button 
                                @click="activeTab = 'riwayat_transaksi'"
                                :class="activeTab === 'riwayat_transaksi' ? 'bg-teal-600 text-white font-semibold shadow-md ring-1 ring-teal-500' : 'bg-white text-gray-700 hover:bg-slate-50 border border-slate-200'"
                                class="w-full text-left px-4 py-3 rounded-lg text-xs transition-all duration-150 flex items-center gap-2.5 shadow-sm"
                            >
                                <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Bersihkan Riwayat
                            </button>
                        </div>
                    </div>
                </div>

                <!-- CONTENT AREA (Right Side) -->
                <div class="flex-grow">
                    
                    <!-- TAB 1: LAPORAN & STATISTIK -->
                    <div x-show="activeTab === 'laporan_rekap'" class="space-y-6" x-transition>
                        
                        <!-- Header & Date Range Filter -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4 border-b border-gray-150 pb-4">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900">Rekap Data & Statistik Praktikum</h3>
                                    <p class="text-xs text-gray-500">Analisis utilitas alat, durasi pemakaian, serta unduh format PDF/Excel resmi.</p>
                                </div>
                                
                                <div class="flex flex-wrap gap-2">
                                    @if($reportTransactions->count() > 0)
                                        <a 
                                            href="{{ route('superadmin.report.print', ['start_date' => $startDate, 'end_date' => $endDate, 'jurusan' => $jurusan]) }}"
                                            target="_blank"
                                            class="inline-flex items-center px-3 py-1.5 border border-teal-500 text-xs font-semibold rounded text-teal-600 bg-white hover:bg-teal-50 transition duration-150 shadow-sm"
                                        >
                                            📄 Cetak PDF
                                        </a>
                                        <a 
                                            href="{{ route('superadmin.report.export-csv', ['start_date' => $startDate, 'end_date' => $endDate, 'jurusan' => $jurusan]) }}"
                                            class="inline-flex items-center px-3 py-1.5 border border-green-600 text-xs font-semibold rounded text-white bg-green-600 hover:bg-green-700 transition duration-150 shadow-sm"
                                        >
                                            📥 Download Excel (CSV)
                                        </a>
                                    @endif
                                </div>
                            </div>

                            <form method="GET" action="{{ route('dashboard') }}" class="grid grid-cols-1 {{ Auth::user()->role === 'ultraadmin' ? 'sm:grid-cols-4' : 'sm:grid-cols-3' }} gap-4 mb-6 p-4 bg-slate-50 rounded-lg border border-slate-200">
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Tanggal Mulai</label>
                                    <input type="date" name="start_date" value="{{ $startDate }}" class="w-full text-xs border-gray-300 rounded focus:ring-teal-500 focus:border-teal-500 py-1" />
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Tanggal Selesai</label>
                                    <input type="date" name="end_date" value="{{ $endDate }}" class="w-full text-xs border-gray-300 rounded focus:ring-teal-500 focus:border-teal-500 py-1" />
                                </div>
                                @if(Auth::user()->role === 'ultraadmin')
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Jurusan</label>
                                        <select name="jurusan" class="w-full text-xs border-gray-300 rounded focus:ring-teal-500 focus:border-teal-500 py-1">
                                            <option value="semua" {{ $jurusan === 'semua' ? 'selected' : '' }}>Semua Jurusan</option>
                                            <option value="keperawatan" {{ $jurusan === 'keperawatan' ? 'selected' : '' }}>Keperawatan</option>
                                            <option value="kebidanan" {{ $jurusan === 'kebidanan' ? 'selected' : '' }}>Kebidanan</option>
                                            <option value="kesehatan_gigi" {{ $jurusan === 'kesehatan_gigi' ? 'selected' : '' }}>Kesehatan Gigi</option>
                                            <option value="ortotik_prostetik" {{ $jurusan === 'ortotik_prostetik' ? 'selected' : '' }}>Ortotik Prostetik</option>
                                        </select>
                                    </div>
                                @endif
                                <div class="flex items-end">
                                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded text-xs font-semibold text-white bg-teal-600 hover:bg-teal-700 transition duration-150 shadow-sm">
                                        Filter Laporan & Statistik
                                    </button>
                                </div>
                            </form>

                            @if($reportTransactions->count() > 0)
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="bg-teal-50/40 border border-teal-100 p-4 rounded text-center">
                                        <span class="text-[10px] text-gray-400 uppercase font-semibold block">Total Transaksi Aktif / Selesai</span>
                                        <span class="text-2xl font-bold text-teal-700">{{ $reportTransactions->count() }}</span>
                                    </div>
                                    <div class="bg-blue-50/40 border border-blue-100 p-4 rounded text-center">
                                        <span class="text-[10px] text-gray-400 uppercase font-semibold block">Total Barang Keluar Gudang</span>
                                        <span class="text-2xl font-bold text-blue-700">{{ $totalItemsIssued }} unit</span>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Statistics Grid -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            
                            <!-- 1. Durasi Pemakaian Alat (Hari) -->
                            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                                <h3 class="text-sm font-bold text-gray-900 mb-3 flex items-center gap-1.5">
                                    📅 Durasi Hari Pemakaian Alat
                                </h3>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-150 text-xs">
                                        <thead class="bg-gray-50 text-gray-500 font-semibold uppercase">
                                            <tr>
                                                <th class="px-3 py-2 text-left">Nama Alat</th>
                                                <th class="px-3 py-2 text-center">Durasi Pemakaian</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            @forelse($toolUsageDays as $usage)
                                                <tr>
                                                    <td class="px-3 py-2.5 font-medium text-gray-900">{{ $usage->item->nama_barang }}</td>
                                                    <td class="px-3 py-2.5 text-center font-bold text-teal-650 bg-teal-50/30 rounded">{{ $usage->total_days }} Hari</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="2" class="px-3 py-6 text-center text-gray-400">Tidak ada durasi pemakaian tercatat pada periode ini.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- 2. Alat Terbanyak Dipinjam -->
                            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                                <h3 class="text-sm font-bold text-gray-900 mb-3 flex items-center gap-1.5">
                                    📈 Frekuensi Peminjaman Alat Terbanyak
                                </h3>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-150 text-xs">
                                        <thead class="bg-gray-50 text-gray-500 font-semibold uppercase">
                                            <tr>
                                                <th class="px-3 py-2 text-left">Nama Alat</th>
                                                <th class="px-3 py-2 text-center">Frekuensi Pinjam</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            @forelse($mostBorrowed as $b)
                                                <tr>
                                                    <td class="px-3 py-2.5 font-medium text-gray-900">{{ $b->item->nama_barang }}</td>
                                                    <td class="px-3 py-2.5 text-center font-bold text-indigo-650 bg-indigo-50/30 rounded">{{ $b->total_borrowed }} Kali</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="2" class="px-3 py-6 text-center text-gray-400">Tidak ada data peminjaman alat pada periode ini.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>

                        <!-- Low stock materials -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
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
                                                <td colspan="4" class="px-3 py-6 text-center text-green-600 font-semibold">✔ Seluruh persediaan bahan habis pakai terpantau aman.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>

                    <!-- TAB 2: KELOLA AKUN PENGGUNA -->
                    <div x-show="activeTab === 'kelola_akun'" class="space-y-6" style="display: none;" x-transition>
                        
                        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900">Kelola Akun Pengguna</h3>
                                    <p class="text-xs text-gray-500">Tambah, ubah, hapus, atau impor akun mahasiswa otomatis dari Google Sheets.</p>
                                    <div class="mt-3">
                                        <input 
                                            type="text" 
                                            x-model="searchUser" 
                                            placeholder="Cari nama, NIM, email, atau role..." 
                                            class="text-xs border-gray-300 rounded-md focus:ring-teal-500 focus:border-teal-500 py-1.5 px-3 w-full sm:w-64"
                                        />
                                    </div>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <button 
                                        @click="isImportUsersOpen = true"
                                        class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-semibold rounded text-gray-700 bg-white hover:bg-gray-50 transition duration-150 shadow-sm"
                                    >
                                        🟢 Impor Google Sheet
                                    </button>
                                    <button 
                                        @click="isCreateUserOpen = true"
                                        class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-semibold rounded text-white bg-teal-600 hover:bg-teal-700 transition duration-150 shadow-sm"
                                    >
                                        + Tambah Akun Baru
                                    </button>
                                </div>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 text-xs">
                                    <thead class="bg-gray-50 text-gray-500 font-semibold uppercase">
                                        <tr>
                                            <th class="px-4 py-3 text-left">Nama</th>
                                            <th class="px-4 py-3 text-left">NIM / NIDN</th>
                                            <th class="px-4 py-3 text-left">Jurusan</th>
                                            <th class="px-4 py-3 text-left">Email</th>
                                            <th class="px-4 py-3 text-center">Role</th>
                                            <th class="px-4 py-3 text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-150">
                                        @foreach($allUsers as $u)
                                            <tr x-show="searchUser === '' || '{{ strtolower(addslashes($u->name)) }}'.includes(searchUser.toLowerCase()) || '{{ strtolower($u->nomor_induk) }}'.includes(searchUser.toLowerCase()) || '{{ strtolower($u->email) }}'.includes(searchUser.toLowerCase()) || '{{ strtolower($u->role) }}'.includes(searchUser.toLowerCase())">
                                                <td class="px-4 py-3 font-semibold text-gray-900">{{ $u->name }}</td>
                                                <td class="px-4 py-3 text-gray-600 font-bold">{{ $u->nomor_induk }}</td>
                                                <td class="px-4 py-3 text-teal-800 font-medium capitalize">{{ str_replace('_', ' ', $u->jurusan) }}</td>
                                                <td class="px-4 py-3 text-gray-500">{{ $u->email }}</td>
                                                <td class="px-4 py-3 text-center">
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                                                        {{ $u->role === 'superadmin' ? 'bg-red-50 text-red-700 border border-red-200' : ($u->role === 'laboran' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-blue-50 text-blue-700 border border-blue-200') }}">
                                                        {{ $u->role }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 text-center space-x-2">
                                                    <button 
                                                        @click="
                                                            editUser = {
                                                                id: '{{ $u->id }}',
                                                                name: '{{ addslashes($u->name) }}',
                                                                nomor_induk: '{{ $u->nomor_induk }}',
                                                                email: '{{ $u->email }}',
                                                                role: '{{ $u->role }}',
                                                                jurusan: '{{ $u->jurusan }}',
                                                                password: ''
                                                            };
                                                            isEditUserOpen = true;
                                                        "
                                                        class="text-teal-600 hover:text-teal-900 font-semibold"
                                                    >
                                                        Edit
                                                    </button>
                                                    @if($u->id !== Auth::id())
                                                        <form method="POST" action="/superadmin/users/{{ $u->id }}" class="inline" onsubmit="return confirm('Hapus akun ini?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="text-red-500 hover:text-red-700 font-semibold">Hapus</button>
                                                        </form>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>

                    <!-- TAB 3: BERSIHKAN RIWAYAT -->
                    <div x-show="activeTab === 'riwayat_transaksi'" class="space-y-6" style="display: none;" x-transition>
                        
                        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900">Penghapusan Riwayat Transaksi</h3>
                                    <p class="text-xs text-gray-500">Sebagai superadmin, Anda dapat menghapus data log transaksi untuk merapikan riwayat **tanpa** memengaruhi ataupun mengembalikan stok inventaris.</p>
                                    <div class="mt-3">
                                        <input 
                                            type="text" 
                                            x-model="searchHistory" 
                                            placeholder="Cari nomor transaksi (e.g. TX-00005) atau nama peminjam..." 
                                            class="text-xs border-gray-300 rounded-md focus:ring-teal-500 focus:border-teal-500 py-1.5 px-3 w-full sm:w-80 shadow-sm"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 text-xs">
                                    <thead class="bg-gray-50 text-gray-500 font-semibold uppercase">
                                        <tr>
                                            <th class="px-4 py-3 text-left">ID Transaksi</th>
                                            <th class="px-4 py-3 text-left">Peminjam</th>
                                            <th class="px-4 py-3 text-left">Tanggal</th>
                                            <th class="px-4 py-3 text-left">Barang</th>
                                            <th class="px-4 py-3 text-center">Status</th>
                                            <th class="px-4 py-3 text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-150 bg-white">
                                        @forelse($allTransactionsForDeletion as $tx)
                                            @php
                                                $borrowerName = $tx->is_insidentil ? $tx->peminjam_insidentil : ($tx->user ? $tx->user->name : 'Akun Dihapus');
                                                $txCode = 'TX-' . str_pad($tx->id, 5, '0', STR_PAD_LEFT);
                                            @endphp
                                            <tr 
                                                x-show="searchHistory === '' || '{{ strtolower($txCode) }}'.includes(searchHistory.toLowerCase()) || '{{ strtolower(addslashes($borrowerName)) }}'.includes(searchHistory.toLowerCase())"
                                                class="hover:bg-gray-50/50 transition-colors"
                                            >
                                                <td class="px-4 py-3 font-bold text-teal-700">{{ $txCode }}</td>
                                                <td class="px-4 py-3">
                                                    @if($tx->is_insidentil)
                                                        <span class="font-semibold text-gray-800">{{ $tx->peminjam_insidentil }}</span>
                                                        <span class="block text-[9px] text-gray-400">Insidentil (Non-Mahasiswa)</span>
                                                    @else
                                                        <span class="font-semibold text-gray-850">{{ $tx->user ? $tx->user->name : 'Akun Dihapus' }}</span>
                                                        <span class="block text-[9px] text-gray-400">NIM: {{ $tx->user ? $tx->user->nomor_induk : '-' }}</span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 text-gray-500">{{ $tx->tanggal_pengajuan->format('d M Y, H:i') }}</td>
                                                <td class="px-4 py-3">
                                                    <ul class="list-disc list-inside space-y-0.5 text-[10px]">
                                                        @foreach($tx->details as $d)
                                                            <li>{{ $d->item->nama_barang }} ({{ $d->jumlah_diminta }} {{ $d->item->satuan }})</li>
                                                        @endforeach
                                                    </ul>
                                                </td>
                                                <td class="px-4 py-3 text-center">
                                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase
                                                        {{ $tx->status === 'selesai' ? 'bg-green-50 text-green-750' : ($tx->status === 'disetujui' ? 'bg-blue-50 text-blue-700' : 'bg-amber-50 text-amber-700') }}">
                                                        {{ $tx->status }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 text-center">
                                                    <form method="POST" action="/superadmin/transactions/{{ $tx->id }}" class="inline" onsubmit="return confirm('Hapus riwayat transaksi ini secara permanen?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="bg-red-50 text-red-600 border border-red-150 px-2.5 py-1 rounded text-[10px] font-bold hover:bg-red-100 transition duration-150">
                                                            Hapus Log
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="px-4 py-8 text-center text-gray-400">Tidak ada riwayat transaksi ditemukan.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>

                </div>
            </div>

        </div>

        <!-- CREATE USER MODAL -->
        <div x-show="isCreateUserOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-transition:enter="transition ease-out duration-250" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="isCreateUserOpen = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg px-6 pt-5 pb-6 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-150">
                    <div class="mb-4 flex justify-between items-center border-b border-gray-100 pb-3">
                        <h4 class="text-lg font-bold text-gray-900 font-sans">Tambah Akun Baru</h4>
                        <button @click="isCreateUserOpen = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('superadmin.users.store') }}" class="space-y-4 text-xs">
                        @csrf
                        <div>
                            <label class="block text-gray-700 mb-1 font-semibold">Nama Lengkap</label>
                            <input type="text" name="name" required placeholder="Contoh: Budi Santoso" class="w-full text-xs border-gray-300 rounded focus:ring-teal-500 focus:border-teal-500" />
                        </div>
                        <div class="grid grid-cols-1 {{ Auth::user()->role === 'ultraadmin' ? 'sm:grid-cols-3' : 'sm:grid-cols-2' }} gap-4">
                            <div>
                                <label class="block text-gray-700 mb-1 font-semibold">NIM / NIDN</label>
                                <input type="text" name="nomor_induk" required placeholder="Contoh: P0712412..." class="w-full text-xs border-gray-300 rounded focus:ring-teal-500 focus:border-teal-500" />
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-1 font-semibold">Role Akun</label>
                                <select name="role" class="w-full text-xs border-gray-300 rounded focus:ring-teal-500 focus:border-teal-500">
                                    <option value="mahasiswa">Mahasiswa</option>
                                    <option value="laboran">Laboran Staff</option>
                                    @if(Auth::user()->role === 'ultraadmin')
                                        <option value="superadmin">Superadmin</option>
                                        <option value="ultraadmin">Ultra Admin</option>
                                    @endif
                                </select>
                            </div>
                            @if(Auth::user()->role === 'ultraadmin')
                                <div>
                                    <label class="block text-gray-700 mb-1 font-semibold">Jurusan</label>
                                    <select name="jurusan" class="w-full text-xs border-gray-300 rounded focus:ring-teal-500 focus:border-teal-500">
                                        <option value="keperawatan">Keperawatan</option>
                                        <option value="kebidanan">Kebidanan</option>
                                        <option value="kesehatan_gigi">Kesehatan Gigi</option>
                                        <option value="ortotik_prostetik">Ortotik Prostetik</option>
                                    </select>
                                </div>
                            @else
                                <input type="hidden" name="jurusan" value="{{ Auth::user()->jurusan }}" />
                            @endif
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1 font-semibold">Email</label>
                            <input type="email" name="email" required placeholder="budi@example.com" class="w-full text-xs border-gray-300 rounded focus:ring-teal-500 focus:border-teal-500" />
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1 font-semibold">Password</label>
                            <input type="password" name="password" required placeholder="Minimal 6 karakter" class="w-full text-xs border-gray-300 rounded focus:ring-teal-500 focus:border-teal-500" />
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t border-gray-105">
                            <button type="button" @click="isCreateUserOpen = false" class="px-4 py-2 border border-gray-300 rounded text-gray-700 bg-white hover:bg-gray-50 font-medium">Batal</button>
                            <button type="submit" class="px-4 py-2 rounded text-white bg-teal-600 hover:bg-teal-700 font-semibold shadow-sm">Simpan Akun</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- EDIT USER MODAL -->
        <div x-show="isEditUserOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-transition:enter="transition ease-out duration-250" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="isEditUserOpen = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg px-6 pt-5 pb-6 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-150">
                    <div class="mb-4 flex justify-between items-center border-b border-gray-100 pb-3">
                        <h4 class="text-lg font-bold text-gray-900 font-sans">Ubah Akun Pengguna</h4>
                        <button @click="isEditUserOpen = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <form method="POST" :action="'/superadmin/users/' + editUser.id" class="space-y-4 text-xs">
                        @csrf
                        @method('PUT')
                        <div>
                            <label class="block text-gray-700 mb-1 font-semibold">Nama Lengkap</label>
                            <input type="text" name="name" x-model="editUser.name" required class="w-full text-xs border-gray-300 rounded focus:ring-teal-500 focus:border-teal-500" />
                        </div>
                        <div class="grid grid-cols-1 {{ Auth::user()->role === 'ultraadmin' ? 'sm:grid-cols-3' : 'sm:grid-cols-2' }} gap-4">
                            <div>
                                <label class="block text-gray-700 mb-1 font-semibold">NIM / NIDN</label>
                                <input type="text" name="nomor_induk" x-model="editUser.nomor_induk" required class="w-full text-xs border-gray-300 rounded focus:ring-teal-500 focus:border-teal-500" />
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-1 font-semibold">Role Akun</label>
                                <select name="role" x-model="editUser.role" class="w-full text-xs border-gray-300 rounded focus:ring-teal-500 focus:border-teal-500">
                                    <option value="mahasiswa">Mahasiswa</option>
                                    <option value="laboran">Laboran Staff</option>
                                    @if(Auth::user()->role === 'ultraadmin')
                                        <option value="superadmin">Superadmin</option>
                                        <option value="ultraadmin">Ultra Admin</option>
                                    @endif
                                </select>
                            </div>
                            @if(Auth::user()->role === 'ultraadmin')
                                <div>
                                    <label class="block text-gray-700 mb-1 font-semibold">Jurusan</label>
                                    <select name="jurusan" x-model="editUser.jurusan" class="w-full text-xs border-gray-300 rounded focus:ring-teal-500 focus:border-teal-500">
                                        <option value="keperawatan">Keperawatan</option>
                                        <option value="kebidanan">Kebidanan</option>
                                        <option value="kesehatan_gigi">Kesehatan Gigi</option>
                                        <option value="ortotik_prostetik">Ortotik Prostetik</option>
                                    </select>
                                </div>
                            @else
                                <input type="hidden" name="jurusan" x-model="editUser.jurusan" />
                            @endif
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1 font-semibold">Email</label>
                            <input type="email" name="email" x-model="editUser.email" required class="w-full text-xs border-gray-300 rounded focus:ring-teal-500 focus:border-teal-500" />
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1 font-semibold">Ganti Password (Kosongkan jika tidak diubah)</label>
                            <input type="password" name="password" placeholder="Masukkan password baru" class="w-full text-xs border-gray-300 rounded focus:ring-teal-500 focus:border-teal-500" />
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t border-gray-105">
                            <button type="button" @click="isEditUserOpen = false" class="px-4 py-2 border border-gray-300 rounded text-gray-700 bg-white hover:bg-gray-50 font-medium">Batal</button>
                            <button type="submit" class="px-4 py-2 rounded text-white bg-teal-600 hover:bg-teal-700 font-semibold shadow-sm">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- IMPORT USERS MODAL -->
        <div x-show="isImportUsersOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-transition:enter="transition ease-out duration-250" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="isImportUsersOpen = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg px-6 pt-5 pb-6 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-150">
                    <div class="mb-4 flex justify-between items-center border-b border-gray-100 pb-3">
                        <h4 class="text-lg font-bold text-gray-900 font-sans">Impor Akun dari Google Sheets</h4>
                        <button @click="isImportUsersOpen = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('superadmin.users.import') }}" class="space-y-4 text-xs">
                        @csrf
                        <div>
                            <label class="block text-gray-700 mb-1 font-semibold">Tautan / Link Google Sheets</label>
                            <input type="url" name="sheet_url" required placeholder="https://docs.google.com/spreadsheets/d/..." class="w-full text-xs border-gray-300 rounded focus:ring-teal-500 focus:border-teal-500" />
                        </div>
                        @if(Auth::user()->role === 'ultraadmin')
                            <div>
                                <label class="block text-gray-700 mb-1 font-semibold">Impor Ke Jurusan</label>
                                <select name="jurusan" class="w-full text-xs border-gray-300 rounded focus:ring-teal-500 focus:border-teal-500">
                                    <option value="keperawatan">Keperawatan</option>
                                    <option value="kebidanan">Kebidanan</option>
                                    <option value="kesehatan_gigi">Kesehatan Gigi</option>
                                    <option value="ortotik_prostetik">Ortotik Prostetik</option>
                                </select>
                                <p class="text-[10px] text-gray-400 mt-1">Pastikan spreadsheet diatur public share ("Siapa saja dengan link dapat melihat") dan memiliki urutan kolom berikut: **Nama Mahasiswa, NIM, Email, Password**.</p>
                            </div>
                        @else
                            <input type="hidden" name="jurusan" value="{{ Auth::user()->jurusan }}" />
                            <div>
                                <p class="text-[10px] text-gray-400 mt-1">Mengimpor mahasiswa ke jurusan **{{ ucwords(str_replace('_', ' ', Auth::user()->jurusan)) }}**. Pastikan spreadsheet diatur public share ("Siapa saja dengan link dapat melihat") dan memiliki urutan kolom berikut: **Nama Mahasiswa, NIM, Email, Password**.</p>
                            </div>
                        @endif

                        <div class="flex justify-end gap-3 pt-4 border-t border-gray-105">
                            <button type="button" @click="isImportUsersOpen = false" class="px-4 py-2 border border-gray-300 rounded text-gray-700 bg-white hover:bg-gray-50 font-medium">Batal</button>
                            <button type="submit" class="px-4 py-2 rounded text-white bg-teal-600 hover:bg-teal-700 font-semibold shadow-sm">Mulai Impor Akun</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
