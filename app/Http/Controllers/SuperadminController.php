<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SuperadminController extends Controller
{
    /**
     * Create a new user manually.
     */
    public function storeUser(Request $request)
    {
        $creator = Auth::user();
        
        $roleRules = $creator->role === 'ultraadmin' 
            ? 'required|in:mahasiswa,laboran,superadmin,ultraadmin'
            : 'required|in:mahasiswa,laboran';

        $request->validate([
            'name' => 'required|string|max:255',
            'nomor_induk' => 'required|string|max:255|unique:users,nomor_induk',
            'email' => 'required|string|email|max:255|unique:users,email',
            'role' => $roleRules,
            'password' => 'required|string|min:6',
            'jurusan' => 'required|in:keperawatan,kebidanan,kesehatan_gigi,ortotik_prostetik',
            'program_studi' => 'nullable|string|in:DIII Keperawatan,Sarjana Terapan Keperawatan,Profesi Ners Keperawatan',
        ]);

        $targetJurusan = $creator->role === 'ultraadmin' 
            ? $request->jurusan 
            : $creator->jurusan;

        User::create([
            'name' => $request->name,
            'nomor_induk' => $request->nomor_induk,
            'email' => $request->email,
            'role' => $request->role,
            'password' => Hash::make($request->password),
            'jurusan' => $targetJurusan,
            'program_studi' => $request->program_studi,
        ]);

        return redirect()->back()->with('success', 'Akun berhasil dibuat.');
    }

    /**
     * Update an existing user's details.
     */
    public function updateUser(Request $request, $id)
    {
        $creator = Auth::user();
        
        if ($creator->role === 'ultraadmin') {
            $user = User::findOrFail($id);
            $roleRules = 'required|in:mahasiswa,laboran,superadmin,ultraadmin';
        } else {
            $user = User::where('jurusan', $creator->jurusan)
                ->where('role', '!=', 'ultraadmin')
                ->where('role', '!=', 'superadmin')
                ->findOrFail($id);
            $roleRules = 'required|in:mahasiswa,laboran';
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'nomor_induk' => 'required|string|max:255|unique:users,nomor_induk,' . $user->id,
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => $roleRules,
            'password' => 'nullable|string|min:6',
            'jurusan' => 'required|in:keperawatan,kebidanan,kesehatan_gigi,ortotik_prostetik',
            'program_studi' => 'nullable|string|in:DIII Keperawatan,Sarjana Terapan Keperawatan,Profesi Ners Keperawatan',
        ]);

        $targetJurusan = $creator->role === 'ultraadmin' 
            ? $request->jurusan 
            : $creator->jurusan;

        $data = [
            'name' => $request->name,
            'nomor_induk' => $request->nomor_induk,
            'email' => $request->email,
            'role' => $request->role,
            'jurusan' => $targetJurusan,
            'program_studi' => $request->program_studi,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->back()->with('success', 'Akun berhasil diperbarui.');
    }

    /**
     * Delete a user account.
     */
    public function destroyUser($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            return redirect()->back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();

        return redirect()->back()->with('success', 'Akun berhasil dihapus.');
    }

    /**
     * Import student accounts in bulk using Google Sheets.
     */
    public function importUsersSheet(Request $request)
    {
        $request->validate([
            'sheet_url' => 'required|url',
            'jurusan' => 'required|in:keperawatan,kebidanan,kesehatan_gigi,ortotik_prostetik',
        ]);

        $url = $request->input('sheet_url');
        $targetJurusan = $request->input('jurusan');

        if (preg_match('/\/d\/([a-zA-Z0-9-_]+)/', $url, $matches)) {
            $spreadsheetId = $matches[1];
            $csvUrl = "https://docs.google.com/spreadsheets/d/{$spreadsheetId}/export?format=csv";
        } else {
            return redirect()->back()->with('error', 'URL Google Sheet tidak valid. Pastikan format URL benar.');
        }

        try {
            $options = [
                'http' => [
                    'header' => "User-Agent: PHP\r\n"
                ]
            ];
            $context = stream_context_create($options);
            $csvData = file_get_contents($csvUrl, false, $context);
            if ($csvData === false) {
                throw new \Exception("Gagal mengambil data. Pastikan Sheet diatur Publik (Siapa saja yang memiliki link dapat melihat).");
            }

            $rows = array_map('str_getcsv', explode("\n", $csvData));
            $header = array_shift($rows);

            $count = 0;
            DB::transaction(function () use ($rows, $targetJurusan, &$count) {
                foreach ($rows as $row) {
                    if ($targetJurusan === 'keperawatan') {
                        if (count($row) < 5 || empty($row[0])) continue;
                        $nama = trim($row[0]);
                        $nim = trim($row[1]);
                        $prodi = trim($row[2]);
                        $email = trim($row[3]);
                        $password = trim($row[4]);
                    } else {
                        if (count($row) < 4 || empty($row[0])) continue;
                        $nama = trim($row[0]);
                        $nim = trim($row[1]);
                        $email = trim($row[2]);
                        $password = trim($row[3]);
                        $prodi = null;
                    }

                    User::updateOrCreate(
                        ['nomor_induk' => $nim],
                        [
                            'name' => $nama,
                            'email' => $email,
                            'role' => 'mahasiswa',
                            'password' => Hash::make($password),
                            'jurusan' => $targetJurusan,
                            'program_studi' => $prodi,
                        ]
                    );
                    $count++;
                }
            });

            return redirect()->back()->with('success', "Berhasil membuat {$count} akun mahasiswa dari Google Sheet.");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengimpor akun: ' . $e->getMessage());
        }
    }

    /**
     * Delete transaction history record without altering inventory stock.
     */
    public function destroyTransaction($id)
    {
        $transaction = Transaction::findOrFail($id);
        
        // Delete details cascade
        $transaction->details()->delete();
        $transaction->delete();

        return redirect()->back()->with('success', 'Riwayat transaksi berhasil dihapus tanpa memengaruhi stok barang.');
    }

    public function destroyRoomBooking($id)
    {
        $booking = \App\Models\RoomBooking::findOrFail($id);
        $booking->items()->delete();
        $booking->delete();

        return redirect()->back()->with('success', 'Riwayat peminjaman ruangan berhasil dihapus.');
    }

    /**
     * Authenticate as the selected student.
     */
    public function impersonate($id)
    {
        $creator = Auth::user();
        if (!in_array($creator->role, ['superadmin', 'ultraadmin'])) {
            abort(403, 'Unauthorized.');
        }

        $student = User::where('role', 'mahasiswa')->findOrFail($id);

        // Store original user ID in the session
        session(['original_user_id' => $creator->id]);

        // Login as the student
        Auth::login($student);

        return redirect()->route('dashboard')->with('success', "Anda sekarang masuk sebagai mahasiswa: {$student->name}");
    }

    /**
     * Revert authentication back to the original superadmin/ultraadmin user.
     */
    public function leaveImpersonate()
    {
        if (!session()->has('original_user_id')) {
            return redirect()->route('dashboard');
        }

        $originalUserId = session('original_user_id');
        $originalUser = User::findOrFail($originalUserId);

        // Clear the session
        session()->forget('original_user_id');

        // Login back as the original user
        Auth::login($originalUser);

        return redirect()->route('dashboard')->with('success', 'Kembali ke panel admin.');
    }
}
