class MailReader {
    load(isBlock = true) {
        this.#getSMSLogs(isBlock);
    }
    mailReaderPostData = {
        filter_data: null,
        page_no: 1,
        limit: 50
    };

    #mailReaderPaginateData = {
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
        FsClient.post("/developer/dashboard/GetMailReader", this.mailReaderPostData)
            .then((response) => {
                this.#mailReaderPaginateData.current_page = response.current_page;
                this.#mailReaderPaginateData.last_page = response.last_page;
                this.#mailReaderPaginateData.is_last_page = response.is_last_page;
                this.#mailReaderPaginateData.total = response.total_item;
                this.#mailReaderPaginateData.current_item_count = response.current_item_count;
                this.setSmsLogsHtmlData(response.data);
            })
            .catch((error) => {
                console.log(error)
                this.#mailReaderPaginateData = {
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
                                <td>
                                <span class="d-block font-weight-bold mb-1"><span class="text-muted">Bank ID</span>: ${item.av_bank_id}</span>
                                <span class="d-block font-weight-bold mb-1"><span class="text-muted">Label</span>: ${item.bank_details ? item.bank_details.account_holder_name : "-"}</span>
                                <span class="d-block font-weight-bold mb-1"><span class="text-muted">A/C</span>: ${item.bank_details ? item.bank_details.account_number : "-"}</span>
                                <span class="d-block font-weight-bold mb-1"><span class="text-muted">UPI</span>: ${item.bank_details ? item.bank_details.upi_id : "-"}</span>
                                <span class="d-block font-weight-bold mb-1"><span class="text-muted">IFSC</span>: ${item.bank_details ? item.bank_details.ifsc_code : "-"}</span>
                                <span class="d-block font-weight-bold mb-1"><span class="text-muted">Bank</span>: ${item.bank_details ? item.bank_details.bank_name : "-"}</span>
                                </td>
                                <td>${item.username}</td>
                                <td>${item.mail_sender}</td>
                                <td>${item.mail_from}</td>
                                <td>${item.provider}</td>
                                <td>
                                    <a href="#" class="mail-reader-editable"
                                       data-type="select"
                                       data-value="${item.is_active ? " Yes" : " No"}"
                                       data-source="[{value: '1',text: 'YES'},{value: '0',text: 'NO'},]"
                                       data-pk="${item.av_bank_id}"
                                       data-url="/developer/dashboard/UpdateMailReaderStatus"
                                    >${item.is_active ? "Yes" : "No"}</a>
                                </td>
                                <td>
                                    <span class="d-block"><span class="text-muted">Create: </span>${item.created_at_ist}</span>
                                    <span class="d-block"><span class="text-muted">Update: </span>${item.updated_at_ist ? item.updated_at_ist : "-"}</span>
                                </td>
                            </tr>`;
        });
        $("#smsData").html(htmlData);
        $('.mail-reader-editable').editable({
            success: function(response, newValue) {
                // console.log(response);
                toastr.success(response.message,"success",toastOption);
                (new MailReader()).load();
            },
            error: function(error) {
                // console.log(error);
                toastr.error(error.responseJSON.message,"error", toastOption);
                (new MailReader()).load();
            }
        });
        setPaginateButton("mail-reader-page-change", this.#mailReaderPaginateData, this.mailReaderPostData);
    }

    setSmsLogsErrorHtml() {
        let htmlData = `<tr><td colspan="9" class="p-5"><h1 class="text-center">Oops!</h1><h3 class="text-center">We can't seem to find the data you're looking for</h3></td></tr>`;
        $("#smsData").html(htmlData);
        $("#pagination").html("");
    }
}

let mailReader = new MailReader();
mailReader.load();
DzpDatePickerService.init();

function resetMailReaderFilter() {
    mailReader.mailReaderPostData.filter_data = {};
    mailReader.mailReaderPostData.limit = 50;
    mailReader.mailReaderPostData.page_no = 1;
    mailReader.load();
}

EventListener.dispatch.on("mail-reader-page-change", (event, callback) => {
    mailReader.mailReaderPostData.page_no = callback.page_number;
    mailReader.load();
});


$("#MaileReaderFilterForm").submit(() => {
    const pd = FsHelper.serializeObject($("#MaileReaderFilterForm"));
    mailReader.mailReaderPostData.filter_data = {};
    mailReader.mailReaderPostData.filter_data[pd.search_key] = pd.search_value;
    mailReader.mailReaderPostData.limit = pd.limit;
    mailReader.mailReaderPostData.page_no = 1;
    if(pd.txnDatePicker) {
        let splitDate = pd.txnDatePicker.split(/-/);
        smsLogs.smsLogsPostData.filter_data.start_date = moment(splitDate[0], 'DD/MM/YYYY').format('YYYY-MM-DD 00:00:00');
        smsLogs.smsLogsPostData.filter_data.end_date = moment(splitDate[1], 'DD/MM/YYYY').format('YYYY-MM-DD 23:59:59');
    }
    mailReader.load();
});

function addMailReader() {
    const htmlContent = `<form class="form" action="javascript:void(0)" id="addMailReaderForm">
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="">Bank ID</label>
                                    <input type="text" name="bank_id" class="form-control">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="">Username</label>
                                    <input type="text" name="username" class="form-control">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="">Password</label>
                                    <input type="text" name="password" class="form-control">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="">Mail Sender</label>
                                    <input type="text" name="mail_sender" class="form-control">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="">Mail From</label>
                                    <input type="text" name="mail_from" class="form-control">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="">Provider</label>
                                    <input type="text" name="provider" class="form-control">
                                </div>
                            </div>
                        </form>`;
    $.confirm({
        title: `Add Mail Reader`,
        content: htmlContent,
        buttons: {
            formSubmit: {
                text: 'Submit',
                btnClass: 'btn-blue',
                action: function () {
                    const payload = getFormData($("#addMailReaderForm "));
                    FsHelper.blockUi($("#addMailReaderForm"))
                    FsClient.post("/developer/dashboard/AddMailReader", payload)
                        .then(response => {
                            toastr.success(response.message,"success",toastOption);
                            mailReader.load();
                        })
                        .catch(error => {
                            toastr.error(error.responseJSON.message,"error", toastOption);
                        })
                        .finally(() => {
                            FsHelper.unblockUi($("#addMailReaderForm"))
                        })
                }
            },
            cancel: function () {
                FsHelper.unblockUi($("#Merchant_page"));
                return true;
            },
        },
        onContentReady: function () {
            // bind to events
            var jc = this;
            this.$content.find('form').on('submit', function (e) {
                e.preventDefault();
                jc.$$formSubmit.trigger('click');
            });
        }
    });
}


