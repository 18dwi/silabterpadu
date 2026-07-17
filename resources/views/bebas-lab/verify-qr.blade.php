<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verifikasi Bebas Lab - Si-Lab {{ ucwords(str_replace('_', ' ', $certificate->jurusan)) }}</title>
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
                <h1 class="text-xl font-bold tracking-wide">Si-Lab {{ ucwords(str_replace('_', ' ', $certificate->jurusan)) }}</h1>
                <p class="text-xs text-teal-100 uppercase tracking-widest mt-1">Poltekkes Kemenkes Jakarta I</p>
            </div>
        </div>

        <!-- Verification Status Card -->
        <div class="p-6 space-y-6">
            <div class="text-center space-y-2">
                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Sertifikat Digital Valid
                </div>
                <h2 class="text-lg font-bold text-slate-800">BEBAS LABORATORIUM SAH</h2>
                <p class="text-xs text-slate-400">Surat Keterangan Bebas Laboratorium ini sah dikeluarkan oleh pihak Pengelola Laboratorium {{ ucwords(str_replace('_', ' ', $certificate->jurusan)) }}.</p>
            </div>

            <!-- Certificate Info Grid -->
            <div class="bg-slate-50 rounded-xl p-4 border border-slate-100 text-xs space-y-2.5">
                <div class="flex justify-between items-start border-b border-slate-100 pb-2">
                    <span class="text-slate-500">Nomor Surat:</span>
                    <span class="font-bold text-slate-850">{{ $certificate->nomor_surat }}</span>
                </div>
                <div class="flex justify-between items-start border-b border-slate-100 pb-2">
                    <span class="text-slate-500">Mahasiswa:</span>
                    <span class="font-bold text-slate-800 text-right">{{ $certificate->user->name }}</span>
                </div>
                <div class="flex justify-between items-start border-b border-slate-100 pb-2">
                    <span class="text-slate-500">NIM / Nomor Induk:</span>
                    <span class="font-bold text-slate-800">{{ $certificate->user->nomor_induk }}</span>
                </div>
                <div class="flex justify-between items-start border-b border-slate-100 pb-2">
                    <span class="text-slate-500">Status Kelayakan:</span>
                    <span class="font-bold text-emerald-600">BEBAS & LAYAK SIDANG / WISUDA</span>
                </div>
                <div class="flex justify-between items-start border-b border-slate-100 pb-2">
                    <span class="text-slate-500">Tanggal Terbit:</span>
                    <span class="font-medium text-slate-800">{{ $certificate->tanggal_terbit->translatedFormat('d F Y') }}</span>
                </div>
                <div class="flex justify-between items-start">
                    <span class="text-slate-500">Diterbitkan Oleh:</span>
                    <span class="font-bold text-teal-700 text-right">{{ $certificate->laboran ? $certificate->laboran->name : 'Staff Laboran' }} (NIM/NIP: {{ $certificate->laboran ? $certificate->laboran->nomor_induk : '-' }})</span>
                </div>
            </div>

            <!-- Footer info -->
            <div class="text-center pt-2 border-t border-slate-100">
                <a href="/" class="text-xs text-teal-650 hover:underline font-semibold font-sans">Kembali ke Halaman Utama</a>
                <p class="text-[9px] text-slate-400 mt-2">&copy; {{ date('Y') }} Si-Lab {{ ucwords(str_replace('_', ' ', $certificate->jurusan)) }}. All rights reserved.</p>
            </div>
        </div>
    </div>

</body>
</html>
