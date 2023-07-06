<?php

namespace App\Classes\Util;

use App\Models\Management\BankTransactions;
use App\Models\Management\BlockInfo;
use App\Models\Management\Payout;
use App\Models\Management\PgRouter;
use App\Models\Management\SupportReport;
use App\Models\Management\Transactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ReportUtils
{
    public function transactionToCollection($filterData, $emailId, $offset, $downloadId) {
        $collection = collect();
        $pgRouterarray=array();
        $pgMetaarray=array();
        try {

            $result = (new Transactions())->getTransactionDetailsForReport($filterData,false, $offset);

            if(isset($result) && !empty($result)){
                $collection = collect();
                $chunk = $result->chunk(DownloadLimit::CHUNK);
                foreach ($chunk as $_chunk){
                    $progress = sizeof($_chunk);
                    (new SupportReport())->setProgress($downloadId, $emailId, $progress);
                    foreach ($_chunk as $key => $_result) {
                        $pg_label=null;
                        $bank_name=null;
                        $upi_id=null;
                        $bank_holder_account=null;
                        $bank_holder_ifsc=null;

                        if(isset($_result->meta_id) && isset($_result->pg_name)) {
                            $pgRouter=null;
                            if(array_key_exists($_result->pg_name,$pgRouterarray))
                            {
                                $pgRouter = $pgRouterarray[$_result->pg_name];
                            }else {
                                $pgRouter = (new PgRouter())->getRouterByPg($_result->pg_name);
                                $pgRouterarray[$_result->pg_name]=$pgRouter;
                            }
                            if(isset($pgRouter)) {
                                if(isset($pgRouter->payin_meta_router)) {
                                    $pgMeta=null;
                                    if(array_key_exists($_result->meta_id,$pgMetaarray))
                                    {
                                        $pgMeta = $pgMetaarray[$_result->meta_id];
                                    }else {
                                        $pgMeta = (new $pgRouter->payin_meta_router)->getMetaForTransactionById($_result->meta_id);
                                        $pgMetaarray[$_result->meta_id]=$pgMeta;
                                    }
                                    if(isset($pgMeta)) {
                                        $pg_label = $pgMeta->label;
                                        if(isset($pgMeta->account_number)) $bank_holder_account = $pgMeta->account_number;
                                        if(isset($pgMeta->upi_id)) $upi_id = $pgMeta->upi_id;
                                        if(isset($pgMeta->ifsc_code)) $bank_holder_ifsc = $pgMeta->ifsc_code;
                                        if(isset($pgMeta->bank_name)) $bank_name = $pgMeta->bank_name;
                                    }
                                }
                            }
                        }

                        $collection[] = array(
                            'transaction_id'    => $_result->transaction_id ? $_result->transaction_id : 'N/A',
                            'merchant_id'       => $_result->merchant_id ? $_result->merchant_id : 'N/A',
                            'merchant_name'     => isset($_result->merchantDetails) ? ($_result->merchantDetails->merchant_name ? : 'N/A') : 'N/A',
                            'merchant_order_id' => $_result->merchant_order_id ? $_result->merchant_order_id : 'N/A',
                            'customer_id'       => $_result->customer_id ? $_result->customer_id : 'N/A',
                            'customer_name'     => $_result->customer_name ? $_result->customer_name : 'N/A',
                            'customer_email'    => $_result->customer_email ? $_result->customer_email : 'N/A',
                            'customer_mobile'   => $_result->customer_mobile ? $_result->customer_mobile : 'N/A',
                            'currency'          => $_result->currency ? $_result->currency : 'N/A',
                            'payment_status'    => $_result->payment_status ? $_result->payment_status : 'N/A',
                            'payment_amount'    => $_result->payment_amount ? $_result->payment_amount : 'N/A',
                            'pg_fees'           => $_result->pg_fees ? $_result->pg_fees : '0',
                            'associate_fees'    => $_result->associate_fees ? $_result->associate_fees : '0',
                            'payable_amount'    => $_result->payable_amount ? $_result->payable_amount : '0',
                            'pg_res_code'       => $_result->pg_res_code ? $_result->pg_res_code : 'N/A',
                            'pg_res_msg'        => $_result->pg_res_msg ? $_result->pg_res_msg : 'N/A',
                            'pg_ref_id'         => $_result->pg_ref_id ? $_result->pg_ref_id : 'N/A',
                            'bank_rrn'          => $_result->bank_rrn ? $_result->bank_rrn : 'N/A',
                            'payment_method'    => $_result->payment_method ? $_result->payment_method : 'N/A',
                            'pg_name'           => $_result->pg_name ? $_result->pg_name : 'N/A',
                            'pg_label'           => $pg_label ? $pg_label : 'N/A',
                            'bank_name'           => $bank_name ? $bank_name : 'N/A',
                            'bank_holder_account' => $bank_holder_account ? $bank_holder_account : 'N/A',
                            'bank_holder_ifsc'    => $bank_holder_ifsc ? $bank_holder_ifsc : 'N/A',
                            'upi_id'            => $upi_id ? $upi_id : 'N/A',
                            'is_webhook_call'   => $_result->is_webhook_call ? $_result->is_webhook_call : 'N/A',
                            'callback_url'      => $_result->callback_url ? $_result->callback_url : 'N/A',
                            'customer_ip'       => $_result->customer_ip ? $_result->customer_ip : 'N/A',
                            'created_at'        => $_result->created_at_ist ? $_result->created_at_ist : 'N/A',
                        );

                    }
                }
                if ($collection) {
                    return $collection;
                }
            }
            return null;
        } catch (\Exception $ex) {
            return null;
        }
    }

    public function bankTransactionToCollection($filterData, $emailId, $offset, $downloadId) {
        $collection = collect();
        try {
            $result = (new BankTransactions())->getBankTransactionDetailsForReport($filterData,false, $offset);
            if(isset($result) && !empty($result)){
                $collection = collect();
                $chunk = $result->chunk(DownloadLimit::CHUNK);
                foreach ($chunk as $_chunk){
                    $progress = sizeof($_chunk);
                    (new SupportReport())->setProgress($downloadId, $emailId, $progress);
                    foreach ($_chunk as $key => $_result) {
                        $collection[] = array(
                            'created_at'        => $_result->created_at_ist ? $_result->created_at_ist : 'N/A',
                            'account_holder'    => isset($_result->bankDetails) ? $_result->bankDetails->account_holder_name : 'N/A',
                            'account_number'    => $_result->account_number ? $_result->account_number : 'N/A',
                            'upi_id'            => isset($_result->bankDetails) ? $_result->bankDetails->upi_id : 'N/A',
                            'bank_name'         => $_result->bank_name ? $_result->bank_name : 'N/A',
                            'amount'            => $_result->amount ? $_result->amount : 'N/A',
                            'description'       => $_result->description ? $_result->description : 'N/A',
                            'is_get'            => $_result->isget ? "Yes" : "No",
                            'payment_utr'       => $_result->payment_utr ? $_result->payment_utr : 'N/A',
                            'payment_mode'      => $_result->payment_mode ? $_result->payment_mode : 'N/A',
                            'transaction_mode'  => $_result->payment_mode ? $_result->transaction_mode : 'N/A',
                            'transaction_date'  => $_result->transaction_date ? $_result->transaction_date : 'N/A',
                            'entry_date'        => $_result->entry_date ? $_result->entry_date : 'N/A',
                            'id'                => $_result->id ? $_result->id : 'N/A',
                            'uniqe_hash'        => $_result->uniqe_hash ? $_result->uniqe_hash : 'N/A',
                            'ref_id'            => $_result->ref_id ? $_result->ref_id : 'N/A',
                        );
                    }
                }
                if ($collection) {
                    return $collection;
                }
            }
            return null;
        } catch (\Exception $ex) {
            return null;
        }
    }

    public function payoutToCollection($filterData, $emailId, $offset, $downloadId)
    {
        try {
            $result = (new Payout())->getPayoutDetailsForReport($filterData,false, $offset);
            if(isset($result) && !empty($result)){
                $collection = collect();
                $chunk = $result->chunk(DownloadLimit::CHUNK);
                foreach ($chunk as $_chunk){
                    $progress = sizeof($_chunk);
                    (new SupportReport())->setProgress($downloadId, $emailId, $progress);
                    foreach ($_chunk as $key => $_result) {
                        $pg_label=null;
                        if(isset($_result->meta_id) && isset($_result->pg_name)) {
                            $meta_id=$_result->meta_id;
                            $pg_name=$_result->pg_name;
                            $pg_label = Cache::remember($meta_id.$pg_name, 120, function () use ($meta_id,$pg_name) {
                                $pg_label=null;
                                $pgRouter = (new PgRouter())->getRouterByPg($pg_name);
                                if(isset($pgRouter)) {
                                    if(isset($pgRouter->payout_meta_router)) {
                                        $pgMeta = (new $pgRouter->payout_meta_router)->getMetaForPayoutByMetaId($meta_id);
                                        if(isset($pgMeta)) {
                                            $pg_label = $pgMeta->label;
                                        }
                                    }
                                }
                                return $pg_label;
                            });
                        }
                        $collection[] = array(
                            'payout_id'             => $_result->payout_id ? $_result->payout_id : 'N/A',
                            'merchant_ref_id'       => $_result->merchant_ref_id ? $_result->merchant_ref_id : 'N/A',
                            'merchant_id'           => $_result->merchant_id ? $_result->merchant_id : 'N/A',
                            'merchant_name'         => isset($_result->merchantDetails) ? ($_result->merchantDetails->merchant_name ? : 'N/A') : 'N/A',
                            'payout_amount'         => $_result->payout_amount ? $_result->payout_amount : '0',
                            'payout_fees'           => $_result->payout_fees ? $_result->payout_fees : '0',
                            'total_amount'          => $_result->total_amount ? $_result->total_amount : '0',
                            'payout_type'           => $_result->payout_type ? $_result->payout_type : 'N/A',
                            'account_holder_name'   => $_result->account_holder_name ? $_result->account_holder_name : 'N/A',
                            'bank_account'          => $_result->bank_account ? $_result->bank_account : 'N/A',
                            'ifsc_code'             => $_result->ifsc_code ? $_result->ifsc_code : 'N/A',
                            'bank_name'             => $_result->bank_name ? $_result->bank_name : 'N/A',
                            'payout_status'         => $_result->payout_status ? $_result->payout_status : 'N/A',
                            'pg_ref_id'             => $_result->pg_ref_id ? $_result->pg_ref_id : 'N/A',
                            'pg_response_msg'       => $_result->pg_response_msg ? $_result->pg_response_msg : 'N/A',
                            'bank_rrn'              => $_result->bank_rrn ? $_result->bank_rrn : 'N/A',
                            'is_webhook_called'     => $_result->is_webhook_called ? $_result->is_webhook_called : 'N/A',
                            'payout_by'             => $_result->payout_by ? $_result->payout_by : 'N/A',
                            'pg_name'               => $_result->pg_name ? $_result->pg_name : 'N/A',
                            'pg_label'              => $pg_label ? $pg_label : 'N/A',
                            'is_approved'           => $_result->is_approved ? "Yes" : 'No',
                            'approved_at'           => $_result->approved_at ? $_result->approved_at : 'N/A',
                            'created_at'            => $_result->created_at_ist ? $_result->created_at_ist : 'N/A',
                        );
                    }
                }
                if ($collection) {
                    return $collection;
                }
            }
            return null;
        } catch (\Exception $ex) {
            Log::info($ex->getMessage());
            return null;
        }
    }

    public function blockInfoToCollection($filterData, $emailId, $offset, $downloadId)
    {
        try {
            $result = (new BlockInfo())->getBlockInfoDetailsForReport($filterData,false, $offset);
            if(isset($result) && !empty($result)){
                $collection = collect();
                $chunk = $result->chunk(DownloadLimit::CHUNK);
                foreach ($chunk as $_chunk){
                    $progress = sizeof($_chunk);
                    (new SupportReport())->setProgress($downloadId, $emailId, $progress);
                    foreach ($_chunk as $key => $_result) {
                        $collection[] = array(
                            'block_data'            => $_result->block_data ? $_result->block_data : 'N/A',
                            'created_at'            => $_result->created_at_ist ? $_result->created_at_ist : 'N/A',
                        );
                    }
                }
                if ($collection) {
                    return $collection;
                }
            }
            return null;
        } catch (\Exception $ex) {
            Log::info($ex->getMessage());
            return null;
        }
    }
}
