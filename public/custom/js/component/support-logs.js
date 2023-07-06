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


(new LogsData()).getLogs();
DzpDatePickerService.init();
function LogsData() {
    this.getLogs = () => {
        FsHelper.blockUi($("#LogsData"));
        FsClient.post("/support/GetSupportLogs", PostData).then(this.handleResponse).catch(this.handleError);
    }

    this.handleResponse = (data) => {
        FsHelper.unblockUi($("#LogsData"));
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
                                    <span class="d-block font-weight-bold">${item.id ? item.id : ""}</span>
                                </td>
                                <td>
                                    <span class="d-block font-weight-bold">${item.email_id ? item.email_id : ""}</span>
                                </td>
                                <td>
                                    <span class="d-block font-weight-bold mt-1"> ${item.action ? item.action : ""}</span>
                                </td>
                                <td>
                                    <span class="font-weight-bold mt-1"> ${item.action_details ? item.action_details : ""}</span>
                                </td>
                                <td>
                                    <span class="d-block font-weight-bold mt-1"> ${item.client_ip ? item.client_ip : ""}</span>
                                </td>
                                <td>
                                    <span class="d-block font-weight-bold mt-1"> ${item.created_at_ist ? item.created_at_ist : ""}</span>
                                </td>
                            </tr>`;
            });
            $('.preLoader').hide()
            $("#LogsData").html(htmlData);
            setPaginateButton("page-change-event", PaginateData, PostData);
        } else {
            this.setErrorHtml();
        }
    }
    this.setErrorHtml = () => {
        $('.preLoader').hide();
        $('#pagination').hide();
        $("#LogsData").html(`
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

$("#logsForm").on("submit", () => {
    const FormData = getFormData($("#logsForm"));
    PostData.filter_data = {
        email_id: null,
        action: null,
        start_date:null,
        end_date:null,
    }
    PostData.filter_data[FormData.FilterKey] = FormData.FilterValue;
    PostData.filter_data.status = FormData.status;
    PostData.limit = FormData.Limit;
    PostData.page_no=1;

    if(FormData.daterange) {
        let splitDate = FormData.daterange.split(/-/);
        PostData.filter_data.start_date = moment(splitDate[0], 'DD/MM/YYYY').format('YYYY-MM-DD 00:00:00');
        PostData.filter_data.end_date = moment(splitDate[1], 'DD/MM/YYYY').format('YYYY-MM-DD 23:59:59');
    }
    (new LogsData()).getLogs();
});

EventListener.dispatch.on("page-change-event", (event, callback) => {
    PostData.page_no = callback.page_number;
    (new LogsData()).getLogs();
});

function resetlogsForm(){
    PostData.filter_data = {
        email_id: null,
        action: null,
        start_date:null,
        end_date:null,
    }
    PostData.page_no=1;
    PostData.limit=50;

    $('#logsForm')[0].reset();
    DzpDatePickerService.init();
    (new LogsData()).getLogs();
}
