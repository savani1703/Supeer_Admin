class webhookData {
    load(isBlock = true) {
        this.#getData(isBlock);
    }

    PostData = {
        filter_data: null,
        page_no: 1,
        limit: 50
    };

    #PaginateData = {
        link_limit: 2,
        from: 2,
        to: 2,
        total: null,
        is_last: null,
        current_item_count: null,
        current_page: null,
        last_page: null,
    };

    #getData(isBlock) {
        if (isBlock) FsHelper.blockUi($("#blockZone"))
        FsClient.post("/developer/dashboard/getidfcwebhook", this.PostData)
            .then((response) => {
                this.#PaginateData.current_page = response.current_page;
                this.#PaginateData.last_page = response.last_page;
                this.#PaginateData.is_last_page = response.is_last_page;
                this.#PaginateData.total = response.total_item;
                this.#PaginateData.current_item_count = response.current_item_count;
                this.setSmsLogsHtmlData(response.data);
            })
            .catch((error) => {
                console.log(error)
                this.#PaginateData = {
                    link_limit: 2,
                    from: 2,
                    to: 2,
                    total: null,
                    is_last: null,
                    current_item_count: null,
                    current_page: null,
                    last_page: null,
                };
                this.setSmsLogsErrorHtml()
            })
            .finally(() => {
                if (isBlock) FsHelper.unblockUi($("#blockZone"))
            })
    }

    setSmsLogsHtmlData(data) {
        let htmlData = "";
        data.forEach((item, index) => {
            htmlData += `<tr>
                                <td>${item.payout_id ? item.payout_id :"-"}</td>
                                <th>${item.payout_amount ? item.payout_amount :"-"}</th>
                                <th>${item.account_number ? item.account_number :"-"}</th>
                                <th>${item.bank_rrn ? item.bank_rrn:"-"}</th>
                                <th>${item.payment_from ? item.payment_from:"-"}</th>
                                <th>${item.bank_date ? item.bank_date :"-"}</th>
                                <th>${item.is_get ? item.is_get :"-"}</th>
                                <th>${item.is_data_sync ? item.is_data_sync:"-"}</th>
                                <th>${item.error_message ? item.error_message:"-"}</th>
                                <th>${item.created_at_ist ? item.created_at_ist:"-"}</th>
                                <th>${item.updated_at_ist ? item.updated_at_ist:"-"}</th>
                            </tr>`;
        });
        $("#WebhookData").html(htmlData);
        setPaginateButton("sms-logs-page-change", this.#PaginateData, this.PostData);
    }
    setSmsLogsErrorHtml() {
        let htmlData = `<tr><td colspan="9" class="p-5"><h1 class="text-center">Oops!</h1><h3 class="text-center">We can't seem to find the data you're looking for</h3></td></tr>`;
        $("#WebhookData").html(htmlData);
        $("#pagination").html("");
    }
}

let MailWebhook = new webhookData();
MailWebhook.load();
DzpDatePickerService.init();

function resetSMSLogsFilter() {
    MailWebhook.PostData.filter_data = {
        account_number: null,
        payout_id: null,
        bank_rrn: null,
    };
    MailWebhook.PostData.limit = 50;
    MailWebhook.PostData.page_no = 1;
    MailWebhook.load();
}

EventListener.dispatch.on("sms-logs-page-change", (event, callback) => {
    MailWebhook.PostData.page_no = callback.page_number;
    MailWebhook.load();
});


$("#FilterForm").submit(() => {
    const pd = FsHelper.serializeObject($("#FilterForm"));
    MailWebhook.PostData.filter_data = {};
    MailWebhook.PostData.filter_data[pd.search_key] = pd.search_value;
    MailWebhook.PostData.limit = pd.limit;
    MailWebhook.PostData.page_no = 1;
    if (pd.daterange) {
        let splitDate = pd.daterange.split(/-/);
        MailWebhook.PostData.filter_data.start_date = moment(splitDate[0], 'DD/MM/YYYY').format('YYYY-MM-DD 00:00:00');
        MailWebhook.PostData.filter_data.end_date = moment(splitDate[1], 'DD/MM/YYYY').format('YYYY-MM-DD 23:59:59');
    }
    MailWebhook.load();
});


