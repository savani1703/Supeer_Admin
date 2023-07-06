@extends('layout.master')

@section('title', 'Merchant Management Dashboard')

@section('customStyle')
    <style>
        button.btn.btn-sm.btn-default.active.btn-pg-summary-filter.mr-1, button.btn.btn-sm.btn-default.active.btn-pg-summary-filter-wi.mr-1{
            background: #727cf5;
            color: #fff;
        }
    </style>
@endsection
@section('content')

    <div class="card mb-2" id="chartSec">
        <div class="card">
            <div class="card-body">
                <form action="javascript:void(0)" class="mb-2" id="chartFormData">
                    <div class="row mt-1">
                        <div class="col-auto">
                            <span class="text-muted">Date</span>
                            <div class="form-group">
                                <div class="input-group date datepicker dashboard-date mr-2 mb-2 mb-0 d-xl-flex">
                                    <span class="input-group-addon bg-transparent"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-calendar  text-primary"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg></span>
                                    <input type="text" class="form-control dashboard-daterange" name="daterange1"  id="dashboardDatePicker" autocomplete="off">
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <span class="text-muted">Client</span>
                            <div class="form-group">
                                <select name="merchant_id" id="merchantList" class="form-control mid-multiple-select">
                                    <option value="All" selected>All</option>
                                    @if(isset($merchantList))
                                        @foreach($merchantList as $merchant)
                                            <option value="{{$merchant['merchant_id']}}">{{$merchant['merchant_name']}}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div><!-- Col -->
                        <div class="col-auto">
                            <span class="text-muted">Level</span>
                            <div class="form-group">
                                <select name="cust_level" id="cust_level" class="form-control mid-multiple-select">
                                    <option value="All" selected>All</option>
                                    <option value="1"> Level 1</option>
                                    <option value="2"> Level 2</option>
                                    <option value="3"> Level 3</option>
                                    <option value="4"> Level 4</option>
                                </select>
                            </div>
                        </div><!-- Col -->
                        <div class="col-auto mt-4">
                            <label class="control-label"></label>
                            <button class="btn btn-primary" type="submit">Apply</button>
                            <button class="btn btn-danger btn-sm" type="button"  onclick="resetChartFilter()">Clear</button>
                        </div>
                    </div>
                </form>
                <div class="d-flex justify-content-between align-items-baseline mb-4 mb-md-3">
                    <h6 class="card-title mb-0">Transaction Chart</h6>
                </div>
                <div id="txnHoursChart">
                </div>
            </div>
        </div>
    </div>
        <div class="card mb-2" id="dashboardSummery">
            <div class="p-3">
                <h6 class="font-weight-bold">Merchant Management Dashboard</h6>
            </div>
            <div class="card-body">
                <div class="col-md-12  col-xl-12 col-sm-12 stretch-card">
                    <div class="row flex-grow" id="dashboard_summery">
                    </div>
                </div>
            </div>
        </div>
        <div class="card mb-2" id="transactionSummery">
            <div class="card-body">
                <form action="javascript:void(0)" class="mb-2" id="tSummeryForm">
                    <div class="row mt-1">
                        <div class="col-auto">
                            <span class="text-muted">Date</span>
                            <div class="form-group">
                                <div class="input-group date datepicker dashboard-date mr-2 mb-2 mb-0 d-xl-flex">
                                    <span class="input-group-addon bg-transparent"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-calendar  text-primary"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg></span>
                                    <input type="text" class="form-control dashboard-daterange" name="daterange1"  id="dashboardDatePicker" autocomplete="off">
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <span class="text-muted">Client</span>
                            <div class="form-group">
                                <select name="merchant_id" id="merchantListforSummery" class="form-control mid-multiple-select">
                                    <option value="All" selected>All</option>
                                    @if(isset($merchantList))
                                        @foreach($merchantList as $merchant)
                                            <option value="{{$merchant['merchant_id']}}">{{$merchant['merchant_name']}}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div><!-- Col -->

                        <div class="col-auto mt-4">
                            <label class="control-label"></label>
                            <button class="btn btn-primary" type="submit">Apply</button>
                            <button class="btn btn-danger btn-sm" type="button"  onclick="resetTSummeryFilter()">Clear</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="p-3">
                <h6 class="font-weight-bold">Transaction Summary</h6>
            </div>
            <div class="card-body">
                <div class="col-md-12  col-xl-12 col-sm-12 stretch-card">
                    <div class="row flex-grow" id="transactionSummerydata">
                    </div>
                </div>
            </div>
        </div>
        <div class="card mb-2" id="payouttxnSummery">
            <div class="p-3">
                <h6 class="font-weight-bold">Payout Summary</h6>
            </div>
            <div class="card-body">
                <div class="col-md-12  col-xl-12 col-sm-12 stretch-card">
                    <div class="row flex-grow" id="payouttxnSummerydata">
                    </div>
                </div>
            </div>
        </div>
        <div class="card mb-2" id="payoutsummery">
            <div class="p-3">
                <h6 class="font-weight-bold">Payout Dashboard at  {{\Illuminate\Support\Carbon::now('Asia/Kolkata')->toDateTimeString()}}</h6>
            </div>
            <div class="card-body">
                <div class="col-md-12  col-xl-12 col-sm-12 stretch-card">
                    <div class="row flex-grow" id="payout_summery">
                    </div>
                </div>
            </div>
        </div>

        {{--    Merchant PayIn/Payout Summary    --}}
        @include("dashboard.mm-dashboard.merchant-summary")


        {{--    PG Collection/Withdrawal Summary    --}}
        @include("dashboard.mm-dashboard.pg-summary")

@endsection

@section('customJs')
    <script src="{{URL::asset('custom/js/component/dashboard.js?v=18')}}"></script>
@endsection



