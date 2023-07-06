<?php

namespace App\Classes\Util;

use App\Models\Management\SupportReport;
use Aws\S3\S3Client;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Facades\Excel;

class AWSUtils
{
    private $client;

    public function __construct()
    {
        $this->client = $this->config();
    }

    public function storeNew($export, $fileName, $downloadId, $emailId){
        try{
            Excel::store(
                $export,
                $fileName,
                's3',
                ExcelWriter::XLSX
            );

            $cmd = $this->client->getCommand('GetObject', [
                'Bucket' => "apihuk",
                'Key' => $fileName
            ]);

            $res = $this->client->createPresignedRequest($cmd, '+1440 minutes');
            $presignedUrl = (string)$res->getUri();

            if(isset($presignedUrl) && !empty($presignedUrl)){
                return (new SupportReport())->setURL($emailId, $downloadId, $presignedUrl);
            }
            return (new SupportReport())->updateStatus($downloadId, $emailId);
        }catch (\Exception $ex){
            Log::error(__CLASS__.'::'.__FUNCTION__.' Exception', [
                'error_message' => $ex->getMessage(),
                'error_at_line' => $ex->getLine(),
                'error_file' => $ex->getFile()
            ]);
            return (new SupportReport())->updateStatus($downloadId, $emailId);
        }
    }

    private function config()
    {
        return (new S3Client([
            'region'  => 'us-west-2',
            'version' => 'latest',
            'credentials' => [
              
            ],
        ]));
    }


    public function uploadBankFiles($fileName, $accountFile){
        try{

            $result = $this->client->putObject(array(
                'Bucket' => 'apihuk',
                'Key'    => $fileName,
                'Body'   => $accountFile
            ));

            if(isset($result['ObjectURL']) && !empty($result['ObjectURL'])){
                return true;
            }
            return false;
        }catch (\Exception $ex){
            Log::error(__CLASS__.'::'.__FUNCTION__.' Exception', [
                'error_message' => $ex->getMessage(),
                'error_at_line' => $ex->getLine(),
                'error_file' => $ex->getFile()
            ]);
            return false;
        }
    }

    public function storeNewForPayout($export, $fileName){
        try{

            Excel::store(
                $export,
                $fileName,
                's3-payout',
                ExcelWriter::XLSX
            );

            $cmd = $this->client->getCommand('GetObject', [
                'Bucket' => "apihuk",
                'Key' => $fileName
            ]);

            $res = $this->client->createPresignedRequest($cmd, '+1440 minutes');
            $presignedUrl = (string)$res->getUri();
            if(isset($presignedUrl) && !empty($presignedUrl)){
                return $presignedUrl;
            }
            return null;
        }catch (\Exception $ex){
            Log::error(__CLASS__.'::'.__FUNCTION__.' Exception', [
                'error_message' => $ex->getMessage(),
                'error_at_line' => $ex->getLine(),
                'error_file' => $ex->getFile()
            ]);
            return null;
        }
    }

    public function uploadPayoutBankFiles(string $fileName, $accountFile)
    {
        try{

            $result = $this->client->putObject(array(
                'Bucket' => 'apihuk',
                'Key'    => $fileName,
                'Body'   => $accountFile
            ));

            if(isset($result['ObjectURL']) && !empty($result['ObjectURL'])){
                return true;
            }
            return false;
        }catch (\Exception $ex){
            Log::error(__CLASS__.'::'.__FUNCTION__.' Exception', [
                'error_message' => $ex->getMessage(),
                'error_at_line' => $ex->getLine(),
                'error_file' => $ex->getFile()
            ]);
            return false;
        }
    }
}
