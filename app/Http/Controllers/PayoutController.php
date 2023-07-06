<?php

namespace App\Http\Controllers;

use App\Classes\Util\DigiPayUtil;
use App\Classes\Util\PayoutUtils;
use App\Classes\Util\SupportUtils;
use App\Models\Management\PgRouter;
use App\Plugin\AccessControl\AccessControl;
use App\Plugin\AccessControl\Utils\AccessControlUtils;
use App\Plugin\AccessControl\Utils\AccessModule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PayoutController extends Controller
{
    private $payout;

    public function __construct(PayoutUtils $payout){
        $this->payout = $payout;
    }

    public function renderPayout(Request $request){
        $pgType = AccessControlUtils::payoutPgType();
        $merchantList = DigiPayUtil::MerchantList();
        $payoutPgList = DigiPayUtil::PayoutPgList($pgType);
        return view("payout")->with("merchantList", $merchantList)->with("payoutPgList", $payoutPgList);
    }
    public function getSummery(Request $request) {
     /*   if(!(new AccessControl())->hasAccessModule(AccessModule::PAYOUT_SUMMARY)) {
            return response()->json(['status' => false, 'message' => AccessModule::ACCESS_DENIED])->setStatusCode(400);
        }*/
        return $this->payout->getDashboardSummary();
    }
    public function getPayout(Request $request){
        $validator = Validator::make($request->all(), [
            'filter_data' => 'nullable|array',
            'page_no' => 'required',
            'limit' => 'required',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }

        $pgType = AccessControlUtils::payoutPgType();
        return $this->payout->getPayout($request->filter_data, $pgType, $request->limit, $request->page_no);
    }

    public function getPayoutById(Request $request){
        $validator = Validator::make($request->all(), [
            'payout_id' => 'required',
        ]);
        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        return $this->payout->getPayoutById($request->payout_id);
    }

    public function resendWebhook(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payout_id' => 'required',
        ]);
        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        if(!(new AccessControl())->hasAccessModule(AccessModule::PAYOUT_MODIFY)) {
            return response()->json(['status' => false, 'message' => AccessModule::ACCESS_DENIED])->setStatusCode(400);
        }
        return $this->payout->resendWebhook($request->payout_id);
    }

    public function cancelledInitializedPayout(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payout_id' => 'required',
        ]);
        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        if(!(new AccessControl())->hasAccessModule(AccessModule::PAYOUT_MODIFY)) {
            return response()->json(['status' => false, 'message' => AccessModule::ACCESS_DENIED])->setStatusCode(400);
        }
        return $this->payout->cancelledInitializedPayout($request->payout_id);
    }
    public function ResetInitializedPayout(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payout_id' => 'required',
        ]);
        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        if(!(new AccessControl())->hasAccessModule(AccessModule::PAYOUT_MODIFY)) {
            return response()->json(['status' => false, 'message' => AccessModule::ACCESS_DENIED])->setStatusCode(400);
        }
        return $this->payout->ResetInitializedPayout($request->payout_id);
    }

    public function getPayoutConfiguration(Request $request)
    {
        if(!(new AccessControl())->hasAccessModule(AccessModule::PAYOUT_MODIFY)) {
            return response()->json(['status' => false, 'message' => AccessModule::ACCESS_DENIED])->setStatusCode(400);
        }
        return $this->payout->getPayoutConfiguration();
    }

    public function resetLowBalPayoutToInitialize(Request $request)
    {
        if(!(new AccessControl())->hasAccessModule(AccessModule::PAYOUT_MODIFY)) {
            return response()->json(['status' => false, 'message' => AccessModule::ACCESS_DENIED])->setStatusCode(400);
        }
        return $this->payout->resetLowBalPayoutToInitialize();
    }

    public function updatePayoutConfiguration(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'is_auto_transfer_enable' => 'required|in:0,1',
            'is_payout_status_call_enable' => 'required|in:0,1',
            'small_first' => 'required|in:0,1',
            'large_first' => 'required|in:0,1',
            'max_manual_transfer_limit' => 'required',
            'min_manual_transfer_limit' => 'required',
            'max_lowbal_limit' => 'required',
            'max_pending_limit' => 'required',
            'max_last_failed_limit' => 'required',
            'min_auto_transfer_limit' => 'required',
            'max_auto_transfer_limit' => 'required',
            'payout_delayed_in_seconds' => 'required',
            'is_auto_level_active' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        if(!(new AccessControl())->hasAccessModule(AccessModule::PAYOUT_MODIFY)) {
            return response()->json(['status' => false, 'message' => AccessModule::ACCESS_DENIED])->setStatusCode(400);
        }
        return $this->payout->updatePayoutConfiguration(
            $request->is_auto_transfer_enable,
            $request->is_payout_status_call_enable,
            $request->max_manual_transfer_limit,
            $request->min_manual_transfer_limit,
            $request->max_lowbal_limit,
            $request->max_pending_limit,
            $request->max_last_failed_limit,
            $request->min_auto_transfer_limit,
            $request->max_auto_transfer_limit,
            $request->payout_delayed_in_seconds,
            $request->small_first,
            $request->large_first,
            $request->is_auto_level_active,
        );
    }

    public function getPayoutPgMeta(Request $request){
        $validator = Validator::make($request->all(), [
            'pg_name'    => 'required',
        ],[
            'pg_name.required'   => 'pg is required',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        if(!(new AccessControl())->hasAccessModule(AccessModule::PAYOUT_MODIFY)) {
            return response()->json(['status' => false, 'message' => AccessModule::ACCESS_DENIED])->setStatusCode(400);
        }
        return $this->payout->getPayoutPgMeta($request->pg_name);
    }

    public function payoutStatusUpdate(Request $request){
        $validator = Validator::make($request->all(), [
            'payout_id'    => 'required',
            'payout_status'    => 'required|in:Success',
            'payout_utr'    => 'required',
        ],[
            'payout_id.required'   => 'pg is required',
            'payout_status.required'   => 'payout status is required',
            'payout_utr.required'   => 'payout utr is required',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        if(!(new AccessControl())->hasAccessModule(AccessModule::PAYOUT_MODIFY)) {
            return response()->json(['status' => false, 'message' => AccessModule::ACCESS_DENIED])->setStatusCode(400);
        }
        return $this->payout->payoutStatusUpdate($request->payout_id, $request->payout_status, $request->payout_utr);
    }

    public function getCustLevelData(Request $request) {
        $validator = Validator::make($request->all(), [
            'filter_data' => 'nullable|array',
            'page_no' => 'required',
            'limit' => 'required',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        return (new PayoutUtils())->getCustLevelData($request->filter_data, $request->limit, $request->page_no);
    }

    public function getPayoutAccountLoad(){
        if(!(new AccessControl())->hasAccessModule(AccessModule::PAYOUT_MODIFY)) {
            return response()->json(['status' => false, 'message' => AccessModule::ACCESS_DENIED])->setStatusCode(400);
        }
        return (new PayoutUtils())->getPayoutAccountLoad();
    }
}
