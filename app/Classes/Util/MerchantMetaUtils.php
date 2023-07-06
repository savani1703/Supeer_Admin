<?php

namespace App\Classes\Util;

use App\Classes\Util\PgMeta\PgMetaHelper;
use App\Models\Management\MerchantDetails;
use App\Models\Management\MerchantPaymentMeta;
use App\Models\Management\MerchantPayoutMeta;
use App\Models\Management\PgRouter;
use App\Models\PaymentManual\AvailableBank;

class MerchantMetaUtils
{

    public function getMerchantPayInMeta($merchantId, $filterData)
    {
        try {
            $merchantPayInMeta = (new MerchantPaymentMeta())->getPayInMeta($merchantId, $filterData);
            if(isset($merchantPayInMeta)) {
                $merchantPayInMeta = $this->parseMerchantPayInMeta($merchantPayInMeta);
                return response()->json([
                    "status" => true,
                    "message" => "Data Retrieved",
                    "data" => $merchantPayInMeta,
                ]);
            }
            $error['status'] = false;
            $error['message'] = "Merchant Payment Meta Not Found";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function getMerchantPayoutMeta($merchantId, $filterData)
    {
        try {
            $merchantPayoutMetas = (new MerchantPayoutMeta())->getPayoutMeta($merchantId, $filterData);
            $merchantPayoutMetas=json_decode(json_encode($merchantPayoutMetas),false);
            foreach ($merchantPayoutMetas as $merchantPayoutMeta) {
                $pgModule = (new PgMetaHelper())->getPgMetaModel("payout", $merchantPayoutMeta->pg_name);
                if(isset($pgModule)) {
                    $meta = (new $pgModule)->getMetaById($merchantPayoutMeta->pg_id);
                    $merchantPayoutMeta->pglabel="N/a";
                    $merchantPayoutMeta->pgavailable_balance=0;
                    if(isset($meta)) {
                        $merchantPayoutMeta->pglabel =$meta->label;
                        $merchantPayoutMeta->pgavailable_balance =$meta->available_balance;
                    }
                }
            }
            if(isset($merchantPayoutMetas)) {
                return response()->json([
                    "status" => true,
                    "message" => "Data Retrieved",
                    "data" => $merchantPayoutMetas,
                ]);
            }
            $error['status'] = false;
            $error['message'] = "Merchant Payout Meta Not Found";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function updatePayInMetaStatus($merchantId, $pgName, $id, $pgId, $status)
    {
        try {
            $merchantPayInMeta = (new MerchantPaymentMeta())->updatePayInMetaStatus($merchantId, $pgName, $id, $pgId, $status);
            if($merchantPayInMeta) {
                SupportUtils::logs('MERCHANT PAYIN META',"PAYIN Meta Status Updated, MID: $merchantId, PG: $pgName, ID: $id, META_ID: $pgId, STATUS: $status");
                return response()->json([
                    "status" => true,
                    "message" => "Data Retrieved",
                ]);
            }
            $error['status'] = false;
            $error['message'] = "Merchant Payment Meta Not Found";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function updatePayoutMetaStatus($merchantId, $pgName, $id, $pgId, $status)
    {
        try {
            $merchantPayoutMeta = (new MerchantPayoutMeta())->updatePayoutMetaStatus($merchantId, $pgName, $id, $pgId, $status);
            if($merchantPayoutMeta) {
                SupportUtils::logs('MERCHANT PAYOUT META',"PAYOUT Meta Status Updated, MID: $merchantId, PG: $pgName, ID: $id, META_ID: $pgId, STATUS: $status");
                return response()->json([
                    "status" => true,
                    "message" => "Data Retrieved",
                ]);
            }
            $error['status'] = false;
            $error['message'] = "Merchant Payment Meta Not Found";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function updatePayInMetaMinLimit($merchantId, $pgName, $id, $pgId, $minLimit)
    {
        try {
            $merchantPayInMeta = (new MerchantPaymentMeta())->updatePayInMetaMinLimit($merchantId, $pgName, $id, $pgId, $minLimit);
            if(isset($merchantPayInMeta)) {
                SupportUtils::logs('MERCHANT PAYIN META',"PAYIN Meta Min Limit Updated, MID: $merchantId, PG: $pgName, ID: $id, META_ID: $pgId, MIN_LIMIT: $minLimit");
                return response()->json([
                    "status" => true,
                    "message" => "Data Retrieved",
                    "data" => $merchantPayInMeta,
                ]);
            }
            $error['status'] = false;
            $error['message'] = "Merchant Payment Meta Not Found";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function updatePayInMetaMaxLimit($merchantId, $pgName, $id, $pgId, $maxLimit)
    {
        try {
            $merchantPayInMeta = (new MerchantPaymentMeta())->updatePayInMetaMaxLimit($merchantId, $pgName, $id, $pgId, $maxLimit);
            if(isset($merchantPayInMeta)) {
                SupportUtils::logs('MERCHANT PAYIN META',"PAYIN Meta Max Limit Updated, MID: $merchantId, PG: $pgName, ID: $id, META_ID: $pgId, MAX_LIMIT: $maxLimit");
                return response()->json([
                    "status" => true,
                    "message" => "Data Retrieved",
                    "data" => $merchantPayInMeta,
                ]);
            }
            $error['status'] = false;
            $error['message'] = "Merchant Payment Meta Not Found";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function updatePayInMetaDailyLimit($merchantId, $pgName, $id, $pgId, $dailyLimit)
    {
        try {
            $merchantPayInMeta = (new MerchantPaymentMeta())->updatePayInMetaDailyLimit($merchantId, $pgName, $id, $pgId, $dailyLimit);
            if(isset($merchantPayInMeta)) {
                SupportUtils::logs('MERCHANT PAYIN META',"PAYIN Meta Daily Limit Updated, MID: $merchantId, PG: $pgName, ID: $id, META_ID: $pgId, DAILY_LIMIT: $dailyLimit");
                return response()->json([
                    "status" => true,
                    "message" => "Data Retrieved",
                    "data" => $merchantPayInMeta,
                ]);
            }
            $error['status'] = false;
            $error['message'] = "Merchant Payment Meta Not Found";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function updatePayInMetaLevel($merchantId, $pgName, $id, $pgId, $levelKey, $status)
    {
        try {
            $merchantPayInMeta = (new MerchantPaymentMeta())->updatePayInMetaLevel($merchantId, $pgName, $id, $pgId, $levelKey, $status);
            if(isset($merchantPayInMeta)) {
                SupportUtils::logs('MERCHANT PAYIN META',"PAYIN Meta Level Updated, MID: $merchantId, PG: $pgName, ID: $id, META_ID: $pgId, $levelKey: $status");
                return response()->json([
                    "status" => true,
                    "message" => "Data Retrieved",
                    "data" => $merchantPayInMeta,
                ]);
            }
            $error['status'] = false;
            $error['message'] = "Merchant Payment Meta Not Found";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function deletePayInMeta($merchantId, $pgName, $id, $pgId)
    {
        try {
            $merchantPayInMeta = (new MerchantPaymentMeta())->deletePayInMeta($merchantId, $pgName, $id, $pgId);
            if(isset($merchantPayInMeta)) {
                SupportUtils::logs('MERCHANT PAYIN META',"PAYIN Meta Deleted, MID: $merchantId, PG: $pgName, ID: $id, META_ID: $pgId");
                return response()->json([
                    "status" => true,
                    "message" => "Data Retrieved",
                    "data" => $merchantPayInMeta,
                ]);
            }
            $error['status'] = false;
            $error['message'] = "Merchant Payment Meta Not Found";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function getAvailablePaymentMeta($merchantId)
    {
        try {

            $pgRouter = (new PgRouter())->getAllPgMetaModels();

            $availableAutoPaymentMeta = [];
            $availableManualPaymentMeta = [];

            if(isset($pgRouter)) {
                $merchantPaymentMeta = (new MerchantPaymentMeta())->getAllPayInMeta($merchantId);
                foreach ($pgRouter as $pgMetaModule) {
                    if(isset($pgMetaModule->payin_meta_router)) {
                        try {

                            if(class_exists($pgMetaModule->payin_meta_router)) {
                                $pgMeta = (new $pgMetaModule->payin_meta_router)->getAllActivePgMeta($merchantId);
                                if (isset($pgMeta)) {
                                    foreach ($pgMeta as $_pgMeta) {
                                        $availableMetaMethod = [];
                                        $allowedMethods = isset($_pgMeta->available_method) ? explode(",", $_pgMeta->available_method) : [];
                                        if (sizeof($allowedMethods) > 0) {
                                            foreach ($allowedMethods as $_allowedMethods) {
                                                if (isset($merchantPaymentMeta)) {
                                                    if ($merchantPaymentMeta->where("pg_id", $_pgMeta->account_id)->where("payment_method", $_allowedMethods)->where("is_delete", "!=", 1)->count() < 1) $availableMetaMethod[] = $_allowedMethods;
                                                } else {
                                                    $availableMetaMethod[] = $_allowedMethods;
                                                }
                                            }
                                        }
                                        if (sizeof($availableMetaMethod) > 0) {
                                            if (strcmp($pgMetaModule->pg_type, PgType::AUTO) === 0) {
                                                $availableAutoPaymentMeta[] = [
                                                    'account_id' => $_pgMeta->account_id,
                                                    'label' => $_pgMeta->label,
                                                    'merchant_id' => $_pgMeta->merchant_id,
                                                    'pg_name' => $pgMetaModule->pg,
                                                    'is_seamless' => $_pgMeta->is_seamless,
                                                    'pg_type' => $pgMetaModule->pg_type,
                                                    'methods' => $availableMetaMethod
                                                ];
                                            }

                                            if (strcmp($pgMetaModule->pg_type, PgType::MANUAL) === 0) {
                                                $availableManualPaymentMeta[] = [
                                                    'account_id' => $_pgMeta->account_id,
                                                    'label' => $_pgMeta->label,
                                                    'merchant_id' => $_pgMeta->merchant_id,
                                                    'pg_name' => $pgMetaModule->pg,
                                                    'is_seamless' => $_pgMeta->is_seamless,
                                                    'pg_type' => $pgMetaModule->pg_type,
                                                    'methods' => $availableMetaMethod,
                                                    'account_number' => $_pgMeta->account_number,
                                                    'upi_id' => $_pgMeta->upi_id,
                                                    'bank_name' => $_pgMeta->bank_name,
                                                ];
                                            }
                                        }
                                        unset($allowedMethods);
                                    }
                                }
                            }
                        }catch (\Exception $exception)
                        {

                        }
                    }
                }
            }
            return response()->json([
                "status" => true,
                "message" => "Data Retrieved",
                "data" => [
                    "auto" => $availableAutoPaymentMeta,
                    "manual" => $availableManualPaymentMeta,
                ],
            ]);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function addPayInMetaToMerchantCollection($merchantId, $pgName, $pgId, $paymentMethods, $levelsData)
    {
        try {

            if(!(new MerchantDetails())->checkMerchantId($merchantId)) {
                $error['status'] = false;
                $error['message'] = "Invalid Merchant";
                return response()->json($error)->setStatusCode(400);
            }

            $pgRouter = (new PgRouter())->getRouterByPg($pgName);
            if(isset($pgRouter)) {
                if(isset($pgRouter->payin_meta_router) && !empty($pgRouter->payin_meta_router)) {
                    $pgMeta = (new $pgRouter->payin_meta_router)->getPayInMeta($pgId);
                    if(isset($pgMeta)) {
                        if($pgMeta->is_active) {
                            foreach ($paymentMethods as $paymentMethod) {

                                $level1 = 0;
                                $level2 = 0;
                                $level3 = 0;
                                $level4 = 0;

                                foreach ($levelsData as $levelData) {
                                    if($levelData == 1) $level1 = 1;
                                    if($levelData == 2) $level2 = 1;
                                    if($levelData == 3) $level3 = 1;
                                    if($levelData == 4) $level4 = 1;
                                }

                                if(!isset($pgMeta->available_method) || empty($pgMeta->available_method)) {
                                    $error['status'] = false;
                                    $error['message'] = "No Available Method in provided PG Meta";
                                    return response()->json($error)->setStatusCode(400);
                                }
                                $availableMethod = explode(",", $pgMeta->available_method);
                                if(!in_array($paymentMethod, $availableMethod)) {
                                    $error['status'] = false;
                                    $error['message'] = "$paymentMethod is Not Available $pgName($pgId)";
                                    return response()->json($error)->setStatusCode(400);
                                }

                                $checkMetaIsAvailableInMerchantMeta =
                                    (new MerchantPaymentMeta())->checkMetaIsExists($merchantId, $pgName, $pgId, $paymentMethod);

                                if($checkMetaIsAvailableInMerchantMeta) {
                                    if((new MerchantPaymentMeta())->updateMerchantCollectionMeta(
                                        $merchantId,
                                        $pgName,
                                        $pgId,
                                        $paymentMethod
                                    )) {
                                        SupportUtils::logs('MERCHANT PAYIN META',"PAYIN Meta Status Updated, MID: $merchantId, PG: $pgName, METHOD: $paymentMethod, META_ID: $pgId");
                                        $result['status'] = true;
                                        $result['message'] = "Meta Updated";
                                        return response()->json($result)->setStatusCode(200);
                                    }
                                } else {
                                    $isSeamless = isset($pgMeta->is_seamless) ? $pgMeta->is_seamless : false;
                                    if((new MerchantPaymentMeta())->addMerchantCollectionMeta(
                                        $merchantId,
                                        $pgName,
                                        $pgId,
                                        $paymentMethod,
                                        $isSeamless,
                                        $pgRouter->pg_type,
                                        $level1,
                                        $level2,
                                        $level3,
                                        $level4,
                                    )) {
                                        $levelDataString = json_encode($levelsData);
                                        SupportUtils::logs('MERCHANT PAYIN META',"PAYIN Meta Added, MID: $merchantId, PG: $pgName, METHOD: $paymentMethod, META_ID: $pgId, PG_TYPE: $pgRouter->pg_type, LEVEL: $levelDataString, IS_SEAMLESS: $isSeamless");
                                    }
                                }
                            }
                            $result['status'] = true;
                            $result['message'] = "Meta Added";
                            return response()->json($result)->setStatusCode(200);
                        }
                    }
                }
            }
            $error['status'] = false;
            $error['message'] = "Error while add Meta";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
//            $error['message'] = $ex->getMessage();
            $error['message'] = "Internal Server Error";
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function getAvailablePayoutMeta($merchantId)
    {
        try {
            $pgRouter = (new PgRouter())->getAllPgMetaModels();

            $availablePayoutMeta = [];

            if(isset($pgRouter)) {

                $merchantPayoutMeta = (new MerchantPayoutMeta())->getAllPayoutMeta($merchantId);

                foreach ($pgRouter as $pgMetaModule) {
                    if(isset($pgMetaModule->payout_meta_router)) {
                        $pgMeta = (new $pgMetaModule->payout_meta_router)->getAllActivePgMeta();
                        if(isset($pgMeta)) {
                            foreach ($pgMeta as $_pgMeta) {
                                if(isset($merchantPayoutMeta)) {
                                    if($merchantPayoutMeta->where("pg_id", $_pgMeta->account_id)->count() < 1) {
                                        $availablePayoutMeta[] = [
                                            'account_id'    => $_pgMeta->account_id,
                                            'label'         => $_pgMeta->label,
                                            'merchant_id'   => $_pgMeta->merchant_id,
                                            'pg_name'       => $pgMetaModule->pg,
                                        ];
                                    }
                                } else {
                                    $availablePayoutMeta[] = [
                                        'account_id'    => $_pgMeta->account_id,
                                        'label'         => $_pgMeta->label,
                                        'merchant_id'   => $_pgMeta->merchant_id,
                                        'pg_name'       => $pgMetaModule->pg,
                                    ];
                                }

                            }
                        }
                    }
                }
            }
            if(sizeof($availablePayoutMeta) > 0) {
                return response()->json([
                    "status" => true,
                    "message" => "Data Retrieved",
                    "data" => $availablePayoutMeta,
                ]);
            }
            $error['status'] = false;
            $error['message'] = "Payout Meta Not Found";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function addPayInMetaToMerchantWithdrawal($merchantId, $pgName, $pgId)
    {
        try {
            if(!(new MerchantDetails())->checkMerchantId($merchantId)) {
                $error['status'] = false;
                $error['message'] = "Invalid Merchant";
                return response()->json($error)->setStatusCode(400);
            }

            $pgRouter = (new PgRouter())->getRouterByPg($pgName);
            if(isset($pgRouter)) {
                if(isset($pgRouter->payout_meta_router) && !empty($pgRouter->payout_meta_router)) {
                    $pgMeta = (new $pgRouter->payout_meta_router)->getPayoutMetaById($pgId);
                    if(isset($pgMeta)) {
                        if($pgMeta->is_active) {
                            $checkMetaIsAvailableInMerchantMeta =
                                (new MerchantPayoutMeta())->checkMetaIsExists($merchantId, $pgName, $pgId);

                            if($checkMetaIsAvailableInMerchantMeta) {
                                if((new MerchantPayoutMeta())->updateMerchantWithdrawalMeta(
                                    $merchantId,
                                    $pgName,
                                    $pgId,
                                    $pgMeta->label
                                )) {
                                    SupportUtils::logs('MERCHANT PAYOUT META',"PAYOUT Meta Updated, MID: $merchantId, PG: $pgName, META_ID: $pgId");
                                    $result['status'] = true;
                                    $result['message'] = "Meta Updated";
                                    return response()->json($result)->setStatusCode(200);
                                }
                            } else {
                                if((new MerchantPayoutMeta())->addMerchantCollectionMeta(
                                    $merchantId,
                                    $pgName,
                                    $pgId,
                                    $pgMeta->label
                                )) {
                                    SupportUtils::logs('MERCHANT PAYOUT META',"PAYOUT Meta Added, MID: $merchantId, PG: $pgName, META_ID: $pgId");
                                    $result['status'] = true;
                                    $result['message'] = "Meta Added";
                                    return response()->json($result)->setStatusCode(200);
                                }
                            }
                        }
                    }
                }
            }
            $error['status'] = false;
            $error['message'] = "Error while add Meta";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }

    private function parseMerchantPayInMeta($merchantMeta) {
        try {
            $merchantMeta = $this->parseWithPgLable($merchantMeta);
            $parseMeta = [];
            if(isset($merchantMeta)) {
                foreach ($merchantMeta as $_merchantMeta) {
                    $parseMeta[$_merchantMeta->pg_name][] = $_merchantMeta;
                }
            }
            if(sizeof($parseMeta) > 0) {
                return $parseMeta;
            }
            return null;
        } catch (\Exception $ex) {
            return null;
        }
    }

    private function parseWithPgLable($merchantPayInMetas)
    {
        try {
            if(isset($merchantPayInMetas)) {
                foreach ($merchantPayInMetas as $key => $merchantPayInMeta) {
                    if(isset($merchantPayInMeta->pg_id) && isset($merchantPayInMeta->pg_name)) {
                        $pgRouter = (new PgRouter())->getRouterByPg($merchantPayInMeta->pg_name);
                        if(isset($pgRouter)) {
                            if(isset($pgRouter->payin_meta_router)) {
                                $pgMeta = (new $pgRouter->payin_meta_router)->getMetaForTransactionById($merchantPayInMeta->pg_id);
                                if(isset($pgMeta)) {
                                    $merchantPayInMetas[$key]['pg_label'] = $pgMeta->label;
                                    if(isset($pgMeta->account_number)) $merchantPayInMetas[$key]['account_number'] = $pgMeta->account_number;
                                    if(isset($pgMeta->upi_id)) $merchantPayInMetas[$key]['upi_id'] = $pgMeta->upi_id;
                                    if(isset($pgMeta->ifsc_code)) $merchantPayInMetas[$key]['ifsc_code'] = $pgMeta->ifsc_code;
                                    if(isset($pgMeta->bank_name)) $merchantPayInMetas[$key]['bank_name'] = $pgMeta->bank_name;
                                    if(isset($pgMeta->is_account_flow_active)) $merchantPayInMetas[$key]['is_account_flow_active'] = $pgMeta->is_account_flow_active;
                                }
                            }
                        }
                    }
                }
            }
        } catch (\Exception $ex) {

        }
        return $merchantPayInMetas;
    }

    public function updateMerchantAllMeta($merchantId, $action, $bankName)
    {
        try {
            $updateVal  = strcmp($action,'ACTIVE') === 0 ? 1 : 0;
            $value      = strcmp($action,'ACTIVE') === 0 ? 0 : 1;

            $merchantDetails = (new MerchantPaymentMeta())->getMetaMerchantDetails($merchantId, $value);
            if(!isset($merchantDetails) || empty($merchantDetails)){
                return response()->json(['status' => false,'message' => 'meta already '.strtolower($action)])->setStatusCode(400);
            }
            foreach ($merchantDetails as $_merchantDetails){
                $isValidated = (new AvailableBank())->checkDetailsIsValidate($_merchantDetails->pg_id, $bankName);
                if($isValidated){
                    (new MerchantPaymentMeta())->updateMerchantAllMeta($merchantId, $updateVal);
                }
            }
            $message = strcmp($action,'ACTIVE') === 0 ? 'Merchant Meta Activated Successfully' : 'Merchant Meta DeActivated Successfully';
            return response()->json(['status' => true,'message' => $message])->setStatusCode(200);

        }catch (\Exception $ex){
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function updatePayInMetaPerLimit($merchantId, $pgName, $id, $pgId, $perLimit)
    {
        try {
            $merchantPayInMeta = (new MerchantPaymentMeta())->updatePayInMetaPerLimit($merchantId, $pgName, $id, $pgId, $perLimit);
            if(isset($merchantPayInMeta)) {
                SupportUtils::logs('MERCHANT PAYIN META',"PAYIN Meta Daily Limit Updated, MID: $merchantId, PG: $pgName, ID: $id, META_ID: $pgId, PER_LIMIT: $perLimit");
                return response()->json([
                    "status" => true,
                    "message" => "Data Retrieved",
                    "data" => $merchantPayInMeta,
                ]);
            }
            $error['status'] = false;
            $error['message'] = "Merchant Payment Meta Not Found";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }

}
