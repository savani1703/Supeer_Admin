<?php

use App\Http\Controllers\GateWayController;
use Illuminate\Support\Facades\Route;


/*
 * PG PayIn/Payout Meta
 */

Route::get('/payment-gateway/{pgType}/{pgName}', [GateWayController::class,"renderMetaView"]);
Route::post('/payment-gateway/GetPaymentMeta/{pgType}/{pgName}', [GateWayController::class,"getPaymentMeta"]);
Route::post('/payment-gateway/AddPaymentMeta/{pgType}/{pgName}', [GateWayController::class,"addPaymentMeta"]);
Route::post('/payment-gateway/UpdatePaymentMetaStatus/{pgType}/{pgName}', [GateWayController::class,"updatePaymentMetaStatus"]);
Route::post('/payment-gateway/UpdateMetaAutoLoginStatus/{pgType}/{pgName}', [GateWayController::class,"updateMetaAutoLoginStatus"]);
Route::post('/payment-gateway/UpdatePaymentMetaMinLimit/{pgType}/{pgName}', [GateWayController::class,"updatePaymentMetaMinLimit"]);
Route::post('/payment-gateway/UpdatePaymentMetaMaxLimit/{pgType}/{pgName}', [GateWayController::class,"updatePaymentMetaMaxLimit"]);
Route::post('/payment-gateway/UpdatePaymentMetaMaxCountLimit/{pgType}/{pgName}', [GateWayController::class,"updatePaymentMetaMaxCountLimit"]);
Route::post('/payment-gateway/UpdatePaymentMetaTurnOver/{pgType}/{pgName}', [GateWayController::class,"updatePaymentMetaTurnOver"]);
Route::post('/payment-gateway/UpdatePaymentMetaMethod/{pgType}/{pgName}', [GateWayController::class,"updatePaymentMetaMethod"]);
Route::post('/payment-gateway/UpdateMetaProductInfo/{pgType}/{pgName}', [GateWayController::class,"updateMetaProductInfo"]);
Route::post('/payment-gateway/GetPaymentMetaLabelList/{pgType}/{pgName}', [GateWayController::class,"getPaymentMetaLabelList"]);
Route::post('/payment-gateway/TestPaymentAccount', [GateWayController::class,"testPaymentAccount"]);
