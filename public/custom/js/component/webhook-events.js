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


(new WebhookEvents()).getEvent();

DzpDatePickerService.init();
function WebhookEvents() {
    this.getEvent = () => {
        FsHelper.blockUi($("#WebhookEvents"));
        FsClient.post("/support/GetWebhookEvents", PostData).then(this.handleResponse).catch(this.handleError);
    }

    this.handleResponse = (data) => {
        FsHelper.unblockUi($("#WebhookEvents"));
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
        console.log(data)
        if(data && data.length > 0) {
            let htmlData = "";
            data.forEach((item, index) => {
                htmlData += `<tr>
                                <td>
                                    <div class="d-flex align-items-center flex-wrap text-nowrap">
                                        <span class="d-block font-weight-bold">${item.id ? item.id : ""}</span>
                                    </div>
                                </td>
                                 <td>
                                    <span class="d-block font-weight-bold">${item.created_at_ist ? item.created_at_ist : ""}</span>
                                </td>
                                <td>
                                    <span class="d-block font-weight-bold">${item.event_id ? item.event_id : ""}</span>
                                </td>
                                <td>
                                    <span class="d-block font-weight-bold mt-1"> ${item.event_type ? item.event_type : ""}</span>
                                </td>
                                <td>
                                    <span class="font-weight-bold mt-1 ${item.webhook_status_code ? (item.webhook_status_code > 300 ? 'badge badge-danger' : 'badge badge-success') : ''}"> ${item.webhook_status_code ? item.webhook_status_code : ""}</span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary view-sent-data" data-event-id="${item.event_id ? item.event_id : ""}" data-response="${item.webhook_response ? btoa(item.webhook_response) : ""}" data-send-webhook="${item.sent_webhook_data ? btoa(item.sent_webhook_data) : ""}">View Sent Data</button>
                                </td>
                            </tr>`;
            });
            $('.preLoader').hide()
            $("#WebhookEvents").html(htmlData);
            $(".view-sent-data").click((e) => {
                let sendWebhook = e.target.attributes['data-send-webhook'].value;
                let response = e.target.attributes['data-response'].value;
                if (sendWebhook){
                    sendWebhook = atob(sendWebhook);
                    sendWebhook = JSON.parse(sendWebhook);
                    let element = $("#WebhookResponseData");
                    element.html(JSON.stringify(sendWebhook, undefined, 2));
                    $('#viewWebhookResponse').modal('show');
                }
                if (response){
                    response = atob(response);
                    let element2 = $("#sendWebhookRData");
                    let  response_1 = JSON.parse(response);
                    let  response_f = JSON.parse(response_1);
                    element2.html(JSON.stringify(response_f, undefined, 2));
                    $('#viewWebhookResponse').modal('show');
                }
                /*
                    IF YOU Need Collapse Uncommit This Code

                    jsonData = JSON.parse(jsonData);
                      $.dialog({
                      // columnClass: 'l',
                      title: eventId + ' Sent Data',
                      content:  DzpJsonViewer(jsonData, true),
                  });
              */
            });
            setPaginateButton("page-change-event", PaginateData, PostData);
        } else {
            this.setErrorHtml();
        }
    }
    this.setErrorHtml = () => {
        $('.preLoader').hide();
        $('#pagination').hide();
        $("#WebhookEvents").html(`
            <tr>
                <td colspan="7">
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

$("#eventForm").on("submit", () => {
    const FormData = getFormData($("#eventForm"));
    PostData.filter_data = {
        event_type: null,
        event_id: null,
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
    (new WebhookEvents()).getEvent();
});

EventListener.dispatch.on("page-change-event", (event, callback) => {
    PostData.page_no = callback.page_number;
    (new WebhookEvents()).getEvent();
});


function restEventForm(){
    PostData.filter_data = {
        event_type: null,
        event_id: null,
        start_date:null,
        end_date:null,
    }
    PostData.page_no=1;
    PostData.limit=50;

    $('#eventForm')[0].reset();
    DzpDatePickerService.init();
    (new WebhookEvents()).getEvent();
}
