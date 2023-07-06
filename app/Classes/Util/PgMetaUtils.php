<?php

namespace App\Classes\Util;

use App\Classes\Util\PgMeta\PgMetaHelper;
use App\Models\Management\PgRouter;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PgMetaUtils extends PgMetaHelper
{
    public static function getPgMetaRoute() {
        try {
            if(Cache::has("pg_sidebar_meta")) {
                return Cache::get("pg_sidebar_meta");
            } else {
                $pgRouter = (new PgRouter())->getAllPgMetaModels();
                if(isset($pgRouter)) {
                    $tempData = [];
                    foreach ($pgRouter as $key => $pgMeta) {
                        $tempData[$key]['pg'] = $pgMeta->pg;
                        if(isset($pgMeta->payin_meta_router)) {
                            $tempData[$key]['payin_route'] = "/payment-gateway/payin/{$pgMeta->pg}";
                        }
                        if(isset($pgMeta->payout_meta_router)) {
                            $tempData[$key]['payout_route'] = "/payment-gateway/payout/{$pgMeta->pg}";
                        }
                    }
                    if(sizeof($tempData) > 0) {
                        $pgSidebarData = view("pg.pg-sidebar")->with("data", $tempData)->render();
                        Cache::add("pg_sidebar_meta", $pgSidebarData, 180);
                        return $pgSidebarData;
                    }
                }
                return null;
            }
        } catch (\Exception $ex) {
            return null;
        }
    }

    public function getRenderMetaViewData($pgType, $pgName)
    {
        try {
            $template = view("pg.error-pg-meta");
            $pgModule = $this->getPgMetaModel($pgType, $pgName);
            if(isset($pgModule)) {
                $pgRenderConfig = (new $pgModule)->getRenderConfig();
                $pgRenderConfig['show_columns'] = $this->getCommonTableViewColumns($pgRenderConfig['show_columns'], $pgType, $pgName);
                $pgRenderConfig['add_meta_columns'] = $this->parseAddMetaColumnsWithType($pgRenderConfig['add_meta_columns']);
                $pgRenderConfig['pg_name'] = ucfirst(strtolower($pgName));
                $pgRenderConfig['pg_type'] = ucfirst(strtolower($pgType));
                $template = view("pg.pg-meta")->with("pgRenderConfig", $pgRenderConfig);
            }
            return $template;
        } catch (\Exception $ex) {
            return view("pg.error-pg-meta");
        }
    }

    public function getPayInMeta($filterData, $limit, $pageNo, $pgType, $pgName)
    {
        try {
            $pgModule = $this->getPgMetaModel($pgType, $pgName);
            if(isset($pgModule)) {
                $pgMeta = (new $pgModule)->getMeta($filterData, $limit, $pageNo);
                if(isset($pgMeta)) {
                    $result = DigiPayUtil::withPaginate($pgMeta);
                    $config = (new $pgModule)->getRenderConfig();
                    $result['config'] = $config['editable_columns'];
                    return response()->json($result)->setStatusCode(200);
                }
            }
            $error['status'] = false;
            $error['message'] = "Meta Not Found";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(400);
        }
    }

    public function addPaymentMeta($formData, $pgType, $pgName) {
        try {
            $pgModule = $this->getPgMetaModel($pgType, $pgName);
            if(isset($pgModule)) {
                $accountId = (new $pgModule)->getAccountId();
                (new $pgModule)->validateFormData($formData, $accountId);
                if((new $pgModule)->addMeta($formData, $accountId)) {
                    SupportUtils::logs("PG $pgType Meta","New Meta Added, PG: $pgName, META_ID: $accountId");
                    $result['status'] = true;
                    $result['message'] = "Meta Added";
                    return response()->json($result)->setStatusCode(200);
                }
            }
            $error['status'] = false;
            $error['message'] = "Failed to add meta";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";
            return response()->json($error)->setStatusCode(400);
        }
    }

    public function updatePaymentMetaStatus($metaId, $status, $pgType, $pgName)
    {
        try {
            $pgModule = $this->getPgMetaModel($pgType, $pgName);
            if(isset($pgModule)) {
                if($status < 1 || strcmp($status, "0") === 0) {
                    $this->disablePaymentMetaFromMerchantMeta($metaId, $pgType, $pgName);
                }
                if((new $pgModule)->updateMetaStatus($metaId, $status)) {
                    SupportUtils::logs("PG $pgType Meta","Update Meta Status, PG: $pgName, META_ID: $metaId, STATUS: $status");
                    return response()->json([
                        "status" => true,
                        "message" => "$metaId($pgName) Status Updated",
                    ])->setStatusCode(200);
                }
            }
            $error['status'] = false;
            $error['message'] = "Invalid Pg Meta";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";
            return response()->json($error)->setStatusCode(400);
        }
    }

    public function updatePaymentMetaMinLimit($metaId, $minLimit, $pgType, $pgName)
    {
        try {
            $pgModule = $this->getPgMetaModel($pgType, $pgName);
            if(isset($pgModule)) {
                if((new $pgModule)->updateMetaMinLimit($metaId, $minLimit)) {
                    SupportUtils::logs("PG $pgType Meta","Update Meta Min Limit, PG: $pgName, META_ID: $metaId, MIN_LIMIT: $minLimit");
                    return response()->json([
                        "status" => true,
                        "message" => "$metaId($pgName) Min Limit Updated",
                    ])->setStatusCode(200);
                }
            }
            $error['status'] = false;
            $error['message'] = "Invalid Pg Meta";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(400);
        }
    }

    public function updateMetaAutoLoginStatus($metaId, $autoLoginStatus, $pgType, $pgName)
    {
        try {
            $pgModule = $this->getPgMetaModel($pgType, $pgName);
            if(isset($pgModule)) {
                if((new $pgModule)->updateMetaAutoLoginStatus($metaId, $autoLoginStatus)) {
                    SupportUtils::logs("PG $pgType Meta","Update Meta Auto Login Status, PG: $pgName, META_ID: $metaId, AUTO_LOGIN_STATUS: $autoLoginStatus");
                    return response()->json([
                        "status" => true,
                        "message" => "$metaId($pgName) Auto Login Status Updated",
                    ])->setStatusCode(200);
                }
            }
            $error['status'] = false;
            $error['message'] = "Invalid Pg Meta";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(400);
        }
    }

    public function updatePaymentMetaMaxLimit($metaId, $maxLimit, $pgType, $pgName)
    {
        try {
            $pgModule = $this->getPgMetaModel($pgType, $pgName);
            if(isset($pgModule)) {
                if((new $pgModule)->updateMetaMaxLimit($metaId, $maxLimit)) {
                    SupportUtils::logs("PG $pgType Meta","Update Meta Max Limit, PG: $pgName, META_ID: $metaId, MAX_LIMIT: $maxLimit");
                    return response()->json([
                        "status" => true,
                        "message" => "$metaId($pgName) Max Limit Updated",
                    ])->setStatusCode(200);
                }
            }
            $error['status'] = false;
            $error['message'] = "Invalid Pg Meta";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(400);
        }
    }

    public function updatePaymentMetaMaxCountLimit($metaId, $maxCountLimit, $pgType, $pgName)
    {
        try {
            $pgModule = $this->getPgMetaModel($pgType, $pgName);
            if(isset($pgModule)) {
                if((new $pgModule)->updateMetaMaxCountLimit($metaId, $maxCountLimit)) {
                    SupportUtils::logs("PG $pgType Meta","Update Meta Max Count Limit, PG: $pgName, META_ID: $metaId, MAX_COUNT_LIMIT: $maxCountLimit");
                    return response()->json([
                        "status" => true,
                        "message" => "$metaId($pgName) Max Count Limit Updated",
                    ])->setStatusCode(200);
                }
            }
            $error['status'] = false;
            $error['message'] = "Invalid Pg Meta";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(400);
        }
    }

    public function updatePaymentMetaTurnOver($metaId, $turnOver, $pgType, $pgName)
    {
        try {
            $pgModule = $this->getPgMetaModel($pgType, $pgName);
            if(isset($pgModule)) {
                if((new $pgModule)->updateMetaTurnOver($metaId, $turnOver)) {
                    SupportUtils::logs("PG $pgType Meta","Update Meta TurnOver, PG: $pgName, META_ID: $metaId, TURNOVER: $turnOver");
                    return response()->json([
                        "status" => true,
                        "message" => "$metaId($pgName) Max Limit Updated",
                    ])->setStatusCode(200);
                }
            }
            $error['status'] = false;
            $error['message'] = "Invalid Pg Meta";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(400);
        }
    }

    public function updatePaymentMetaMethod($metaId, $method, $pgType, $pgName)
    {
        try {
            if(strcmp(strtolower($pgType), "payout") === 0) {
                throw new \Exception("Allowed Only In PayIn Meta");
            }
            $pgModule = $this->getPgMetaModel($pgType, $pgName);
            if(isset($pgModule)) {
                if((new $pgModule)->updateMetaMethod($metaId, $method)) {
                    SupportUtils::logs("PG $pgType Meta","Update Meta METHOD, PG: $pgName, META_ID: $metaId, METHOD: $method");
                    return response()->json([
                        "status" => true,
                        "message" => "$metaId($pgName) Allowed Method Updated",
                    ])->setStatusCode(200);
                }
            }
            $error['status'] = false;
            $error['message'] = "Invalid Pg Meta";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(400);
        }
    }

    public function updateMetaProductInfo($metaId, $productInfo, $pgType, $pgName)
    {
        try {
            if(strcmp(strtolower($pgType), "payout") === 0) {
                throw new \Exception("Allowed Only In PayIn Meta");
            }
            $pgModule = $this->getPgMetaModel($pgType, $pgName);
            if(isset($pgModule)) {
                if((new $pgModule)->updateMetaProductInfo($metaId, $productInfo)) {
                    SupportUtils::logs("PG $pgType Meta","Update Meta Product Info, PG: $pgName, META_ID: $metaId, PRODUCT_INFO: $productInfo");
                    return response()->json([
                        "status" => true,
                        "message" => "$metaId($pgName) Product Info Updated",
                    ])->setStatusCode(200);
                }
            }
            $error['status'] = false;
            $error['message'] = "Invalid Pg Meta";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(400);
        }
    }

    public function getPaymentMetaLabelList($pgType, $pgName)
    {
        try {
            $pgModule = $this->getPgMetaModel($pgType, $pgName);
            if(isset($pgModule)) {
                $pgMeta = (new $pgModule)->getAllPgMeta();
                if(isset($pgMeta)) {
                    $pgData = [];
                    foreach ($pgMeta as $meta) {
                        $pgData[] = [
                            "account_id" => $meta->account_id,
                            "label" => $meta->label,
                        ];
                    }
                    return response()->json([
                        "status" => true,
                        "message" => "Data Retrieved",
                        "data" => $pgData,
                    ])->setStatusCode(200);
                }
            }
            $error['status'] = false;
            $error['message'] = "Invalid Pg Meta";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(400);
        }
    }

    public function testPaymentAccount($pgName, $metaId, $paymentAmount)
    {
        try {
            $payload = ['bank_id' => $metaId, 'pg_name' => $pgName, 'payment_amount' => $paymentAmount];
            $authorizationToken = DigiPayUtil::createJwtToken($payload);
            $header = [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => $authorizationToken
                ]
            ];

            $client = new Client($header);
            $response = $client->post("https://checkout.payin247.com/api/v1/check/bank/active",
                ['json' => $payload]
            );

            if ($response->getStatusCode() === 200) {
                return json_decode($response->getBody()->getContents(),false);
            }
            return null;
        } catch (RequestException $ex) {
            Log::critical('CentralController Error',['payoutReconInit' => $ex->getMessage()]);
            return json_decode($ex->getResponse()->getBody(true)->getContents(),false);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(400);
        }
    }

}
