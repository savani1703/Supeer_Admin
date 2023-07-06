@extends('layout.master')

@section('title', 'Bank Transactions')

@section('customStyle')
    <link href="{{URL::asset("/custom/plugin/footable/css/footable.standalone.css")}}" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.0.3/css/font-awesome.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/css/bootstrap-editable.css" rel="stylesheet" />
    <link href="/editor/style.css" rel="stylesheet" />
@endsection

@section('content')
    <div class="card" id="bankpage">
        <div class="row">
            <div class="row w-100 mx-0 auth-page" id="unauthorized_user"></div>
            <div class="col-md-12" id="bankTransactionPage">
                <div class="card">
                    <div class="card card-outline-info mb-0">
                        <div class="p-3">
                            <h6 class="font-weight-bold">Bank Transaction</h6>
                        </div>
                        <div class="card-body shadow-sm pt-0">
                            <form action="javascript:void(0)" id="bankForm">
                                <div class="row mt-4">
                                    <div class="col-auto">
                                        <span class="text-muted">Filter</span>
                                        <div class="form-group">
                                            <div class="d-flex ">
                                                <select name="FilterKey"  class="form-control border-right-0 ">
                                                    <option value="payment_utr">UTR</option>
                                                    <option value="amount">Amount</option>
                                                    <option value="account_number">Account Number</option>
                                                    <option value="upi_id">UPI ID</option>
                                                </select>
                                                <input type="text" name="FilterValue" class="form-control" placeholder="Enter Search Value" autocomplete="off">
                                            </div>
                                        </div>
                                    </div><!-- Col -->
                                    <div class="col-auto">
                                    <span class="text-muted">Is Get</span>
                                        <div class="form-group">
                                            <select class="form-select form-select-sm" name="is_get">
                                                <option selected="" disabled="">IS GET</option>
                                                <option value="ALL">ALL</option>
                                                <option value="1">TRUE</option>
                                                <option value="0">FALSE</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <span class="text-muted">Bank</span>
                                        <div class="form-group">
                                            <select name="bank_name" id="bank_name" class="form-control">
                                                <option value="All" selected>All</option>
                                                <option value="HDFC">HDFC</option>
                                                <option value="FEDERAL">FED</option>
                                                <option value="ICICI">ICICI</option>
                                                <option value="IDBI">IDBI</option>
                                                <option value="RBL">RBL</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-auto" id="AccountList">
                                        <span class="text-muted">Account</span>
                                        <div class="form-group">
                                            <select name="bank_account" id="bank_account" class="form-control">
                                            </select>
                                        </div>
                                    </div><!-- Col -->
                                    <div class="col-auto">
                                        <span class="text-muted">Date</span>
                                        <div class="form-group">
                                            <div class="input-group date datepicker dashboard-date mr-2 mb-2 mb-0 d-xl-flex">
                                                <span class="input-group-addon bg-transparent"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-calendar  text-primary"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg></span>
                                                <input type="text" class="form-control" name="daterange" autocomplete="off">
                                            </div>
                                        </div>
                                    </div><!-- Col -->
                                    <div class="col-auto">
                                        <span class="text-muted">Limit</span>
                                        <div class="form-group">
                                            <select name="Limit" id="Limit" class="form-control">
                                                <option value="50" selected>50</option>
                                                <option value="100">100</option>
                                                <option value="200">200</option>
                                                <option value="300">300</option>
                                                <option value="400">400</option>
                                                <option value="500">500</option>
                                                <option value="1000">1000</option>
                                                <option value="5000">5000</option>
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
                                    <div class="col-auto">
                                        <label class="control-label"></label>
                                        <button class="btn btn-primary" type="submit">Apply</button>
                                        <button class="btn btn-danger" type="button"  onclick="resetBankForm()">Clear</button>
                                    </div><!-- Col -->
                                    @if((new \App\Plugin\AccessControl\AccessControl())->hasAccessModule(\App\Plugin\AccessControl\Utils\AccessModule::BANK_TRANSACTION_REPORT))
                                        <div class="col-auto">
                                            <button class="btn btn-primary" type="button"  onclick="generateBankTxnReport()">Generate Report</button>
                                        </div>
                                    @endif
                                    <div class="col-auto">
                                        <button id="autRefreshBtn" class="btn btn-sm float-right btn-primary ml-1" onclick="autoRefreshTransaction()"><span  id="refreshTitle"></span></button>
                                    </div>
                                    @if((new App\Plugin\AccessControl\AccessControl())->hasAccessModule(App\Plugin\AccessControl\Utils\AccessModule::MANUAL_BANK_ENTRY))
                                    <div class="col-auto">
                                        <button id="mergeBankTransaction" class="btn btn-sm float-right btn-primary ml-1"  data-toggle="modal" data-target="#mergeUTR"><span  id="mergeBankTransaction"></span> Merge UTR</button>
                                    </div>
                                    <div class="col-auto">
                                        <button id="addManualEntryInBank" class="btn btn-sm float-right btn-primary ml-1"  data-toggle="modal" data-target="#addmanualBankEntry"><span  id=""></span> Add Manual Bank Entry</button>
                                    </div>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card mt-1">
                    <div class="table-responsive mt-5">
                        <table class="table table-hover mb-0">
                            <thead>
                            <tr>
                                <th class="pt-0">ACTION</th>
                                <th class="pt-0">DATE</th>
                                <th class="pt-0">Amount</th>
                                <th class="pt-0">PAYMENT UTR</th>
                                <th class="pt-0">IS GET	</th>
                                <th class="pt-0">UPI ID</th>
                                <th class="pt-0">MOBILE NO</th>
                                <th class="pt-0">DESC</th>
                                <th class="pt-0">Account</th>
                                <th class="pt-0">Transaction MODE</th>
                                <th class="pt-0">Payment MODE</th>
                                <th class="pt-0">TXN Date</th>
                            </tr>
                            </thead>
                            <tr class="preLoader">
                                <td colspan="9" align="center">
                                    <div class="spinner-grow  text-primary" role="status">
                                    </div>
                                </td>
                            </tr>
                            <tbody id="BankData">

                            </tbody>
                        </table>
                        <div class="pl-3" id="pagination"></div>
                        <a href="#" id="scroll" style="display: none;"><span></span></a>
                    </div>
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
            </div>
        </div>
    </div>


    <div class="modal fade" id="mergeUTR" tabindex="-1"  aria-labelledby="mergeUTR" data-backdrop="static" data-keyboard="false" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-lg">
                <div class="modal-header">
                    <h5 class="modal-title">Merge Two UTR</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <form id="mergeUTRForm">
                    <div class="modal-body">
                        <div class="form-group mr-3 ml-5">
                            <label  class="col-form-label">Enter UTR 1</label>
                            <input type="text" class="form-control" name="utr_ref_1" placeholder="Enter UTR 1">
                        </div>
                        <div class="form-group mr-3 ml-5">
                            <label  class="col-form-label">Enter UTR 2</label>
                            <input type="text" class="form-control" name="utr_ref_2" placeholder="Enter UTR 2">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button id="close_btn_manualmerge" type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button  type="submit" class="btn btn-primary">Merge UTR</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addmanualBankEntry" tabindex="-1"  aria-labelledby="addmanualBankEntry" data-backdrop="static" data-keyboard="false" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-lg">
                <div class="modal-header">
                    <h5 class="modal-title">Add Manual Bank Transaction Entry</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="ManualBankEntryForm">
                    <div class="modal-body">
                        <div class="form-group mr-3 ml-5">
                            <label  class="col-form-label">Enter UTR </label>
                            <input type="text" class="form-control" name="payment_utr" placeholder="Enter UTR ">
                        </div>
                        <div class="form-group mr-3 ml-5">
                            <label  class="col-form-label">Enter Amount</label>
                            <input type="number" class="form-control" name="amount" placeholder="Enter Amount">
                        </div>
                        <div class="form-group mr-3 ml-5">
                            <label  class="col-form-label">Select Bank</label>
                            <div class="form-group">
                                <select name="account_number" id="accountLoad" class="form-control">
                                    <option value="ALL">ALL</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button id="close_btn_manual_Entry" type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button  type="submit" class="btn btn-primary">Add Manual Entry</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@section('customJs')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/js/bootstrap-editable.min.js"></script>
    <script src="/editor/custom.js"></script>
    <script src="/custom/js/component/merchant/editable-load.js?v=1"></script>
    <script src="/custom/js/component/bank-transaction.js?v=27"></script>

@endsection

