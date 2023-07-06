@extends('layout.master')

@section('title', 'Dashboard')

@section('customStyle')
@endsection
@section('content')
    <div class="card">
        <div class="card-body">
            <div id="client_payin_summary" class="mb-3"></div>
            <div id="client_payout_summary"></div>
        </div>
    </div>
@endsection

@section('customJs')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/echarts/5.3.3/echarts.min.js"></script>
    <script src="{{URL::asset('custom/js/component/dashboard/dashboard.js?v=6')}}"></script>

@endsection



