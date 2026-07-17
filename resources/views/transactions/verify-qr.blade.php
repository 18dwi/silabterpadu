<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verifikasi QR - Si-Lab {{ ucwords(str_replace('_', ' ', $transaction->jurusan)) }}</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Outfit', sans-serif;
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4 sm:p-6">

    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden transform transition-all duration-300 hover:shadow-2xl">
        <!-- Banner Header -->
        <div class="bg-teal-600 px-6 py-8 text-center text-white relative">
            <div class="absolute inset-0 bg-gradient-to-r from-teal-600 to-teal-700 opacity-90"></div>
            
            <div class="relative z-10 flex flex-col items-center">
                <img src="/images/logo_poltekkes.png" class="w-16 h-16 object-contain mb-3 bg-white p-1 rounded-full shadow-sm" alt="Logo Poltekkes">
                <h1 class="text-xl font-bold tracking-wide">Si-Lab {{ ucwords(str_replace('_', ' ', $transaction->jurusan)) }}</h1>
                <p class="text-xs text-teal-100 uppercase tracking-widest mt-1">Poltekkes Kemenkes Jakarta I</p>
            </div>
        </div>

        <!-- Verification Status Card -->
        <div class="p-6 space-y-6">
            <div class="text-center space-y-2">
                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Tanda Tangan Digital Sah
                </div>
                <h2 class="text-lg font-bold text-slate-800">DOKUMEN VALID & DIVERIFIKASI</h2>
                <p class="text-xs text-slate-400">Transaksi pengajuan ini sah secara hukum dan tercatat di database Si-Lab {{ ucwords(str_replace('_', ' ', $transaction->jurusan)) }}.</p>
            </div>

            <!-- Transaction Info Grid -->
            <div class="bg-slate-50 rounded-xl p-4 border border-slate-100 text-xs space-y-2.5">
                <div class="flex justify-between items-start border-b border-slate-100 pb-2">
                    <span class="text-slate-500">ID Transaksi:</span>
                    <span class="font-bold text-slate-800">TX-{{ str_pad($transaction->id, 5, '0', STR_PAD_LEFT) }}</span>
                </div>
                <div class="flex justify-between items-start border-b border-slate-100 pb-2">
                    <span class="text-slate-500">Peminjam:</span>
                    @if($transaction->is_insidentil)
                        <span class="font-semibold text-slate-800 text-right">{{ $transaction->peminjam_insidentil }} <span class="text-[10px] text-teal-600 font-bold block">(Insidentil)</span></span>
                    @else
                        <span class="font-semibold text-slate-800 text-right">{{ $transaction->user->name }} (NIM: {{ $transaction->user->nomor_induk }})</span>
                    @endif
                </div>
                <div class="flex justify-between items-start border-b border-slate-100 pb-2">
                    <span class="text-slate-500">Penanggung Jawab:</span>
                    <span class="font-semibold text-slate-800 text-right">{{ $transaction->penanggung_jawab }}</span>
                </div>
                <div class="flex justify-between items-start border-b border-slate-100 pb-2">
                    <span class="text-slate-500">Kegiatan:</span>
                    <span class="font-semibold text-slate-800 text-right">{{ $transaction->kegiatan }}</span>
                </div>
                <div class="flex justify-between items-start border-b border-slate-100 pb-2">
                    <span class="text-slate-500">Tipe Pengajuan:</span>
                    <span class="font-bold text-teal-700 capitalize">{{ str_replace('_', ' ', $transaction->tipe) }}</span>
                </div>
                <div class="flex justify-between items-start border-b border-slate-100 pb-2">
                    <span class="text-slate-500">Tanggal Pengajuan:</span>
                    <span class="font-medium text-slate-800">{{ $transaction->tanggal_pengajuan->format('d M Y, H:i') }} WIB</span>
                </div>
                @if($transaction->tipe === 'peminjaman_alat')
                    <div class="flex justify-between items-start border-b border-slate-100 pb-2">
                        <span class="text-slate-500">Tanggal Pinjam:</span>
                        <span class="font-medium text-slate-800">{{ $transaction->tanggal_pinjam ? $transaction->tanggal_pinjam->format('d M Y, H:i') : '-' }} WIB</span>
                    </div>
                    <div class="flex justify-between items-start border-b border-slate-100 pb-2">
                        <span class="text-slate-500">Rencana Pengembalian:</span>
                        <span class="font-medium text-slate-800">{{ $transaction->tanggal_kembali_rencana ? $transaction->tanggal_kembali_rencana->format('d M Y, H:i') : '-' }} WIB</span>
                    </div>
                    @if($transaction->tanggal_kembali_aktual)
                        <div class="flex justify-between items-start border-b border-slate-100 pb-2">
                            <span class="text-slate-500">Pengembalian Aktual:</span>
                            <span class="font-bold text-green-700">{{ $transaction->tanggal_kembali_aktual->format('d M Y, H:i') }} WIB</span>
                        </div>
                    @endif
                @endif
                <div class="flex justify-between items-start">
                    <span class="text-slate-500">Diverifikasi Oleh:</span>
                    <span class="font-bold text-teal-700 text-right">{{ $transaction->laboran ? $transaction->laboran->name : 'Staff Laboran' }} (NIP: {{ $transaction->laboran ? $transaction->laboran->nomor_induk : '-' }})</span>
                </div>
            </div>

            <!-- List of approved items -->
            <div class="space-y-3">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Barang yang Disetujui</h4>
                <div class="space-y-2">
                    @php 
                        $groupedDetails = $transaction->details->groupBy('package_id');
                    @endphp
                    @foreach($groupedDetails as $pkgId => $details)
                        @if($pkgId)
                            @php 
                                $firstDetail = $details->first();
                                $packageName = $firstDetail->package ? $firstDetail->package->nama_paket : 'Paket Terhapus';
                                $packageQty = $firstDetail->package_qty;
                            @endphp
                            <div class="p-3 bg-teal-50/30 rounded-xl border border-teal-100/50 space-y-2">
                                <div class="text-xs font-bold text-teal-800 flex items-center gap-1">🎁 {{ $packageName }} ({{ $packageQty }} set)</div>
                                <div class="divide-y divide-teal-100/40 border border-teal-100/30 rounded-lg overflow-hidden bg-white/60">
                                    @foreach($details as $detail)
                                        <div class="flex justify-between items-center p-2.5 text-xs">
                                            <div>
                                                <span class="font-medium text-slate-700">{{ $detail->item->nama_barang }}</span>
                                                <span class="block text-[10px] text-slate-400">Kode: {{ $detail->item->kode_barang }}</span>
                                            </div>
                                            <span class="font-bold text-slate-800 bg-teal-50/50 text-teal-700 px-2 py-0.5 rounded text-[10px]">
                                                {{ $detail->jumlah_diminta }} {{ $detail->item->satuan }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/60 space-y-2">
                                <div class="text-xs font-bold text-slate-500 flex items-center gap-1">📦 Barang Satuan</div>
                                <div class="divide-y divide-slate-100 border border-slate-200/30 rounded-lg overflow-hidden bg-white">
                                    @foreach($details as $detail)
                                        <div class="flex justify-between items-center p-2.5 text-xs">
                                            <div>
                                                <span class="font-medium text-slate-700">{{ $detail->item->nama_barang }}</span>
                                                <span class="block text-[10px] text-slate-400">Kode: {{ $detail->item->kode_barang }}</span>
                                            </div>
                                            <span class="font-bold text-slate-800 bg-slate-50 text-slate-650 px-2 py-0.5 rounded text-[10px]">
                                                {{ $detail->jumlah_diminta }} {{ $detail->item->satuan }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- Footer info -->
            <div class="text-center pt-2 border-t border-slate-100">
                <a href="/" class="text-xs text-teal-650 hover:underline font-semibold">Kembali ke Halaman Utama</a>
                <p class="text-[9px] text-slate-400 mt-2">&copy; {{ date('Y') }} Si-Lab {{ ucwords(str_replace('_', ' ', $transaction->jurusan)) }}. All rights reserved.</p>
            </div>
        </div>
    </div>

</body>
</html>
