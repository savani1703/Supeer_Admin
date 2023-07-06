@extends('layout.master')

@section('title', 'Transactions')

@section('customStyle')
@endsection

@section('content')
    <div class="card">

        <div class="card-body" id="transaction_page">
            <div class="d-flex justify-content-between align-items-baseline mb-2">
                <h6 class="card-title mb-0">Transaction By Customer Id </h6>
            </div>

            <div class="mb-4">
                <form action="javascript:void(0)" id="txnFilerForm">
                    <div class="row mt-4">
                        <div class="col-auto">
                            <span class="text-muted">Filter</span>
                            <div class="form-group">
                                <div class="d-flex ">
                                    <select name="txtFilterKey" id="txtFilterData" class="form-control border-right-0 bg-primary text-white">
                                        <option value="searchdata">Search Data</option>
                                        <option value="bank_rrn">Bank RRN</option>
                                        <option value="temp_bank_utr">Temp UTR</option>
                                        <option value="payment_data">UPI ID</option>
                                        <option value="payment_amount">Payment Amount</option>
                                        <option value="cust_city">Cust. City</option>
                                        <option value="udf1">UDF 1</option>
                                        <option value="udf2">UDF 2</option>
                                        <option value="udf3">UDF 3</option>
                                        <option value="udf4">UDF 4</option>
                                        <option value="udf5">UDF 5</option>
                                        <option value="customer_ip">Customer IP</option>
                                        <option value="browser_id">Browser ID</option>
                                    </select>
                                    <input type="text" name="txtFilterValue" class="form-control" placeholder="Enter Search Value" autocomplete="off">
                                </div>
                            </div>
                        </div><!-- Col -->
                        <div class="col-auto">
                            <span class="text-muted">Status</span>
                            <div class="form-group">
                                <select name="txtStatus" id="txtStatus" class="form-control">
                                    <option value="All">All</option>
                                    <option value="Success">Success</option>
                                    <option value="Failed">Failed</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Initialized">Initialized</option>
                                    <option value="Processing">Processing</option>
                                    <option value="Not Attempted">Not Attempted</option>
                                </select>
                            </div>
                        </div><!-- Col -->


                        <div class="col-auto">
                            <span class="text-muted">State</span>
                            <div class="form-group">
                                <select name="cust_state" id="cust_state" class="form-control">
                                    <option value="ALL">ALL</option>
                                    <option value="Andhra Pradesh">Andhra Pradesh</option>
                                    <option value="Arunachal Pradesh">Arunachal Pradesh</option>
                                    <option value="Assam">Assam</option>
                                    <option value="Bihar">Bihar</option>
                                    <option value="Chandigarh">Chandigarh</option>
                                    <option value="Chhattisgarh">Chhattisgarh</option>
                                    <option value="Delhi">Delhi</option>
                                    <option value="Goa">Goa</option>
                                    <option value="Gujarat">Gujarat</option>
                                    <option value="Haryana">Haryana</option>
                                    <option value="Himachal Pradesh">Himachal Pradesh</option>
                                    <option value="Jammu and Kashmir">Jammu and Kashmir</option>
                                    <option value="Jharkhand">Jharkhand</option>
                                    <option value="Karnataka">Karnataka</option>
                                    <option value="Kerala">Kerala</option>
                                    <option value="Madhya Pradesh">Madhya Pradesh</option>
                                    <option value="Maharashtra">Maharashtra</option>
                                    <option value="Manipur">Manipur</option>
                                    <option value="Meghalaya">Meghalaya</option>
                                    <option value="Mizoram">Mizoram</option>
                                    <option value="Nagaland">Nagaland</option>
                                    <option value="Odisha">Odisha</option>
                                    <option value="Puducherry">Puducherry</option>
                                    <option value="Punjab">Punjab</option>
                                    <option value="Rajasthan">Rajasthan</option>
                                    <option value="Rangpur Division">Rangpur Division</option>
                                    <option value="Sikkim">Sikkim</option>
                                    <option value="Tamil Nadu">Tamil Nadu</option>
                                    <option value="Telangana">Telangana</option>
                                    <option value="Tripura">Tripura</option>
                                    <option value="Uttar Pradesh">Uttar Pradesh</option>
                                    <option value="Uttarakhand">Uttarakhand</option>
                                    <option value="West Bengal">West Bengal</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-auto">
                            <span class="text-muted">Block User</span>
                            <div class="form-group">
                                <select name="blockedUser" id="blockedUser" class="form-control">
                                    <option value="ALL">ALL</option>
                                    <option value="yes">Yes</option>
                                    <option value="no">No</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-auto">
                            <span class="text-muted">Show Page</span>
                            <div class="form-group">
                                <select name="showpage" id="showpage" class="form-control">
                                    <option value="All">ALL</option>
                                    <option value="example.com">Example.com</option>
                                    <option value="epacificspree.com">epacificspree.com</option>
                                    <option value="blank page">Blank Page </option>
                                    <option value="odd_amount">Odd Amount</option>
                                </select>
                            </div>
                        </div>


                        <div class="col-auto">
                            <span class="text-muted">Date</span>
                            <div class="form-group">
                                <div class="input-group date datepicker dashboard-date mr-2 mb-2 mb-0 d-xl-flex">
                                    <span class="input-group-addon bg-transparent"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-calendar  text-primary"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg></span>
                                    <input type="text" class="form-control  form-control-sm"  name="daterange" autocomplete="off">
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <span class="text-muted">Success Date</span>
                            <div class="form-group">
                                <div class="input-group date datepicker dashboard-date mr-2 mb-2 mb-0 d-xl-flex">
                                    <span class="input-group-addon bg-transparent"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-calendar  text-primary"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg></span>
                                    <input type="text" class="form-control success-daterange" name="daterange1"   autocomplete="off">
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <span class="text-muted">Player Register Date</span>
                            <div class="form-group">
                                <div class="input-group date datepicker dashboard-date mr-2 mb-2 mb-0 d-xl-flex">
                                    <span class="input-group-addon bg-transparent"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-calendar  text-primary"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg></span>
                                    <input type="text" class="form-control player_register_date_range" name="player_register_date_range"   autocomplete="off">
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <span class="text-muted">Method</span>
                            <div class="form-group">
                                <select name="txtMethod" id="txtMethod" class="form-control form-control-sm">
                                    <option value="ALL">All</option>
                                    <option value="UPI">UPI</option>
                                    <option value="CC">Credit Card</option>
                                    <option value="DC">Debit Card</option>
                                    <option value="WA">Wallet</option>
                                    <option value="NB">NetBanking</option></select>
                            </div>
                        </div>
                        <div class="col-auto">
                            <span class="text-muted">Limit</span>
                            <div class="form-group">
                                <select name="txtLimit" id="txtLimit" class="form-control">
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
                            <span class="text-muted">Min/Max Amount</span>
                            <div class="form-group">
                                <input type="text" name="min_amount" class="form-control form-control-sm" placeholder="Min">
                            </div>
                            <div class="form-group">
                                <input type="text" name="max_amount" class="form-control form-control-sm" placeholder="Max">
                            </div>
                        </div>
                        <div class="col-auto mt-4">
                            <label class="control-label"></label>
                            <button id="apply" class="btn  btn-sm btn-primary" type="submit">Apply</button>
                            <button class="btn btn-danger btn-sm" type="button"  onclick="resetTransaction()">Clear</button>
                        </div>
{{--                        <div class="col-auto mt-4">--}}
{{--                            <button class="btn btn-primary" type="button"  onclick="generateReport()">Generate Report</button>--}}
{{--                        </div>--}}
                        <div class="col-auto mt-4">
                            <button id="autRefreshBtn" class="btn btn-sm float-right btn-primary ml-1" onclick="autoRefreshTransaction()"><span  id="refreshTitle"></span></button>
                        </div>
                        <div class="col-auto mt-4">
                            Show Temp UTR Records Only
                            <div class="form-check form-check-inline">
                                <input type="checkbox" class="form-check-input levelBox" id="idTempUtrOnly" name="idTempUtrOnly">
                            </div>
                        </div>
                        <div class="col-auto mt-4">
                            Show Late Success
                            <div class="form-check form-check-inline">
                                <input type="checkbox" class="form-check-input levelBox" id="lateSuccess" name="lateSuccess">
                            </div>
                        </div>

                    </div>
                </form>
            </div>
            <div class="row flex-grow mt-3" id="countData">
                <div class="col-md-3 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-baseline mb-2">
                                <h6 class="card-title mb-0">
                                    Total Transaction
                                </h6>
                            </div>
                            <div class="row">
                                <div class="col-12 col-md-12 col-xl-12">
                                    <h6 class="mb-2 dz-responsive-text" id="__total_txn">0</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-baseline mb-2">
                                <h6 class="card-title mb-0">
                                    Total Payment Amount
                                </h6>
                            </div>
                            <div class="row">
                                <div class="col-12 col-md-12 col-xl-12">
                                    <h6 class="mb-2 dz-responsive-text">₹ <span id="__total_payment_amount">0</span></h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-baseline mb-2">
                                <h6 class="card-title mb-0">
                                    Total Payable Amount
                                </h6>
                            </div>
                            <div class="row">
                                <div class="col-12 col-md-12 col-xl-12">
                                    <h6 class="mb-2 dz-responsive-text">₹ <span id="__total_payable_amount">0</span></h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-baseline mb-2">
                                <h6 class="card-title mb-0">
                                    Total Fees
                                </h6>
                            </div>
                            <div class="row">
                                <div class="col-12 col-md-12 col-xl-12">
                                    <h6 class="mb-2 dz-responsive-text">₹ <span id="__total_pg_fees">0</span></h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive" id="txnData">
                <table class="table table-hover mb-0" data-show-toggle="false">
                    <thead>
                    <tr>
                        <th class="pt-0"></th>
                        <th class="pt-0">Create At</th>
                        <th class="pt-0">Transaction Id</th>
                        <th class="pt-0">Order Id</th>
                        <th class="pt-0">GRP ID</th>
                        <th class="pt-0">Amount</th>
                        <th class="pt-0">settled Amount</th>
                        <th class="pt-0">Status</th>
                        <th class="pt-0">Method</th>
                        <th class="pt-0">Pg</th>
                    </tr>
                    </thead>
                    <tbody id="transactionData">
                    </tbody>
                </table>
                <div id="pagination">
                </div>
                <a href="#" id="scroll" style="display: none;"><span></span></a>
            </div>

            <div class="modal left fade" id="txnModal" tabindex="" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5>Transaction Id </h5><h5 class="text-primary" id="transaction_id"></h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="nav flex-sm-column flex-row">
                                <div class="preLoaderModal" style="display: flex;justify-content: center;align-items: center;">
                                    <div>
                                        <div class="spinner-grow  text-primary" role="status">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="customerDetails" tabindex="-1"  aria-labelledby="customerDetails" data-backdrop="static" data-keyboard="false" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered" style="max-width: 1200px;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Customer Details</h5>
                            <button class="btn btn-danger" onclick="custBlockByHid()">Block All Details</button>
                            <input type="text" id="BrowserId"  hidden>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="customerDataTable">
                                <thead>
                                <tr>
                                    <th>HId</th>
                                    <th>Customer Id</th>
                                    <th>Merchant Name</th>
                                    <th>Customer Email</th>
                                    <th>Customer Mobile</th>
                                    <th>Last Success Date</th>
                                </tr>
                                </thead>
                                <tbody id="customerData">
                                </tbody>
                            </table>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

@endsection

@section('customJs')
    <script src="{{URL::asset('custom/js/component/transaction-byid.js?v=53')}}"></script>
@endsection
