@extends('layout.master')

@section('title', isset($merchantDetail) ? $merchantDetail->merchant_name." PayIn Meta" : "PayIn Meta")

@section('customStyle')
    <link href="{{URL::asset("/custom/plugin/footable/css/footable.standalone.css")}}" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.0.3/css/font-awesome.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/css/bootstrap-editable.css" rel="stylesheet" />
    <link href="/editor/style.css" rel="stylesheet" />
@endsection

@section('content')
    <div class="card">

        <div class="card-body" id="Merchant_page">
            <div class="d-flex justify-content-between align-items-baseline mb-2">
                <h4 class="card-title pb-1">{{isset($merchantDetail) ? $merchantDetail->merchant_name : "" }} PayIn Meta</h4>
                <h6>
                    <span class="font-weight-bold d-block"><span class="text-muted">Merchant Name</span>: {{isset($merchantDetail) ? $merchantDetail->merchant_name : "" }}</span>
                    <span class="font-weight-bold d-block"><span class="text-muted">Merchant ID</span>: {{isset($merchantDetail) ? $merchantDetail->merchant_id : "" }}</span>
                    <span class="font-weight-bold d-block"><span class="text-muted">Account Status</span>: {{isset($merchantDetail) ? $merchantDetail->account_status : "" }}</span>
                </h6>
            </div>
            <div class="float-lg-left mt-4">
                <button id="fedStartBtn" class="btn btn-sm float-right btn-primary mb-2  ml-1"  data-merchant-id="{{isset($merchantDetail) ? $merchantDetail->merchant_id : "" }}" data-value="ACTIVE"  onclick="startFed()"><span>Start All Fedral Bank</span></button>
            </div>
            <div class="float-lg-left mt-4">
                <button id="fedStopBtn"  class="btn btn-sm float-right btn-danger mb-2  ml-1"  data-merchant-id="{{isset($merchantDetail) ? $merchantDetail->merchant_id : "" }}" data-value="DEACTIVE"  onclick="stopFed()"><span>Stop All Fedral Bank</span></button>
            </div>
            <div class="col-auto mt-4">
                <button id="autRefreshBtn" class="btn btn-sm float-right btn-primary ml-1" onclick="autoRefreshTransaction()"><span  id="refreshTitle"></span></button>
            </div>
            <div class="table-responsive mb-2">
                <table class="table table-bordered table-hover mb-0" id="payinDataTab" >
                    <thead>
                    <tr class="tbl_blk">
                        <th class="text-black">MMPID</th>
                        <th class="text-black">pg Info</th>
                        <th class="text-black">payment method</th>
                        <th class="text-black">active</th>
                        <th class="text-black">amount</th>
                        <th class="text-black">Level1</th>
                        <th class="text-black">Level2</th>
                        <th class="text-black">Level3</th>
                        <th class="text-black">Level4</th>
                        <th class="text-black">seamless</th>
                        <th class="text-black">Date</th>
                        <th class="text-black">Action</th>
                    </tr>
                    </thead>
                    <tbody id="payinData">
                    </tbody>
                </table>
                <div id="pagination">
                </div>
            </div>
                <div class="row">
                    <div class="col-6">
                    <div class="table-responsive">
                        <h5 class="p-2 text-primary">Auto Payin Meta </h5>

                        <table class="table table-bordered table-hover mb-0" id="merchantAutoDataTable">
                            <thead>
                            <tr>
                                <th class="text-black">pg Info</th>
                                <th class="text-black">Label</th>
                                <th class="text-black">seamless</th>
                                <th class="text-black">Action</th>
                            </tr>
                            </thead>
                            <tbody id="payinAvailableData">

                            </tbody>
                        </table>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="table-responsive">
                            <h5 class="p-2 text-primary">Manual Payin Meta </h5>
                            <table class="table table-bordered table-hover mb-0" id="merchantManualDataTable">
                                <thead>
                                    <tr>
                                        <th class="text-black">pg Info</th>
                                        <th class="text-black">Label</th>
                                        <th class="text-black">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="payinAvailableDataManual">
                                </tbody>
                            </table>
                            <a href="#" id="scroll" style="display: none;"><span></span></a>
                        </div>
                    </div>
                </div>
        </div>
    </div>

    @include('merchant.merchant-model')

@endsection

@section('customJs')

    <script src="https://cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/js/bootstrap-editable.min.js"></script>
    <script src="/editor/custom.js"></script>
    {{--    <script src="https://unpkg.com/bootstrap-table@1.20.2/dist/bootstrap-table.min.js"></script>--}}
    <script src="{{URL::asset('custom/js/component/merchant/editable-load.js?v=1')}}"></script>
    <script src="{{URL::asset('custom/js/component/merchant/merchant-payin.js?v=15')}}"></script>
@endsection
