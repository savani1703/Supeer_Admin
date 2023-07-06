<?php


namespace App\Http\Controllers;

use App\Classes\Util\DigiPayUtil;
use App\Classes\Util\PgType;
use App\Classes\Util\TransactionUtils;
use App\Models\Management\Transactions;
use App\Plugin\AccessControl\AccessControl;
use App\Plugin\AccessControl\Utils\AccessControlUtils;
use App\Plugin\AccessControl\Utils\AccessModule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TxnController extends Controller
{
    public function txnView() {
        $pgType = AccessControlUtils::paymentPgType();
        $merchantList = DigiPayUtil::MerchantList();
        $payInPgList = DigiPayUtil::PayInPgList($pgType);
        return view('transaction')
            ->with("merchantList", $merchantList)
            ->with("payInPgList", $payInPgList);
    }
    public function custIdView() {
        return view('cust-byId');
    }

    public function getTransactions(Request $request){
        $validator = Validator::make($request->all(), [
            'filter_data' => 'nullable|array',
            'page_no' => 'required',
            'limit' => 'required',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        $pgTye = AccessControlUtils::paymentPgType();
        return (new TransactionUtils())->getTransactions($request->filter_data, $pgTye, $request->limit, $request->page_no);
    }

    public function getTransactionSummary(Request $request){
        $validator = Validator::make($request->all(), [
            'filter_data' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }

        if(!(new AccessControl())->hasAccessModule(AccessModule::TRANSACTION_SUMMARY)) {
            return response()->json(['status' => false, 'message' => AccessModule::ACCESS_DENIED])->setStatusCode(400);
        }

        $pgTye = AccessControlUtils::paymentPgType();
        return (new TransactionUtils())->getTransactionSummary($request->filter_data, $pgTye);
    }

    public function getTransactionById(Request $request){
        $validator = Validator::make($request->all(), [
            'transaction_id' => 'required',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        return (new TransactionUtils())->getTransactionById($request->transaction_id);
    }
    public function getTransactionByBrowserId(Request $request){
        $validator = Validator::make($request->all(), [
            'browser_id' => 'required',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        return (new TransactionUtils())->getTransactionByBrowserId($request->browser_id);
    }
    public function getTransactionByUTR(Request $request){
        $validator = Validator::make($request->all(), [
            'bank_utr' => 'required',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        return (new TransactionUtils())->getTransactionByUTR($request->bank_utr);
    }

    public function resendTransactionWebhook(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'transaction_id' => 'required',
        ]);
        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }

        if(!(new AccessControl())->hasAccessModule(AccessModule::TRANSACTION_ACTION)) {
            return response()->json(['status' => false, 'message' => AccessModule::ACCESS_DENIED])->setStatusCode(400);
        }
        return (new TransactionUtils())->resendTransactionWebhook($request->transaction_id);
    }

    public function updateTransactionTempUtr(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'transaction_id' => 'required',
            'temp_utr' => 'required',
        ]);
        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }

        $pgType = AccessControlUtils::paymentPgType();
        if(strcmp($pgType, PgType::AUTO) === 0) {
            return response()->json(['status' => false, 'message' => AccessModule::ACCESS_DENIED])->setStatusCode(400);
        }
        return (new TransactionUtils())->updateTransactionTempUtr($request->transaction_id, $request->temp_utr);
    }

    public function setUtrTransaction_id(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'transaction_id' => 'required',
            'utr_no_id' => 'required',
        ]);
        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }

        $pgType = AccessControlUtils::paymentPgType();
        if(strcmp($pgType, PgType::AUTO) === 0) {
            return response()->json(['status' => false, 'message' => AccessModule::ACCESS_DENIED])->setStatusCode(400);
        }
        return (new TransactionUtils())->updateTransactionTempUtr($request->transaction_id, $request->utr_no_id);
    }

    public function setUtrPaymentRef(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'utr_no_id' => 'required',
            'payment_ref_id' => 'required',
        ]);
        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }

        $pgType = AccessControlUtils::paymentPgType();
        if(strcmp($pgType, PgType::AUTO) === 0) {
            return response()->json(['status' => false, 'message' => AccessModule::ACCESS_DENIED])->setStatusCode(400);
        }
        $itxn_id= (new Transactions())->where('merchant_order_id',$request->payment_ref_id)->value('transaction_id');
        if(isset($itxn_id)) {
            return (new TransactionUtils())->updateTransactionTempUtr($itxn_id, $request->utr_no_id);
        }else
        {
            return response()->json(['status' => false, 'message' => "Not Found"])->setStatusCode(400);
        }
    }

    public function blockCustomerDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'transaction_id' => 'required'
        ]);
        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        $pgType = AccessControlUtils::paymentPgType();
        if(!(new AccessControl())->hasAccessModule(AccessModule::TRANSACTION_ACTION)) {
            return response()->json(['status' => false, 'message' => AccessModule::ACCESS_DENIED])->setStatusCode(400);
        }
        return (new TransactionUtils())->blockCustomerDetails($request->transaction_id);
    }
    public function blockCustomerAllDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'browser_id' => 'required'
        ]);
        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        $pgType = AccessControlUtils::paymentPgType();
        if(!(new AccessControl())->hasAccessModule(AccessModule::TRANSACTION_ACTION)) {
            return response()->json(['status' => false, 'message' => AccessModule::ACCESS_DENIED])->setStatusCode(400);
        }
        return (new TransactionUtils())->blockCustomerAllDetails($request->browser_id);
    }

    public function transactionRefund(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'transaction_id' => 'required',
            'refund_amount' => 'required',
            'remark' => 'required',
        ]);
        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }

        if(!(new AccessControl())->hasAccessModule(AccessModule::TRANSACTION_ACTION)) {
            return response()->json(['status' => false, 'message' => AccessModule::ACCESS_DENIED])->setStatusCode(400);
        }
        return (new TransactionUtils())->transactionRefund($request->transaction_id, $request->refund_amount, $request->remark);
    }
    public function transactionSetUTR(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'transaction_id' => 'required',
            'payment_utr' => 'required',
        ]);
        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }

        if(!(new AccessControl())->hasAccessModule(AccessModule::TRANSACTION_ACTION)) {
            return response()->json(['status' => false, 'message' => AccessModule::ACCESS_DENIED])->setStatusCode(400);
        }
        return (new TransactionUtils())->transactionSetUTR($request->transaction_id, $request->payment_utr);
    }
    public function transactionDeleteManualUTR(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'transaction_id' => 'required',
            'payment_amount' => 'required',
        ]);
        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }

        if(!(new AccessControl())->hasAccessModule(AccessModule::TRANSACTION_ACTION)) {
            return response()->json(['status' => false, 'message' => AccessModule::ACCESS_DENIED])->setStatusCode(400);
        }

        return (new TransactionUtils())->transactionDelete($request->transaction_id, $request->payment_amount);
    }
    public function transactionRemoveFees(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'transaction_id' => 'required',
            'payment_amount' => 'required',
        ]);
        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }

        if(!(new AccessControl())->hasAccessModule(AccessModule::TRANSACTION_ACTION)) {
            return response()->json(['status' => false, 'message' => AccessModule::ACCESS_DENIED])->setStatusCode(400);
        }

        return (new TransactionUtils())->transactionRemoveFees($request->transaction_id, $request->payment_amount);
    }
    public function getTransactionChartByHours(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "start_date" => "required",
            "end_date" => "required",
            "merchant_id" => "nullable",
            "cust_level" => "nullable"
        ]);
        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        return (new TransactionUtils())->getTransactionChartByHours( $request->start_date,$request->end_date,$request->merchant_id,$request->cust_level);
    }
}
