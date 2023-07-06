
let mKey = localStorage.getItem('MFilterKey');
let  MerchantId=atob(mKey);

let PostData = {
    page_no: 1,
    limit: 50,
    MerchantId:MerchantId,
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



(new DashboardLogs()).getLogs();

function DashboardLogs() {
    this.getLogs = () => {
        FsHelper.blockUi($("#DashboardLogs"));
        FsClient.post("/dashboard-logs", PostData).then(this.handleResponse).catch(this.handleError);
    }

    this.handleResponse = (data) => {
        FsHelper.unblockUi($("#DashboardLogs"));
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
                                    <span class="d-block font-weight-bold mt-1"> ${item.action_type ? item.action_type : ""}</span>
                                </td>
                                <td>
                                    <span class="font-weight-bold mt-1"> ${item.action ? item.action : ""}</span>
                                </td>
                                <td>
                                    <span class="d-block font-weight-bold mt-1"> ${item.request_ip ? item.request_ip : ""}</span>
                                </td>
                                <td>
                                    <span class="d-block font-weight-bold mt-1"> ${item.user_agent ? item.user_agent : ""}</span>
                                </td>
                                <td>
                                    <span class="d-block font-weight-bold mt-1"> ${item.created_at ? item.created_at : ""}</span>
                                </td>
                            </tr>`;
            });
            $('.preLoader').hide()
            $("#DashboardLogs").html(htmlData);
            setPaginateButton("txn-logs-page-change-event", PaginateData, PostData);
        } else {
            this.setErrorHtml();
        }
    }
    this.setErrorHtml = () => {
        $('.preLoader').hide();
        $('#pagination').hide();
        $("#DashboardLogs").html(`
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

EventListener.dispatch.on("txn-logs-page-change-event", (event, callback) => {
    PostData.page_no = callback.page_number;
    (new DashboardLogs()).getLogs();
});

