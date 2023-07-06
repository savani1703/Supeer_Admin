@extends('layout.master')

@section('title', isset($merchantDetail) ? $merchantDetail->merchant_name." PayIn Meta" : "PayIn Meta")

@section('customStyle')
@endsection

@section('content')
    <div class="card">
        <div class="card-body" id="Merchant_page">
            <div class="row">
                <div class="col-12">
                    <div class="table-responsive">
                        <h5 class="p-2 text-primary">Merchant Payin </h5>
                        <table class="table table-bordered table-hover mb-0" id="merchantManualDataTable">
                            <thead>
                            <tr>
                                <th class="text-black">pg Info</th>
                                <th class="text-black">Active</th>
                                <th class="text-black">Create At</th>
                                <th class="text-black">Update At</th>
                            </tr>
                            </thead>
                            <tbody id="readpayinData">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('customJs')
    <script src="{{URL::asset('custom/js/component/merchant/marchant-read-payin.js?v=11')}}"></script>
@endsection
