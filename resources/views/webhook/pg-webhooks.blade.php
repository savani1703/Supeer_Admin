@extends('layout.master')

@section('title', 'Pg-Webhooks')

@section('customStyle')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.0.3/css/font-awesome.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/css/bootstrap-editable.css" rel="stylesheet" />
    <link href="/editor/style.css" rel="stylesheet" />

@endsection

@section('content')
    <div class="card">
        <div class="row">
            <div class="row w-100 mx-0 auth-page" id="unauthorized_user"></div>
            <div class="col-md-12" id="supportCustomerDetail">
                <div class="card">
                    <div class="card card-outline-info mb-0">
                        <div class="p-3">
                            <h6 class="font-weight-bold">Pg Webhooks</h6>
                        </div>
                        <div class="card-body shadow-sm">
                            <form action="javascript:void(0)" id="webhookForm">
                                <div class="row mt-4">
                                    <div class="col-auto">
                                        <div class="form-group">
                                            <div class="d-flex ">
                                                <select name="FilterKey"  class="form-control border-right-0 ">
                                                    <option value="transaction_id">Transaction Id</option>
                                                    <option value="pg_id">Pg Id</option>
                                                    <option value="is_get">Is Get</option>
                                                </select>
                                                <input type="text" name="FilterValue" class="form-control" placeholder="Enter Search Value">
                                            </div>
                                        </div>
                                    </div><!-- Col -->
                                    <div class="col-auto">
                                        <div class="form-group">
                                            <div class="input-group date datepicker dashboard-date mr-2 mb-2 mb-0 d-xl-flex">
                                                <span class="input-group-addon bg-transparent"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-calendar  text-primary"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg></span>
                                                <input type="text" class="form-control" name="daterange" autocomplete="off">
                                            </div>
                                        </div>
                                    </div><!-- Col -->
                                    <div class="col-auto">
                                        <div class="form-group">
                                            <select name="Limit" id="Limit" class="form-control">
                                                <option value="50" selected>50</option>
                                                <option value="100">100</option>
                                                <option value="200">200</option>
                                                <option value="300">300</option>
                                                <option value="400">400</option>
                                                <option value="500">500</option>
                                            </select>
                                        </div>
                                    </div><!-- Col -->
                                    <div class="col-auto">
                                        <label class="control-label"></label>
                                        <button class="btn btn-primary" type="submit">Apply</button>
                                        <button class="btn btn-danger" type="button"  onclick="resetWebhookForm()">Clear</button>
                                    </div><!-- Col -->
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card mt-1" id="webhookTbl">
                    <div class="table-responsive mt-5">
                        <table class="table table-hover mb-0">
                            <thead>
                            <tr>
                                <th class="pt-0">#</th>
                                <th class="pt-0">Txn #</th>
                                <th class="pt-0">pg id</th>
                                <th class="pt-0">is get</th>
                                <th class="pt-0">pg res</th>
                                <th class="pt-0">created at</th>
                                <th class="pt-0">updated at</th>
                            </tr>
                            </thead>
                            <tr class="preLoader">
                                <td colspan="9" align="center">
                                    <div class="spinner-grow  text-primary" role="status">
                                    </div>
                                </td>
                            </tr>
                            <tbody id="PgWebhooksData">

                            </tbody>
                        </table>
                        <div class="pl-3" id="pagination"></div>
                        <a href="#" id="scroll" style="display: none;"><span></span></a>
                    </div>
                </div>
            </div>
        </div>
    </div>




@endsection

@section('customJs')
    <script src="{{URL::asset('custom/js/component/webhooks/pg-webhooks.js?v=6')}}"></script>
@endsection
