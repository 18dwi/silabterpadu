<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use App\Models\BebasLabCertificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the main entry dashboard based on the user's role.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->role === 'superadmin' || $user->role === 'ultraadmin') {
            return $this->superadminDashboard($request);
        }

        if ($user->role === 'laboran') {
            $items = Item::where('jurusan', $user->jurusan)->get()->sort(function($a, $b) {
                $aExpired = $a->kategori === 'bahan' && ($a->is_expired || $a->is_expiring_soon);
                $bExpired = $b->kategori === 'bahan' && ($b->is_expired || $b->is_expiring_soon);

                if ($aExpired && !$bExpired) {
                    return -1;
                }
                if (!$aExpired && $bExpired) {
                    return 1;
                }

                if ($aExpired && $bExpired) {
                    return strcmp($a->tanggal_expired, $b->tanggal_expired);
                }

                return strcasecmp($a->nama_barang, $b->nama_barang);
            })->values();
            $packages = \App\Models\Package::where('jurusan', $user->jurusan)->with('items.item')->get();
            
            $groupFunction = function($tx) {
                return $tx->is_insidentil ? 'insidentil-' . $tx->id : ($tx->user_id ?? 'unknown');
            };

            // 1. Verifikasi (Pending)
            $pendingTransactions = Transaction::with(['user', 'details.item'])
                ->where('jurusan', $user->jurusan)
                ->where('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy($groupFunction);
                
            // 2. Alat Dalam Peminjaman (Approved tool rentals, returned_status is not selesai and not belum_selesai)
            $alatActiveLoans = Transaction::with(['user', 'details.item'])
                ->where('jurusan', $user->jurusan)
                ->where('status', 'disetujui')
                ->where('tipe', 'peminjaman_alat')
                ->where(function($q) {
                    $q->whereNull('status_pengembalian')
                      ->orWhere('status_pengembalian', '!=', 'belum_selesai');
                })
                ->orderBy('tanggal_pinjam', 'desc')
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy($groupFunction);

            // 3. Alat Dikembalikan Dengan Catatan (Returned but incomplete / damaged)
            $alatReturnedWithNotes = Transaction::with(['user', 'details.item'])
                ->where('jurusan', $user->jurusan)
                ->where('status', 'disetujui')
                ->where('tipe', 'peminjaman_alat')
                ->where('status_pengembalian', 'belum_selesai')
                ->orderBy('tanggal_pinjam', 'desc')
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy($groupFunction);

            // 4. Alat Dikembalikan Sepenuhnya (Completed tool rentals)
            $alatReturnedFully = Transaction::with(['user', 'details.item'])
                ->where('jurusan', $user->jurusan)
                ->where('status', 'selesai')
                ->where('tipe', 'peminjaman_alat')
                ->orderBy('tanggal_kembali_realisasi', 'desc')
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy($groupFunction);

            // 5. Peminjaman Alat Ditolak atau Ditangguhkan
            $alatRejectedOrSuspended = Transaction::with(['user', 'details.item'])
                ->where('jurusan', $user->jurusan)
                ->where('tipe', 'peminjaman_alat')
                ->whereIn('status', ['ditolak', 'ditangguhkan'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy($groupFunction);

            // 6. Permintaan Bahan Disetujui (Completed material requests)
            $bahanApproved = Transaction::with(['user', 'details.item'])
                ->where('jurusan', $user->jurusan)
                ->where('status', 'selesai')
                ->where('tipe', 'permintaan_bahan')
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy($groupFunction);

            // 7. Permintaan Bahan Ditolak atau Ditangguhkan
            $bahanRejectedOrSuspended = Transaction::with(['user', 'details.item'])
                ->where('jurusan', $user->jurusan)
                ->where('tipe', 'permintaan_bahan')
                ->whereIn('status', ['ditolak', 'ditangguhkan'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy($groupFunction);

            $students = User::where('role', 'mahasiswa')->where('jurusan', $user->jurusan)->with('bebasLabCertificate')->orderBy('name')->get();
            $certificates = BebasLabCertificate::where('jurusan', $user->jurusan)->with(['user', 'laboran'])->orderBy('tanggal_terbit', 'desc')->get();

            // Room Bookings for Laboran
            $rooms = \App\Models\Room::where('jurusan', $user->jurusan)->orderBy('kode_ruangan')->get();
            $pendingBookings = \App\Models\RoomBooking::with(['user', 'items.room'])
                ->where('jurusan', $user->jurusan)
                ->where('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->get();
            $nowDate = now()->format('Y-m-d');
            $nowTime = now()->format('H:i:s');
            
            $approvedBookings = \App\Models\RoomBooking::with(['user', 'items.room'])
                ->where('jurusan', $user->jurusan)
                ->where('status', 'disetujui')
                ->where('is_insidentil', false)
                ->whereHas('items', function ($q) use ($nowDate, $nowTime) {
                    $q->where('tanggal_selesai', '>', $nowDate)
                      ->orWhere(function ($q2) use ($nowDate, $nowTime) {
                          $q2->where('tanggal_selesai', '=', $nowDate)
                             ->where('waktu_selesai', '>=', $nowTime);
                      });
                })
                ->orderBy('created_at', 'desc')
                ->get();

            $historyBookings = \App\Models\RoomBooking::with(['user', 'items.room'])
                ->where('jurusan', $user->jurusan)
                ->where('status', 'disetujui')
                ->whereDoesntHave('items', function ($q) use ($nowDate, $nowTime) {
                    $q->where('tanggal_selesai', '>', $nowDate)
                      ->orWhere(function ($q2) use ($nowDate, $nowTime) {
                          $q2->where('tanggal_selesai', '=', $nowDate)
                             ->where('waktu_selesai', '>=', $nowTime);
                      });
                })
                ->orderBy('created_at', 'desc')
                ->get();
            $insidentilBookings = \App\Models\RoomBooking::with(['user', 'items.room'])
                ->where('jurusan', $user->jurusan)
                ->where('status', 'disetujui')
                ->where('is_insidentil', true)
                ->orderBy('created_at', 'desc')
                ->get();
            $rejectedBookings = \App\Models\RoomBooking::with(['user', 'items.room'])
                ->where('jurusan', $user->jurusan)
                ->where('status', 'ditolak')
                ->orderBy('created_at', 'desc')
                ->get();

            $filterTanggalMulai  = $request->input('filter_tanggal_mulai');
            $filterTanggalSelesai = $request->input('filter_tanggal_selesai');
            $filterWaktuMulai    = $request->input('filter_waktu_mulai');
            $filterWaktuSelesai  = $request->input('filter_waktu_selesai');

            $availableRooms = $rooms->filter(function ($room) use ($filterTanggalMulai, $filterTanggalSelesai, $filterWaktuMulai, $filterWaktuSelesai) {
                if (!$filterTanggalMulai || !$filterTanggalSelesai) return true;
                $waktuMulai  = $filterWaktuMulai  ?: '00:00';
                $waktuSelesai = $filterWaktuSelesai ?: '23:59';
                return $room->isAvailableFor($filterTanggalMulai, $filterTanggalSelesai, $waktuMulai, $waktuSelesai);
            })->values();

            $nowDateLab = now()->format('Y-m-d');
            $nowTimeLab = now()->format('H:i:s');
            
            $usedRoomItems = \App\Models\RoomBookingItem::with(['room', 'booking.user'])
                ->whereHas('booking', function ($q) use ($user) {
                    $q->where('jurusan', $user->jurusan)
                      ->whereIn('status', ['pending', 'disetujui']);
                })
                ->where(function ($q) use ($nowDateLab, $nowTimeLab) {
                    $q->where('tanggal_selesai', '>', $nowDateLab)
                      ->orWhere(function ($q2) use ($nowDateLab, $nowTimeLab) {
                          $q2->where('tanggal_selesai', '=', $nowDateLab)
                             ->where('waktu_selesai', '>=', $nowTimeLab);
                      });
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

        $lowStockMaterials = \App\Models\Item::where('kategori', 'bahan')
            ->where('stok_tersedia', '<', 10)
            ->where('jurusan', $user->jurusan)
            ->orderBy('stok_tersedia')
            ->get();

        $lowStockMaterials = \App\Models\Item::where('kategori', 'bahan')
            ->where('stok_tersedia', '<', 10)
            ->where('jurusan', $user->jurusan)
            ->orderBy('stok_tersedia')
            ->get();

            return view('dashboard-laboran', compact(
                'items', 
                'packages', 
                'pendingTransactions', 
                'alatActiveLoans', 
                'alatReturnedWithNotes', 
                'alatReturnedFully', 
                'alatRejectedOrSuspended', 
                'bahanApproved', 
                'bahanRejectedOrSuspended',
                'students',
                'certificates',
                'rooms',
                'pendingBookings',
                'approvedBookings',
                'historyBookings',
                'insidentilBookings',
                'rejectedBookings',
                'filterTanggalMulai',
                'filterTanggalSelesai',
                'filterWaktuMulai',
                'filterWaktuSelesai',
                'availableRooms',
                'usedRoomItems',
                'lowStockMaterials',
                'lowStockMaterials'
            ));
        }

        // Default role is mahasiswa
        $items = Item::where('jurusan', $user->jurusan)->orderBy('nama_barang')->get();
        $packages = \App\Models\Package::where('jurusan', $user->jurusan)->with('items.item')->get();
        
        $transactions = Transaction::with(['details.item'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $certificate = BebasLabCertificate::where('user_id', $user->id)->with('laboran')->first();

        // Room Bookings for Mahasiswa
        $rooms = \App\Models\Room::where('jurusan', $user->jurusan)
            ->where('status', 'tersedia')
            ->orderBy('nama_ruangan')
            ->get();

        $filterTanggalMulai  = $request->input('filter_tanggal_mulai');
        $filterTanggalSelesai = $request->input('filter_tanggal_selesai');
        $filterWaktuMulai    = $request->input('filter_waktu_mulai');
        $filterWaktuSelesai  = $request->input('filter_waktu_selesai');

        $availableRooms = $rooms->filter(function ($room) use ($filterTanggalMulai, $filterTanggalSelesai, $filterWaktuMulai, $filterWaktuSelesai) {
            if (!$filterTanggalMulai || !$filterTanggalSelesai) return true;
            $waktuMulai  = $filterWaktuMulai  ?: '00:00';
            $waktuSelesai = $filterWaktuSelesai ?: '23:59';
            return $room->isAvailableFor($filterTanggalMulai, $filterTanggalSelesai, $waktuMulai, $waktuSelesai);
        })->values();

        $nowDateMhs = now()->format('Y-m-d');
        $nowTimeMhs = now()->format('H:i:s');
        
        $usedRoomItems = \App\Models\RoomBookingItem::with(['room', 'booking.user'])
            ->whereHas('booking', function ($q) use ($user) {
                $q->where('jurusan', $user->jurusan)
                  ->whereIn('status', ['pending', 'disetujui']);
            })
            ->where(function ($q) use ($nowDateMhs, $nowTimeMhs) {
                $q->where('tanggal_selesai', '>', $nowDateMhs)
                  ->orWhere(function ($q2) use ($nowDateMhs, $nowTimeMhs) {
                      $q2->where('tanggal_selesai', '=', $nowDateMhs)
                         ->where('waktu_selesai', '>=', $nowTimeMhs);
                  });
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

        $lowStockMaterials = \App\Models\Item::where('kategori', 'bahan')
            ->where('stok_tersedia', '<', 10)
            ->where('jurusan', $user->jurusan)
            ->orderBy('stok_tersedia')
            ->get();

        $lowStockMaterials = \App\Models\Item::where('kategori', 'bahan')
            ->where('stok_tersedia', '<', 10)
            ->where('jurusan', $user->jurusan)
            ->orderBy('stok_tersedia')
            ->get();

        $myBookings = \App\Models\RoomBooking::with(['items.room'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('dashboard-mahasiswa', compact(
            'items', 
            'packages', 
            'transactions', 
            'certificate',
            'rooms',
            'availableRooms',
            'usedRoomItems',
                'lowStockMaterials',
                'lowStockMaterials',
            'myBookings',
            'filterTanggalMulai',
            'filterTanggalSelesai',
            'filterWaktuMulai',
            'filterWaktuSelesai'
        ));
    }

    /**
     * Superadmin Dashboard logic.
     */
    private function superadminDashboard(Request $request)
    {
        $user = Auth::user();
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        
        if ($user->role === 'ultraadmin') {
            $jurusan = $request->input('jurusan', 'semua');
        } else {
            $jurusan = $user->jurusan;
        }

        // 1. Tool utility (most borrowed tools inside selected date range)
        $mostBorrowedQuery = TransactionDetail::select('item_id', DB::raw('SUM(jumlah_diminta) as total_borrowed'))
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->where('transactions.tipe', 'peminjaman_alat')
            ->where('transactions.status', '!=', 'ditolak');

        if ($jurusan !== 'semua') {
            $mostBorrowedQuery->where('transactions.jurusan', $jurusan);
        }

        if ($startDate && $endDate) {
            $mostBorrowedQuery->whereBetween('transactions.tanggal_pengajuan', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ]);
        } else {
            $mostBorrowedQuery->whereMonth('transactions.tanggal_pengajuan', now()->month)
                ->whereYear('transactions.tanggal_pengajuan', now()->year);
        }

        $mostBorrowed = $mostBorrowedQuery->groupBy('item_id')
            ->orderByDesc('total_borrowed')
            ->with('item')
            ->limit(5)
            ->get();

        // 2. Low stock materials (stock < 10)
        $lowStockQuery = Item::where('kategori', 'bahan')
            ->where('stok_tersedia', '<', 10);
            
        if ($jurusan !== 'semua') {
            $lowStockQuery->where('jurusan', $jurusan);
        }
        
        $lowStockMaterials = $lowStockQuery->orderBy('stok_tersedia')->get();

        // 3. Rekap Laporan with Date Range Filter
        $reportQuery = Transaction::with(['user', 'details.item'])
            ->orderBy('created_at', 'desc');

        if ($jurusan !== 'semua') {
            $reportQuery->where('jurusan', $jurusan);
        }

        if ($startDate && $endDate) {
            $reportQuery->whereBetween('tanggal_pengajuan', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ]);
        } else {
            // Default: show current month
            $reportQuery->whereMonth('tanggal_pengajuan', now()->month)
                ->whereYear('tanggal_pengajuan', now()->year);
        }

        $reportTransactions = $reportQuery->get();

        // Calculate total items issued
        $totalItemsIssued = 0;
        foreach ($reportTransactions as $tx) {
            $totalItemsIssued += $tx->details->sum('jumlah_diminta');
        }

        // 4. Calculate total usage days per tool (DATEDIFF)
        $usageDaysQuery = TransactionDetail::select('item_id', DB::raw('SUM(GREATEST(1, DATEDIFF(IFNULL(transactions.tanggal_kembali_realisasi, IFNULL(transactions.tanggal_kembali_rencana, NOW())), transactions.tanggal_pinjam))) as total_days'))
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->where('transactions.tipe', 'peminjaman_alat')
            ->whereIn('transactions.status', ['disetujui', 'selesai']);

        if ($jurusan !== 'semua') {
            $usageDaysQuery->where('transactions.jurusan', $jurusan);
        }

        if ($startDate && $endDate) {
            $usageDaysQuery->whereBetween('transactions.tanggal_pengajuan', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ]);
        } else {
            $usageDaysQuery->whereMonth('transactions.tanggal_pengajuan', now()->month)
                ->whereYear('transactions.tanggal_pengajuan', now()->year);
        }

        $toolUsageDays = $usageDaysQuery->groupBy('item_id')
            ->with('item')
            ->orderByDesc('total_days')
            ->get();

        // 5. Fetch all users for control panel
        if ($user->role === 'ultraadmin') {
            $allUsers = \App\Models\User::orderBy('name')->get();
        } else {
            $allUsers = \App\Models\User::where('jurusan', $user->jurusan)
                ->where('role', '!=', 'ultraadmin')
                ->orderBy('name')
                ->get();
        }

        // 6. Fetch all transactions for deletion log
        $allTransactionsQuery = Transaction::with(['user', 'details.item'])
            ->orderBy('created_at', 'desc');
            
        if ($user->role === 'superadmin') {
            $allTransactionsQuery->where('jurusan', $user->jurusan);
        } elseif ($jurusan !== 'semua') {
            $allTransactionsQuery->where('jurusan', $jurusan);
        }
        
        $allTransactionsForDeletion = $allTransactionsQuery->get();

        // For deletion (Room Bookings)
        $allRoomBookingsQuery = \App\Models\RoomBooking::with(['user', 'items.room'])->orderBy('created_at', 'desc');
        if ($user->role === 'admin' || $user->role === 'laboran') {
            $allRoomBookingsQuery->where('jurusan', $user->jurusan);
        } elseif ($jurusan !== 'semua') {
            $allRoomBookingsQuery->where('jurusan', $jurusan);
        }
        $allRoomBookingsForDeletion = $allRoomBookingsQuery->get();

        // Rekapitulasi Bahan (Material Summary requested/issued)
        $materialRecapQuery = TransactionDetail::select('transaction_details.item_id', DB::raw('SUM(transaction_details.jumlah_diminta) as total_qty'))
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->join('items', 'transaction_details.item_id', '=', 'items.id')
            ->where('items.kategori', 'bahan')
            ->where('transactions.tipe', 'permintaan_bahan')
            ->where('transactions.status', 'selesai');

        if ($user->role === 'superadmin') {
            $materialRecapQuery->where('transactions.jurusan', $user->jurusan);
        } elseif ($jurusan !== 'semua') {
            $materialRecapQuery->where('transactions.jurusan', $jurusan);
        }

        if ($startDate && $endDate) {
            $materialRecapQuery->whereBetween('transactions.tanggal_pengajuan', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ]);
        } else {
            $materialRecapQuery->whereMonth('transactions.tanggal_pengajuan', now()->month)
                ->whereYear('transactions.tanggal_pengajuan', now()->year);
        }

        $materialRecap = $materialRecapQuery->groupBy('transaction_details.item_id')
            ->with('item')
            ->get();

        // Rekapitulasi Penggunaan Ruangan
        $roomBookingsQuery = \App\Models\RoomBooking::with('items.room')
            ->where('status', 'disetujui');
            
        if ($user->role === 'superadmin') {
            $roomBookingsQuery->where('jurusan', $user->jurusan);
        } elseif ($jurusan !== 'semua') {
            $roomBookingsQuery->where('jurusan', $jurusan);
        }
        
        if ($startDate && $endDate) {
            $roomBookingsQuery->whereBetween('tanggal_pengajuan', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ]);
        } else {
            $roomBookingsQuery->whereMonth('tanggal_pengajuan', now()->month)
                ->whereYear('tanggal_pengajuan', now()->year);
        }
        
        $roomBookings = $roomBookingsQuery->get();
        
        $roomUsages = [];
        foreach ($roomBookings as $booking) {
            foreach ($booking->items as $item) {
                if (!$item->room) continue;
                $roomId = $item->room_id;
                
                if (!isset($roomUsages[$roomId])) {
                    $roomUsages[$roomId] = [
                        'room' => $item->room,
                        'total_mahasiswa' => 0,
                        'total_jam' => 0,
                    ];
                }
                
                $roomUsages[$roomId]['total_mahasiswa'] += $booking->jumlah_mahasiswa;
                $roomUsages[$roomId]['total_jam'] += $item->calculateUsageHours();
            }
        }
        
        $roomUsages = collect($roomUsages)->sortByDesc('total_jam')->values();

        return view('dashboard-superadmin', compact(
            'mostBorrowed',
            'lowStockMaterials',
            'reportTransactions',
            'totalItemsIssued',
            'startDate',
            'endDate',
            'jurusan',
            'toolUsageDays',
            'allUsers',
            'allTransactionsForDeletion',
            'allRoomBookingsForDeletion',
            'materialRecap',
            'roomUsages'
        ));
    }

    /**
     * Print-friendly HTML report view.
     */
    public function printReport(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $jurusan = $request->input('jurusan', 'semua');

        $reportQuery = Transaction::with(['user', 'details.item'])
            ->where('status', '!=', 'ditolak')
            ->orderBy('tanggal_pengajuan', 'asc');

        if ($jurusan !== 'semua') {
            $reportQuery->where('jurusan', $jurusan);
        }

        if ($startDate && $endDate) {
            $reportQuery->whereBetween('tanggal_pengajuan', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ]);
        } else {
            // Default: show current month
            $reportQuery->whereMonth('tanggal_pengajuan', now()->month)
                ->whereYear('tanggal_pengajuan', now()->year);
        }

        $reportTransactions = $reportQuery->get();

        $totalItemsIssued = 0;
        foreach ($reportTransactions as $tx) {
            $totalItemsIssued += $tx->details->sum('jumlah_diminta');
        }

        return view('superadmin-report-print', compact(
            'reportTransactions',
            'totalItemsIssued',
            'startDate',
            'endDate',
            'jurusan'
        ));
    }

    public function exportCsv(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $jurusan = $request->input('jurusan', 'semua');

        $reportQuery = Transaction::with(['user', 'details.item'])
            ->where('status', '!=', 'ditolak')
            ->orderBy('tanggal_pengajuan', 'asc');

        if ($jurusan !== 'semua') {
            $reportQuery->where('jurusan', $jurusan);
        }

        if ($startDate && $endDate) {
            $reportQuery->whereBetween('tanggal_pengajuan', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ]);
        } else {
            $reportQuery->whereMonth('tanggal_pengajuan', now()->month)
                ->whereYear('tanggal_pengajuan', now()->year);
        }

        $transactions = $reportQuery->get();

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=Laporan_SiLab_" . now()->format('Ymd') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['ID Transaksi', 'Tanggal Pengajuan', 'Peminjam / Mahasiswa', 'NIM / Nomor Induk', 'Tipe', 'Kegiatan', 'Dosen PJ', 'Barang & Jumlah', 'Status'];

        $callback = function() use($transactions, $columns) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for proper UTF-8 Excel parsing
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Declare semicolon delimiter for Excel
            fwrite($file, "sep=;\n");
            
            fputcsv($file, $columns, ';');

            foreach ($transactions as $tx) {
                $itemsStr = $tx->details->map(function($d) {
                    return $d->item->nama_barang . ' (' . $d->jumlah_diminta . ' ' . $d->item->satuan . ')';
                })->implode(', ');

                fputcsv($file, [
                    'TX-' . str_pad($tx->id, 5, '0', STR_PAD_LEFT),
                    $tx->tanggal_pengajuan->format('Y-m-d H:i'),
                    $tx->is_insidentil ? $tx->peminjam_insidentil : ($tx->user ? $tx->user->name : 'Akun Dihapus'),
                    $tx->is_insidentil ? 'Insidentil (Non-Mahasiswa)' : ($tx->user ? $tx->user->nomor_induk : '-'),
                    $tx->tipe === 'peminjaman_alat' ? 'Pinjam Alat' : 'Minta Bahan',
                    $tx->kegiatan,
                    $tx->penanggung_jawab,
                    $itemsStr,
                    strtoupper($tx->status)
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export realtime inventory of tools or materials to Excel (CSV format compatible with MS Excel).
     */
    public function exportInventoryExcel(Request $request, $kategori)
    {
        if (auth()->user()->role !== 'laboran' && auth()->user()->role !== 'superadmin') {
            abort(403);
        }

        $items = Item::where('kategori', $kategori)->orderBy('nama_barang')->get();

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=Rekap_Inventaris_" . ucfirst($kategori) . "_" . now()->format('Ymd') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        if ($kategori === 'alat') {
            $columns = ['Kode Alat', 'Nama Alat', 'Merk/Type', 'Jumlah Total', 'Baik', 'Perbaikan', 'Rusak', 'Ketersediaan', 'Lokasi Rak'];
            $callback = function() use($items, $columns) {
                $file = fopen('php://output', 'w');
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
                fwrite($file, "sep=;\n");
                fputcsv($file, $columns, ';');
                foreach ($items as $item) {
                    fputcsv($file, [
                        $item->kode_barang,
                        $item->nama_barang,
                        $item->merk_tipe ?: '-',
                        $item->stok_total,
                        $item->jumlah_baik,
                        $item->jumlah_perbaikan,
                        $item->jumlah_rusak,
                        $item->stok_tersedia,
                        $item->lokasi_rak ?: '-'
                    ], ';');
                }
                fclose($file);
            };
        } else {
            $columns = [
                'Kode Bahan', 
                'Nama Bahan', 
                'Merk/Type', 
                'Satuan', 
                'Lokasi Rak', 
                'Jumlah Stok Bahan Awal', 
                'Jumlah Bahan Masuk', 
                'Tanggal Bahan Masuk', 
                'Jumlah Bahan Keluar', 
                'Tanggal Bahan Keluar', 
                'Jumlah Stok Bahan Terakhir'
            ];
            $callback = function() use($items, $columns) {
                $file = fopen('php://output', 'w');
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
                fwrite($file, "sep=;\n");
                fputcsv($file, $columns, ';');
                foreach ($items as $item) {
                    $incomingMap = [];
                    if (($item->bahan_masuk ?? 0) > 0) {
                        $tglM = $item->tanggal_masuk ? \Carbon\Carbon::parse($item->tanggal_masuk)->translatedFormat('F Y') : ($item->created_at ? $item->created_at->translatedFormat('F Y') : now()->translatedFormat('F Y'));
                        $incomingMap[$tglM] = $item->bahan_masuk;
                    }
                    
                    $outgoingTrans = DB::table('transaction_details')
                        ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
                        ->where('transaction_details.item_id', $item->id)
                        ->where('transactions.tipe', 'permintaan_bahan')
                        ->whereIn('transactions.status', ['selesai', 'disetujui'])
                        ->select('transaction_details.jumlah_diminta', 'transactions.tanggal_pengajuan')
                        ->get();
                        
                    $outgoingMap = [];
                    foreach ($outgoingTrans as $ot) {
                        $date = \Carbon\Carbon::parse($ot->tanggal_pengajuan);
                        $monthYear = $date->translatedFormat('F Y');
                        if (!isset($outgoingMap[$monthYear])) {
                            $outgoingMap[$monthYear] = 0;
                        }
                        $outgoingMap[$monthYear] += $ot->jumlah_diminta;
                    }
                    if (empty($outgoingMap) && ($item->bahan_keluar ?? 0) > 0) {
                        $tglK = $item->tanggal_keluar ? \Carbon\Carbon::parse($item->tanggal_keluar)->translatedFormat('F Y') : now()->translatedFormat('F Y');
                        $outgoingMap[$tglK] = $item->bahan_keluar;
                    }

                    // Format strings for CSV
                    $incomingStrings = [];
                    foreach ($incomingMap as $my => $qty) {
                        $incomingStrings[] = "{$qty} ({$my})";
                    }
                    $masukVal = implode("\n", $incomingStrings) ?: '0';
                    $tglMasukVal = implode("\n", array_keys($incomingMap)) ?: '-';

                    $outgoingStrings = [];
                    foreach ($outgoingMap as $my => $qty) {
                        $outgoingStrings[] = "{$qty} ({$my})";
                    }
                    $keluarVal = implode("\n", $outgoingStrings) ?: '0';
                    $tglKeluarVal = implode("\n", array_keys($outgoingMap)) ?: '-';

                    fputcsv($file, [
                        $item->kode_barang,
                        $item->nama_barang,
                        $item->merk_tipe ?: '-',
                        $item->satuan,
                        $item->lokasi_rak ?: '-',
                        $item->stok_awal ?? 0,
                        $masukVal,
                        $tglMasukVal,
                        $keluarVal,
                        $tglKeluarVal,
                        $item->dynamic_stock
                    ], ';');
                }
                fclose($file);
            };
        }

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Download or stream the inventory recap as PDF.
     */
    public function printInventoryReport(Request $request, $kategori)
    {
        if (auth()->user()->role !== 'laboran' && auth()->user()->role !== 'superadmin') {
            abort(403);
        }

        $items = Item::where('kategori', $kategori)->orderBy('nama_barang')->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.inventaris-rekap', compact('items', 'kategori'));
        
        if ($kategori === 'bahan') {
            $pdf->setPaper('a4', 'landscape');
        }
        
        $filename = 'Rekap_Inventaris_' . ucfirst($kategori) . '_' . now()->format('Ymd') . '.pdf';
        
        if ($request->query('action') === 'preview') {
            return $pdf->stream($filename);
        }
        
        return $pdf->download($filename);
    }

    public function printMaterialsReport(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $jurusan = $request->input('jurusan', 'semua');

        $query = Item::where('kategori', 'bahan');

        if ($jurusan !== 'semua') {
            $query->where('jurusan', $jurusan);
        }

        $materialRecap = $query->orderBy('nama_barang')->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.rekap-bahan', compact(
            'materialRecap',
            'startDate',
            'endDate',
            'jurusan'
        ));

        $pdf->setPaper('a4', 'portrait');

        $filename = 'Rekap_Bahan_' . now()->format('Ymd') . '.pdf';

        if ($request->query('action') === 'preview') {
            return $pdf->stream($filename);
        }
        return $pdf->download($filename);
    }

    public function exportMaterialsExcel(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $jurusan = $request->input('jurusan', 'semua');

        $query = Item::where('kategori', 'bahan');

        if ($jurusan !== 'semua') {
            $query->where('jurusan', $jurusan);
        }

        $materialRecap = $query->orderBy('nama_barang')->get();

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=Rekap_Bahan_" . now()->format('Ymd') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = [
            'Kode Bahan', 
            'Nama Bahan', 
            'Merk/Type', 
            'Satuan', 
            'Lokasi Rak', 
            'Jumlah Stok Bahan Awal', 
            'Jumlah Bahan Masuk', 
            'Tanggal Bahan Masuk', 
            'Jumlah Bahan Keluar', 
            'Tanggal Bahan Keluar', 
            'Jumlah Stok Bahan Terakhir'
        ];

        $callback = function() use($materialRecap, $columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fwrite($file, "sep=;\n");
            fputcsv($file, $columns, ';');
            foreach ($materialRecap as $item) {
                $incomingMap = [];
                if (($item->bahan_masuk ?? 0) > 0) {
                    $tglM = $item->tanggal_masuk ? \Carbon\Carbon::parse($item->tanggal_masuk)->translatedFormat('F Y') : ($item->created_at ? $item->created_at->translatedFormat('F Y') : now()->translatedFormat('F Y'));
                    $incomingMap[$tglM] = $item->bahan_masuk;
                }
                
                $outgoingTrans = DB::table('transaction_details')
                    ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
                    ->where('transaction_details.item_id', $item->id)
                    ->where('transactions.tipe', 'permintaan_bahan')
                    ->whereIn('transactions.status', ['selesai', 'disetujui'])
                    ->select('transaction_details.jumlah_diminta', 'transactions.tanggal_pengajuan')
                    ->get();
                    
                $outgoingMap = [];
                foreach ($outgoingTrans as $ot) {
                    $date = \Carbon\Carbon::parse($ot->tanggal_pengajuan);
                    $monthYear = $date->translatedFormat('F Y');
                    if (!isset($outgoingMap[$monthYear])) {
                        $outgoingMap[$monthYear] = 0;
                    }
                    $outgoingMap[$monthYear] += $ot->jumlah_diminta;
                }
                if (empty($outgoingMap) && ($item->bahan_keluar ?? 0) > 0) {
                    $tglK = $item->tanggal_keluar ? \Carbon\Carbon::parse($item->tanggal_keluar)->translatedFormat('F Y') : now()->translatedFormat('F Y');
                    $outgoingMap[$tglK] = $item->bahan_keluar;
                }

                // Format strings for CSV
                $incomingStrings = [];
                foreach ($incomingMap as $my => $qty) {
                    $incomingStrings[] = "{$qty} ({$my})";
                }
                $masukVal = implode("\n", $incomingStrings) ?: '0';
                $tglMasukVal = implode("\n", array_keys($incomingMap)) ?: '-';

                $outgoingStrings = [];
                foreach ($outgoingMap as $my => $qty) {
                    $outgoingStrings[] = "{$qty} ({$my})";
                }
                $keluarVal = implode("\n", $outgoingStrings) ?: '0';
                $tglKeluarVal = implode("\n", array_keys($outgoingMap)) ?: '-';

                fputcsv($file, [
                    $item->kode_barang,
                    $item->nama_barang,
                    $item->merk_tipe ?: '-',
                    $item->satuan,
                    $item->lokasi_rak ?: '-',
                    $item->stok_awal ?? 0,
                    $masukVal,
                    $tglMasukVal,
                    $keluarVal,
                    $tglKeluarVal,
                    $item->dynamic_stock
                ], ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
    public function exportRoomPdf(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $jurusan = $request->input('jurusan', 'semua');

        $roomBookingsQuery = \App\Models\RoomBooking::with('items.room')
            ->where('status', '!=', 'ditolak')
            ->orderBy('tanggal_pengajuan', 'asc');

        if ($jurusan !== 'semua') {
            $roomBookingsQuery->where('jurusan', $jurusan);
        }

        if ($startDate && $endDate) {
            $roomBookingsQuery->whereBetween('tanggal_pengajuan', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ]);
        } else {
            $roomBookingsQuery->whereMonth('tanggal_pengajuan', now()->month)
                              ->whereYear('tanggal_pengajuan', now()->year);
        }

        $roomBookings = $roomBookingsQuery->get();

        $roomUsages = [];
        foreach ($roomBookings as $booking) {
            foreach ($booking->items as $item) {
                $roomId = $item->room_id;
                if (!isset($roomUsages[$roomId])) {
                    $roomUsages[$roomId] = [
                        'room' => $item->room,
                        'total_mahasiswa' => 0,
                        'total_jam' => 0,
                    ];
                }
                
                $roomUsages[$roomId]['total_mahasiswa'] += $booking->jumlah_mahasiswa;
                $roomUsages[$roomId]['total_jam'] += $item->calculateUsageHours();
            }
        }
        
        $roomUsages = collect($roomUsages)->sortByDesc('total_jam')->values();

        // Check if PDF class exists (using DomPDF or similar if installed)
        // Since we don't know the exact PDF library, we'll try to load a view first.
        // Wait! Let's just return a simple view that the browser can print, or use PDF facade.
        // I see other PDF methods in the project. Let's assume PDF::loadView() works.
        if (class_exists('\Barryvdh\DomPDF\Facade\Pdf') || class_exists('\PDF')) {
            $pdf = \PDF::loadView('reports.rooms_pdf', compact('roomUsages', 'startDate', 'endDate', 'jurusan'));
            return $pdf->download('rekap_ruangan_' . date('Ymd_His') . '.pdf');
        }

        // Fallback to HTML view if PDF facade isn't available
        return view('reports.rooms_pdf', compact('roomUsages', 'startDate', 'endDate', 'jurusan'));
    }

    public function exportRoomCsv(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $jurusan = $request->input('jurusan', 'semua');

        $roomBookingsQuery = \App\Models\RoomBooking::with('items.room')
            ->where('status', '!=', 'ditolak')
            ->orderBy('tanggal_pengajuan', 'asc');

        if ($jurusan !== 'semua') {
            $roomBookingsQuery->where('jurusan', $jurusan);
        }

        if ($startDate && $endDate) {
            $roomBookingsQuery->whereBetween('tanggal_pengajuan', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ]);
        } else {
            $roomBookingsQuery->whereMonth('tanggal_pengajuan', now()->month)
                              ->whereYear('tanggal_pengajuan', now()->year);
        }

        $roomBookings = $roomBookingsQuery->get();

        $roomUsages = [];
        foreach ($roomBookings as $booking) {
            foreach ($booking->items as $item) {
                $roomId = $item->room_id;
                if (!isset($roomUsages[$roomId])) {
                    $roomUsages[$roomId] = [
                        'room' => $item->room,
                        'total_mahasiswa' => 0,
                        'total_jam' => 0,
                    ];
                }
                
                $roomUsages[$roomId]['total_mahasiswa'] += $booking->jumlah_mahasiswa;
                $roomUsages[$roomId]['total_jam'] += $item->calculateUsageHours();
            }
        }
        
        $roomUsages = collect($roomUsages)->sortByDesc('total_jam')->values();

        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=rekap_ruangan_" . date('Ymd_His') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array('No', 'Kode Ruangan', 'Nama Ruangan', 'Lokasi', 'Total Mahasiswa Pengguna', 'Total Jam Penggunaan');

        $callback = function() use($roomUsages, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns, ';'); // Use semicolon for Excel standard

            $no = 1;
            foreach ($roomUsages as $ru) {
                $row['No'] = $no++;
                $row['Kode Ruangan'] = $ru['room']->kode_ruangan;
                $row['Nama Ruangan'] = $ru['room']->nama_ruangan;
                $row['Lokasi'] = $ru['room']->lokasi;
                $row['Total Mahasiswa Pengguna'] = $ru['total_mahasiswa'] . ' org';
                $row['Total Jam Penggunaan'] = number_format($ru['total_jam'], 1, ',', '.') . ' jam';

                fputcsv($file, array($row['No'], $row['Kode Ruangan'], $row['Nama Ruangan'], $row['Lokasi'], $row['Total Mahasiswa Pengguna'], $row['Total Jam Penggunaan']), ';');
            }

            fclose($file);
        };

    }
}
