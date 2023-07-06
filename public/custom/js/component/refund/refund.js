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

(new RefundModule()).getRefund();
DzpDatePickerService.init();

function RefundModule() {

    this.getRefund = () => {
        FsHelper.blockUi($("#refundPage"));
        FsClient.post("/refund", PostData).then(this.handleResponse).catch(this.handleError).finally(() => { FsHelper.unblockUi($("#refundPage")); });
    }

    this.handleResponse = (response) => {
        if(response.status) {
            PaginateData.current_page = response.current_page;
            PaginateData.last_page = response.last_page;
            PaginateData.is_last_page = response.is_last_page;
            PaginateData.total = response.total_item;
            PaginateData.current_item_count = response.current_item_count;
            this.setTxnHtmlData(response.data);
            $('#pagination').show();
        } else {
            this.setErrorHtml();
        }
    }

    this.handleError = (error) => {
        PaginateData = {
            link_limit: 2,
            from: 2,
            to: 2,
            total: null,
            is_last: null,
            current_item_count: null,
            current_page: null,
            last_page: null,
        };
        this.setErrorHtml();
    }

    this.setTxnHtmlData = (data) => {
        if(data && data.length > 0) {
            let htmlData = "";
            data.forEach((item, index) => {
                htmlData += `<tr>
                                <td>
                                    <span class="d-block font-weight-bold mb-1">${item.refund_id ? item.refund_id : ""}</span>
                                    <span class="d-block mb-1">Type: ${item.refund_type ? item.refund_type : ""}</span>
                                </td>
                                <td>
                                    <span class="d-block  mb-1">${item.merchant_details ? item.merchant_details.merchant_name : ""}</span>
                                    <span class="d-block "><small>${item.merchant_id ? item.merchant_id : ""}</small></span>
                                </td>
                                <td>
                                    <span class="d-block font-weight-bold mt-1"> ${item.transaction_id ? item.transaction_id : ""}</span>
                                </td>
                                <td>
                                    <span class="d-block font-weight-bold mt-1">₹ ${item.refund_amount ? item.refund_amount : ""}</span>
                                </td>
                                <td>
                                    <span class="d-block font-weight-bold mt-1"> ${item.refund_reason ? item.refund_reason : ""}</span>
                                </td>
                                <td>
                                    <span class="font-weight-bold mt-1 badge ${FsHelper.getStatusBadge(item.refund_status)}"> ${item.refund_status ? item.refund_status : ""}</span>
                                </td>
                                <td>
                                    <span class="font-weight-bold mt-1 badge ${FsHelper.getStatusBadge(item.internal_status)}"> ${item.internal_status ? item.internal_status : ""}</span>
                                </td>
                                <td>
                                    <span class="d-block  mb-1">Create:  ${item.created_at_ist ? item.created_at_ist : ""}</span>
                                    <span class="d-block  mb-1"> Update:  ${item.updated_at_ist ? item.updated_at_ist : ""}</span>
                                </td>
                            </tr>`;
            });
            $('.preLoader').hide()
            $("#RefundData").html(htmlData);
            setPaginateButton("page-change-event", PaginateData, PostData);
        } else {
            this.setErrorHtml();
        }
    }

    this.setErrorHtml = () => {
        $('#pagination').hide();
        $("#RefundData").html(`
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
}

EventListener.dispatch.on("page-change-event", (event, callback) => {
    PostData.page_no = callback.page_number;
    (new RefundModule()).getRefund();
});


$("#refundForm").on("submit", () => {
    const FormData = getFormData($("#refundForm"));
    PostData.filter_data = {
        refund_id: null,
        merchant_id: null,
        transaction_id: null,
        status: null,
        start_date: null,
        end_date: null,
    }
    PostData.filter_data[FormData.FilterKey] = FormData.FilterValue;
    PostData.filter_data.status = FormData.status;
    PostData.filter_data.merchant_id = FormData.merchant_id;
    PostData.limit = FormData.Limit;
    PostData.page_no = 1;

    if(FormData.daterange) {
        let splitDate = FormData.daterange.split(/-/);
        PostData.filter_data.start_date = moment(splitDate[0], 'DD/MM/YYYY').format('YYYY-MM-DD 00:00:00');
        PostData.filter_data.end_date = moment(splitDate[1], 'DD/MM/YYYY').format('YYYY-MM-DD 23:59:59');
    }
    (new RefundModule()).getRefund();
});

function resetRefundForm(){
    PostData.filter_data = {
        refund_id: null,
        merchant_id: null,
        transaction_id: null,
        status: null,
        start_date: null,
        end_date: null,
    }
    PostData.page_no=1;
    PostData.limit=50;

    $('#refundForm')[0].reset();
    DzpDatePickerService.init();
    (new RefundModule()).getRefund();
}
