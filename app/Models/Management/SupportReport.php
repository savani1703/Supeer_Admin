<?php

namespace App\Models\Management;

use App\Classes\Util\ReportStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Log;

class SupportReport extends Model
{
    protected $connection = 'merchant_management';
    protected $table = 'tbl_support_report';
    protected $primaryKey = 'download_id';
    public $incrementing = false;
    public $timestamps = false;

    protected $appends = [
        "created_at_ist",
        "updated_at_ist",
        "expire_at_ist",
        "is_expire",
    ];


    public function getCreatedAtIstAttribute() {
        $createdAtOriginal = $this->created_at;
        if(isset($createdAtOriginal)) {
            return Carbon::parse($createdAtOriginal, "UTC")->setTimezone("Asia/Kolkata")->format("d-m-Y H:i:s");
        }
        return $createdAtOriginal;
    }

    public function getUpdatedAtIstAttribute() {
        $updatedAtOriginal = $this->updated_at;
        if(isset($updatedAtOriginal)) {
            return Carbon::parse($updatedAtOriginal, "UTC")->setTimezone("Asia/Kolkata")->format("d-m-Y H:i:s");
        }
        return $updatedAtOriginal;
    }

    public function getExpireAtIstAttribute() {
        $updatedAtOriginal = $this->expired_at;
        if(isset($updatedAtOriginal)) {
            return Carbon::parse($updatedAtOriginal, "UTC")->setTimezone("Asia/Kolkata")->format("d-m-Y H:i:s");
        }
        return $updatedAtOriginal;
    }

    protected $fillable = [
        'progress'
    ];

    public function getIsExpireAttribute(){
        $expired_at = $this->attributes['expired_at'];
        if($expired_at){
            $currentDateAndTime = Carbon::now();
            $expired_at = Carbon::parse($expired_at);
            $second = $currentDateAndTime->diffInSeconds($expired_at,false);
            if($second <= 0){
                return true;
            }
            return false;
        }
    }

    public function getDownloadByHash($hash)
    {
        try{
            $result = $this->where('sha1hash',$hash)->where(function ($query){
                $query->where('status','=', ReportStatus::Processing)
                    ->orWhere('status','=',ReportStatus::Success);
            })->first();

            if($result){
                return $result;
            }
            return null;
        }catch (QueryException $ex){
            Log::error(__CLASS__.'::'.__FUNCTION__.' Query Exception', [
                'error_message' => $ex->getMessage(),
                'error_at_line' => $ex->getLine(),
                'error_file' => $ex->getFile()
            ]);
            return null;
        }
    }

    public function addRecord($reportType, $emailId, $count, $downloadId, $fileName, $sha1Hash)
    {
        try{
            /* $result = $this->where('sha1hash',$sha1Hash)->where(function ($query){
                 $query->where('status','!=', ReportStatus::Processing)
                     ->orWhere('status','!=',ReportStatus::Success);
             })->exists();
             dd($result);*/

            $this->download_id  = $downloadId;
            $this->email_id     = $emailId;
            $this->status       = ReportStatus::Processing;
            $this->file_name    = $fileName;
            $this->report_type  = $reportType;
            $this->count        = $count;
            $this->sha1hash     = $sha1Hash;
            $this->expired_at   = Carbon::now()->addDays(1);
            if ($this->save()) {
                return true;


            }
            return false;
        }catch (QueryException $ex){
            Log::error(__CLASS__.'::'.__FUNCTION__.' Query Exception', [
                'error_message' => $ex->getMessage(),
                'error_at_line' => $ex->getLine(),
                'error_file' => $ex->getFile()
            ]);
            return false;
        }
    }

    public function setProgress($downloadId, $userName, $progress)
    {
        try{
            $result = $this->where('download_id',$downloadId)->where('email_id',$userName)->increment('progress',$progress);
            if($result){
                return true;
            }
            return false;
        }catch (QueryException $ex){
            Log::error(__CLASS__.'::'.__FUNCTION__.' Query Exception', [
                'error_message' => $ex->getMessage(),
                'error_at_line' => $ex->getLine(),
                'error_file' => $ex->getFile()
            ]);
            return false;
        }
    }

    public function updateStatus($download_id, $emailId)
    {
        try{
            $result = $this->where('download_id',$download_id)->where('email_id',$emailId)->update(['status' => 'Failed']);
            if($result){
                return true;
            }
            return false;
        }catch (QueryException $ex){
            Log::error(__CLASS__.'::'.__FUNCTION__.' Query Exception', [
                'error_message' => $ex->getMessage(),
                'error_at_line' => $ex->getLine(),
                'error_file' => $ex->getFile()
            ]);
            return false;
        }
    }

    public function setURL($emailId, $downloadId, $download_url)
    {
        try{
            $result = $this->where('download_id',$downloadId)->where('email_id',$emailId)->update([
                'download_url'  => $download_url,
                'status'        => 'Success',
            ]);

            if($result){
                return true;
            }
            return false;
        }catch (QueryException $ex){
            Log::error(__CLASS__.'::'.__FUNCTION__.' Query Exception', [
                'error_message' => $ex->getMessage(),
                'error_at_line' => $ex->getLine(),
                'error_file' => $ex->getFile()
            ]);
            return false;
        }
    }

    public function getDownloadReportDetails($limit, $pageNo)
    {
        try {

            $obj = $this->newQuery();

            Paginator::currentPageResolver(function () use ($pageNo) {
                return $pageNo;
            });

            $obj->select([
                'download_id',
                'email_id',
                'status',
                'file_name',
                'report_type',
                'download_url',
                'count',
                'progress',
                'expired_at',
                'created_at',
                'updated_at'
            ]);

            $obj->orderBy('created_at', 'desc');

            if($obj->count()){
                return $obj->paginate($limit);
            }
            return null;

        }catch (QueryException $ex){
            Log::error(__CLASS__.'::'.__FUNCTION__.' Query Exception', [
                'error_message' => $ex->getMessage(),
                'error_at_line' => $ex->getLine(),
                'error_file' => $ex->getFile()
            ]);
            return null;
        }
    }
}
