<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Bebas Laboratorium - Si-Lab {{ ucwords(str_replace('_', ' ', $certificate->jurusan)) }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 12px;
            color: #333333;
            line-height: 1.6;
            margin: 0;
            padding: 10px 20px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #0d9488;
            padding-bottom: 12px;
            margin-bottom: 25px;
        }
        .header-logo {
            width: 80px;
            text-align: left;
            vertical-align: middle;
        }
        .header-text {
            text-align: left;
            vertical-align: middle;
            padding-left: 15px;
        }
        .header-title {
            font-size: 16px;
            font-weight: bold;
            color: #0f766e;
            margin: 0;
        }
        .header-subtitle {
            font-size: 11px;
            color: #4b5563;
            margin: 2px 0 0 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .doc-title {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            color: #1f2937;
            text-transform: uppercase;
            margin-top: 15px;
            margin-bottom: 5px;
            letter-spacing: 1px;
            text-decoration: underline;
        }
        .doc-number {
            text-align: center;
            font-size: 11px;
            color: #4b5563;
            margin-bottom: 30px;
        }
        .intro-text {
            margin-bottom: 20px;
            text-align: justify;
            text-indent: 30px;
        }
        .student-table {
            width: 85%;
            margin: 0 auto 30px auto;
            border-collapse: collapse;
        }
        .student-table td {
            padding: 6px 10px;
            vertical-align: top;
        }
        .label-cell {
            width: 35%;
            font-weight: bold;
            color: #4b5563;
        }
        .value-cell {
            width: 65%;
            color: #111827;
        }
        .declaration-text {
            margin-bottom: 40px;
            text-align: justify;
            text-indent: 30px;
        }
        .sign-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 40px;
        }
        .sign-cell {
            width: 50%;
            text-align: center;
            vertical-align: top;
        }
        .stamp-box {
            border: 2px dashed #d1d5db;
            border-radius: 50%;
            width: 100px;
            height: 100px;
            margin: 30px auto 0 auto;
            display: block;
            text-align: center;
            vertical-align: middle;
        }
        .stamp-text {
            font-size: 8px;
            color: #9ca3af;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 42px;
            letter-spacing: 1px;
        }
        .sign-title {
            font-weight: bold;
            color: #4b5563;
            margin-bottom: 5px;
        }
        .qr-container {
            margin: 10px 0;
            text-align: center;
        }
    </style>
</head>
<body>

    @php
        // Convert Poltekkes logo to Base64 to ensure DOMPDF loads it offline
        $logoPath = public_path('images/logo_poltekkes.png');
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoBase64 = base64_encode(file_get_contents($logoPath));
        }

        // Convert QR code to Base64 to ensure DOMPDF loads it offline
        $qrPayload = route('bebas-lab.verify-qr', $certificate->id);
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=' . urlencode($qrPayload);
        $qrBase64 = '';
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $qrUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            $qrImg = curl_exec($ch);
            curl_close($ch);
            if ($qrImg) {
                $qrBase64 = base64_encode($qrImg);
            }
        } catch (\Exception $e) {}
    @endphp

    <!-- HEADER TABLE -->
    <table class="header-table">
        <tr>
            <td class="header-logo">
                @if($logoBase64)
                    <img src="data:image/png;base64,{{ $logoBase64 }}" style="height: 60px;" alt="Logo Poltekkes" />
                @endif
            </td>
            <td class="header-text">
                <div class="header-title">Si-Lab {{ ucwords(str_replace('_', ' ', $certificate->jurusan)) }}</div>
                <div class="header-subtitle">Poltekkes Kemenkes Jakarta I</div>
                <div style="font-size: 9px; color: #6b7280; margin-top: 2px;">Jl. Wijaya Kusuma No. 47 Cilandak, Jakarta Selatan</div>
            </td>
        </tr>
    </table>

    <!-- CERTIFICATE TITLE -->
    <div class="doc-title">Surat Keterangan Bebas Laboratorium</div>
    <div class="doc-number">Nomor: {{ $certificate->nomor_surat }}</div>

    <!-- INTRO -->
    <div class="intro-text">
        Yang bertanda tangan di bawah ini, Kepala/Petugas Pengelola Laboratorium {{ ucwords(str_replace('_', ' ', $certificate->jurusan)) }} Poltekkes Kemenkes Jakarta I menerangkan bahwa mahasiswa tersebut di bawah ini:
    </div>

    <!-- STUDENT DATA TABLE -->
    <table class="student-table">
        <tr>
            <td class="label-cell">Nama Lengkap</td>
            <td>:</td>
            <td class="value-cell" style="font-weight: bold; font-size: 13px;">{{ $certificate->user->name }}</td>
        </tr>
        <tr>
            <td class="label-cell">NIM / Nomor Induk</td>
            <td>:</td>
            <td class="value-cell" style="font-weight: bold;">{{ $certificate->user->nomor_induk }}</td>
        </tr>
        <tr>
            <td class="label-cell">Program Studi</td>
            <td>:</td>
            <td class="value-cell">D3 / Sarjana Terapan {{ ucwords(str_replace('_', ' ', $certificate->jurusan)) }}</td>
        </tr>
        <tr>
            <td class="label-cell">Status Kelulusan</td>
            <td>:</td>
            <td class="value-cell"><span style="color: #0d9488; font-weight: bold;">Bebas Laboratorium (Syarat Sidang)</span></td>
        </tr>
    </table>

    <!-- DECLARATION -->
    <div class="declaration-text">
        Dinyatakan telah menyelesaikan seluruh tanggung jawab dan kewajiban administrasi peminjaman alat serta bahan di Laboratorium {{ ucwords(str_replace('_', ' ', $certificate->jurusan)) }}. Mahasiswa yang bersangkutan dinyatakan **BEBAS dari pinjaman barang laboratorium** dan surat keterangan ini diterbitkan secara resmi sebagai syarat administrasi pelaksanaan Sidang Akhir / Wisuda.
    </div>

    <!-- SIGNATURE SECTION -->
    <table class="sign-table">
        <tr>
            <td class="sign-cell">
                <div style="font-size: 11px; color: #374151; margin-top: 25px;">Jakarta, {{ $certificate->tanggal_terbit->translatedFormat('d F Y') }}</div>
            </td>
            <td class="sign-cell">
                <div class="sign-title">Mengetahui,</div>
                <div class="sign-title">Petugas Pengelola Laboratorium</div>
                
                <div class="qr-container">
                    @if($qrBase64)
                        <img src="data:image/png;base64,{{ $qrBase64 }}" style="width: 85px; height: 85px;" alt="Tanda Tangan QR Code" />
                    @else
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data={{ urlencode($qrPayload) }}" style="width: 85px; height: 85px;" alt="Tanda Tangan QR Code" />
                    @endif
                </div>
                <div style="font-weight: bold; color: #0f766e; font-size: 8px; margin-top: 2px;">TANDA TANGAN ELEKTRONIK</div>
                <div style="font-size: 7px; color: #6b7280; margin-bottom: 5px;">Tervalidasi secara Sistem</div>
                <div style="font-weight: bold; margin-top: 5px;">( {{ $certificate->laboran ? $certificate->laboran->name : 'Laboran Si-Lab' }} )</div>
                <div style="font-size: 9px; color: #4b5563;">NIP. {{ $certificate->laboran ? $certificate->laboran->nomor_induk : '-' }}</div>
            </td>
        </tr>
    </table>

</body>
</html>
