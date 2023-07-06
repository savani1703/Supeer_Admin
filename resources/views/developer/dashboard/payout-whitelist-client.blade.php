@extends('developer.layout.master')

@section('title', 'Payout WhiteList Client\'s')

@section('customStyle')
    <link href="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap-switch-button@1.1.0/css/bootstrap-switch-button.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.0.3/css/font-awesome.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/css/bootstrap-editable.css" rel="stylesheet" />
    <link href="/editor/style-black.css" rel="stylesheet" />
@endsection

@section('content')
    <div class="page-content" id="dashboard_page" style="margin-top: 0px">

        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title d-flex justify-content-between">
                            Payout WhiteList Client's

                            {{--                            <button class="btn btn-primary"> Add Client</button>--}}
                        </h6>

                        <div class="table-responsive pt-3">
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <th>Client</th>
                                    <th>Auto Status</th>
                                    <th>min Limit</th>
                                    <th>Max Limit</th>
                                    <th>Manual Status</th>
                                </tr>
                                </thead>
                                <tbody id="payoutWlDownData">

                                </tbody>
                            </table>
                            <div class="pl-3" id="pagination"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
@section('customJs')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/js/bootstrap-editable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/linways/table-to-excel@v1.0.4/dist/tableToExcel.js"></script>
    <script src="/editor/custom.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap-switch-button@1.1.0/dist/bootstrap-switch-button.min.js"></script>
    <script src="{{URL::asset('custom/js/component/developer/dashboard/payout-wl-client.js?v=6')}}"></script>
@endsection


