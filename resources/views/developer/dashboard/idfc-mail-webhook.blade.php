@extends('developer.layout.master')

@section('title', 'Idfc Mail webhook')

@section('customStyle')
@endsection

@section('content')
    <div class="page-content" id="dashboard_page" style="margin-top: 0px">
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title d-flex justify-content-between">
                            Idfc Mail webhook
                        </h6>

                        <div class="card-body shadow-sm">
                            <form action="javascript:void(0)" id="FilterForm">
                                <div class="row">
                                    <div class="col-auto">
                                        <span class="text-muted">Filter</span>
                                        <div class="form-group">
                                            <div class="d-flex ">
                                                <select name="search_key"  class="form-control border-right-0 ">
                                                    <option value="account_number">Account Number </option>
                                                    <option value="payout_id">Payout ID</option>
                                                    <option value="bank_rrn">Bank RRN</option>
                                                </select>
                                                <input type="text" name="search_value" class="form-control" placeholder="Enter Search Value">
                                            </div>
                                        </div>
                                    </div><!-- Col -->

                                    <div class="col-auto">
                                        <span class="text-muted">Limit</span>
                                        <div class="form-group">
                                            <select name="limit" id="limit" class="form-control">
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
                                        <span class="text-muted">Date</span>
                                        <div class="form-group">
                                            <div class="input-group date datepicker dashboard-date mr-2 mb-2 mb-0 d-xl-flex">
                                                <span class="input-group-addon bg-transparent"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-calendar  text-primary"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg></span>
                                                <input type="text" class="form-control  form-control-sm"  name="daterange" autocomplete="off">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-auto mt-4">
                                        <button class="btn btn-primary border-0" type="submit">Apply</button>
                                        <button class="btn btn-danger border-0" type="reset"  onclick="resetSMSLogsFilter()">Clear</button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="table-responsive pt-3" id="blockZone">
                            <table class="table table-hover">
                                <thead>
                                <tr>
                                    <th scope="col">payout id</th>
                                    <th scope="col">payout amount</th>
                                    <th scope="col">account number</th>
                                    <th scope="col">bank rrn</th>
                                    <th scope="col">payment from</th>
                                    <th scope="col">bank date</th>
                                    <th scope="col">is get</th>
                                    <th scope="col">is data sync</th>
                                    <th scope="col">error message</th>
                                    <th scope="col">created at</th>
                                    <th scope="col">updated at</th>

                                </tr>
                                </thead>
                                <tbody id="WebhookData">

                                </tbody>
                            </table>
                            <div class="pl-3" id="pagination"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <a href="#" id="scroll" style="display: none;"><span></span></a>

@endsection

@section('customJs')
    <script src="{{URL::asset('custom/js/component/developer/dashboard/idfc-mail-webhook.js?v=3')}}"></script>
@endsection


