<?php

namespace App\Console\Commands\MailReader;

use App\Models\Management\Payout;
use App\Models\PaymentManual\MailReader;
use Carbon\Carbon;
use DOMDocument;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use stdClass;
use Webklex\PHPIMAP\ClientManager;
use Webklex\PHPIMAP\Exceptions\ConnectionFailedException;

class AllInOneMailReader extends Command
{
    protected $signature = 'AllInOneMailReader';
    protected $description = 'Command description';

    public function __construct()
    {
        parent::__construct();
    }
    public function handle()
    {
        while (true) {
            try {
                $mailDetails = (new MailReader())->getAllMailDetailsForIDfc();
                if (isset($mailDetails) && !empty($mailDetails)) {
                    foreach ($mailDetails as $_mailDetails) {
                        if (isset($_mailDetails->username) && !empty($_mailDetails->username) && isset($_mailDetails->password) && !empty($_mailDetails->password)) {
                            $username = $_mailDetails->username;
                            $password = $_mailDetails->password;
                            $mailSender = $_mailDetails->mail_sender;
                            $mailFrom = $_mailDetails->mail_from;
                            $accountHolderName = $_mailDetails->idfcDetails->label;
                            echo "\n ".$accountHolderName." trying to connected .....";
                            $this->connectToClient($username, $password, $mailSender, $mailFrom, $accountHolderName);
                        }
                        sleep(2);
                        echo "\n sleep for 2 second ( Cool )";
                    }
                }
                sleep(5);
                echo "\n sleep for 5 second";

            } catch (\Exception $ex) {
                Log::info('AllInOneMailReaderClasses Errors ',['handle' => $ex->getMessage()]);
                print_r($ex->getMessage());
            }
        }
    }

    public function connectToClient($username, $password, $mailSender, $mailFrom, $accountHolderName)
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

            $res = $client->connect();
            //$client->getConnection()->enableDebug();

            echo "\n ".$res->username." connected .....";

            echo "\n getting inbox details .....";

            $folder = $client->getFolder("INBOX");
            $client->openFolder($folder->path);
            $messages = $folder->query()->from("noreply@idfcfirstbank.com")->fetchOrderDesc()->limit(200)->get();

            echo "\n inbox retried success .....";

            $this->dynamicPrint(['Account Name' => $accountHolderName, 'message' => " ✅ connected"]);


            foreach ($messages as $message) {
                if (Str::contains($message->get('received_spf')->toString(), "delivery.idfcfirstbank.com") && Str::contains($message->get('received_spf')->toString(), "pass ")) {
                    $arr = $message->from->toArray();
                    if (count($arr) > 0) {
                        $arr = $arr[0];
                        $this->mailCompiler($message,  $arr);
                        usleep(10);
                        echo "\n sleep for 10 usleep ( Cool )";
                    }
                }
            }
        }catch (ConnectionFailedException $ex) {
            $this->dynamicPrint(['Account Name' => $accountHolderName, 'Exception' => " 🛑 ".$ex->getMessage()]);
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


    public function mailCompiler($message, $arr){
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
            Log::info('AllInOneMailReaderClasses Errors ',['mailCompiler' => $ex->getMessage()]);
            print_r($ex->getMessage());
        }
    }
}
