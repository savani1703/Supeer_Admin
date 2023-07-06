@extends('risk.layout.master')

@section('title', 'State')

@section('customStyle')

@endsection

@section('content')
    <div class="card">
        <div class="row">
            <div class="row w-100 mx-0 auth-page" id="unauthorized_user"></div>
            <div class="col-md-12" id="custStateDetail">
                <div class="card">
                    <div class="card card-outline-info mb-0">
                        <div class="p-3">
                            <h6 class="font-weight-bold">State</h6>
                        </div>
                        <div class="card-body shadow-sm">
                            <form action="javascript:void(0)" id="custDateFilter">
                                <div class="row mt-2">
                                    <div class="col-auto">
                                        <span class="text-muted">Filter</span>
                                        <div class="form-group">
                                            <div class="d-flex ">
                                                <select name="FilterKey"  class="form-control border-right-0 ">
                                                    <option value="customer_id">Cust Id</option>
                                                </select>
                                                <input type="text" name="FilterValue" class="form-control" placeholder="Enter Search Value">
                                            </div>
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
                                    <div class="col-auto">
                                        <span class="text-muted">Date</span>
                                        <div class="form-group">
                                            <div class="input-group date datepicker dashboard-date mr-2 mb-2 mb-0 d-xl-flex">
                                                <span class="input-group-addon bg-transparent"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-calendar  text-primary"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg></span>
                                                <input type="text" class="form-control" name="daterange" autocomplete="off">
                                            </div>
                                        </div>
                                    </div><!-- Col -->
                                    <div class="col-auto" style="margin-top: 1.4rem !important;">
                                        <label class="control-label"></label>
                                        <button class="btn btn-primary" type="submit">Apply</button>
                                        <button class="btn btn-danger" type="button"  onclick="resetFilter()">Clear</button>
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
                                <th class="pt-0">cust state</th>
                                <th class="pt-0">Level</th>
                                <th class="pt-0">total txn</th>
                                <th class="pt-0">success txn</th>
                                <th class="pt-0">Processing txn</th>
                                <th class="pt-0">Pending txn</th>
                                <th class="pt-0">Initialized txn</th>
                                <th class="pt-0">Failed txn</th>
                                <th class="pt-0">Not Attempted</th>
                            </tr>
                            </thead>
                            <tr class="preLoader">
                                <td colspan="9" align="center">
                                    <div class="spinner-grow  text-primary" role="status">
                                    </div>
                                </td>
                            </tr>
                            <tbody id="CustStateData">
                            </tbody>
                        </table>
                        <div class="pl-3" id="pagination"></div>
                        <a href="#" id="scroll" style="display: none;"><span></span></a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="customerDetails" tabindex="-1"  aria-labelledby="customerDetails" data-backdrop="static" data-keyboard="false" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" style="max-width: 1200px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Customer Details</h5>
                    <button class="btn btn-danger" onclick="riskCustBlockByHid()">Block All Details</button>
                    <input type="text" id="BrowserId"  hidden>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="customerDataTable">
                        <thead>
                        <tr>
                            <th>HId</th>
                            <th>Customer Id</th>
                            <th>Merchant Name</th>
                            <th>Customer Email</th>
                            <th>Customer Mobile</th>
                            <th>Last Success Date</th>

                        </tr>
                        </thead>
                        <tbody id="customerData">
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
    <script src="{{URL::asset('custom/js/component/risk/state.js?v=6')}}"></script>
@endsection
