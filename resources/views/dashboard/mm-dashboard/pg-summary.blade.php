<div class="card mb-2">
    <div class="p-3">
        <h6 class="font-weight-bold">PG Collection/Withdrawal Summary</h6>
    </div>
    <div class="card-body" id="pgSummaryZone">
        <form action="javascript:void(0)" class="mb-2" id="pgSummaryForm">
            <div class="row mt-1">
                <div class="col-auto">
                    <div class="form-group">
                        <div class="input-group date datepicker dashboard-date mr-2 mb-2 mb-0 d-xl-flex">
                            <span class="input-group-addon bg-transparent"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-calendar  text-primary"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg></span>
                            <input type="text" class="form-control dashboard-daterange" name="daterange" id="dashboardDatePicker" autocomplete="off">
                        </div>
                    </div>
                </div>
                <div class="col-auto">
                    <label class="control-label"></label>
                    <button class="btn btn-primary" type="submit">Apply</button>
                </div>
            </div>
        </form>
        <div class="row">
            <div class="col-md-6">
                <div class="table-responsive" id="dashboardData">
                    <h6 class="font-weight-bold mb-2">Collection</h6>
                    <div id="pgCollectionFilter" class="mb-2">
                        <button class="btn btn-sm btn-default active mr-1" data-tr-id="tr-all">ALL</button>
                    </div>
                    <table class="table table-hover table-bordered">
                        <thead>
                        <tr>
                            <th>PG</th>
                            <th>Auto Collection</th>
                            <th>Manual Collection</th>
                        </tr>
                        </thead>
                        <tbody id="pgCollectionSummary">
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-md-6">
                <div class="table-responsive" id="dashboardData">
                    <h6 class="font-weight-bold mb-2">Withdrawal</h6>
                    <div id="pgWithdrawalFilter" class="mb-2">
                        <button class="btn btn-sm btn-default active mr-1" data-tr-id="tr-all">ALL</button>
                    </div>
                    <table class="table table-hover table-bordered ">
                        <thead>
                        <tr>
                            <th>PG</th>
                            <th>Auto Withdrawal</th>
                            <th>Manual Withdrawal</th>
                        </tr>
                        </thead>
                        <tbody id="pgWithdrawalSummary">
                        </tbody>
                    </table>
                </div>

                <div class="table-responsive" id="payoutCrData">
                    <h6 class="font-weight-bold mb-2">Payout CR Data</h6>
                        <form action="javascript:void(0)" id="PayoutCrDataForm">
                            <div class="row">
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
                                            <option value="10" selected>10</option>
                                            <option value="20">20</option>
                                            <option value="50">50</option>
                                            <option value="100">100</option>
                                            <option value="200">200</option>
                                            <option value="500">500</option>
                                        </select>
                                    </div>
                                </div><!-- Col -->
                                <div class="col-auto">
                                    <label class="control-label"></label>
                                    <button class="btn btn-primary" type="submit">Apply</button>
                                    <button class="btn btn-danger" type="button"  onclick="resetPCrForm()">Clear</button>
                                </div><!-- Col -->
                            </div>
                        </form>
                    <table class="table table-hover table-bordered ">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>account Info</th>
                            <th>pg Info</th>
                            <th>utr</th>
                            <th>Balance </th>
                            <th>Date</th>
                        </tr>
                        </thead>
                        <tbody id="GetPayoutCrData">
                        </tbody>
                    </table>
                    <div id="pagination">
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>
