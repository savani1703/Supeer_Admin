<?php

namespace App\Http\Controllers;

use App\Classes\Util\MerchantMetaUtils;
use App\Plugin\AccessControl\AccessControl;
use App\Plugin\AccessControl\Utils\AccessModule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MerchantMetaController extends Controller
{

    public function getPayInMeta(Request $request) {
        $validator = Validator::make($request->all(), [
            'merchant_id' => 'required',
            'filter_data' => 'nullable',
        ]);
        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }

        return (new MerchantMetaUtils())->getMerchantPayInMeta($request->merchant_id, $request->filter_data);
    }

    public function getPayoutMeta(Request $request) {
        $validator = Validator::make($request->all(), [
            'merchant_id' => 'required',
            'filter_data' => 'nullable',
        ]);
        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        return (new MerchantMetaUtils())->getMerchantPayoutMeta($request->merchant_id, $request->filter_data);
    }

    public function updatePayInMetaStatus(Request $request) {
        $validator = Validator::make($request->all(), [
            'merchant_id' => 'required',
            'pg_name' => 'required',
            'id' => 'required',
            'pk' => 'required',
            'value' => 'required|in:0,1'
        ]);
        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }

        return (new MerchantMetaUtils())->updatePayInMetaStatus(
            $request->merchant_id,
            $request->pg_name,
            $request->id,
            $request->pk,
            $request->value
        );
    }

    public function updatePayoutMetaStatus(Request $request) {
        $validator = Validator::make($request->all(), [
            'merchant_id' => 'required',
            'pg_name' => 'required',
            'id' => 'required',
            'pk' => 'required',
            'value' => 'required|in:0,1'
        ]);
        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }

        return (new MerchantMetaUtils())->updatePayoutMetaStatus(
            $request->merchant_id,
            $request->pg_name,
            $request->id,
            $request->pk,
            $request->value
        );
    }

    public function updatePayInMetaMinLimit(Request $request) {
        $validator = Validator::make($request->all(), [
            'merchant_id' => 'required',
            'pg_name' => 'required',
            'id' => 'required',
            'pk' => 'required',
            'value' => 'required'
        ]);
        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        return (new MerchantMetaUtils())->updatePayInMetaMinLimit(
            $request->merchant_id,
            $request->pg_name,
            $request->id,
            $request->pk,
            $request->value
        );
    }

    public function updatePayInMetaMaxLimit(Request $request) {
        $validator = Validator::make($request->all(), [
            'merchant_id' => 'required',
            'pg_name' => 'required',
            'id' => 'required',
            'pk' => 'required',
            'value' => 'required'
        ]);
        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        return (new MerchantMetaUtils())->updatePayInMetaMaxLimit(
            $request->merchant_id,
            $request->pg_name,
            $request->id,
            $request->pk,
            $request->value
        );
    }

    public function updatePayInMetaDailyLimit(Request $request) {
        $validator = Validator::make($request->all(), [
            'merchant_id' => 'required',
            'pg_name' => 'required',
            'id' => 'required',
            'pk' => 'required',
            'value' => 'required'
        ]);
        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        return (new MerchantMetaUtils())->updatePayInMetaDailyLimit(
            $request->merchant_id,
            $request->pg_name,
            $request->id,
            $request->pk,
            $request->value
        );
    }
    public function UpdatePayInMetaPerLimit(Request $request) {
        $validator = Validator::make($request->all(), [
            'merchant_id' => 'required',
            'pg_name' => 'required',
            'id' => 'required',
            'pk' => 'required',
            'value' => 'required'
        ]);
        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        return (new MerchantMetaUtils())->updatePayInMetaPerLimit(
            $request->merchant_id,
            $request->pg_name,
            $request->id,
            $request->pk,
            $request->value
        );
    }

    public function updatePayInMetaLevel(Request $request) {
        $validator = Validator::make($request->all(), [
            'merchant_id' => 'required',
            'pg_name' => 'required',
            'id' => 'required',
            'pg_id' => 'required',
            'level_key' => 'required',
            'status' => 'required|in:0,1'
        ]);
        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        return (new MerchantMetaUtils())->updatePayInMetaLevel(
            $request->merchant_id,
            $request->pg_name,
            $request->id,
            $request->pg_id,
            $request->level_key,
            $request->status
        );
    }

    public function deletePayInMeta(Request $request) {
        $validator = Validator::make($request->all(), [
            'merchant_id' => 'required',
            'pg_name' => 'required',
            'id' => 'required',
            'pg_id' => 'required'
        ]);
        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        return (new MerchantMetaUtils())->deletePayInMeta(
            $request->merchant_id,
            $request->pg_name,
            $request->id,
            $request->pg_id
        );
    }

    public function getAvailablePaymentMeta(Request $request) {
        $validator = Validator::make($request->all(), [
            'merchant_id' => 'required'
        ]);
        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        return (new MerchantMetaUtils())->getAvailablePaymentMeta($request->merchant_id);
    }

    public function getAvailablePayoutMeta(Request $request) {
        $validator = Validator::make($request->all(), [
            'merchant_id' => 'required'
        ]);
        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        return (new MerchantMetaUtils())->getAvailablePayoutMeta($request->merchant_id);
    }

    public function addPayInMetaToMerchantCollection(Request $request) {
        $validator = Validator::make($request->all(), [
            'merchant_id' => 'required',
            'pg_name' => 'required',
            'pg_id' => 'required',
            'payment_method' => 'required|array|min:1',
            'level' => 'required|array|min:1',
        ]);
        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        return (new MerchantMetaUtils())->addPayInMetaToMerchantCollection(
            $request->merchant_id,
            $request->pg_name,
            $request->pg_id,
            $request->payment_method,
            $request->level
        );
    }

    public function addPayoutMetaToMerchantWithdrawal(Request $request) {
        $validator = Validator::make($request->all(), [
            'merchant_id' => 'required',
            'pg_name' => 'required',
            'pg_id' => 'required'
        ]);
        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        return (new MerchantMetaUtils())->addPayInMetaToMerchantWithdrawal(
            $request->merchant_id,
            $request->pg_name,
            $request->pg_id
        );
    }

    public function updateMerchantAllMeta(Request $request) {
        $validator = Validator::make($request->all(), [
            'merchant_id'   => 'required',
            'action'        => 'required:in:ACTIVE,DEACTIVE',
            'bank_name'     => 'required|in:FEDERAL,IDFC'
        ]);
        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }

        return (new MerchantMetaUtils())->updateMerchantAllMeta(
            $request->merchant_id,
            $request->action,
            $request->bank_name
        );
    }

}
