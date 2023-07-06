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


(new LateSuccess()).getData();
DzpDatePickerService.init();
function LateSuccess() {
    this.getData = () => {
        FsHelper.blockUi($("#lateSucessMain"));
        FsClient.post("/get/late-success", PostData).then(this.handleResponse).catch(this.handleError);
    }

    this.handleResponse = (data) => {
        FsHelper.unblockUi($("#lateSucessMain"));
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
                                    <span class="d-block font-weight-bold">${item.transaction_id ? item.transaction_id : ""}</span>
                                </td>
                                <td>
                                    <span class="d-block font-weight-bold">${item.utr_number ? item.utr_number : ""}</span>
                                </td>
                                <td>
                                    <span class="d-block font-weight-bold mt-1"> ${item.created_at_ist ? item.created_at_ist : ""}</span>
                                </td>
                            </tr>`;
            });
            $('.preLoader').hide()
            $("#lateSuccessData").html(htmlData);
            setPaginateButton("page-change-event", PaginateData, PostData);
        } else {
            this.setErrorHtml();
        }
    }
    this.setErrorHtml = () => {
        FsHelper.unblockUi($("#lateSucessMain"));
        $('.preLoader').hide();
        $('#pagination').hide();
        $("#lateSuccessData").html(`
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

$("#lateSuccessForm").on("submit", () => {
    const FormData = getFormData($("#lateSuccessForm"));
    PostData.filter_data = {
        search_value: null,
        start_date:null,
        end_date:null,
    }
    PostData.filter_data[FormData.FilterKey] = FormData.FilterValue;
    PostData.limit = FormData.Limit;
    PostData.page_no=1;

    if(FormData.daterange) {
        let splitDate = FormData.daterange.split(/-/);
        PostData.filter_data.start_date = moment(splitDate[0], 'DD/MM/YYYY').format('YYYY-MM-DD 00:00:00');
        PostData.filter_data.end_date = moment(splitDate[1], 'DD/MM/YYYY').format('YYYY-MM-DD 23:59:59');
    }
    (new LateSuccess()).getData();
});

EventListener.dispatch.on("page-change-event", (event, callback) => {
    PostData.page_no = callback.page_number;
    (new LateSuccess()).getData();
});

function resetLateSuccess(){
    PostData.filter_data = {
        search_value: null,
        start_date:null,
        end_date:null,
    }
    PostData.page_no=1;
    PostData.limit=50;

    $('#lateSuccessForm')[0].reset();
    DzpDatePickerService.init();
    (new LateSuccess()).getData();
}
