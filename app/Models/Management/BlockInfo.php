<?php

namespace App\Models\Management;

use App\Classes\Util\DownloadLimit;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Log;

class BlockInfo extends Model
{
    protected $connection = 'merchant_management';
    protected $table = 'tbl_block_info';
    protected $primaryKey = 'block_data';
    public $incrementing = false;
    public $timestamps = false;

    protected $appends = [
        "created_at_ist"
    ];

    protected $casts = [
        'is_auto_block' => 'boolean'
    ];

    public function getCreatedAtIstAttribute() {
        $createdAtOriginal = $this->created_at;
        if(isset($createdAtOriginal)) {
            return Carbon::parse($createdAtOriginal, "UTC")->setTimezone("Asia/Kolkata")->format("d-m-Y H:i:s");
        }
        return $createdAtOriginal;
    }

    public function getBlockInfoData($filterData, $limit, $pageNo) {
        try {
            $blockData = $this->newQuery();
            if(isset($filterData)) {
                if(isset($filterData['block_data'])) {
                    $blockData->where("block_data", $filterData['block_data']);
                }
            }
            Paginator::currentPageResolver(function () use ($pageNo) {
                return $pageNo;
            });
            $blockData->orderBy('created_at', 'desc');
            if($blockData->count() > 0){
                return $blockData->paginate($limit);
            }
            return null;
        } catch (QueryException $ex) {
            Log::error('Error while executing SQL Query', [
                'class' => __CLASS__,
                'function' => __METHOD__,
                'file' => $ex->getFile(),
                'line_no' => $ex->getLine(),
                'error_message' => $ex->getMessage(),
            ]);
            return null;
        }
    }

    public function deleteBlockData($blockData) {
        try {
            if($this->where("block_data", $blockData)->delete()) {
                return true;
            }
            return false;
        } catch (QueryException $ex) {
            Log::error('Error while executing SQL Query', [
                'class' => __CLASS__,
                'function' => __METHOD__,
                'file' => $ex->getFile(),
                'line_no' => $ex->getLine(),
                'error_message' => $ex->getMessage(),
            ]);
            return false;
        }
    }

    public function addBlockData($blockData) {
        try {
            BlockInfo::insertOrIgnore($blockData);
            return true;
        } catch (QueryException $ex) {
            Log::error('Error while executing SQL Query', [
                'class' => __CLASS__,
                'function' => __METHOD__,
                'file' => $ex->getFile(),
                'line_no' => $ex->getLine(),
                'error_message' => $ex->getMessage(),
            ]);
            return false;
        }
    }

    public function checkIsBlock($blockData)
    {
        try {
            $result = $this->where("block_data", $blockData)->exists();
            if($result) {
                return true;
            }
            return false;
        } catch (QueryException $ex) {
            Log::error('Error while executing SQL Query', [
                'class' => __CLASS__,
                'function' => __METHOD__,
                'file' => $ex->getFile(),
                'line_no' => $ex->getLine(),
                'error_message' => $ex->getMessage(),
            ]);
            return false;
        }
    }

    public function getBlockInfoDetailsForReport($filterData, $count = true, $offset = null) {
        try {
            $blockInfo = $this->newQuery();
            if(isset($filterData) && sizeof($filterData) > 0) {
                if(isset($filterData['block_data'])) {
                    $blockInfo->where("block_data", $filterData['block_data']);
                }
                if(isset($filterData['start_date']) && !empty($filterData['start_date']) && isset($filterData['end_date']) && !empty($filterData['end_date'])) {
                    $blockInfo->whereBetween('created_at', [$filterData['start_date'], $filterData['end_date']]);
                }
            }
            if($count === true){
                $result = $blockInfo->count();
            }else{
                $result = $blockInfo->offset($offset)->limit(DownloadLimit::LIMIT)->orderBy("created_at", "desc")->get();
            }
            if($result){
                return $result;
            }
            return null;
        } catch (QueryException $ex) {
            Log::error('Error while executing SQL Query', [
                'class' => __CLASS__,
                'function' => __METHOD__,
                'file' => $ex->getFile(),
                'line_no' => $ex->getLine(),
                'error_message' => $ex->getMessage(),
            ]);
            return null;
        }
    }

    public function checkIsManuallyBlocked($block_data)
    {
        try {
            if($this->where('block_data',$block_data)->where('is_auto_block', false)->exists()){
                return true;
            }
            return false;
        }catch (QueryException $ex){
            return false;
        }
    }

    public function getManuallyBlockedCount($block_data)
    {
        try {
            $result = $this->whereIn('block_data',$block_data)->where('is_auto_block', false)->count();
            if($result){
                return $result;
            }
            return 0;
        }catch (QueryException $ex){
            return 0;
        }
    }

    public function getAutoBlockedCount($block_data)
    {
        try {
            $result = $this->whereIn('block_data',$block_data)->where('is_auto_block', true)->count();
            if($result){
                return $result;
            }
            return 0;
        }catch (QueryException $ex){
            return 0;
        }
    }
}
