<?php


namespace App\Http\Controllers;


use App\Classes\Util\MerchantUtils;
use App\Models\Management\MerchantDetails;
use App\Plugin\AccessControl\AccessControl;
use App\Plugin\AccessControl\Utils\AccessModule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MerchantController extends Controller
{

    private $merchant;

    public function __construct(MerchantUtils $merchant){
        $this->merchant = $merchant;
    }

    public function renderView(Request $request){
        $isAllowedAddMerchant       = (new AccessControl())->hasAccessModule(AccessModule::MERCHANT_MODIFY);
        $isAllowedModifyBankConfig  = (new AccessControl())->hasAccessModule(AccessModule::BANK_CONFIG);
        return view("merchant.merchant-detail")->with("isAllowedAddMerchant", $isAllowedAddMerchant)->with("isAllowedModifyBankConfig", $isAllowedModifyBankConfig);
    }


    public function renderPayInView(Request $request, $merchant_id){
        $merchantDetail = (new MerchantDetails())->getMerchantDetails($merchant_id);
        return view("merchant.payin-meta")->with("merchantDetail", $merchantDetail);
    }

    public function renderPayoutView(Request $request, $merchant_id){
        $merchantDetail = (new MerchantDetails())->getMerchantDetails($merchant_id);
        return view("merchant.payout-meta")->with("merchantDetail", $merchantDetail);
    }

    public function getMerchants(Request $request){
        $validator = Validator::make($request->all(), [
            'filter_data' => 'nullable|array',
            'page_no' => 'required',
            'limit' => 'required',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        if(!(new AccessControl())->hasAccessModule(AccessModule::MERCHANT_VIEW)) {
            return response()->json(['status' => false, 'message' => AccessModule::ACCESS_DENIED])->setStatusCode(400);
        }
        return $this->merchant->getMerchants($request->filter_data, $request->limit, $request->page_no);
    }

    public function addMerchant(Request $request){
        $validator = Validator::make($request->all(), [
            'merchant_email' => 'required',
            'merchant_name' => 'required'
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        if(!(new AccessControl())->hasAccessModule(AccessModule::MERCHANT_MODIFY)) {
            return response()->json(['status' => false, 'message' => AccessModule::ACCESS_DENIED])->setStatusCode(400);
        }
        return $this->merchant->addMerchant($request->merchant_email, $request->merchant_name);
    }

    public function updateAccountStatus(Request $request){
        $validator = Validator::make($request->all(), [
            'value' => 'required|in:New,Hold,Approved,Suspended',
            'pk' => 'required'
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        if(!(new AccessControl())->hasAccessModule(AccessModule::MERCHANT_MODIFY)) {
            return response()->json(['status' => false, 'message' => AccessModule::ACCESS_DENIED])->setStatusCode(400);
        }
        return $this->merchant->updateAccountStatus($request->pk, $request->value);
    }

    public function updateIsPayoutEnable(Request $request){
        $validator = Validator::make($request->all(), [
            'value' => 'required|in:0,1',
            'pk' => 'required'
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        if(!(new AccessControl())->hasAccessModule(AccessModule::MERCHANT_MODIFY)) {
            return response()->json(['status' => false, 'message' => AccessModule::ACCESS_DENIED])->setStatusCode(400);
        }
        return $this->merchant->updateIsPayoutEnable($request->pk, $request->value);
    }

    public function updateIsPayInEnable(Request $request){
        $validator = Validator::make($request->all(), [
            'value' => 'required|in:0,1',
            'pk' => 'required'
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        if(!(new AccessControl())->hasAccessModule(AccessModule::MERCHANT_MODIFY)) {
            return response()->json(['status' => false, 'message' => AccessModule::ACCESS_DENIED])->setStatusCode(400);
        }
        return $this->merchant->updateIsPayInEnable($request->pk, $request->value);
    }

    public function updateIsFailedWebhookRequired(Request $request){
        $validator = Validator::make($request->all(), [
            'value' => 'required|in:0,1',
            'pk' => 'required'
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        if(!(new AccessControl())->hasAccessModule(AccessModule::MERCHANT_MODIFY)) {
            return response()->json(['status' => false, 'message' => AccessModule::ACCESS_DENIED])->setStatusCode(400);
        }
        return $this->merchant->updateIsFailedWebhookRequired($request->pk, $request->value);
    }

    public function updateIsEnableBrowserCheck(Request $request){
        $validator = Validator::make($request->all(), [
            'value' => 'required|in:0,1',
            'pk' => 'required'
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        if(!(new AccessControl())->hasAccessModule(AccessModule::MERCHANT_MODIFY)) {
            return response()->json(['status' => false, 'message' => AccessModule::ACCESS_DENIED])->setStatusCode(400);
        }
        return $this->merchant->updateIsEnableBrowserCheck($request->pk, $request->value);
    }

    public function updateIsEnablePayoutBalanceCheck(Request $request){
        $validator = Validator::make($request->all(), [
            'value' => 'required|in:0,1',
            'pk' => 'required'
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        if(!(new AccessControl())->hasAccessModule(AccessModule::MERCHANT_MODIFY)) {
            return response()->json(['status' => false, 'message' => AccessModule::ACCESS_DENIED])->setStatusCode(400);
        }
        return $this->merchant->updateIsEnablePayoutBalanceCheck($request->pk, $request->value);
    }

    public function updateIsDashboardPayoutEnable(Request $request){
        $validator = Validator::make($request->all(), [
            'value' => 'required|in:0,1',
            'pk' => 'required'
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        if(!(new AccessControl())->hasAccessModule(AccessModule::MERCHANT_MODIFY)) {
            return response()->json(['status' => false, 'message' => AccessModule::ACCESS_DENIED])->setStatusCode(400);
        }
        return $this->merchant->updateIsDashboardPayoutEnable($request->pk, $request->value);
    }

    public function updateIsAutoApprovedPayout(Request $request){
        $validator = Validator::make($request->all(), [
            'value' => 'required|in:0,1',
            'pk' => 'required'
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        if(!(new AccessControl())->hasAccessModule(AccessModule::MERCHANT_MODIFY)) {
            return response()->json(['status' => false, 'message' => AccessModule::ACCESS_DENIED])->setStatusCode(400);
        }
        return $this->merchant->updateIsAutoApprovedPayout($request->pk, $request->value);
    }

    public function updateShowCustomerDetailsPage(Request $request){
        $validator = Validator::make($request->all(), [
            'value' => 'required|in:0,1',
            'pk' => 'required'
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        if(!(new AccessControl())->hasAccessModule(AccessModule::MERCHANT_MODIFY)) {
            return response()->json(['status' => false, 'message' => AccessModule::ACCESS_DENIED])->setStatusCode(400);
        }
        return $this->merchant->updateShowCustomerDetailsPage($request->pk, $request->value);
    }

    public function updateIsCustomerDetailsRequired(Request $request){
        $validator = Validator::make($request->all(), [
            'value' => 'required|in:0,1',
            'pk' => 'required'
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        if(!(new AccessControl())->hasAccessModule(AccessModule::MERCHANT_MODIFY)) {
            return response()->json(['status' => false, 'message' => AccessModule::ACCESS_DENIED])->setStatusCode(400);
        }
        return $this->merchant->updateIsCustomerDetailsRequired($request->pk, $request->value);
    }

    public function updateIsSettlementEnable(Request $request){
        $validator = Validator::make($request->all(), [
            'value' => 'required|in:0,1',
            'pk' => 'required'
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        if(!(new AccessControl())->hasAccessModule(AccessModule::MERCHANT_MODIFY)) {
            return response()->json(['status' => false, 'message' => AccessModule::ACCESS_DENIED])->setStatusCode(400);
        }
        return $this->merchant->updateIsSettlementEnable($request->pk, $request->value);
    }

    public function updatePayInWebhook(Request $request){
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
        return $this->merchant->updatePayInWebhook($request->pk, $request->value);
    }

    public function updatePayoutWebhook(Request $request){
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
        return $this->merchant->updatePayoutWebhook($request->pk, $request->value);
    }

    public function updatePayInAutoFees(Request $request){
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
        return $this->merchant->updatePayInAutoFees($request->pk, $request->value);
    }

    public function updatePayInManualFees(Request $request){
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
        return $this->merchant->updatePayInManualFees($request->pk, $request->value);
    }

    public function updatePayoutFees(Request $request){
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
        return $this->merchant->updatePayoutFees($request->pk, $request->value);
    }

    public function updatePayoutAssociateFees(Request $request){
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
        return $this->merchant->updatePayoutAssociateFees($request->pk, $request->value);
    }

    public function updatePayInAssociateFees(Request $request){
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
        return $this->merchant->updatePayInAssociateFees($request->pk, $request->value);
    }

    public function updateSettlementCycle(Request $request){
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
        return $this->merchant->updateSettlementCycle($request->pk, $request->value);
    }

    public function updatePayoutDelayedTime(Request $request){
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
        return $this->merchant->updatePayoutDelayedTime($request->pk, $request->value);
    }

    public function updateOldUsersDays(Request $request){
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
        return $this->merchant->updateOldUsersDays($request->pk, $request->value);
    }

    public function updateCheckoutColor(Request $request){
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
        return $this->merchant->updateCheckoutColor($request->pk, $request->value);
    }

    public function updateCheckoutThemeUrl(Request $request){
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
        return $this->merchant->updateCheckoutThemeUrl($request->pk, $request->value);
    }

    public function updateMinLimit(Request $request){
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
        return $this->merchant->updateMinLimit($request->pk, $request->value);
    }

    public function updateMaxLimit(Request $request){
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
        return $this->merchant->updateMaxLimit($request->pk, $request->value);
    }

    public function resetMerchantAccountPassword(Request $request){
        $validator = Validator::make($request->all(), [
            'merchant_id' => 'required'
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        if(!(new AccessControl())->hasAccessModule(AccessModule::MERCHANT_MODIFY)) {
            return response()->json(['status' => false, 'message' => AccessModule::ACCESS_DENIED])->setStatusCode(400);
        }
        return $this->merchant->resetMerchantAccountPassword($request->merchant_id);
    }

    public function viewDashboardLogs(Request $request){
        $validator = Validator::make($request->all(), [
            'merchant_id' => 'required',
            'filter_data' => 'nullable',
            'limit' => 'required',
            'page_no' => 'required',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        if(!(new AccessControl())->hasAccessModule(AccessModule::MERCHANT_VIEW)) {
            return response()->json(['status' => false, 'message' => AccessModule::ACCESS_DENIED])->setStatusCode(400);
        }
        return $this->merchant->viewDashboardLogs($request->merchant_id, $request->filter_data, $request->limit, $request->page_no);
    }

    public function viewStatement(Request $request){
        $validator = Validator::make($request->all(), [
            'merchant_id' => 'required',
            'filter_data' => 'nullable',
            'limit' => 'required',
            'page_no' => 'required',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        if(!(new AccessControl())->hasAccessModule(AccessModule::MERCHANT_VIEW)) {
            return response()->json(['status' => false, 'message' => AccessModule::ACCESS_DENIED])->setStatusCode(400);
        }
        return $this->merchant->viewStatement($request->merchant_id, $request->filter_data, $request->limit, $request->page_no);
    }
    public function GetPendingSettlement(Request $request){
        $validator = Validator::make($request->all(), [
            'merchant_id' => 'required'
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        if(!(new AccessControl())->hasAccessModule(AccessModule::MERCHANT_RELEASE_SETTLEMENT)) {
            return response()->json(['status' => false, 'message' => AccessModule::ACCESS_DENIED])->setStatusCode(400);
        }
        return $this->merchant->getPendingSettlement($request->merchant_id);
    }

    public function viewMerchantWhitelistIps(Request $request){
        $validator = Validator::make($request->all(), [
            'merchant_id' => 'required'
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        if(!(new AccessControl())->hasAccessModule(AccessModule::MERCHANT_VIEW)) {
            return response()->json(['status' => false, 'message' => AccessModule::ACCESS_DENIED])->setStatusCode(400);
        }
        return $this->merchant->viewMerchantWhitelistIps($request->merchant_id);
    }
    public function AddMerchantSettlement(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'merchant_id' => 'required',
            'release_amount' => 'required'
        ]);
        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        if(!(new AccessControl())->hasAccessModule(AccessModule::MERCHANT_RELEASE_SETTLEMENT)) {
          return response()->json(['status' => false, 'message' => AccessModule::ACCESS_DENIED])->setStatusCode(400);
        }
        return $this->merchant->AddMerchantSettlement($request->merchant_id,$request->release_amount);

    }
    public function addManualPayout(Request $request) {
        $validator = Validator::make($request->all(), [
            'merchant_id' => 'required',
            'payout_amount' => 'required',
            'payout_fees' => 'required',
            'payout_associate_fees' => 'required',
            'bank_holder' => 'required',
            'account_number' => 'required',
            'ifsc_code' => 'required',
            'bank_rrn' => 'required',
            'remarks' => 'nullable',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        if(!(new AccessControl())->hasAccessModule(AccessModule::MERCHANT_ADD_PAY_IN_OUT)) {
            return response()->json(['status' => false, 'message' => AccessModule::ACCESS_DENIED])->setStatusCode(400);
        }
        return $this->merchant->addManualPayout(
            $request->merchant_id,
            $request->payout_amount,
            $request->payout_fees,
            $request->payout_associate_fees,
            $request->bank_holder,
            $request->account_number,
            $request->ifsc_code,
            $request->bank_rrn,
            $request->remarks
        );
    }

    public function addManualPayIn(Request $request) {
        $validator = Validator::make($request->all(), [
            'merchant_id' => 'required',
            'amount' => 'required',
            'utr_ref' => 'required',
            'remark' => 'required',
            'transaction_date' => 'required',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        if(!(new AccessControl())->hasAccessModule(AccessModule::MERCHANT_ADD_PAY_IN_OUT)) {
            return response()->json(['status' => false, 'message' => AccessModule::ACCESS_DENIED])->setStatusCode(400);
        }
        return $this->merchant->addManualPayIn(
            $request->merchant_id,
            $request->amount,
            $request->utr_ref,
            $request->remark,
            $request->transaction_date,
            $request->withfee
        );
    }

    public function getMerchantPayin(){
        return $this->merchant->getMerchantPayin();
    }

}
