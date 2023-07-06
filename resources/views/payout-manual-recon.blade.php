@extends('layout.master')

@section('title', 'Manual Support Logs')

@section('customStyle')

@endsection

@section('content')
    <div class="card">
        <div class="row">
            <div class="row w-100 mx-0 auth-page" id="unauthorized_user"></div>
            <div class="col-md-12" id="PayoutStatement">
                    <div class="card card-outline-info mb-0">
                        <div class="p-3">
                            <div class="btn btn-info float-right" data-toggle="modal" data-target="#BankStatement">Bank Statement</div>
                        </div>
                        <div class="card-body shadow-sm">
                            <form action="javascript:void(0)" id="PayoutStatementForm">
                                <div class="row mt-4">
                                    <div class="col-auto">
                                        <div class="form-group">
                                            <div class="input-group date datepicker dashboard-date mr-2 mb-2 mb-0 d-xl-flex">
                                                <span class="input-group-addon bg-transparent"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-calendar  text-primary"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg></span>
                                                <input type="text" class="form-control" name="daterange" autocomplete="off">
                                            </div>
                                        </div>
                                    </div><!-- Col -->
                                    <div class="col-auto">
                                        <div class="form-group">
                                            <select name="Limit" id="Limit" class="form-control">
                                                <option value="10" selected>10</option>
                                                <option value="20">20</option>
                                                <option value="50">50</option>
                                                <option value="100">100</option>
                                            </select>
                                        </div>
                                    </div><!-- Col -->
                                    <div class="col-auto">
                                        <label class="control-label"></label>
                                        <button class="btn btn-primary" type="submit">Apply</button>
                                        <button class="btn btn-danger" type="button"  onclick="resetPayoutStatementForm()">Clear</button>
                                        <button id="autRefreshBtn" class="btn btn-sm float-right btn-primary ml-1" onclick="autoRefreshStatement()"><span  id="refreshTitle"></span></button>
                                    </div><!-- Col -->
                                </div>
                            </form>
                        </div>
                    </div>

                <div class="card mt-1">
                    <div class="table-responsive mt-5">
                        <table class="table table-hover mb-0">
                            <thead>
                            <tr>
                                <th class="pt-0">id</th>
                                <th class="pt-0">file name</th>
                                <th class="pt-0">account Info</th>
                                <th class="pt-0">get</th>
                                <th class="pt-0">running</th>
                                <th class="pt-0">count</th>
                                <th class="pt-0">progress</th>
                                <th class="pt-0">total added utr</th>
                                <th class="pt-0">remark</th>
                                <th class="pt-0">file size</th>
                                <th class="pt-0">created at</th>
                            </tr>
                            </thead>
                            <tr class="preLoader">
                                <td colspan="9" align="center">
                                    <div class="spinner-grow  text-primary" role="status">
                                    </div>
                                </td>
                            </tr>
                            <tbody id="PayoutStatementData">

                            </tbody>
                        </table>
                        <div class="pl-3" id="StatementPagination"></div>
                        <a href="#" id="scroll" style="display: none;"><span></span></a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="row">
            <div class="row w-100 mx-0 auth-page" id="unauthorized_user"></div>
            <div class="col-md-12" id="PayoutManualRecon">
                <div class="card">
                    {{--<div class="p-3">
                        <div class="btn btn-primary float-right" data-toggle="modal" data-target="#generateReconPayoutFile">Bank Statement</div>
                    </div>--}}
                    <div class="card-body shadow-sm">
                        <form action="javascript:void(0)" id="payoutReconForm">
                            <div class="row mt-4">
                                <div class="col-auto">
                                    <div class="form-group">
                                        <span class="text-muted">Filter</span>
                                        <div class="d-flex ">
                                            <select name="FilterKey"  class="form-control border-right-0 ">
                                                <option value="payout_id">payout Id</option>
                                                <option value="merchant_id">merchant id</option>
                                                <option value="manual_pay_batch_id">manual pay batch id</option>
                                            </select>
                                            <input type="text" name="FilterValue" class="form-control" placeholder="Enter Search Value">
                                        </div>
                                    </div>
                                </div><!-- Col -->
                                <div class="col-auto">
                                    <div class="form-group">
                                        <span class="text-muted">Solved</span>
                                        <select name="is_solved" id="is_solved" class="form-control">
                                            <option value="all">All</option>
                                            <option value="1">Yes</option>
                                            <option value="0">No</option>
                                        </select>
                                    </div>
                                </div><!-- Col -->
                                <div class="col-auto">
                                    <div class="form-group">
                                        <span class="text-muted">Date</span>
                                        <div class="input-group date datepicker dashboard-date mr-2 mb-2 mb-0 d-xl-flex">
                                            <span class="input-group-addon bg-transparent"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-calendar  text-primary"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg></span>
                                            <input type="text" class="form-control" name="daterange" autocomplete="off">
                                        </div>
                                    </div>
                                </div><!-- Col -->

                                <div class="col-auto">
                                    <div class="form-group">
                                        <span class="text-muted">Limit</span>
                                        <select name="Limit" id="Limit" class="form-control">
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
                                    <label class="control-label"></label>
                                    <button class="btn btn-primary" type="submit">Apply</button>
                                    <button class="btn btn-danger" type="button"  onclick="resteManualPayoutForm()">Clear</button>
                                </div><!-- Col -->
                            </div>
                        </form>
                    </div>
                </div>
                <div class="row flex-grow mt-3" id="countData">
                    <div class="col-md-3 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-baseline mb-2">
                                    <h6 class="card-title mb-0">
                                        Total Return Amount
                                    </h6>
                                </div>
                                <div class="row">
                                    <div class="col-12 col-md-12 col-xl-12">
                                        <h6 class="mb-2 dz-responsive-text" id="__total_amount">0</h6>
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
                                        Total Released Amount
                                    </h6>
                                </div>
                                <div class="row">
                                    <div class="col-12 col-md-12 col-xl-12">
                                        <h6 class="mb-2 dz-responsive-text">₹ <span id="__total_released">0</span></h6>
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
                                        Total Un Released Amount
                                    </h6>
                                </div>
                                <div class="row">
                                    <div class="col-12 col-md-12 col-xl-12">
                                        <h6 class="mb-2 dz-responsive-text">₹ <span id="__total_un_released">0</span></h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card mt-1">
                    <div class="table-responsive mt-5">
                        <table class="table table-hover mb-0">
                            <thead>
                            <tr>
                                <th class="pt-0">payout id</th>
                                <th class="pt-0">payout Amount</th>
                                <th class="pt-0">merchant id</th>
                                <th class="pt-0">Status</th>
                                <th class="pt-0">PG</th>
                                <th class="pt-0">solved</th>
                                <th class="pt-0">Payout Date</th>
                                <th class="pt-0">Reconciliation at</th>
                            </tr>
                            </thead>
                            <tr class="preLoader">
                                <td colspan="9" align="center">
                                    <div class="spinner-grow  text-primary" role="status">
                                    </div>
                                </td>
                            </tr>
                            <tbody id="payoutReconData">

                            </tbody>
                        </table>
                        <div class="pl-3" id="pagination"></div>
                        <a href="#" id="scroll" style="display: none;"><span></span></a>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <div class="modal fade" id="BankStatement" tabindex="-1" role="dialog" aria-labelledby="BankStatement" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Bank Statement</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="javascript:void(0)" id="fileDataForm">
                    <div class="modal-body">
                        {{--                        <div class="form-group">--}}
                        {{--                            <label for="proxyLabel">Account No</label>--}}
                        {{--                            <select name="account_number" id="account_number" class="js-example-basic-single" data-width="100%">--}}
                        {{--                                @if(isset($availableBank))--}}
                        {{--                                    @foreach($availableBank as $bank)--}}
                        {{--                                        <option value="{{$bank->account_number}}">{{$bank->account_number}} - {{$bank->label}}</option>--}}
                        {{--                                    @endforeach--}}
                        {{--                                @endif--}}
                        {{--                            </select>--}}
                        {{--                        </div>--}}
                        {{--                        <div class="form-group">--}}
                        {{--                            <label for="proxyLabel">Bank</label>--}}
                        {{--                            <select name="bank_name" id="bank_name" class="form-control text-uppercase">--}}
                        {{--                                <option value="OPENMONEY">OPEN MONEY</option>--}}
                        {{--                                <option value="IDFCBANK">IDFC BANK</option>--}}
                        {{--                                <option value="HDFCBANK">HDFC BANK</option>--}}
                        {{--                                <option value="BANDHANBANK">BANDHAN BANK</option>--}}
                        {{--                                <option value="YESBANK">YES BANK</option>--}}
                        {{--                            </select>--}}
                        {{--                        </div>--}}
                        <div class="form-group">
                            <input type="file" id="account_file"  name="account_file" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" id="close_btn">Close</button>
                        <button type="submit" class="btn btn-primary">Add</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="showAddedUtr" tabindex="-1" role="dialog" aria-labelledby="showAddedUtr" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Show Payout Added Utr</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <table class="table table-hover mt-4" data-show-toggle="false">
                    <thead>
                    <tr>
                        <th class="pt-0">payout_id</th>
                        <th class="pt-0">merchant_id</th>
                        <th class="pt-0">manual_pay_batch_id</th>
                        <th class="pt-0">bank_statement_id</th>
                        <th class="pt-0">file_name</th>
                        <th class="pt-0">is_solved</th>
                        <th class="pt-0">created at</th>
                    </tr>
                    </thead>
                    <tbody id="addedUtrData">
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal" id="generateReconPayoutFile">
        <div class="modal-dialog">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">Generate File For Return Payout</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <form action="javascript:void(0)" id="addManualReconPayoutForm">
                    <!-- Modal body -->
                    <div class="modal-body">
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
                        <label for="result">Result # </label>
                        <div class="form-group">
                            <p class="text-muted"><b id="payoutInitAmountText"></b></p>
                            <p class="text-muted"><b id="initCountText"></b></p>
                        </div>
                        <label for="sheet_key">Bank Sheet Requirement </label>
                        <div class="form-group">
                            <div class="d-flex ">
                                <input type="text" name="sheet_value" id="sheet_value" class="form-control" placeholder="Enter Value" autocomplete="off">
                                <select name="sheet_key" id="sheet_key" class="form-control border-right-0 bg-primary text-white">
                                    <option value="">Select Your Requirement </option>
                                    <option value="count">Count</option>
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

@endsection

@section('customJs')
    <script src="{{URL::asset('custom/js/component/manual-payout-recon.js?v=21')}}"></script>
@endsection
