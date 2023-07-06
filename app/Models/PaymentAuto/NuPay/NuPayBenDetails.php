<?php

namespace App\Models\PaymentAuto\NuPay;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;

/**
 * @mixin Builder
 */
class NuPayBenDetails extends Model
{
    protected $connection = 'payment_auto';
    protected $table = 'tbl_nupay_bene_details';
    protected $primaryKey = 'id';
    public $incrementing = false;
    public $timestamps = false;

    public function AddBeneDetails($account_number,$ifsc_code,$bene_id)
    {

        try {
            $this->account_number=$account_number;
            $this->ifsc_code=$ifsc_code;
            $this->bene_id=$bene_id;
            $this->save();
        }catch (QueryException $ex){
            report($ex);
        }
    }
    public function getBeneId($account_number,$ifsc_code)
    {
        try {
           return $this->where('account_number',$account_number)->where('ifsc_code',$ifsc_code)->value('bene_id');
        }catch (QueryException $ex){
            report($ex);
        }
        return null;
    }

}
