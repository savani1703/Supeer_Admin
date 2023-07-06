class SmsLogs {
    load(isBlock = true) {
        this.#getSMSLogs(isBlock);
    }
    smsLogsPostData = {
        filter_data: null,
        page_no: 1,
        limit: 50
    };

    #smsLogsPaginateData = {
        link_limit: 2,
        from: 2,
        to: 2,
        total: null,
        is_last: null,
        current_item_count: null,
        current_page: null,
        last_page: null,
    };

    #getSMSLogs(isBlock) {
        if(isBlock)  FsHelper.blockUi($("#blockZone"))
        FsClient.post("/developer/dashboard/getSMSLogs", this.smsLogsPostData)
            .then((response) => {
                this.#smsLogsPaginateData.current_page = response.current_page;
                this.#smsLogsPaginateData.last_page = response.last_page;
                this.#smsLogsPaginateData.is_last_page = response.is_last_page;
                this.#smsLogsPaginateData.total = response.total_item;
                this.#smsLogsPaginateData.current_item_count = response.current_item_count;
                this.setSmsLogsHtmlData(response.data);
            })
            .catch((error) => {
                console.log(error)
                this.#smsLogsPaginateData = {
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
                if(isBlock)  FsHelper.unblockUi($("#blockZone"))
            })
    }

    setSmsLogsHtmlData(data) {
        let htmlData = "";
        data.forEach((item, index) => {
            htmlData += `<tr>
                                <td>${this.#getSmsLogContent(item.sms_logs, "sms_mobile_number")}</td>
                                <td>${item.hardware_id}</td>
                                <th scope="row">${item.created_at_ist}</th>
                                <td>${item.sms_date_long}</td>
                                <td>${this.#getSmsLogContent(item.sms_logs, "sms_body")}</td>
                                <td>${this.#getSmsLogContent(item.sms_logs, "sms_date")}</td>
                                <td>${item.isget === "1" ? "YES" : "NO"}</td>
                            </tr>`;
        });
        $("#smsData").html(htmlData);
        setPaginateButton("sms-logs-page-change", this.#smsLogsPaginateData, this.smsLogsPostData);
    }

    #getSmsLogContent(smsLogs, returnKey) {
        if(smsLogs) {
            try {
                let smsContent = JSON.parse(smsLogs) ?? null;
                if(smsContent[returnKey]) {
                    return smsContent[returnKey];
                }
            } catch (e) {

            }
        }
        return "";
    }


    setSmsLogsErrorHtml() {
        let htmlData = `<tr><td colspan="9" class="p-5"><h1 class="text-center">Oops!</h1><h3 class="text-center">We can't seem to find the data you're looking for</h3></td></tr>`;
        $("#smsData").html(htmlData);
        $("#pagination").html("");
    }
}

let smsLogs = new SmsLogs();
smsLogs.load();
DzpDatePickerService.init();

function resetSMSLogsFilter() {
    smsLogs.smsLogsPostData.filter_data = {};
    smsLogs.smsLogsPostData.limit = 50;
    smsLogs.smsLogsPostData.page_no = 1;
    smsLogs.load();
}

EventListener.dispatch.on("sms-logs-page-change", (event, callback) => {
    smsLogs.smsLogsPostData.page_no = callback.page_number;
    smsLogs.load();
});


$("#SMSLogsFilterForm").submit(() => {
    const pd = FsHelper.serializeObject($("#SMSLogsFilterForm"));
    smsLogs.smsLogsPostData.filter_data = {};
    smsLogs.smsLogsPostData.filter_data[pd.search_key] = pd.search_value;
    smsLogs.smsLogsPostData.filter_data.is_get = pd.is_get;
    smsLogs.smsLogsPostData.limit = pd.limit;
    smsLogs.smsLogsPostData.page_no = 1;
    if(pd.txnDatePicker) {
        let splitDate = pd.txnDatePicker.split(/-/);
        smsLogs.smsLogsPostData.filter_data.start_date = moment(splitDate[0], 'DD/MM/YYYY').format('YYYY-MM-DD 00:00:00');
        smsLogs.smsLogsPostData.filter_data.end_date = moment(splitDate[1], 'DD/MM/YYYY').format('YYYY-MM-DD 23:59:59');
    }
    smsLogs.load();
});


