@extends('layout.master')

@section('title', 'Bank Statement')

@section('customStyle')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container .select2-selection--single {
            height: auto !important;
        }
    </style>
@endsection
@section('content')
        <div class="row">
            <div class="row w-100 mx-0 auth-page" id=""></div>
            <div class="col-md-12" id="bankStatement">
                <div class="card">
                    <div class="card card-outline-info mb-0">
                        <div class="p-3">
                            <h6 class="font-weight-bold">Bank Statement </h6>
                            <div class="btn btn-info float-right" data-toggle="modal" data-target="#BankStatement">Bank Statement</div>

                        </div>
                        <div class="card-body shadow-sm">
                            <form action="javascript:void(0)" id="statementForm">
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
                                                <option value="50" selected>50</option>
                                                <option value="100">100</option>
                                                <option value="200">200</option>
                                                <option value="300">300</option>
                                                <option value="400">400</option>
                                                <option value="500">500</option>
                                            </select>
                                        </div>
                                    </div><!-- Col -->
                                    <div class="col-auto" id="is_get" style="margin-top: -20px;">
                                        <span class="text-muted">Is Get</span>
                                        <div class="form-group">
                                            <select name="is_get" id="is_get" class="form-control  form-control-sm">
                                                <option value="">Select</option>
                                                <option value="1">True</option>
                                                <option value="0">False</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <label class="control-label"></label>
                                        <button class="btn btn-primary" type="submit">Apply</button>
                                        <button class="btn btn-danger" type="button"  onclick="resetStatementForm()">Clear</button>
                                    </div><!-- Col -->
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card" id="statementSec" style="margin-top:1.5rem !important;">
                    <div class="table-responsive mt-5">
                        <button id="autRefreshBtn" class="btn btn-sm float-right btn-primary mr-1" onclick="autoRefreshStatement()"><span  id="refreshTitle"></span></button>
                        <table class="table table-hover mb-0" data-show-toggle="false">
                            <thead>
                            <tr>
                                <th class="pt-0">File Name</th>
                                <th class="pt-0">account Info</th>
                                <th class="pt-0">Get</th>
                                <th class="pt-0">running</th>
                                <th class="pt-0">Total Count</th>
                                <th class="pt-0">progress</th>
                                <th class="pt-0">total added utr</th>
                                <th class="pt-0">remark</th>
                                <th class="pt-0">file size</th>
                                <th class="pt-0">Created At</th>
                                <th class="pt-0">Updated At</th>
                            </tr>
                            </thead>
                            <tbody id="statementData">
                            </tbody>
                        </table>
                        <div class="pl-3" id="pagination"></div>
                        <a href="#" id="scroll" style="display: none;"><span></span></a>
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
                    <h5 class="modal-title" id="exampleModalLabel">Show Added Utr</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <table class="table table-hover mt-4" data-show-toggle="false">
                    <thead>
                    <tr>
                        <th class="pt-0">bank statement id</th>
                        <th class="pt-0">file name</th>
                        <th class="pt-0">bank utr</th>
                        <th class="pt-0">Amount</th>
                        <th class="pt-0">remark</th>
                        <th class="pt-0">created at</th>
                    </tr>
                    </thead>
                    <tbody id="addedUtrData">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('customJs')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{URL::asset('custom/js/component/bank-statement.js?v=11')}}"></script>
@endsection




