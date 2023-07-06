<?php

namespace App\Http\Controllers;

use App\Classes\Util\AWSUtils;
use App\Classes\Util\DigiPayUtil;
use App\Classes\Util\ManualPayoutUtils;
use App\Classes\Util\PaymentStatus;
use App\Classes\Util\PgName;
use App\Classes\Util\SupportUtils;
use App\Constant\PayoutStatus;
use App\Exports\Bank\IDFCManualPayoutExport;
use App\Exports\Bank\YesManualPayoutExport;
use App\Imports\UsersImport;
use App\Models\Management\BatchTransfer;
use App\Models\Management\CustomerLevel;
use App\Models\Management\MerchantPayoutMeta;
use App\Models\Management\Payout;
use App\Models\Management\PayoutConfig;
use App\Models\Management\PayoutCustomerLevel;
use App\Models\Management\PayoutManualReconciliation;
use App\Models\Management\PayoutWhiteListClient;
use App\Models\Management\PgRouter;
use App\Models\Management\TransactionEvent;
use App\Models\PaymentManual\BankConfig;
use App\Models\PaymentManual\ICICI\ICICIPayoutMeta;
use App\Models\PaymentManual\IDFC\IDFCPayoutMeta;
use App\Models\PaymentManual\PayoutManualRecon;
use App\Models\PaymentManual\YES\YesPayoutMeta;
use App\Plugin\AccessControl\Utils\AccessControlUtils;
use App\Plugin\ManualPayout\ManualPayout;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ManualPayoutController extends Controller
{
    public function manualPayoutView() {
        $activeMerchant = (new PayoutWhiteListClient())->getActiveManualMerchantPayoutList();
        $availableBank = $this->getAvailableBankMeta();
        return view('manual-payout.manual-payout')->with("availableBank", $availableBank)->with("activeMerchant", $activeMerchant);
    }

    public function manualPayoutReconView() {
        $availableBank = $this->getAvailableBankMeta();
        return view('payout-manual-recon')->with("availableBank", $availableBank);
    }

    public function markAsProcessing(Request $request) {
        $validator = Validator::make($request->all(), [
            'bank_id' => 'required',
            'merchant_id' => 'required',
            'logic_key' => 'required|in:less_than,greater_than,equal',
            'logic_amount' => 'required',
            'sheet_key' => 'required|in:count,amount',
            'sheet_value' => 'required',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }

        $merchantId = $request->merchant_id;

        $bankTransferConfig = (new PayoutConfig())->loadConfig();
        if(!isset($bankTransferConfig) || empty($bankTransferConfig)) {
            return response()->json(['status' => false, 'message' => "System Error Please contact to developer"])->setStatusCode(400);
        }

        $logicKey   = $request->logic_key;
        $logicAmount = floatval($request->logic_amount);

        $sheetKey = $request->sheet_key;
        $sheetValue = $request->sheet_value;

        $remainBatchAmountLimit = 0;
        $isAmountFlow = false;
        if(strcmp($sheetKey,'amount') === 0){
            $remainBatchAmountLimit = floatval($request->sheet_value);
            $isAmountFlow = true;
        }

        $requestDataId = explode("#", $request->bank_id);

        $metaId = $requestDataId[1];
        $pgName = $requestDataId[0];

        $payoutMeta = $this->getPayoutMeta($metaId, $pgName);

        if(!isset($payoutMeta) || empty($payoutMeta)) {
            return response()->json(['status' => false, 'message' => "Invalid Account Settings"])->setStatusCode(400);
        }

        $debitAccount = $payoutMeta->debit_account;

        $isActive  = (new PayoutWhiteListClient())->checkMerchantIsActive($merchantId);
        if(!$isActive){
            return response()->json(['status' => false, 'message' => "manual payout not active provided by merchant id"])->setStatusCode(400);
        }

        $payoutConfig = (new PayoutConfig())->loadConfig();
        if($payoutConfig->is_manual_level_active) {
            $isLevelFlow = false;
            if(strcmp($merchantId,'MID_3UOP4XZR4OO17D') === 0 || strcmp($merchantId,'MID_2TYKNS2KMZ25RZ') === 0 || strcmp($merchantId,'MID_HOM2ZHT1MEIXZX') === 0){
                $isLevelFlow = true;
            }
            if($isLevelFlow){
                $customerIds = (new Payout())->getPayoutCustomerIdByMid($merchantId);
                $eligibleCustomerIds = (new PayoutCustomerLevel())->getEligibleCustomerIDByMeta($customerIds, $metaId);
                $payoutListForManualPayout = (new Payout())->getPayoutRecordForManualPayoutByLevelFlow($logicAmount, $logicKey, $merchantId, $sheetKey, $sheetValue, $eligibleCustomerIds);
            }else{
                $payoutListForManualPayout = (new Payout())->getPayoutRecordForManualPayout($logicAmount, $logicKey, $merchantId, $sheetKey, $sheetValue);
            }
        }else{
            $payoutListForManualPayout = (new Payout())->getPayoutRecordForManualPayout($logicAmount, $logicKey, $merchantId, $sheetKey, $sheetValue);
        }

        if(!isset($payoutListForManualPayout)) {
            return response()->json(['status' => false, 'message' => "No Payout Record found for manual transfer"])->setStatusCode(400);
        }

        $batchId = strtoupper(Str::random(10));
        if((new BatchTransfer())->checkBatchIsExist($batchId)) {
            return response()->json(['status' => false, 'message' => "System Error Please try again after some time"])->setStatusCode(400);
        }

        $totalBatchAmount = 0;
        $totalBatchRecord = 0;

        // payout record mark for processing
        foreach ($payoutListForManualPayout as $_payoutListForManualPayout) {
            if($isAmountFlow) {
                if ($remainBatchAmountLimit > $_payoutListForManualPayout->payout_amount) {
                    $totalBatchAmount = $totalBatchAmount + floatval($_payoutListForManualPayout->payout_amount);
                    $remainBatchAmountLimit = $remainBatchAmountLimit - floatval($_payoutListForManualPayout->payout_amount);
                    (new Payout())->markAsProcessingForManualPayout($_payoutListForManualPayout->payout_id, $batchId, $debitAccount, $metaId, $pgName);
                    $totalBatchRecord++;
                } else {
                    break;
                }
            }else{
                $totalBatchAmount = $totalBatchAmount + floatval($_payoutListForManualPayout->payout_amount);
                (new Payout())->markAsProcessingForManualPayout($_payoutListForManualPayout->payout_id, $batchId, $debitAccount, $metaId, $pgName);
                $totalBatchRecord++;
            }
        }

        // validate amount and record before adding batch
        $dbBatchRecord = (new Payout())->getBatchCountAndSum($batchId);
        if(isset($dbBatchRecord)) {
            if(
                $totalBatchRecord > 0 &&
                intval($dbBatchRecord->total_batch_record) == intval($totalBatchRecord) &&
                floatval($dbBatchRecord->total_batch_amount) == floatval($totalBatchAmount)
            ) {
                if((new BatchTransfer())->addManualPayoutBatch($batchId, $metaId, $pgName, $debitAccount, $totalBatchAmount, $totalBatchRecord)) {
                    return response()->json(['status' => true, 'message' => "$totalBatchRecord payout added in batch"])->setStatusCode(200);
                }
            }
        }
        // revert to init
        (new Payout())->unMarkBatchPayout($batchId);
        (new BatchTransfer())->markAsUsed($batchId);
        return response()->json(['status' => false, 'message' => "error while add payout in batch"])->setStatusCode(400);
    }

    public function getManualPayoutList(Request $request) {
        $validator = Validator::make($request->all(), [
            'filter_data' => 'nullable|array',
            'page_no' => 'required',
            'limit' => 'required',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }

        $data = (new BatchTransfer())->getBatchTransferList($request->filter_data, $request->limit, $request->page_no);
        if(isset($data) && !empty($data)) {
            foreach ($data as $key => $_customerDetails) {
                if (isset($_customerDetails->batch_id) && !empty($_customerDetails->batch_id)) {
                    $batchId = $_customerDetails->batch_id;
                    $merchantList = (new Payout())->getMerchantByBatchId($batchId);
                    if($merchantList){
                        $data[$key]['merchantList'] = $merchantList;
                    }
                    $totalReturn = (new PayoutManualRecon())->getCountByBatchId($batchId);
                    if($totalReturn){
                        $data[$key]['total_return'] = $totalReturn;
                    }
                    $data[$key]['Count']= (new Payout())->getPayoutStatue($batchId);
                }
            }
        }


        foreach ($data as $key => $_data){
            if($_data->bank_name && $_data->pg_id){
                if(strcmp($_data->bank_name,PgName::ICICI) === 0){
                    $accountHolderName = (new ICICIPayoutMeta())->getAccountHolderName($_data->pg_id);
                    if($accountHolderName){
                        $data[$key]['account_holder'] = $accountHolderName;
                    }
                }
                if(strcmp($_data->bank_name,PgName::IDFC) === 0){
                    $accountHolderName = (new IDFCPayoutMeta())->getAccountHolderName($_data->pg_id);
                    if($accountHolderName){
                        $data[$key]['account_holder'] = $accountHolderName;
                    }
                }  if(strcmp($_data->bank_name,PgName::YES) === 0){
                    $accountHolderName = (new YesPayoutMeta())->getAccountHolderName($_data->pg_id);
                    if($accountHolderName){
                        $data[$key]['account_holder'] = $accountHolderName;
                    }
                }
            }
        }
        if(isset($data)) {
            $result['status'] = true;
            $result['message'] = 'Batch Details Retrieve successfully';
            $result['current_page'] = $data->currentPage();
            $result['last_page'] = $data->lastPage();
            $result['is_last_page'] = !$data->hasMorePages();
            $result['total_item'] = $data->total();
            $result['current_item_count'] = $data->count();
            $result['data'] = $data->items();
            return response()->json($result)->setStatusCode(200);
        }
        $error['status'] = false;
        $error['message'] = "Batch Not found";
        return response()->json($error)->setStatusCode(400);
    }

    public function downloadBatchFile(Request $request) {
        $validator = Validator::make($request->all(), [
            'batch_id' => 'required'
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }

        $batchDetail = (new BatchTransfer())->getBatchById($request->batch_id);
        if(!isset($batchDetail)) {
            return response()->json(['status' => false, 'message' => "Invalid Batch id"])->setStatusCode(400);
        }

        $bankTransferMeta = $this->getPayoutMeta($batchDetail->pg_id, $batchDetail->bank_name);
        if(!isset($bankTransferMeta)) {
            $result['status'] = false;
            $result['message'] = 'Invalid Account Settings';
            return response()->json($result)->setStatusCode(400);
        }

        $notifyEmailId = null;
        if(strcmp($batchDetail->bank_name,'IDFC') === 0){
            if(!isset($bankTransferMeta->notify_email_id) || empty($bankTransferMeta->notify_email_id)){
                $result['status'] = false;
                $result['message'] = 'Notify Email id Not Found';
                return response()->json($result)->setStatusCode(400);
            }
            $notifyEmailId = $bankTransferMeta->notify_email_id;
        }

        if(isset($batchDetail->file_data) && !empty($batchDetail->file_data)) {
            if(strcmp($batchDetail->bank_name,'IDFC') === 0){
                $url = (new AWSUtils())->storeNewForPayout((new IDFCManualPayoutExport(json_decode($batchDetail->file_data,false),$notifyEmailId)), $batchDetail->file_name);
                if($url){
                    $result['status'] = true;
                    $result['message'] = 'Batch File Downloaded';
                    $result['data'] = [
                        "is_seamless" => true,
                        "file_data" => $url,
                        "file_name" => $batchDetail->file_name
                    ];
                    return response()->json($result)->setStatusCode(200);
                }else{
                    $result['status'] = false;
                    $result['message'] = 'Batch File Downloaded Failed';
                    return response()->json($result)->setStatusCode(400);
                }
            }
            /*if(strcmp($batchDetail->bank_name,PgName::YES) === 0){
                $collection = (new YesManualPayoutExport(json_decode($batchDetail->file_data,false)));
                $url = (new AWSUtils())->storeNewForPayout($collection, $batchDetail->file_name);
                if($url){
                    $result['status'] = true;
                    $result['message'] = 'Batch File Downloaded';
                    $result['data'] = [
                        "is_seamless" => true,
                        "file_data" => $url,
                        "file_name" => $batchDetail->file_name
                    ];
                    return response()->json($result)->setStatusCode(200);
                }else{
                    $result['status'] = false;
                    $result['message'] = 'Batch File Downloaded Failed';
                    return response()->json($result)->setStatusCode(400);
                }
            }*/
            $result['status'] = true;
            $result['message'] = 'Batch File Downloaded';
            $result['data'] = [
                "is_seamless" => false,
                "file_data" => base64_encode($batchDetail->file_data),
                "file_name" => $batchDetail->file_name
            ];
            return response()->json($result)->setStatusCode(200);
        }

        $payoutData = (new Payout())->getPayoutForBatchTransfer($batchDetail->batch_id);
        if(!isset($payoutData)) {
            $result['status'] = false;
            $result['message'] = 'No Payout Record Found';
            return response()->json($result)->setStatusCode(400);
        }

        $_preFix = $bankTransferMeta->label ? $bankTransferMeta->label : null;
        if($_preFix){
            $preFix = strtoupper(substr(str_replace(' ', '', $_preFix), 0, 2));
        }

        if(!isset($preFix) || empty($preFix)){
            $result['status'] = false;
            $result['message'] = 'something went wrong';
            return response()->json($result)->setStatusCode(400);
        }


        $generatedFileData = (new ManualPayout())->initPayout($batchDetail->batch_id, $batchDetail->bank_name, $bankTransferMeta, $payoutData);

        if(isset($generatedFileData)) {
            if(isset($generatedFileData->fileData)) {
                if((new Payout())->markAsPendingBatchPayout($batchDetail->batch_id)) {
                    (new BatchTransfer())->updateBatchFileData($batchDetail->batch_id, $generatedFileData->fileData, $generatedFileData->fileName);
                    $isSeamless = false;
                    $file_data  = null;
                    if(strcmp($batchDetail->bank_name,'IDFC') === 0){
                         //$this->markAsSuccessWithTempUtr($batchDetail->batch_id, $preFix);
                         $isSeamless = true;
                         $url = (new AWSUtils())->storeNewForPayout((new IDFCManualPayoutExport(json_decode(json_encode($generatedFileData->fileData)),$notifyEmailId)), $generatedFileData->fileName);
                         if($url){
                             $file_data = $url;
                         }
                    }/*else if (strcmp($batchDetail->bank_name,PgName::YES) === 0){
                        $collection = (new YesManualPayoutExport(json_decode(json_encode($generatedFileData->fileData))));
                        $url = (new AWSUtils())->storeNewForPayout($collection, $batchDetail->file_name);
                        if($url){
                            $file_data = $url;
                        }
                    }*/else{
                        $file_data = base64_encode($generatedFileData->fileData);
                    }

                    $result['status'] = true;
                    $result['message'] = 'Batch File Downloaded';
                    $result['data'] = [
                        "is_seamless" => $isSeamless,
                        "file_data" => $file_data,
                        "file_name" => $generatedFileData->fileName
                    ];
                    return response()->json($result)->setStatusCode(200);
                }
            }
        }

        $result['status'] = false;
        $result['message'] = 'Error while download file';
        return response()->json($result)->setStatusCode(400);
    }

    public function markAsSuccessWithTempUtr($batchId, $preFix){
        try {
            $payoutList  = (new Payout())->getTotalPayoutListByIdForIDFC($batchId);
            if(!$payoutList){
                return false;
            }
            foreach ($payoutList as $_payoutList){
                $tempUtr = DigiPayUtil::generateTempUtr($preFix);
                if($tempUtr){
                    (new Payout())->markAsSuccessWithTempUtrForIDFC($batchId, $_payoutList->payout_id, $tempUtr);
                }
            }

        }catch (\Exception $ex){
            return false;
        }
    }

    public function uploadStatusFile(Request $request) {
        $validator = Validator::make($request->all(), [
            'bank_id' => 'required',
            'bank_file' => 'required|file|mimes:txt',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }

        $requestDataId = explode("#", $request->bank_id);

        $metaId = $requestDataId[1];
        $pgName = $requestDataId[0];

        $bankPayoutMeta = $this->getPayoutMeta($metaId, $pgName);
        if(!isset($bankPayoutMeta)) {
            return response()->json(['status' => false, 'message' => "Invalid Account Settings"])->setStatusCode(400);
        }

        $fileContent = file_get_contents($request->bank_file);
        if(!isset($fileContent) || empty($fileContent)) {
            return response()->json(['status' => false, 'message' => "File Data is invalid or empty"])->setStatusCode(400);
        }

        $bankResponse = (new ManualPayout())->payoutStatus($pgName, $bankPayoutMeta, $fileContent);
        if(isset($bankResponse)) {
            if(isset($bankResponse->bankResponseData)) {
                return $this->updatePayoutData($bankResponse->bankResponseData);
            }
        }
        return response()->json(['status' => false, 'message' => "Invalid File Data"])->setStatusCode(400);
    }

    public function getBatchTransferConfig() {
        $configData = (new PayoutConfig())->loadConfig();
        if(isset($configData)) {
            return response()->json(['status' => false, 'message' => "Config Retrieved", 'data' => $configData])->setStatusCode(200);
        }
        return response()->json(['status' => false, 'message' => "No Config Found"])->setStatusCode(400);
    }

    public function getInitPayoutAmount() {
        try {
            $initPayoutAmount = (new Payout())->getInitPayoutAmount();
            return response()->json([
                "status" => true,
                "message" => "Payout Init Amount Received",
                "payout_amount" => round(floatval($initPayoutAmount), 2)
            ]);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Error while Init Payout Amount";
            Log::error('Error in Exception', [
                'class' => __CLASS__,
                'function' => __METHOD__,
                'file' => $ex->getFile(),
                'line_no' => $ex->getLine(),
                'error_message' => $ex->getMessage(),
            ]);
            return response()->json($error)->setStatusCode(500);
        }
    }

    private function updatePayoutData($bankResponseObject) {
        $responseData = [
            "success" => [],
            "error" => []
        ];
        foreach ($bankResponseObject as $pgResponse) {
            if(isset($pgResponse->payoutId)) {
                $payoutDetails = (new Payout())->getPayoutDetailsForBatchTransferStatusUpdate($pgResponse->payoutId); // ToDo
                if(isset($payoutDetails)) {
                    $updateData = $this->getPayoutUpdateData($pgResponse);
                    if((new Payout())->updatePayoutStatusForBatchTransferStatusUpdate($pgResponse->payoutId, $updateData)) { // ToDo
                        if(strcmp($pgResponse->status, PayoutStatus::FAILED) === 0) {
                            $check = (new TransactionEvent())->checkWebhookSent($pgResponse->payoutId);
                            if($check){
                                (new TransactionEvent())->markAsResend($pgResponse->payoutId);
                            }
                        }
                        $responseData["success"][] = $pgResponse->payoutId;
                    } else {
                        $responseData["error"][] = $pgResponse->payoutId;
                    }
                } else {
                    $responseData["error"][] = $pgResponse->payoutId;
                }
            }
        }

        $successOperationPayoutDetails = [];

        if(sizeof($responseData['success']) > 0) {
            foreach ($responseData['success'] as $successPayoutOperationId) {
                $successOperationPayoutDetails[] = (new Payout())->getPayoutDetailById($successPayoutOperationId); // ToDo
            }
        }
        return response()->json([
            'status' => true,
            'message' => "Operation Success",
            'data' => [
                "success" => $successOperationPayoutDetails,
                "error" => $responseData['error']
            ]
        ])->setStatusCode(200);
    }

    private function getPayoutUpdateData($data) {
        $updateData['pg_res'] = $data->pgResponse;
        $updateData['pg_response_code'] = $data->pgResponseCode;
        $updateData['pg_response_msg'] = $data->pgResponseMessage;
        $updateData['bank_rrn'] = $data->bankUtr;
        $updateData['pg_ref_id'] = $data->pgPayoutId;
        $updateData['payout_status'] = PayoutStatus::PENDING;
        if(strcmp($data->status, PayoutStatus::SUCCESS) === 0) {
            $updateData['payout_status'] = PayoutStatus::SUCCESS;
            $updateData['success_at'] = Carbon::now();
        }
        if(strcmp($data->status, PayoutStatus::FAILED) === 0) {
            $updateData['payout_status'] = PayoutStatus::FAILED;
            $updateData['is_webhook_called'] = 0;
        }
        return $updateData;
    }

    private function getPayoutMeta($pgId, $bankName) {
        $pgRouters = (new PgRouter())->getRouterByPg($bankName);
        if(isset($pgRouters)) {
            if(isset($pgRouters->payout_meta_router)) {
                return (new $pgRouters->payout_meta_router)->getPayoutMetaById($pgId);
            }
        }
        return null;
    }

    private function getAvailableBankMeta() {
        $availableBankMeta = [];

        $pgRouters = (new PgRouter())->getRouterForManualPayout();
        if(isset($pgRouters)) {
            foreach ($pgRouters as $pgRouter) {
                if(isset($pgRouter->payout_meta_router)) {
                    $payoutMeta = (new $pgRouter->payout_meta_router)->getAllActivePgMeta();
                    if(isset($payoutMeta)) {
                        foreach ($payoutMeta as $_payoutMeta) {
                            $availableBankMeta[] = [
                                "account_id" => $_payoutMeta->account_id,
                                "bank_name" => $pgRouter->pg,
                                "account_label" => $_payoutMeta->label,
                                "debit_account" => $_payoutMeta->debit_account,
                            ];
                        }
                    }
                }
            }
        }

        if(sizeof($availableBankMeta) > 0) {
            return $availableBankMeta;
        }

        return null;
    }

    public function markAsUsed(Request $request){
        $validator = Validator::make($request->all(), [
            'batch_id' => 'required'
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        $result =  (new BatchTransfer())->markAsUsed($request->batch_id);
        if($result){
            return response()->json(['status' => true, 'message' => 'mark as complete successfully'])->setStatusCode(200);
        }
        return response()->json(['status' => false, 'message' => 'failed to mark'])->setStatusCode(400);
    }

    public function getInitPayoutDetails(Request $request){
        $validator = Validator::make($request->all(), [
            'merchant_id' => 'required'
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }

        $merchantId = $request->merchant_id;

        $totalInitAmount =  (new Payout())->getTotalInitAmountPayoutById($merchantId);
        $totalInitCount  =  (new Payout())->getTotalInitCountPayoutById($merchantId);
        $data = [
            'total_init_amount' => $totalInitAmount,
            'total_init_count'  => $totalInitCount,
        ];

        return response()->json(['status' => true, 'message' => 'details retrieved successfully','data' => $data])->setStatusCode(200);
    }

    public function getLogicalInitPayoutDetails(Request $request){
        $validator = Validator::make($request->all(), [
            'merchant_id' => 'required',
            'login_key' => 'required|in:less_than,greater_than,equal',
            'logic_amount' => 'required',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }

        $merchantId = $request->merchant_id;
        $loginKey = $request->login_key;
        $logicAmount = $request->logic_amount;

        $totalInitAmount =  (new Payout())->getLogicalInitAmountPayoutById($merchantId, $logicAmount, $loginKey);
        $totalInitCount  =  (new Payout())->getLogicalInitPayoutCountById($merchantId, $logicAmount, $loginKey);
        $data = [
            'total_logical_init_count'  => $totalInitCount,
            'total_logical_init_amount'  => $totalInitAmount
        ];

        return response()->json(['status' => true, 'message' => 'details retrieved successfully','data' => $data])->setStatusCode(200);
    }

    public function getManualPayoutRecon(Request $request) {
        $validator = Validator::make($request->all(), [
            'filter_data' => 'nullable|array',
            'page_no' => 'required',
            'limit' => 'required',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }

        $data = (new PayoutManualRecon())->getPayoutManualRecon($request->filter_data, $request->page_no, $request->limit);
        if(isset($data)) {
            $payoutData = $this->parseWithPgLable($data->items());
            $result['data'] = $payoutData;
            $result['status'] = true;
            $result['message'] = 'Payout Manual Recon Details Retrieve successfully';
            $result['current_page'] = $data->currentPage();
            $result['last_page'] = $data->lastPage();
            $result['is_last_page'] = !$data->hasMorePages();
            $result['total_item'] = $data->total();
            $result['current_item_count'] = $data->count();

            return response()->json($result)->setStatusCode(200);
        }
        $error['status'] = false;
        $error['message'] = "Payout Manual Recon Not found";
        return response()->json($error)->setStatusCode(400);
    }

    private function parseWithPgLable($payouts)
    {
        try {
            if(isset($payouts)) {
                foreach ($payouts as $key => $payout) {
                    if(isset($payout->payoutDetails->meta_id) && isset($payout->payoutDetails->pg_name)) {
                        $pgRouter = (new PgRouter())->getRouterByPg($payout->payoutDetails->pg_name);
                        if(isset($pgRouter)) {
                            if(isset($pgRouter->payout_meta_router)) {
                                $pgMeta = (new $pgRouter->payout_meta_router)->getMetaForPayoutByMetaId($payout->payoutDetails->meta_id);
                                if(isset($pgMeta)) {
                                    $payouts[$key]['payoutDetails']['pg_label'] = $pgMeta->label;
                                }
                            }
                        }
                    }
                }
            }
        } catch (\Exception $ex) {

        }
        return $payouts;
    }

    public function getManualPayoutReconSummary(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'filter_data' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                $error = $validator->errors()->first();
                return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
            }

            $filterData = $request->filter_data;
            $filterData = DigiPayUtil::parseFilterData($filterData);

            $totalAmount = 0;
            $totalReleased = 0;
            $totalUnReleased = 0;

            (new PayoutManualRecon())->getAmount($filterData, $totalAmount, $totalReleased, $totalUnReleased);

            $payoutData = [
                'total_amount' => $totalAmount,
                'total_released' => $totalReleased,
                'total_un_released' => $totalUnReleased,
            ];

            $result['status'] = true;
            $result['message'] = 'Payout Manual Recon Summery Retrieve successfully';
            $result['payout_summary'] = $payoutData;
            return response()->json($result)->setStatusCode(200);

        }catch (\Exception $ex){
            $error['status'] = false;
            $error['message'] = "Error while get Payout Manual Recon Summery";
            Log::error('Error in Exception', [
                'class' => __CLASS__,
                'function' => __METHOD__,
                'file' => $ex->getFile(),
                'line_no' => $ex->getLine(),
                'error_message' => $ex->getMessage(),
            ]);
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function processReconManualPayout(Request $request){
        $validator = Validator::make($request->all(), [
            'bank_id' => 'required',
            'merchant_id' => 'required',
            'sheet_key' => 'required|in:count',
            'sheet_value' => 'required',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }

        $sheetValue = $request->sheet_value;

        $requestDataId = explode("#", $request->bank_id);

        $metaId = $requestDataId[1];
        $pgName = $requestDataId[0];

        $payoutMeta = $this->getPayoutMeta($metaId, $pgName);

        if(!isset($payoutMeta) || empty($payoutMeta)) {
            return response()->json(['status' => false, 'message' => "Invalid Account Settings"])->setStatusCode(400);
        }

        $debitAccount = $payoutMeta->debit_account;

        $payoutListForManualPayout = (new PayoutManualReconciliation())->getReconManualPayout($sheetValue);

        if(!isset($payoutListForManualPayout)) {
            return response()->json(['status' => false, 'message' => "No Payout Record found for manual transfer"])->setStatusCode(400);
        }

        $batchId = strtoupper(Str::random(10));
        if((new BatchTransfer())->checkBatchIsExist($batchId)) {
            return response()->json(['status' => false, 'message' => "System Error Please try again after some time"])->setStatusCode(400);
        }

        $totalBatchAmount = 0;
        $totalBatchRecord = 0;

        // payout record mark for processing
        foreach ($payoutListForManualPayout as $_payoutListForManualPayout) {
            $payoutDetails = (new Payout())->getPayoutDetailById($_payoutListForManualPayout->payout_id);
            if(isset($payoutDetails) && !empty($payoutDetails)){
                $totalBatchAmount = $totalBatchAmount + floatval($payoutDetails->payout_amount);
                (new Payout())->markAsProcessingForManualPayout($payoutDetails->payout_id, $batchId, $debitAccount, $metaId, $pgName);
                $totalBatchRecord++;
            }
        }

        // validate amount and record before adding batch
        $dbBatchRecord = (new Payout())->getBatchCountAndSum($batchId);
        if(isset($dbBatchRecord)) {
            if(
                $totalBatchRecord > 0 &&
                intval($dbBatchRecord->total_batch_record) == intval($totalBatchRecord) &&
                floatval($dbBatchRecord->total_batch_amount) == floatval($totalBatchAmount)
            ) {
                if((new BatchTransfer())->addManualPayoutBatch($batchId, $metaId, $pgName, $debitAccount, $totalBatchAmount, $totalBatchRecord)) {
                    return response()->json(['status' => true, 'message' => "$totalBatchRecord payout added in batch"])->setStatusCode(200);
                }
            }
        }
        // revert to init
        (new Payout())->unMarkBatchPayout($batchId);
        (new BatchTransfer())->markAsUsed($batchId);
        return response()->json(['status' => false, 'message' => "error while add payout in batch"])->setStatusCode(400);
    }

    public function ManualPayoutController(Request $request) {
        $validator = Validator::make($request->all(), [
            'is_manual_level_active' => 'required|in:0,1',
        ]);
        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        $updateData = ['is_manual_level_active' => $request->is_manual_level_active];
        $payoutConfig = (new PayoutConfig())->loadConfig();
        if((new PayoutConfig())->updateConfig($payoutConfig->id, $updateData)) {
            $logData = json_encode($updateData);
            SupportUtils::logs('PAYOUT',"Payout Config Updated, Update Data: $logData");
            $result['status'] = true;
            $result['message'] = 'Payout Configuration Update successfully';
            $result['data'] = $payoutConfig;
            return response()->json($result)->setStatusCode(200);
        }
        $error['status'] = false;
        $error['message'] = "Payout Manual Recon Not found";
        return response()->json($error)->setStatusCode(400);
    }
}
