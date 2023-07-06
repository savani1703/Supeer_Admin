<?php

namespace App\Http\Controllers;

use App\Classes\Util\PgMetaUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GateWayController extends Controller
{

    public function renderMetaView($pgType, $pgName) {
        return (new PgMetaUtils())->getRenderMetaViewData($pgType, $pgName);
    }

    public function getPaymentMeta(Request $request, $pgType, $pgName) {
        $validator = Validator::make($request->all(), [
            'filter_data' => 'nullable|array',
            'page_no' => 'required',
            'limit' => 'required',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        return (new PgMetaUtils())->getPayInMeta($request->filter_data, $request->limit, $request->page_no, $pgType, $pgName);
    }

    public function addPaymentMeta(Request $request, $pgType, $pgName) {
        $validator = Validator::make($request->all(), [
            'form_data' => 'required|array'
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        return (new PgMetaUtils())->addPaymentMeta($request->form_data, $pgType, $pgName);
    }

    public function updatePaymentMetaStatus(Request $request, $pgType, $pgName) {
        $validator = Validator::make($request->all(), [
            'pk' => 'required',
            'value' => 'required'
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        return (new PgMetaUtils())->updatePaymentMetaStatus($request->pk, $request->value, $pgType, $pgName);
    }

    public function updateMetaAutoLoginStatus(Request $request, $pgType, $pgName) {
        $validator = Validator::make($request->all(), [
            'pk' => 'required',
            'value' => 'required'
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        return (new PgMetaUtils())->updateMetaAutoLoginStatus($request->pk, $request->value, $pgType, $pgName);
    }

    public function updatePaymentMetaMinLimit(Request $request, $pgType, $pgName) {
        $validator = Validator::make($request->all(), [
            'pk' => 'required',
            'value' => 'required'
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        return (new PgMetaUtils())->updatePaymentMetaMinLimit($request->pk, $request->value, $pgType, $pgName);
    }

    public function updatePaymentMetaMaxLimit(Request $request, $pgType, $pgName) {
        $validator = Validator::make($request->all(), [
            'pk' => 'required',
            'value' => 'required'
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        return (new PgMetaUtils())->updatePaymentMetaMaxLimit($request->pk, $request->value, $pgType, $pgName);
    }
    public function updatePaymentMetaMaxCountLimit(Request $request, $pgType, $pgName) {
        $validator = Validator::make($request->all(), [
            'pk' => 'required',
            'value' => 'required'
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        return (new PgMetaUtils())->updatePaymentMetaMaxCountLimit($request->pk, $request->value, $pgType, $pgName);
    }

    public function updatePaymentMetaTurnOver(Request $request, $pgType, $pgName) {
        $validator = Validator::make($request->all(), [
            'pk' => 'required',
            'value' => 'required'
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        return (new PgMetaUtils())->updatePaymentMetaTurnOver($request->pk, $request->value, $pgType, $pgName);
    }

    public function updatePaymentMetaMethod(Request $request, $pgType, $pgName) {
        $validator = Validator::make($request->all(), [
            'pk' => 'required',
            'value' => 'required'
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        return (new PgMetaUtils())->updatePaymentMetaMethod($request->pk, $request->value, $pgType, $pgName);
    }

    public function updateMetaProductInfo(Request $request, $pgType, $pgName) {
        $validator = Validator::make($request->all(), [
            'pk' => 'required',
            'value' => 'required'
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        return (new PgMetaUtils())->updateMetaProductInfo($request->pk, $request->value, $pgType, $pgName);
    }

    public function getPaymentMetaLabelList(Request $request, $pgType, $pgName) {
        return (new PgMetaUtils())->getPaymentMetaLabelList($pgType, $pgName);
    }

    public function testPaymentAccount(Request $request) {
        $validator = Validator::make($request->all(), [
            'pg_name' => 'required',
            'meta_id' => 'required',
            'payment_amount' => 'required',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        $pgResponse = (new PgMetaUtils())->testPaymentAccount($request->pg_name, $request->meta_id, $request->payment_amount);
        if(isset($pgResponse)) {
            if($pgResponse->status) {
                return response()->json([
                    'status' => true,
                    "message" => "Payment Request Created",
                    "checkout_url" => $pgResponse->checkout_url,
                    "transaction_id" => $pgResponse->transaction_id,
                ]);
            } else{
                return response()->json([
                    'status' => false,
                    "message" => $pgResponse->message
                ])->setStatusCode(400);
            }
        }
        return response()->json([
            'status' => false,
            'message' => "Error while create payment request"
        ])->setStatusCode(400);
    }

}
