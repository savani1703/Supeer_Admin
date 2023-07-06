<div class="card mb-2">
    <div class="p-3">
        <h6 class="font-weight-bold">Merchant PayIn/Payout Summary</h6>
    </div>
    <div class="card-body">
        <form action="javascript:void(0)" class="mb-2" id="dashboardForm">
            <div class="row mt-1">
                <div class="col-auto">
                    <div class="form-group">
                        <div class="input-group date datepicker dashboard-date mr-2 mb-2 mb-0 d-xl-flex">
                            <span class="input-group-addon bg-transparent"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-calendar  text-primary"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg></span>
                            <input type="text" class="form-control dashboard-daterange" name="daterange1"  id="dashboardDatePicker" autocomplete="off">
                        </div>
                    </div>
                </div>
                <div class="col-auto">
                    <label class="control-label"></label>
                    <button class="btn btn-primary" type="submit">Apply</button>
                </div>
            </div>
        </form>

        <div class="table-responsive" id="dashboardData">
            <table class="table table-hover table-bordered ">
                <thead>
                <tr>
                    <th>Client name</th>
                    <th>Collection</th>
                    <th>Payout</th>
                    <th>Payout Balance</th>
                    <th>Unsettled Balance</th>
                    <th>Cur. Min Ticket</th>
                    <th>Cur. Max Ticket</th>
                </tr>
                </thead>
                <tbody id="mmDashboardData">
                </tbody>
            </table>
        </div>
    </div>
</div>
