<?php

namespace App\Plugin\AccessControl\Utils;

use App\Classes\Util\DigiPayUtil;
use App\Classes\Util\PgType;
use App\Models\Management\PgRouter;
use App\Models\Support\RoleAccessModule;
use App\Models\Support\SupportModule;
use App\Plugin\AccessControl\AccessControl;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class AccessControlUtils
{
    static function paymentPgType(): string
    {
        $isAutoTransactionPermissionAllowed = (new AccessControl())->hasAccessModule(AccessModule::TRANSACTION_AUTO_VIEW);
        $isManualTransactionPermissionAllowed = (new AccessControl())->hasAccessModule(AccessModule::TRANSACTION_MANUAL_VIEW);
        $pgTye = "";
        if(($isAutoTransactionPermissionAllowed && !$isManualTransactionPermissionAllowed)) {
            $pgTye = PgType::AUTO;
        }
        if((!$isAutoTransactionPermissionAllowed && $isManualTransactionPermissionAllowed)) {
            $pgTye = PgType::MANUAL;
        }
        if(($isAutoTransactionPermissionAllowed && $isManualTransactionPermissionAllowed)) {
            $pgTye = "ALL";
        }
        return $pgTye;
    }

    static function payoutPgType(): string
    {
        $isAutoPayoutPermissionAllowed = (new AccessControl())->hasAccessModule(AccessModule::PAYOUT_AUTO_VIEW);
        $isManualPayoutPermissionAllowed = (new AccessControl())->hasAccessModule(AccessModule::PAYOUT_MANUAL_VIEW);
        $pgTye = "";
        if(($isAutoPayoutPermissionAllowed && !$isManualPayoutPermissionAllowed)) {
            $pgTye = PgType::AUTO;
        }
        if((!$isAutoPayoutPermissionAllowed && $isManualPayoutPermissionAllowed)) {
            $pgTye = PgType::MANUAL;
        }
        if(($isAutoPayoutPermissionAllowed && $isManualPayoutPermissionAllowed)) {
            $pgTye = "ALL";
        }
        return $pgTye;
    }

    static function reconsOptions(): array
    {
        $options = [];
        if((new AccessControl())->hasAccessModule(AccessModule::TRANSACTION_RECONCILIATION)) $options[] = ["value" => "transaction_id", "label" => "Transaction"];
        if((new AccessControl())->hasAccessModule(AccessModule::PAYOUT_RECONCILIATION)) $options[] = ["value" => "payout_id", "label" => "Payout"];
        return $options;
    }

    static function renderSidebar() {
        $sessionId = md5(DigiPayUtil::getAuthUser());
        $sidebarSessionId = md5(DigiPayUtil::getAuthUser()."siderbar");

        if(Cache::has($sidebarSessionId)) {
           // dd($sidebarSessionId);
           // return Cache::get($sidebarSessionId);
        }

        $accessData = null;

        (new AccessControl())->storeSession(DigiPayUtil::getAuthUserRoleId());
        $accessData = Cache::get($sessionId);

        $sideBarData = [];
        $routeModule = (new SupportModule())->where("is_route_module", 1)->where('is_active',1)->get()->toArray();
        if(sizeof($routeModule) > 0) {
            foreach ($routeModule as $_routeModule) {
                $sideBarData[] = [
                    "name" => $_routeModule['module_name'],
                    "route" => $_routeModule['module_route'],
                    "is_child" => $_routeModule['is_child'],
                    "icon_class" => self::iconClass($_routeModule['module_name']),
                    "sorting" => self::sorting($_routeModule['module_name']),
                    "group" => self::groping($_routeModule['module_name']),
                ];
            }
        }

        if((new AccessControl())->hasAccessModule(AccessModule::PG_PAYIN_META_VIEW) || (new AccessControl())->hasAccessModule(AccessModule::PG_PAYOUT_META_VIEW)) {
            $pgRouter = (new PgRouter())->getAllPgMetaModels();
            if (isset($pgRouter)) {

                foreach ($pgRouter as $key => $pgMeta) {
                    $tempData = [];
                    $tempData['name'] = $pgMeta->pg;
                    $tempData['route'] = "";
                    $tempData['is_child'] = true;
                    $tempData['icon_class'] = "chevrons-right";
                    $tempData['group'] = "PG";
                    $tempData['sorting'] = 7;

                    if ((new AccessControl())->hasAccessModule(AccessModule::PG_PAYIN_META_VIEW)) {
                        if (isset($pgMeta->payin_meta_router)) {
                            $tempData["child"][] = [
                                "name" => "PayIn Meta",
                                "child_route" => "/payment-gateway/payin/{$pgMeta->pg}",
                            ];
                        }
                    }
                    if ((new AccessControl())->hasAccessModule(AccessModule::PG_PAYOUT_META_VIEW)) {
                        if (isset($pgMeta->payout_meta_router)) {
                            $tempData["child"][] = [
                                "name" => "Payout Meta",
                                "child_route" => "/payment-gateway/payout/{$pgMeta->pg}",
                            ];
                        }
                    }
                    $sideBarData[] = $tempData;
                }
            }
        }
        usort($sideBarData, function($a, $b) {
            return $a['sorting'] <=> $b['sorting'];
        });
        $finalData = [];
        foreach ($sideBarData as $_sideBarData) {
            $finalData[$_sideBarData['group']][] = $_sideBarData;
        }
        Cache::put($sidebarSessionId, $finalData, 600);
        return $finalData;
    }

    static function routeTo(): string
    {
        $sidebarData = self::renderSidebar();
        $routeTo = "";
        foreach ($sidebarData as $group => $sidebar) {
            foreach ($sidebar as $_sidebar) {
                if(!$_sidebar['is_child']) {
                    $routeTo = $_sidebar['route'];
                } else {
                    foreach ($_sidebar['child'] as $__sidebar) {
                        $routeTo = $__sidebar['child_route'];
                    }
                    if(strlen($routeTo) > 0) {
                        break;
                    }
                }
                if(strlen($routeTo) > 0) {
                    break;
                }
            }
            if(strlen($routeTo) > 0) {
                break;
            }
        }
        return $routeTo;
    }

    static function iconClass($name): string
    {
        $classList = [
                    "dashboard" => "box",
                    "pgm dashboard" => "box",
                    "transaction" => "arrow-down-left",
                    "payout" => "arrow-up-right",
                    "payout manual" => "arrow-up-right",
                    "p manual recon" => "arrow-up-right",
                    "payout signal" => "alert-triangle",
                    "payout cust level" => "bar-chart",
                    "merchant" => "rotate-cw",
                    "refund" => "git-merge",
                    "bank transaction" => "columns",
                    "reconciliation" => "layers",
                    "report" => "book-open",
                    "support logs" => "users",
                    "webhook event" => "command",
                    "payment method" => "credit-card",
                    "customers" => "user",
                    "pg webhook" => "book",
                    "block info" => "x-octagon",
                    "bank statement" => "archive",
                    "bank sync" => "trello",
                    "mobile sync" => "trello",
                    "utr reconciliation" => "layers",
                    "merchant read payin" => "arrow-up-right",
                    "late success" => "watch",
                    "sms logs" => "mail",
                ];
        return $classList[strtolower($name)] ?? "";
    }

    static function sorting($name): string
    {
        $sort = [
                    "dashboard" => 0,
                    "pgm dashboard" => 1,
                    "transaction" => 2,
                    "payout" => 3,
                    "payout manual" => 3.2,
                    "p manual recon" => 3.3,
                    "payout signal" => 3.4,
                    "payout cust level" => 3.5,
                    "merchant" => 4,
                    "refund" => 5,
                    "bank transaction" => 5,
                    "reconciliation" => 6,
                    "utr reconciliation" => 6,
                    "pg meta" => 7,
                    "report" => 8,
                    "support logs" => 9,
                    "webhook event" => 10,
                    "payment method" => 11,
                    "customers" => 12,
                    "pg webhook" => 13,
                    "block info" => 14,
                    "bank statement" => 15,
                    "bank sync" => 1,
                    "mobile sync" => 1.1,
                    "merchant read payin" =>16,
                    "late success" =>18,
                    "sms logs" =>19,
        ];
        return $sort[strtolower($name)] ?? 100;
    }

    static function groping($name): string
    {
        $sort = [
                    "dashboard" => "MAIN",
                    "pgm dashboard" => "MAIN",
                    "transaction" => "PAYIN & PAYOUT",
                    "payout" => "PAYIN & PAYOUT",
                    "payout manual" => "PAYIN & PAYOUT",
                    "p manual recon" => "PAYIN & PAYOUT",
                    "payout signal" => "PAYIN & PAYOUT",
                    "payout cust level" => "PAYIN & PAYOUT",
                    "merchant" => "PAYIN & PAYOUT",
                    "refund" => "PAYIN & PAYOUT",
                    "bank transaction" => "PAYIN & PAYOUT",
                    "reconciliation" => "PAYIN & PAYOUT",
                    "utr reconciliation" => "PAYIN & PAYOUT",
                    "report" => "SUPPORT",
                    "support logs" => "SUPPORT",
                    "webhook events" => "SUPPORT",
                    "payment method" => "SUPPORT",
                    "customers" => "SUPPORT",
                    "pg webhooks" => "SUPPORT",
                    "block info" => "SUPPORT",
                    "bank statement" => "SUPPORT",
                    "bank sync" => "MAIN",
                    "mobile sync" => "MAIN",
                    "merchant read payin" => "MAIN",
                    "late success" => "MAIN",
                    "sms logs" => "MAIN",
                ];
        return $sort[strtolower($name)] ?? "SUPPORT";
    }

}
