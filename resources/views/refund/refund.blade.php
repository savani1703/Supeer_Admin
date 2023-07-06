@extends('layout.master')

@section('title', 'Refund')

@section('customStyle')
@endsection

@section('content')
    <div class="card">
        <div class="row">
            <div class="col-md-12" id="refundPage">
                <div class="card">
                    <div class="card card-outline-info mb-0">
                        <div class="p-3">
                            <h6 class="font-weight-bold">Refund</h6>
                        </div>
                        <div class="card-body shadow-sm pt-0">
                            <form action="javascript:void(0)" id="refundForm">
                                <div class="row mt-4">
                                    <div class="col-auto">
                                        <div class="form-group">
                                            <div class="d-flex ">
                                                <select name="FilterKey"  class="form-control border-right-0 ">
                                                    <option value="refund_id">Refund Id</option>
                                                    <option value="transaction_id">Transaction Id</option>
                                                </select>
                                                <input type="text" name="FilterValue" class="form-control" placeholder="Enter Search Value">
                                            </div>
                                        </div>
                                    </div><!-- Col -->
                                    <div class="col-auto form-group">
                                        <select class="form-select form-select-sm" name="status">
                                            <option value="ALL">ALL</option>
                                            <option value="Success">Success</option>
                                            <option value="Failed">Failed</option>
                                        </select>
                                    </div>
                                    <div class="col-auto">
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
                                    <div class="col-auto">
                                        <label class="control-label"></label>
                                        <button class="btn btn-primary" type="submit">Apply</button>
                                        <button class="btn btn-danger" type="button" onclick="resetRefundForm()">Clear</button>
                                    </div><!-- Col -->
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card mt-1">
                    <div class="table-responsive mt-5">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th class="pt-0">ID</th>
                                    <th class="pt-0">Client</th>
                                    <th class="pt-0">TRANSACTION ID</th>
                                    <th class="pt-0">AMOUNT</th>
                                    <th class="pt-0">DESC</th>
                                    <th class="pt-0">STATUS</th>
                                    <th class="pt-0">INTERNAL STATUS</th>
                                    <th class="pt-0">Date</th>
                                </tr>
                            </thead>
                            <tbody id="RefundData">

                            </tbody>
                        </table>
                        <div class="pl-3" id="pagination"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('customJs')
    <script src="{{URL::asset('custom/js/component/refund/refund.js?v=1')}}"></script>

@endsection

