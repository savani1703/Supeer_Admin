let PostData = {
    filter_data: {
     start_date:moment().format("YYYY-MM-DD 00:00:00"),
        end_date:moment().format("YYYY-MM-DD 23:59:59"),
    },
     report_type: 'PAYOUT',
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

const status = {
    Success: 'badge-success',
    Failed: 'badge-danger',
    Initialized: 'badge-warning',
    Pending: 'badge-outlineprimary',
    LOWBAL: 'badge-secondary',
    Cancelled: 'badge-secondary',
};

(new PayoutData()).getPayout();
(new PayoutData()).getConfig();
DzpDatePickerService.init();

function PayoutData() {
    this.getPayout = (isLoading = true) => {
        if(isLoading) {
            FsHelper.blockUi($("#payoutpage"));
        }
        FsClient.post("/payout", PostData).then(this.handleResponse).catch(this.handleError);
    }
    this.getConfig = (isLoading = true) => {
        FsClient.post("/payout/GetPayoutConfiguration").then(
            Response => {
                const data = Response.data;
                $.each(data, (index, item) => {
                    if(index === "is_auto_transfer_enable" || index === "is_payout_status_call_enable" || index === "small_first" || index === "large_first" || index==="is_auto_level_active") {
                        $(`#${index}`).prop("checked", item)
                    } else {
                        $(`#${index}`).val(item)
                    }
                })
            }
        ).catch(Error=>{
            console.log(Error)
            toastr.error(Error.responseJSON.message, "error", toastOption);
        });

    }
    this.getPayoutById = (payout_id) => {
        FsHelper.blockUi($(".modal-body"));
        let paytmIdPostData = {
            payout_id:payout_id,
        }
        FsHelper.blockUi($("#payoutpage"));
        FsClient.post("/payout/byId",paytmIdPostData).then(this.Response).catch(this.setErrorHtml2);
    }
    this.Response = (data) => {
        let idData = null;
        if(data.status) {
            FsHelper.unblockUi($("#payoutpage"));
            $("#payout_id").html(data.data.payout_id);
            let PayoutStatus = status[data.data.payout_status];
            idData=`
                     <table class="table table-hover table-bordered">
                        <h5><div class="mt-2 mb-1 text-primary">Payout Detail</div></h5>
                        <tr><td><span class="text-muted">Payout Amount          </span></td><td class="pt-2 pl-4"> ${data.data.payout_amount ? data.data.payout_amount : "-" }</td></tr>
                        <tr><td><span class="text-muted">Payout Currency        </span></td><td class="pt-2 pl-4"> ${data.data.payout_currency ? data.data.payout_currency : "-"}</td></tr>
                        <tr><td><span class="text-muted">Payout  Fees           </span></td><td class="pt-2 pl-4"> ${data.data.payout_fees ? data.data.payout_fees : "-"}</td></tr>
                        <tr><td><span class="text-muted">Associate Fees         </span></td><td class="pt-2 pl-4"> ${data.data.associate_fees ? data.data.associate_fees : "-"}</td></tr>
                        <tr><td><span class="text-muted">Total Amount           </span></td><td class="pt-2 pl-4"> ${data.data.total_amount ? data.data.total_amount : "-"}</td></tr>
                        <tr><td><span class="text-muted">Payout Status          </span></td><td> <label class="pt-2 pb-2  ml-2 badge ${PayoutStatus} ">${data.data.payout_status ? data.data.payout_status : "-"}</lable> </td></tr>
                        <tr><td><span class="text-muted">Payout Type            </span></td><td class="pt-2 pl-4"> ${data.data.payout_type ? data.data.payout_type : "-"}</td></tr>
                        <tr><td><span class="text-muted">Vpa Address            <span></td><td  class="pt-2 pl-4"> ${data.data.vpa_address ? data.data.vpa_address : "-"}</td></tr>
                        <tr><td><span class="text-muted">Internal Status        </span></td><td class="pt-2 pl-4"> ${data.data.internal_status ? data.data.internal_status : "-"}</td></tr>
                        <tr><td><span class="text-muted">Is Webhook Called      </span></td><td class="pt-2 pl-4"> ${data.data.is_webhook_called ? data.data.is_webhook_called : "-"}</td></tr>
                        <tr><td><span class="text-muted">Payout By              </span></td><td class="pt-2 pl-4"> ${data.data.payout_by ? data.data.payout_by : "-"}</td></tr>
                </table>
                 <table class="table table-hover table-bordered">
                        <h5><div class="mt-5 mb-1 text-primary">Customer Detail</div></h5>
                        <tr><td><span class="text-muted">Customer ID         </span></td><td class="pt-2 pl-4"> ${data.data.customer_id ? data.data.customer_id : "-"}</td></tr>
                        <tr><td><span class="text-muted">Customer Email         </span></td><td class="pt-2 pl-4"> ${data.data.customer_email ? data.data.customer_email : "-"}</td></tr>
                        <tr><td><span class="text-muted">Customer Mobile        </span></td><td class="pt-2 pl-4"> ${data.data.customer_mobile ? data.data.customer_mobile : "-"}</td></tr>
                        <tr><td><span class="text-muted">Customer Name          </span></td><td class="pt-2 pl-4"> ${data.data.customer_name ? data.data.customer_name : "-"}</td></tr>
                        <tr><td><span class="text-muted">Customer Ip            </span></td><td class="pt-2 pl-4"> ${data.data.customer_ip ? data.data.customer_ip : "-"}</td></tr>
                </table>
                <table class="table table-hover table-bordered">
                        <h5><div class="mt-5 mb-1 text-primary">Past History</div></h5>
                        <tr><td><span class="text-muted">Payout Count         </span></td><td class="pt-2 pl-4"> ${data.data.payout_count ? data.data.payout_count : "-"}</td></tr>
                        <tr><td><span class="text-muted">Total Payout Amount         </span></td><td class="pt-2 pl-4"> ${data.data.total_payout_amount ? data.data.total_payout_amount : "-"}</td></tr>
                </table>
                <table class="table table-hover table-bordered">
                        <h5><div class="mt-5 mb-1 text-primary">PG Detail</div></h5>
                         <tr><td><span class="text-muted">Pg Name                </span></td><td class="pt-2 pl-4"> ${data.data.pg_name ? data.data.pg_name : "-"}</td></tr>
                         <tr><td><span class="text-muted">Pg RefId               </span></td><td class="pt-2 pl-4"> ${data.data.pg_ref_id ? data.data.pg_ref_id : "-"}</td></tr>
                        <tr><td><span class="text-muted">Pg Response Msg        </span></td><td class="pt-2 pl-4"> ${data.data.pg_response_msg ? data.data.pg_response_msg : "-"}</td></tr>
                        <tr><td><span class="text-muted">Pg Payout Date         </span></td><td class="pt-2 pl-4"> ${data.data.pg_payout_date ? data.data.pg_payout_date : "-"}
                </table>
                <table class="table table-hover table-bordered">
                        <h5><div class="mt-5 mb-1 text-primary">Bank Detail</div></h5>
                         <tr><td><span class="text-muted">Account Holder Name    </span></td><td class="pt-2 pl-4"> ${data.data.account_holder_name ? data.data.account_holder_name : "-"}</td></tr>
                        <tr><td><span class="text-muted">Bank Name              </span></td><td class="pt-2 pl-4"> ${data.data.bank_name ? data.data.bank_name : "-"}</td></tr>
                        <tr><td><span class="text-muted">Bank Account           </span></td><td class="pt-2 pl-4"> ${data.data.bank_account ? data.data.bank_account : "-"}</td></tr>
                        <tr><td><span class="text-muted">Ifsc Code              </span></td><td class="pt-2 pl-4"> ${data.data.ifsc_code ? data.data.ifsc_code : "-"}</td></tr>
                        <tr><td><span class="text-muted">Bank RRN               </span></td><td class="pt-2 pl-4"> ${data.data.bank_rrn ? data.data.bank_rrn : "-"}</td></tr>
                </table>
                 <table class="table table-hover table-bordered">
                        <h5><div class="mt-5 mb-1 text-primary">Other Detail</div></h5>
                        <tr><td><span class="text-muted">UDF1                   </span></td><td class="pt-2 pl-4"> ${data.data.udf1 ? data.data.udf1 : "-"}</td></tr>
                        <tr><td><span class="text-muted">UDF2                   </span></td><td class="pt-2 pl-4"> ${data.data.udf2 ? data.data.udf2 : "-"}</td></tr>
                        <tr><td><span class="text-muted">UDF3                   </span></td><td class="pt-2 pl-4"> ${data.data.udf3 ? data.data.udf3 : "-"}</td></tr>
                        <tr><td><span class="text-muted">UDF4                   </span></td><td class="pt-2 pl-4"> ${data.data.udf4 ? data.data.udf4 : "-"}</td></tr>
                        <tr><td><span class="text-muted">UDF5                   </span></td><td class="pt-2 pl-4"> ${data.data.udf5 ? data.data.udf5 : "-"}</td></tr>
                        <tr><td><span class="text-muted">Latitude               </span></td><td class="pt-2 pl-4"> ${data.data.latitude ? data.data.latitude : "-"}</td></tr>
                        <tr><td><span class="text-muted">Longitude              </span></td><td class="pt-2 pl-4"> ${data.data.longitude ? data.data.longitude : "-"}</td></tr>
                        <tr><td><span class="text-muted">Created At             </span></td><td class="pt-2 pl-4"> ${data.data.created_at_ist ? data.data.created_at_ist : "-"}</td></tr>
                        <tr><td><span class="text-muted">Updated At             </span></td><td class="pt-2 pl-4"> ${data.data.updated_at_ist ? data.data.updated_at_ist : "-"}</td></tr>
                </table>
            `;
            $(".modal-body").html(idData);
            $('.preLoaderModal').show()
        } else {
            this.setErrorHtml2();
        }
    }

    this.handleResponse = (data) => {
        FsHelper.unblockUi($("#payoutpage"));
        if(data.status) {
            PaginateData.current_page = data.current_page;
            PaginateData.last_page = data.last_page;
            PaginateData.is_last_page = data.is_last_page;
            PaginateData.total = data.total_item;
            PaginateData.current_item_count = data.current_item_count;
            this.setTxnHtmlData(data.data);
            this.setTxnSummaryHtml(data.payout_summary);
            $('#pagination').show();
        } else {
            this.setErrorHtml();
        }
    }

    this.handleError = (error) => {
        console.log(error)
        FsHelper.unblockUi($("#payoutpage"));
        if(error.responseJSON.payout_summary) {
            this.setTxnSummaryHtml(error.responseJSON.payout_summary);
        }
        if (error.status === 401){
            FsHelper.unauthorizedUserPage("unauthorized_user");
            $("#payoutDetail").html('');
        }else {
            this.setErrorHtml();
        }
    }

    this.setTxnHtmlData = (data) => {
        if(data && data.length > 0) {
            let htmlData = "";
            data.forEach((item, index) => {
                let PayoutStatus = status[item.payout_status];
                htmlData += `<tr>
                                <td>
                                 <div class="d-block align-items-center flex-wrap text-nowrap">
                                    <span>
                                         <div class="dropdown mr-2">
                                        <button class="btn p-0" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-settings"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                                        </button>
                                        <div class="dropdown-menu mb-5" aria-labelledby="dropdownMenuButton" style="position: absolute">
                                            <h6 class="dropdown-header">${item.payout_id}</h6>
                                            ${getPayoutActionButton(item.payout_id,item.pg_ref_id, item.payout_status, item.is_webhook_called)}
                                        </div>
                                      </div>
                                  </span>
                                </div>
                                </td>
                                <td>
                                    <span class="d-block font-weight-bold mt-1"><a onclick="(new PayoutData()).getPayoutById('${item.payout_id}');
                                        " href="" data-toggle="modal" data-target="#payoutModal" >
                                        ${item.payout_id ? item.payout_id : ""} </a> </span>
                                    <span class="d-block mt-1">Order#: ${item.merchant_ref_id ? item.merchant_ref_id : ""}</span>
                                    <span class="d-block mt-1">PG Ref#: ${item.pg_ref_id ? item.pg_ref_id : ""}</span>
                                    <span class="d-block">UTR: ${item.bank_rrn ? item.bank_rrn : ""}</span>
                                    <span class="d-block">Temp UTR: ${item.temp_bank_rrn ? item.temp_bank_rrn : ""}</span>
                                    <span class="d-block">Cust Id: ${item.customer_id ? item.customer_id : ""}</span>
                                    <span class="d-block">${item.process_by ? `<span>Process By: </span>`+ item.process_by : ""}</span>
                                </td>

                                <td>
                                    <span class="d-block mt-1">AC Name : ${item.account_holder_name ? item.account_holder_name : ""}</span>
                                    <span class="d-block mt-1">Bank @ : ${item.bank_name ? item.bank_name : ""}</span>
                                    <span class="d-block mt-1">AC @ : ${item.bank_account ? item.bank_account : ""}</span>
                                    <span class="d-block">Ifsc : ${item.ifsc_code ? item.ifsc_code : ""}</span>
                                </td>
                                    <td>
                                    <span class="d-block mb-1"> Count : ${item.payout_count ? item.payout_count : ""}</span>
                                    <span class="d-block"> Total Amount : ${item.total_payout_amount ? item.total_payout_amount : ""} </span>
                                </td>
                                <td>
                                    <span class="d-block font-weight-bold mb-1">${item.merchant_details ? item.merchant_details.merchant_name : ""}</span>
                                    <span class="d-block"><small>${item.merchant_id ? item.merchant_id : ""}</small></span>
                                </td>

                                <td>
                                    <span class="font-weight-bold badge ${PayoutStatus} mt-1"> ${item.payout_status ? item.payout_status : ""}</span>
                                </td>
                                <td>
                                    <span class="d-block mt-1"> ${item.payout_type ? item.payout_type : ""}</span>
                                </td>
                                <td>
                                    <span class="d-block mt-1">Amount: ${item.payout_amount ? item.payout_amount : ""}</span>
                                    <span class="d-block mt-1">Fees: ${item.payout_fees ? item.payout_fees : 0}</span>
                                    <span class="d-block mt-1">Assoc. Fees: ${item.associate_fees ? item.associate_fees : 0}</span>
                                    <span class="d-block mt-1">Total: ${item.total_amount ? item.total_amount : 0}</span>
                                </td>
                                <td>
                                    <span class="d-block">${item.pg_label ? item.pg_label : ""}</span>
                                    <span class="d-block mt-1">${item.pg_name ? item.pg_name : ""} ${item.meta_id ? `(${item.meta_id})` : ""}</span>
                                    <span class="d-block">${item.manual_pay_batch_id ? `<span>Pay Batch @ : </span>`+ item.manual_pay_batch_id : ""}</span>
                                </td>

                                <td>
                                    <span class="d-block">Create: ${item.created_at_ist ? item.created_at_ist : ""}</span>
                                    <span class="d-block">Update: ${item.updated_at_ist ? item.updated_at_ist : ""}</span>
                                    <span class="d-block">Success: ${item.success_at_ist ? item.success_at_ist : ""}</span>
                                </td>
                                 <td>
                                   <button onclick="(new PayoutData()).setManualPayoutData('${item.payout_id}', '${item.bank_rrn}', '${item.amount}');" type="button" class="btn btn-outline-primary mdi mdi-update" data-toggle="modal" data-target="#updateManualPayout"> Update </button>
                                </td>
                            </tr>`;
            });
            $('.preLoader').hide()
            $("#PayoutData").html(htmlData);
            setPaginateButton("page-change-event", PaginateData, PostData);
        } else {
            this.setErrorHtml();
        }
    }

    this.setTxnSummaryHtml = (data) => {
        if(data) {
            $.each(data, (index, item) => {
                $("#__"+index).text(parseFloat(item).toFixed(2));
            })
        }
    }

    this.setManualPayoutData = (payout_id, bank_rrn) => {
        $('#updateManualPayout #payout_id').val(payout_id);
        $('#updateManualPayout #payout_utr').val(bank_rrn ? bank_rrn : '');
    }

    this.setErrorHtml = () => {
        $('.preLoader').hide();
        $('#pagination').hide();
        FsHelper.unblockUi($("#payoutpage"));
        $("#PayoutData").html(`
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
        FsHelper.unblockUi($("#payoutpage"));
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

    this.setPayoutConfig = (postData) => {

        FsHelper.blockUi($("#updateBankTransferConfigForm"));
        FsClient.post("/payout/UpdatePayoutConfiguration", postData)
            .then(res => {
                console.log(res)
                this.getConfig(true);
                toastr.success(res.message, "Success", options);
            })
            .catch(err => {
                toastr.error(err.responseJSON.message, "Error", options);
                console.log(err);
            })
            .finally(() => {
                FsHelper.unblockUi($("#updateBankTransferConfigForm"))
            });
    }

}
$("#updateBankTransferConfigForm").submit(() => {
    const FormData = getFormData($("#updateBankTransferConfigForm"));
    FormData.is_auto_transfer_enable = $("#is_auto_transfer_enable").is(':checked') ? 1 : 0;
    FormData.is_payout_status_call_enable = $("#is_payout_status_call_enable").is(':checked') ? 1 : 0;
    FormData.small_first = $("#small_first").is(':checked') ? 1 : 0;
    FormData.large_first = $("#large_first").is(':checked') ? 1 : 0;
    FormData.is_auto_level_active = $("#is_auto_level_active").is(':checked') ? 1 : 0;
    (new PayoutData()).setPayoutConfig(FormData);
});


$("#PayoutForm").on("submit", () => {
    const FormData = getFormData($("#PayoutForm"));
    console.log(FormData);
    PostData.filter_data = {
        payout_id: null,
        merchant_ref_id: null,
        merchant_id: null,
        customer_id: null,
        customer_email: null,
        customer_mobile: null,
        customer_name: null,
        payout_type: null,
        payout_amount: null,
        temp_bank_rrn: null,
        process_by: null,
        pg_name: null,
        min_amount:null,
        max_amount:null,
        account_holder_name: null,
        udf1: null,
        udf2: null,
        udf3: null,
        udf4: null,
        udf5: null,
        bank_rrn: null,
        status: null,
        start_date:null,
        meta_id:null,
        end_date:null,
        success_start_date:null,
        success_end_date:null,
    }
    PostData.filter_data[FormData.FilterKey] = FormData.FilterValue;
    PostData.filter_data.status = FormData.status;
    PostData.filter_data.merchant_id = FormData.merchant_id;
    PostData.filter_data.pg_name = FormData.pg_name;
    PostData.filter_data.min_amount = FormData.min_amount;
    PostData.filter_data.max_amount = FormData.max_amount;
    PostData.filter_data.payout_type = FormData.payout_type;
    PostData.filter_data.meta_id = FormData.meta_id;
    PostData.limit = FormData.limit;
    PostData.page_no=1;

    if(FormData.daterange) {
        let splitDate = FormData.daterange.split(/-/);
        PostData.filter_data.start_date = moment(splitDate[0], 'DD/MM/YYYY').format('YYYY-MM-DD 00:00:00');
        PostData.filter_data.end_date = moment(splitDate[1], 'DD/MM/YYYY').format('YYYY-MM-DD 23:59:59');
    }
    if(FormData.daterange1) {
        let splitDate = FormData.daterange1.split(/-/);
        PostData.filter_data.success_start_date = moment(splitDate[0], 'DD/MM/YYYY').format('YYYY-MM-DD 00:00:00');
        PostData.filter_data.success_end_date = moment(splitDate[1], 'DD/MM/YYYY').format('YYYY-MM-DD 23:59:59');
    }
    (new PayoutData()).getPayout();
});

EventListener.dispatch.on("page-change-event", (event, callback) => {
    PostData.page_no = callback.page_number;
    (new PayoutData()).getPayout();
});

function PayoutWebhook(payout_id) {
    var myModal =  new jBox('Confirm', {
        confirmButton: 'YES',
        cancelButton: 'No',
        content: 'Are You Sure Do you want to Resend Webhook ?',
        confirm: function () {
            let postData = {
                payout_id: payout_id,
            }
            FsClient.post("/payout/resend-webhook", postData).then(
                Response => {
                    toastr.success(Response.message, "success", toastOption);
                    FsHelper.unblockUi($("#payoutpage"));
                    (new PayoutData()).getPayout();
                }
            ).catch(Error=>{
                toastr.error(Error.responseJSON.message, "error", toastOption);
                FsHelper.unblockUi($("#payoutpage"));
                (new PayoutData()).getPayout();

            });
        },
        cancel : function (){

        }
    });
    myModal.open();
}
function InitializedPayout(payout_id) {
    var myModal =  new jBox('Confirm', {
        confirmButton: 'YES',
        cancelButton: 'No',
        content: `Are You Sure Do you want to Initialized this Payout Request (${payout_id}) ?`,
        confirm: function () {
            let postData = {
                payout_id: payout_id,
            }
            FsClient.post("/payout/ResetInitializedPayout", postData).then(
                Response => {
                    toastr.success(Response.message, "success", toastOption);
                    FsHelper.unblockUi($("#payoutpage"));
                    (new PayoutData()).getPayout();
                }
            ).catch(Error=>{
                toastr.error(Error.responseJSON.message, "error", toastOption);
                FsHelper.unblockUi($("#payoutpage"));
                (new PayoutData()).getPayout();

            });
        },
        cancel : function (){

        }
    });
    myModal.open();
}
function cancelPayout(payout_id) {
    var myModal =  new jBox('Confirm', {
        confirmButton: 'YES',
        cancelButton: 'No',
        content: `Are You Sure Do you want to Cancel this Payout Request (${payout_id}) ?`,
        confirm: function () {
            let postData = {
                payout_id: payout_id,
            }
            FsClient.post("/payout/CancelledInitializedPayout", postData).then(
                Response => {
                    toastr.success(Response.message, "success", toastOption);
                    FsHelper.unblockUi($("#payoutpage"));
                    (new PayoutData()).getPayout();
                }
            ).catch(Error=>{
                toastr.error(Error.responseJSON.message, "error", toastOption);
                FsHelper.unblockUi($("#payoutpage"));
                (new PayoutData()).getPayout();

            });
        },
        cancel : function (){

        }
    });
    myModal.open();
}


function resetLowBal() {
    var myModal =  new jBox('Confirm', {
        confirmButton: 'YES',
        cancelButton: 'No',
        content: `Are You Sure Do you want to Reset Low Balance`,
        confirm: function () {
            FsClient.post("/payout/ResetLowBalPayoutToInitialize").then(
                Response => {
                    toastr.success(Response.message, "success", toastOption);
                    FsHelper.unblockUi($("#payoutpage"));
                    (new PayoutData()).getPayout();
                }
            ).catch(Error=>{
                console.log(Error)
                toastr.error(Error.responseJSON.message, "error", toastOption);
                FsHelper.unblockUi($("#payoutpage"));
            });
        },
        cancel : function (){

        }
    });
    myModal.open();
}



function resetPayoutForm(){
    PostData.filter_data = {
        payout_id: null,
        merchant_ref_id: null,
        merchant_id: null,
        customer_email: null,
        customer_mobile: null,
        customer_name: null,
        customer_id: null,
        process_by: null,
        payout_amount: null,
        pg_name: null,
        payout_type: null,
        temp_bank_rrn: null,
        account_holder_name: null,
        min_amount:null,
        max_amount:null,
        udf1: null,
        udf2: null,
        udf3: null,
        udf4: null,
        udf5: null,
        bank_rrn: null,
        status: null,
        start_date:null,
        end_date:null,
        success_start_date:null,
        success_end_date:null,
    }
    PostData.page_no=1;
    PostData.limit=50;

    $('#PayoutForm')[0].reset();
    $("#payoutPG").hide();
    DzpDatePickerService.init();
    (new PayoutData()).getPayout();
}

function getPayoutActionButton(payoutId,pg_ref_id, payoutStatus, isWebhookCalled) {
    let actionButton = "";
    if(payoutStatus === "Pending") {
        actionButton += `<a class="dropdown-item d-flex align-items-center mt-2 mb-2" href="#!"><div class="ml-2" onclick="InitializedPayout('${payoutId}')"><i class="mdi mdi-rotate-left"></i> <span>Set Payout To Initialized</span></div></a>`;
         }
    if((payoutStatus === "Failed" || payoutStatus === "Success") && isWebhookCalled > 0) {
        actionButton += `<a class="dropdown-item d-flex align-items-center mt-2 mb-2" href="#!"><div class="ml-2" onclick="PayoutWebhook('${payoutId}')"><i class="mdi mdi-rotate-left"></i> <span>Resend Webhook</span></div></a>`;
    }
    // if((payoutStatus !== "Initialized" || payoutStatus !== "Processing")) {
    //     actionButton += `<a class="dropdown-item d-flex align-items-center mt-2 mb-2" href="#!"><div class="ml-2" onclick="getPayoutPgResponse('${payoutId}')"><i class="mdi mdi-rotate-left"></i> <span>View Response</span></div></a>`;
    // }
    actionButton += `<a class="dropdown-item d-flex align-items-center mt-2 mb-2" href="#!"><div class="ml-2" onclick="cancelPayout('${payoutId}')"><i class="mdi mdi-rotate-left"></i> <span>Set Payout To Failed</span></div></a>`;
    return actionButton;

}



function getPayoutPgResponse(payoutId) {
    let paytmIdPostData = {
        payout_id:payoutId,
    }
    FsHelper.blockUi($("#payoutpage"));
    FsClient.post("/payout/byId", paytmIdPostData)
        .then((response) => {
            if(response.data.pg_res) {
                $.dialog({
                    columnClass: 'l',
                    title: payoutId + ' PG Response',
                    content: DzpJsonViewer(JSON.parse(response.data.pg_res), true),
                });
                console.log(JSON.parse(response.data.pg_res));
            } else {
                toastr.error("PG Response Not Available", "Error", toastOption);
            }
        })
        .catch(error => {
            console.log(error);
        })
        .finally(() => {
            FsHelper.unblockUi($("#payoutpage"));
        });
}

$("#pg_name").on("change", () => {
    const txnFormData = getFormData($("#PayoutForm"));
    if (txnFormData.pg_name === "ALL"){
        $("#meta_id").hide();
    }
    if (txnFormData.pg_name){
        if (txnFormData.pg_name !=='ALL'){
            $("#meta_id").html('');
            // $("#Pgtitle").html(txnFormData.pg_name +' '+ 'Label')
            FsHelper.blockUi($("#payoutpage"));
            FsClient.post('/payout/get/pgMeta',txnFormData)
                .then(response => {
                    let data = ' <option value="All">ALL</option>';
                    response.data.forEach((item, index) => {
                        data += `
                            <option value="${item.account_id}">${item.label}</option>
                     `;
                    });
                    $("#meta_id").html(data);
                })
                .catch(error => {
                    console.log(error);
                })
                .finally(() => {
                    FsHelper.unblockUi($("#payoutpage"));
                });
            $("#payoutPG").show();
        }
    }
});


function generateReport() {
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

let autoRefreshInterval = null;
let refreshCnt = 0;

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
            (new PayoutData()).getPayout();
            refreshCnt++;
            console.log(`Transaction Refresh: ${refreshCnt}`)
        }, 5000);
    }
}

$("#updateManualPayoutForm").on("submit", () => {
    const FormData = getFormData($("#updateManualPayoutForm"));
    console.log(FormData);
    FsHelper.blockUi($("#updateManualPayoutForm"));
    FsClient.post("/payout/status/update", FormData)
        .then(res => {
            toastr.success(res.message, "success", options);
            $("#updateManualPayoutForm")[0].reset();
            $("#updateManualPayout").modal('hide');
            (new PayoutData()).getPayout();
        })
        .catch(err => {
            toastr.error(err.responseJSON.message, "Error", options);
            console.log(err);
        })
        .finally(() => {
            FsHelper.unblockUi($("#updateManualPayoutForm"));
        });
});


$('.success-daterange').daterangepicker({
    "autoApply": true,
    "autoUpdateInput": false,
    "locale": {
        "format": "DD/MM/YYYY",
        "separator": " - ",
        "applyLabel": "Apply",
        "cancelLabel": "Cancel",
        "fromLabel": "From",
        "toLabel": "To",
        "customRangeLabel": "Custom",
    },
    "linkedCalendars": false,
    "showCustomRangeLabel": false,
    "startDate": moment(),
    "endDate": moment(),
    "maxDate": moment(),
    "maxSpan": {
        "days": 30
    },
}, function(start, end, label,item) {
    $('input[name="daterange1"]').val(start.format('DD/MM/YYYY') + ' - ' + end.format('DD/MM/YYYY'));
});
