<?php

namespace App\Classes\Util\PgMeta;

use App\Classes\Util\PgName;
use App\Models\Management\MerchantPaymentMeta;
use App\Models\Management\MerchantPayoutMeta;
use App\Models\Management\PgRouter;
use App\Models\Management\ProxyList;

class PgMetaHelper
{
    public function getPgMetaModel($pgType, $pgName) {
        try {
            if((new PgRouter())->checkPgIsAvailable($pgName)) {
                $pgRouter = (new PgRouter())->getRouterByPg($pgName);
                if(isset($pgRouter)) {
                    if(isset($pgType) && !empty($pgType)) {
                        if(strcmp(strtoupper($pgType), "PAYIN") === 0) {
                            if(isset($pgRouter->payin_meta_router) && !empty($pgRouter->payin_meta_router)) {
                                return $pgRouter->payin_meta_router;
                            }
                        }
                        if(strcmp(strtoupper($pgType), "PAYOUT") === 0) {
                            if(isset($pgRouter->payout_meta_router) && !empty($pgRouter->payout_meta_router)) {
                                return $pgRouter->payout_meta_router;
                            }
                        }
                    }
                }
            }
            return null;
        } catch (\Exception $ex) {
            return null;
        }
    }

    protected function disablePaymentMetaFromMerchantMeta($metaId, $pgType, $pgName) {
        try {
            $merchantMeta = null;
            if(strcmp(strtolower($pgType), "payout") === 0) {
                $merchantMeta = new MerchantPayoutMeta();
            }
            if(strcmp(strtolower($pgType), "payin") === 0) {
                $merchantMeta = new MerchantPaymentMeta();
            }

            if(isset($merchantMeta)) {
                $merchantMeta->disableMerchantMetaMeta($metaId, $pgName);
            } else {
                throw new \Exception("Error While Update $metaId($pgName) Status, Please Check in Merchant Account Meta");
            }
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    protected function getCommonTableViewColumns($availableShowColumns, $pgType, $pgName)
    {
        $commonColumns = [
            "id",
            "ACCOUNT ",
            "MERCHANT ID",
            "LABEL/EMAIL",
            "BOUNCER",
            "Status",
            "Limit/Turn Over"
        ];

        if(strcmp(strtoupper($pgName), PgName::UPIPAY) === 0) {
            $commonColumns = [
                "Bank Id",
                "ACCOUNT",
                "Vendor",
                "Status",
                "Limit/Turn Over"
            ];
        }

//        if(strcmp(strtoupper($pgName), PgName::ICICI) === 0 && strcmp(strtoupper($pgType), "PAYOUT") === 0) {
//            $commonColumns = [
//                "Id",
//                "ACCOUNT",
//                "MERCHANT ID",
//                "Bank Details",
//                "Status",
//                "Limit/Turn Over"
//            ];
//        }


        if(strcmp(strtolower($pgType), "payin") === 0) {
            if(in_array("available_method", $availableShowColumns)) {
                $commonColumns[] = "Available Method";
            }
        }

        $commonColumns[] = "DATE";
        if(strcmp(strtolower($pgType), "payin") === 0) $commonColumns[] = "ACTION";

        return $commonColumns;
    }

    protected function parseAddMetaColumnsWithType($columns)
    {
        $proxyList = (new ProxyList())->getProxyList();
        try {
            return view("pg.add-pg-meta")
                ->with("columns", $columns)
                ->with("proxyList", $proxyList)
                ->render();
        } catch (\Exception $ex) {
            return null;
        }
    }

}
