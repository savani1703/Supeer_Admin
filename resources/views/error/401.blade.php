@extends('layout.master')

@section('title', 'Access Denied')

@section('customStyle')
@endsection

@section('content')
    <div class="card">
        <div class="col-md-12 col-sm-6" style="text-align: center;padding: 50px">
            <div class="sec-title pt50">
                <img src="/assets/images/401.svg" class="img-fluid" style="    width: 60vh;" draggable="false">
                <h1 class="font-weight-bold mb-22 mt-2 tx-80 text-muted">401</h1>
                <h4 class="mb-2">Unauthorized User</h4>
                <h6 class="text-muted mb-3 text-center">Oopps!! You are not authorized to access this Page.</h6>
            </div>
        </div>
    </div>
@endsection

@section('customJs')

@endsection
