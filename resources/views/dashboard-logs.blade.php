@extends('layout.master')

@section('title', 'Merchant Dashboard Logs')

@section('customStyle')

@endsection

@section('content')
    <div class="card">
            <div class="row">
                <div class="row w-100 mx-0 auth-page" id="unauthorized_user"></div>
                <div class="col-md-12" id="supportLogsDetail">
                    <div class="card">
                        <div class="card card-outline-info mb-0">
                            <div class="card-header head-border">
                                <h6 class="m-b-0  font-weight-bold">Merchant Dashboard Logs</h6>
                            </div>
                        </div>

                        <div class="card">
                            <div class="table-responsive mt-5">
                                <table class="table table-hover mb-0">
                                    <thead>
                                    <tr>
                                        <th class="pt-0">action type</th>
                                        <th class="pt-0">action</th>
                                        <th class="pt-0">request ip</th>
                                        <th class="pt-0">user agent</th>
                                        <th class="pt-0">created at</th>
                                    </tr>
                                    </thead>
                                    <tr class="preLoader">
                                        <td colspan="9" align="center">
                                            <div class="spinner-grow  text-primary" role="status">
                                            </div>
                                        </td>
                                    </tr>
                                    <tbody id="DashboardLogs">

                                    </tbody>
                                </table>
                                <div class="pl-3" id="pagination"></div>
                                <a href="#" id="scroll" style="display: none;"><span></span></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('customJs')
    <script src="{{URL::asset('custom/js/component/dashboard-logs.js?v=2')}}"></script>
@endsection
