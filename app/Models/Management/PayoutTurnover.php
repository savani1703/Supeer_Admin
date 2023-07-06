<?php

namespace App\Models\Management;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayoutTurnover extends Model
{
    protected $connection = 'merchant_management';
    protected $table = 'tbl_payout_turnover';
    protected $primaryKey = 'id';
    public $incrementing = false;
    public $timestamps = false;

    public function getSummary($pgName, $pgAccount, $startDate, $endDate)
    {
        try {
            $turnover = $this->newQuery();
            $turnover->whereBetween("pay_date", [$startDate, $endDate]);
            if(strcmp(strtolower($pgName), "all") !== 0) {
                $turnover->where("pg_name", $pgName);
            }
            if(strcmp(strtolower($pgAccount), "all") !== 0) {
                $turnover->where("meta_merchant_id", $pgAccount);
            }

            $turnover->groupBy("meta_merchant_id");

            $turnover->select([
                "pg_name",
                "meta_merchant_id",
                DB::raw("SUM(total_amount) as total_turnover")
            ]);

            $result = $turnover->get();

            if($result->count() > 0) {
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
}
