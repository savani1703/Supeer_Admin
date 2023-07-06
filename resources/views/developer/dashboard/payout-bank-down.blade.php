@extends('developer.layout.master')

@section('title', 'Payout Bank Down')

@section('customStyle')
@endsection

@section('content')
    <div class="page-content" id="dashboard_page" style="margin-top: 0px">

        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title d-flex justify-content-between">
                            Payout Bank Down

                            <button class="btn btn-primary" onclick="deleteAllBank()"> Delete All</button>
                        </h6>

                        <div class="table-responsive pt-3">
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <th>Bank</th>
                                    <th>IFSC Prefix</th>
                                    <th>is_down</th>
                                    <th>Record</th>
                                    <th>amount</th>
                                    <th>last down at</th>
                                    <th>action</th>
                                </tr>
                                </thead>
                                <tbody id="bankDownData">

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
    <script src="{{URL::asset('custom/js/component/developer/dashboard/payout-bank-down.js?v=2')}}"></script>
@endsection


