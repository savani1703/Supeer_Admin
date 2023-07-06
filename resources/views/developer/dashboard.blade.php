@extends('developer.layout.master')

@section('title', 'Dashboard')

@section('customStyle')
@endsection
@section('content')
    <div class="card">
        <div class="row">
            <div class="row w-100 mx-0 auth-page"></div>
            <div class="col-md-12">
                <div class="card" id="dashboardSummery">
                    <div class="card card-outline-info mb-0">
                        <div class="p-3">
                            <h6 class="font-weight-bold"> Dashboard</h6>
                        </div>
                        <div class="card-body">
                            <div class="col-md-12  col-xl-12 col-sm-12 stretch-card">
                                <div class="row flex-grow" id="dashboard_summery">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
       </div>
@endsection

@section('customJs')
    <script src="{{URL::asset('custom/js/component/developer/dashboard.js?v=2')}}"></script>
@endsection



