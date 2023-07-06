@extends('layout.master')

@section('title', 'Payouts')

@section('customStyle')

@endsection

@section('content')
    <div class="card">

        <div class="card-body" id="payoutpage">
            <div class="d-flex justify-content-between align-items-baseline mb-2">
                <h6 class="card-title mb-0">Payouts</h6>
                 <button class="float-right btn btn-primary border-0" data-target="#updateBankTransferConfigModal" data-toggle="modal">Configuration</button>
            </div>

            <div class="mb-4">
                <form action="javascript:void(0)" id="PayoutForm">
                    <div class="row mt-4">
                        <div class="col-auto">
                            <span class="text-muted">Filter</span>
                            <div class="form-group">
                                <div class="d-flex ">
                                    <select name="FilterKey" id="FilterKey" class="form-control border-right-0 bg-primary text-white">
                                        <option value="payout_id">Payout Id </option>
                                        <option value="merchant_ref_id">Merchant Ref Id</option>
                                        <option value="pg_ref_id">PG Ref Id</option>
                                        <option value="manual_pay_batch_id">manual pay batch id</option>
                                        <option value="customer_id">Customer Id</option>
                                        <option value="payout_amount">Payment Amount </option>
                                        <option value="bank_account">Account No </option>
                                        <option value="process_by">Process By</option>
                                        <option value="temp_bank_rrn">Temp Bank RRN</option>
                                        <option value="account_holder_name">Account Holder Name </option>
                                        <option value="bank_rrn">Bank RRN</option>
                                        <option value="udf1">UDF1 </option>
                                        <option value="udf2">UDF2 </option>
                                        <option value="udf3">UDF3 </option>
                                        <option value="udf4">UDF4 </option>
                                        <option value="udf5">UDF5 </option>
                                    </select>
                                    <input type="text" name="FilterValue" class="form-control" placeholder="Enter Search Value" autocomplete="off">
                                </div>
                            </div>
                        </div><!-- Col -->
                        <div class="col-auto">
                            <span class="text-muted">Status</span>
                            <div class="form-group">
                                <select name="status" id="status" class="form-control">
                                    <option value="All">All</option>
                                    <option value="Success">Success</option>
                                    <option value="LOWBAL">LOWBAL</option>
                                    <option value="Cancelled">Cancelled</option>
                                    <option value="Failed">Failed</option>
                                    <option value="Initialized">Initialized</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Processing">Processing</option>
                                    <option value="OnHold">OnHold</option>
                                </select>
                            </div>
                        </div><!-- Col -->

                        <div class="col-auto">
                            <span class="text-muted">Client</span>
                            <div class="form-group">
                                <select name="merchant_id" id="merchantList" class="form-control">
                                    <option value="All" selected>All</option>
                                    @if(isset($merchantList))
                                        @foreach($merchantList as $merchant)
                                            <option value="{{$merchant['merchant_id']}}">{{$merchant['merchant_name']}}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div><!-- Col -->
                        <div class="col-auto" id="PGName">
                            <span class="text-muted">PG</span>
                            <div class="form-group">
                                <select name="pg_name" id="pg_name" class="form-control">
                                    <option value="All">ALL</option>
                                    @if(isset($payoutPgList))
                                        @foreach($payoutPgList as $pg)
                                            <option value="{{$pg}}">{{$pg}}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="col-auto" id="payoutPG">
                            <span class="text-muted">PG Account</span>
                            <div class="form-group">
                                <select name="meta_id" id="meta_id" class="form-control">
                                    <option value="All">ALL</option>
                                </select>
                            </div>
                        </div><!-- Col -->
                        <div class="col-auto" id="payout_type">
                            <span class="text-muted">Type</span>
                            <div class="form-group">
                                <select name="payout_type" id="payout_type" class="form-control">
                                    <option value="All">ALL</option>
                                    <option value="IMPS">IMPS</option>
                                    <option value="RTGS">RTGS</option>
                                    <option value="Manual">Manual</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-auto">
                            <span class="text-muted">Date</span>
                            <div class="form-group">
                                <div class="input-group date datepicker dashboard-date mr-2 mb-2 mb-0 d-xl-flex">
                                    <span class="input-group-addon bg-transparent"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-calendar  text-primary"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg></span>
                                    <input type="text" class="form-control" name="daterange" autocomplete="off">
                                </div>
                            </div>
                        </div><!-- Col -->
                        <div class="col-auto">
                            <span class="text-muted">Success Date</span>
                            <div class="form-group">
                                <div class="input-group date datepicker dashboard-date mr-2 mb-2 mb-0 d-xl-flex">
                                    <span class="input-group-addon bg-transparent"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-calendar  text-primary"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg></span>
                                    <input type="text" class="form-control success-daterange" name="daterange1"   autocomplete="off">
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <span class="text-muted">Limit</span>
                            <div class="form-group">
                                <select name="limit" id="limit" class="form-control">
                                    <option value="50" selected>50</option>
                                    <option value="100">100</option>
                                    <option value="200">200</option>
                                    <option value="300">300</option>
                                    <option value="400">400</option>
                                    <option value="500">500</option>
                                </select>
                            </div>
                        </div><!-- Col -->
                        <div class="col-auto">
                            <span class="text-muted">Min/Max Amount</span>
                            <div class="form-group">
                                <input type="text" name="min_amount" class="form-control form-control-sm" placeholder="Min">
                            </div>
                            <div class="form-group">
                                <input type="text" name="max_amount" class="form-control form-control-sm" placeholder="Max">
                            </div>
                        </div>
                        <div class="col-auto mt-4">
                            <button class="btn btn-primary border-0" type="submit">Apply</button>
                            <button class="btn btn-danger border-0" type="button" onclick="resetPayoutForm()">Clear</button>
                        </div>
                        <div class="col-auto mt-4">
                            <button class="btn btn-primary" type="button"  onclick="generateReport()">Generate Report</button>
                        </div>
                        <div class="col-auto mt-4">
                            <button id="autRefreshBtn" class="btn btn-sm float-right btn-primary ml-1" onclick="autoRefreshTransaction()"><span  id="refreshTitle"></span></button>
                        </div>
                    </div>

                </form>
                <div class="container demo">
                    <div class="modal left fade" id="payoutModal" tabindex="" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Payout Id</h5> <h5 class="text-primary" id="payout_id" onclick="copy(this,'Payout Id Copy')" data-toggle="tooltip" data-placement="top" title="Copy Payout Id"></h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="nav flex-sm-column flex-row">
                                        <div class="preLoaderModal" style="display: flex;justify-content: center;align-items: center;">
                                            <div>
                                                <div class="spinner-grow  text-primary" role="status">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row flex-grow mt-3" id="countData">
                <div class="col-md-3 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-baseline mb-2">
                                <h6 class="card-title mb-0">
                                    Total Payout
                                </h6>
                            </div>
                            <div class="row">
                                <div class="col-12 col-md-12 col-xl-12">
                                    <h6 class="mb-2 dz-responsive-text" id="__total_payout">0</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-baseline mb-2">
                                <h6 class="card-title mb-0">
                                    PAYOUT AMOUNT
                                </h6>
                            </div>
                            <div class="row">
                                <div class="col-12 col-md-12 col-xl-12">
                                    <h6 class="mb-2 dz-responsive-text">₹ <span id="__payout_amount">0</span></h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-baseline mb-2">
                                <h6 class="card-title mb-0">
                                    Total Payout Amount
                                </h6>
                            </div>
                            <div class="row">
                                <div class="col-12 col-md-12 col-xl-12">
                                    <h6 class="mb-2 dz-responsive-text">₹ <span id="__total_payout_amount">0</span></h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-baseline mb-2">
                                <h6 class="card-title mb-0">
                                    Total Payout Fees
                                </h6>
                            </div>
                            <div class="row">
                                <div class="col-12 col-md-12 col-xl-12">
                                    <h6 class="mb-2 dz-responsive-text">₹ <span id="__total_payout_fees">0</span></h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="table-responsive" id="payoutDataZone">
                <table class="table table-hover mb-0" id="">
                    <thead>
                    <tr>
                        <th class="pt-0"></th>
                        <th class="pt-0">Order</th>
                        <th class="pt-0">Bank</th>
                        <th class="pt-0">Cust Detail</th>
                        <th class="pt-0">Client</th>
                        <th class="pt-0">status</th>
                        <th class="pt-0">type</th>
                        <th class="pt-0">Amount/Fees</th>
                        <th class="pt-0">pg</th>
                        <th class="pt-0">Date</th>
                        <th class="pt-0">Action</th>
                    </tr>
                    </thead>
                    <tbody id="PayoutData">
                    </tbody>
                </table>
                <div id="pagination">
                </div>
                <a href="#" id="scroll" style="display: none;"><span></span></a>
            </div>


            <div class="modal fade" id="updateBankTransferConfigModal" tabindex="-1"  aria-labelledby="updateBankTransferConfigModal" data-backdrop="static" data-keyboard="false" aria-hidden="true">
                <div class="modal-dialog modal-md modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Merchant Bank Config</h5>
                            <button type="button" class="btn btn-danger" onclick="resetLowBal()">
                                Reset Low Bal
                            </button>
                            <button type="button" class="close" id="closeBtn" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="table-responsive">

                            <form action="javascript:void(0)" id="updateBankTransferConfigForm">

                                <div class="modal-body2">
                                    <div class="custom-control custom-switch mb-2">
                                        <input type="checkbox" name="is_auto_transfer_enable" class="custom-control-input custom-input-lg" id="is_auto_transfer_enable">
                                        <label class="custom-control-label" for="is_auto_transfer_enable">Is Auto Payout Enable</label>
                                    </div>
                                    <div class="custom-control custom-switch mb-2">
                                        <input type="checkbox" name="is_payout_status_call_enable" class="custom-control-input custom-input-lg" id="is_payout_status_call_enable">
                                        <label class="custom-control-label" for="is_payout_status_call_enable">Is Payout Status Call Enable</label>
                                    </div>
                                    <div class="custom-control custom-switch mb-2">
                                        <input type="checkbox" name="small_first" class="custom-control-input custom-input-lg" id="small_first">
                                        <label class="custom-control-label" for="small_first">Is Small First</label>
                                    </div>
                                    <div class="custom-control custom-switch mb-2">
                                        <input type="checkbox" name="large_first" class="custom-control-input custom-input-lg" id="large_first">
                                        <label class="custom-control-label" for="large_first">Is Large First</label>
                                    </div>


                                    <div class="custom-control custom-switch mb-2">
                                        <input type="checkbox" name="is_auto_level_active" class="custom-control-input custom-input-lg" id="is_auto_level_active">
                                        <label class="custom-control-label" for="is_auto_level_active">Is Auto Level</label>
                                    </div>


                                    <div class="form-group">
                                        <label for="max_manual_transfer_limit">Max Manual Transfer Limit</label>
                                        <input type="number" name="max_manual_transfer_limit" id="max_manual_transfer_limit" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label for="min_manual_transfer_limit">Min Manual Transfer Limit</label>
                                        <input type="number" name="min_manual_transfer_limit" id="min_manual_transfer_limit" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label for="max_lowbal_limit">Max LowBal Limit</label>
                                        <input type="number" name="max_lowbal_limit" id="max_lowbal_limit" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label for="max_pending_limit">Max Pending Limit</label>
                                        <input type="number" name="max_pending_limit" id="max_pending_limit" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label for="max_last_failed_limit">Max Last Failed Limit</label>
                                        <input type="number" name="max_last_failed_limit" id="max_last_failed_limit" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label for="min_auto_transfer_limit">Min Auto Transfer Limit</label>
                                        <input type="number" name="min_auto_transfer_limit" id="min_auto_transfer_limit" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label for="max_auto_transfer_limit">Max Auto Transfer Limit</label>
                                        <input type="number" name="max_auto_transfer_limit" id="max_auto_transfer_limit" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label for="payout_delayed_in_seconds">Payout Delay in Seconds</label>
                                        <input type="number" name="payout_delayed_in_seconds" id="payout_delayed_in_seconds" class="form-control">
                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-primary">Update</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="updateManualPayout" tabindex="-1"  aria-labelledby="updateManualPayout" data-backdrop="static" data-keyboard="false" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content modal-lg">
                        <div class="modal-header">
                            <h5 class="modal-title">Update Manual Bank Transaction</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form action="javascript:void(0)" id="updateManualPayoutForm">
                            <div class="modal-body">
                                <div class="row" style="display: none">
                                    <input id="payout_id" type="text" class="form-control" name="payout_id">
                                </div>
                                <div class="col-auto">
                                    <div class="form-group col-12">
                                        <label  class="col-form-label">Enter Bank Utr</label>
                                        <input id="payout_utr" type="text" class="form-control" name="payout_utr" placeholder="Enter Bank Utr">
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <span class="text-muted"></span>
                                    <div class="form-group">
                                        <select name="payout_status" id="payout_status" class="form-control">
                                            <option value="">Select Status</option>
                                            <option value="Success">Success</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button  type="submit" class="btn btn-primary">Update Payout Status </button>
                                    <button id="close_btn" type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('customJs')
    <script src="{{URL::asset('custom/js/component/payout.js?v=36')}}"></script>
@endsection
