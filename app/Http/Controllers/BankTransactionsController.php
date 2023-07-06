<?php

namespace App\Http\Controllers;

use App\Classes\Util\BankTransactionUtils;
use App\Plugin\AccessControl\AccessControl;
use App\Plugin\AccessControl\Utils\AccessModule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;


class BankTransactionsController extends Controller
{
    private $bankTransaction;

    public function __construct(){
        $this->bankTransaction = new BankTransactionUtils();
    }

    public function getBankTransaction(Request $request){
        $validator = Validator::make($request->all(), [
            'filter_data' => 'nullable|array',
            'page_no' => 'required',
            'limit' => 'required',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        return $this->bankTransaction->getBankTransactions($request->filter_data,$request->limit, $request->page_no);
    }
    public function MarkAsUsed(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payment_utr' => 'required',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }

        if(!(new AccessControl())->hasAccessModule(AccessModule::TRANSACTION_MANUAL_VIEW)) {
            return response()->json(['status' => false, 'message' => AccessModule::ACCESS_DENIED])->setStatusCode(400);
        }

        return $this->bankTransaction->MarkAsUsed($request->payment_utr);
    }
    public function mergeUtr(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'utr_ref_1' => 'required',
            'utr_ref_2' => 'required',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }

        if(!(new AccessControl())->hasAccessModule(AccessModule::MANUAL_BANK_ENTRY)) {
            return response()->json(['status' => false, 'message' => AccessModule::ACCESS_DENIED])->setStatusCode(400);
        }


        return $this->bankTransaction->mergeUtr($request->utr_ref_1,$request->utr_ref_2);
    }

    public function addBankTransaction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payment_utr' => 'required',
            'amount' => 'required',
            'account_number' => 'required',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }

        if(!(new AccessControl())->hasAccessModule(AccessModule::MANUAL_BANK_ENTRY)) {
            return response()->json(['status' => false, 'message' => AccessModule::ACCESS_DENIED])->setStatusCode(400);
        }
        return $this->bankTransaction->addBankTransaction($request->payment_utr, $request->amount, $request->account_number);
    }

    public function getAvailableBank(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bank_name' => 'nullable',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }

        if(!(new AccessControl())->hasAccessModule(AccessModule::MANUAL_BANK_ENTRY)) {
            return response()->json(['status' => false, 'message' => AccessModule::ACCESS_DENIED])->setStatusCode(400);
        }
        return $this->bankTransaction->getAvailableBank($request->bank_name);
    }
    public function UpdateMobileNumber(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'value' => 'required',
            'pk' => 'required'
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        if(!(new AccessControl())->hasAccessModule(AccessModule::MERCHANT_MODIFY)) {
            return response()->json(['status' => false, 'message' => AccessModule::ACCESS_DENIED])->setStatusCode(400);
        }
        return $this->bankTransaction->updateMobileNumber($request->pk, $request->value);
    }
}
