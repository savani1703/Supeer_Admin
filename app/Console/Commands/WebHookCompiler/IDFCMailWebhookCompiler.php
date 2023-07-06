<?php

namespace App\Console\Commands\WebHookCompiler;

use App\Models\Management\Payout;
use App\Models\PaymentManual\IDFC\IDFCPayoutMeta;
use App\Models\PaymentManual\IdfcMailWebhook;
use Carbon\Carbon;
use DOMDocument;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use stdClass;

class IDFCMailWebhookCompiler extends Command
{

    protected $signature = 'IDFCMailWebhookCompiler';
    protected $description = 'Command description';

    public function handle()
    {
        $id = null;
        try {
            while (true) {
                $webhook = (new IdfcMailWebhook())->getPendingWebhook();
                if(isset($webhook) && !empty($webhook)) {
                    $id = $webhook->id;
                    if (isset($webhook->mail_data) && !empty($webhook->mail_data)) {
                        $mailDetails = json_decode($webhook->mail_data, false);
                        if ($mailDetails) {
                            $body = $mailDetails->data;
                            if (isset($body) && !empty($body)) {
                                if (isset($body->from->address) && !empty($body->from->address) && isset($body->to) && !empty($body->to)) {
                                    /*if (Str::contains($body->from->address, "noreply@idfcfirstbank.com")) {
                                        if(isset($body->html) && !empty($body->html)){
                                            $this->mailCompiler($body->html);
                                        }
                                    }*/
                                    if (isset($body->text->html) && !empty($body->text->html)) {
                                        $this->mailCompiler($body->text->html, $webhook->id, $body->to);
                                    }
                                }
                            }
                        }
                    }
                }
                sleep(1);
                echo "\n sleep for 1 second ( Cool )";
            }

        } catch (\Exception $ex) {
            Log::error('Error while executing SQL Query', [
                'class' => __CLASS__,
                'function' => __METHOD__,
                'file' => $ex->getFile(),
                'line_no' => $ex->getLine(),
                'error_message' => $ex->getMessage(),
            ]);
            (new IdfcMailWebhook())->setRemark($id, $ex->getMessage());
            print_r($ex->getMessage());
        }
    }


    public function mailCompiler($message, $id, $notifyEmailId){
        try {

            libxml_use_internal_errors(true);

            $dom = new DOMDocument();
            $dom->loadHTML($message);
            $table = array();

            $dom->preserveWhiteSpace = false;

            $rows = $dom->getElementsByTagName('tr');
            $row_headers = NULL;
            foreach ($rows as $row) {

                // get each column by tag name
                $cols = $row->getElementsByTagName('td');
                $row = array();
                $i = 0;
                foreach ($cols as $node) {
                    if ($row_headers == NULL)
                        $row[] = $node->nodeValue;
                    else
                        $row[$row_headers[$i]] = $node->nodeValue;
                    $i++;
                }
                $table[] = $row;
            }

            $payout = new StdClass();
            foreach ($table as $rows) {
                if (count($rows) == 2) {

                    if (Str::contains($rows[0], "Custom Header – 1")) {
                        $payout->payout_id = trim(str_replace(":", "", $rows[1]));
                    }
                    if (Str::contains($rows[0], "Reference Number")) {
                        $payout->reference_number = trim(str_replace(":", "", $rows[1]));
                    }
                    if (Str::contains($rows[0], "Transaction Amount")) {
                        $payout->payout_amount = trim(str_replace("₹", "", $rows[1]));
                        $payout->payout_amount = trim(str_replace(",", "", $payout->payout_amount));
                        $payout->payout_amount = intval(trim(str_replace(":", "", $payout->payout_amount)));
                    }
                    if (Str::contains($rows[0], "Transaction Date")) {
                        $payout->bank_date = trim(str_replace(":", "", $rows[1]));
                    }
                    if (Str::contains($rows[0], "Payment from")) {
                        $payout->payment_from = trim(str_replace(":", "", $rows[1]));
                    }
                    $payout->created_at = Carbon::now()->toDateTimeString();
                }
            }

            $accountNumber = null;
            foreach($notifyEmailId as $_notifyEmailId){
               if(isset($_notifyEmailId->address) && !empty($_notifyEmailId->address)){
                   $accountNumber  = (new IDFCPayoutMeta())->getAccountNumberByEmailId($_notifyEmailId->address);
               }
           }


            /*echo "\n Ref found : ". "Payout Id :". $payout->payout_id . " reference number :" .$payout->reference_number;

            if(isset($payout->payout_id) && !empty($payout->payout_id) && isset($payout->reference_number) && !empty($payout->reference_number)){
                $isExists =  (new Payout())->checkTempUtr($payout->reference_number);
                if(!$isExists){
                    $result = (new Payout())->setIDFCPayoutPgRefId($payout->payout_id, $payout->reference_number);
                    if($result){
                        echo "\n Temp Utr updated : ". "Payout Id :". $payout->payout_id ." reference number :" .$payout->reference_number;
                    }
                }else{
                    echo "\n Temp Utr All ready Exists : ", $isExists." reference number :" .$payout->reference_number;
                }
            }*/

            echo "\n Ref found in Mail Hook : ". "Payout Id : ". $payout->payout_id . " reference number : " .$payout->reference_number. " account number : " .$accountNumber;

            $isExists = (new IdfcMailWebhook())->checkUtrExists($payout->reference_number, $accountNumber);
            if(!$isExists){
                $result = (new IdfcMailWebhook())->updateRecData($id, $payout->payout_id, $payout->payout_amount, $payout->reference_number, $payout->payment_from, $payout->bank_date, $accountNumber);
                if($result){
                    echo "\n Data extracted success : ". "Payout Id :". $payout->payout_id ." reference number : " .$payout->reference_number, " account number : " .$accountNumber;
                }else{
                    echo "\n Data extracted skipped : ". "Payout Id :". $payout->payout_id ." reference number : " .$payout->reference_number, " account number : " .$accountNumber;
                }
            }else{
                (new IdfcMailWebhook())->setRemark($id, "UTR_ALREADY_EXIST");
                echo "\n UTR_ALREADY_EXIST : ". "Payout Id : ". $payout->payout_id ." reference number : " .$payout->reference_number, " account number : " .$accountNumber;
            }
            (new IdfcMailWebhook())->markAsUsed($id);

        }catch (\Exception $ex){
            Log::error('Error while executing SQL Query', [
                'class' => __CLASS__,
                'function' => __METHOD__,
                'file' => $ex->getFile(),
                'line_no' => $ex->getLine(),
                'error_message' => $ex->getMessage(),
            ]);
            (new IdfcMailWebhook())->setRemark($id, $ex->getMessage());
            print_r($ex->getMessage());
        }
    }
}
