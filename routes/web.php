<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BankConfigController;
use App\Http\Controllers\BankStatementController;
use App\Http\Controllers\BankTransactionsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeveloperController;
use App\Http\Controllers\LateSuccessController;
use App\Http\Controllers\ManualPayoutController;
use App\Http\Controllers\MerchantController;
use App\Http\Controllers\MerchantMetaController;
use App\Http\Controllers\PayoutBankStatementController;
use App\Http\Controllers\PayoutController;
use App\Http\Controllers\ReconciliationController;
use App\Http\Controllers\RefundController;
use App\Http\Controllers\SmsReaderController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\TxnController;
use App\Plugin\AccessControl\Utils\AccessControlUtils;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/



//auth
Route::post('/authentication',[AuthController::class, 'authenticate']);
Route::get('/',[AuthController::class, 'dashboard']);
Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::get('/register', [AuthController::class, 'register'])->name('register');
Route::get('/logout',[AuthController::class, 'logout']);

Route::group(['middleware' => ['auth', 'access']], function(){

    /*
     * DASHBOARD
     */
    Route::get('/dashboard', [DashboardController::class, "renderMMDashboard"]);
    Route::get('/dashboard-1', function () { return view("dashboard.dashboard"); });
    Route::post('/dashboard/GetMMDashboardSummary', [DashboardController::class, "getMMDashboardSummary"]);
    Route::post('/dashboard/GetMMTransactionSummary', [DashboardController::class, "GetMMTransactionSummary"]);
    Route::post('/dashboard/GetMMPayoutSummary', [DashboardController::class, "GetMMPayoutSummary"]);
    Route::post('/dashboard/GetMMDashboardData', [DashboardController::class, "getMMDashboardData"]);
    Route::post('/dashboard/balance/GetMMDashboardData', [DashboardController::class, "getMMDashboardBalanceData"]);
    Route::post('/dashboard/GetPgSummary', [DashboardController::class, "getPgSummary"]);
    Route::post('/dashboard/payoutCRData', [DashboardController::class, "getPayoutCrData"]);

    Route::get('/pg-management-dashboard', [DashboardController::class, "getPgManagementDashboardView"]);
    Route::post('/pg-management-dashboard/GetSummary', [DashboardController::class, "getPgManagementDashboardSummary"]);
    Route::post('/pg-management-dashboard/GetPgList', [DashboardController::class, "getPgManagementDashboardGetPgList"]);


    /*
     * TRANSACTION: Access Done
     */
    Route::get('/transaction', [TxnController::class, "txnView"]);
    Route::get('/custById/{custid}', [TxnController::class, "custIdView"]);
    Route::post('/transaction',[TxnController::class, "getTransactions"]);
    Route::post('/transaction/summary',[TxnController::class, "getTransactionSummary"]);
    Route::post('/transaction/byId',[TxnController::class, "getTransactionById"]);
    Route::post('/transaction/byBrowserId',[TxnController::class, "getTransactionByBrowserId"]);
    Route::post('/transaction/byUtr',[TxnController::class, "getTransactionByUTR"]);
    Route::post('/transaction/resend/webhook',[TxnController::class, "resendTransactionWebhook"]);
    Route::post('/transaction/update/tempUtr',[TxnController::class, "updateTransactionTempUtr"]);
    Route::post('/transaction/update/setUtrPaymentRef',[TxnController::class, "setUtrPaymentRef"]);
    Route::post('/transaction/update/setUtrTransaction_id',[TxnController::class, "setUtrTransaction_id"]);
    Route::post('/transaction/block/customer',[TxnController::class, "blockCustomerDetails"]);
    Route::post('/transaction/block/customer/byHId',[TxnController::class, "blockCustomerAllDetails"]);
    Route::post('/transaction/refund',[TxnController::class, "transactionRefund"]);
    Route::post('/transaction/set/utr',[TxnController::class, "transactionSetUTR"]);
    Route::post('/transaction/delete/manual',[TxnController::class, "transactionDeleteManualUTR"]);
    Route::post('/transaction/removefees/manual',[TxnController::class, "transactionRemoveFees"]);

    /*
     * PAYOUT: Access Done
     */
    Route::get('/payout',[PayoutController::class,"renderPayout"]);
    Route::post('/payout/summery',[PayoutController::class,"getSummery"]);
    Route::post('/payout',[PayoutController::class,"getPayout"]);
    Route::post('/payout/resend-webhook',[PayoutController::class,"resendWebhook"]);
    Route::post('/payout/byId',[PayoutController::class,"getPayoutById"]);
    Route::post('/payout/CancelledInitializedPayout',[PayoutController::class,"cancelledInitializedPayout"]);
    Route::post('/payout/ResetInitializedPayout',[PayoutController::class,"ResetInitializedPayout"]);
    Route::post('/payout/GetPayoutConfiguration',[PayoutController::class,"getPayoutConfiguration"]);
    Route::post('/payout/UpdatePayoutConfiguration',[PayoutController::class,"updatePayoutConfiguration"]);
    Route::post('/payout/ResetLowBalPayoutToInitialize',[PayoutController::class,"resetLowBalPayoutToInitialize"]);
    Route::post('/payout/get/pgMeta',[PayoutController::class,"getPayoutPgMeta"]);
    Route::post('/payout/status/update',[PayoutController::class,"payoutStatusUpdate"]);


    Route::get('/payout-cust-level', function () { return view("payout-customer-level"); });
    Route::post('/Payout/GetCustomerLevelData', [PayoutController::class, "getCustLevelData"]);
    Route::post('/get/payout/account/load', [PayoutController::class, "getPayoutAccountLoad"]);



    /*
     * Refund
     */
    Route::get('/refund', [RefundController::class, "renderRefund"]);
    Route::post('/refund', [RefundController::class, "getRefund"]);

    /*
     * MERCHANT: Access Done
     */
    Route::get('/payin-meta/{merchant_id}', [MerchantController::class, "renderPayInView"]);
    Route::get('/payout-meta/{merchant_id}', [MerchantController::class, "renderPayoutView"]);
    Route::get('/merchant',[MerchantController::class, "renderView"]);
    Route::post('/merchant',[MerchantController::class, "getMerchants"]);
    Route::post('/merchant/add',[MerchantController::class, "addMerchant"]);
    Route::post('/merchant/action/UpdateIsPayoutEnable',[MerchantController::class, "updateIsPayoutEnable"]);
    Route::post('/merchant/action/UpdateIsPayInEnable',[MerchantController::class, "updateIsPayInEnable"]);
    Route::post('/merchant/action/UpdateAccountStatus',[MerchantController::class, "updateAccountStatus"]);
    Route::post('/merchant/action/UpdateIsFailedWebhookRequired',[MerchantController::class, "updateIsFailedWebhookRequired"]);
    Route::post('/merchant/action/UpdateIsEnableBrowserCheck',[MerchantController::class, "updateIsEnableBrowserCheck"]);
    Route::post('/merchant/action/UpdateIsEnablePayoutBalanceCheck',[MerchantController::class, "updateIsEnablePayoutBalanceCheck"]);
    Route::post('/merchant/action/UpdatePayInWebhook',[MerchantController::class, "updatePayInWebhook"]);
    Route::post('/merchant/action/UpdatePayoutWebhook',[MerchantController::class, "updatePayoutWebhook"]);
    Route::post('/merchant/action/UpdateIsDashboardPayoutEnable',[MerchantController::class, "updateIsDashboardPayoutEnable"]);
    Route::post('/merchant/action/UpdatePayInAutoFees',[MerchantController::class, "updatePayInAutoFees"]);
    Route::post('/merchant/action/UpdatePayInManualFees',[MerchantController::class, "updatePayInManualFees"]);
    Route::post('/merchant/action/UpdatePayoutFees',[MerchantController::class, "updatePayoutFees"]);
    Route::post('/merchant/action/UpdatePayoutAssociateFees',[MerchantController::class, "updatePayoutAssociateFees"]);
    Route::post('/merchant/action/UpdatePayInAssociateFees',[MerchantController::class, "updatePayInAssociateFees"]);
    Route::post('/merchant/action/UpdateSettlementCycle',[MerchantController::class, "updateSettlementCycle"]);
    Route::post('/merchant/action/UpdatePayoutDelayedTime',[MerchantController::class, "updatePayoutDelayedTime"]);
    Route::post('/merchant/action/UpdateIsAutoApprovedPayout',[MerchantController::class, "updateIsAutoApprovedPayout"]);
    Route::post('/merchant/action/UpdateShowCustomerDetailsPage',[MerchantController::class, "updateShowCustomerDetailsPage"]);
    Route::post('/merchant/action/UpdateIsCustomerDetailsRequired',[MerchantController::class, "updateIsCustomerDetailsRequired"]);
    Route::post('/merchant/action/UpdateIsSettlementEnable',[MerchantController::class, "updateIsSettlementEnable"]);
    Route::post('/merchant/action/UpdateOldUsersDays',[MerchantController::class, "updateOldUsersDays"]);
    Route::post('/merchant/action/UpdateCheckoutColor',[MerchantController::class, "updateCheckoutColor"]);
    Route::post('/merchant/action/UpdateCheckoutThemeUrl',[MerchantController::class, "updateCheckoutThemeUrl"]);
    Route::post('/merchant/action/updateMinLimit',[MerchantController::class, "updateMinLimit"]);
    Route::post('/merchant/action/updateMaxLimit',[MerchantController::class, "updateMaxLimit"]);
    Route::post('/merchant/action/ResetMerchantAccountPassword',[MerchantController::class, "resetMerchantAccountPassword"]);
    Route::post('/merchant/ViewDashboardLogs',[MerchantController::class, "viewDashboardLogs"]);
    Route::post('/merchant/ViewStatement',[MerchantController::class, "viewStatement"]);
    Route::post('/merchant/ViewMerchantWhitelistIps',[MerchantController::class, "viewMerchantWhitelistIps"]);
    Route::post('/merchant/AddManualPayout',[MerchantController::class, "addManualPayout"]);
    Route::post('/merchant/AddManualPayIn',[MerchantController::class, "addManualPayIn"]); // new
    Route::post('/merchant/GetPendingSettlement',[MerchantController::class, "GetPendingSettlement"]); // new
    Route::post('/merchant/AddMerchantSettlement',[MerchantController::class, "AddMerchantSettlement"]); // new

    Route::post('/merchant/GetPayInMeta',[MerchantMetaController::class, "getPayInMeta"]);
    Route::post('/merchant/UpdatePayInMetaStatus',[MerchantMetaController::class, "updatePayInMetaStatus"]);
    Route::post('/merchant/UpdatePayInMetaMinLimit',[MerchantMetaController::class, "updatePayInMetaMinLimit"]);

    Route::post('/merchant/UpdatePayInMetaMaxLimit',[MerchantMetaController::class, "updatePayInMetaMaxLimit"]);
    Route::post('/merchant/UpdatePayInMetaDailyLimit',[MerchantMetaController::class, "updatePayInMetaDailyLimit"]);
    Route::post('/merchant/UpdatePayInMetaPerLimit',[MerchantMetaController::class, "UpdatePayInMetaPerLimit"]);
    Route::post('/merchant/UpdatePayInMetaLevel',[MerchantMetaController::class, "updatePayInMetaLevel"]);
    Route::post('/merchant/DeletePayInMeta',[MerchantMetaController::class, "deletePayInMeta"]);
    Route::post('/merchant/GetPayoutMeta',[MerchantMetaController::class, "getPayoutMeta"]);
    Route::post('/merchant/UpdatePayoutMetaStatus', [MerchantMetaController::class, "updatePayoutMetaStatus"]);

    /*
     * BANK TRANSACTION: Access Done
     */
    Route::get('/bank-transaction',function () { return view("bank-transaction"); });
    Route::post('/bank-transaction',[BankTransactionsController::class, "getBankTransaction"]);
    Route::post('/bank/MarkAsUsed',[BankTransactionsController::class, "MarkAsUsed"]);
    Route::post('/bank-transaction/mergeUtr',[BankTransactionsController::class, "mergeUtr"]);
    Route::post('/add/bank-transaction',[BankTransactionsController::class, "addBankTransaction"]);
    Route::post('/get/available-bank',[BankTransactionsController::class, "getAvailableBank"]);
    Route::post('/bank-transaction/UpdateMobileNumber',[BankTransactionsController::class, "UpdateMobileNumber"]);

    /*
     * PG META
     */
    Route::post('/meta/GetAvailablePaymentMeta', [MerchantMetaController::class, "getAvailablePaymentMeta"]);
    Route::post('/meta/GetAvailablePayoutMeta', [MerchantMetaController::class, "getAvailablePayoutMeta"]);
    Route::post('/meta/AddPayInMetaToMerchantCollection', [MerchantMetaController::class, "addPayInMetaToMerchantCollection"]);
    Route::post('/meta/AddPayoutMetaToMerchantWithdrawal', [MerchantMetaController::class, "addPayoutMetaToMerchantWithdrawal"]);

    Route::post('/meta/update/merchant/all', [MerchantMetaController::class, "updateMerchantAllMeta"]);

    /*
     * Reconciliation: Access Done
     */
    Route::get('/reconciliation', [ReconciliationController::class, "renderReconView"]);
    Route::get('/utrreconciliation', [ReconciliationController::class, "UtrRecon"]);
    Route::post('/GetUtrReconciliation', [ReconciliationController::class, "GetUtrReconciliation"]);
    Route::post('/SetUtrReconciliation', [ReconciliationController::class, "SetUtrReconciliation"]);
    Route::post('/GetUtrReconciliationReport', [ReconciliationController::class, "GetUtrReconciliationReport"]);
    Route::post('/payment/ReconciliationPayment', [ReconciliationController::class, "reconciliationPayment"]);
    Route::post('/payment/ReconciliationPaymentAction', [ReconciliationController::class, "reconciliationPaymentAction"]);
    Route::post('/empty/bank', [ReconciliationController::class, 'emptyBulkPeBal']);


    /*|--------------------------------------------------------------------------
                          | SUPPORT SYSTEM |
    |--------------------------------------------------------------------------*/

      /* Support Logs*/
    Route::get('/support-logs', function () { return view("support-logs"); });
    Route::post('/support/GetSupportLogs', [SupportController::class, "getSupportLogs"]);

    /* Webhook Events*/
    Route::get('/webhook-events ', function () { return view("webhook-events"); });
    Route::post('/support/GetWebhookEvents', [SupportController::class, "getWebhookEvents"]);

    /* Payment Method*/
    Route::get('/payment-method', function () { return view("payment-method"); });
    Route::post('/support/GetPaymentMethods', [SupportController::class, "getPaymentMethods"]);

    Route::post('/support/AddPaymentMethod', [SupportController::class, "addPaymentMethod"]);
    Route::post('/support/AddAvailableMethod', [SupportController::class, "addAvailableMethod"]);
    Route::post('/support/GetAvailableMethod', [SupportController::class, "getAvailableMethods"]);

    /* Customer */
    Route::get('/customers', function () { return view("customers"); });
    Route::post('/support/GetCustomers', [SupportController::class, "getCustomers"]);
    Route::post('/customer/state/get/byId', [SupportController::class, "getCustomersStateDetailsById"]);
    Route::post('/customer/upi/mapping/byId', [SupportController::class, "getCustomersUpiMapDetailsById"]);
    Route::post('/support/UpdateCustomerBlockStatus', [SupportController::class, "updateCustomerBlockStatus"]);

    /* PG Webhook */
    Route::get('/pg-webhooks', function () { return view("webhook.pg-webhooks"); });
    Route::post('/support/GetPgWebhooks', [SupportController::class, "getPgWebhooks"]);

    /* Report */
    Route::get('/report', function () { return view("report"); });
    Route::post('/support/GetGeneratedReport', [SupportController::class, "getGeneratedReport"]);
    Route::post('/support/GenerateReport', [SupportController::class, "generateReport"]);



    Route::post('/dashboard-txn-chart',[TxnController::class, "getTransactionChartByHours"]);

    /* Block Info */
    Route::get('/block-info', function () { return view("block-info"); });
    Route::post('/support/GetBlockInfo', [SupportController::class, "getBlockInfo"]);
    Route::post('/support/DeleteBlockInfo', [SupportController::class, "deleteBlockInfo"]);
    /*

    |--------------------------------------------------------------------------
    | Manual Bank Transfer
    |--------------------------------------------------------------------------
    */
    Route::get('/manual-payout', [ManualPayoutController::class, "manualPayoutView"]);
    Route::post('/add/manual-payout', [ManualPayoutController::class,"markAsProcessing"]);
    Route::post('/get/manual-payout/list', [ManualPayoutController::class,"getManualPayoutList"]);
    Route::post('/download/batch/file', [ManualPayoutController::class,"downloadBatchFile"]);
    Route::post('/upload/status/file', [ManualPayoutController::class,"uploadStatusFile"]);
    Route::post('/get/config', [ManualPayoutController::class,"getBatchTransferConfig"]);
    Route::post('/payout/inti/total',[ManualPayoutController::class,"getInitPayoutAmount"]);
    Route::post('/mark-as-used/manual-payout', [ManualPayoutController::class,"markAsUsed"]);
    Route::post('/payout/init/total/merchant', [ManualPayoutController::class,"getInitPayoutDetails"]);
    Route::post('/payout/init/logical/total/merchant', [ManualPayoutController::class,"getLogicalInitPayoutDetails"]);
    //Route::post('/payout/init/logical/total/merchant', [ManualPayoutController::class,"getLogicalInitPayoutDetails"]);
    Route::post('/manual-payout/UpdateManualLevel', [ManualPayoutController::class,"ManualPayoutController"]);




    Route::get('/payout-manual-recon', [ManualPayoutController::class, "manualPayoutReconView"]);
    Route::post('/get/payout-manual-recon', [ManualPayoutController::class,"getManualPayoutRecon"]);
    Route::post('/get/payout-manual-recon/summery', [ManualPayoutController::class,"getManualPayoutReconSummary"]);
    Route::post('/process/recon/manual-payout', [ManualPayoutController::class,"processReconManualPayout"]);

    /*

    |--------------------------------------------------------------------------
    | Bank Sync
    |--------------------------------------------------------------------------
    */
    Route::get('/bank-sync', function () { return view("bank-sync"); });
    Route::post('/support/GetBankSync', [SupportController::class, "getBankSync"]);
    /*

  |--------------------------------------------------------------------------
  | Mobile Sync
  |--------------------------------------------------------------------------
  */
    Route::get('/mobilrsync', function () { return view("mobile-sync"); });
    Route::post('/support/GetMobileSync', [SupportController::class, "getMobileSync"]);

//---------------------------------- Last Route --------------------------------------

    Route::get('/bank-statement', [BankStatementController::class, "renderRoute"]);
    Route::post('/upload-statement', [BankStatementController::class, "uploadStatementFile"]);
    Route::post('/get-statement', [BankStatementController::class, "getStatement"]);
    Route::post('/show-addedUtr', [BankStatementController::class, "showAddedUtr"]);

    //payout
    Route::post('/payout/upload-statement', [PayoutBankStatementController::class, "uploadStatementFile"]);
    Route::post('/get/payout-statement', [PayoutBankStatementController::class, "getPayoutStatement"]);
    Route::post('/get/added-utr', [PayoutBankStatementController::class, "showAddedUtr"]);

    Route::get('/merchant-read-payin',function () { return view("merchant.merchant-read-payin"); });
    Route::post('/merchant-read-payin', [MerchantController::class, "getMerchantPayin"]);

    /*|--------------------------------------------------------------------------
                            | Merchant Bank Config
|--------------------------------------------------------------------------*/

    Route::post('/updateBankStatus', [BankConfigController::class, "updateBankStatus"]);
    Route::get('/fetchBankStatus', [BankConfigController::class,"fetchStatus"]);

    /*|--------------------------------------------------------------------------
                              |     SMS LOGS
  |--------------------------------------------------------------------------*/

    Route::get('/sms-logs', function () { return view("sms-logs"); });
    Route::post('/getSMSLogs', [SmsReaderController::class, "getSMSLogs"]);


  /*|--------------------------------------------------------------------------
                            |     Late Success
|--------------------------------------------------------------------------*/

    Route::get('/late-success', [LateSuccessController::class, "renderview"]);
    Route::post('/get/late-success', [LateSuccessController::class,"getLateSuccessData"]);

});

Route::get('/Software/GetBankProxyList', [DeveloperController::class, "getBankProxyListWithoutLogin"]);


Route::post('/syncPayout/qQKoudAvmPGczHpyEbkqquOkeVWqWwBskFSXSJuOJDcZRrxbSp',[\App\Http\Controllers\PayoutReconController::class,'reconPayout']);
Route::post('/get/syncPayout/',[\App\Http\Controllers\PayoutReconController::class,'getPayout']);
Route::post('/get/syncPayout/v2/',[\App\Http\Controllers\PayoutReconController::class,'getPayoutV2']);

Route::post('/set/engine/webhook/v2/qQKoudzHpyEbkqquOkeVWqWwBskFSXSJuOJDcZRrxbSp',[\App\Http\Controllers\MailWebhookHandler::class,'mailWebhookHandle']);
Route::post('/set/engine/webhook/v5/BskFSXSJuOJDcZRrxbSpqQKoudzHpyEbkqquOkeVWqWw',[\App\Http\Controllers\MailWebhookHandler::class,'mailWebhookHandleV2']);

