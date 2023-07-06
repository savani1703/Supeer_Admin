@extends('developer.layout.master')

@section('title', 'Swift Customer')

@section('customStyle')
@endsection

@section('content')
    <div class="page-content" id="dashboard_page" style="margin-top: 0px">
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title d-flex justify-content-between">
                            Swift Customer
                        </h6>
                        <div class="card-body shadow-sm">
                            <form action="javascript:void(0)" id="SMSLogsFilterForm">
                                <div class="row">
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
                                    </div>
                                    <div class="col-auto">
                                        <span class="text-muted">To</span>
                                        <div class="form-group">
                                            <div class="d-flex ">
                                                <select name="search_key"  class="form-control border-right-0 ">
                                                    @if(isset($bankList))
                                                        {{$bankList}}
                                                        @foreach($bankList as $bnk)
                                                            <option value="{{$bnk['account_number']}}">{{$bnk['account_holder_name']}}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <span class="text-muted">Per</span>
                                        <div class="form-group">
                                            <select name="is_get" id="is_get" class="form-control">
                                                <option value="100" selected>ALL</option>
                                                <option value="10">10 %</option>
                                                <option value="20">20 %</option>
                                                <option value="30">30 %</option>
                                                <option value="40">40 %</option>
                                                <option value="50">50 %</option>
                                                <option value="60">60 %</option>
                                                <option value="70">70 %</option>
                                                <option value="80">80 %</option>
                                                <option value="90">90 %</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-auto">
                                        <span class="text-muted">From</span>
                                        <div class="form-group">
                                            <div class="d-flex ">
                                                <select name="search_key"  class="form-control border-right-0 ">
                                                    @if(isset($bankList))
                                                        {{$bankList}}
                                                        @foreach($bankList as $bnk)
                                                            <option value="{{$bnk['account_number']}}">{{$bnk['account_holder_name']}}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-auto mt-4">
                                        <button class="btn btn-primary border-0" type="submit">Apply</button>
                                        <button class="btn btn-danger border-0" type="reset"  onclick="resetSMSLogsFilter()">Clear</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <a href="#" id="scroll" style="display: none;"><span></span></a>

@endsection

@section('customJs')
{{--    <script src="{{URL::asset('custom/js/component/developer/dashboard/sms-logs.js?v=2')}}"></script>--}}
@endsection


