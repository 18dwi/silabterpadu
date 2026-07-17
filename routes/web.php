<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\BebasLabController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/transactions/{id}/verify-qr', [TransactionController::class, 'verifyQr'])->name('transactions.verify-qr');
Route::get('/bebas-lab/{id}/verify-qr', [BebasLabController::class, 'verifyQr'])->name('bebas-lab.verify-qr');
Route::get('/room-bookings/verify/{token}', [\App\Http\Controllers\RoomBookingController::class, 'verifyQr'])->name('room-bookings.verify-qr');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Bebas Laboratorium
    Route::post('/bebas-lab', [BebasLabController::class, 'store'])->name('bebas-lab.store');
    Route::delete('/bebas-lab/{id}', [BebasLabController::class, 'destroy'])->name('bebas-lab.destroy');
    Route::get('/bebas-lab/{id}/pdf', [BebasLabController::class, 'downloadPdf'])->name('bebas-lab.pdf');

    // Packages CRUD (Laboran)
    Route::post('/packages', [PackageController::class, 'store'])->name('packages.store');
    Route::put('/packages/{id}', [PackageController::class, 'update'])->name('packages.update');
    Route::delete('/packages/{id}', [PackageController::class, 'destroy'])->name('packages.destroy');

    // Item CRUD (Laboran)
    Route::post('/items', [ItemController::class, 'store'])->name('items.store');
    Route::put('/items/{id}', [ItemController::class, 'update'])->name('items.update');
    Route::delete('/items/{id}', [ItemController::class, 'destroy'])->name('items.destroy');
    Route::patch('/items/{id}/status', [ItemController::class, 'updateStatus'])->name('items.update-status');
    Route::post('/items/import', [ItemController::class, 'importGoogleSheet'])->name('items.import');
    Route::get('/items/{id}/stock-card', [ItemController::class, 'stockCard'])->name('items.stock-card');
    Route::post('/items/{id}/update-stock-card', [ItemController::class, 'updateStockCard'])->name('items.update-stock-card');

    // Transactions Web Flow
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
    Route::put('/transactions/details/{detailId}', [TransactionController::class, 'updateDetailQuantity'])->name('transactions.update-detail');
    Route::put('/transactions/{id}', [TransactionController::class, 'updateTransaction'])->name('transactions.update');
    Route::post('/transactions/{id}/update-details', [TransactionController::class, 'updateBatchDetails'])->name('transactions.update-batch');
    Route::post('/transactions/{id}/approve', [TransactionController::class, 'approve'])->name('transactions.approve');
    Route::post('/transactions/{id}/reject', [TransactionController::class, 'reject'])->name('transactions.reject');
    Route::post('/transactions/{id}/suspend', [TransactionController::class, 'suspend'])->name('transactions.suspend');
    Route::post('/transactions/{id}/return', [TransactionController::class, 'returnTransaction'])->name('transactions.return');
    Route::get('/transactions/{id}/pdf', [TransactionController::class, 'downloadPdf'])->name('transactions.pdf');

    // Inventory Recap Exports (Laboran)
    Route::get('/laboran/inventory/{kategori}/export-excel', [DashboardController::class, 'exportInventoryExcel'])->name('laboran.inventory.export-excel');
    Route::get('/laboran/inventory/{kategori}/print-pdf', [DashboardController::class, 'printInventoryReport'])->name('laboran.inventory.print-pdf');

    // Superadmin Routing Flow
    Route::get('/superadmin/report/print', [DashboardController::class, 'printReport'])->name('superadmin.report.print');
    Route::get('/superadmin/report/export-csv', [DashboardController::class, 'exportCsv'])->name('superadmin.report.export-csv');
    Route::get('/superadmin/report/print-materials', [DashboardController::class, 'printMaterialsReport'])->name('superadmin.report.print-materials');
    Route::get('/superadmin/report/export-room-pdf', [DashboardController::class, 'exportRoomPdf'])->name('superadmin.report.export-room-pdf');
    Route::get('/superadmin/report/export-room-csv', [DashboardController::class, 'exportRoomCsv'])->name('superadmin.report.export-room-csv');
    Route::get('/superadmin/report/export-materials', [DashboardController::class, 'exportMaterialsExcel'])->name('superadmin.report.export-materials');
    Route::post('/superadmin/users', [\App\Http\Controllers\SuperadminController::class, 'storeUser'])->name('superadmin.users.store');
    Route::put('/superadmin/users/{id}', [\App\Http\Controllers\SuperadminController::class, 'updateUser'])->name('superadmin.users.update');
    Route::delete('/superadmin/users/{id}', [\App\Http\Controllers\SuperadminController::class, 'destroyUser'])->name('superadmin.users.destroy');
    Route::post('/superadmin/users/import', [\App\Http\Controllers\SuperadminController::class, 'importUsersSheet'])->name('superadmin.users.import');
    Route::delete('/superadmin/transactions/{id}', [\App\Http\Controllers\SuperadminController::class, 'destroyTransaction'])->name('superadmin.transactions.destroy');
    Route::delete('/superadmin/room-bookings/{id}', [\App\Http\Controllers\SuperadminController::class, 'destroyRoomBooking'])->name('superadmin.room-bookings.destroy');
    Route::post('/superadmin/impersonate/{id}', [\App\Http\Controllers\SuperadminController::class, 'impersonate'])->name('superadmin.impersonate');
    Route::post('/impersonate/leave', [\App\Http\Controllers\SuperadminController::class, 'leaveImpersonate'])->name('impersonate.leave');

    // ── Room Master (Laboran) ──────────────────────────────────────────────────
    Route::post('/rooms', [\App\Http\Controllers\RoomController::class, 'store'])->name('rooms.store');
    Route::put('/rooms/{id}', [\App\Http\Controllers\RoomController::class, 'update'])->name('rooms.update');
    Route::delete('/rooms/{id}', [\App\Http\Controllers\RoomController::class, 'destroy'])->name('rooms.destroy');

    // ── Room Bookings (Mahasiswa) ─────────────────────────────────────────────
    Route::get('/room-bookings', [\App\Http\Controllers\RoomBookingController::class, 'index'])->name('room-bookings.index');
    Route::post('/room-bookings', [\App\Http\Controllers\RoomBookingController::class, 'store'])->name('room-bookings.store');
    Route::delete('/room-bookings/{id}', [\App\Http\Controllers\RoomBookingController::class, 'destroyBooking'])->name('room-bookings.destroy');
    Route::get('/room-bookings/check-availability', [\App\Http\Controllers\RoomBookingController::class, 'checkAvailability'])->name('room-bookings.check');
    Route::get('/room-bookings/{id}/pdf', [\App\Http\Controllers\RoomBookingController::class, 'printPdf'])->name('room-bookings.pdf');

    // ── Room Bookings (Laboran Management) ───────────────────────────────────
    Route::get('/laboran/room-bookings', [\App\Http\Controllers\RoomBookingController::class, 'laboranIndex'])->name('laboran.room-bookings.index');
    Route::post('/laboran/room-bookings/{id}/approve', [\App\Http\Controllers\RoomBookingController::class, 'approve'])->name('room-bookings.approve');
    Route::post('/laboran/room-bookings/{id}/reject', [\App\Http\Controllers\RoomBookingController::class, 'reject'])->name('room-bookings.reject');
    Route::put('/laboran/room-bookings/{id}', [\App\Http\Controllers\RoomBookingController::class, 'update'])->name('room-bookings.update');
    Route::delete('/laboran/room-bookings/{id}', [\App\Http\Controllers\RoomBookingController::class, 'laboranDestroy'])->name('laboran.room-bookings.destroy');
});

require __DIR__.'/auth.php';
