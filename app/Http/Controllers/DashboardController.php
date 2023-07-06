<?php

namespace App\Http\Controllers;

use App\Classes\Util\DashboardUtils;
use App\Classes\Util\DigiPayUtil;
use App\Classes\Util\ReconciliationUtils;
use App\Classes\Util\TransactionUtils;
use App\Plugin\AccessControl\Utils\AccessControlUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DashboardController extends Controller
{
    public function renderMMDashboard(Request $request) {
        $merchantList = DigiPayUtil::MerchantList();
        return view("dashboard")->with("merchantList", $merchantList);
    }
    public function getMMDashboardSummary(Request $request) {
        return (new DashboardUtils())->getMMDashboardSummary();
    }
    public function GetMMTransactionSummary(Request $request) {
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
        return (new DashboardUtils())->GetMMTransactionSummary($request->start_date,$request->end_date,$request->merchant_id,);
    }
    public function GetMMPayoutSummary(Request $request) {
        return (new DashboardUtils())->GetMMPayoutSummary();
    }

    public function getPgManagementDashboardView() {
        $payInPgList = DigiPayUtil::PayInPgList();
        return view("dashboard.pg-dashboard.pg-management-dashboard")->with("payInPgList", $payInPgList);
    }

    public function getMMDashboardData(Request $request) {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required',
            'end_date' => 'required',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        return (new DashboardUtils())->getMMDashboardData($request->start_date, $request->end_date);
    }

    public function getMMDashboardBalanceData(Request $request) {
        $validator = Validator::make($request->all(), [
            'merchant_id' => 'required'
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        return (new DashboardUtils())->getMMDashboardBalanceData($request->merchant_id);
    }

    public function getPgSummary(Request $request) {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required',
            'end_date' => 'required',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        return (new DashboardUtils())->getPgSummary($request->start_date, $request->end_date);
    }

    public function getPgManagementDashboardSummary(Request $request) {
        $validator = Validator::make($request->all(), [
            'pg_type' => 'required',
            'pg_name' => 'required',
            'pg_account' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        return (new DashboardUtils())->getPgManagementDashboardSummary(
            $request->pg_type,
            $request->pg_name,
            $request->pg_account,
            $request->start_date,
            $request->end_date
        );
    }

    public function getPgManagementDashboardGetPgList(Request $request) {
        $validator = Validator::make($request->all(), [
            'pg_type' => 'required'
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        $pgType = $request->pg_type;
        if(strcmp(strtolower($pgType), "payin") === 0) {
            $result['status'] = true;
            $result['message'] = "Data Retrieved";
            $result['data'] = DigiPayUtil::PayInPgList();
            return response()->json($result)->setStatusCode(200);
        }
        if(strcmp(strtolower($pgType), "payout") === 0) {
            $result['status'] = true;
            $result['message'] = "Data Retrieved";
            $result['data'] = DigiPayUtil::PayoutPgList();
            return response()->json($result)->setStatusCode(200);
        }
        return response()->json(['status' => false, 'message' => "Invalid Request"])->setStatusCode(400);
    }

    public function getPayoutCrData(Request $request){
        $validator = Validator::make($request->all(), [
            'filter_data' => 'nullable|array',
            'page_no' => 'required',
            'limit' => 'required',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        return (new DashboardUtils())->getPayoutCrData($request->filter_data, $request->page_no,$request->limit);
    }
}
