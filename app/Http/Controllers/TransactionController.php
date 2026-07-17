<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    /**
     * Submit a new transaction request (mahasiswa).
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'tipe' => 'required|in:peminjaman_alat,permintaan_bahan',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.jumlah_diminta' => 'required|integer|min:1',
            'items.*.package_id' => 'nullable|exists:packages,id',
            'items.*.package_qty' => 'nullable|integer|min:1',
            'tanggal_pinjam' => 'required|date',
            'tanggal_kembali_rencana' => 'required_if:tipe,peminjaman_alat|nullable|date|after_or_equal:tanggal_pinjam',
            'penanggung_jawab' => 'required|string|max:255',
            'kegiatan' => 'required|string|max:255',
            'is_insidentil' => 'nullable|boolean',
            'peminjam_insidentil' => 'required_if:is_insidentil,1|nullable|string|max:255',
        ]);

        $isInsidentil = (bool)$request->is_insidentil;
        $userId = $isInsidentil ? null : (Auth::id() ?? $request->user_id);

        if (!$isInsidentil && !$userId) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Unauthenticated or user_id missing.'], 401);
            }
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        if (!$isInsidentil && $userId) {
            $hasClearance = \App\Models\BebasLabCertificate::where('user_id', $userId)->exists();
            if ($hasClearance) {
                if ($request->wantsJson()) {
                    return response()->json(['error' => 'Anda telah diterbitkan surat bebas laboratorium dan tidak dapat melakukan peminjaman/permintaan lagi.'], 403);
                }
                return redirect()->back()->with('error', 'Anda telah diterbitkan surat bebas laboratorium dan tidak dapat melakukan peminjaman/permintaan lagi.');
            }
        }

        try {
            $transaction = DB::transaction(function () use ($request, $userId, $isInsidentil) {
                // If insidentil, status is disetujui (for tools) or selesai (for materials)
                $status = 'pending';
                if ($isInsidentil) {
                    $status = $request->tipe === 'permintaan_bahan' ? 'selesai' : 'disetujui';
                }

                // Create transaction
                $transaction = Transaction::create([
                    'user_id' => $userId,
                    'tipe' => $request->tipe,
                    'tanggal_pengajuan' => now(),
                    'tanggal_pinjam' => $request->tanggal_pinjam,
                    'tanggal_kembali_rencana' => $request->tipe === 'peminjaman_alat' ? $request->tanggal_kembali_rencana : null,
                    'penanggung_jawab' => $request->penanggung_jawab,
                    'kegiatan' => $request->kegiatan,
                    'status' => $status,
                    'laboran_id' => $isInsidentil ? Auth::id() : null,
                    'is_insidentil' => $isInsidentil,
                    'peminjam_insidentil' => $isInsidentil ? $request->peminjam_insidentil : null,
                    'jurusan' => Auth::user()->jurusan ?? 'keperawatan',
                ]);

                // Create transaction details and deduct stocks if insidentil
                foreach ($request->items as $itemData) {
                    TransactionDetail::create([
                        'transaction_id' => $transaction->id,
                        'item_id' => $itemData['item_id'],
                        'jumlah_diminta' => $itemData['jumlah_diminta'],
                        'package_id' => $itemData['package_id'] ?? null,
                        'package_qty' => $itemData['package_qty'] ?? null,
                    ]);

                    if ($isInsidentil) {
                        $item = Item::lockForUpdate()->findOrFail($itemData['item_id']);
                        if ($item->stok_tersedia < $itemData['jumlah_diminta']) {
                            throw new \Exception("Stok tidak mencukupi untuk barang: " . $item->nama_barang);
                        }
                        $item->stok_tersedia -= $itemData['jumlah_diminta'];
                        if ($item->kategori === 'bahan') {
                            $item->stok_total -= $itemData['jumlah_diminta'];
                            $item->bahan_keluar += $itemData['jumlah_diminta'];
                            $item->tanggal_keluar = $transaction->tanggal_pinjam ? \Carbon\Carbon::parse($transaction->tanggal_pinjam)->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s');
                        }
                        if ($item->stok_tersedia === 0) {
                            $item->status = 'dipinjam';
                        }
                        $item->save();
                    }
                }

                return $transaction;
            });

            // Automatically generate and upload PDF to Google Drive if insidentil
            if ($isInsidentil) {
                try {
                    $transaction->load(['laboran', 'details.item']);
                    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.peminjaman', compact('transaction'));
                    $pdfContent = $pdf->output();

                    $borrowerName = $transaction->peminjam_insidentil;
                    $filename = 'TX-INS-' . str_pad($transaction->id, 5, '0', STR_PAD_LEFT) . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $borrowerName) . '_' . $transaction->tanggal_pengajuan->format('Y-m-d') . '.pdf';

                    $driveService = new \App\Services\GoogleDriveService();
                    $driveService->uploadTransactionPdf($pdfContent, $filename);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Failed to automatically sync Insidentil PDF to Google Drive: " . $e->getMessage());
                }
            }

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Transaction request submitted successfully.',
                    'transaction' => $transaction->load('details.item')
                ], 201);
            }

            return redirect()->back()->with('success', $isInsidentil ? 'Transaksi insidentil berhasil dicatat.' : 'Pengajuan transaksi berhasil diajukan.');

        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Failed to process request: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->withErrors(['error' => 'Gagal mengajukan transaksi: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Display a list of all pending transaction requests (laboran).
     */
    public function pendingList()
    {
        $transactions = Transaction::with(['user', 'details.item'])
            ->where('status', 'pending')
            ->orderBy('tanggal_pengajuan', 'desc')
            ->get();

        return response()->json($transactions);
    }

    /**
     * Edit the requested item and quantity of a detail record in a pending or approved transaction (laboran).
     */
    public function updateDetailQuantity(Request $request, $detailId)
    {
        $request->validate([
            'jumlah_diminta' => 'required|integer|min:1',
            'item_id' => 'nullable|exists:items,id',
        ]);

        $detail = TransactionDetail::with(['transaction', 'item'])->findOrFail($detailId);
        $transaction = $detail->transaction;

        if (!in_array($transaction->status, ['pending', 'disetujui'])) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Cannot update quantity for this transaction status.'], 422);
            }
            return redirect()->back()->with('error', 'Transaksi dengan status ini tidak dapat diubah.');
        }

        $oldQty = $detail->jumlah_diminta;
        $newQty = (int)$request->jumlah_diminta;
        $diff = $newQty - $oldQty;
        
        $newItemId = $request->input('item_id');
        $itemChanged = $newItemId && (int)$newItemId !== $detail->item_id;

        if ($diff === 0 && !$itemChanged) {
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Requested quantity and item are the same.',
                    'detail' => $detail->load('item')
                ]);
            }
            return redirect()->back()->with('success', 'Tidak ada perubahan barang.');
        }

        try {
            DB::transaction(function () use ($detail, $transaction, $diff, $newQty, $newItemId, $itemChanged) {
                $oldItem = $detail->item;

                // Adjust stock if transaction is already approved
                if ($transaction->status === 'disetujui') {
                    if ($itemChanged) {
                        $newItem = Item::findOrFail($newItemId);
                        
                        // 1. Revert stock of the old item
                        if ($transaction->tipe === 'permintaan_bahan') {
                            $oldItem->stok_total += $detail->jumlah_diminta;
                            $oldItem->stok_tersedia += $detail->jumlah_diminta;
                            $oldItem->save();

                            // 2. Reduce stock of the new item
                            if ($newItem->stok_tersedia < $newQty || $newItem->stok_total < $newQty) {
                                throw new \Exception("Stok bahan '{$newItem->nama_barang}' tidak mencukupi (Tersedia: {$newItem->stok_tersedia}).");
                            }
                            $newItem->stok_total -= $newQty;
                            $newItem->stok_tersedia -= $newQty;
                            $newItem->save();
                        } else { // peminjaman_alat
                            $oldItem->stok_tersedia += $detail->jumlah_diminta;
                            if ($oldItem->stok_tersedia > 0 && $oldItem->status === 'dipinjam') {
                                $oldItem->status = 'tersedia';
                            }
                            $oldItem->save();

                            if ($newItem->stok_tersedia < $newQty) {
                                throw new \Exception("Stok alat '{$newItem->nama_barang}' tidak mencukupi (Tersedia: {$newItem->stok_tersedia}).");
                            }
                            $newItem->stok_tersedia -= $newQty;
                            if ($newItem->stok_tersedia === 0) {
                                $newItem->status = 'dipinjam';
                            }
                            $newItem->save();
                        }
                        
                        // Update detail item
                        $detail->item_id = $newItem->id;

                    } else {
                        // Just change quantity
                        $item = $oldItem;
                        if ($transaction->tipe === 'permintaan_bahan') {
                            if ($diff > 0) {
                                if ($item->stok_tersedia < $diff || $item->stok_total < $diff) {
                                    throw new \Exception("Stok bahan '{$item->nama_barang}' tidak mencukupi (Tersedia: {$item->stok_tersedia}).");
                                }
                            }
                            $item->stok_total -= $diff;
                            $item->stok_tersedia -= $diff;
                            $item->save();
                        } else {
                            if ($diff > 0) {
                                if ($item->stok_tersedia < $diff) {
                                    throw new \Exception("Stok alat '{$item->nama_barang}' tidak mencukupi (Tersedia: {$item->stok_tersedia}).");
                                }
                            }
                            $item->stok_tersedia -= $diff;
                            if ($item->stok_tersedia === 0) {
                                $item->status = 'dipinjam';
                            } elseif ($item->stok_tersedia > 0 && $item->status === 'dipinjam') {
                                $item->status = 'tersedia';
                            }
                            $item->save();
                        }
                    }
                } else {
                    // Pending status (just update details, no seed stock change)
                    if ($itemChanged) {
                        $detail->item_id = $newItemId;
                    }
                }

                $detail->jumlah_diminta = $newQty;
                $detail->save();
            });

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Transaction item/quantity updated successfully.',
                    'detail' => $detail->fresh()->load('item')
                ]);
            }

            return redirect()->back()->with('success', 'Barang transaksi berhasil diperbarui.');

        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Approve a transaction (laboran).
     */
    public function approve(Request $request, $id)
    {
        $transaction = Transaction::with('details.item')->findOrFail($id);

        if (!in_array($transaction->status, ['pending', 'ditolak', 'ditangguhkan'])) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Transaction with this status cannot be approved.'], 422);
            }
            return redirect()->back()->with('error', 'Transaksi dengan status ini tidak dapat disetujui.');
        }

        try {
            DB::transaction(function () use ($transaction) {
                foreach ($transaction->details as $detail) {
                    $item = $detail->item;
                    $requested = $detail->jumlah_diminta;

                    if ($transaction->tipe === 'permintaan_bahan') {
                        // Check stock sufficiency
                        if ($item->stok_tersedia < $requested || $item->stok_total < $requested) {
                            throw new \Exception("Stok bahan '{$item->nama_barang}' tidak mencukupi (Tersedia: {$item->stok_tersedia}, Diminta: {$requested}).");
                        }

                        // Permanently reduce total and available stock, and update tracking
                        $item->stok_total -= $requested;
                        $item->stok_tersedia -= $requested;
                        $item->bahan_keluar += $requested;
                        $item->tanggal_keluar = $transaction->tanggal_pinjam ? \Carbon\Carbon::parse($transaction->tanggal_pinjam)->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s');
                        $item->save();

                    } elseif ($transaction->tipe === 'peminjaman_alat') {
                        // Check available stock sufficiency
                        if ($item->stok_tersedia < $requested) {
                            throw new \Exception("Stok alat '{$item->nama_barang}' tidak mencukupi untuk dipinjam (Tersedia: {$item->stok_tersedia}, Diminta: {$requested}).");
                        }

                        // Reduce available stock
                        $item->stok_tersedia -= $requested;

                        // Change status to 'dipinjam' if available stock becomes 0
                        if ($item->stok_tersedia === 0) {
                            $item->status = 'dipinjam';
                        }
                        $item->save();
                    }
                }

                // Approve the transaction
                if ($transaction->tipe === 'permintaan_bahan') {
                    $transaction->status = 'selesai';
                } else {
                    $transaction->status = 'disetujui';
                }
                $transaction->laboran_id = auth()->id();
                $transaction->catatan_laboran = null; // Clear rejection/suspension note on approval
                $transaction->save();
            });

            // Automatically generate and upload PDF to Google Drive
            try {
                $transaction->load(['user', 'laboran', 'details.item']);
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.peminjaman', compact('transaction'));
                $pdfContent = $pdf->output();

                $filename = 'TX-' . str_pad($transaction->id, 5, '0', STR_PAD_LEFT) . '_' . $transaction->user->name . '_' . $transaction->tanggal_pengajuan->format('Y-m-d') . '.pdf';

                $driveService = new \App\Services\GoogleDriveService();
                $driveService->uploadTransactionPdf($pdfContent, $filename);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Failed to automatically sync PDF to Google Drive: " . $e->getMessage());
            }

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Transaction approved successfully.',
                    'transaction' => $transaction->fresh()->load('details.item')
                ]);
            }

            return redirect()->back()->with('success', 'Transaksi berhasil disetujui.');

        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Reject a transaction request (laboran).
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'catatan_laboran' => 'required|string',
        ]);

        $transaction = Transaction::findOrFail($id);

        if (!in_array($transaction->status, ['pending', 'ditangguhkan'])) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Only pending or suspended transactions can be rejected.'], 422);
            }
            return redirect()->back()->with('error', 'Transaksi dengan status ini tidak dapat ditolak.');
        }

        $transaction->status = 'ditolak';
        $transaction->catatan_laboran = $request->catatan_laboran;
        $transaction->save();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Transaction rejected successfully.',
                'transaction' => $transaction
            ]);
        }

        return redirect()->back()->with('success', 'Pengajuan transaksi berhasil ditolak.');
    }

    /**
     * Suspend a transaction request (laboran).
     */
    public function suspend(Request $request, $id)
    {
        $request->validate([
            'catatan_laboran' => 'required|string',
        ]);

        $transaction = Transaction::findOrFail($id);

        if (!in_array($transaction->status, ['pending', 'ditolak'])) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Only pending or rejected transactions can be suspended.'], 422);
            }
            return redirect()->back()->with('error', 'Transaksi dengan status ini tidak dapat ditangguhkan.');
        }

        $transaction->status = 'ditangguhkan';
        $transaction->catatan_laboran = $request->catatan_laboran;
        $transaction->save();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Transaction suspended successfully.',
                'transaction' => $transaction
            ]);
        }

        return redirect()->back()->with('success', 'Pengajuan transaksi berhasil ditangguhkan.');
    }

    /**
     * Complete rental and return the tools (mahasiswa or laboran).
     */
    public function returnTransaction(Request $request, $id)
    {
        if (auth()->user()->role !== 'laboran') {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Only laboran can process returns.'], 403);
            }
            return redirect()->back()->with('error', 'Hanya petugas laboran yang dapat memproses pengembalian alat.');
        }

        $request->validate([
            'status_pengembalian' => 'required|in:selesai,belum_selesai',
            'catatan_pengembalian' => 'nullable|string',
        ]);

        $transaction = Transaction::with('details.item')->findOrFail($id);

        if ($transaction->status !== 'disetujui') {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Only approved transactions can be returned.'], 422);
            }
            return redirect()->back()->with('error', 'Hanya transaksi yang telah disetujui yang dapat dikembalikan.');
        }

        if ($transaction->tipe !== 'peminjaman_alat') {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Only tool rentals (peminjaman_alat) can be returned.'], 422);
            }
            return redirect()->back()->with('error', 'Hanya peminjaman alat yang memerlukan pengembalian.');
        }

        try {
            DB::transaction(function () use ($transaction, $request) {
                // If returned fully (selesai), restore the tool stocks
                if ($request->status_pengembalian === 'selesai') {
                    foreach ($transaction->details as $detail) {
                        $item = $detail->item;
                        $returnedQty = $detail->jumlah_diminta;

                        // Restore available stock
                        $item->stok_tersedia += $returnedQty;

                        // If item status was 'dipinjam' and now there is stock available, change status to 'tersedia'
                        if ($item->status === 'dipinjam' && $item->stok_tersedia > 0) {
                            $item->status = 'tersedia';
                        }
                        $item->save();
                    }

                    // Update transaction status to selesai
                    $transaction->status = 'selesai';
                    $transaction->tanggal_kembali_realisasi = now();
                }

                // Store return status details
                $transaction->status_pengembalian = $request->status_pengembalian;
                $transaction->catatan_pengembalian = $request->status_pengembalian === 'selesai' ? null : $request->catatan_pengembalian;
                $transaction->save();
            });

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Return status recorded successfully.',
                    'transaction' => $transaction->fresh()->load('details.item')
                ]);
            }

            return redirect()->back()->with('success', 'Status pengembalian alat berhasil dicatat.');

        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }
            return redirect()->back()->with('error', 'Gagal memproses pengembalian: ' . $e->getMessage());
        }
    }

    /**
     * Batch update or delete transaction details (laboran).
     */
    public function updateBatchDetails(Request $request, $id)
    {
        $transaction = Transaction::with('details.item')->findOrFail($id);

        if (!in_array($transaction->status, ['pending', 'disetujui', 'ditangguhkan'])) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Cannot edit this transaction status.'], 422);
            }
            return redirect()->back()->with('error', 'Transaksi dengan status ini tidak dapat diubah.');
        }

        $request->validate([
            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.jumlah_diminta' => 'required|integer|min:1',
            'items.*.detail_id' => 'nullable|integer',
        ]);

        try {
            DB::transaction(function () use ($request, $transaction) {
                $submittedItems = $request->input('items');
                $submittedDetailIds = collect($submittedItems)->pluck('detail_id')->filter()->toArray();

                // 1. Revert stocks for deleted item details if transaction is already approved
                if ($transaction->status === 'disetujui') {
                    $deletedDetails = $transaction->details->whereNotIn('id', $submittedDetailIds);
                    foreach ($deletedDetails as $delDetail) {
                        $item = $delDetail->item;
                        if ($transaction->tipe === 'permintaan_bahan') {
                            $item->stok_total += $delDetail->jumlah_diminta;
                            $item->stok_tersedia += $delDetail->jumlah_diminta;
                        } else { // peminjaman_alat
                            $item->stok_tersedia += $delDetail->jumlah_diminta;
                            if ($item->stok_tersedia > 0 && $item->status === 'dipinjam') {
                                $item->status = 'tersedia';
                            }
                        }
                        $item->save();
                    }
                }

                // Delete the details database records not in submitted list
                TransactionDetail::where('transaction_id', $transaction->id)
                    ->whereNotIn('id', $submittedDetailIds)
                    ->delete();

                // 2. Process updates and additions
                foreach ($submittedItems as $itemData) {
                    $detailId = $itemData['detail_id'] ?? null;
                    $itemId = (int)$itemData['item_id'];
                    $qty = (int)$itemData['jumlah_diminta'];

                    if ($detailId) {
                        // Existing detail
                        $detail = TransactionDetail::findOrFail($detailId);
                        $oldItemId = $detail->item_id;
                        $oldQty = $detail->jumlah_diminta;
                        $itemChanged = $oldItemId !== $itemId;
                        $diff = $qty - $oldQty;

                        if ($transaction->status === 'disetujui') {
                            if ($itemChanged) {
                                // Revert old item stock
                                $oldItem = Item::findOrFail($oldItemId);
                                if ($transaction->tipe === 'permintaan_bahan') {
                                    $oldItem->stok_total += $oldQty;
                                    $oldItem->stok_tersedia += $oldQty;
                                } else {
                                    $oldItem->stok_tersedia += $oldQty;
                                    if ($oldItem->stok_tersedia > 0 && $oldItem->status === 'dipinjam') {
                                        $oldItem->status = 'tersedia';
                                    }
                                }
                                $oldItem->save();

                                // Deduct new item stock
                                $newItem = Item::findOrFail($itemId);
                                if ($transaction->tipe === 'permintaan_bahan') {
                                    if ($newItem->stok_tersedia < $qty || $newItem->stok_total < $qty) {
                                        throw new \Exception("Stok bahan '{$newItem->nama_barang}' tidak mencukupi.");
                                    }
                                    $newItem->stok_total -= $qty;
                                    $newItem->stok_tersedia -= $qty;
                                } else {
                                    if ($newItem->stok_tersedia < $qty) {
                                        throw new \Exception("Stok alat '{$newItem->nama_barang}' tidak mencukupi.");
                                    }
                                    $newItem->stok_tersedia -= $qty;
                                    if ($newItem->stok_tersedia === 0) {
                                        $newItem->status = 'dipinjam';
                                    }
                                }
                                $newItem->save();
                            } else {
                                // Same item, adjust quantity difference
                                $item = Item::findOrFail($itemId);
                                if ($diff > 0) {
                                    if ($transaction->tipe === 'permintaan_bahan') {
                                        if ($item->stok_tersedia < $diff || $item->stok_total < $diff) {
                                            throw new \Exception("Stok bahan '{$item->nama_barang}' tidak mencukupi.");
                                        }
                                        $item->stok_total -= $diff;
                                        $item->stok_tersedia -= $diff;
                                    } else {
                                        if ($item->stok_tersedia < $diff) {
                                            throw new \Exception("Stok alat '{$item->nama_barang}' tidak mencukupi.");
                                        }
                                        $item->stok_tersedia -= $diff;
                                        if ($item->stok_tersedia === 0) {
                                            $item->status = 'dipinjam';
                                        }
                                    }
                                } elseif ($diff < 0) {
                                    $absDiff = abs($diff);
                                    if ($transaction->tipe === 'permintaan_bahan') {
                                        $item->stok_total += $absDiff;
                                        $item->stok_tersedia += $absDiff;
                                    } else {
                                        $item->stok_tersedia += $absDiff;
                                        if ($item->stok_tersedia > 0 && $item->status === 'dipinjam') {
                                            $item->status = 'tersedia';
                                        }
                                    }
                                }
                                $item->save();
                            }
                        }

                        $detail->item_id = $itemId;
                        $detail->jumlah_diminta = $qty;
                        $detail->save();

                    } else {
                        // New detail item added during edit
                        if ($transaction->status === 'disetujui') {
                            $item = Item::findOrFail($itemId);
                            if ($transaction->tipe === 'permintaan_bahan') {
                                if ($item->stok_tersedia < $qty || $item->stok_total < $qty) {
                                    throw new \Exception("Stok bahan '{$item->nama_barang}' tidak mencukupi.");
                                }
                                $item->stok_total -= $qty;
                                $item->stok_tersedia -= $qty;
                            } else {
                                if ($item->stok_tersedia < $qty) {
                                    throw new \Exception("Stok alat '{$item->nama_barang}' tidak mencukupi.");
                                }
                                $item->stok_tersedia -= $qty;
                                if ($item->stok_tersedia === 0) {
                                    $item->status = 'dipinjam';
                                }
                            }
                            $item->save();
                        }

                        TransactionDetail::create([
                            'transaction_id' => $transaction->id,
                            'item_id' => $itemId,
                            'jumlah_diminta' => $qty,
                        ]);
                    }
                }
            });

            return redirect()->back()->with('success', 'Perubahan permohonan berhasil disimpan.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Download or stream transaction receipt as PDF with laboran QR digital signature.
     */
    public function downloadPdf(\Illuminate\Http\Request $request, $id)
    {
        $transaction = Transaction::with(['user', 'laboran', 'details.item'])->findOrFail($id);

        if (!in_array($transaction->status, ['disetujui', 'selesai', 'ditolak', 'ditangguhkan'])) {
            return redirect()->back()->with('error', 'Formulir hanya dapat diunduh untuk pengajuan yang telah diverifikasi atau diproses.');
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.peminjaman', compact('transaction'));
        $borrowerName = $transaction->is_insidentil ? $transaction->peminjam_insidentil : ($transaction->user ? $transaction->user->name : 'Peminjam');
        $filename = 'TX-' . str_pad($transaction->id, 5, '0', STR_PAD_LEFT) . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $borrowerName) . '_' . $transaction->tanggal_pengajuan->format('Y-m-d') . '.pdf';
        
        if ($request->query('action') === 'preview') {
            return $pdf->stream($filename);
        }
        return $pdf->download($filename);
    }

    /**
     * Public route to verify transaction digital QR signature validity.
     */
    public function verifyQr($id)
    {
        $transaction = Transaction::with(['user', 'laboran', 'details.item'])->findOrFail($id);
        return view('transactions.verify-qr', compact('transaction'));
    }

    /**
     * Edit full transaction: Date/Times, items, packages, and quantities.
     */
    public function updateTransaction(Request $request, $id)
    {
        $transaction = \App\Models\Transaction::with('details.item', 'details.package')->findOrFail($id);

        if (!in_array($transaction->status, ['pending', 'disetujui', 'ditangguhkan'])) {
            return redirect()->back()->with('error', 'Transaksi dengan status ini tidak dapat diubah.');
        }

        $request->validate([
            'tanggal_pinjam' => 'required|date',
            'tanggal_kembali_rencana' => 'required_if:tipe,peminjaman_alat|nullable|date|after_or_equal:tanggal_pinjam',
            'penanggung_jawab' => 'required|string|max:255',
            'kegiatan' => 'required|string|max:255',
            'items' => 'nullable|array',
            'items.*.item_id' => 'nullable|exists:items,id',
            'items.*.jumlah_diminta' => 'nullable|integer|min:1',
            'items.*.package_id' => 'nullable|exists:packages,id',
            'items.*.package_qty' => 'nullable|integer|min:1',
        ]);

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($request, $transaction) {
                // Update basic info
                $transaction->tanggal_pinjam = $request->tanggal_pinjam;
                if ($transaction->tipe === 'peminjaman_alat') {
                    $transaction->tanggal_kembali_rencana = $request->tanggal_kembali_rencana;
                }
                $transaction->penanggung_jawab = $request->penanggung_jawab;
                $transaction->kegiatan = $request->kegiatan;
                $transaction->save();

                $submittedItems = $request->input('items', []);

                // If status is approved, we need to revert old stocks and then deduct new stocks
                if ($transaction->status === 'disetujui') {
                    foreach ($transaction->details as $oldDetail) {
                        if ($oldDetail->item_id) {
                            $item = \App\Models\Item::lockForUpdate()->findOrFail($oldDetail->item_id);
                            if ($transaction->tipe === 'permintaan_bahan') {
                                $item->stok_total += $oldDetail->jumlah_diminta;
                                $item->stok_tersedia += $oldDetail->jumlah_diminta;
                            } else {
                                $item->stok_tersedia += $oldDetail->jumlah_diminta;
                                if ($item->stok_tersedia > 0 && $item->status === 'dipinjam') {
                                    $item->status = 'tersedia';
                                }
                            }
                            $item->save();
                        } elseif ($oldDetail->package_id) {
                            $package = \App\Models\Package::with('items.item')->findOrFail($oldDetail->package_id);
                            foreach ($package->items as $pi) {
                                $item = \App\Models\Item::lockForUpdate()->findOrFail($pi->item_id);
                                $qtyToReturn = $pi->jumlah * $oldDetail->jumlah_diminta;
                                if ($transaction->tipe === 'permintaan_bahan') {
                                    $item->stok_total += $qtyToReturn;
                                    $item->stok_tersedia += $qtyToReturn;
                                } else {
                                    $item->stok_tersedia += $qtyToReturn;
                                    if ($item->stok_tersedia > 0 && $item->status === 'dipinjam') {
                                        $item->status = 'tersedia';
                                    }
                                }
                                $item->save();
                            }
                        }
                    }
                }

                // Delete old details
                $transaction->details()->delete();

                // Process new items and packages
                foreach ($submittedItems as $itemData) {
                    if (!empty($itemData['item_id'])) {
                        $itemId = (int)$itemData['item_id'];
                        $qty = (int)$itemData['jumlah_diminta'];

                        if ($transaction->status === 'disetujui') {
                            $newItem = \App\Models\Item::lockForUpdate()->findOrFail($itemId);
                            if ($transaction->tipe === 'permintaan_bahan') {
                                if ($newItem->stok_tersedia < $qty || $newItem->stok_total < $qty) {
                                    throw new \Exception("Maaf Jumlah Permintaan Tidak Tersedia");
                                }
                                $newItem->stok_total -= $qty;
                                $newItem->stok_tersedia -= $qty;
                                $newItem->bahan_keluar += $qty;
                                $newItem->tanggal_keluar = $transaction->tanggal_pinjam ? \Carbon\Carbon::parse($transaction->tanggal_pinjam)->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s');
                            } else {
                                if ($newItem->stok_tersedia < $qty) {
                                    throw new \Exception("Maaf Jumlah Permintaan Tidak Tersedia");
                                }
                                $newItem->stok_tersedia -= $qty;
                                if ($newItem->stok_tersedia === 0) {
                                    $newItem->status = 'dipinjam';
                                }
                            }
                            $newItem->save();
                        }
                        $transaction->details()->create([
                            'item_id' => $itemId,
                            'jumlah_diminta' => $qty,
                        ]);
                    } elseif (!empty($itemData['package_id'])) {
                        $packageId = (int)$itemData['package_id'];
                        $qty = (int)$itemData['package_qty'];

                        if ($transaction->status === 'disetujui') {
                            $package = \App\Models\Package::with('items.item')->findOrFail($packageId);
                            foreach ($package->items as $pi) {
                                $itemToDeduct = \App\Models\Item::lockForUpdate()->findOrFail($pi->item_id);
                                $qtyToDeduct = $pi->jumlah * $qty;
                                if ($transaction->tipe === 'permintaan_bahan') {
                                    if ($itemToDeduct->stok_tersedia < $qtyToDeduct || $itemToDeduct->stok_total < $qtyToDeduct) {
                                        throw new \Exception("Maaf Jumlah Permintaan Tidak Tersedia");
                                    }
                                    $itemToDeduct->stok_total -= $qtyToDeduct;
                                    $itemToDeduct->stok_tersedia -= $qtyToDeduct;
                                    $itemToDeduct->bahan_keluar += $qtyToDeduct;
                                    $itemToDeduct->tanggal_keluar = $transaction->tanggal_pinjam ? \Carbon\Carbon::parse($transaction->tanggal_pinjam)->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s');
                                } else {
                                    if ($itemToDeduct->stok_tersedia < $qtyToDeduct) {
                                        throw new \Exception("Maaf Jumlah Permintaan Tidak Tersedia");
                                    }
                                    $itemToDeduct->stok_tersedia -= $qtyToDeduct;
                                    if ($itemToDeduct->stok_tersedia === 0) {
                                        $itemToDeduct->status = 'dipinjam';
                                    }
                                }
                                $itemToDeduct->save();
                            }
                        }
                        $transaction->details()->create([
                            'package_id' => $packageId,
                            'jumlah_diminta' => $qty,
                        ]);
                    }
                }
            });

            return redirect()->back()->with('success', 'Transaksi berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
