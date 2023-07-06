@extends('developer.layout.master')

@section('title', 'support Customer')

@section('customStyle')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.0.3/css/font-awesome.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/css/bootstrap-editable.css" rel="stylesheet" />
    <link href="/editor/style.css" rel="stylesheet" />

@endsection

@section('content')
            <div class="row w-100 mx-0 auth-page" id="unauthorized_user"></div>
                <div class="col-md-12" id="supportCustomerDetail">
                    <div class="card">
                         <div class="card card-outline-info mb-0">
                                <div class="p-3">
                                <h6 class="font-weight-bold">PG Routers</h6>
                                <div class="table-responsive mt-5">
                                <table class="table table-hover mb-0">
                                    <thead>
                                      <tr>
                                        <th class="pt-0">PG</th>
                                        <th class="pt-0">payin</th>
                                        <th class="pt-0">payout</th>
                                        <th class="pt-0">payin down</th>
                                        <th class="pt-0">Date</th>
                                      </tr>
                                    </thead>
                                    <tr class="preLoader">
                                          <td colspan="9" align="center">
                                             <div class="spinner-grow  text-primary" role="status">  </div>
                                         </td>
                                    </tr>
                                 <tbody id="PgRoutersData">
                            </tbody>
                        </table>
                    <a href="#" id="scroll" style="display: none;"><span></span></a>
                </div>
            </div>
        </div>
@endsection

@section('customJs')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/js/bootstrap-editable.min.js"></script>
    <script src="/editor/custom.js"></script>
    <script src="{{URL::asset('custom/js/component/merchant/editable-load.js?v=1')}}"></script>
    <script src="{{URL::asset('custom/js/component/developer/dashboard/pg-routers.js?v=2')}}"></script>
@endsection

