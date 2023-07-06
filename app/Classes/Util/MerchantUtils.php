<?php

namespace App\Classes\Util;

use App\Models\Management\MerchantBalance;
use App\Models\Management\MerchantDashboardLogs;
use App\Models\Management\MerchantDetails;
use App\Models\Management\MerchantIpWhiteList;
use App\Models\Management\MerchantPaymentMeta;
use App\Models\Management\Payout;
use App\Models\Management\PgRouter;
use App\Models\Management\Transactions;
use App\Models\PaymentManual\AvailableBank;
use App\Plugin\AccessControl\AccessControl;
use App\Plugin\AccessControl\Utils\AccessModule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class MerchantUtils
{
    public function addMerchant($merchantEmail, $merchantName)
    {
        try {
            $merchantId = DigiPayUtil::generateMerchantId();
            $publicKey = DigiPayUtil::generateRandomString(50);
            $tempPassword = DigiPayUtil::generateRandomString();
            $hashPublicKey = Hash::make($publicKey);
            $hashPassword = Hash::make($tempPassword);

            $merchantEmailExist = (new MerchantDetails())->checkMerchantEmail($merchantEmail);

            if ($merchantEmailExist) {
                return response()->json([
                    'status' => false,
                    'message' => 'provided Merchant email is already associated with us'
                ])->setStatusCode(400);
            }

            $merchantIdExist = (new MerchantDetails())->checkMerchantId($merchantId);
            if ($merchantIdExist) {
                return response()->json([
                    'status' => false,
                    'message' => 'Error while register new merchant'
                ])->setStatusCode(400);
            }

            if ((new MerchantDetails())->addMerchant($merchantId, $hashPublicKey, $merchantEmail, $hashPassword, $merchantName)) {
                SupportUtils::logs('MERCHANT', "New Merchant Added, MID: $merchantId, EMAIL: $merchantEmail, NAME: $merchantName");
                $merchantData = [
                    'merchant_id' => $merchantId,
                    'public_key' => $publicKey,
                    'temp_password' => $tempPassword
                ];
                return response()->json([
                    'status' => true,
                    'message' => 'Merchant  Add successfully',
                    'data' => $merchantData
                ])->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Error while register new merchant";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function getMerchants($filterData, $limit, $pageNo)
    {
        try {
            $merchants = (new MerchantDetails())->getMerchants($filterData, $limit, $pageNo);
            if (isset($merchants)) {
                $result = DigiPayUtil::withPaginate($merchants);
                $result["config"] = [];
                if ((new AccessControl())->hasAccessModule(AccessModule::MERCHANT_MODIFY)) {
                    $result["config"] = (new MerchantDetails())->renderConfig()['editable_columns'];
                    $result["config"][] = "is_action_allowed";
                    if ((new AccessControl())->hasAccessModule(AccessModule::MERCHANT_PAYIN_META_VIEW)) $result["config"][] = "is_payin_meta_allowed";
                    if ((new AccessControl())->hasAccessModule(AccessModule::MERCHANT_PAYOUT_META_VIEW)) $result["config"][] = "is_payout_meta_allowed";
                }
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Merchants  Not found";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function updateAccountStatus($merchantId, $accountStatus)
    {
        try {
            $updateData = [
                "account_status" => $accountStatus
            ];

            $checkIsPayInFeesSet = false;
            $checkIsPayInMetaSet = false;

            if (strcmp($accountStatus, AccountStatus::Approved) === 0) {
                $merchantDetails = (new MerchantDetails())->getMerchantDetails($merchantId);
                if (isset($merchantDetails)) {
                    $checkIsPayInFeesSet = floatval($merchantDetails->pay_in_auto_fees) > 0 &&
                        floatval($merchantDetails->pay_in_manual_fees) > 0;
                }
                $checkIsPayInMetaSet = (new MerchantPaymentMeta())->checkActiveMetaIsExists($merchantId);
            } else {
                $checkIsPayInFeesSet = true;
                $checkIsPayInMetaSet = true;
            }

            if ($checkIsPayInFeesSet && $checkIsPayInMetaSet) {
                if ((new MerchantDetails())->updateMerchantData($merchantId, $updateData)) {
                    SupportUtils::logs('MERCHANT', "Merchant Account Update, MID: $merchantId, ACCOUNT_STATUS: $accountStatus");
                    return response()->json([
                        "status" => true,
                        "message" => "Merchant Account Status Updated",
                    ])->setStatusCode(200);
                }
            }

            $error['status'] = false;
            $error['message'] = "Failed to update merchant account status";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function updateIsPayoutEnable($merchantId, $accountStatus)
    {
        try {
            $updateData = [
                "is_payout_enable" => $accountStatus
            ];
            if ((new MerchantDetails())->updateMerchantData($merchantId, $updateData)) {
                SupportUtils::logs('MERCHANT', "Merchant Account Update, MID: $merchantId, IS_PAYOUT_ENABLE: $accountStatus");
                return response()->json([
                    "status" => true,
                    "message" => "Merchant Payout Enable Status Updated",
                ])->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Failed to Update Merchant Payout Enable Status";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function updateIsPayInEnable($merchantId, $accountStatus)
    {
        try {
            $updateData = [
                "is_payin_enable" => $accountStatus
            ];
            if ((new MerchantDetails())->updateMerchantData($merchantId, $updateData)) {
                SupportUtils::logs('MERCHANT', "Merchant Account Update, MID: $merchantId, IS_PAYIN_ENABLE: $accountStatus");
                return response()->json([
                    "status" => true,
                    "message" => "Merchant PayIn Enable Status Updated",
                ])->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Failed to Update Merchant PayIn Enable Status";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function updateIsFailedWebhookRequired($merchantId, $isRequiredPaymentFailedWebhook)
    {
        try {
            $updateData = [
                "is_required_payment_failed_webhook" => $isRequiredPaymentFailedWebhook
            ];
            if ((new MerchantDetails())->updateMerchantData($merchantId, $updateData)) {
                SupportUtils::logs('MERCHANT', "Merchant Account Update, MID: $merchantId, IS_REQUIRED_PAYMENT_FAILED_WEBHOOK: $isRequiredPaymentFailedWebhook");
                return response()->json([
                    "status" => true,
                    "message" => "Merchant Failed Webhook Enable Status Updated",
                ])->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Failed to Update Merchant Failed Webhook Enable Status ";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function updateIsEnableBrowserCheck($merchantId, $isEnableBrowserCheck)
    {
        try {
            $updateData = [
                "is_enable_browser_check" => $isEnableBrowserCheck
            ];
            if ((new MerchantDetails())->updateMerchantData($merchantId, $updateData)) {
                SupportUtils::logs('MERCHANT', "Merchant Account Update, MID: $merchantId, IS_REQUIRED_PAYMENT_FAILED_WEBHOOK: $isEnableBrowserCheck");
                return response()->json([
                    "status" => true,
                    "message" => "Merchant Browser Check Status Updated",
                ])->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Failed to Update Merchant Browser Check Status";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function updateIsEnablePayoutBalanceCheck($merchantId, $isBalanceCheckEnable)
    {
        try {
            $updateData = [
                "is_balance_check_enable" => $isBalanceCheckEnable
            ];
            if ((new MerchantDetails())->updateMerchantData($merchantId, $updateData)) {
                SupportUtils::logs('MERCHANT', "Merchant Account Update, MID: $merchantId, IS_BALANCE_CHECK_ENABLE: $isBalanceCheckEnable");
                return response()->json([
                    "status" => true,
                    "message" => "Merchant Payout Balance Check Enable Status Updated",
                ])->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Failed to Update Merchant Payout Balance Check Enable Status";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function updateIsDashboardPayoutEnable($merchantId, $isDashboardPayoutEnable)
    {
        try {
            $updateData = [
                "is_dashboard_payout_enable" => $isDashboardPayoutEnable
            ];
            if ((new MerchantDetails())->updateMerchantData($merchantId, $updateData)) {
                SupportUtils::logs('MERCHANT', "Merchant Account Update, MID: $merchantId, IS_DASHBOARD_PAYOUT_ENABLE: $isDashboardPayoutEnable");
                return response()->json([
                    "status" => true,
                    "message" => "Merchant Dashboard Payout Enable Status Updated",
                ])->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Failed to Update Merchant Dashboard Payout Enable Status";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function updateIsAutoApprovedPayout($merchantId, $isAutoApprovedPayout)
    {
        try {
            $updateData = [
                "is_auto_approved_payout" => $isAutoApprovedPayout
            ];
            if ((new MerchantDetails())->updateMerchantData($merchantId, $updateData)) {
                SupportUtils::logs('MERCHANT', "Merchant Account Update, MID: $merchantId, IS_AUTO_APPROVED_PAYOUT: $isAutoApprovedPayout");
                return response()->json([
                    "status" => true,
                    "message" => "Merchant Payout Auto Approved Status Updated",
                ])->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Failed to Update Merchant Payout Auto Approved Status";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function updateShowCustomerDetailsPage($merchantId, $haveCustomerDetails)
    {
        try {
            $updateData = [
                "have_customer_details" => $haveCustomerDetails
            ];
            if ((new MerchantDetails())->updateMerchantData($merchantId, $updateData)) {
                SupportUtils::logs('MERCHANT', "Merchant Account Update, MID: $merchantId, HAVE_CUSTOMER_DETAILS: $haveCustomerDetails");
                return response()->json([
                    "status" => true,
                    "message" => "Merchant Show Customer Page Status Updated",
                ])->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Failed to Update Show Customer Page Status";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function updateIsCustomerDetailsRequired($merchantId, $haveCustomerDetails)
    {
        try {
            $updateData = [
                "have_customer_details_in_api" => $haveCustomerDetails
            ];
            if ((new MerchantDetails())->updateMerchantData($merchantId, $updateData)) {
                SupportUtils::logs('MERCHANT', "Merchant Account Update, MID: $merchantId, HAVE_CUSTOMER_DETAILS_IN_API: $haveCustomerDetails");
                return response()->json([
                    "status" => true,
                    "message" => "Merchant Customer Details Required Status Updated",
                ])->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Failed to Update Customer Details Required Status";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function updateIsSettlementEnable($merchantId, $isSettlementEnable)
    {
        try {
            $updateData = [
                "is_settlement_enable" => $isSettlementEnable
            ];
            if ((new MerchantDetails())->updateMerchantData($merchantId, $updateData)) {
                SupportUtils::logs('MERCHANT', "Merchant Account Update, MID: $merchantId, IS_SETTLEMENT_ENABLE: $isSettlementEnable");
                return response()->json([
                    "status" => true,
                    "message" => "Merchant Settlement Status Updated",
                ])->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Failed to Update Merchant Settlement Status ";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function updatePayInWebhook($merchantId, $webhookUrl)
    {
        try {
            $updateData = [
                "webhook_url" => $webhookUrl
            ];
            if ((new MerchantDetails())->updateMerchantData($merchantId, $updateData)) {
                SupportUtils::logs('MERCHANT', "Merchant Account Update, MID: $merchantId, WEBHOOK_URL: $webhookUrl");
                return response()->json([
                    "status" => true,
                    "message" => "Merchant PayIn Webhook Updated",
                ])->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Failed to Update Merchant PayIn Webhook";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function updatePayoutWebhook($merchantId, $payoutWebhookUrl)
    {
        try {
            $updateData = [
                "payout_webhook_url" => $payoutWebhookUrl
            ];
            if ((new MerchantDetails())->updateMerchantData($merchantId, $updateData)) {
                SupportUtils::logs('MERCHANT', "Merchant Account Update, MID: $merchantId, PAYOUT_WEBHOOK_URL: $payoutWebhookUrl");
                return response()->json([
                    "status" => true,
                    "message" => "Merchant Payout Webhook Updated",
                ])->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Failed to Update Merchant Payout Webhook";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function updatePayInAutoFees($merchantId, $payInAutoFees)
    {
        try {
            $updateData = [
                "pay_in_auto_fees" => $payInAutoFees
            ];
            if ((new MerchantDetails())->updateMerchantData($merchantId, $updateData)) {
                SupportUtils::logs('MERCHANT', "Merchant Account Update, MID: $merchantId, PAY_IN_AUTO_FEES: $payInAutoFees");
                return response()->json([
                    "status" => true,
                    "message" => "Merchant Is PayIn Auto Fees Updated",
                ])->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Failed to Update Merchant PayIn Auto Fees";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function updatePayInManualFees($merchantId, $payInManualFees)
    {
        try {
            $updateData = [
                "pay_in_manual_fees" => $payInManualFees
            ];
            if ((new MerchantDetails())->updateMerchantData($merchantId, $updateData)) {
                SupportUtils::logs('MERCHANT', "Merchant Account Update, MID: $merchantId, PAY_IN_MANUAL_FEES: $payInManualFees");
                return response()->json([
                    "status" => true,
                    "message" => "Merchant Is PayIn Auto Fees Updated",
                ])->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Failed to Update Merchant PayIn Auto Fees";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function updatePayoutFees($merchantId, $payoutFees)
    {
        try {
            $updateData = [
                "payout_fees" => $payoutFees
            ];
            if ((new MerchantDetails())->updateMerchantData($merchantId, $updateData)) {
                SupportUtils::logs('MERCHANT', "Merchant Account Update, MID: $merchantId, PAYOUT_FEES: $payoutFees");
                return response()->json([
                    "status" => true,
                    "message" => "Merchant Payout Fees Updated",
                ])->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Failed to Update Merchant Payout Fees";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function updatePayoutAssociateFees($merchantId, $payoutAssociateFees)
    {
        try {
            $updateData = [
                "payout_associate_fees" => $payoutAssociateFees
            ];
            if ((new MerchantDetails())->updateMerchantData($merchantId, $updateData)) {
                SupportUtils::logs('MERCHANT', "Merchant Account Update, MID: $merchantId, PAYOUT_ASSOCIATE_FEES: $payoutAssociateFees");
                return response()->json([
                    "status" => true,
                    "message" => "Merchant Payout Associate Fees Updated",
                ])->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Failed to Update Merchant Payout Associate Fees";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function updatePayInAssociateFees($merchantId, $payInAssociateFees)
    {
        try {
            $updateData = [
                "payin_associate_fees" => $payInAssociateFees
            ];
            if ((new MerchantDetails())->updateMerchantData($merchantId, $updateData)) {
                SupportUtils::logs('MERCHANT', "Merchant Account Update, MID: $merchantId, PAYIN_ASSOCIATE_FEES: $payInAssociateFees");
                return response()->json([
                    "status" => true,
                    "message" => "Merchant PayIn Associate Fees Updated",
                ])->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Failed to Update Merchant PayIn Associate Fees";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function updateSettlementCycle($merchantId, $settlementCycle)
    {
        try {
            $updateData = [
                "settlement_cycle" => $settlementCycle
            ];
            if ((new MerchantDetails())->updateMerchantData($merchantId, $updateData)) {
                SupportUtils::logs('MERCHANT', "Merchant Account Update, MID: $merchantId, SETTLEMENT_CYCLE: $settlementCycle");
                return response()->json([
                    "status" => true,
                    "message" => "Merchant Settlement Cycle Updated",
                ])->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Failed Update Merchant Settlement Cycle";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function updatePayoutDelayedTime($merchantId, $payoutDelayedTime)
    {
        try {
            $updateData = [
                "payout_delayed_time" => $payoutDelayedTime
            ];
            if ((new MerchantDetails())->updateMerchantData($merchantId, $updateData)) {
                SupportUtils::logs('MERCHANT', "Merchant Account Update, MID: $merchantId, PAYOUT_DELAYED_TIME: $payoutDelayedTime");
                return response()->json([
                    "status" => true,
                    "message" => "Merchant Is Payout Delay Time Updated",
                ])->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Failed Update Merchant Is Payout Delay Time ";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function updateOldUsersDays($merchantId, $oldUsersDays)
    {
        try {
            $updateData = [
                "old_users_days" => $oldUsersDays
            ];
            if ((new MerchantDetails())->updateMerchantData($merchantId, $updateData)) {
                SupportUtils::logs('MERCHANT', "Merchant Account Update, MID: $merchantId, OLD_USERS_DAYS: $oldUsersDays");
                return response()->json([
                    "status" => true,
                    "message" => "Merchant Old User Day Updated",
                ])->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Failed Update Merchant Old User Day";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function updateCheckoutColor($merchantId, $checkoutColor)
    {
        try {
            $updateData = [
                "checkout_color" => $checkoutColor
            ];
            if ((new MerchantDetails())->updateMerchantData($merchantId, $updateData)) {
                SupportUtils::logs('MERCHANT', "Merchant Account Update, MID: $merchantId, CHECKOUT_COLOR: $checkoutColor");
                return response()->json([
                    "status" => true,
                    "message" => "Merchant Checkout Theme Color Updated",
                ])->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Failed Update Merchant Checkout Theme Color";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function updateCheckoutThemeUrl($merchantId, $checkoutThemeUrl)
    {
        try {
            $updateData = [
                "checkout_theme_url" => $checkoutThemeUrl
            ];
            if ((new MerchantDetails())->updateMerchantData($merchantId, $updateData)) {
                SupportUtils::logs('MERCHANT', "Merchant Account Update, MID: $merchantId, CHECKOUT_THEME_URL: $checkoutThemeUrl");
                return response()->json([
                    "status" => true,
                    "message" => "Merchant Checkout Theme URL Updated",
                ])->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Failed Update Merchant Checkout Theme URL ";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function updateMinLimit($merchantId, $minLimit)
    {
        try {
            $updateData = [
                "min_transaction_limit" => $minLimit
            ];
            if ((new MerchantDetails())->updateMerchantData($merchantId, $updateData)) {
                SupportUtils::logs('MERCHANT', "Merchant Account Update, MID: $merchantId, MIN_TRANSACTION_LIMIT: $minLimit");
                return response()->json([
                    "status" => true,
                    "message" => "Merchant Transaction Min Limit Updated",
                ])->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Failed to update merchant Transaction Min Limit";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function updateMaxLimit($merchantId, $maxLimit)
    {
        try {
            $updateData = [
                "max_transaction_limit" => $maxLimit
            ];
            if ((new MerchantDetails())->updateMerchantData($merchantId, $updateData)) {
                SupportUtils::logs('MERCHANT', "Merchant Account Update, MID: $merchantId, MAX_TRANSACTION_LIMIT: $maxLimit");
                return response()->json([
                    "status" => true,
                    "message" => "Merchant Transaction Max Limit Updated",
                ])->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Failed to update merchant Transaction Max Limit";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function addManualPayout(
        $merchantId,
        $payoutAmount,
        $payoutFees,
        $payoutAssociateFees,
        $bankHolder,
        $accountNumber,
        $ifscCode,
        $bankRrn,
        $remarks
    )
    {
        try {
            if (!(new MerchantDetails())->checkMerchantId($merchantId)) {
                $error['status'] = false;
                $error['message'] = "Invalid Merchant";
                return response()->json($error)->setStatusCode(400);
            }

            $payoutId = "P" . DigiPayUtil::generateRandomNumber(11);
            if ((new Payout())->addManualPayout(
                $payoutId,
                $merchantId,
                $payoutAmount,
                $payoutFees,
                $payoutAssociateFees,
                $bankHolder,
                $accountNumber,
                $ifscCode,
                $bankRrn,
                $remarks)) {
                SupportUtils::logs('MERCHANT', "Merchant Manual Payout Added, MID: $merchantId, PAYOUT_ID: $payoutId");
                $result['status'] = true;
                $result['message'] = "Payout Added";
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Error while add payout";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(400);
        }
    }

    public function resetMerchantAccountPassword($merchantId)
    {
        try {

            $newTempPassword = DigiPayUtil::generateRandomString(20);

            $updateData = [
                "password" => Hash::make($newTempPassword),
                "is_password_temp" => true,
            ];

            if ((new MerchantDetails())->updateMerchantData($merchantId, $updateData)) {
                SupportUtils::logs('MERCHANT', "Merchant Account Password Reset, MID: $merchantId");
                return response()->json([
                    "status" => true,
                    "message" => "Merchant Password Reset",
                    "data" => [
                        "temp_password" => $newTempPassword
                    ],
                ])->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Failed to update merchant Transaction Min Limit";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function viewDashboardLogs($merchantId, $filterData, $limit, $pageNo)
    {
        try {
            $filterData = DigiPayUtil::parseFilterData($filterData);
            $merchantDashboardLogs = (new MerchantDashboardLogs())->getDahboardLogs($merchantId, $filterData, $limit, $pageNo);
            if (isset($merchantDashboardLogs)) {
                $result = DigiPayUtil::withPaginate($merchantDashboardLogs);
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Merchant Dashboard Logs Not Found";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function getMerchantPayin()
    {
        try {
            $merchantPayInMeta = (new MerchantPaymentMeta())->getReadPayInMeta();
            if (isset($merchantPayInMeta)) {
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


    private function parseMerchantPayInMeta($merchantMeta)
    {
        try {
            $merchantMeta = $this->parseWithPgLable($merchantMeta);
            $parseMeta = [];
            if (isset($merchantMeta)) {
                foreach ($merchantMeta as $_merchantMeta) {
                    $parseMeta[$_merchantMeta->pg_name][] = $_merchantMeta;
                }
            }
            if (sizeof($parseMeta) > 0) {
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
            if (isset($merchantPayInMetas)) {
                $today = \Carbon\Carbon::now()->format("Y-m-d");
                foreach ($merchantPayInMetas as $key => $merchantPayInMeta) {
                    if (isset($merchantPayInMeta->pg_id) && isset($merchantPayInMeta->pg_name)) {
                        $pgRouter = (new PgRouter())->getRouterByPg($merchantPayInMeta->pg_name);
                        $mname=(new MerchantDetails())->getMerchantDetails($merchantPayInMeta->merchant_id);
                        if (isset($pgRouter) && isset($mname)) {
                            if (isset($pgRouter->payin_meta_router) && isset($mname->merchant_name)) {
                                $pgMeta = (new $pgRouter->payin_meta_router)->getMetaForTransactionById($merchantPayInMeta->pg_id);
                                if (isset($pgMeta) && isset($mname->merchant_name)) {
                                    $merchantPayInMetas[$key]['pg_label'] = $pgMeta->label;
                                    $merchantPayInMetas[$key]['upi_id'] = $pgMeta->upi_id;
                                    $merchantPayInMetas[$key]['merchant_name'] = $mname->merchant_name;
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

    private function getPgName($merchantMeta){

    }

    public function viewStatement($merchantId, $filterData, $limit, $pageNo)
    {
        try {
            $filterData = DigiPayUtil::parseFilterData($filterData);
            $merchantStatement = (new MerchantBalance())->getStatement($merchantId, $filterData, $limit, $pageNo);
            if (isset($merchantStatement)) {
              $txns=   (new Transactions());
              $payts=   (new Payout());

                foreach ($merchantStatement as &$dt)
                {
                    //$startDate = Carbon::parse($dt->pay_date,"Asia/Kolkata")->format("Y-m-d 00:00:00");
                   // $endDate =  Carbon::parse($dt->pay_date,"Asia/Kolkata")->format("Y-m-d 23:59:59");
                    $startDate = Carbon::parse($dt->pay_date, "Asia/Kolkata")->setTimezone("UTC")->format("Y-m-d H:i:s");
                    $endDate = Carbon::parse($dt->pay_date, "Asia/Kolkata")->addDay()->subSecond()->setTimezone("UTC")->format("Y-m-d H:i:s");
                    $dt->payin_live= round( round( $txns->getPayinByDate($merchantId,$startDate,$endDate),2) -$dt->payin,2);
                    $dt->payout_live= round(  round( $payts->getPayoutByDate($merchantId,$startDate,$endDate),2)-$dt->payout,2);
                }
                $result = DigiPayUtil::withPaginate($merchantStatement);
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Merchant  Statement Not Found";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function viewMerchantWhitelistIps($merchantId)
    {
        try {
            $merchantWhiteListIps = (new MerchantIpWhiteList())->getWhiteListIps($merchantId);
            if (isset($merchantWhiteListIps)) {
                $result['status'] = true;
                $result['message'] = "Data Retrieved";
                $result['data'] = $merchantWhiteListIps;
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Merchant Dashboard Logs Not Found";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function addManualPayIn($merchantId, $amount, $utrRef, $pgResMsg, $transactionDate,$withfee)
    {
        try {
            if((new Transactions())->checkUtrIsUsed($utrRef)) {
                throw new \Exception("UTR is already used");
            }

            $merchantDetail = (new MerchantDetails())->getMerchantDetails($merchantId);

            if(!isset($merchantDetail)) {
                throw new \Exception("Invalid Merchant");
            }

            $transactionId = DigiPayUtil::generatePaymentId();
            $paymentAmount = $amount;
            $paymentFees=0;
            if(strcmp($withfee,'on')==0) {
                $paymentFees = DigiPayUtil::calculateFees($paymentAmount, $merchantDetail->pay_in_manual_fees);
            }
            $pgType = PgType::MANUAL;
            $pgRefId = $utrRef;
            $bankRrn = $utrRef;
            $merchantOrderId = $utrRef;
            $transactionDate=Carbon::parse($transactionDate)->addMinute()->toDateTimeString();
            $transactionDate = DigiPayUtil::TO_UTC($transactionDate);
            if((new Transactions())->addManualPayIn($merchantId, $transactionId, $paymentAmount, $paymentFees, $pgType, $pgRefId, $bankRrn, $merchantOrderId, $pgResMsg, $transactionDate)) {
                SupportUtils::logs('MERCHANT',"Merchant Manual PayIn Added, MID: $merchantId, TXN_ID: $transactionId");
                $result['status'] = true;
                $result['message'] = "manual payin added";
                return response()->json($result)->setStatusCode(200);
            }
            throw new \Exception("Error while add manual payin");
        }  catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = $ex->getMessage();
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function getPendingSettlement($merchantId)
    {
        try {
            $merchantBalance = (new MerchantBalance())->getMerchantBalanceByMid($merchantId);
            $merchantBalances = (new MerchantBalance())->getMerchantBalancesByMid($merchantId);
            if (isset($merchantBalance)) {
                $result['status'] = true;
                $result['message'] = "Merchant Data Retrieved";
                $result['databalances'] = $merchantBalances;
                $result['data'] = [
                    "PayoutBalance" => round((new MerchantBalance())->getMerchantLastClosingBalance($merchantBalance->merchant_id)),
                    "UnsettledBalance" =>round( $merchantBalance->un_settled_balance),
                ];
                return response()->json($result)->setStatusCode(200);
            }
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = $ex->getMessage();
            return response()->json($error)->setStatusCode(500);
        }
        $error['status'] = false;
        $error['message'] = "Merchant Data Not Found";
        return response()->json($error)->setStatusCode(400);
    }
    public function AddMerchantSettlement($merchantId,$amount)
    {
        try {
            $merchantBalance = (new MerchantBalance())->getMerchantBalanceByMid($merchantId);
            if (isset($merchantBalance)) {
                $stucktxns=(new Transactions())->getStuckTransactions();
                if(isset($stucktxns))
                {
                    foreach ($stucktxns as $txn)
                    {
                        $txn->created_at=Carbon::parse($txn->created_at)->addMinutes(2);
                        $txn->save();
                    }
                    $error['status'] = false;
                    $error['message'] = "Wait Some Time";
                    return response()->json($error)->setStatusCode(400);
                }
                if($merchantBalance->un_settled_balance < $amount)
                {
                    $error['status'] = false;
                    $error['message'] = "insufficient balance";
                    return response()->json($error)->setStatusCode(400);

                }else
                {
                    $releaseamount=$amount;
                    $merchantUnSettledRecords = (new MerchantBalance())->getMerchantUnsettledByMid($merchantId);
                    if($merchantUnSettledRecords) {
                        foreach ($merchantUnSettledRecords as $mer_bal)
                        {
                            if($amount>0) {
                                if ($mer_bal->un_settled > $amount) {
                                    $amount_pre_settle = $mer_bal->amount_pre_settle + $amount;
                                    (new MerchantBalance())->getMerchantReleaseByMidAndData($merchantId, $mer_bal->pay_date, $amount_pre_settle);
                                    break;
                                } else {
                                    $amount = $amount - $mer_bal->un_settled;
                                    (new MerchantBalance())->getMerchantReleaseByMidAndData($merchantId, $mer_bal->pay_date, $mer_bal->un_settled+$mer_bal->settled);
                                }
                            }
                        }
                        SupportUtils::logs('MERCHANT',"Merchant Settlement Add , MID: $merchantId, PAYOUT_ID: $amount");
                        $result['status'] = true;
                        $result['message'] = "Settlement Released:  " . $releaseamount;
                        $result['data'] = '';
                        return response()->json($result)->setStatusCode(200);
                    }
                }

               /* $result['status'] = true;
                $result['message'] = "Merchant Data Retrieved";
                $result['data'] = [
                    "PayoutBalance" => (new MerchantBalance())->getMerchantLastClosingBalance($merchantBalance->merchant_id),
                    "UnsettledBalance" => $merchantBalance->un_settled_balance,
                ];
                return response()->json($result)->setStatusCode(200);*/
            }
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = $ex->getMessage();
            return response()->json($error)->setStatusCode(500);
        }
        $error['status'] = false;
        $error['message'] = "Merchant Data Not Found";
        return response()->json($error)->setStatusCode(400);
    }
}
