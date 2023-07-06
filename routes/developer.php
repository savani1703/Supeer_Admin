<?php

use App\Http\Controllers\DeveloperController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', [DeveloperController::class, "renderDashboardView"]);
Route::get('/', [DeveloperController::class, "renderDashboardView"]);

Route::get('/payin/summary', [DeveloperController::class, "renderPayInSummaryView"]);
Route::post('/dashboard/GetPgPayInSummary', [DeveloperController::class, "getPgPayInSummary"]);

Route::get('/pg-routers', function () { return view("developer.dashboard.pg-routers"); });
Route::post('/dashboard/GetPgRouters', [DeveloperController::class, "getPgRouters"]);

Route::get('/proxy', function () { return view("developer.dashboard.proxy"); });
Route::post('/dashboard/GetBankProxyList', [DeveloperController::class, "getBankProxyList"]);
Route::post('/dashboard/AddProxy', [DeveloperController::class, "addProxy"]);

Route::get('/bank-proxy', function () { return view("developer.dashboard.bank-proxy"); });
Route::post('/dashboard/GetProxyList', [DeveloperController::class, "getProxyList"]);
Route::post('/dashboard/AddBankProxy', [DeveloperController::class, "addBankProxy"]);
Route::post('/dashboard/DeleteByIdBankProxy', [DeveloperController::class, "bankProxyDelete"]);
Route::post('/dashboard/EditBankProxyStatus', [DeveloperController::class, "editBankProxyStatus"]);


Route::get('/bouncer', function () { return view("developer.dashboard.bouncer"); });
Route::get('/bouncer', [DeveloperController::class, "renderBouncerView"]);
Route::post('/dashboard/GetBouncerData', [DeveloperController::class, "getBouncerData"]);

Route::get('/payout-bank-down', function () { return view("developer.dashboard.payout-bank-down"); });
Route::post('/dashboard/GetPayoutDownBanks', [DeveloperController::class, "getPayoutDownBanks"]);
Route::post('/dashboard/DeleteByIdPayoutDownBank', [DeveloperController::class, "deleteByIdPayoutDownBank"]);
Route::post('/dashboard/DeletePayoutDownBank', [DeveloperController::class, "deletePayoutDownBank"]);

Route::get('/sms-logs', function () { return view("developer.dashboard.sms-logs"); });
Route::post('/dashboard/getSMSLogs', [DeveloperController::class, "getSMSLogs"]);

Route::get('/mail-reader', function () { return view("developer.dashboard.mail-reader"); });
Route::post('/dashboard/GetMailReader', [DeveloperController::class, "getMailReader"]);
Route::post('/dashboard/UpdateMailReaderStatus', [DeveloperController::class, "updateMailReaderStatus"]);
Route::post('/dashboard/AddMailReader', [DeveloperController::class, "addMailReader"]);


Route::get('/payout-whitelist-client', function () { return view("developer.dashboard.payout-whitelist-client"); });
Route::post('/dashboard/GetPayoutWhiteListClient', [DeveloperController::class, "getPayoutWhiteListClient"]);
Route::post('/dashboard/UpdatePayoutWhiteListClientStatus', [DeveloperController::class, "updatePayoutWhiteListClientStatus"]);
Route::post('/dashboard/UpdatePayoutWhiteListClientStatus/manual', [DeveloperController::class, "updatePayoutWhiteListClientStatusManual"]);
Route::post('/dashboard/AddPayoutWhiteListClient', [DeveloperController::class, "addPayoutWhiteListClient"]);
Route::post('/dashboard/editPayoutWhiteListClientLimit', [DeveloperController::class, "editClientWhiteListLimit"]);

Route::get('/idfc-mail-webhook', function () { return view("developer.dashboard.idfc-mail-webhook"); });
Route::post('/dashboard/getidfcwebhook', [DeveloperController::class, "getIdfcWebhook"]);

Route::get('/swift-customer', [DeveloperController::class, "renderSwiftCustView"]);


