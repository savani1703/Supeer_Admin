<?php

namespace App\Models\Management;

use App\Classes\Util\PgType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;

class PgRouter extends Model
{
    protected $connection = 'merchant_management';
    protected $table = 'tbl_pg_router';
    protected $primaryKey = 'pg';
    public $incrementing = false;
    public $timestamps = false;

    protected $casts = [
        'is_payin_down' => 'boolean'
    ];
    protected $appends = [
        "created_at_ist",
        "updated_at_ist",
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


    public function getRouterByPg($pg){
        try {
            $result = $this->where('pg',$pg)->first();
            if(isset($result)){
                return $result;
            }
            return null;
        }catch (QueryException $ex){
            report($ex);
            return null;
        }
    }

    public function getAllPgMetaModels()
    {
        try {
            $result = $this->orderBy("pg", "asc")->get();
            if($result->count() > 0){
                return $result;
            }
            return null;
        }catch (QueryException $ex){
            report($ex);
            return null;
        }
    }

    public function checkPgIsAvailable($pgName)
    {
        try {
            return $this->where('pg', $pgName)->exists();
        }catch (QueryException $ex){
            report($ex);
            return false;
        }
    }

    public function getPayInPgList($pgType)
    {
        try {
            $pgList = $this->newQuery();
            $pgList->whereNotNull('payin_meta_router');
            if(strcmp(strtolower($pgType), "all") !== 0) {
                $pgList->where('pg_type', $pgType);
            }
            $pgList->orderBy("pg", "asc");
            $result = $pgList->pluck("pg");
            if(sizeof($result) > 0){
                return $result;
            }
            return null;
        }catch (QueryException $ex){
            report($ex);
            return null;
        }
    }

    public function getPayoutPgList($pgType)
    {
        try {
            $pgList = $this->newQuery();
            $pgList->whereNotNull('payout_meta_router');
            if(strcmp(strtolower($pgType), "all") !== 0) {
                $pgList->where('pg_type', $pgType);
            }
            $pgList->orderBy("pg", "asc");
            $result = $pgList->pluck("pg");
            if(sizeof($result) > 0){
                return $result;
            }
            return null;
        }catch (QueryException $ex){
            report($ex);
            return null;
        }
    }

    public function getRouterForManualPayout()
    {
        try {
            $result = $this->whereNotNull("payout_meta_router")->where("pg_type", PgType::MANUAL)->get();
            if($result->count() > 0){
                return $result;
            }
            return null;
        }catch (QueryException $ex){
            report($ex);
            return null;
        }
    }

    public function getPayoutPgRoute($pgName)
    {
        try {
            $result =  $this->whereNotNull("payout_meta_router")->where('pg', $pgName)->first();
            if($result){
                return $result;
            }
            return null;
        }catch (QueryException $ex){
            report($ex);
            return null;
        }
    }
}
