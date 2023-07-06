@extends('risk.layout.master')

@section('title', 'Dashboard')

@section('customStyle')
    <style>
        h5{
           font-size: 2rem;
        }
    </style>
@endsection
@section('content')
    <div class="card mb-2" id="" style="zoom: 1;">
        <div class="p-3">
            <h6 class="font-weight-bold">Customer Leveling Dashboard</h6>
            <div class="col-3 offset-9">
                <div class="form-group">
                    <form action="javascript:void(0)" id="custDateFilter">
                        <div class="row mt-4">
                            <div class="col-auto">
                                <div class="form-group">
                                    <div class="input-group date datepicker dashboard-date mr-2 mb-2 mb-0 d-xl-flex">
                                        <span class="input-group-addon bg-transparent"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-calendar  text-primary"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg></span>
                                        <input type="text" class="form-control" name="daterange" autocomplete="off">
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <label class="control-label"></label>
                                <button class="btn btn-primary" type="submit">Apply</button>
                                <button class="btn btn-danger" onclick="resetDateFilter()" type="reset">Clear</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="card-body" id="custSummery">
            <div class="col-md-12  col-xl-12 col-sm-12 stretch-card">
                <div class="row flex-grow" id="cust_Summery">
                </div>
            </div>
        </div>
    </div>
    <div class="card mb-2">
        <div class="p-3">
            <h6 class="font-weight-bold">Customer Report</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive" id="" style="position: static; zoom: 1;">
                <table class="table table-hover table-bordered ">
                    <thead>
                    <tr>
                        <th>Bank name</th>
                        <th>Total User</th>
                        <th>Total Block</th>
                        <th>Total Safe</th>
                        <th>Today New</th>
                    </tr>
                    </thead>
                    <tbody id="">
                    <tr>
                        <td>0</td>
                        <td>0</td>
                        <td>0</td>
                        <td>0</td>
                        <td>0</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
@section('customJs')
    <script src="{{URL::asset('custom/js/component/risk/risk.js?v=3')}}"></script>
@endsection



