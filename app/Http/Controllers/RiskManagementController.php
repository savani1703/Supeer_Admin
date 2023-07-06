<?php

namespace App\Http\Controllers;

use App\Classes\Util\DigiPayUtil;
use App\Classes\Util\RiskManager;
use App\Plugin\AccessControl\Utils\AccessControlUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RiskManagementController extends Controller
{

    public function renderStateView() {
        $merchantList = DigiPayUtil::MerchantList();
        return view('risk.state')
            ->with("merchantList", $merchantList);
    }

    public function getCustSummery(Request $request) {
        $validator = Validator::make($request->all(), [
            'filter_data' => 'required',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        return (new RiskManager())->getCustSummery($request->filter_data);
    }

    public function getCustomerLevelingReport(Request $request){
        $validator = Validator::make($request->all(), [
            'filter_type' => 'required:TOTAL,LV1,LV2,LV5',
            'filter_data' => 'required',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        return (new RiskManager())->getCustomerLevelingReport($request->filter_type, $request->filter_data);
    }

    public function GetCustHidDetail(Request $request){
        $validator = Validator::make($request->all(), [
            'filter_data' => 'nullable|array',
            'page_no' => 'required',
            'limit' => 'required',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        return (new RiskManager())->GetCustHidDetail($request->filter_data,$request->limit, $request->page_no);
    }

    public function getUserStateData(Request $request){
        $validator = Validator::make($request->all(), [
            'filter_data' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        return (new RiskManager())->getUserStateData($request->filter_data);
    }
    public function custBehaviour(Request $request){
        $validator = Validator::make($request->all(), [
            'filter_data' => 'nullable|array',
            'page_no' => 'required',
            'limit' => 'required',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        return (new RiskManager())->custBehaviour($request->filter_data,$request->limit, $request->page_no);
    }

}
