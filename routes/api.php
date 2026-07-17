<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

use App\Http\Controllers\TransactionController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\PackageController;

Route::post('/sheets-webhook', [ItemController::class, 'handleSheetsWebhook']);
Route::get('/packages/{id}', [PackageController::class, 'showApi']);

Route::prefix('transactions')->group(function () {
    Route::post('/', [TransactionController::class, 'store']);
    Route::get('/pending', [TransactionController::class, 'pendingList']);
    Route::put('/details/{detailId}/quantity', [TransactionController::class, 'updateDetailQuantity']);
    Route::post('/{id}/approve', [TransactionController::class, 'approve']);
    Route::post('/{id}/reject', [TransactionController::class, 'reject']);
    Route::post('/{id}/return', [TransactionController::class, 'returnTransaction']);
});
