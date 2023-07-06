@extends('developer.layout.master')

@section('title', 'PG PayIn Summary')

@section('customStyle')
@endsection

@section('content')
    <div class="page-content" id="dashboard_page" style="margin-top: 0px">

        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title d-flex justify-content-between">
                            PG PayIn Summary
                        </h6>

                        <form action="javascript:void(0)" class="mb-2" id="payINSummaryForm">
                            <div class="row mt-1">
                                <div class="col-auto">
                                    <div class="form-group">
                                        <div class="input-group date datepicker dashboard-date mr-2 mb-2 mb-0 d-xl-flex">
                                            <select name="merchant_id" id="merchant_id" class="form-control" aria-label="PG Name">
                                                <option value="All" selected>All</option>
                                                @if(isset($merchantList))
                                                    @foreach($merchantList as $merchant)
                                                        <option value="{{$merchant['merchant_id']}}">{{$merchant['merchant_name']}}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <div class="form-group">
                                        <div class="input-group date datepicker dashboard-date mr-2 mb-2 mb-0 d-xl-flex">
                                            <span class="input-group-addon bg-transparent"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-calendar  text-primary"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg></span>
                                            <input type="text" class="form-control dashboard-daterange" name="daterange1"  id="dashboardDatePicker" autocomplete="off">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <label class="control-label"></label>
                                    <button class="btn btn-primary" type="submit">Apply</button>
                                </div>
                                <div class="col-auto">
                                    <label class="control-label"></label>
                                    <button class="btn btn-dark" type="reset" onclick="resetPayInSummaryForm()">Clear</button>
                                </div>
                            </div>
                        </form>

                        <div class="table-responsive pt-3">
                            <table class="table table-bordered table-hover">
                                <thead class="">
                                <tr>
                                    <th>#</th>
                                    <th>PG</th>
                                    <th>Processing</th>
                                    <th>Pending</th>
                                    <th>Success</th>
                                    <th>Failed</th>
                                    <th>Last Transaction At</th>
                                    <th>Total</th>
                                </tr>
                                </thead>
                                <tbody id="pgSummaryData">

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@section('customJs')
    <script src="{{URL::asset('custom/js/component/developer/dashboard/payin-summary.js?v=3')}}"></script>
@endsection


