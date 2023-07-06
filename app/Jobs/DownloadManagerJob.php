<?php

namespace App\Jobs;

use App\Classes\Util\AWSUtils;
use App\Classes\Util\DownloadLimit;
use App\Classes\Util\ReportType;
use App\Classes\Util\ReportUtils;
use App\Exports\BankTransactionExport;
use App\Exports\BlockInfoExport;
use App\Exports\PayoutExport;
use App\Exports\TransactionExport;
use App\Models\Management\SupportReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class DownloadManagerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $filterData;
    private $downloadId;
    private $emailId;
    private $reportType;
    private $fileName;
    private $totalRecord;


    public function __construct($filterData, $reportType, $emailId, $count, $downloadId, $fileName)
    {
        $this->filterData   = $filterData;
        $this->downloadId   = $downloadId;
        $this->emailId      = $emailId;
        $this->reportType   = $reportType;
        $this->fileName     = $fileName;
        $this->totalRecord  = $count;
    }

    public function handle()
    {
        try{
            if(strcmp($this->reportType,ReportType::TRANSACTION) === 0){
                $this->transactionBackUp();
            }
            if(strcmp($this->reportType,ReportType::PAYOUT) === 0){
                $this->payoutBackUp();
            }
            if(strcmp($this->reportType,ReportType::BANK_TRANSACTION) === 0){
                $this->bankTransactionBackUp();
            }
            if(strcmp($this->reportType,ReportType::BLOCK_INFO) === 0){
                $this->blockInfoBackUp();
            }

        }catch (\Exception $ex){
            Log::error(__CLASS__.'::'.__FUNCTION__.' Exception', [
                'error_message' => $ex->getMessage(),
                'error_at_line' => $ex->getLine(),
                'error_file' => $ex->getFile()
            ]);
        }
    }

    private function transactionBackUp()
    {
        try {
            $total = 1;
            if ($this->totalRecord > DownloadLimit::LIMIT) {
                $total = intval(ceil($this->totalRecord / DownloadLimit::LIMIT));
            }
            $transactionObj = new Collection();
            for ($i = 0; $i < $total; $i++) {
                if ($i === 0) {
                    $offset = 0;
                } else {
                    $offset = ($i * DownloadLimit::LIMIT);
                }
                $_transactionObj    = (new ReportUtils())->transactionToCollection($this->filterData, $this->emailId, $offset, $this->downloadId);
                $transactionObj     = $transactionObj->merge($_transactionObj);
            }
            // $transactionObj = $transactionObj->unique("transaction_id")
            (new AWSUtils())->storeNew((new TransactionExport($transactionObj)), $this->fileName, $this->downloadId, $this->emailId);

        }catch (\Exception $ex){
            Log::error(__CLASS__.'::'.__FUNCTION__.' Exception', [
                'error_message' => $ex->getMessage(),
                'error_at_line' => $ex->getLine(),
                'error_file' => $ex->getFile()
            ]);
            (new SupportReport())->updateStatus($this->downloadId, $this->emailId);
        }
    }

    private function bankTransactionBackUp()
    {
        try {
            $total = 1;
            if ($this->totalRecord > DownloadLimit::LIMIT) {
                $total = intval(ceil($this->totalRecord / DownloadLimit::LIMIT));
            }
            $transactionObj = new Collection();
            for ($i = 0; $i < $total; $i++) {
                if ($i === 0) {
                    $offset = 0;
                } else {
                    $offset = ($i * DownloadLimit::LIMIT);
                }
                $_transactionObj    = (new ReportUtils())->bankTransactionToCollection($this->filterData, $this->emailId, $offset, $this->downloadId);
                $transactionObj     = $transactionObj->merge($_transactionObj);
            }
            (new AWSUtils())->storeNew((new BankTransactionExport($transactionObj)), $this->fileName, $this->downloadId, $this->emailId);

        }catch (\Exception $ex){
            Log::error(__CLASS__.'::'.__FUNCTION__.' Exception', [
                'error_message' => $ex->getMessage(),
                'error_at_line' => $ex->getLine(),
                'error_file' => $ex->getFile()
            ]);
            (new SupportReport())->updateStatus($this->downloadId, $this->emailId);
        }
    }

    private function payoutBackUp()
    {
        try {
            $total = 1;
            if ($this->totalRecord > DownloadLimit::LIMIT) {
                $total = intval(ceil($this->totalRecord / DownloadLimit::LIMIT));
            }
            $payoutObj = new Collection();
            for ($i = 0; $i < $total; $i++) {
                if ($i === 0) {
                    $offset = 0;
                } else {
                    $offset = ($i * DownloadLimit::LIMIT);
                }

                $_payoutObj    = (new ReportUtils())->payoutToCollection($this->filterData, $this->emailId, $offset, $this->downloadId);
                $payoutObj     = $payoutObj->merge($_payoutObj);
            }
            (new AWSUtils())->storeNew((new PayoutExport($payoutObj)), $this->fileName, $this->downloadId, $this->emailId);

        }catch (\Exception $ex){
            Log::error(__CLASS__.'::'.__FUNCTION__.' Exception', [
                'error_message' => $ex->getMessage(),
                'error_at_line' => $ex->getLine(),
                'error_file' => $ex->getFile()
            ]);
            (new SupportReport())->updateStatus($this->downloadId, $this->emailId);
        }
    }
    private function blockInfoBackUp()
    {
        try {
            $total = 1;
            if ($this->totalRecord > DownloadLimit::LIMIT) {
                $total = intval(ceil($this->totalRecord / DownloadLimit::LIMIT));
            }
            $blockInfoObj = new Collection();
            for ($i = 0; $i < $total; $i++) {
                if ($i === 0) {
                    $offset = 0;
                } else {
                    $offset = ($i * DownloadLimit::LIMIT);
                }

                $_blockInfoObj  = (new ReportUtils())->blockInfoToCollection($this->filterData, $this->emailId, $offset, $this->downloadId);
                $blockInfoObj   = $blockInfoObj->merge($_blockInfoObj);
            }
            (new AWSUtils())->storeNew((new BlockInfoExport($blockInfoObj)), $this->fileName, $this->downloadId, $this->emailId);

        }catch (\Exception $ex){
            Log::error(__CLASS__.'::'.__FUNCTION__.' Exception', [
                'error_message' => $ex->getMessage(),
                'error_at_line' => $ex->getLine(),
                'error_file' => $ex->getFile()
            ]);
            (new SupportReport())->updateStatus($this->downloadId, $this->emailId);
        }
    }

}
