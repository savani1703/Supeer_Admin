<?php

namespace App\Http\Controllers;

use App\Classes\Util\ReconciliationUtils;
use App\Plugin\AccessControl\Utils\AccessControlUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReconciliationController extends Controller
{

    public function renderReconView() {
        $reconOptions = AccessControlUtils::reconsOptions();
        return view("reconciliation.recon")->with("options", $reconOptions);
    }

    public function UtrRecon() {
        return view("reconciliation.utr-reconciliation");
    }
   public function GetUtrReconciliation() {
       return (new ReconciliationUtils())->UtrRecon();
    }
    public function SetUtrReconciliation() {
        return (new ReconciliationUtils())->SetUtrRecon();
    }
    public function GetUtrReconciliationReport() {
        return (new ReconciliationUtils())->UtrReconReport();
    }

    public function reconciliationPayment(Request $request) {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'type' => 'required|in:PAYIN,PAYOUT'
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }

        return (new ReconciliationUtils())->recon($request->id, $request->type);
    }

    public function reconciliationPaymentAction(Request $request) {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'type' => 'required|in:PAYIN,PAYOUT',
            'action' => 'required|in:ACCEPT',
        ]);
        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }

        return (new ReconciliationUtils())->reconAction($request->id, $request->type, $request->action);
    }

    public function emptyBulkPeBal(Request $request) {
        $validator = Validator::make($request->all(), [
            'account_id' => 'required',
        ]);
        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }

        return (new ReconciliationUtils())->emptyBulkPeBal($request->account_id);
    }
}
