@extends('layout.master')

@section('title', isset($merchantDetail) ? $merchantDetail->merchant_name." Payout Meta" : "Payout Meta")

@section('customStyle')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.0.3/css/font-awesome.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/css/bootstrap-editable.css" rel="stylesheet" />
    <link href="/editor/style.css" rel="stylesheet" />
@endsection

@section('content')
    <div id="merchant_payoutPage">
    <div class="card mb-2">
        <div class="card-body" id="Payout_Meta">
            <div class="d-flex justify-content-between align-items-baseline mb-2">
                <h4 class="card-title pb-1">{{isset($merchantDetail) ? $merchantDetail->merchant_name : "" }} Payout Meta</h4>
                <h6>
                    <span class="font-weight-bold d-block"><span class="text-muted">Merchant Name</span>: {{isset($merchantDetail) ? $merchantDetail->merchant_name : "" }}</span>
                    <span class="font-weight-bold d-block"><span class="text-muted">Merchant ID</span>: {{isset($merchantDetail) ? $merchantDetail->merchant_id : "" }}</span>
                    <span class="font-weight-bold d-block"><span class="text-muted">Account Status</span>: {{isset($merchantDetail) ? $merchantDetail->account_status : "" }}</span>
                </h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="payoutDataTab" >
                    <thead>
                    <tr>
                        <th>id</th>
                        <th>merchant Id</th>
                        <th>Pg Info</th>
                        <th>Balance</th>
                        <th>Active </th>
                        <th>Date </th>
                    </tr>
                    </thead>
                    <tbody id="merchant_Payout">
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body" id="available_payout">
            <div class="d-flex justify-content-between align-items-baseline mb-2">
                <h6 class="card-title pb-4">Available Payout Meta</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="payoutDataTab" >
                    <thead>
                    <tr>
                        <th>merchant id</th>
                        <th>pg name</th>
                        <th>account id</th>
                        <th>label </th>
                    </tr>
                    </thead>
                    <tbody id="available_merchant_Payout">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </div>
@endsection

@section('customJs')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/js/bootstrap-editable.min.js"></script>
    <script src="/editor/custom.js"></script>
    <script src="{{URL::asset('custom/js/component/merchant/editable-load.js?v=1')}}"></script>
    <script src="{{URL::asset('custom/js/component/merchant/merchant-payout.js?v=8')}}"></script>
@endsection

