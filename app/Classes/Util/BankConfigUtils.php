<?php

namespace App\Classes\Util;

use App\Models\PaymentManual\BankConfig;
use Illuminate\Support\Facades\Log;

class BankConfigUtils
{
    public function fetchStatus()
    {
        try {
            $result = (new BankConfig())->fetchStatus();
            return response()->json([
                "status" => true,
                "message" => "Bank Config Data Retried  Success",
                "data"=>$result,
            ])->setStatusCode(200);
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

    public function updateBankStatus($bank_name,$value) {
        try {
            $bank_rec= (new BankConfig())->updateBankStatus($bank_name,$value);
            if(($bank_rec)) {
                if($value){
                    SupportUtils::logs('PAYIN',"Bank Down : $bank_name");
                }else{
                    SupportUtils::logs('PAYIN',"Bank Up : $bank_name");
                }
                $error['status'] = true;
                $error['message'] = "Bank Config Update Success";
                return response()->json($error)->setStatusCode(200);
            }

            $error['status'] = false;
            $error['message'] = "Error";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";
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
   }
