<?php

namespace App\Models\Management;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Log;

class BankPerseUtr extends Model
{
    protected $connection = 'payment_manual';
    protected $table = 'tbl_bank_parse_utr';
    protected $primaryKey = 'id';
    public $incrementing = false;
    public $timestamps = false;


    public function showAddedUtr($id)
    {
        try {
            $data=$this->where('bank_statement_id',$id)->get();
            if($data->count() > 0){
                return $data;
            }
            return null;
        }catch (QueryException $ex){
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
