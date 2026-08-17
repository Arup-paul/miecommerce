<?php

use App\Http\Controllers\Api\MiAccountsReceiverController;
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

Route::middleware('miaccounts.secret')->prefix('miaccounts')->group(function () {
    Route::get('pending-products', [MiAccountsReceiverController::class, 'pendingProducts']);
    Route::post('products', [MiAccountsReceiverController::class, 'products']);
    Route::post('order-status', [MiAccountsReceiverController::class, 'orderStatus']);
    Route::get('pending-orders', [MiAccountsReceiverController::class, 'pendingOrders']);
    Route::post('orders/claim', [MiAccountsReceiverController::class, 'claimOrder']);
});
