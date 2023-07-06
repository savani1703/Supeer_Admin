@extends('layout.master')
@section('title') Batch Transfer | DigiPayZone Control Panel   @endsection
@section('customStyle')
    <style>
        #autRefreshBtn.active {
            background: #0c8b44 !important;
            color: #fff !important;
        }
    </style>
@endsection
@section('content')
    <div class="row">
        <div class="row w-100 mx-0 auth-page" id="unauthorized_user"></div>
        <div class="col-md-12" id="payoutDetail">
            <div class="card">
                <div class="card card-outline-info mb-0">
                    <div class="card-header head-border">
                        <h4 class="m-b-0 text-white font-weight-bold"> Batch Transfer
                            {{--<button class="btn btn-sm float-right text-primary btn-rounded bg-white ml-1" data-target="#updateBankTransferConfigModal" data-toggle="modal">Configuration</button>--}}
                            <button class="btn btn-sm float-right text-primary btn-rounded bg-white ml-1" data-target="#BankTransferStatusFileModal" data-toggle="modal">Upload Status File</button>
                            <button class="btn btn-sm float-right text-primary btn-rounded bg-white ml-1" data-toggle="modal" data-target="#generateBankTransferFile">Generate Bank File</button>
                            <button class="btn btn-sm float-right text-primary btn-rounded bg-white ml-1" data-target="#accountLoad" data-toggle="modal">Account / Bank Load</button>
                        </h4>
                    </div>
                </div>
            </div>

            <div class="card p-3 mb-4">
                <form action="javascript:void(0)" id="ManualPayoutForm">
                    <div class="row mt-4">
                        <div class="col-auto">
                            <span class="text-muted">Filter</span>
                            <div class="form-group">
                                <div class="d-flex ">
                                    <select name="FilterKey" id="FilterKey" class="form-control border-right-0 bg-primary text-white">
                                        <option value="batch_id">Batch Id </option>
{{--                                        <option value="bank_name">Bank Name </option>--}}
                                    </select>
                                    <input type="text" name="FilterValue" class="form-control" placeholder="Enter Search Value" autocomplete="off">
                                </div>
                            </div>
                        </div><!-- Col -->
                        <div class="col-auto">
                            <span class="text-muted">Bank Name</span>
                            <div class="form-group">
                                <select name="debit_account" id="debit_account" class="form-control">
                                    <option value="">--- Select Debit Account ---</option>
                                    @if(isset($availableBank))
                                        @foreach($availableBank as $_availableBank)
                                            <option value="{{$_availableBank['debit_account']}}">
                                                {{$_availableBank['bank_name']}} -
                                                {{$_availableBank['account_label']}} -
                                                {{$_availableBank['debit_account']}}
                                            </option>
                                        @endforeach
                                    @endif
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
                        <div class="col-auto mt-4">
                            <button class="btn btn-primary border-0" type="submit">Apply</button>
                            <button class="btn btn-danger border-0" type="button" onclick="resetPayoutForm()">Clear</button>
                        </div>
                    </div>
                </form>
            </div>


            <div class="card mt-1" id="manualPayoutZone">
                <div class="table-responsive mt-5">
                    <table class="table table-hover mb-0 table-bordered">
                        <thead>
                        <tr>
                            <th>Date</th>
                            <th>Batch#</th>
                            <th>Bank</th>
                            <th>Amount</th>
                            <th>Record</th>
                            <th>File</th>
                            <th>action</th>
                            <th>action</th>
                        </tr>
                        </thead>
                        <tbody id="ManualPayoutData">

                        </tbody>
                    </table>
                    <div class="pl-3" id="pagination"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payout Selection Modal -->
    <div class="modal" id="generateBankTransferFile">
        <div class="modal-dialog">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">Generate File</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <form action="javascript:void(0)" id="addManualPayoutForm">
                    <!-- Modal body -->
                    <div class="modal-body">
                        <div class="custom-control custom-switch mb-2 float-right">
                            <input type="checkbox" name="is_manual_level_active" class="custom-control-input custom-input-lg" id="is_manual_level_active">
                            <label class="custom-control-label" for="is_manual_level_active">Is Manual Level</label>
                        </div>
                        <div class="form-group">
                            <label for="manual_bank">Bank Name</label>
                            <p class="text-muted"><small>Bank - Label - A/C</small></p>
                            <select name="bank_id" id="manual_bank" class="form-control">
                                <option value="">--- Select Debit Account ---</option>
                                @if(isset($availableBank))
                                    @foreach($availableBank as $_availableBank)
                                        <option value="{{$_availableBank['bank_name']}}#{{$_availableBank['account_id']}}">
                                            {{$_availableBank['bank_name']}} -
                                            {{$_availableBank['account_label']}} -
                                            {{$_availableBank['debit_account']}}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="merchant_id">Merchant Name</label>
                            <select name="merchant_id" id="merchant_id" class="form-control">
                                <option value="">--- Select Merchant ---</option>
                                @if(isset($activeMerchant))
                                    @foreach($activeMerchant as $_activeMerchant)
                                        <option value="{{$_activeMerchant->merchant_id}}">
                                            {{$_activeMerchant->merchantDetails->merchant_name}}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <label for="result">Result # </label>
                        <div class="form-group">
                            <p class="text-muted"><b id="payoutInitAmountText"></b></p>
                            <p class="text-muted"><b id="initCountText"></b></p>
                        </div>
                        <label for="login_key">Logical Amount </label>
                        <div class="form-group">
                            <div class="d-flex ">
                                <input type="text" name="logic_amount" id="logic_amount" class="form-control" placeholder="Enter Amount" autocomplete="off">
                                <select name="logic_key" id="logic_key" class="form-control border-right-0 bg-primary text-white">
                                    <option value="">Select Amount Logic </option>
                                    <option value="less_than">Less than</option>
                                    <option value="greater_than">Greater Than</option>
                                    <option value="equal">Equal</option>
                                </select>
                            </div>
                        </div>
                        <label for="prepare_result">Prepare Result # </label>
                        <div class="form-group">
                            <p class="text-muted"><b id="total_logical_init_count"></b></p>
                            <p class="text-muted"><b id="total_logical_init_amount"></b></p>
                        </div>

                        <label for="sheet_key">Bank Sheet Requirement </label>
                        <div class="form-group">
                            <div class="d-flex ">
                                <input type="text" name="sheet_value" id="sheet_value" class="form-control" placeholder="Enter Value" autocomplete="off">
                                <select name="sheet_key" id="sheet_key" class="form-control border-right-0 bg-primary text-white">
                                    <option value="">Select Your Requirement </option>
                                    <option value="count">Count</option>
                                    <option value="amount">Amount</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Modal footer -->
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary" >Generate </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Payout Status Modal -->
    <div class="modal" id="BankTransferStatusFileModal">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">Upload Status File</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <form action="javascript:void(0)"  id="ManualPayoutStatusForm">
                    <!-- Modal body -->
                    <div class="modal-body">
                        <div class="row">

                            <div class="form-group col-auto">
                                <label for="manual_bank">Bank Name</label>
                                <select name="bank_id" id="bank_file_id" class="form-control" required>
                                    <option value="">--- Select Debit Account ---</option>
                                    @if(isset($availableBank))
                                        @foreach($availableBank as $_availableBank)
                                            <option value="{{$_availableBank['bank_name']}}#{{$_availableBank['account_id']}}">
                                                {{$_availableBank['bank_name']}} -
                                                {{$_availableBank['account_label']}} -
                                                {{$_availableBank['debit_account']}}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <div class="form-group col-auto">
                                <label for="amountRange">Upload File</label>
                                <input type="file" name="bank_file" id="bank_file" class="form-control" required>
                            </div>

                            <div class="col-auto">
                                <button type="submit" class="btn btn-primary btn-status-file-upload" style="margin-top: 32px;">Upload File</button>
                            </div>

                        </div>

                        <div class="mt-3 mb-2" id="payoutSuccessOperationZone" style="display: none">
                            <h5 class="mb-2">Updated Payouts</h5>

                            <div class="table-responsive mb-2">
                                <table class="table table-bordered">
                                    <thead>
                                    <tr>
                                        <th>Payout ID</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>UTR</th>
                                        <th>Date</th>
                                    </tr>
                                    </thead>
                                    <tbody id="payoutSuccessOperationData">
                                    <tr>

                                    </tr>
                                    </tbody>
                                </table>
                            </div>

                            <h5 class="mb-2 mt-5">Error To Updated Payouts</h5>

                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                    <tr>
                                        <th>Payout ID</th>
                                    </tr>
                                    </thead>
                                    <tbody id="payoutErrorOperationData">
                                    <tr>

                                    </tr>
                                    </tbody>
                                </table>
                            </div>

                        </div>

                    </div>

                </form>
            </div>
        </div>
    </div>

    <!-- Payout Selection Modal -->
    <div class="modal" id="updateBankTransferConfigModal">
        <div class="modal-dialog">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">Payout Configuration</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <form action="javascript:void(0)" id="updateBankTransferConfigForm">
                    <!-- Modal body -->
                    <div class="modal-body">
                        <div class="custom-control custom-switch mb-2">
                            <input type="checkbox" name="is_auto_transfer_enable" class="custom-control-input custom-input-lg" id="is_auto_transfer_enable">
                            <label class="custom-control-label" for="is_auto_transfer_enable">Is Auto Payout Enable</label>
                        </div>
                        <div class="form-group">
                            <label for="max_manual_transfer_limit">Max Manual Transfer Limit</label>
                            <input type="number" name="max_manual_transfer_limit" id="max_manual_transfer_limit" class="form-control">
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
                    </div>

                    <!-- Modal footer -->
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="accountLoad" tabindex="-1"  aria-labelledby="accountLoad" data-backdrop="static" data-keyboard="false" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" style="max-width: 1200px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Account Load Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="customerDataTable">
                        <thead>
                        <tr>
                            <th>Bank Details</th>
                            <th>Total Amount</th>
                            <th>Total Count</th>
                        </tr>
                        </thead>
                        <tbody id="accountLoadDetails">
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

@endsection
@section('customJs')
    <script src="{{URL::asset('/custom/js/component/manual-payout.js?v=52')}}"></script>
@endsection

