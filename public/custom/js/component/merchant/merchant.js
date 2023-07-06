let PostData = {
    filter_data: {
        start_date:null,
        end_date:null,
    },
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



(new MerchantData()).getMerchant();
DzpDatePickerService.init();
function MerchantData() {
    this.getMerchant  = () => {
        FsHelper.blockUi($("#Merchant_page"));
        FsClient.post("/merchant", PostData).then(this.handleResponse).catch(this.handleError);
    }

    this.handleResponse = (data) => {
        FsHelper.unblockUi($("#Merchant_page"));
        if(data.status) {
            PaginateData.current_page = data.current_page;
            PaginateData.last_page = data.last_page;
            PaginateData.is_last_page = data.is_last_page;
            PaginateData.total = data.total_item;
            PaginateData.current_item_count = data.current_item_count;
            this.setMerchantHtmlData (data.data, data.config);
            $('#pagination').show();
        } else {
            this.setErrorHtml();
        }
    }

    this.handleError = (error) => {
        if (error.status === 401){
            FsHelper.unauthorizedUserPage("unauthorized_user");
            $("#supportLogsDetail").html('');
        }else {
            console.log(error)
            this.setErrorHtml();
        }
    }
    this.setMerchantHtmlData  = (data, configs) => {
        if(data && data.length > 0) {
            let htmlData = "";

            data.forEach((item, index) => {
                htmlData += `<tr>
                            <td>
                                <span><img src="${item.checkout_theme_url ? item.checkout_theme_url  : '/assets/images/no-image-set.png'}" alt=""></span>
                                <span class="text-primary font-weight-bold"> ${item.merchant_name ? item.merchant_name :""}</span> (${item.merchant_id ? item.merchant_id :""})
                            </td>
                            <td>${item.account_status ? item.account_status :"New"}</td>
                            <td>₹${item.min_transaction_limit ? item.min_transaction_limit :"0"}</td>
                            <td>₹${item.max_transaction_limit ? item.max_transaction_limit :"0"}</td>
                            <td>
                                ${
                                    checkConfigKey("is_payin_meta_allowed", configs) ?
                                        `<a class="btn btn-primary btn-sm mr-1" href="/payin-meta/${item.merchant_id}"
                                           target="_blank">PAYIN</a>` : "-"
                                }
                                ${
                                    checkConfigKey("is_payout_meta_allowed", configs) ?
                                        `<a class="btn btn-primary btn-sm mr-1" href="/payout-meta/${item.merchant_id}"
                                           target="_blank">PAYOUT</a>` : "-"
                                }
                            </td>

                            <td>
                            ${
                                checkConfigKey("account_status", configs) ?
                                `<a href="#" class="merchant-editable"
                                   data-type="select"
                                   data-value="${item.account_status ? item.account_status : "New"}"
                                   data-pk="${item.merchant_id}"
                                   data-url="/merchant/action/UpdateAccountStatus"
                                   data-source="[{value: 'New', text: 'New'}, {value: 'Hold', text: 'Hold'}, {value: 'Approved', text: 'Approved'}, {value: 'Suspended', text: 'Suspended'}]"
                                   >${item.account_status ? item.account_status : "New"}</a>` :
                                    `<span>${item.account_status ? item.account_status : "New"}</span>`
                            }
                            </td>

                            <td>
                            ${
                                checkConfigKey("min_transaction_limit", configs) ?
                                    `<a href="#" class="merchant-editable"
                                              data-type="text"
                                              data-value="${item.min_transaction_limit ? item.min_transaction_limit : 0}"
                                              data-pk="${item.merchant_id}"
                                              data-url="/merchant/action/updateMinLimit"
                                              >${item.min_transaction_limit ? item.min_transaction_limit : 0}</a>` :
                                    `<span>${item.min_transaction_limit ? item.min_transaction_limit : "0"}</span>`
                            }
                            </td>

                            <td>
                            ${
                                checkConfigKey("max_transaction_limit", configs) ?
                                    `<a href="#" class="merchant-editable"
                                       data-type="text"
                                       data-value="${item.max_transaction_limit ? item.max_transaction_limit : 0}"
                                       data-pk="${item.merchant_id}"
                                       data-url="/merchant/action/updateMaxLimit"
                                    >${item.max_transaction_limit ? item.max_transaction_limit : 0}</a>` :
                                    `<span>${item.max_transaction_limit ? item.max_transaction_limit : "0"}</span>`
                            }
                            </td>

                            <td>
                            ${
                                checkConfigKey("is_payin_enable", configs) ?
                                    `<a href="#" class="merchant-editable"
                                                   data-type="select"
                                                   data-value="${item.is_payin_enable ? " Yes" : " No"}"
                                                   data-source="[{value: '1',text: 'YES'},{value: '0',text: 'NO'},]"
                                                   data-pk="${item.merchant_id}"
                                                   data-url="/merchant/action/UpdateIsPayInEnable"
                                                >${item.is_payin_enable ? "Yes" : "No"}</a>` :
                                    `<span>${item.is_payin_enable ? "Yes" : "No"}</span>`
                            }
                            </td>

                            <td>
                            ${
                                checkConfigKey("is_payout_enable", configs) ?
                                    `<a href="#" class="merchant-editable"
                                       data-type="select"
                                       data-value="${item.is_payout_enable ? " Yes" : " No"}"
                                       data-source="[{value: '1',text: 'YES'},{value: '0',text: 'NO'},]"
                                       data-pk="${item.merchant_id}"
                                       data-url="/merchant/action/UpdateIsPayoutEnable"
                                    >${item.is_payout_enable ? "Yes" : "No"}</a>` :
                                    `<span>${item.is_payout_enable ? "Yes" : "No"}</span>`
                            }
                            </td>

                            <td>
                            ${
                                checkConfigKey("is_required_payment_failed_webhook", configs) ?
                                    `<a href="#" class="merchant-editable"
                                       data-type="select"
                                       data-value="${item.is_required_payment_failed_webhook ? " Yes" : " No"}"
                                       data-source="[{value: '1',text: 'YES'},{value: '0',text: 'NO'},]"
                                       data-url="/merchant/action/UpdateIsFailedWebhookRequired"
                                       data-pk="${item.merchant_id}">${item.is_required_payment_failed_webhook ? "Yes" : "No"}</a>` :
                                    `<span>${item.is_required_payment_failed_webhook ? "Yes" : "No"}</span>`
                            }
                            </td>

                            <td>
                            ${
                                checkConfigKey("is_enable_browser_check", configs) ?
                                    `<a href="#" class="merchant-editable"
                                       data-type="select"
                                       data-url="/merchant/action/UpdateIsEnableBrowserCheck"
                                       data-value="${item.is_enable_browser_check ? " Yes" : " No"}"
                                       data-pk="${item.merchant_id}">${item.is_enable_browser_check ? "Yes" : "No"}</a>` :
                                    `<span>${item.is_enable_browser_check ? "Yes" : "No"}</span>`
                            }
                            </td>

                            <td>
                            ${
                                checkConfigKey("is_balance_check_enable", configs) ?
                                    `<a href="#" class="merchant-editable"
                                       data-type="select"
                                       data-source="[{value: '1',text: 'YES'},{value: '0',text: 'NO'},]"
                                       data-url="/merchant/action/UpdateIsEnablePayoutBalanceCheck"
                                       data-value="${item.is_balance_check_enable ? " Yes" : " No"}"
                                       data-pk="${item.merchant_id}">${item.is_balance_check_enable ? "Yes" : "No"}</a>` :
                                    `<span>${item.is_balance_check_enable ? "Yes" : "No"}</span>`
                            }
                            </td>

                            <td>
                            ${
                                checkConfigKey("webhook_url", configs) ?
                                    `<a href="#" class="merchant-editable"
                                       data-type="text"
                                       data-url="/merchant/action/UpdatePayInWebhook"
                                       data-value="${item.webhook_url ? item.webhook_url : 'Not Set'}"
                                       data-pk="${item.merchant_id}">${item.webhook_url ? item.webhook_url : "Not Set"}</a>` :
                                    `<span>${item.webhook_url ? item.webhook_url : "No"}</span>`
                            }
                            </td>

                            <td>
                            ${
                                checkConfigKey("payout_webhook_url", configs) ?
                                    `<a href="#" class="merchant-editable"
                                       data-type="text"
                                       data-url="/merchant/action/UpdatePayoutWebhook"
                                       data-value="${item.payout_webhook_url ? item.payout_webhook_url : 'Not Set'}"
                                       data-pk="${item.merchant_id}">${item.payout_webhook_url ? item.payout_webhook_url : "Not Set"}</a>` :
                                    `<span>${item.payout_webhook_url ? item.payout_webhook_url : "Not Set"}</span>`
                            }
                            </td>

                            <td>
                            ${
                                checkConfigKey("is_dashboard_payout_enable", configs) ?
                                    `<a href="#" class="merchant-editable"
                                       data-type="select"
                                       data-source="[{value: '1',text: 'YES'},{value: '0',text: 'NO'},]"
                                       data-url="/merchant/action/UpdateIsDashboardPayoutEnable"
                                       data-value="${item.is_dashboard_payout_enable ? " Yes" : " No"}"
                                       data-pk="${item.merchant_id}">${item.is_dashboard_payout_enable === 1 ? "Yes" : "No"}</a>` :
                                    `<span>${item.is_dashboard_payout_enable ? "Yes" : "No"}</span>`
                            }
                            </td>

                            <td>
                            ${
                                checkConfigKey("pay_in_auto_fees", configs) ?
                                    `<a href="#" class="merchant-editable"
                                       data-type="text"
                                       data-url="/merchant/action/UpdatePayInAutoFees"
                                       data-value="${item.pay_in_auto_fees ? item.pay_in_auto_fees : '0'}"
                                       data-pk="${item.merchant_id}">${item.pay_in_auto_fees}</a>` :
                                    `<span>${item.pay_in_auto_fees ? item.pay_in_auto_fees : "0"}</span>`
                            }
                            </td>

                            <td>
                            ${
                                checkConfigKey("pay_in_manual_fees", configs) ?
                                    `<a href="#" class="merchant-editable"
                                       data-type="text"
                                       data-url="/merchant/action/UpdatePayInManualFees"
                                       data-value="${item.pay_in_manual_fees ? item.pay_in_manual_fees : '0'}"
                                       data-pk="${item.merchant_id}">${item.pay_in_manual_fees}</a>` :
                                    `<span>${item.pay_in_manual_fees ? item.pay_in_manual_fees : "0"}</span>`
                            }
                            </td>

                            <td>
                            ${
                                checkConfigKey("payin_associate_fees", configs) ?
                                    `<a href="#" class="merchant-editable"
                                       data-type="text"
                                       data-url="/merchant/action/UpdatePayInAssociateFees"
                                       data-value="${item.payin_associate_fees ? item.payin_associate_fees : '0'}"
                                       data-pk="${item.merchant_id}">${item.payin_associate_fees}</a>` :
                                    `<span>${item.payin_associate_fees ? item.payin_associate_fees : "0"}</span>`
                            }
                            </td>

                            <td>
                            ${
                                checkConfigKey("payout_fees", configs) ?
                                    `<a href="#" class="merchant-editable"
                                       data-type="text"
                                       data-url="/merchant/action/UpdatePayoutFees"
                                       data-value="${item.payout_fees ? item.payout_fees : '0'}"
                                       data-pk="${item.merchant_id}">${item.payout_fees ? item.payout_fees : 0}</a>` :
                                    `<span>${item.payout_fees ? item.payout_fees : "0"}</span>`
                            }
                            </td>

                            <td>
                            ${
                                checkConfigKey("payout_associate_fees", configs) ?
                                    `<a href="#" class="merchant-editable"
                                       data-type="text"
                                       data-url="/merchant/action/UpdatePayoutAssociateFees"
                                       data-value="${item.payout_associate_fees ? item.payout_associate_fees : '0'}"
                                       data-pk="${item.merchant_id}">${item.payout_associate_fees ? item.payout_associate_fees : 0}</a>` :
                                    `<span>${item.payout_associate_fees ? item.payout_associate_fees : "0"}</span>`
                            }
                            </td>

                            <td>
                            ${
                                checkConfigKey("settlement_cycle", configs) ?
                                    `<a href="#" class="merchant-editable"
                                       data-type="text"
                                       data-url="/merchant/action/UpdateSettlementCycle"
                                       data-value="${item.settlement_cycle ? item.settlement_cycle : 'Not Set'}"
                                       data-pk="${item.merchant_id}">${item.settlement_cycle ? item.settlement_cycle : "Not Set"}</a>` :
                                    `<span>${item.settlement_cycle ? item.settlement_cycle : "0"}</span>`
                            }
                            </td>

                            <td>
                            ${
                                checkConfigKey("payout_delayed_time", configs) ?
                                    `<a href="#" class="merchant-editable"
                                       data-type="text"
                                       data-url="/merchant/action/UpdatePayoutDelayedTime"
                                       data-value="${item.payout_delayed_time ? item.payout_delayed_time : '0'}"
                                       data-pk="${item.merchant_id}">${item.payout_delayed_time ? item.payout_delayed_time : 0}</a>` :
                                    `<span>${item.payout_delayed_time ? item.payout_delayed_time : "0"}</span>`
                            }
                            </td>

                            <td>
                            ${
                                checkConfigKey("is_auto_approved_payout", configs) ?
                                    `<a href="#" class="merchant-editable"
                                       data-type="select"
                                       data-source="[{value: '1',text: 'YES'},{value: '0',text: 'NO'},]"
                                       data-url="/merchant/action/UpdateIsAutoApprovedPayout"
                                       data-value="${item.is_auto_approved_payout ? " Yes" : " No"}"
                                       data-pk="${item.merchant_id}">${item.is_auto_approved_payout ? "Yes" : "No"}</a>` :
                                    `<span>${item.is_auto_approved_payout ? "Yes" : "No"}</span>`
                            }
                            </td>

                            <td>
                            ${
                                checkConfigKey("have_customer_details", configs) ?
                                    `<a href="#" class="merchant-editable"
                                       data-type="select"
                                       data-source="[{value: '1',text: 'YES'},{value: '0',text: 'NO'},]"
                                       data-url="/merchant/action/UpdateShowCustomerDetailsPage"
                                       data-value="${item.have_customer_details ? " Yes" : " No"}"
                                       data-pk="${item.merchant_id}">${item.have_customer_details ? "Yes" : "No"}</a>` :
                                    `<span>${item.have_customer_details ? "Yes" : "No"}</span>`
                            }
                            </td>

                            <td>
                            ${
                                checkConfigKey("have_customer_details_in_api", configs) ?
                                    `<a href="#" class="merchant-editable"
                                       data-type="select"
                                       data-source="[{value: '1',text: 'YES'},{value: '0',text: 'NO'},]"
                                       data-url="/merchant/action/UpdateIsCustomerDetailsRequired"
                                       data-value="${item.have_customer_details_in_api ? " Yes" : " No"}"
                                       data-pk="${item.merchant_id}">${item.have_customer_details_in_api ? "Yes" : "No"}</a>` :
                                    `<span>${item.have_customer_details_in_api ? "Yes" : "No"}</span>`
                            }
                            </td>


                            <td>
                            ${
                                checkConfigKey("is_settlement_enable", configs) ?
                                    `<a href="#" class="merchant-editable"
                                       data-type="select"
                                       data-source="[{value: '1',text: 'YES'},{value: '0',text: 'NO'},]"
                                       data-url="/merchant/action/UpdateIsSettlementEnable"
                                       data-value="${item.is_settlement_enable ? " Yes" : " No"}"
                                       data-pk="${item.merchant_id}">${item.is_settlement_enable === 1 ? "Yes" : "No"}</a>` :
                                    `<span>${item.is_settlement_enable ? "Yes" : "No"}</span>`
                            }
                            </td>

                            <td>
                            ${
                                checkConfigKey("old_users_days", configs) ?
                                    `<a href="#" class="merchant-editable"
                                       data-type="text"
                                       data-url="/merchant/action/UpdateOldUsersDays"
                                       data-value="${item.old_users_days ? item.old_users_days : 'Not Set'}"
                                       data-pk="${item.merchant_id}">${item.old_users_days ? item.old_users_days : "Not Set"}</a>` :
                                    `<span>${item.old_users_days ? item.old_users_days : "Not Set"}</span>`
                            }
                            </td>

                            <td>
                            ${
                                checkConfigKey("checkout_color", configs) ?
                                    `<a href="#" class="merchant-editable"
                                       data-type="text"
                                       data-url="/merchant/action/UpdateCheckoutColor"
                                       data-value="${item.checkout_color ? item.checkout_color : 'Not Set'}"
                                       data-pk="${item.merchant_id}"><span
                                        style="color:${item.checkout_color}">${item.checkout_color ? item.checkout_color : "Not Set"}</span></a>` :
                                    `<span>${item.checkout_color ? item.checkout_color : "Not Set"}</span>`
                            }
                            </td>

                            <td>
                            ${
                                checkConfigKey("checkout_theme_url", configs) ?
                                    `<a href="#" class="merchant-editable"
                                       data-type="text"
                                       data-url="/merchant/action/UpdateCheckoutThemeUrl"
                                       data-value="${item.checkout_theme_url ? item.checkout_theme_url : 'Not Set'}"
                                       data-pk="${item.merchant_id}">
                                        ${item.checkout_theme_url ? item.checkout_theme_url : "Not Set"}</a>` :
                                    `<span>${item.checkout_theme_url ? item.checkout_theme_url : "Not Set"}</span>`
                            }
                            </td>

                            <td>
                                ${item.merchant_bouncer_url ? item.merchant_bouncer_url : "Not Set"}
                            </td>

                            <td>
                                    <button class="btn btn-sm btn-primary" onclick="getMid(('${item.merchant_id}'))" data-toggle="modal" data-target="#addmanualPayout">
                                        Add Manual Payout
                                    </button>
                                    <button class="btn btn-sm btn-primary" onclick="getMid(('${item.merchant_id}'))" data-toggle="modal" data-target="#addmanualPayin">
                                        Add Manual Payin
                                    </button>
                            ${
                                checkConfigKey("is_action_allowed", configs) ?
                                    `<button class="btn btn-sm btn-primary" onclick="viewStatement('${item.merchant_id}')" >
                                        View Statement
                                    </button>

                                     <button class="btn btn-sm btn-primary" onclick="releaseSettlement('${item.merchant_id}')">
                                       Release Settlement
                                    </button>
                                    <button class="btn btn-sm btn-primary" onclick="dashboardLogs('${item.merchant_id}')">
                                        View Dashboard Logs</button>
                                    <button class="btn btn-sm btn-primary" onclick="viewWhitelistIp('${item.merchant_id}')">
                                        View WhiteList Ip
                                    </button>
                                    <button class="btn btn-sm btn-primary" onclick="resetPass('${item.merchant_id}')">
                                        Reset Password
                                    </button>` : "-"
                            }
                            </td>
                        </tr>`;
            });

            $("#GetMerchantData").html(htmlData);
            // FooTable.get('#merchantDataTable').destroy();
            $('#merchantDataTable').footable();
            $("#merchantDataTable").on("click","td:not(.footable-first-column)",function(e){
                $('.merchant-editable').editable({
                    success: function(response, newValue) {
                        // console.log(response);
                        toastr.success(response.message,"success",toastOption);
                        (new MerchantData()).getMerchant();
                    },
                    error: function(error) {
                        // console.log(error);
                        toastr.error(error.responseJSON.message,"error", toastOption);
                        (new MerchantData()).getMerchant();
                    }
                });
            });
            $('.preLoader').hide()

            // setPaginateButton("page-change-event", PaginateData, PostData);
        } else {
            this.setErrorHtml();
        }
    }

    this.getStatusClass = (status) => {
        let badge = "badge-warning";
        if(status === "New") {
            badge = "badge-success";
        }
        if(status === "1") {
            badge = "badge-success";
        }
        if(status === "Suspended") {
            badge = "badge-danger";
        }
        if(status === "0") {
            badge = "badge-danger";
        }
        if(status === "Hold") {
            badge = "badge-primary";
        }
        if(status === "Approved") {
            badge = "badge-info";
        }
        return badge;
    }

    this.setErrorHtml = () => {
        FsHelper.unblockUi($("#Merchant_page"));
        $("#GetMerchantData").html(`
            <tr>
                <td colspan="6">
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
}


$("#merchantFilterForm").on("submit", () => {
    const FormData = getFormData($("#merchantFilterForm "));
    PostData.filter_data = {
        merchant_id: null,
    }
    PostData.filter_data[FormData.FilterKey] = FormData.FilterValue;
    PostData.limit = FormData.Limit;
    PostData.page_no=1;
    (new MerchantData()).getMerchant();
});

EventListener.dispatch.on("page-change-event", (event, callback) => {
    PostData.page_no = callback.page_number;
    (new MerchantData()).getMerchant();
});

function resetMerchantFilter(){
    PostData.filter_data = {
        merchant_id: null,
    }
    PostData.page_no=1;
    PostData.limit=50;
    $('#merchantFilterForm')[0].reset();
    DzpDatePickerService.init();
    (new MerchantData()).getMerchant();
}


function resetPass(MerchantId) {
    $.confirm({
        title: 'Reset Password !',
        content: 'Are You Sure To Reset Merchant Password !',
        buttons: {
            confirm: function () {
                FsHelper.blockUi($("#Merchant_page"));
                let postData = {
                    merchant_id: MerchantId,
                }
                FsClient.post("/merchant/action/ResetMerchantAccountPassword", postData).then(
                    Response => {
                        FsHelper.unblockUi($("#Merchant_page"));
                        toastr.success(Response.message,"success",toastOption);
                        $.alert('Merchant New Pass is ' + ' :- ' + Response.data.temp_password);
                    }
                ).catch(Error => {
                    toastr.error(Error.responseJSON.message,"error",toastOption);
                    FsHelper.unblockUi($("#transaction_page"));
                    (new MerchantData()).getMerchant();
                });

            },
            cancel: function () {

            },
        }
    });
}

function dashboardLogs(MerchantId) {
    FsHelper.blockUi($("#Merchant_page"));
    let postData = {
        merchant_id: MerchantId,
        page_no: 1,
        limit: 50,
    }
    let htmlData = null;
    FsClient.post("/merchant/ViewDashboardLogs", postData).then(
        Response => {
            if (Response) {
                PaginateData.current_page = Response.current_page;
                PaginateData.last_page = Response.last_page;
                PaginateData.is_last_page = Response.is_last_page;
                PaginateData.total = Response.total_item;
                PaginateData.current_item_count = Response.current_item_count;
                $('#pagination').show();
                      let htmlData = "";
                        Response.data.forEach((item, index) => {
                            htmlData += `<tr>

                                    <td>  ${item.action_type ? item.action_type :""}  </td>
                                    <td>  ${item.action ? item.action :""}  </td>
                                    <td>  ${item.request_ip ? item.request_ip :""}  </td>
                                    <td>  ${item.user_agent ? item.user_agent :""}  </td>
                                    <td>  ${item.created_at_ist ? item.created_at_ist :""}  </td>

                         </tr>`;
                            $('#dashboardLogs').modal();
                            $("#dashboardData").html(htmlData);
                            setPaginateButton("change-event", PaginateData, postData);
                        });

                FsHelper.unblockUi($("#Merchant_page"));
            }

            EventListener.dispatch.on("change-event", (event, callback) => {
                postData.page_no = callback.page_number;
                (new MerchantData()).getMerchant();
            });
        }
    ).catch(Error => {
        console.log(Error)
        $('#pagination').hide();
        FsHelper.unblockUi($("#transaction_page"));
        $('#dashboardLogs').modal();
        $("#dashboardData").html(`
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
    }).finally(function () {
        FsHelper.unblockUi($("#transaction_page"));
    });
}

function viewStatement(MerchantId) {
    FsHelper.blockUi($("#Merchant_page"));
    let postData = {
        merchant_id: MerchantId,
        page_no: 1,
        limit: 50,
    }
    let htmlData = null;
    FsClient.post("/merchant/ViewStatement", postData).then(
        Response => {
            if (Response) {
                PaginateData.current_page = Response.current_page;
                PaginateData.last_page = Response.last_page;
                PaginateData.is_last_page = Response.is_last_page;
                PaginateData.total = Response.total_item;
                PaginateData.current_item_count = Response.current_item_count;
                $('#pagination').show();
                      let htmlData = "";
                        Response.data.forEach((item, index) => {
                            htmlData += `<tr>
                                    <td>  ${item.pay_date ? item.pay_date :""}  </td>
                                    <td>  ${item.open_balance ? item.open_balance :""}  </td>
                                    <td>  ${item.payin ? item.payin :""}  </td>
                                    <td>  ${item.payin_live ? item.payin_live :""}  </td>
                                    <td>  ${item.payout ? item.payout :""}  </td>
                                    <td>  ${item.payout_live ? item.payout_live :""}  </td>
                                    <td>  ${item.refund ? item.refund :""}  </td>
                                    <td>  ${item.un_settled ? item.un_settled :""}  </td>
                                    <td>  ${item.settled ? item.settled :""}  </td>
                                    <td>  ${item.closing_balance ? item.closing_balance :""}  </td>
                                    <td>  ${item.created_at_ist ? item.created_at_ist :""}  </td>
                                    <td>  ${item.updated_at_ist ? item.updated_at_ist :""}  </td>
                         </tr>`;
                            $('#ViewStatement').modal();
                            $("#StatementData").html(htmlData);
                            setPaginateButton("change-event", PaginateData, postData);
                        });

                FsHelper.unblockUi($("#Merchant_page"));
            }
        }
    ).catch(Error => {
        console.log(Error)
        $('#pagination').hide();
        FsHelper.unblockUi($("#Merchant_page"));
        $('#ViewStatement').modal();
        $("#StatementData").html(`
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
    }).finally(function () {
        FsHelper.unblockUi($("#Merchant_page"));
    });
}

function getMid(mid){
localStorage.setItem('MFilterKey',(mid));
    $("#merchant_id").val(mid);
    $("#merchant_id_forPayin").val(mid);
}

$("#addManualPayoutForm").on("submit", (e) => {
    e.preventDefault()
    const FormData = getFormData($("#addManualPayoutForm"));
    FsHelper.blockUi($("#addManualPayoutForm"));
    FsClient.post("/merchant/AddManualPayout", FormData)
        .then(res => {
            toastr.success(res.message, "success", options);
            $("#addManualPayoutForm")[0].reset();
            $("#close_btn").click();
        })
        .catch(err => {
            toastr.error(err.responseJSON.message, "Error", options);
            console.log(err);
        })
        .finally(() => {
            FsHelper.unblockUi($("#addManualPayoutForm"));
    });
});
$("#addManualPayinForm").on("submit", (e) => {
    e.preventDefault()
    const FormData = getFormData($("#addManualPayinForm"));
    FsHelper.blockUi($("#addManualPayinForm"));
    FsClient.post("/merchant/AddManualPayIn", FormData)
        .then(res => {
            toastr.success(res.message, "success", options);
            $("#addManualPayinForm")[0].reset();
            FsHelper.unblockUi($("#addManualPayinForm"));
            $("#close_btn_manualpayin").click();
        })
        .catch(err => {
            toastr.error(err.responseJSON.message, "Error", options);
            console.log(err);
        })
        .finally(() => {
            FsHelper.unblockUi($("#addManualPayinForm"));
        });
});
$("#addSettlementForm").on("submit", (e) => {
    e.preventDefault()
    const FormData = getFormData($("#addSettlementForm"));
    FsHelper.blockUi($("#addSettlementForm"));
    FsClient.post("/merchant/AddMerchantSettlement", FormData)
        .then(res => {
            toastr.success(res.message, "success", options);
            $("#addSettlementForm")[0].reset();
            FsHelper.unblockUi($("#addSettlementForm"));
            $("#close_btn_addsettlement").click();
        })
        .catch(err => {
            toastr.error(err.responseJSON.message, "Error", options);
            console.log(err);
        })
        .finally(() => {
            FsHelper.unblockUi($("#addSettlementForm"));
        });
});


function viewWhitelistIp(MerchantId) {
    FsHelper.blockUi($("#Merchant_page"));
    let postData = {
        merchant_id: MerchantId,
        page_no: 1,
        limit: 50,
    }
    let htmlData = null;
    FsClient.post("/merchant/ViewMerchantWhitelistIps", postData).then(
        Response => {
            if (Response) {
                PaginateData.current_page = Response.current_page;
                PaginateData.last_page = Response.last_page;
                PaginateData.is_last_page = Response.is_last_page;
                PaginateData.total = Response.total_item;
                PaginateData.current_item_count = Response.current_item_count;
                $('#pagination').show();
                let htmlData = "";
                Response.data.forEach((item, index) => {
                    htmlData += `<tr>
                                    <td>  ${item.id ? item.id :""}  </td>
                                    <td>  ${item.merchant_id ? item.merchant_id :""}  </td>
                                    <td>  ${item.is_active ? item.is_active :""}  </td>
                                    <td>  ${item.merchant_ip ? item.merchant_ip :""}  </td>
                                    <td>  ${item.type ? item.type :""}  </td>
                                    <td>  ${item.created_at_ist ? item.created_at_ist :""}  </td>
                                    <td>  ${item.updated_at_ist ? item.updated_at_ist :""}  </td>
                         </tr>`;
                    $('#ViewWhitelistIp').modal();
                    $("#whitelistData").html(htmlData);
                    setPaginateButton("change-event", PaginateData, postData);
                });

                FsHelper.unblockUi($("#Merchant_page"));
            }
        }
    ).catch(Error => {
        console.log(Error)
        $('#pagination').hide();
        FsHelper.unblockUi($("#Merchant_page"));
        $('#ViewWhitelistIp').modal();
        $("#whitelistData").html(`
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
    }).finally(function () {
        FsHelper.unblockUi($("#Merchant_page"));
    });
}
function releaseSettlement(MerchantId) {
    FsHelper.blockUi($("#Merchant_page"));

    let postData = {
        merchant_id: MerchantId
    }
    let htmlData = null;
    FsClient.post("/merchant/GetPendingSettlement", postData).then(
        Response => {
            if (Response) {
                let htmlData = "";
                Response.databalances.forEach((item, index) => {
                        htmlData += `<tr>
                    <td>${item.pay_date ? item.pay_date:""}</td>
                    <td>${item.settled ? item.settled:""}</td>
                    <td>${item.un_settled ? item.un_settled:""}</td>
                    <td>${item.closing_balance ? item.closing_balance:""}</td>
                      </tr>`;
                });
                    $("#MerStatementData").html(htmlData);
                    $('#addsettlement').modal();
                    $("#currbalance").text("Current Balance :  "+ Response.data.PayoutBalance);
                    $("#availablebalance").text("Available Unsettled Balance :  "+ Response.data.UnsettledBalance);
                    $("#merchant_id_forSettlement").val(MerchantId);
                    $("#release_amount").val('');
                    FsHelper.unblockUi($("#Merchant_page"));

            }
        }
    ).catch(Error => {
        console.log(Error)
        FsHelper.unblockUi($("#Merchant_page"));
        $('#addsettlement').modal();
    }).finally(function () {
        FsHelper.unblockUi($("#Merchant_page"));
    });
}


$("#addMerchant").on("submit", (e) => {
    e.preventDefault()
    const FormData = getFormData($("#addMerchant"));
    // FsHelper.blockUi($("#addMerchant"));
    console.log(FormData)
    FsClient.post("/merchant/add", FormData)
        .then(res => {
            toastr.success(res.message, "success", options);
            $("#addMerchant")[0].reset();
            $("#close_btn").click();
            (new MerchantData()).getMerchant ();
        })
        .catch(err => {
            toastr.error(err.responseJSON.message, "Error", options);
            console.log(err);
        })
        .finally(() => {
            FsHelper.unblockUi($("#addMerchant"));
        });
});

function checkConfigKey(key, configs) {
    return configs.includes(key);
}


$("#merchantConfigBtn").on("click", (e) => {
    e.preventDefault()
    FsHelper.blockUi($("#merchantConfigModal"));
    // $('#merchantConfigModal')[0].reset();
    FsClient.get("/fetchBankStatus")
        .then(res => {
            console.log(res);
            const data = res.data;
            $.each(data, (index, item) => {
                if(item.bank_name === "FEDERAL" || item.bank_name === "HDFC" || item.bank_name === "ICICI" || item.bank_name === "RBL") {
                    $(`#${item.bank_name}`).prop("checked", item.bank_name)
                } else {
                    $(`#${item.bank_name}`).val(item.bank_name)
                }
            })
        })
        .catch(err => {
            toastr.error(err.responseJSON.message, "Error", options);
            console.log(err);
        })
        .finally(() => {
            FsHelper.unblockUi($("#merchantConfigModal"));
        });
});

let ConfigFormData = {
    bank_name: {
        bank_name:null,
    },
};

$("#FEDERAL").on("change", (e) => {
    e.preventDefault()
    ConfigFormData = getFormData($("#FEDERAL"));
    ConfigFormData.value = $("#FEDERAL").is(':checked') ? 1 : 0;
    ConfigFormData.bank_name=null;
    ConfigFormData.bank_name='FEDERAL';
    FsHelper.blockUi($("#merchantConfigModal"));
     callConfig(ConfigFormData)
});

$("#HDFC").on("change", (e) => {
    e.preventDefault()
    ConfigFormData = getFormData($("#HDFC"));
    ConfigFormData.value = $("#HDFC").is(':checked') ? 1 : 0;
    ConfigFormData.bank_name=null;
    ConfigFormData.bank_name='HDFC';
    FsHelper.blockUi($("#merchantConfigModal"));
     callConfig(ConfigFormData)
});

$("#ICICI").on("change", (e) => {
    e.preventDefault()
    ConfigFormData = getFormData($("#ICICI"));
    ConfigFormData.value = $("#ICICI").is(':checked') ? 1 : 0;
    ConfigFormData.bank_name=null;
    FsHelper.blockUi($("#merchantConfigModal"));
    ConfigFormData.bank_name='ICICI';
     callConfig(ConfigFormData)
});

$("#RBL").on("change", (e) => {
    e.preventDefault()
    ConfigFormData = getFormData($("#RBL"));
    ConfigFormData.value = $("#RBL").is(':checked') ? 1 : 0;
    ConfigFormData.bank_name=null;
    FsHelper.blockUi($("#merchantConfigModal"));
    ConfigFormData.bank_name='RBL';
     callConfig(ConfigFormData)
});

function callConfig(ConfigFormData) {
    FsClient.post("/updateBankStatus", ConfigFormData)
        .then(res => {
            toastr.success(res.message, "success", options);
        })
        .catch(err => {
            toastr.error(err.responseJSON.message, "Error", options);
            console.log(err);
        })
        .finally(() => {
            FsHelper.unblockUi($("#merchantConfigModal"));
        });
}
