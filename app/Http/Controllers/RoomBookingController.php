<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\RoomBooking;
use App\Models\RoomBookingItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class RoomBookingController extends Controller
{
    /* ─────────────────────────────────────────────────
     * MAHASISWA
     * ──────────────────────────────────────────────── */

    /**
     * Halaman utama peminjaman ruangan mahasiswa.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Semua ruangan aktif di jurusan
        $rooms = Room::where('jurusan', $user->jurusan)
            ->where('status', 'tersedia')
            ->orderBy('nama_ruangan')
            ->get();

        // Filter opsional
        $filterTanggalMulai  = $request->input('filter_tanggal_mulai');
        $filterTanggalSelesai = $request->input('filter_tanggal_selesai');
        $filterWaktuMulai    = $request->input('filter_waktu_mulai');
        $filterWaktuSelesai  = $request->input('filter_waktu_selesai');

        // Ruangan tersedia: tidak ada konflik di rentang filter
        $availableRooms = $rooms->filter(function ($room) use ($filterTanggalMulai, $filterTanggalSelesai, $filterWaktuMulai, $filterWaktuSelesai) {
            if (!$filterTanggalMulai || !$filterTanggalSelesai) return true;
            $waktuMulai  = $filterWaktuMulai  ?: '00:00';
            $waktuSelesai = $filterWaktuSelesai ?: '23:59';
            return $room->isAvailableFor($filterTanggalMulai, $filterTanggalSelesai, $waktuMulai, $waktuSelesai);
        })->values();

        // Ruangan digunakan: sedang aktif di rentang filter
        $usedRoomItems = RoomBookingItem::with(['room', 'booking.user'])
            ->whereHas('booking', function ($q) use ($user) {
                $q->where('jurusan', $user->jurusan)
                  ->whereIn('status', ['pending', 'disetujui']);
            });

        if ($filterTanggalMulai && $filterTanggalSelesai) {
            $wm  = $filterWaktuMulai  ?: '00:00';
            $ws  = $filterWaktuSelesai ?: '23:59';
            $usedRoomItems = $usedRoomItems
                ->where('tanggal_mulai', '<=', $filterTanggalSelesai)
                ->where('tanggal_selesai', '>=', $filterTanggalMulai)
                ->where('waktu_mulai', '<', $ws)
                ->where('waktu_selesai', '>', $wm);
        }

        $usedRoomItems = $usedRoomItems->orderBy('tanggal_mulai')->get();

        // Riwayat booking milik mahasiswa ini
        $myBookings = RoomBooking::with(['items.room'])
            ->where('user_id', $user->id)
            ->orderBy('tanggal_pengajuan', 'desc')
            ->get();

        return view('room-bookings-mahasiswa', compact(
            'rooms',
            'availableRooms',
            'usedRoomItems',
            'myBookings',
            'filterTanggalMulai',
            'filterTanggalSelesai',
            'filterWaktuMulai',
            'filterWaktuSelesai'
        ));
    }

    /**
     * Simpan peminjaman ruangan baru dari mahasiswa atau laboran (insidentil).
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $isInsidentil = $request->input('is_insidentil') == '1';

        $rules = [
            'tujuan_penggunaan' => 'required|string|max:500',
            'rooms'             => 'required|array|min:1',
            'rooms.*.room_id'        => 'required|exists:rooms,id',
            'rooms.*.tanggal_mulai'  => 'required|date',
            'rooms.*.tanggal_selesai'=> 'required|date|after_or_equal:rooms.*.tanggal_mulai',
            'rooms.*.waktu_mulai'    => 'required|date_format:H:i',
            'rooms.*.waktu_selesai'  => 'required|date_format:H:i|after:rooms.*.waktu_mulai',
        ];

        if ($isInsidentil) {
            $rules['jumlah_mahasiswa'] = 'nullable|integer|min:1';
            $rules['peminjam_insidentil'] = 'required|string|max:255';
            $rules['institusi_insidentil'] = 'required|string|max:255';
        } else {
            $rules['jumlah_mahasiswa'] = 'required|integer|min:1';
        }

        $request->validate($rules);

        // Check availability for each room
        $conflicts = [];
        foreach ($request->rooms as $idx => $item) {
            $room = Room::find($item['room_id']);
            if (!$room) continue;

            if (!$room->isAvailableFor(
                $item['tanggal_mulai'],
                $item['tanggal_selesai'],
                $item['waktu_mulai'],
                $item['waktu_selesai']
            )) {
                $conflicts[] = $room->nama_ruangan . ' (' .
                    Carbon::parse($item['tanggal_mulai'])->format('d-m-Y') . ' ' .
                    $item['waktu_mulai'] . ' - ' .
                    Carbon::parse($item['tanggal_selesai'])->format('d-m-Y') . ' ' .
                    $item['waktu_selesai'] . ')';
            }
        }

        if (!empty($conflicts)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['rooms' => 'Ruangan berikut sudah dipesan pada waktu yang dipilih: ' . implode(', ', $conflicts)]);
        }

        DB::transaction(function () use ($request, $user, $isInsidentil) {
            $booking = RoomBooking::create([
                'user_id'              => $isInsidentil ? null : $user->id,
                'laboran_id'           => $isInsidentil ? $user->id : null,
                'tujuan_penggunaan'    => $request->tujuan_penggunaan,
                'jumlah_mahasiswa'     => $request->input('jumlah_mahasiswa', 1),
                'status'               => $isInsidentil ? 'disetujui' : 'pending',
                'jurusan'              => $user->jurusan,
                'tanggal_pengajuan'    => now(),
                'qr_token'             => Str::random(40),
                'is_insidentil'        => $isInsidentil,
                'peminjam_insidentil'  => $isInsidentil ? $request->peminjam_insidentil : null,
                'institusi_insidentil' => $isInsidentil ? $request->institusi_insidentil : null,
            ]);

            foreach ($request->rooms as $item) {
                RoomBookingItem::create([
                    'room_booking_id' => $booking->id,
                    'room_id'         => $item['room_id'],
                    'tanggal_mulai'   => $item['tanggal_mulai'],
                    'tanggal_selesai' => $item['tanggal_selesai'],
                    'waktu_mulai'     => $item['waktu_mulai'],
                    'waktu_selesai'   => $item['waktu_selesai'],
                ]);
            }
        });

        $msg = $isInsidentil 
            ? 'Peminjaman ruangan insidentil berhasil dicatat dan disetujui.' 
            : 'Permohonan peminjaman ruangan berhasil diajukan. Menunggu verifikasi laboran.';

        return redirect()->back()->with('success', $msg);
    }

    /**
     * Batalkan booking (hanya jika masih pending).
     */
    public function destroyBooking($id)
    {
        $user    = Auth::user();
        $booking = RoomBooking::where('user_id', $user->id)->findOrFail($id);

        if ($booking->status !== 'pending') {
            return redirect()->back()->withErrors(['error' => 'Hanya peminjaman berstatus pending yang dapat dibatalkan.']);
        }

        $booking->delete();

        return redirect()->route('room-bookings.index')
            ->with('success', 'Peminjaman ruangan berhasil dibatalkan.');
    }

    /**
     * Cek ketersediaan ruangan secara AJAX.
     */
    public function checkAvailability(Request $request)
    {
        $room = Room::find($request->room_id);
        if (!$room) {
            return response()->json(['available' => false, 'message' => 'Ruangan tidak ditemukan.']);
        }

        $excludeBookingId = $request->input('exclude_booking_id');

        $available = $room->isAvailableFor(
            $request->tanggal_mulai,
            $request->tanggal_selesai,
            $request->waktu_mulai,
            $request->waktu_selesai,
            $excludeBookingId
        );

        return response()->json([
            'available' => $available,
            'message'   => $available
                ? 'Ruangan tersedia pada waktu tersebut.'
                : 'Ruangan sudah dipesan pada waktu tersebut.',
        ]);
    }

    /* ─────────────────────────────────────────────────
     * LABORAN
     * ──────────────────────────────────────────────── */

    /**
     * Halaman manajemen peminjaman ruangan (laboran).
     */
    public function laboranIndex(Request $request)
    {
        $user = Auth::user();

        // Filter opsional
        $filterTanggalMulai  = $request->input('filter_tanggal_mulai');
        $filterTanggalSelesai = $request->input('filter_tanggal_selesai');
        $filterWaktuMulai    = $request->input('filter_waktu_mulai');
        $filterWaktuSelesai  = $request->input('filter_waktu_selesai');

        // Master Ruangan (semua ruangan di jurusan)
        $rooms = Room::where('jurusan', $user->jurusan)->orderBy('nama_ruangan')->get();

        // Ruangan Tersedia: tidak ada konflik di rentang filter
        $availableRooms = $rooms->filter(function ($room) use ($filterTanggalMulai, $filterTanggalSelesai, $filterWaktuMulai, $filterWaktuSelesai) {
            if (!$filterTanggalMulai || !$filterTanggalSelesai) return true;
            $waktuMulai  = $filterWaktuMulai  ?: '00:00';
            $waktuSelesai = $filterWaktuSelesai ?: '23:59';
            return $room->isAvailableFor($filterTanggalMulai, $filterTanggalSelesai, $waktuMulai, $waktuSelesai);
        })->values();

        $nowDate = now()->format('Y-m-d');
        $nowTime = now()->format('H:i:s');

        // Ruangan Digunakan / Sedang Digunakan
        $usedRoomItems = RoomBookingItem::with(['room', 'booking.user'])
            ->whereHas('booking', function ($q) use ($user) {
                $q->where('jurusan', $user->jurusan)
                  ->whereIn('status', ['pending', 'disetujui']);
            });

        if ($filterTanggalMulai && $filterTanggalSelesai) {
            $wm  = $filterWaktuMulai  ?: '00:00';
            $ws  = $filterWaktuSelesai ?: '23:59';
            $usedRoomItems = $usedRoomItems
                ->where('tanggal_mulai', '<=', $filterTanggalSelesai)
                ->where('tanggal_selesai', '>=', $filterTanggalMulai)
                ->where('waktu_mulai', '<', $ws)
                ->where('waktu_selesai', '>', $wm);
        } else {
            // Default tampilkan yang sedang digunakan sekarang atau ke depan
            $usedRoomItems = $usedRoomItems->where(function ($q) use ($nowDate, $nowTime) {
                $q->where('tanggal_selesai', '>', $nowDate)
                  ->orWhere(function ($q2) use ($nowDate, $nowTime) {
                      $q2->where('tanggal_selesai', '=', $nowDate)
                         ->where('waktu_selesai', '>=', $nowTime);
                  });
            });
        }
        $usedRoomItems = $usedRoomItems->orderBy('tanggal_mulai')->get();

        // 1. Verifikasi (Pending Bookings)
        $pendingBookings = RoomBooking::with(['user', 'items.room'])
            ->where('jurusan', $user->jurusan)
            ->where('status', 'pending')
            ->orderBy('tanggal_pengajuan', 'desc')
            ->get();

        // 2. Ruangan Disetujui (Active Approved Bookings: yang belum habis waktunya)
        $approvedBookings = RoomBooking::with(['user', 'items.room', 'laboran'])
            ->where('jurusan', $user->jurusan)
            ->where('status', 'disetujui')
            ->whereHas('items', function ($q) use ($nowDate, $nowTime) {
                $q->where('tanggal_selesai', '>', $nowDate)
                  ->orWhere(function ($q2) use ($nowDate, $nowTime) {
                      $q2->where('tanggal_selesai', '=', $nowDate)
                         ->where('waktu_selesai', '>=', $nowTime);
                  });
            })
            ->orderBy('tanggal_pengajuan', 'desc')
            ->get();

        // 3. Riwayat Peminjaman Ruangan (Expired Approved Bookings: yang sudah habis waktunya)
        $historyBookings = RoomBooking::with(['user', 'items.room', 'laboran'])
            ->where('jurusan', $user->jurusan)
            ->where('status', 'disetujui')
            ->whereDoesntHave('items', function ($q) use ($nowDate, $nowTime) {
                $q->where('tanggal_selesai', '>', $nowDate)
                  ->orWhere(function ($q2) use ($nowDate, $nowTime) {
                      $q2->where('tanggal_selesai', '=', $nowDate)
                         ->where('waktu_selesai', '>=', $nowTime);
                  });
            })
            ->orderBy('tanggal_pengajuan', 'desc')
            ->get();

        // 4. Ditolak / Ditangguhkan
        $rejectedBookings = RoomBooking::with(['user', 'items.room', 'laboran'])
            ->where('jurusan', $user->jurusan)
            ->whereIn('status', ['ditolak', 'ditangguhkan'])
            ->orderBy('tanggal_pengajuan', 'desc')
            ->get();

        return view('room-bookings-laboran', compact(
            'pendingBookings',
            'approvedBookings',
            'historyBookings',
            'rejectedBookings',
            'rooms',
            'availableRooms',
            'usedRoomItems',
            'filterTanggalMulai',
            'filterTanggalSelesai',
            'filterWaktuMulai',
            'filterWaktuSelesai'
        ));
    }

    /**
     * Setujui peminjaman ruangan.
     */
    public function approve($id)
    {
        $user    = Auth::user();
        $booking = RoomBooking::where('jurusan', $user->jurusan)->findOrFail($id);

        // Re-check conflicts before approving
        $conflicts = [];
        foreach ($booking->items as $item) {
            if (!$item->room->isAvailableFor(
                $item->tanggal_mulai->format('Y-m-d'),
                $item->tanggal_selesai->format('Y-m-d'),
                $item->waktu_mulai,
                $item->waktu_selesai,
                $booking->id
            )) {
                $conflicts[] = $item->room->nama_ruangan;
            }
        }

        if (!empty($conflicts)) {
            return redirect()->back()
                ->withErrors(['error' => 'Konflik jadwal terdeteksi: ' . implode(', ', $conflicts)]);
        }

        $booking->update([
            'status'     => 'disetujui',
            'laboran_id' => $user->id,
        ]);

        return redirect()->back()->with('success', 'Peminjaman ruangan berhasil disetujui.');
    }

    /**
     * Tolak peminjaman ruangan.
     */
    public function reject(Request $request, $id)
    {
        $user    = Auth::user();
        $booking = RoomBooking::where('jurusan', $user->jurusan)->findOrFail($id);

        $booking->update([
            'status'          => 'ditolak',
            'laboran_id'      => $user->id,
            'catatan_laboran' => $request->input('catatan_laboran'),
        ]);

        return redirect()->back()->with('success', 'Peminjaman ruangan telah ditolak.');
    }

    /**
     * Update booking oleh laboran (edit ruangan/waktu).
     */
    public function update(Request $request, $id)
    {
        $user    = Auth::user();
        $booking = RoomBooking::where('jurusan', $user->jurusan)->findOrFail($id);

        $request->validate([
            'tujuan_penggunaan'      => 'required|string|max:500',
            'jumlah_mahasiswa'       => 'required|integer|min:1',
            'rooms'                  => 'required|array|min:1',
            'rooms.*.room_id'        => 'required|exists:rooms,id',
            'rooms.*.tanggal_mulai'  => 'required|date',
            'rooms.*.tanggal_selesai'=> 'required|date|after_or_equal:rooms.*.tanggal_mulai',
            'rooms.*.waktu_mulai'    => 'required|date_format:H:i',
            'rooms.*.waktu_selesai'  => 'required|date_format:H:i|after:rooms.*.waktu_mulai',
        ]);

        // Conflict check (excluding current booking)
        $conflicts = [];
        foreach ($request->rooms as $item) {
            $room = Room::find($item['room_id']);
            if (!$room) continue;

            if (!$room->isAvailableFor(
                $item['tanggal_mulai'],
                $item['tanggal_selesai'],
                $item['waktu_mulai'],
                $item['waktu_selesai'],
                $booking->id
            )) {
                $conflicts[] = $room->nama_ruangan;
            }
        }

        if (!empty($conflicts)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['rooms' => 'Konflik jadwal: ' . implode(', ', $conflicts)]);
        }

        DB::transaction(function () use ($request, $booking) {
            $booking->update([
                'tujuan_penggunaan' => $request->tujuan_penggunaan,
                'jumlah_mahasiswa'  => $request->jumlah_mahasiswa,
            ]);

            // Delete old items and re-insert
            $booking->items()->delete();
            foreach ($request->rooms as $item) {
                RoomBookingItem::create([
                    'room_booking_id' => $booking->id,
                    'room_id'         => $item['room_id'],
                    'tanggal_mulai'   => $item['tanggal_mulai'],
                    'tanggal_selesai' => $item['tanggal_selesai'],
                    'waktu_mulai'     => $item['waktu_mulai'],
                    'waktu_selesai'   => $item['waktu_selesai'],
                ]);
            }
        });

        return redirect()->back()->with('success', 'Data peminjaman ruangan berhasil diperbarui.');
    }

    /**
     * Cetak formulir PDF peminjaman ruangan (QR Code verifikasi).
     */
    public function printPdf(Request $request, $id)
    {
        $booking = RoomBooking::with(['user', 'items.room', 'laboran'])->findOrFail($id);

        // Generate QR verification URL
        $verifyUrl = route('room-bookings.verify-qr', ['token' => $booking->qr_token]);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.room-booking', compact('booking', 'verifyUrl'));
        $pdf->setPaper('a4', 'portrait');

        $borrowerName = $booking->is_insidentil ? $booking->peminjam_insidentil : ($booking->user ? $booking->user->name : 'Peminjam');
        $filename = 'RB-' . str_pad($booking->id, 5, '0', STR_PAD_LEFT) . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $borrowerName) . '_' . $booking->tanggal_pengajuan->format('Y-m-d') . '.pdf';

        if ($request->query('action') === 'preview') {
            return $pdf->stream($filename);
        }

        return $pdf->download($filename);
    }

    /**
     * Verifikasi QR Code peminjaman ruangan (public).
     */
    public function verifyQr($token)
    {
        $booking = RoomBooking::with(['user', 'items.room', 'laboran'])
            ->where('qr_token', $token)
            ->firstOrFail();

        return view('room-bookings-verify', compact('booking'));
    }

    /**
     * Hapus peminjaman ruangan oleh laboran (permanen).
     */
    public function laboranDestroy($id)
    {
        $user    = Auth::user();
        $booking = RoomBooking::where('jurusan', $user->jurusan)->findOrFail($id);
        $booking->delete();

        return redirect()->back()->with('success', 'Peminjaman ruangan berhasil dihapus.');
    }
}
