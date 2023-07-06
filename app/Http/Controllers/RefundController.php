<?php

namespace App\Http\Controllers;

use App\Classes\Util\DigiPayUtil;
use App\Classes\Util\RefundUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RefundController extends Controller
{

    public function renderRefund() {
        $merchantList = DigiPayUtil::MerchantList();
        return view("refund.refund")
            ->with("merchantList", $merchantList);
    }

    public function getRefund(Request $request){
        $validator = Validator::make($request->all(), [
            'filter_data' => 'nullable|array',
            'page_no' => 'required',
            'limit' => 'required',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        return (new RefundUtils())->getRefund($request->filter_data, $request->limit, $request->page_no);
    }
}
