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


(new CustData()).getCustHid();
DzpDatePickerService.init();
function CustData() {
    this.getCustHid = () => {
        FsHelper.blockUi($("#CustData"));
        console.log(PostData);
        FsClient.post("/risk/get/custHidDetail", PostData).then(this.handleResponse).catch(this.handleError);
    }

    this.handleResponse = (data) => {
        FsHelper.unblockUi($("#CustData"));
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

    this.handleError = (error) => {
        this.setErrorHtml();
    }
    this.setTxnHtmlData = (data) => {
        if(data && data.length > 0) {
            let htmlData = "";
            data.forEach((item, index) => {
                htmlData += `<tr>
                                <td>
                                    <span class="d-block font-weight-bold">${item.device_id ? item.device_id : ""}</span>
                                </td>
                                <td>
                                    <span class="d-block font-weight-bold"> ${multiCust(item.total_customer_id ,item.device_id)}</span>
                                </td>
                                <td>
                                    <span class="d-block font-weight-bold mt-1"> ${item.created_at_ist ? item.created_at_ist : ""}</span>
                                </td>
                            </tr>`;
            });
            $('.preLoader').hide()
            $("#CustData").html(htmlData);
            setPaginateButton("page-change-event", PaginateData, PostData);
        } else {
            this.setErrorHtml();
        }
    }
    this.setErrorHtml = () => {
        $('.preLoader').hide();
        $('#pagination').hide();
        $("#CustData").html(`
            <tr>
                <td colspan="8">
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

    this.getCustomerHidMappingDetails = (device_id) => {
        let customerPostData = {
            browser_id : device_id,
        }
        FsHelper.blockUi($("#customerDetails"));
        FsClient.post("/transaction/byBrowserId",customerPostData).then(this.ResponseModaltxn).catch(this.setErrorHtml2);
    }
    this.ResponseModaltxn = (data) => {
        console.log(data)
        FsHelper.unblockUi($("#customerDetails"));
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

    this.setErrorHtml2 = () => {
        $('.preLoader').hide();
        FsHelper.unblockUi($("#customerDetails"));
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




$("#CustHidForm").on("submit", () => {
    const FormData = getFormData($("#CustHidForm"));
    PostData.filter_data = {
        email_id: null,
        action: null,
        start_date:null,
        end_date:null,
        customerFilter:null,
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
    (new CustData()).getCustHid();
});

EventListener.dispatch.on("page-change-event", (event, callback) => {
    PostData.page_no = callback.page_number;
    (new CustData()).getCustHid();
});

function resetCustHidForm(){
    PostData.filter_data = {
        email_id: null,
        action: null,
        start_date:null,
        end_date:null,
        customerFilter:null,
    }
    PostData.page_no=1;
    PostData.limit=50;

    $('#CustHidForm')[0].reset();
    DzpDatePickerService.init();
    (new CustData()).getCustHid();
}


function multiCust(total_customer_id, device_id) {
    if(total_customer_id > 1){
        return `<a class="btn btn-danger" style="color: white;" onclick="(new CustData()).getCustomerHidMappingDetails('${device_id}');" href="" data-toggle="modal" data-target="#customerDetails" >${total_customer_id ? total_customer_id : ""}</a>`;
    }
    return `<span style="margin-left: 12px;">${total_customer_id}</span>`;
}

function riskCustBlockByHid() {
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
                    FsHelper.unblockUi($("#CustData"));
                    (new CustData()).getCustHid();
                }
            ).catch(Error => {
                toastr.error(Error.responseJSON.message,"error",toastOption);
                FsHelper.unblockUi($("#CustData"));
                (new CustData()).getCustHid();
            });
        },
        cancel : function (){
        }
    });
    myModal.open();
}
