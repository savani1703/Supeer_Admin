<?php

namespace App\Http\Controllers;

use App\Models\PaymentManual\IdfcMailWebhook;
use App\Models\PaymentManual\PaymentMailWebhook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MailWebhookHandler extends Controller
{
    public function mailWebhookHandle(Request $request){
        try {
            $mailData = null;
            $data = $request->all();
            if($data){
                $mailData = json_encode($data);
            }
            if($mailData){
                (new IdfcMailWebhook())->setMailWebhook($mailData);
            }
        }catch (\Exception $ex){
            Log::info('AllInOneMailReaderClasses Errors ',['handle' => $ex->getMessage()]);
        }
    }

    public function mailWebhookHandleV2(Request $request){
        try {
            $mailData = null;
            $data = $request->all();
            if($data){
                $mailData = json_encode($data);
            }
            if($mailData){
                (new PaymentMailWebhook())->setMailWebhook($mailData);
            }
        }catch (\Exception $ex){
            Log::info('AllInOneMailReaderClasses Errors ',['handle' => $ex->getMessage()]);
        }
    }
}
