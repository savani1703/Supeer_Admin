@extends('layout.master')

@section('title', 'PG Management Dashboard')

@section('customStyle')
    <style>
        .bg-danger-light {
            background-color: rgb(255 0 0 / 20%) !important;
        }
    </style>
@endsection
@section('content')
    <div class="card mb-2">
        <div class="p-3">
            <h6 class="font-weight-bold">PG Management Dashboard</h6>
        </div>
        <div class="card-body">
            <form action="javascript:void(0)" class="mb-2" id="pgDashboardForm">
                <div class="row mt-1">
                    <div class="col-auto">
                        <div class="form-group">
                            <div class="input-group date datepicker dashboard-date mr-2 mb-2 mb-0 d-xl-flex">
                                <select name="pg_system" id="pg_system" class="form-control" aria-label="PG Type">
                                    <option value="PAYIN">PayIn</option>
                                    <option value="PAYOUT">Payout</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="form-group">
                            <div class="input-group date datepicker dashboard-date mr-2 mb-2 mb-0 d-xl-flex">
                                <select name="pg_name" id="pg_name" class="form-control" aria-label="PG Name">
                                    <option value="ALL">ALL</option>
                                    @if(isset($payInPgList))
                                        @foreach($payInPgList as $pg)
                                            <option value="{{$pg}}">{{$pg}}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="form-group">
                            <div class="input-group date datepicker dashboard-date mr-2 mb-2 mb-0 d-xl-flex">
                                <select name="pg_account" id="pg_account" class="form-control" aria-label="PG Account">
                                    <option value="ALL">ALL</option>
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
                        <button class="btn btn-dark" type="reset" onclick="resetPgDashboardForm()">Clear</button>
                    </div>
                </div>
            </form>

            <div class="table-responsive" id="dashboardData">
                <table class="table table-hover table-bordered">
                    <thead>
                        <tr>
                            <th>PG</th>
                            <th>Collection/Withdrawal Turnover</th>
                            <th>Total Limit</th>
                            <th>Remaining Limit</th>
                        </tr>
                    </thead>
                    <tbody id="pgDashboardData">

                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection

@section('customJs')
    <script src="{{URL::asset('custom/js/component/dashboard/pg-dashboard.js?v=3')}}"></script>
@endsection
