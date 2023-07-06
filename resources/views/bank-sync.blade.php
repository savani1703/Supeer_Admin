@extends('layout.master')

@section('title', 'Bank Sync')

@section('customStyle')
    <style>
        .invalid-blink {
            background-color: #f7948d;
        }
    </style>
@endsection

@section('content')
    <div class="card" id="bankpage">

        <div class="row">
            <div class="row w-100 mx-0 auth-page" id="unauthorized_user"></div>
            <div class="col-md-12" id="bankTransactionPage">
                <div class="card">
                    <div class="card card-outline-info mb-0">
                        <div class="p-3">
                            <h6 class="font-weight-bold float-left">Bank Sync - Total Bal :&nbsp;&nbsp; </h6>
                            <h5 class="font-weight-bolder float-left" id="totalbal">0</h5>
                            <button id="autRefreshBtn" class="btn btn-sm float-right btn-primary ml-1" onclick="autoRefreshbank()"><span  id="refreshTitle"></span></button>
                        </div>
                    </div>

                </div>
                <div class="card mt-1">
                    <div class="table-responsive mt-5">
                        <table class="table table-hover mb-0">
                            <thead>
                            <tr>
                                <th class="pt-0">Bank</th>
                                <th class="pt-0">Bank Meta Active</th>
                                <th class="pt-0">Balance</th>
                                <th class="pt-0">Turn Over</th>
                                <th class="pt-0">Sync At</th>
                                <th class="pt-0">Last Success At</th>
                                <th class="pt-0">Last Success Ago</th>
                                <th class="pt-0">Note</th>
                            </tr>
                            </thead>
                            <tr class="preLoader">
                                <td colspan="9" align="center">
                                    <div class="spinner-grow  text-primary" role="status">
                                    </div>
                                </td>
                            </tr>
                            <tbody id="BankData">

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('customJs')
    <script src="{{URL::asset('custom/js/component/bank-sync.js?v=26')}}"></script>
@endsection

