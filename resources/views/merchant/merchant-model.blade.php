
<div class="modal fade" id="dashboardLogs" tabindex="-1"  aria-labelledby="dashboardLogs" data-backdrop="static" data-keyboard="false" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Merchant Dashboard Logs</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="merchantDataTable">
                    <thead>
                    <tr>
                        <th>Action Type</th>
                        <th>Action</th>
                        <th>Request Ip</th>
                        <th>User Agent</th>
                        <th>Created At</th>
                    </tr>
                    </thead>
                    <tbody id="dashboardData">
                    </tbody>
                </table>
                <div id="pagination">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="ViewStatement" tabindex="-1"  aria-labelledby="ViewStatement" data-backdrop="static" data-keyboard="false" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Merchant View Statement</h5>
                <button class="btn btn-primary" type="button"  onclick="exportReportToExcel()">Generate Report</button>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="merchantStatementDataTable">
                    <thead>
                    <tr>
                        <th>Pay date</th>
                        <th>Open balance</th>
                        <th>Payin</th>
                        <th>Payin Live</th>
                        <th>Payout</th>
                        <th>Payout Live</th>
                        <th>refund</th>
                        <th>un settled</th>
                        <th>settled</th>
                        <th>closing balance</th>
                        <th>created at</th>
                        <th>updated at</th>
                    </tr>
                    </thead>
                    <tbody id="StatementData">
                    </tbody>
                </table>
                <div id="pagination">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="addmanualPayout" tabindex="-1"  aria-labelledby="addmanualPayout" data-backdrop="static" data-keyboard="false" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-lg">
            <div class="modal-header">
                <h5 class="modal-title">Add Manual Payout</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form id="addManualPayoutForm">
                <div class="modal-body">
                    <div class="row">
                    <div class="form-group mr-3 ml-5">
                        <label  class="col-form-label">Enter Amount</label>
                        <input type="number" class="form-control" name="payout_amount" placeholder="Enter Amount">
                    </div>

                    <div class="form-group">
                        <label class="col-form-label">Enter Payout Fees</label>
                        <input type="number" class="form-control" name="payout_fees" placeholder="Enter Payout Fees">
                    </div>
                    </div>

                    <div class="row">
                        <div class="form-group mr-3 ml-5">
                            <label  class="col-form-label">Enter Payout Associate Fees</label>
                            <input type="number" class="form-control" name="payout_associate_fees" placeholder="Enter Payout Associate Fees">
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">Bank Holder Name</label>
                            <input type="text" class="form-control" name="bank_holder" placeholder="Enter Bank Holder Name">
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group mr-3 ml-5">
                            <label  class="col-form-label">Enter Account Number</label>
                            <input type="number" class="form-control" name="account_number" placeholder="Enter Account Number">
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">Enter Ifsc Code</label>
                            <input type="text" class="form-control" name="ifsc_code" placeholder="Enter Ifsc Code">
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group mr-3 ml-5">
                            <label  class="col-form-label">Enter Bank Utr</label>
                            <input type="text" class="form-control" name="bank_rrn" placeholder="Enter Bank Utr">
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">Enter Remark</label>
                            <input type="text" class="form-control" name="remarks" placeholder="Enter Remark">
                        </div>
                        <input type="text" id="merchant_id"   class="form-control" name="merchant_id"  hidden>
                    </div>

                <div class="modal-footer">
                    <button id="close_btn" type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button  type="submit" class="btn btn-primary">Add Manual Payout</button>
                </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="addsettlement" tabindex="-1"  aria-labelledby="addsettlement" data-backdrop="static" data-keyboard="false" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-lg">
            <div class="modal-header">
                <h5 class="modal-title">Add Settlement</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-bordered mb-0" id="SettlementDataTable">
                    <thead>
                    <tr>
                        <th>Pay date</th>
                        <th>Settled</th>
                        <th>UnSettled</th>
                        <th>Closing Bal</th>
                    </tr>
                    </thead>
                    <tbody id="MerStatementData">
                    </tbody>
                </table>
            </div>

            <form id="addSettlementForm">
                <div class="modal-body">
                    <div class="form-group mr-3 ml-5">
                        <label  class="col-form-label" id="currbalance">Current Balance :  </label>
                    </div>
                    <div class="form-group mr-3 ml-5">
                        <label  class="col-form-label" id="availablebalance">Available Unsettled Balance :  </label>
                    </div>
                    <div class="form-group mr-3 ml-5">
                        <label  class="col-form-label">Enter Amount To Release</label>
                        <input type="number" class="form-control" name="release_amount" id="release_amount" placeholder="Enter Amount">
                    </div>

                    <input type="text" id="merchant_id_forSettlement"   class="form-control" name="merchant_id"  hidden>
                </div>
                <div class="modal-footer">
                    <button id="close_btn_addsettlement" type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button  type="submit" class="btn btn-primary">Add Settlement</button>
                </div>
            </form>
        </div>
    </div>
</div>



<div class="modal fade" id="addmanualPayin" tabindex="-1"  aria-labelledby="addmanualPayin" data-backdrop="static" data-keyboard="false" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-lg">
            <div class="modal-header">
                <h5 class="modal-title">Add Manual Payin</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form id="addManualPayinForm">
                <div class="modal-body">
                        <div class="form-group mr-3 ml-5">
                            <label  class="col-form-label">Enter Amount</label>
                            <input type="number" class="form-control" name="amount" placeholder="Enter Amount">
                        </div>

                        <div class="form-group mr-3 ml-5">
                            <label  class="col-form-label">Enter Bank Ref</label>
                            <input type="text" class="form-control" name="utr_ref" placeholder="Enter Bank Ref">
                        </div>
                        <div class="form-group mr-3 ml-5">
                            <label  class="col-form-label">Remark</label>
                            <input type="text" class="form-control" name="remark">
                        </div>
                    <div class="form-group mr-3 ml-5">
                        <label  class="col-form-label">With Fees</label>
                        <input type="checkbox" class="form-control" name="withfee" checked="checked">
                    </div>
                        <div class="form-group mr-3 ml-5">
                            <label  class="col-form-label">Date</label>
                            <input type="date" value="{{\Carbon\Carbon::now("Asia/Kolkata")->format("d-m-Y")}}" class="form-control" name="transaction_date">
                        </div>

                        <input type="text" id="merchant_id_forPayin"   class="form-control" name="merchant_id"  hidden>
                    </div>

                    <div class="modal-footer">
                        <button id="close_btn_manualpayin" type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button  type="submit" class="btn btn-primary">Add Manual Payin</button>
                    </div>
            </form>
        </div>
    </div>
</div>





<div class="modal fade" id="ViewWhitelistIp" tabindex="-1"  aria-labelledby="ViewWhitelistIp" data-backdrop="static" data-keyboard="false" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Merchant Whitelist Ip</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="merchantDataTable">
                    <thead>
                    <tr>
                        <th>Id</th>
                        <th>Merchant Id</th>
                        <th>Is Active</th>
                        <th>Merchant Ip</th>
                        <th>Type</th>
                        <th>Created At</th>
                        <th>Updated At</th>
                    </tr>
                    </thead>
                    <tbody id="whitelistData">
                    </tbody>
                </table>
                <div id="pagination">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="payinmodal" tabindex="-1"  aria-labelledby="payinmodal" data-backdrop="static" data-keyboard="false" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Merchant Payin Data   </h5>
                <span id="selectedMid"></span>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="merchantDataTable">
                    <thead>
                    <tr class="trtag" style="position: -webkit-sticky; position: sticky; top: 0;background-color:#727cf5;color:#ffff">
                        <th class="text-white">Id</th>
                        <th class="text-white">pg Info</th>
                        <th class="text-white">payment method</th>
                        <th class="text-white">active</th>
                        <th class="text-white">min amount</th>
                        <th class="text-white">max amount</th>
                        <th class="text-white">current turnover</th>
                        <th class="text-white">daily limit</th>
                        <th class="text-white">Level</th>
                        <th class="text-white">seamless</th>
                        <th class="text-white">visible</th>
                        <th class="text-white">Date</th>
                        <th class="text-white">Action</th>
                    </tr>
                    </thead>
                    <tbody id="payinData">
                    </tbody>
                </table>
                <div id="pagination">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>




<div class="modal fade" id="addMerchant" tabindex="-1"  aria-labelledby="addMerchant" data-backdrop="static" data-keyboard="false" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-lg">
            <div class="modal-header">
                <h5 class="modal-title">Add Merchant</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addMerchant" action="javascript:void(0)">
                <div class="modal-body">
                        <div class="form-group">
                            <label  class="col-form-label">Merchant Email</label>
                            <input type="email" class="form-control" name="merchant_email" placeholder="Merchant Email">
                        </div>

                        <div class="form-group">
                            <label class="col-form-label">Merchant Name</label>
                            <input type="text" class="form-control" name="merchant_name" placeholder="Merchant Name">
                        </div>

                    <div class="modal-footer">
                        <button id="close_btn" type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button  type="submit" class="btn btn-primary">Add Merchant</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="merchantConfigModal" tabindex="-1"  aria-labelledby="merchantConfig" data-backdrop="static" data-keyboard="false" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bank Config</h5>
                <button type="button" class="close" id="closeBtn" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="table-responsive">
                <form action="javascript:void(0)" id="merchantConfigForm">
                    <div class="modal-body2">
                        <div class="custom-control custom-switch mb-2">
                            <input type="checkbox" name="FEDERAL" class="custom-control-input custom-input-lg" id="FEDERAL">
                            <label class="custom-control-label" for="FEDERAL">FEDERAL IS DOWN</label>
                        </div>

                        <div class="custom-control custom-switch mb-2">
                            <input type="checkbox" name="HDFC" class="custom-control-input custom-input-lg" id="HDFC">
                            <label class="custom-control-label" for="HDFC">HDFC IS DOWN</label>
                        </div>
                        <div class="custom-control custom-switch mb-2">
                            <input type="checkbox" name="ICICI" class="custom-control-input custom-input-lg" id="ICICI">
                            <label class="custom-control-label" for="ICICI">ICICI IS DOWN</label>
                        </div>
                        <div class="custom-control custom-switch mb-2">
                            <input type="checkbox" name="RBL" class="custom-control-input custom-input-lg" id="RBL">
                            <label class="custom-control-label" for="RBL">RBL IS DOWN</label>
                        </div>
                    </div>
                </form>
                <div class="modal-footer" style="margin-right: 25px;">
                    <p class="note note-warning"> <strong>Note:</strong> Please note when UTR not come in bank then down those bank,
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
