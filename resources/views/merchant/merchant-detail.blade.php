@extends('layout.master')

@section('title', 'Merchants')

@section('customStyle')
    <link href="{{URL::asset("/custom/plugin/footable/css/footable.standalone.css")}}" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.0.3/css/font-awesome.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/css/bootstrap-editable.css" rel="stylesheet" />
    <link href="/editor/style-black.css" rel="stylesheet" />
@endsection

@section('content')
    <div class="card">

        <div class="card-body" id="Merchant_page">
            <div class="d-flex justify-content-between align-items-baseline mb-2">
                <h6 class="card-title mb-0">Merchants</h6>
                @if($isAllowedAddMerchant)
                <div class="offset-lg-9  col-auto">
                    <button class="btn btn-primary" data-toggle="modal" data-target="#addMerchant">Add Merchant</button>
                </div>
                @endif
                @if($isAllowedModifyBankConfig)
                    <div class="col-auto">
                        <button class="btn btn-danger"  id="merchantConfigBtn" data-toggle="modal" data-target="#merchantConfigModal">Bank Config</button>
                    </div>
                @endif
            </div>

            <div class="mb-4">
                <form action="javascript:void(0)" id="merchantFilterForm">
                    <div class="row mt-4">
                        <div class="col-auto">
                            <div class="form-group">
                                <div class="d-flex ">
                                    <select name="FilterKey" id="FilterKey" class="form-control border-right-0 bg-primary text-white">
                                        <option value="merchant_id">Merchant Id</option>
                                    </select>
                                    <input type="text" name="FilterValue" class="form-control" placeholder="Enter Search Value" autocomplete="off">
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
                            <button id="apply" class="btn  btn-sm btn-primary" type="submit">Apply</button>
                            <button class="btn btn-danger btn-sm" type="button"  onclick="resetMerchantFilter()">Clear</button>
                        </div>

                    </div>
                </form>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="merchantDataTable">
                    <thead>
                    <tr>
                        <th class="pt-0">MID</th>
                        <th class="pt-0">Status</th>
                        <th class="pt-0">Min Limit</th>
                        <th class="pt-0">Max Limit</th>
                        <th class="pt-0">Set Meta</th>

                        <th class="pt-0" data-breakpoints="md lg">Account Status</th>
                        <th class="pt-0" data-breakpoints="md lg">Min Limit</th>
                        <th class="pt-0" data-breakpoints="md lg">Max Limit	</th>

                        <th class="pt-0" data-breakpoints="md lg">Is PayIn Enable</th>
                        <th class="pt-0" data-breakpoints="md lg">Is Payout Enable</th>
                        <th class="pt-0" data-breakpoints="md lg">Is Failed Webhook Req.</th>
                        <th class="pt-0" data-breakpoints="md lg">Is Enable Browser Check</th>
                        <th class="pt-0" data-breakpoints="md lg">Is Payout Balance Check</th>
                        <th class="pt-0" data-breakpoints="md lg">PayIn Webhook</th>
                        <th class="pt-0" data-breakpoints="md lg">Payout Webhook</th>
                        <th class="pt-0" data-breakpoints="md lg">Is Dashboard Payout Enable</th>
                        <th class="pt-0" data-breakpoints="md lg">PayIn Auto Fees</th>
                        <th class="pt-0" data-breakpoints="md lg">PayIn Manual Fees</th>
                        <th class="pt-0" data-breakpoints="md lg">PayIn Associate Fees</th>
                        <th class="pt-0" data-breakpoints="md lg">Payout Fees</th>
                        <th class="pt-0" data-breakpoints="md lg">Payout Associate Fees</th>
                        <th class="pt-0" data-breakpoints="md lg">Settlement Cycle</th>
                        <th class="pt-0" data-breakpoints="md lg">Payout Delayed Time</th>
                        <th class="pt-0" data-breakpoints="md lg">Is Auto Approved Payout</th>
                        <th class="pt-0" data-breakpoints="md lg">Show Customer Details Page</th>
                        <th class="pt-0" data-breakpoints="md lg">Is Customer Details Required</th>
                        <th class="pt-0" data-breakpoints="md lg">Is Settlement Enable</th>
                        <th class="pt-0" data-breakpoints="md lg">Old Users Days</th>
                        <th class="pt-0" data-breakpoints="md lg">Checkout Color</th>
                        <th class="pt-0" data-breakpoints="md lg">Checkout Theme Url</th>
                        <th class="pt-0" data-breakpoints="md lg">Merchant Bouncer Url</th>
                        <th class="pt-0" data-breakpoints="md lg">Actions</th>
                    </tr>
                    </thead>
                    <tbody id="GetMerchantData">
                    </tbody>
                </table>

                <a href="#" id="scroll" style="display: none;"><span></span></a>
            </div>

        </div>
    </div>

    @include('merchant.merchant-model')

@endsection

@section('customJs')

    <script src="https://cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/js/bootstrap-editable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/linways/table-to-excel@v1.0.4/dist/tableToExcel.js"></script>
    <script src="/editor/custom.js"></script>
    <script src="{{URL::asset("/custom/plugin/footable/js/footable.min.js")}}"></script>
    <script src="{{URL::asset('custom/js/component/merchant/merchant.js?v=14')}}"></script>
    <script>
        function exportReportToExcel() {
            let table = document.getElementById("merchantStatementDataTable"); // you can use document.getElementById('tableId') as well by providing id to the table tag
            TableToExcel.convert(table, { // html code may contain multiple tables so here we are refering to 1st table tag
                name: `MerchantBalanceSheet.xlsx`, // fileName you could use any name
                sheet: {
                    name: 'Sheet 1' // sheetName
                }
            });
        }
    </script>
@endsection
