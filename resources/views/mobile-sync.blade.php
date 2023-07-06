@extends('layout.master')

@section('title', 'Mobile Sync')

@section('customStyle')
    <style>
        .invalid-blink {
            background-color: #f7948d;
        }
    </style>
@endsection

@section('content')
    <div class="card" id="mobilepage">

        <div class="row">
            <div class="row w-100 mx-0 auth-page" id="unauthorized_user"></div>
            <div class="col-md-12" id="MobileTxnPage">
                <div class="card mt-1">
                    <div class="table-responsive mt-5">
                        <table class="table table-hover mb-0">
                            <thead>
                            <tr>
                                <th class="pt-0">Hardware Id</th>
                                <th class="pt-0">Account Number</th>
                                <th class="pt-0">Battery</th>
                                <th class="pt-0">Last Sync Date</th>
                                <th class="pt-0">Last Sync Ago</th>
                                <th class="pt-0">Created At</th>
                            </tr>
                            </thead>
                            <tr class="preLoader">
                                <td colspan="9" align="center">
                                    <div class="spinner-grow  text-primary" role="status">
                                    </div>
                                </td>
                            </tr>
                            <tbody id="MobileData">

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('customJs')
    <script src="{{URL::asset('custom/js/component/mobile-sync.js?v=26')}}"></script>
@endsection

