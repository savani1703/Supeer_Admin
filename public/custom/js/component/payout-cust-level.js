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

(new PayoutCustLevel()).getData();
DzpDatePickerService.init();
function PayoutCustLevel() {
    this.getData = () => {
        FsHelper.blockUi($("#PayoutCustLevel"));
        FsClient.post("/Payout/GetCustomerLevelData", PostData).then(this.handleResponse).catch(this.handleError);
    }

    this.handleResponse = (data) => {
        FsHelper.unblockUi($("#PayoutCustLevel"));
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
                console.log(item)
                htmlData += `<tr>
                                <td>
                                    <span class="d-block font-weight-bold">${item.customer_id ? item.customer_id : ""}</span>
                                </td>
                                <td>
                                    <span class="d-block font-weight-bold">${item.account_number ? item.account_number : ""}</span>
                                </td>
                                <td>
                                    <span class="d-block font-weight-bold mt-1"> ${item.account_holder_name ? item.account_holder_name : ""}</span>
                                </td>
                                <td>
                                    <span class="font-weight-bold mt-1"> ${item.bank_name ? item.bank_name : ""}</span>
                                </td>
                                <td>
                                    <span class="d-block font-weight-bold mt-1"> ${item.pg_name ? item.pg_name : ""}</span>
                                </td>
                                 <td>
                                    <span class="d-block font-weight-bold mt-1"> ${item.meta_id ? item.meta_id : ""}</span>
                                    <span class="d-block font-weight-bold mt-1"> ${item.pg_label ? item.pg_label : ""}</span>
                                </td>
                                 <td>
                                    <span class="d-block font-weight-bold mt-1"> ${item.last_meta_merchant_id ? item.last_meta_merchant_id : ""}</span>
                                </td>
                                 <td>
                                    <span class="d-block font-weight-bold mt-1"> ${item.is_get ? item.is_get : ""}</span>
                                </td>
                                 <td>
                                    <span class="d-block font-weight-bold mt-1"> ${item.remark ? item.remark : ""}</span>
                                </td>
                                 <td>
                                    <span class="d-block font-weight-bold mt-1"> ${item.last_success_at_ist ? item.last_success_at_ist : ""}</span>
                                </td>
                                <td>
                                    <span class="d-block font-weight-bold mt-1">  <span>Creat : </span> ${item.created_at_ist ? item.created_at_ist : ""}</span>
                                    <span class="d-block font-weight-bold mt-1"> <span>Update : </span>  ${item.updated_at_ist ? item.updated_at_ist : "-"}</span>
                                </td>
                            </tr>`;
            });
            $('.preLoader').hide()
            $("#PayoutCustLevelData").html(htmlData);
            setPaginateButton("page-change-event", PaginateData, PostData);
        } else {
            this.setErrorHtml();
        }
    }
    this.setErrorHtml = () => {
        FsHelper.unblockUi($("#PayoutCustLevel"));
        $('.preLoader').hide();
        $('#pagination').hide();
        $("#PayoutCustLevelData").html(`
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

$("#custLevelFilterForm").on("submit", () => {
    const FormData = getFormData($("#custLevelFilterForm"));
    PostData.filter_data = {
        customer_id: null,
        account_number: null,
        pg_name: null,
        meta_id: null,
        start_date:null,
        end_date:null,
    }
    PostData.filter_data[FormData.search_key] = FormData.search_value;
    PostData.limit = FormData.limit;
    PostData.filter_data.pg_type = FormData.pg_name;
    PostData.filter_data.meta_id = FormData.meta_id;
    PostData.page_no=1;

    if(FormData.daterange) {
        let splitDate = FormData.daterange.split(/-/);
        PostData.filter_data.start_date = moment(splitDate[0], 'DD/MM/YYYY').format('YYYY-MM-DD 00:00:00');
        PostData.filter_data.end_date = moment(splitDate[1], 'DD/MM/YYYY').format('YYYY-MM-DD 23:59:59');
    }
    (new PayoutCustLevel()).getData();
});

EventListener.dispatch.on("page-change-event", (event, callback) => {
    PostData.page_no = callback.page_number;
    (new PayoutCustLevel()).getData();
});

function resetCustLevelForm(){
    PostData.filter_data = {
        customer_id: null,
        account_number: null,
        pg_name: null,
        meta_id: null,
        start_date:null,
        end_date:null,
    }
    PostData.page_no=1;
    PostData.limit=50;

    $('#custLevelFilterForm')[0].reset();
    DzpDatePickerService.init();
    (new PayoutCustLevel()).getData();
}

$("#pg_name").on("change", () => {
    const txnFormData = getFormData($("#custLevelFilterForm"));
    if (txnFormData.pg_name === "ALL"){
        $("#meta_id").hide();
    }
    if (txnFormData.pg_name){
        if (txnFormData.pg_name !=='ALL'){
            $("#meta_id").html('');
            FsHelper.blockUi($("#PayoutCustLevel"));
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
                    FsHelper.unblockUi($("#PayoutCustLevel"));
                });
            $("#payoutPG").show();
        }
    }
});

$('#accountLoad').on('shown.bs.modal', function () {
    (new PayoutCustLevel()).getAccountLoad();
})
