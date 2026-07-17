<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Transaction;
use App\Models\BebasLabCertificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BebasLabController extends Controller
{
    /**
     * Store a newly issued clearance certificate.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'nomor_surat' => 'required|string|unique:bebas_lab_certificates,nomor_surat',
        ]);

        $student = User::findOrFail($request->user_id);

        if ($student->role !== 'mahasiswa') {
            return redirect()->back()->with('error', 'Hanya mahasiswa yang dapat diberikan surat bebas laboratorium.');
        }

        // Check if student already has a certificate
        $existing = BebasLabCertificate::where('user_id', $student->id)->first();
        if ($existing) {
            return redirect()->back()->with('error', 'Surat bebas laboratorium untuk mahasiswa ini sudah pernah diterbitkan.');
        }

        // Validate active or pending transactions
        $activeLoansCount = Transaction::where('user_id', $student->id)
            ->whereNotIn('status', ['selesai', 'ditolak'])
            ->count();

        if ($activeLoansCount > 0) {
            return redirect()->back()->with('error', 'Gagal menerbitkan surat. Mahasiswa ini masih memiliki transaksi peminjaman alat yang belum selesai atau masih berstatus pending di sistem.');
        }

        BebasLabCertificate::create([
            'user_id' => $student->id,
            'laboran_id' => Auth::id(),
            'nomor_surat' => $request->nomor_surat,
            'tanggal_terbit' => now(),
            'jurusan' => Auth::user()->jurusan ?? 'keperawatan',
        ]);

        return redirect()->back()->with('success', "Surat bebas laboratorium berhasil diterbitkan untuk mahasiswa {$student->name}.");
    }

    /**
     * Revoke/delete a clearance certificate.
     */
    public function destroy($id)
    {
        $cert = BebasLabCertificate::findOrFail($id);
        $studentName = $cert->user ? $cert->user->name : 'Mahasiswa';
        $cert->delete();

        return redirect()->back()->with('success', "Surat bebas laboratorium untuk {$studentName} berhasil dibatalkan.");
    }

    /**
     * Download or stream the clearance certificate as PDF.
     */
    public function downloadPdf(Request $request, $id)
    {
        $certificate = BebasLabCertificate::with(['user', 'laboran'])->findOrFail($id);

        // Security check: Only the student, laboran, or superadmin can view this certificate
        $currentUser = Auth::user();
        if ($currentUser->role === 'mahasiswa' && $currentUser->id !== $certificate->user_id) {
            abort(403, 'Anda tidak diizinkan melihat surat bebas laboratorium mahasiswa lain.');
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.bebas-lab', compact('certificate'));
        
        $tanggalPengesahan = $certificate->tanggal_terbit->translatedFormat('d F Y');
        $filename = $certificate->user->name . '_Surat Bebas Laboratorium_' . $tanggalPengesahan . '.pdf';
        
        if ($request->query('action') === 'preview') {
            return $pdf->stream($filename);
        }
        
        return $pdf->download($filename);
    }

    /**
     * Public route to verify transaction/certificate digital signature validity.
     */
    public function verifyQr($id)
    {
        $certificate = BebasLabCertificate::with(['user', 'laboran'])->findOrFail($id);
        return view('bebas-lab.verify-qr', compact('certificate'));
    }
}
