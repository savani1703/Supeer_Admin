let PostData = {
    filter_data: {
        start_date:null,
        end_date:null,
    },
    report_type: 'BANK_TRANSACTION',
    page_no: 1,
    limit: 50,
};

let PaginateData = {
    link_limit: 2,
    from: 2,
    to: 2,
    total: null,
    is_last: null,
    current_item_count: null,
    current_page: null,
    last_page: null,
};

let autoRefreshInterval = null;
let refreshCnt = 0;
(new BankTransaction()).getBank();
DzpDatePickerService.init();
function BankTransaction() {
    this.getBank = () => {
        FsHelper.blockUi($("#bankTransactionPage"));
        FsClient.post("/bank-transaction", PostData).then(this.handleResponse).catch(this.handleError);
    }

    this.handleResponse = (data) => {
        FsHelper.unblockUi($("#bankTransactionPage"));
        if(data.status) {
            PaginateData.current_page = data.current_page;
            PaginateData.last_page = data.last_page;
            PaginateData.is_last_page = data.is_last_page;
            PaginateData.total = data.total_item;
            PaginateData.current_item_count = data.current_item_count;
            this.setTxnHtmlData(data.data);
            $('#pagination').show();
        } else {
            this.setErrorHtml();
        }
    }
    this.gettxnbyUtr = (bank_utr) => {
        $("#transaction_id").html("");
        $(".modal-body").html("");
        FsHelper.blockUi($(".modal-body"));
        let txnIdPostData = {
            bank_utr:bank_utr,
        }
        FsHelper.blockUi($("#bankpage"));
        FsClient.post("/transaction/byUtr",txnIdPostData).then(this.Responsetxn).catch(this.setErrorHtml2);
    }

    this.Responsetxn = (data) => {
        FsHelper.unblockUi($("#bankpage"));

        let idData = null;
        if(data.status) {
            $("#transaction_id").html(data.data.transaction_id);
            idData=`
                 <table class="table table-hover table-bordered">
                        <h5><div class="mt-2 mb-1 text-primary"> Transaction Detail</div></h5>
                        <tr><td><span class="text-muted">Order Id         </span></td><td class="pt-2 pl-4"> ${data.data.merchant_order_id ? data.data.merchant_order_id : "-" }</td></tr>
                        <tr><td><span class="text-muted">Payment Amount         </span></td><td class="pt-2 pl-4"> ${data.data.payment_amount ? data.data.payment_amount : "-" }</td></tr>
                        <tr><td><span class="text-muted">Currency               </span></td><td class="pt-2 pl-4"> ${data.data.currency ? data.data.currency : "-"}</td></tr>
                        <tr><td><span class="text-muted">Payment Status         </span></td><td> <label class="pt-2 pb-2  ml-2 badge ${this.getStatusClass(data.data.payment_status)}"">${data.data.payment_status ? data.data.payment_status : "-"}</lable> </td></tr>
                        <tr><td><span class="text-muted">Pg Fees                </span></td><td class="pt-2 pl-4"> ${data.data.pg_fees ? data.data.pg_fees : "-"}</td></tr>
                        <tr><td><span class="text-muted">Associate Fees         </span></td><td class="pt-2 pl-4"> ${data.data.associate_fees ? data.data.associate_fees : "-"}</td></tr>
                        <tr><td><span class="text-muted">Payable Amount         </span></td><td class="pt-2 pl-4"> ${data.data.payable_amount ? data.data.payable_amount : "-"}</td></tr>
                        <tr><td><span class="text-muted">Is Settled             </span></td><td class="pt-2 pl-4"> ${data.data.is_settled ? data.data.is_settled : "-"}</td></tr>
                        <tr><td><span class="text-muted">Pg Res Code            </span></td><td class="pt-2 pl-4"> ${data.data.pg_res_code ? data.data.pg_res_code : "-"}</td></tr>
                        <tr><td><span class="text-muted">Pg Ref Id              </span></td><td class="pt-2 pl-4"> ${data.data.pg_ref_id ? data.data.pg_ref_id : "-"}</td></tr>
                        <tr><td><span class="text-muted">Pg Ord Id              </span></td><td class="pt-2 pl-4"> ${data.data.pg_order_id ? data.data.pg_order_id : "-"}</td></tr>
                        <tr><td><span class="text-muted">Pg Res Msg             </span></td><td class="pt-2 pl-4"> ${data.data.pg_res_msg ? data.data.pg_res_msg : "-"}</td></tr>
                        <tr><td><span class="text-muted">Bank RRN               </span></td><td class="pt-2 pl-4"> ${data.data.bank_rrn ? data.data.bank_rrn : "-"}</td></tr>
                        <tr><td><span class="text-muted">Pg Name                </span></td><td class="pt-2 pl-4"> ${data.data.pg_name ? data.data.pg_name : "-"}</td></tr>
                        <tr><td><span class="text-muted">Is Webhook Call        </span></td><td class="pt-2 pl-4"> ${data.data.is_webhook_call ? data.data.is_webhook_call : "-"}</td></tr>
                        <tr><td><span class="text-muted">Callback Url           </span></td><td class="pt-2 pl-4"> <a href="${data.data.callback_url}" target="_blank">${data.data.callback_url ? data.data.callback_url : "-"}</td></a></tr>
                        <tr><td><span class="text-muted">Browser Id             </span></td><td class="pt-2 pl-4"> ${data.data.browser_id ? data.data.browser_id : "-"}</td></tr>
                </table>

                <table class="table table-hover table-bordered">
                        <h5><div class="mt-5 mb-1 text-primary"> Customer Detail</div></h5>
                        <tr><td><span class="text-muted">Customer Id         </span></td><td class="pt-2 pl-4"> ${data.data.customer_id ? data.data.customer_id : "-"}</td></tr>
                        <tr><td><span class="text-muted">Customer Email         </span></td><td class="pt-2 pl-4"> ${data.data.customer_email ? data.data.customer_email : "-"}</td></tr>
                        <tr><td><span class="text-muted">Customer Mobile        </span></td><td class="pt-2 pl-4"> ${data.data.customer_mobile ? data.data.customer_mobile : "-"}</td></tr>
                        <tr><td><span class="text-muted">Customer Ip            </span></td><td class="pt-2 pl-4"> ${data.data.customer_ip ? data.data.customer_ip : "-"}</td></tr>
                        <tr><td><span class="text-muted">Customer Name          </span></td><td class="pt-2 pl-4"> ${data.data.customer_name ? data.data.customer_name : "-"}</td></tr>
                        <tr><td><span class="text-muted">Payment Data          </span></td><td class="pt-2 pl-4"> ${data.data.payment_data ? data.data.payment_data : "-"}</td></tr>
                </table>

                <table class="table table-hover table-bordered">
                        <h5><div class="mt-5 mb-1 text-primary"> Player Detail</div></h5>
                        <tr><td><span class="text-muted">Register Date         </span></td><td class="pt-2 pl-4"> ${data.data.player_register_date ? data.data.player_register_date : "-"}</td></tr>
                        <tr><td><span class="text-muted">Deposit Amount        </span></td><td class="pt-2 pl-4"> ${data.data.player_deposit_amount ? data.data.player_deposit_amount : "-"}</td></tr>
                        <tr><td><span class="text-muted">Deposit Count            </span></td><td class="pt-2 pl-4"> ${data.data.player_deposit_count ? data.data.player_deposit_count : "-"}</td></tr>
                </table>

                <table class="table table-hover table-bordered">
                        <h5><div class="mt-5 mb-1 text-primary"> Payment Method Detail</div></h5>
                        <tr><td><span class="text-muted">ID         </span></td><td class="pt-2 pl-4"> ${data.data.method_detail ? data.data.method_detail.id : "-"}</td></tr>
                        <tr><td><span class="text-muted">Meta        </span></td><td class="pt-2 pl-4"> ${data.data.method_detail ? data.data.method_detail.pxn_meta : "-"}</td></tr>
                        <tr><td><span class="text-muted">Meta Code            </span></td><td class="pt-2 pl-4"> ${data.data.method_detail ? data.data.method_detail.meta_code : "-"}</td></tr>
                </table>

                <table class="table table-hover table-bordered">
                        <h5><div class="mt-5 mb-1 text-primary"> Other Detail</div></h5>
                         <tr><td><span class="text-muted">UDF1                  </span></td><td class="pt-2 pl-4"> ${data.data.udf1 ? data.data.udf1 : "-"}</td></tr>
                        <tr><td><span class="text-muted">UDF2                   </span></td><td class="pt-2 pl-4"> ${data.data.udf2 ? data.data.udf2 : "-"}</td></tr>
                        <tr><td><span class="text-muted">UDF3                   </span></td><td class="pt-2 pl-4"> ${data.data.udf3 ? data.data.udf3 : "-"}</td></tr>
                        <tr><td><span class="text-muted">UDF4                   </span></td><td class="pt-2 pl-4"> ${data.data.udf4 ? data.data.udf4 : "-"}</td></tr>
                        <tr><td><span class="text-muted">UDF5                   </span></td><td class="pt-2 pl-4"> ${data.data.udf5 ? data.data.udf5 : "-"}</td></tr>
                        <tr><td><span class="text-muted">Ip Data                </span></td><td class="pt-2 pl-4"> ${data.data.ip_data ? data.data.ip_data : "-"}</td></tr>
                        <tr><td><span class="text-muted">IS BLOCKED              </span></td><td class="pt-2 pl-4"> ${data.data.is_blocked ? data.data.is_blocked : "-"}</td></tr>
                        <tr><td><span class="text-muted">Reason                 </span></td><td class="pt-2 pl-4"> ${data.data.reason ? data.data.reason : "-"}</td></tr>
                        <tr><td><span class="text-muted">IS VPN IP              </span></td><td class="pt-2 pl-4"> ${data.data.is_vpn_ip ? data.data.is_vpn_ip : "-"}</td></tr>
                        <tr><td><span class="text-muted">Cust. Country          </span></td><td class="pt-2 pl-4"> ${data.data.cust_country ? data.data.cust_country : "-"}</td></tr>
                        <tr><td><span class="text-muted">Cust. State          </span></td><td class="pt-2 pl-4"> ${data.data.cust_state ? data.data.cust_state : "-"}</td></tr>
                        <tr><td><span class="text-muted">Cust. City          </span></td><td class="pt-2 pl-4"> ${data.data.cust_city ? data.data.cust_city : "-"}</td></tr>
                        <tr><td><span class="text-muted">Created At             </span></td><td class="pt-2 pl-4"> ${data.data.created_at_ist ? data.data.created_at_ist : "-"}</td></tr>
                        <tr><td><span class="text-muted">Updated At             </span></td><td class="pt-2 pl-4"> ${data.data.updated_at_ist ? data.data.updated_at_ist : "-"}</td></tr>
                </table>`;
            $(".modal-body").html(idData);
            $('.preLoaderModal').show()
        } else {
            this.setErrorHtml2();
        }
    }

    this.getStatusClass = (status) => {
        let badge = "badge-danger-muted";
        if(status === "Success") {
            badge = "badge-success";
        }
        if(status === "Failed") {
            badge = "badge-danger";
        }
        if(status === "Initialized") {
            badge = "badge-warning";

        } if(status === "Processing") {
            badge = "badge-info";
        }
        if(status === "Expired") {
            badge = "badge-info";
        }
        if(status === "Pending") {
            badge = "badge-outlineprimary";
        }
        if(status === "Not Attempted") {
            badge = "badge-outlineinfo";
        }
        return badge;
    }
    this.handleError = (error) => {
        if (error.status === 401){
            FsHelper.unauthorizedUserPage("unauthorized_user");
            $("#supportLogsDetail").html('');
        }else {
            this.setErrorHtml();
        }
    }
    this.setTxnHtmlData = (data) => {
        if(data && data.length > 0) {
            let htmlData = "";
            data.forEach((item, index) => {
                htmlData += `<tr>
                                  <td>
                                 <div class="d-block align-items-center flex-wrap text-nowrap">
                                    <span>
                                         <div class="dropdown mr-2">
                                        <button class="btn p-0" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-settings"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                                        </button>
                                        <div class="dropdown-menu mb-5" aria-labelledby="dropdownMenuButton" style="position: absolute">
                                            <h6 class="dropdown-header">${item.payment_utr}</h6>
                                           ${getBankActionButton(item.payment_utr,item.isget)}
                                        </div>
                                      </div>
                                  </span>
                                </div>
                                </td>
                                <td>
                                    <span class="d-block font-weight-bold">${item.created_at_ist ? item.created_at_ist : ""}</span>
                                </td>
                                <td>
                                    <span class="d-block font-weight-bold">${item.amount ? item.amount : ""}</span>
                                </td>
                                <td>
                                    <span class="d-block font-weight-bold mt-1">
                                         <a onclick="(new BankTransaction()).gettxnbyUtr('${item.payment_utr ? item.payment_utr : ""}');" href="" data-toggle="modal" data-target="#txnModal" > ${item.payment_utr ? item.payment_utr : ""} </a>
                                     </span>
                                </td>
                                <td>
                                    <span class="font-weight-bold mt-1"> ${item.isget ? "Yes" : "No"}</span>
                                </td>
                                  <td>
                                    <span class="d-block font-weight-bold mt-1"> ${item.upi_id ? item.upi_id : ""}</span>

                                </td>
                                  <td>
                                    <span class="d-block font-weight-bold mt-1">
                                        <a href="#" class="bank_txn_mobile_loadXeditable"
                                           data-type="text"
                                           data-pk="${item.id}"
                                           data-id="${item.id}"
                                           data-abc="true">${item.mobile_number ? item.mobile_number : "No Set"}</a>

                                     </span>
                                </td>
                                <td>
                                    <span class="d-block font-weight-bold mt-1"> ${item.description ? item.description : ""}</span>
                                </td>
                                <td>
                                    <span class="d-block font-weight-bold mt-1"> ${item.bank_details ? item.bank_details.account_holder_name : ""}</span>
                                    <span class="d-block font-weight-bold mt-1">A/C: ${item.account_number ? item.account_number : ""}</span>
                                    <span class="d-block font-weight-bold mt-1">IFSC: ${item.bank_details ? item.bank_details.ifsc_code : ""}</span>
                                    <span class="d-block font-weight-bold mt-1">UPI: ${item.bank_details ? item.bank_details.upi_id : ""}</span>
                                </td>
                                <td>
                                    <span class="d-block font-weight-bold mt-1"> ${item.transaction_mode ? item.transaction_mode : ""}</span>
                                </td>
                                <td>
                                    <span class="d-block font-weight-bold mt-1"> ${item.payment_mode ? item.payment_mode : ""}</span>
                                </td>
                                <td>
                                    <span class="d-block font-weight-bold mt-1"> ${item.transaction_date ? item.transaction_date : ""}</span>
                                </td>
                            </tr>`;
            });
            $('.preLoader').hide()
            $("#BankData").html(htmlData);
            setPaginateButton("page-change-event", PaginateData, PostData);
            loadEdiTable();
        } else {
            this.setErrorHtml();
        }
    }
    this.setErrorHtml = () => {
        $('.preLoader').hide();
        FsHelper.unblockUi($("#bankTransactionPage"));
        $('#pagination').hide();
        $("#BankData").html(`
            <tr>
                <td colspan="12">
                    <div class="text-center pt-5 pb-5">
                        <img src="/assets/images/record-not-found.svg" class="record-not-found">
                        <div class="mt-2">
                            <span>Record does not exist.</span>
                        </div>
                    </div>
                </td>
            </tr>
        `);
    }
    this.setErrorHtml2 = () => {
        $('.preLoader').hide();
        $('#pagination').hide();
        FsHelper.unblockUi($("#bankpage"));
        $(".modal-body").html(`
            <tr>
                <td colspan="5">
                    <div class="text-center pt-5 pb-5" style="width: 475px; margin: 0 auto; margin-top: 50px; background: transparent; box-shadow: none;">
                        <img src="/assets/images/record-not-found.svg" class="record-not-found">
                        <div class="mt-2">
                            <span>Record does not exist.</span>
                        </div>
                    </div>
                </td>
            </tr>
        `);
    }
}
function getBankActionButton(payment_utr,isget) {
    let actionButton = "";
    if(isget === false) {
        actionButton += `<a class="dropdown-item d-flex align-items-center mt-2 mb-2" href="#!"><div class="ml-2" onclick="markasused('${payment_utr}')"><i class="mdi mdi-rotate-left"></i> <span>Mark As Used</span></div></a>`;
        actionButton += `<a class="dropdown-item d-flex align-items-center" href="#!">
                 <div class="ml-2" onclick="upldate_via_payment_ref('${payment_utr}')">
                 <i class="mdi mdi-rotate-left"></i> <span>Update Payment Ref Id</span></div></a>`
        actionButton += `<a class="dropdown-item d-flex align-items-center" href="#!">
                 <div class="ml-2" onclick="upldate_via_transaction_id('${payment_utr}')">
                 <i class="mdi mdi-rotate-left"></i> <span>Update Transaction Id</span></div></a>`
    }
    if(actionButton.length < 1) {
        actionButton = `<p class='pl-2 pr-2'> There Is No Action Allow Here</p>`;
    }
    return actionButton;
}
function markasused(payment_utr) {
    FsHelper.blockUi($("#bankpage"));
    var myModal =  new jBox('Confirm', {
        confirmButton: 'YES',
        cancelButton: 'No',
        content: `Are You Sure Do you want to Set Used UTR (${payment_utr}) ?`,
        confirm: function () {
            let postData = {
                payment_utr: payment_utr,
            }
            FsClient.post("/bank/MarkAsUsed", postData).then(
                Response => {
                    toastr.success(Response.message, "success", toastOption);
                    FsHelper.unblockUi($("#bankpage"));
                    (new BankTransaction()).getBank();
                }
            ).catch(Error=>{
                toastr.error(Error.responseJSON.message, "error", toastOption);
                FsHelper.unblockUi($("#bankpage"));
                (new BankTransaction()).getBank();
            });
        },
        cancel : function (){
            FsHelper.unblockUi($("#bankpage"));
        }
    });
    myModal.open();
}

$("#bankForm").on("submit", () => {
    const FormData = getFormData($("#bankForm"));
    PostData.filter_data = {
        isget: null,
        amount: null,
        payment_utr:null,
        account_number:null,
        start_date:null,
        end_date:null,
        min_amount:null,
        max_amount:null,
        bank_account:null,
    }
    PostData.filter_data[FormData.FilterKey] = FormData.FilterValue;
    PostData.filter_data.min_amount = FormData.min_amount;
    PostData.filter_data.max_amount = FormData.max_amount;
    PostData.filter_data.bank_account = FormData.bank_account;
    PostData.filter_data.isget = FormData.is_get;
    PostData.limit = FormData.Limit;
    PostData.page_no=1;

    if(PostData.bank_name !== "All") {
        PostData.filter_data.bank_name = FormData.bank_name;
    }
    console.log(FormData);

    if(FormData.daterange) {
        let splitDate = FormData.daterange.split(/-/);
        PostData.filter_data.start_date = moment(splitDate[0], 'DD/MM/YYYY').format('YYYY-MM-DD 00:00:00');
        PostData.filter_data.end_date = moment(splitDate[1], 'DD/MM/YYYY').format('YYYY-MM-DD 23:59:59');
    }
    (new BankTransaction()).getBank();
});

EventListener.dispatch.on("page-change-event", (event, callback) => {
    PostData.page_no = callback.page_number;
    (new BankTransaction()).getBank();
});

function resetBankForm(){
    PostData.filter_data = {
        isget: null,
        amount: null,
        payment_utr:null,
        start_date:null,
        end_date:null,
        min_amount:null,
        max_amount:null,
        bank_account:null,
    }
    PostData.page_no=1;
    PostData.limit=50;

    $('#bankForm')[0].reset();
    DzpDatePickerService.init();
    (new BankTransaction()).getBank();
}

function generateBankTxnReport() {
    var myModal =  new jBox('Confirm', {
        confirmButton: 'YES',
        cancelButton: 'No',
        content: 'Are You Sure Do you want to Download Report ?',
        confirm: function () {
            FsClient.post("/support/GenerateReport", PostData).then(
                response => {
                    toastr.success(response.message,"success",toastOption);
                }
            ).catch(error => {
                toastr.error(error.responseJSON.message,"error",toastOption);
            });
        },
        cancel : function (){
        }
    });
    myModal.open();
}


$("#refreshTitle").html("Auto Refresh");
function autoRefreshTransaction() {
    $("#refreshTitle").html("Auto Refresh Off");
    if($("#autRefreshBtn").hasClass("active")) {
        $("#autRefreshBtn").removeClass("active");
        clearInterval(autoRefreshInterval);
        console.log(`Transaction Refresh Reset`)
        refreshCnt = 0;
    } else {
        $("#refreshTitle").html("Auto Refresh On");
        $("#autRefreshBtn").addClass("active");
        autoRefreshInterval = setInterval(() => {

            (new BankTransaction()).getBank();
            refreshCnt++;
            console.log(`Transaction Refresh: ${refreshCnt}`)
        }, 10000);
    }
}
$("#mergeUTRForm").on("submit", (e) => {
    e.preventDefault()
    const FormData = getFormData($("#mergeUTRForm"));
    FsHelper.blockUi($("#mergeUTRForm"));
    FsClient.post("/bank-transaction/mergeUtr", FormData)
        .then(res => {
            toastr.success(res.message, "success", options);
            $("#mergeUTRForm")[0].reset();
            FsHelper.unblockUi($("#mergeUTRForm"));
            $("#close_btn_manualmerge").click();
        })
        .catch(err => {
            toastr.error(err.responseJSON.message, "Error", options);
            console.log(err);
        })
        .finally(() => {
            FsHelper.unblockUi($("#mergeUTRForm"));
        });
});

$("#ManualBankEntryForm").on("submit", (e) => {
    e.preventDefault()
    const FormData = getFormData($("#ManualBankEntryForm"));
    FsHelper.blockUi($("#addmanualBankEntry"));
    FsClient.post("/add/bank-transaction", FormData)
        .then(res => {
            toastr.success(res.message, "success", options);
            $("#ManualBankEntryForm")[0].reset();
            FsHelper.unblockUi($("#addmanualBankEntry"));
            $("#close_btn_manual_Entry").click();
        })
        .catch(err => {
            toastr.error(err.responseJSON.message, "Error", options);
            console.log(err);
        })
        .finally(() => {
            FsHelper.unblockUi($("#addmanualBankEntry"));
        });
});

$("#addManualEntryInBank").on("click", (e) => {
    e.preventDefault()
    const FormData = getFormData($("#ManualBankEntryForm"));
    FsHelper.blockUi($("#addmanualBankEntry"));
    FsClient.post("/get/available-bank", FormData)
    .then(res => {
        let AcName = "";
            res.data.forEach((item, index) => {
                AcName += ` <option value="${item.account_number}">${item.account_holder_name}</option>`;
                 });
            $("#accountLoad").html(AcName);
        })
        .catch(err => {
            toastr.error(err.responseJSON.message, "Error", options);
            console.log(err);
        })
        .finally(() => {
            FsHelper.unblockUi($("#addmanualBankEntry"));
        });
});

$("#AccountList").hide();

$("#bank_name").on("change", () => {
    const FormData = getFormData($("#bankForm"));
    if (FormData.bank_name === "All"){
        $("#AccountList").hide();
    }
    if (FormData.bank_name){
        const postData={bank_name:FormData.bank_name }
        if (FormData.bank_name !=='All'){
            FsHelper.blockUi($("#bankTransactionPage"));
            FsClient.post('/get/available-bank',postData)
                .then(response => {
                    if (response.data) {
                        $.each(response.data, (index, item) => {
                            let data = ' <option value="All">ALL</option>';
                            response.data.forEach((item, index) => {
                                console.log(item)
                                data += `
                                    <option value="${item.account_number}" >${item.account_holder_name}</option>
                                 `;
                            });
                            $("#bank_account").html(data);
                        });
                    }
                })
                .catch(error => {
                    console.log(error);
                })
                .finally(() => {
                    FsHelper.unblockUi($("#bankTransactionPage"));
                });
            $("#AccountList").show();
        }
    }
});
function upldate_via_payment_ref(utr_no) {
    $.confirm({
        title: 'Set Payment Ref!',
        content: '' +
            '<form action="" class="formName" id="txn_refForm">' +
            '<div class="form-group">' +
            '<label>Enter Payment Ref ID</label>' +
            '<input type="text" placeholder="Payment Ref"  name="payment_ref" class="name form-control" required />' +
            '</div>' +
            '</form>',
        buttons: {
            formSubmit: {
                text: 'Submit',
                btnClass: 'btn-blue',
                action: function () {
                    var name = this.$content.find('.name').val();
                    if(!name){
                        $.alert('provide a valid Payment Ref Id');
                        return false;
                    }
                    FsHelper.blockUi($("#bankpage"));
                    const tempUtr = getFormData($("#txn_refForm"));
                    let postData = {
                        utr_no_id: utr_no,
                        payment_ref_id: tempUtr.payment_ref,
                    }
                    FsClient.post("/transaction/update/setUtrPaymentRef", postData).then(
                        Response => {
                            toastr.success(Response.message,"success",toastOption);
                            FsHelper.unblockUi($("#bankpage"));
                        }
                    ).catch(Error => {
                        toastr.error(Error.responseJSON.message,"error",toastOption);
                        FsHelper.unblockUi($("#bankpage"));

                    });
                }
            },
            cancel: function () {
                //close
            },
        },
        onContentReady: function () {
            // bind to events
            var jc = this;
            this.$content.find('form').on('submit', function (e) {
                // if the user submits the form by pressing enter in the field.
                e.preventDefault();
                jc.$$formSubmit.trigger('click'); // reference the button and click it
            });
        }
    });
    FsHelper.unblockUi($("#bankpage"));
}
function upldate_via_transaction_id(utr_no) {
    $.confirm({
        title: 'Set Payment ID',
        content: '' +
            '<form action="" class="formName" id="txn_refForm">' +
            '<div class="form-group">' +
            '<label>Enter Payment Ref ID</label>' +
            '<input type="text" placeholder="Transaction Id"  name="transaction_id" class="name form-control" required />' +
            '</div>' +
            '</form>',
        buttons: {
            formSubmit: {
                text: 'Submit',
                btnClass: 'btn-blue',
                action: function () {
                    var name = this.$content.find('.name').val();
                    if(!name){
                        $.alert('provide a valid Payment Ref Id');
                        return false;
                    }
                    FsHelper.blockUi($("#bankpage"));
                    const tempUtr = getFormData($("#txn_refForm"));
                    let postData = {
                        utr_no_id: utr_no,
                        transaction_id: tempUtr.payment_ref,
                    }
                    FsClient.post("/transaction/update/setUtrTransaction_id", postData).then(
                        Response => {
                            toastr.success(Response.message,"success",toastOption);
                            FsHelper.unblockUi($("#bankpage"));
                        }
                    ).catch(Error => {
                        toastr.error(Error.responseJSON.message,"error",toastOption);
                        FsHelper.unblockUi($("#bankpage"));

                    });
                }
            },
            cancel: function () {
                //close
            },
        },
        onContentReady: function () {
            // bind to events
            var jc = this;
            this.$content.find('form').on('submit', function (e) {
                // if the user submits the form by pressing enter in the field.
                e.preventDefault();
                jc.$$formSubmit.trigger('click'); // reference the button and click it
            });
        }
    });
    FsHelper.unblockUi($("#bankpage"));
}
