@props(['transaction', 'items', 'packages'])

<!-- Alpine Component for Edit Modal -->
<div x-data="{
        isOpen: false,
        tx: null,
        items: [],
        packages: [],
        selectedItems: [],
        selectedPackages: [],
        formTglPinjam: '',
        
        openModal(transactionData) {
            this.tx = transactionData;
            this.formTglPinjam = this.tx?.tanggal_pinjam ? this.tx.tanggal_pinjam.slice(0,16) : '';
            this.selectedItems = [...this.tx.details.filter(d => d.item_id).map(d => ({
                id: d.id,
                item_id: d.item_id,
                item: d.item,
                jumlah_diminta: d.jumlah_diminta
            }))];
            this.selectedPackages = [...this.tx.details.filter(d => d.package_id).map(d => ({
                id: d.id,
                package_id: d.package_id,
                package: d.package,
                jumlah_diminta: d.jumlah_diminta
            }))];
            this.isOpen = true;
        },
        
        closeModal() {
            this.isOpen = false;
            this.tx = null;
        }
    }" 
    @open-edit-tx-modal.window="openModal($event.detail)"
    x-show="isOpen" 
    class="fixed inset-0 z-[100] overflow-y-auto" 
    style="display: none;"
>
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
        <div x-show="isOpen" x-transition.opacity class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="closeModal"></div>

        <div x-show="isOpen" x-transition class="relative inline-block w-full max-w-lg p-6 text-left align-middle transition-all transform bg-white shadow-2xl rounded-2xl">
            <div class="absolute top-0 right-0 pt-5 pr-5">
                <button @click="closeModal" type="button" class="text-gray-400 bg-white rounded-md hover:text-gray-500 focus:outline-none">
                    <span class="sr-only">Close</span>
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <div class="sm:flex sm:items-start mb-6">
                <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 mx-auto bg-blue-100 rounded-full sm:mx-0 sm:h-10 sm:w-10">
                    <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                </div>
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                    <h3 class="text-xl font-bold leading-6 text-gray-900" id="modal-title">Edit Transaksi <span x-text="tx?.id ? 'TRX-'+tx.id : ''"></span></h3>
                    <p class="text-sm text-gray-500 mt-1">Ubah tanggal, waktu, atau jumlah barang/paket yang dipinjam.</p>
                </div>
            </div>

            <form :action="tx ? '/transactions/' + tx.id : '#'" method="POST" class="space-y-6">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Kegiatan</label>
                        <input type="text" name="kegiatan" :value="tx?.kegiatan" required class="w-full text-sm border-gray-300 rounded-md focus:ring-teal-500 focus:border-teal-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Penanggung Jawab</label>
                        <input type="text" name="penanggung_jawab" :value="tx?.penanggung_jawab" required class="w-full text-sm border-gray-300 rounded-md focus:ring-teal-500 focus:border-teal-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal & Waktu Mulai</label>
                        <input type="datetime-local" name="tanggal_pinjam" x-model="formTglPinjam" required class="w-full text-sm border-gray-300 rounded-md focus:ring-teal-500 focus:border-teal-500">
                    </div>
                    <div x-show="tx?.tipe === 'peminjaman_alat'">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal & Waktu Selesai</label>
                        <input type="datetime-local" name="tanggal_kembali_rencana" :min="formTglPinjam" :value="tx?.tanggal_kembali_rencana ? tx.tanggal_kembali_rencana.slice(0,16) : ''" :required="tx?.tipe === 'peminjaman_alat'" class="w-full text-sm border-gray-300 rounded-md focus:ring-teal-500 focus:border-teal-500">
                    </div>
                </div>

                <div class="border-t border-gray-200 pt-6">
                    <h4 class="text-lg font-bold text-gray-900 mb-4">Daftar Barang & Paket</h4>
                    
                    <h5 x-show="selectedItems.length > 0" class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 mt-4">Barang/Bahan Satuan</h5>
                    <!-- Loop selectedItems -->
                    <template x-for="(item, index) in selectedItems" :key="'item-'+index">
                        <div class="flex items-center gap-4 mb-3 bg-gray-50 p-3 rounded-lg border border-gray-100">
                            <input type="hidden" :name="'items['+index+'][item_id]'" :value="item.item_id">
                            <div class="flex-1">
                                <select x-model="item.item_id" class="w-full text-sm border-gray-300 rounded focus:ring-teal-500">
                                    <option value="" disabled>-- Pilih Barang --</option>
                                    @foreach($items as $dbItem)
                                        <option value="{{ $dbItem->id }}" x-show="tx?.tipe === 'peminjaman_alat' ? '{{ $dbItem->kategori }}' === 'alat' : '{{ $dbItem->kategori }}' === 'bahan'">
                                            {{ $dbItem->nama_barang }} (Stok: {{ $dbItem->stok_tersedia }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="w-32">
                                <label class="text-xs text-gray-500 block">Jumlah</label>
                                <input type="number" :name="'items['+index+'][jumlah_diminta]'" x-model="item.jumlah_diminta" min="1" required class="w-full text-sm border-gray-300 rounded focus:ring-teal-500">
                            </div>
                            <button type="button" @click="selectedItems.splice(index, 1)" class="mt-4 text-red-500 hover:text-red-700 font-bold p-2">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </div>
                    </template>
                    
                    <h5 x-show="selectedPackages.length > 0" class="text-xs font-bold text-indigo-500 uppercase tracking-wider mb-2 mt-4">Paket Praktikum</h5>
                    <!-- Loop selectedPackages -->
                    <template x-for="(pkg, index) in selectedPackages" :key="'pkg-'+index">
                        <div class="flex items-center gap-4 mb-3 bg-indigo-50 p-3 rounded-lg border border-indigo-100">
                            <input type="hidden" :name="'items['+(selectedItems.length + index)+'][package_id]'" :value="pkg.package_id">
                            <div class="flex-1">
                                <select x-model="pkg.package_id" class="w-full text-sm border-indigo-300 rounded focus:ring-indigo-500">
                                    <option value="" disabled>-- Pilih Paket --</option>
                                    @foreach($packages as $dbPkg)
                                        <option value="{{ $dbPkg->id }}">{{ $dbPkg->nama_paket }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="w-32">
                                <label class="text-xs text-indigo-500 block">Jumlah Paket</label>
                                <input type="number" :name="'items['+(selectedItems.length + index)+'][package_qty]'" x-model="pkg.jumlah_diminta" min="1" required class="w-full text-sm border-indigo-300 rounded focus:ring-indigo-500">
                            </div>
                            <button type="button" @click="selectedPackages.splice(index, 1)" class="mt-4 text-red-500 hover:text-red-700 font-bold p-2">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </div>
                    </template>

                    <!-- Add Buttons inside edit modal -->
                    <div class="flex flex-wrap gap-2.5 mt-4 pt-3 border-t border-dashed border-gray-200">
                        <button type="button" @click="selectedItems.push({ id: null, item_id: '', jumlah_diminta: 1 })" class="inline-flex items-center px-3 py-1.5 bg-teal-50 text-teal-700 hover:bg-teal-100 rounded border border-teal-200 text-xs font-bold transition duration-150">
                            + Tambah Alat / Bahan Baru
                        </button>
                        <button type="button" @click="selectedPackages.push({ id: null, package_id: '', jumlah_diminta: 1 })" class="inline-flex items-center px-3 py-1.5 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 rounded border border-indigo-200 text-xs font-bold transition duration-150">
                            + Tambah Paket Baru
                        </button>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-5 border-t border-gray-200 mt-6">
                    <button type="button" @click="closeModal" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 focus:outline-none">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg shadow-sm hover:bg-blue-700 focus:outline-none">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
