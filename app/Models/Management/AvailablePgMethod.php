<?php

namespace App\Models\Management;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class AvailablePgMethod extends Model
{
    protected $connection = 'merchant_management';
    protected $table = 'tbl_available_pg_method';
    protected $primaryKey = 'pg_method_id';
    public $incrementing = false;
    public $timestamps = false;

    public function checkMethodIdIsExists($pgMethodId)
    {
        try {
            return $this->where("pg_method_id", $pgMethodId)->exists();
        } catch (QueryException $ex) {
            return true;
        }
    }

    public function addAvailableMethod($pgMethodId, $methodName, $methodIconUrl, $subMethodIconUrl)
    {
        try {
            $this->pg_method_id = $pgMethodId;
            $this->pg_method_name = $methodName;
            $this->method_icon_url = $methodIconUrl;
            $this->sub_method_icon_url = $subMethodIconUrl;
            if($this->save()) {
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

    public function getAvailableMethods()
    {
        try {
            $methods = $this->orderBy("pg_method_id", "asc")->get();
            if($methods->count() > 0) {
                return $methods;
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
