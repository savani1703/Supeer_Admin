<?php

namespace App\Console\Commands\MailReader;

use App\Models\Management\Payout;
use App\Models\PaymentManual\MailReader;
use App\Models\PaymentManual\PayoutMailRecon;
use Carbon\Carbon;
use DOMDocument;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use stdClass;
use Webklex\PHPIMAP\ClientManager;

class AllInOneMailReaderRecon extends Command
{

    protected $signature = 'AllInOneMailReaderRecon {av_bank_id}';
    protected $description = 'Command description';

    public function handle()
    {
        while (true) {
            try {
                $avBankId = $this->argument('av_bank_id');
                if(!isset($avBankId) || empty($avBankId)){
                    dd('OK');
                }

                $mailDetails = (new MailReader())->getMailDetailsForIDfcById($avBankId);
                if(!isset($mailDetails) || empty($mailDetails)){
                    dd("Details Not Exists");
                }

                $username = $mailDetails->username;
                $password = $mailDetails->password;
                $mailSender = $mailDetails->mail_sender;
                $mailFrom = $mailDetails->mail_from;
                $accountHolderName = $mailDetails->idfcDetails->label;
                $accountNumber = $mailDetails->idfcDetails->debit_account;
                echo "\n ".$accountHolderName." trying to connected .....";
                $this->connectToClient($username, $password, $mailSender, $mailFrom, $accountHolderName, $accountNumber);


            } catch (\Exception $ex) {
                Log::info('AllInOneMailReaderClasses Errors ',['handle' => $ex->getMessage()]);
                print_r($ex->getMessage());
            }
        }
    }

    public function connectToClient($username, $password, $mailSender, $mailFrom, $accountHolderName, $accountNumber)
    {
        try {

            $cm = new ClientManager();
            $client = $cm->make([
                'host'  => 'imap.gmail.com',
                'port'  => 993,
                'encryption'    => 'ssl',
                'validate_cert' => true,
                'username' => $username,
                'password' => $password,
            ]);

            $client->connect();
            $folder = $client->getFolder("INBOX");
            $client->openFolder($folder->path);
            $messages = $folder->query()->from("noreply@idfcfirstbank.com")->since(Carbon::now())->fetchOrderDesc()->limit(1)->get();
            $this->dynamicPrint(['Account Name' => $accountHolderName, 'message' => " ✅ connected"]);

            foreach ($messages as $message) {
                if (Str::contains($message->get('received_spf')->toString(), "delivery.idfcfirstbank.com") && Str::contains($message->get('received_spf')->toString(), "pass ")) {
                    $arr = $message->from->toArray();
                    if (count($arr) > 0) {
                        $arr = $arr[0];
                        $this->mailCompiler($message,  $arr, $accountNumber, $accountHolderName);
                        sleep(1);
                        echo "\n sleep for 1 second ( Cool )";
                    }
                }
            }
        }catch (\Exception $ex){
            $this->dynamicPrint(['Account Name' => $accountHolderName, 'Exception' => " 🛑 ".$ex->getMessage()]);
            Log::info('AllInOneMailReaderClasses Errors ',['connectToClient' => $ex->getMessage()]);
            print_r($ex->getMessage());
        }
    }

    private function dynamicPrint($array) {
        $key    = [];
        $value  = [];
        foreach ($array as $_key => $_value){
            $key[]  = $_key;
            $value[]  = $_value;
        }

        $this->table(
            [$key],
            [$value]
        );
    }


    public function mailCompiler($message, $arr, $accountNumber, $accountHolderName){
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
                        if (Str::contains($rows[0], "Payment from")) {
                            $payout->payment_from = trim(str_replace(":", "", $rows[1]));
                        }
                        if (Str::contains($rows[0], "Reference Number")) {
                            $payout->reference_number = trim(str_replace(":", "", $rows[1]));
                        }
                        if (Str::contains($rows[0], "Transaction Date")) {
                            $payout->bank_date = trim(str_replace(":", "", $rows[1]));
                        }
                        if (Str::contains($rows[0], "Transaction Amount")) {
                            $payout->payout_amount = trim(str_replace("₹", "", $rows[1]));
                            $payout->payout_amount = trim(str_replace(",", "", $payout->payout_amount));
                            $payout->payout_amount = intval(trim(str_replace(":", "", $payout->payout_amount)));
                        }
                    }
                }
                dd($payout);
                echo "\n Ref found : ". "Payout Id :". $payout->payout_id . " reference number :" .$payout->reference_number;

                if(isset($payout->payout_id) && !empty($payout->payout_id) && isset($payout->reference_number) && !empty($payout->reference_number)){
                    $isExists = (new PayoutMailRecon())->checkPayoutIdExists($payout->payout_id);
                    if($isExists){
                        dd($payout, $accountNumber, $accountHolderName);
                    }
                    $result = (new PayoutMailRecon())->addPayoutMailRecon($payout->payout_id, $payout->payout_amount, $accountNumber);
                    if($result){
                        echo "\n Data Added : ". "Payout Id :". $payout->payout_id ." reference number :" .$payout->reference_number;
                    }else{
                        echo "\n Skipped... : ". "Payout Id :". $payout->payout_id ." reference number :" .$payout->reference_number;
                    }
                }
            }

        }catch (\Exception $ex){
            Log::info('AllInOneMailReaderClasses Errors ',['mailCompiler' => $ex->getMessage()]);
            print_r($ex->getMessage());
        }
    }
}
