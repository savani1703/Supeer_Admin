@extends('layout.master')

@section('title', $pgRenderConfig['pg_name'].' '.$pgRenderConfig['pg_type'].' Meta')

@section('customStyle')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.0.3/css/font-awesome.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/css/bootstrap-editable.css" rel="stylesheet" />
    <link href="/editor/style.css" rel="stylesheet" />
@endsection

@section('content')
    <div class="card">
        <div class="row">
            <div class="row w-100 mx-0 auth-page" id="unauthorized_user"></div>
            <div class="col-md-12" id="bankTransactionPage">
                <div class="card">
                    <div class="card mb-0">
                        <div class="card-header">
                            <button type="button" class="btn btn-light mdi mdi-account-plus float-right" data-toggle="modal" data-target="#addMetaModal"> Add {{ $pgRenderConfig['pg_name'] }} {{ $pgRenderConfig['pg_type'] }} Meta</button>
                            <h6 class="m-b-0  font-weight-bold">{{ $pgRenderConfig['pg_name'] }} {{ $pgRenderConfig['pg_type'] }} Meta</h6>
                        </div>
                        <div class="card-body shadow-sm pt-0">
                            <form action="javascript:void(0)" id="FilterForm">
                                <div class="row mt-4">
                                    <div class="col-auto">
                                        <div class="form-group">
                                            <div class="d-flex">
                                                <select name="FilterKey"  class="form-control border-right-0">
                                                    @if(strcmp(strtolower($pgRenderConfig['pg_name']), 'upipay') === 0)
                                                        <option value="upi_id">UPI Id</option>
                                                        <option value="account_number">Account Number</option>
                                                    @endif
                                                    <option value="merchant_id">Merchant Id</option>
                                                    <option value="account_id">Account Id</option>
                                                    <option value="label">Label</option>
                                                </select>
                                                <input type="text" name="FilterValue" class="form-control" placeholder="Enter Search Value">
                                            </div>
                                        </div>
                                    </div><!-- Col -->
                                    <div class="col-auto">
                                        <div class="form-group">
                                            <select name="Limit" id="Limit" class="form-control">
                                                <option value="50" selected>50</option>
                                                <option value="100">100</option>
                                                <option value="200">200</option>
                                                <option value="500">500</option>
                                            </select>
                                        </div>
                                    </div><!-- Col -->
                                    <div class="col-auto">
                                        <label class="control-label"></label>
                                        <button class="btn btn-primary border-0" type="submit">Apply</button>
                                        <button class="btn btn-danger border-0" type="reset" onclick="resetMetaForm()">Clear</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card mt-1">
                    <div class="table-responsive mt-5">
                        <table class="table table-hover mb-0" id="pgMetaTable">
                            <thead>
                            <tr id="dynamicColspan">
                                @foreach($pgRenderConfig['show_columns'] as $showColum)
                                    <th class="pt-0">{{strtoupper(str_replace("_", " ", $showColum))}}</th>
                                @endforeach
                            </tr>
                            </thead>
                            <tbody id="pgMetaData">

                            </tbody>
                        </table>
                        <div class="pl-3" id="pagination"></div>
                        <a href="#" id="scroll" style="display: none;"><span></span></a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addMetaModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Insert New {{ $pgRenderConfig['pg_name'] }} {{ $pgRenderConfig['pg_type'] }} Meta</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="addMetaForm" action="javascript:void(0)">
                    {!! $pgRenderConfig['add_meta_columns'] !!}
                    <div class="modal-footer">
                        <button id="close_btn" type="button" class="btn btn-secondary " data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add {{ $pgRenderConfig['pg_name'] }} Meta</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@section('customJs')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/js/bootstrap-editable.min.js"></script>
    <script src="/editor/custom.js"></script>
    <script src="{{URL::asset('custom/js/component/pg/payment-meta.module.js?v=6')}}"></script>
    <script src="{{URL::asset('custom/js/component/pg/pg-meta.js?v=4')}}"></script>

@endsection

