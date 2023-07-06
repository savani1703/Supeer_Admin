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



const badge_color = {
    1: 'badge badge-success',
    0: 'badge badge-danger',
};

(new SupportCustomer()).getCust();

DzpDatePickerService.init();
function SupportCustomer() {
    this.getCust = () => {
        FsHelper.blockUi($("#custTablw"));
        FsClient.post("/support/GetCustomers", PostData).then(this.handleResponse).catch(this.handleError);
    }

    this.handleResponse = (data) => {
        FsHelper.unblockUi($("#custTablw"));
        if(data.status) {
            PaginateData.current_page = data.current_page;
            PaginateData.last_page = data.last_page;
            PaginateData.is_last_page = data.is_last_page;
            PaginateData.total = data.total_item;
            PaginateData.current_item_count = data.current_item_count;
            this.setHtmlData(data.data);
            $('#pagination').show();
        } else {
            this.setErrorHtml();
        }
    }

    this.handleError = (error) => {
        this.setErrorHtml();
    }

    this.setHtmlData = (data) => {
        if(data && data.length > 0) {
            let htmlData = "";
            data.forEach((item, index) => {
                let BadgeColor =  badge_color[item.is_block];
                htmlData += `<tr>
                                <td>
                                    <span class="d-block">${item.customer_id ? item.customer_id : ""}</span>
                                </td>
                                <td>
                                    <span class="d-block">${item.merchant_id ? item.merchant_id : ""}</span>
                                    <span class="d-block  mt-2">${item.merchant_details.merchant_name ? item.merchant_details.merchant_name : ""}</span>
                                </td>
                                 <td>
                                    <span class="d-block">${item.pg_method ? item.pg_method : ""}</span>
                                </td>
                                <td>
                                    <span class="d-block">${item.txn_amount ? item.txn_amount : "0"}</span>
                                </td>

                                <td>
                                    <span class="d-block"> ${item.txn_count ? item.txn_count : "0"}</span>
                                </td>
                                <td>
                                     <a href="#" class="updateCustBlockStatus ${BadgeColor}"
                                         data-type="select"
                                         data-pk="${item.merchant_id}"
                                         data-customer_id="${item.customer_id}"
                                         data-pg_method="${item.pg_method}"
                                         data-abc="true">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                         ${item.is_block===1 ? "Yes"  : "No"}
                                    </a>
                                </td>
                                <td>
                                    <span class="d-block"> ${item.last_state ? item.last_state : ""}</span>
                                </td>
                                <td>
                                    <span class="d-block"> ${item.pg_label ? item.pg_label : ""}</span>
                                </td>
                                <td>
                                    <span class="d-block"> ${item.user_security_level ? item.user_security_level : ""}</span>
                                </td>
                                <td>
                                    ${setUpiPaymentIndicator(item.total_success_upi_id ,item.customer_id)}
                                </td>
                                <td>
                                    ${getCustState(item.customer_id)}
                                </td>
                                <td>
                                    <span class="d-block"> ${item.created_at_ist ? item.created_at_ist : ""}</span>
                                </td>
                                <td>
                                    <span class="d-block"> ${item.updated_at_ist ? item.updated_at_ist : ""}</span>
                                </td>
                            </tr>`;
            });
            $('.preLoader').hide()
            $("#supportCustomerData").html(htmlData);
            setPaginateButton("page-change-event", PaginateData, PostData);
            loadIsBlockxEditable()
        } else {
            this.setErrorHtml();
        }
        function loadIsBlockxEditable() {
            $('.updateCustBlockStatus').editable({
                type: 'select',
                params: function (params) {
                    params.pg_method = $(this).attr("data-pg_method");
                    params.customer_id = $(this).attr("data-customer_id");
                    return params;
                },
                url: '/support/UpdateCustomerBlockStatus',
                title: 'updateCustBlockStatus',
                name: 'updateCustBlockStatus',
                source: [
                    {
                        value: '1',
                        text: 'Yes'
                    },
                    {
                        value: '0',
                        text: 'No'
                    },
                ],
                success: function (response) {
                    toastr.success("success", response.message, toastOption);
                    (new SupportCustomer()).getCust();
                }, error: function (error) {
                    toastr.error("error", error.responseJSON.message, toastOption);
                },
            });
        }
    }
    this.setErrorHtml = () => {
        $('.preLoader').hide();
        FsHelper.unblockUi($("#custTablw"));
        $('#pagination').hide();
        $("#supportCustomerData").html(`
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
    this.getCustomerUpiMappingDetails = (customer_id) => {
        FsHelper.blockUi($(".modal-body"));
        let customerPostData = {
            customer_id : customer_id,
        }
        FsHelper.blockUi($("#custTablw"));
        FsClient.post("/customer/upi/mapping/byId",customerPostData).then(this.ResponseModaltxn).catch(this.setErrorHtml2);
    }

    this.getCustStateDetail = (customer_id) => {
        FsHelper.blockUi($(".modal-body"));
        let customerPostData = {
            customer_id : customer_id,
        }
        FsHelper.blockUi($("#custStateDataTbl"));
        FsClient.post("/customer/state/get/byId",customerPostData).then(this.ResponseModalStateData).catch(this.custStateDataEror);
    }

    this.ResponseModaltxn = (data) => {
        FsHelper.unblockUi($("#custTablw"));
        if (data.status) {
            let onlyData = data.data;
            if(onlyData) {
                let htmlData = "";
                $.each(onlyData, (index, item) => {
                    console.log(item.success_upi_sum);
                    htmlData += `<tr>
                                     <td>
                                        <span class="d-block  mb-1">${item.customer_id ? item.customer_id : "--"}</span>
                                    </td>
                                    <td>
                                        <span class="d-block  mb-1">${item.upi_id ? item.upi_id : "--"}</span>
                                    </td>
                                    <td>
                                        <span class="d-block  mb-1">${item.success_upi_sum[0].s_upi ? item.success_upi_sum[0].s_upi :"--" }</span>
                                    </td>
                                    <td>
                                        <span class="d-block  mb-1"><b> ${item.success_upi_sum[0].total_success_upi ? item.success_upi_sum[0].total_success_upi :"0" }</b>  (${item.success_upi_sum[0].sum_success_upi ? item.success_upi_sum[0].sum_success_upi : "0" })</span>
                                    </td>
                                <tr>`;
                });
                $('.preLoader').hide();
                $("#customerUpiMappingData").html(htmlData);
            }else {
                this.setErrorHtml2();
            }
        }
    }

    this.ResponseModalStateData = (data) => {
        FsHelper.unblockUi($("#custStateDataTbl"));
        if (data.status) {
            let onlyData = data.data;
            if(onlyData) {
                let htmlData = "";
                    $.each(onlyData, (index, item) => {
                        htmlData += `<tr>

                                     <td>
                                        <span class="d-block font-weight-bold mb-1">${index ? index : ""}</span>
                                    </td>
                                    <td>
                                        <span class="d-block  mb-1">${item.total_transaction ? item.total_transaction : "0"}</span>
                                    </td>
                                    <td>
                                        <span class="d-block  mb-1"><b> ${item.total_success.total_txn ? item.total_success.total_txn : "0"}</b> (${item.total_success.txn_sum ? item.total_success.txn_sum : "0"}) </span>
                                    </td>
                                    <td>
                                        <span class="d-block  mb-1"> <b> ${item.total_processing.total_txn ? item.total_processing.total_txn : "0"}</b> (${item.total_processing.txn_sum ? item.total_processing.txn_sum : "0"})</span>
                                    </td>
                                    <td>
                                        <span class="d-block  mb-1"> <b>${item.total_initialized.total_txn ? item.total_initialized.total_txn : "0"}</b> (${item.total_initialized.txn_sum ? item.total_initialized.txn_sum : "0"})</span>
                                    </td>
                                    <td>
                                        <span class="d-block  mb-1"> <b>${item.total_failed.total_txn ? item.total_failed.total_txn : "0"}</b> (${item.total_failed.txn_sum ? item.total_failed.txn_sum : "0"})</span>
                                    </td>
                                <tr>`;
                            });
                $('.preLoader').hide();
                $("#CustStateData").html(htmlData);
            }else {
                this.setErrorHtml2();
            }
        }
    }

    this.setErrorHtml2 = () => {
        $('.preLoader').hide();
        FsHelper.unblockUi($("#custTablw"));
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

    this.custStateDataEror = () => {
        $('.preLoader').hide();
        FsHelper.unblockUi($("#custStateDataTbl"));
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

function setUpiPaymentIndicator(total_success_upi_id, customer_id) {
    if(total_success_upi_id > 1){
        return `<a class="btn btn-danger" style="color: white;" onclick="(new SupportCustomer()).getCustomerUpiMappingDetails('${customer_id}');" href="" data-toggle="modal" data-target="#customerUpiMappingDetails" >${total_success_upi_id ? total_success_upi_id : ""}</a>`;
    }
    return ``;
}

function getCustState(customer_id) {
        return `<a class="btn btn-primary" onclick="(new SupportCustomer()).getCustStateDetail('${customer_id}');" href="" data-toggle="modal" data-target="#custStateViseData" >Show State</a>`;
}



$("#eventForm").on("submit", () => {
    const FormData = getFormData($("#eventForm"));
    PostData.filter_data = {
        pg_method: null,
        customer_id: null,
        merchant_id: null,
        start_date:null,
        end_date:null,
        customerFilter : null
    }
    PostData.filter_data[FormData.FilterKey] = FormData.FilterValue;
    PostData.filter_data.status = FormData.status;
    PostData.limit = FormData.Limit;
    PostData.page_no=1;

    if(FormData.customerFilter !== "All") {
        PostData.filter_data.customerFilter = FormData.customerFilter;
    } else {
        PostData.filter_data.customerFilter = null;
    }

    if(FormData.daterange) {
        let splitDate = FormData.daterange.split(/-/);
        PostData.filter_data.start_date = moment(splitDate[0], 'DD/MM/YYYY').format('YYYY-MM-DD 00:00:00');
        PostData.filter_data.end_date = moment(splitDate[1], 'DD/MM/YYYY').format('YYYY-MM-DD 23:59:59');
    }
    (new SupportCustomer()).getCust();
});

EventListener.dispatch.on("page-change-event", (event, callback) => {
    PostData.page_no = callback.page_number;
    (new SupportCustomer()).getCust();
});


function resetCustomerForm(){
    PostData.filter_data = {
        pg_method: null,
        customer_id: null,
        merchant_id: null,
        start_date:null,
        end_date:null,
    }
    PostData.page_no=1;
    PostData.limit=50;

    $('#eventForm')[0].reset();
    DzpDatePickerService.init();
    (new SupportCustomer()).getCust();
}
