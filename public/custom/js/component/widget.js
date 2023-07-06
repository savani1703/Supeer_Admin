const blankTransactionDetailModal = `<div class="modal-header">
    <h5 class="modal-title">
    ***************************
    </h5>
    <button type="button" class="close" data-dismiss="modal">
        <i class="mdi mdi-close"></i>
    </button>
</div>
<div class="modal-body">
    <div>
        <div class="d-flex justify-content-between align-items-baseline mb-2 border-bottom py-3">
            <h6 class="card-title mb-0">
                Transaction Details
            </h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <tr>
                    <td class="font-weight-bold border-0">Transaction ID</td>
                    <td class="border-0">:</td>
                    <td class="border-0">***************************</td>
                </tr>
                <tr>
                    <td class="font-weight-bold border-0">Order ID</td>
                    <td class="border-0">:</td>
                    <td class="border-0">***************************</td>
                </tr>
                <tr>
                    <td class="font-weight-bold border-0">Status</td>
                    <td class="border-0">:</td>
                    <td class="border-0">
                    ***************************
                    </td>
                </tr>
                <tr>
                    <td class="font-weight-bold border-0">Amount</td>
                    <td class="border-0">:</td>
                    <td class="border-0">***************************</td>
                </tr>
                <tr>
                    <td class="font-weight-bold border-0">Method</td>
                    <td class="border-0">:</td>
                    <td class="border-0">***************************</td>
                </tr>
                <tr>
                    <td class="font-weight-bold border-0">UTR</td>
                    <td class="border-0">:</td>
                    <td class="border-0">***************************</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-baseline mt-3 border-bottom py-3">
        <h6 class="card-title mb-0">Customer Details</h6>
    </div>

    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <tr>
                <td class="font-weight-bold border-0">Customer Name</td>
                <td class="border-0">:</td>
                <td class="border-0">***************************</td>
            </tr>
            <tr>
                <td class="font-weight-bold border-0">Customer Email</td>
                <td class="border-0">:</td>
                <td class="border-0">***************************</td>
            </tr>
            <tr>
                <td class="font-weight-bold border-0">Customer Mobile</td>
                <td class="border-0">:</td>
                <td class="border-0">***************************</td>
            </tr>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-baseline mt-3 border-bottom py-3">
        <h6 class="card-title mb-0">Other Details</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <tr>
                <td class="font-weight-bold border-0">UDF1</td>
                <td class="border-0">:</td>
                <td class="border-0">***************************</td>
            </tr>
            <tr>
                <td class="font-weight-bold border-0">UDF2</td>
                <td class="border-0">:</td>
                <td class="border-0">***************************</td>
            </tr>
            <tr>
                <td class="font-weight-bold border-0">UDF3</td>
                <td class="border-0">:</td>
                <td class="border-0">***************************</td>
            </tr>
            <tr>
                <td class="font-weight-bold border-0">UDF4</td>
                <td class="border-0">:</td>
                <td class="border-0">***************************</td>
            </tr>
            <tr>
                <td class="font-weight-bold border-0">UDF5</td>
                <td class="border-0">:</td>
                <td class="border-0">***************************</td>
            </tr>
        </table>

    </div>
</div>`;

const blankPayoutDetail = `<div class="modal-header">
                    <h5 class="modal-title">***************************
                        <span class="d-block text-muted font-weight-bold"><small>***************************</small></span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span data-feather="x"></span>
                    </button>
                </div>
                <div class="modal-body">
                    <div>
                        <div class="d-flex justify-content-between align-items-baseline mb-2 border-bottom py-3">
                            <h6 class="card-title mb-0">
                                Payout Details
                            </h6>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <tr>
                                    <td class="font-weight-bold border-0">Payout ID</td>
                                    <td class="border-0">:</td>
                                    <td class="border-0">***************************</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold border-0">Ref. ID</td>
                                    <td class="border-0">:</td>
                                    <td class="border-0">***************************</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold border-0">Status</td>
                                    <td class="border-0">:</td>
                                    <td class="border-0">
                                        -
                                    </td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold border-0">Amount</td>
                                    <td class="border-0">:</td>
                                    <td class="border-0">***************************</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold border-0">Method</td>
                                    <td class="border-0">:</td>
                                    <td class="border-0">***************************</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold border-0">UTR</td>
                                    <td class="border-0">:</td>
                                    <td class="border-0">***************************</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-baseline mt-3 border-bottom py-3">
                        <h6 class="card-title mb-0">Customer Details</h6>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <tr>
                                <td class="font-weight-bold border-0">Account</td>
                                <td class="border-0">:</td>
                                <td class="border-0">
                                    <span class="d-block"><strong>A/C:</strong>***************************</span>
                                    <span class="d-block"><strong>IFSC:</strong>***************************</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold border-0">Customer Name</td>
                                <td class="border-0">:</td>
                                <td class="border-0">***************************</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold border-0">Customer Email</td>
                                <td class="border-0">:</td>
                                <td class="border-0">***************************</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold border-0">Customer Mobile</td>
                                <td class="border-0">:</td>
                                <td class="border-0">***************************</td>
                            </tr>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-baseline mt-3 border-bottom py-3">
                        <h6 class="card-title mb-0">Other Details</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <tr>
                                <td class="font-weight-bold border-0">UDF1</td>
                                <td class="border-0">:</td>
                                <td class="border-0">***************************</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold border-0">UDF2</td>
                                <td class="border-0">:</td>
                                <td class="border-0">***************************</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold border-0">UDF3</td>
                                <td class="border-0">:</td>
                                <td class="border-0">***************************</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold border-0">UDF4</td>
                                <td class="border-0">:</td>
                                <td class="border-0">***************************</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold border-0">UDF5</td>
                                <td class="border-0">:</td>
                                <td class="border-0">***************************</td>
                            </tr>
                        </table>
                    </div>
                </div>`;

const blankRefundDetail = `<div class="modal-header">
    <h5 class="modal-title">***************************
        <span class="d-block text-muted font-weight-bold"><small>***************************</small></span>
    </h5>
    <button type="button" class="close" data-dismiss="modal">
        <span class="svg-icon svg-icon-muted svg-icon-2hx">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="#5e5da9">
                <rect opacity="0.2" x="2" y="2" width="20" height="20" rx="10" fill="#5e5da9"/>
                <rect x="7" y="15.3137" width="12" height="2" rx="1" transform="rotate(-45 7 15.3137)" fill="#5e5da9"/>
                <rect x="8.41422" y="7" width="12" height="2" rx="1" transform="rotate(45 8.41422 7)" fill="#5e5da9"/>
            </svg>
        </span>
    </button>
</div>
<div class="modal-body" id="transactionDetailData">
    <div>
        <div class="d-flex justify-content-between align-items-baseline mb-2 border-bottom py-3">
            <h6 class="card-title mb-0">
                Refund Details
            </h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <tr>
                    <td class="font-weight-bold border-0">Refund ID</td>
                    <td class="border-0">:</td>
                    <td class="border-0">***************************</td>
                </tr>
                <tr>
                    <td class="font-weight-bold border-0">Transaction ID</td>
                    <td class="border-0">:</td>
                    <td class="border-0">***************************</td>
                </tr>
                <tr>
                    <td class="font-weight-bold border-0">Status</td>
                    <td class="border-0">:</td>
                    <td class="border-0">
                        ***************************
                    </td>
                </tr>
                <tr>
                    <td class="font-weight-bold border-0">Amount</td>
                    <td class="border-0">:</td>
                    <td class="border-0">***************************</td>
                </tr>
                <tr>
                    <td class="font-weight-bold border-0">Type</td>
                    <td class="border-0">:</td>
                    <td class="border-0">***************************</td>
                </tr>
                <tr>
                    <td class="font-weight-bold border-0">UTR</td>
                    <td class="border-0">:</td>
                    <td class="border-0">***************************</td>
                </tr>
                <tr>
                    <td class="font-weight-bold border-0">Reason</td>
                    <td class="border-0">:</td>
                    <td class="border-0">***************************</td>
                </tr>
                <tr>
                    <td class="font-weight-bold border-0">Expected Credit Date</td>
                    <td class="border-0">:</td>
                    <td class="border-0">***************************</td>
                </tr>
            </table>
        </div>
    </div>
</div>
`;

const transactionRefund = () => {
    return `<form class="forms-sample">
         <div class="form-group">
          <label for="exampleInputUsername1">Username</label>
          <input type="text" class="form-control" id="exampleInputUsername1" autocomplete="off" placeholder="Username">
         </div>
         <div class="form-group">
          <label for="exampleInputEmail1">Email address</label>
          <input type="email" class="form-control" id="exampleInputEmail1" placeholder="Email">
         </div>
         <div class="form-group">
          <label for="exampleInputPassword1">Password</label>
          <input type="password" class="form-control" id="exampleInputPassword1" autocomplete="off" placeholder="Password" aria-autocomplete="list">
         </div>
         <div class="form-check form-check-flat form-check-primary">
          <label class="form-check-label">
           <input type="checkbox" class="form-check-input">
           Remember me
          <i class="input-frame"></i></label>
         </div>
         <button type="submit" class="btn btn-primary mr-2">Submit</button>
         <button class="btn btn-light">Cancel</button>
        </form>`;
};
