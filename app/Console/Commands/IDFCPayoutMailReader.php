<?php

namespace App\Console\Commands;

use App\Models\Management\Payout;
use DOMDocument;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use stdClass;
use Webklex\IMAP\Facades\Client;
use Illuminate\Support\Str;

class IDFCPayoutMailReader extends Command
{

    protected $signature = 'IDFCPayoutMailReader';
    protected $description = 'Command description';

    public function handle()
    {
        while (true) {
            try {
                echo "\n\n idfc Checking Started";
                $client = null;
                try {
                    $client = Client::account('sm_trading');
                    $client->connect();
                } catch (\Exception $ex) {

                    //return "Unable Connect TO Server";
                }
                echo "\n Getting Folder";

                $aFolder = $client->getFolders('INBOX');
                echo "\n Getting Folder Done";

                foreach ($aFolder as $folder) {
                    echo "\n checking in " . $folder->full_name;
                    echo "\n Reading Mail ";
                    $messages = $folder->query()->from('noreply@idfcfirstbank.com')->since(Carbon::now()->subDays(1))->fetchOrderDesc()->get();
                    echo "\n Reading Mail Done";
                    foreach ($messages as $message) {
                        if (Str::contains($message->get('received_spf')->toString(), "delivery.idfcfirstbank.com") && Str::contains($message->get('received_spf')->toString(), "pass ")) {
                            $arr = $message->from->toArray();
                            if (count($arr) > 0) {
                                $arr = $arr[0];
                                $this->mailCompiler($message,  $arr);
                            }
                        }
                    }
                }
            } catch (\Exception $exception) {
                echo $exception->getMessage();
            }
            echo "\n sleep for 5 seconds";
            sleep(5);
        }
    }

    private function mailCompiler($message, $arr){
        try {

            if (Str::contains($arr->mail, "noreply@idfcfirstbank.com") && Str::contains($arr->full, "noreply@idfcfirstbank.com")) {

                libxml_use_internal_errors(true);
                $dom = new DOMDocument();
                $dom->loadHTML($message->getHTMLBody());
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
                        }
                        $payout->created_at = Carbon::now()->toDateTimeString();
                    }
                }

                echo "\n Ref found : ". "Payout Id :". $payout->payout_id . " reference number :" .$payout->reference_number;

                /*if(isset($payout->payout_id) && !empty($payout->payout_id) && isset($payout->reference_number) && !empty($payout->reference_number)){
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
            }

        }catch (\Exception $ex){
            Log::info('CointabMailCompilerClasses Errors ',['mailCompiler' => $ex->getMessage()]);
            print_r($ex->getMessage());
        }
    }
}
