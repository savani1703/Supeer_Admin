let txnPostData = {
    filter_data: {
        customer_id:getHid()
    },
    page_no: 1,
    limit: 50,
};

let txnPaginateData = {
    link_limit: 2,
    from: 2,
    to: 2,
    total: null,
    is_last: null,
    current_item_count: null,
    current_page: null,
    last_page: null,
};

(new Digipay()).getTxn();
(new Digipay()).getTxnSummary();
DzpDatePickerService.init();

function Digipay() {
    this.getTxn = (isLoading = true) => {
        if(isLoading) {
            FsHelper.blockUi($("#transaction_page"));
        }
        FsClient.post("/transaction", txnPostData).then(this.handleResponse).catch(this.handleError);
    }
    this.getTxnSummary = (isLoading = true) => {
        /*if(isLoading) {
            FsHelper.blockUi($("#transaction_page"));
        }*/
        FsClient.post("/transaction/summary", txnPostData).then(this.handleResponseForSummary).catch(this.handleErrorForSummary);
    }
    this.getCustomerDetails = (browser_id) => {
        FsHelper.blockUi($(".modal-body"));
        let txnIdPostData = {
            browser_id : browser_id,
        }
        FsHelper.blockUi($("#transaction_page"));
        FsClient.post("/transaction/byBrowserId",txnIdPostData).then(this.ResponseModaltxn).catch(this.setErrorHtml2);
    }

    this.ResponseModaltxn = (data) => {
        FsHelper.unblockUi($("#transaction_page"));
        if (data.status) {
            let onlyData = data.data;
            if(onlyData && onlyData.length > 0) {
                let htmlData = "";
                onlyData.forEach((item, index) => {
                    $("#BrowserId").html(item.browser_id);
                    htmlData += `<tr">
                                     <td>
                                        <span class="d-block  mb-1">${item.browser_id ? item.browser_id : ""}</span>
                                    </td>
                                     <td>
                                        <span class="d-block  mb-1">${item.customer_id ? item.customer_id : ""}</span>
                                    </td>
                                    <td>
                                        <span class="d-block  mb-1">${item.merchant_details.merchant_name ? item.merchant_details.merchant_name : ""}</span>
                                    </td>
                                    <td>
                                        <span class="d-block  mb-1">${item.customer_email ? item.customer_email : ""}</span>
                                    </td>
                                    <td>
                                        <span class="d-block  mb-1">${item.customer_mobile ? item.customer_mobile : ""}</span>
                                    </td>
                                    <td>
                                        <span class="d-block  mb-1">${item.last_success_date ? item.last_success_date : ""}</span>
                                    </td>
                                <tr>`;
                });
                $('.preLoader').hide();
                $("#customerData").html(htmlData);
            }else {
                this.setErrorHtml2();
            }
        }
    }

    this.gettxnbyid = (transaction_id) => {
        FsHelper.blockUi($(".modal-body"));
        let txnIdPostData = {
            transaction_id:transaction_id,
        }
        FsHelper.blockUi($("#transaction_page"));
        FsClient.post("/transaction/byId",txnIdPostData).then(this.Responsetxn).catch(this.setErrorHtml2);
    }

    this.Responsetxn = (data) => {
        FsHelper.unblockUi($("#transaction_page"));
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
                        <tr><td><span class="text-muted"> show page              </span></td><td class="pt-2 pl-4"> ${data.data.showing_data ? data.data.showing_data : "-"}</td></tr>
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

    this.handleResponse = (data) => {
        FsHelper.unblockUi($("#transaction_page"));
        if(data.status) {
            txnPaginateData.current_page = data.current_page;
            txnPaginateData.last_page = data.last_page;
            txnPaginateData.is_last_page = data.is_last_page;
            txnPaginateData.total = data.total_item;
            txnPaginateData.current_item_count = data.current_item_count;
            this.setTxnHtmlData(data.data);
            $('#pagination').show();
            (new Digipay()).getTxnSummary();
        } else {
            this.setErrorHtml();
        }
    }

    this.handleError = (error) => {
        FsHelper.unblockUi($("#transaction_page"));
        if (error.status === 401){
            FsHelper.unauthorizedUserPage("unauthorized_user");
            $("#transactionDetail").html('');
        }else {
            this.setErrorHtml();
        }
    }

    this.handleResponseForSummary = (data) => {
        if(data.status) {
            this.setTxnSummaryHtml(data.summary);
        }
    }

    this.handleErrorForSummary = (error) => {
        FsHelper.unblockUi($("#transaction_page"));
        if (error.status === 401){
            FsHelper.unauthorizedUserPage("unauthorized_user");
        }else {
            this.setTxnSummaryHtml(error.responseJSON.summary);
        }
    }

    this.setTxnHtmlData = (data) => {
        if(data && data.length > 0) {
            let htmlData = "";
            data.forEach((item, index) => {
                htmlData += `<tr class="${item.is_blocked === '1' ? 'bg-light-danger' : ''}">
                                <td>
                                     <div class="d-block align-items-center flex-wrap text-nowrap">
                                        <span>
                                             <div class="dropdown mr-2">
                                            <button class="btn p-0" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-settings"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                                            </button>
                                            <div class="dropdown-menu mb-5" aria-labelledby="dropdownMenuButton" style="position: absolute">
                                                <h6 class="dropdown-header">${item.transaction_id}</h6>
                                                ${(new Digipay()).data(item.transaction_id, item.payment_status, item.is_webhook_call, item.payment_amount,item.pg_name,item.meta_id)}
                                            </div>
                                          </div>
                                      </span>
                                    </div>
                                </td>
                                <td>
                                 <span class="d-block  mb-1">Create:  ${item.created_at_ist ? item.created_at_ist : ""}</span>
                                 <span class="d-block  mb-1"> Update:  ${item.updated_at_ist ? item.updated_at_ist : ""}</span>
                                 <span class="d-block  mb-1"> Success:  ${item.success_at_ist ? item.success_at_ist : ""}</span>
                                 <span class="d-block "> Cust.ID:${item.customer_id ? item.customer_id : ""}</span>
                                 <span class="d-block "> Cust.Level: <span class="badge badge-primary">${item.user_security_level ? item.user_security_level : "-"}</span>  </span>
                                 <span class="d-block "> Cust Entry Date: ${item.user_created_at ? item.user_created_at : "-"}  </span>
                                 ${item.app_name ? `<span class="d-block "><small><strong>APP: </strong>${item.app_name ? item.app_name : ""}</small></span>` : ''}
                                 ${setCustomerIdCount(item.browser_id , item.total_customer_id)}
                                </td>
                                <td>
                                     <span class="d-block position-relative mb-1">
                                     ${getBlockingIndicator(item.is_blocked, item.reason)}

                                     ${getIsRisky(item.is_risky)}
                                      <a onclick="(new Digipay()).gettxnbyid('${item.transaction_id}');" href="" data-toggle="modal" data-target="#txnModal" >${item.transaction_id ? item.transaction_id : ""} </a>
                                    </span>
                                    <span class="d-block "><strong>PG TYPE</strong>: ${item.pg_type ? item.pg_type : "-"}
                                    ${item.temp_bank_utr ? `<span class="d-block "><strong>Temp UTR: </strong>${item.temp_bank_utr ? item.temp_bank_utr : ""}</span>` : ''}
                                    ${item.is_blocked && item.cust_state && item.cust_country ? getBlockingIndicatorForState(item.is_blocked, item.cust_state, item.cust_country, item.cust_city) : ''}
                                    ${getBlockingIndicatorForDataBlocked(item.is_block_by_data)}
                                    ${item.payment_data ? `<span class="d-block "><strong>Enter Upi : </strong>${item.payment_data ? item.payment_data : ""}</span>` : ''}
                                    ${item.success_upi_id ? `<span class="d-block "><strong>Success Upi : </strong>${item.success_upi_id ? item.success_upi_id : ""}</span>` : ''}
                                </td>

                                <td>
                                    <span class="d-block  mb-1">Order : ${item.merchant_order_id ? item.merchant_order_id : ""}</span>

                                     <span class="d-block mb-1">UTR : ${item.bank_rrn ? item.bank_rrn : ""}</span>

                                     <span class="d-block  mb-1">PG id : ${item.pg_order_id ? item.pg_order_id : ""}</span>

                                     <span class="d-block  mb-1">PG ref : ${item.pg_ref_id ? item.pg_ref_id : ""}</span>
                                     <span class="d-block ">MMPID : ${item.meta_merchant_pay_id ? item.meta_merchant_pay_id : ""}</span>
                                </td>

                                <td>
                                    <span class="d-block  mb-1">${item.merchant_details ? item.merchant_details.merchant_name : ""}</span>
                                    <span class="d-block "><small>${item.merchant_id ? item.merchant_id : ""}</small></span>
                                </td>

                                <td>
                                    <span class="d-block ">₹ ${item.payment_amount ? item.payment_amount : "0"}</span>
                                     <span class="d-block ">Asso.Fee:  ${item.associate_fees ? item.associate_fees : "0"}</span>
                                     <span class="d-block ">Pg Fees: ${item.pg_fees ? item.pg_fees : "0"}</span>
                                </td>

                                <td>
                                    <span class="d-block ">₹ ${item.payable_amount ? item.payable_amount : "0"}</span>
                                </td>
                                <td>
                                    <span class="mr-2  badge ${this.getStatusClass(item.payment_status)} ">${item.payment_status}</span>
                                </td>
                                  <td>
                                    <span class="d-block ">${item.payment_method ? item.payment_method : ""}</span>
                                </td>

                                <td>
                                    <span class="d-block ">${item.pg_name ? item.pg_name : ""} (${item.meta_id ? item.meta_id : ""})</span>
                                    ${item.pg_label ? `<span class="d-block mb-1"><strong>Label:</strong> ${item.pg_label ? item.pg_label : ""}</span>` : ''}
                                    ${item.account_number ? `<span class="d-block mb-1"><strong>Account:</strong> ${item.account_number ? item.account_number : ""}</span>` : ''}
                                    ${item.upi_id ? `<span class="d-block mb-1"><strong>UPI:</strong> ${item.upi_id ? item.upi_id : ""}</span>` : ''}
                                    ${item.bank_name ? `<span class="d-block mb-1"><strong>Bank:</strong> ${item.bank_name ? item.bank_name : ""}</span>` : ''}
                                    ${item.ifsc_code ? `<span class="d-block mb-1"><strong>IFSC:</strong> ${item.ifsc_code ? item.ifsc_code : ""}</span>` : ''}
                                </td>
                        </tr>`;

            });
            $('.preLoader').hide();
            $("#transactionData").html(htmlData);
            setPaginateButton("txn-logs-page-change-event", txnPaginateData, txnPostData);

        } else {

            this.setErrorHtml();
        }
    }

    this.setTxnSummaryHtml = (data) => {
        console.log(data);
        if(data) {
            $.each(data, (index, item) => {
                $("#__"+index).text(parseFloat(item).toFixed(2));
            })
        }
    }


    this.data = (transaction, payment_status, is_webhook_call, payment_amount, pg_name,meta_id) => {
        let button='';
        if ((payment_status ==='Success' ||  payment_status === 'Failed') && parseInt(is_webhook_call) >0){
            button += `<a class="dropdown-item d-flex align-items-center" href="#!">
                 <div class="ml-2" onclick="webhook('${transaction}')">
                 <i class="mdi mdi-rotate-left"></i> <span>Resend Webhook</span></div></a>`
        }
        if (pg_name ==='UPIPAY' && payment_status !=='Success'){
            button += `<a class="dropdown-item d-flex align-items-center" href="#!">
                 <div class="ml-2" onclick="tempUtr('${transaction}')">
                 <i class="mdi mdi-rotate-left"></i> <span>Update New TempUtr</span></div></a>`
        }

        button += `<a class="dropdown-item d-flex align-items-center" href="#!">
                 <div class="ml-2" onclick="blockCustomerInfo('${transaction}')">
                 <i class="mdi mdi-block-helper"></i> <span>Block Customer</span></div></a>`;

        if(payment_status==='Success'){
            button += `<a class="dropdown-item d-flex align-items-center mt-2" href="#!">
                <div class="ml-2" onclick="refund('${transaction}','${payment_amount}')">
                <i class="mdi mdi-reload"></i>Refund </div></a>`
        }
        if(meta_id===null && payment_status==='Success'){
            button += `<a class="dropdown-item d-flex align-items-center mt-2" href="#!">
                <div class="ml-2" onclick="deleteTransaction('${transaction}','${payment_amount}')">
                <i class="mdi mdi-reload"></i>Delete</div></a>`
            button += `<a class="dropdown-item d-flex align-items-center mt-2" href="#!">
                <div class="ml-2" onclick="RemoveFees('${transaction}','${payment_amount}')">
                <i class="mdi mdi-reload"></i>Remove Fees</div></a>`
        }

        if(button.length < 1) {
            button +='<p class="ml-1 mb-1"> Action not available </p>';
        }
        return button;
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

    this.setErrorHtml = () => {

        $('.preLoader').hide();
        $('#pagination').hide();
        FsHelper.unblockUi($("#transaction_page"));
        $("#transactionData").html(`
            <tr>
                <td colspan="16">
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
        FsHelper.unblockUi($("#transaction_page"));
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

$("#payinPG").hide();


$("#PGName").on("change", () => {
    const txnFormData = getFormData($("#txnFilerForm"));
    if (txnFormData.PG === "ALL"){
        $("#payinPG").hide();
    }
    if (txnFormData.PG){
        if (txnFormData.PG !=='ALL'){
            $("#PayinPg").html('');
            $("#Pgtitle").html(txnFormData.PG +' '+ 'Label')
            FsHelper.blockUi($("#transaction_page"));
            FsClient.post('/transaction/pgPayin',txnFormData)
                .then(response => {
                    let data = ' <option value="All">ALL</option>';
                    response.data.forEach((item, index) => {
                        data += `
                            <option>${item.label}</option>
                     `;
                    });
                    $("#PayinPg").html(data);
                })
                .catch(error => {
                    console.log(error);
                })
                .finally(() => {
                    FsHelper.unblockUi($("#transaction_page"));
                });
            $("#payinPG").show();
        }
    }
});

$("#txnFilerForm").on("submit", () => {
    const txnFormData = getFormData($("#txnFilerForm"));
    txnPostData.filter_data = {
        searchdata:null,
        bank_rrn:null,
        temp_bank_utr:null,
        cust_state:null,
        customer_id:getHid(),
        merchant_id:null,
        payment_amount:null,
        payment_method:null,
        udf1:null,
        udf2:null,
        udf3:null,
        udf4:null,
        udf5:null,
        status:null,
        min_amount:null,
        max_amount:null,
        pg_name:null,
        meta_id:null,
        start_date: null,
        end_date: null,
        success_start_date:null,
        success_end_date:null,
    };
    txnPostData.filter_data[txnFormData.txtFilterKey] = txnFormData.txtFilterValue;
    txnPostData.filter_data.status = txnFormData.txtStatus;

    if(txnFormData.pg_name !== "All") {
        txnPostData.filter_data.pg_name = txnFormData.pg_name;
    } else {
        txnPostData.filter_data.pg_name = null;
    }

    if(txnFormData.merchant_id !== "All") {
        txnPostData.filter_data.merchant_id = txnFormData.merchant_id;
    }
    if(txnFormData.cust_state !== "All") {
        txnPostData.filter_data.cust_state = txnFormData.cust_state;
    }

    if(txnFormData.meta_id !== "All") {
        txnPostData.filter_data.meta_id = txnFormData.meta_id;
    }

    if(txnFormData.blockedUser !== "All") {
        txnPostData.filter_data.blockedUser = txnFormData.blockedUser;
    }

    if(txnFormData.showpage !== "All") {
        txnPostData.filter_data.showpage = txnFormData.showpage;
    }

    txnPostData.limit = txnFormData.txtLimit;
    txnPostData.filter_data.payment_method = txnFormData.txtMethod;
    txnPostData.filter_data.min_amount = txnFormData.min_amount;
    txnPostData.filter_data.max_amount = txnFormData.max_amount;
    txnPostData.filter_data.onlytemputr = txnFormData.idTempUtrOnly;
    txnPostData.filter_data.lateSuccess = txnFormData.lateSuccess;
    txnPostData.page_no = 1;
    if(txnFormData.daterange) {
        let splitDate = txnFormData.daterange.split(/-/);
        txnPostData.filter_data.start_date = moment(splitDate[0], 'DD/MM/YYYY').format('YYYY-MM-DD 00:00:00');
        txnPostData.filter_data.end_date = moment(splitDate[1], 'DD/MM/YYYY').format('YYYY-MM-DD 23:59:59');
    }
    if(txnFormData.player_register_date_range) {
        let splitDate = txnFormData.player_register_date_range.split(/-/);
        txnPostData.filter_data.player_register_start_date = moment(splitDate[0], 'DD/MM/YYYY').format('YYYY-MM-DD 00:00:00');
        txnPostData.filter_data.player_register_end_date = moment(splitDate[1], 'DD/MM/YYYY').format('YYYY-MM-DD 23:59:59');
    }
    if(txnFormData.daterange1) {
        let splitDate = txnFormData.daterange1.split(/-/);
        txnPostData.filter_data.success_start_date = moment(splitDate[0], 'DD/MM/YYYY').format('YYYY-MM-DD 00:00:00');
        txnPostData.filter_data.success_end_date = moment(splitDate[1], 'DD/MM/YYYY').format('YYYY-MM-DD 23:59:59');
    }
    (new Digipay()).getTxn();
});

EventListener.dispatch.on("txn-logs-page-change-event", (event, callback) => {
    txnPostData.page_no = callback.page_number;
    (new Digipay()).getTxn();
});

function webhook(transaction_id) {
    var myModal =  new jBox('Confirm', {
        confirmButton: 'YES',
        cancelButton: 'No',
        content: 'Are You Sure Do you want to Resend Webhook ?',
        confirm: function () {
            FsHelper.blockUi($("#transaction_page"));
            let postData = {
                transaction_id: transaction_id,
            }
            FsClient.post("/transaction/resend/webhook", postData).then(
                Response => {
                    toastr.success(Response.message,"success",toastOption);
                    FsHelper.unblockUi($("#transaction_page"));
                    (new Digipay()).getTxn();
                }
            ).catch(Error => {
                toastr.error(Error.responseJSON.message,"error",toastOption);
                FsHelper.unblockUi($("#transaction_page"));
                (new Digipay()).getTxn();
            });
        },
        cancel : function (){

        }
    });
    myModal.open();
}

function deleteTransaction(transaction_id,payment_amount) {
    var myModal =  new jBox('Confirm', {
        confirmButton: 'YES',
        cancelButton: 'No',
        content: 'Are You Sure Do you want to Delete Transaction ?',
        confirm: function () {
            FsHelper.blockUi($("#transaction_page"));
            let postData = {
                transaction_id: transaction_id,
                payment_amount: payment_amount
            }
            FsClient.post("/transaction/delete/manual", postData).then(
                Response => {
                    toastr.success(Response.message,"success",toastOption);
                    FsHelper.unblockUi($("#transaction_page"));
                    (new Digipay()).getTxn();
                }
            ).catch(Error => {
                toastr.error(Error.responseJSON.message,"error",toastOption);
                FsHelper.unblockUi($("#transaction_page"));
                (new Digipay()).getTxn();
            });
        },
        cancel : function (){

        }
    });
    myModal.open();
}

function RemoveFees(transaction_id,payment_amount) {
    var myModal =  new jBox('Confirm', {
        confirmButton: 'YES',
        cancelButton: 'No',
        content: 'Are You Sure Do you want to Remove Fees ?',
        confirm: function () {
            FsHelper.blockUi($("#transaction_page"));
            let postData = {
                transaction_id: transaction_id,
                payment_amount: payment_amount
            }
            FsClient.post("/transaction/removefees/manual", postData).then(
                Response => {
                    toastr.success(Response.message,"success",toastOption);
                    FsHelper.unblockUi($("#transaction_page"));
                    (new Digipay()).getTxn();
                }
            ).catch(Error => {
                toastr.error(Error.responseJSON.message,"error",toastOption);
                FsHelper.unblockUi($("#transaction_page"));
                (new Digipay()).getTxn();
            });
        },
        cancel : function (){

        }
    });
    myModal.open();
}

function tempUtr(transaction_id) {
    $.confirm({
        title: 'Set Temp Utr!',
        content: '' +
            '<form action="" class="formName" id="utrForm">' +
            '<div class="form-group">' +
            '<label>Enter Temp Utr</label>' +
            '<input type="text" placeholder="Temp Utr"  name="temp_utr" class="name form-control" required />' +
            '</div>' +
            '</form>',
        buttons: {
            formSubmit: {
                text: 'Submit',
                btnClass: 'btn-blue',
                action: function () {
                    var name = this.$content.find('.name').val();
                    if(!name){
                        $.alert('provide a valid Utr');
                        return false;
                    }
                    FsHelper.blockUi($("#transaction_page"));
                    const tempUtr = getFormData($("#utrForm"));
                    let postData = {
                        transaction_id: transaction_id,
                        temp_utr: tempUtr.temp_utr,
                    }
                    FsClient.post("/transaction/update/tempUtr", postData).then(
                        Response => {
                            toastr.success(Response.message,"success",toastOption);
                            FsHelper.unblockUi($("#transaction_page"));
                            (new Digipay()).getTxn();
                        }
                    ).catch(Error => {
                        toastr.error(Error.responseJSON.message,"error",toastOption);
                        FsHelper.unblockUi($("#transaction_page"));
                        (new Digipay()).getTxn();

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
    FsHelper.unblockUi($("#transaction_page"));
}

function resetTransaction(){
    txnPostData.filter_data = {
        transaction_id:null,
        merchant_order_id:null,
        bank_rrn:null,
        customer_id:getHid(),
        payment_amount:null,
        cust_state:null,
        pg_name:null,
        meta_id:null,
        udf1:null,
        udf2:null,
        udf3:null,
        udf4:null,
        udf5:null,
        status:null,
        PG:null,
        success_start_date:null,
        success_end_date:null,
    }
    txnPostData.page_no=1;
    txnPostData.limit=50;

    $('#txnFilerForm')[0].reset();
    $("#payinPG").hide();
    DzpDatePickerService.init();
    (new Digipay()).getTxn();
}

function refund(transaction_id,amount1){
    let PostData={
        transaction_id:transaction_id,
        refund_amount:null,
        remark:null,
    }

    $.confirm({
        title: 'Refund!',
        content: '' +
            '<form action="" class="formName">' +
            '<div class="form-group">' +
            '<label>Select Refund Type</label>' +
            '<select id="dropDownId"  onchange="getval(this)">' +
            '<option value="Full">Full Refund</option>' +
            '<option value="Partial">Partial Refund</option>' +
            '</select>' +
            '<br>'+
            '<input type="text" placeholder="Remark"  id="remark" class="remark form-control" required />' +
            '<br>'+
            '</div>' +
            '<div id="data"> ' +
            '</div>'
            +
            '</form>',
        type: 'blue',
        buttons: {
            formSubmit: {
                text: 'Submit',
                btnClass: 'btn-blue',
                action: function () {
                    var amount = this.$content.find('.name').val();
                    var value = $('#dropDownId').val();
                    var remark = $('#remark').val();

                    if(!remark){
                        $.alert('Fill Out  Remark Field');
                        return false;
                    }

                    if (value==="Full"){
                        PostData.refund_amount = amount1
                        PostData.remark = remark
                        getresponse(PostData);
                        return true;
                    }

                    if(!amount){
                        $.alert('provide a valid Amount');
                        return false;
                    }
                    if(amount <= amount1){
                        PostData.refund_amount = amount;
                        PostData.remark = remark;
                        getresponse(PostData);
                    }
                    else {
                        $.alert('provide a valid Amount');
                        return false;
                    }

                }
            },
            cancel: function () {
                //close
            },
        },

        onContentReady: function () {
            var jc = this;
            this.$content.find('form').on('submit', function (e) {
                e.preventDefault();
                jc.$$formSubmit.trigger('click');
            });
        },

    });

}


function  getval(sel){
    $('#data').empty();
    if (sel.value==="Partial"){
        $('#data').show();
        let test =  `<input type="number" placeholder="Enter Amount" class="name form-control" required />`
        $('#data').html(test);
    }
    else{
        $('#data').hide();
    }
}

function getresponse(PostData){
    FsClient.post("/transaction/refund",PostData).then(
        Response => {
            toastr.success(Response.message,"success",toastOption);
            (new Digipay()).getTxn();
        }
    ).catch(Error =>{
        console.log(Error)
        toastr.error(Error.responseJSON.message,"error",toastOption);
        (new Digipay()).getTxn();
    });
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
            (new Digipay()).getTxn();
            refreshCnt++;
            console.log(`Transaction Refresh: ${refreshCnt}`)
        }, 10000);
    }
}


function blockCustomerInfo(txnId) {
    var myModal =  new jBox('Confirm', {
        confirmButton: 'YES',
        cancelButton: 'No',
        content: 'Are You Sure Do you want to Block Customer?',
        confirm: function () {
            FsHelper.blockUi($("#transaction_page"));
            const postData = {
                transaction_id: txnId
            };
            FsClient.post("/transaction/block/customer", postData).then(
                response => {
                    toastr.success(response.message,"success",toastOption);
                }
            ).catch(error => {
                toastr.error(error.responseJSON.message,"error",toastOption);
            }).finally(function (){
                FsHelper.unblockUi($("#transaction_page"));
            });
        },
        cancel : function (){

        }
    });
    myModal.open();

}

function generateReport(){
    FsClient.post("/support/GenerateReport", txnPostData).then(
        response => {
            toastr.success(response.message,"success",toastOption);
        }
    ).catch(error => {
        toastr.error(error.responseJSON.message,"error",toastOption);
    });
}
$.fn.sort_select_box = function(){
    // Get options from select box
    var my_options = $("#" + this.attr('id') + ' option');
    // sort alphabetically
    my_options.sort(function(a,b) {
        if (a.text > b.text) return 1;
        else if (a.text < b.text) return -1;
        else return 0
    })
    //replace with sorted my_options;
    $(this).empty().append( my_options );

    // clearing any selections
    $("#"+this.attr('id')+" option").attr('selected', false);
}
$("#pgList").change((e) => {
    FsHelper.blockUi($("#transactionPg"));
    const url = "/payment-gateway/GetPaymentMetaLabelList/PAYIN/" + $("#pgList").val();
    let pgAccountOptions = `<option value="All" selected>All</option>`;
    FsClient.post(url, "").then(
        response => {
            if(response.data) {
                response.data.forEach((item) => {
                    pgAccountOptions += `<option value="${item.account_id}">${item.label}</option>`;
                });
            }
            $("#transactionPg #meta_id").html(pgAccountOptions);
            $("#transactionPg #meta_id").sort_select_box()
        }
    ).catch(error => {
        $("#transactionPg #meta_id").html(pgAccountOptions);
    })
        .finally(() => {
            FsHelper.unblockUi($("#transactionPg"));
        });
});

function getBlockingIndicator(isBlock, reason, state) {
    const isBlockedByVpn = isBlock && reason === "Transaction Blocked By VPN";
    const isBlockedByCustomer = isBlock && reason === "Transaction Blocked By Fraud System";
    if(isBlockedByVpn) {
        return `<div class="led-box" title="VPN Detect"><div class="led-red"></div></div>`;
    }
    if(isBlockedByCustomer) {
        return `<div class="led-box" title="Transaction Blocked By Fraud System"><div class="led-warn"></div></div>`;
    }
    if(isBlock && state){
        var blockedStateArray = ["jharkhand","rajasthan","haryana","uttarakhand","chhattisgarh","bihar","karnataka"];
        const lowerState = state.toLowerCase();
        if(blockedStateArray.indexOf(lowerState) > -1){
            return `<div><span class="badge badge-danger">Danger</span></div>`;
        }
    }
    return "";
}
function getBlockingIndicatorForDataBlocked(is_block_by_data) {
    if(is_block_by_data){
        return `<div><span class="badge badge-danger" style="margin-top: 2px;">Manual Blocked</span></div>`;
    }
    return "";
}

function setCustomerIdCount(browser_id, total_customer_id) {
    if(total_customer_id > 1){
        return ` HID :  <a onclick="(new Digipay()).getCustomerDetails('${browser_id}');" href="" data-toggle="modal" data-target="#customerDetails" >${browser_id ? browser_id : ""}</a> <span class="badge badge-danger" style="margin-top: 2px;margin-left: 8px;">`+total_customer_id+`</span>`;
    }
    return `<span class="d-block "> HID :  ${browser_id ? browser_id : ""} </span>`;
}

function custBlockByHid() {
    var myModal =  new jBox('Confirm', {
        confirmButton: 'YES',
        cancelButton: 'No',
        content: 'Are You Sure Do you want to Block This Customer ?',
        confirm: function () {
            let postData = {
                browser_id:$("#BrowserId").text()
            }
            FsClient.post("/transaction/block/customer/byHId", postData).then(
                Response => {
                    toastr.success(Response.message,"success",toastOption);
                    FsHelper.unblockUi($("#transaction_page"));
                    (new Digipay()).getTxn();
                }
            ).catch(Error => {
                toastr.error(Error.responseJSON.message,"error",toastOption);
                FsHelper.unblockUi($("#transaction_page"));
                (new Digipay()).getTxn();
            });
        },
        cancel : function (){

        }
    });
    myModal.open();
}



function getBlockingIndicatorForState(isBlock, state, country, city) {
    if(isBlock && state && country === 'IN'){
        var blockedStateArray = ["jharkhand","rajasthan","haryana","uttarakhand","chhattisgarh","bihar","gujarat","maharashtra","karnataka"];
        const lowerState = state.toLowerCase();
        if(blockedStateArray.indexOf(lowerState) > -1){
            if(lowerState === 'maharashtra'){
            }else{
                return `<div><span class="badge badge-danger" style="margin-top: 2px;">`+state+`</span></div>`;
            }
        }

    }
    if(country !== 'IN'){
        return `<div><span class="badge badge-danger" style="margin-top: 2px;">`+country+`</span></div>`;
    }
    return "";
}
function getIsRisky(is_Risky) {
    if(is_Risky) {
        return `<div class="led-box" title="Risky Detected"><div class="led-red"></div></div>`;
    }
    return "";
}


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

function getHid() {
    return (window.location.pathname).split("/")[2];
}
