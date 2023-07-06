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


(new PgWebhooks()).getWebhooks();

DzpDatePickerService.init();
function PgWebhooks() {
    this.getWebhooks = () => {
        FsHelper.blockUi($("#webhookTbl"));
        FsClient.post("/support/GetPgWebhooks", PostData).then(this.handleResponse).catch(this.handleError);
    }

    this.handleResponse = (data) => {
        FsHelper.unblockUi($("#webhookTbl"));
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
        console.log(data)
        if(data && data.length > 0) {
            let htmlData = "";
            data.forEach((item, index) => {
                htmlData += `<tr>
                                 <td>
                                    <span class="d-block">${item.id ? item.id : ""}</span>
                                </td>
                                 <td>
                                    <span class="d-block">${item.transaction_id ? item.transaction_id : ""}</span>
                                </td>
                                <td>
                                    <span class="d-block">${item.pg_id ? item.pg_id : "0"}</span>
                                </td>
                                <td>
                                    <span class="d-block">${item.is_get ? item.is_get : "0"}</span>
                                </td>

                                <td>
                                    <span class="d-block"> ${item.created_at_ist ? item.created_at_ist : ""}</span>
                                </td>
                                <td>
                                    <span class="d-block"> ${item.updated_at_ist ? item.updated_at_ist : ""}</span>
                                </td>
                                 <td>
                                    <button class="btn btn-sm btn-primary btn-show-data" data-formd="${btoa(item.pg_res)}">Show Responce</button>
                                 </td>
                            </tr>`;
            });
            $('.preLoader').hide()
            $("#PgWebhooksData").html(htmlData);
            setPaginateButton("page-change-event", PaginateData, PostData);
            $(".btn-show-data").click((e) => {
                let fd = e.target.attributes['data-formd'].value;
                fd = atob(fd);
                fd = JSON.parse(fd);
                $.dialog({
                    columnClass: 'l',
                    title: ' Bouncer Form Data',
                    content:  DzpJsonViewer(fd, true),
                });
            })

        } else {
            this.setErrorHtml();
        }
    }
    this.setErrorHtml = () => {
        $('.preLoader').hide();
        FsHelper.unblockUi($("#webhookTbl"));
        $('#pagination').hide();
        $("#PgWebhooksData").html(`
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
}

$("#webhookForm").on("submit", () => {
    const FormData = getFormData($("#webhookForm"));
    PostData.filter_data = {
        is_get: null,
        pg_id: null,
        transaction_id: null,
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
    (new PgWebhooks()).getWebhooks();
});

EventListener.dispatch.on("page-change-event", (event, callback) => {
    PostData.page_no = callback.page_number;
    (new PgWebhooks()).getWebhooks();
});


function resetWebhookForm(){
    PostData.filter_data = {
        is_get: null,
        pg_id: null,
        transaction_id: null,
        start_date:null,
        end_date:null,
    }
    PostData.page_no=1;
    PostData.limit=50;

    $('#webhookForm')[0].reset();
    DzpDatePickerService.init();
    (new PgWebhooks()).getWebhooks();
}
