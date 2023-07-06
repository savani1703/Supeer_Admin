let PostData = {
    filter_data: {
        start_date:null,
        end_date:null,
    },
    page_no: 1,
    limit: 10,
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

const status = {
    Success: 'badge-success',
    Failed: 'badge-danger',
    Initialized: 'badge-warning',
    Pending: 'badge-outlineprimary',
    LOWBAL: 'badge-secondary',
    Cancelled: 'badge-secondary',
};

(new PayoutManualRecon()).getReconData();
(new PayoutManualRecon()).getReconSummeryData();

DzpDatePickerService.init();
function PayoutManualRecon() {
    this.getReconData = () => {
        FsHelper.blockUi($("#PayoutManualRecon"));
        FsClient.post("/get/payout-manual-recon", PostData).then(this.handleResponse).catch(this.handleError);
    }

    this.handleResponse = (data) => {
        FsHelper.unblockUi($("#PayoutManualRecon"));
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
                let PayoutStatus = status[item.payout_details.payout_status];
                htmlData += `<tr>
                                <td>
                                    <span class="d-block font-weight-bold">${item.payout_id ? item.payout_id : ""}</span>
                                </td>
                                <td>
                                    <span class="d-block font-weight-bold">${item.payout_details.payout_amount ? item.payout_details.payout_amount : "-"}</span>
                                </td>
                                <td>
                                    <span class="d-block font-weight-bold">${item.merchant_id ? item.merchant_id : ""}</span>
                                     <span class="d-block mt-1">${item.merchant_detail.merchant_name ? item.merchant_detail.merchant_name : ""}</span>

                                </td>
                                <td>
                                    <span class="font-weight-bold badge ${PayoutStatus} mt-1"> ${item.payout_details.payout_status ? item.payout_details.payout_status : ""}</span>
                                </td>
                                <td>
                                    <span class="d-block">${item.payout_details.pg_label ? item.payout_details.pg_label : ""}</span>
                                    <span class="d-block mt-1">${item.payout_details.pg_name ? item.payout_details.pg_name : ""} ${item.payout_details.meta_id ? `(${item.payout_details.meta_id})` : ""}</span>
                                    <span class="d-block"><span>Pay Batch @ :</span>${item.manual_pay_batch_id ? item.manual_pay_batch_id : ""}</span>
                                </td>
                                <td>
                                    <span class="font-weight-bold mt-1"> ${item.is_solved ? item.is_solved : ""}</span>
                                </td>
                                <td>
                                    <span class="d-block font-weight-bold mt-1"> ${item.payout_details.created_at_ist ? item.payout_details.created_at_ist : ""}</span>
                                </td>
                                <td>
                                    <span class="d-block font-weight-bold mt-1"> ${item.created_at_ist ? item.created_at_ist : ""}</span>
                                </td>
                            </tr>`;
            });
            $('.preLoader').hide()
            $("#payoutReconData").html(htmlData);
            setPaginateButton("page-change-event", PaginateData, PostData);
        } else {
            this.setErrorHtml();
        }
    }
    this.setErrorHtml = () => {
        FsHelper.unblockUi($("#PayoutManualRecon"));
        $('.preLoader').hide();
        $('#pagination').hide();
        $("#payoutReconData").html(`
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
    this.getReconSummeryData = () => {
        FsHelper.blockUi($("#PayoutManualRecon"));
        FsClient.post("/get/payout-manual-recon/summery", PostData).then(this.setPayoutRecSummaryHtml).catch(this.handleError);
    }
    this.setPayoutRecSummaryHtml = (data) => {
        if(data.payout_summary) {
            $.each(data.payout_summary, (index, item) => {
                $("#__"+index).text(parseFloat(item).toFixed(2));
            })
        }
    }
}

$("#payoutReconForm").on("submit", () => {
    const FormData = getFormData($("#payoutReconForm"));
    PostData.filter_data = {
        payout_id: null,
        merchant_id: null,
        is_solved: null,
        manual_pay_batch_id: null,
        start_date:null,
        end_date:null,
    }
    PostData.filter_data[FormData.FilterKey] = FormData.FilterValue;
    PostData.filter_data.is_solved = FormData.is_solved;
    PostData.limit = FormData.Limit;
    PostData.page_no=1;

    if(FormData.daterange) {
        let splitDate = FormData.daterange.split(/-/);
        PostData.filter_data.start_date = moment(splitDate[0], 'DD/MM/YYYY').format('YYYY-MM-DD 00:00:00');
        PostData.filter_data.end_date = moment(splitDate[1], 'DD/MM/YYYY').format('YYYY-MM-DD 23:59:59');
    }
    (new PayoutManualRecon()).getReconData();
    (new PayoutManualRecon()).getReconSummeryData();
});

EventListener.dispatch.on("page-change-event", (event, callback) => {
    PostData.page_no = callback.page_number;
    (new PayoutManualRecon()).getReconData();
});

function resteManualPayoutForm(){
    PostData.filter_data = {
        payout_id: null,
        merchant_id: null,
        manual_pay_batch_id: null,
        is_solved: null,
        start_date:null,
        end_date:null,
    }
    PostData.page_no=1;
    PostData.limit=50;

    $('#payoutReconForm')[0].reset();
    DzpDatePickerService.init();
    (new PayoutManualRecon()).getReconData();
    (new PayoutManualRecon()).getReconSummeryData();
}


// -------------------------------------------PAYOUT STATEMENT SECTION ---------------------------------------------





let StatementPostData = {
    filter_data: {
        start_date:null,
        end_date:null,
    },
    page_no: 1,
    limit:10,
    PaginateId:'StatementPagination'
};

let StatementPaginateData = {
    link_limit: 2,
    from: 2,
    to: 2,
    total: null,
    is_last: null,
    current_item_count: null,
    current_page: null,
    last_page: null,
};


(new PayoutStatement()).getstatement();
DzpDatePickerService.init();
function PayoutStatement() {
    this.getstatement = () => {
        FsHelper.blockUi($("#PayoutStatement"));
        FsClient.post("/get/payout-statement", StatementPostData).then(this.handleResponse).catch(this.handleError);
    }

    this.handleResponse = (data) => {
        FsHelper.unblockUi($("#PayoutStatement"));
        if(data.status) {
            StatementPaginateData.current_page = data.current_page;
            StatementPaginateData.last_page = data.last_page;
            StatementPaginateData.is_last_page = data.is_last_page;
            StatementPaginateData.total = data.total_item;
            StatementPaginateData.current_item_count = data.current_item_count;
            this.setTxnHtmlData(data.data);
            $('#StatementPagination').show();
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
                                <td><span class="d-block font-weight-bold">${item.id ? item.id : ""}</span> </td>
                                <td><span class="d-block font-weight-bold">${item.file_name ? item.file_name : ""}</span> </td>
                                <td>
                                    <span class="d-block font-weight-bold"> <span class="text-muted font-weight-bold">Ac No : </span>  ${item.account_number ? item.account_number : ""}</span>
                                     ${item.acount_detail ?`
                                    <span class="d-block font-weight-bold"> <span class="text-muted font-weight-bold">Ac Name : </span>  ${item.acount_detail.label ? item.acount_detail.label : ""}</span>
                                    <span class="d-block font-weight-bold"> <span class="text-muted font-weight-bold">Ac Id : </span>  ${item.acount_detail.account_id ? item.acount_detail.account_id : ""}</span>
                                    <span class="d-block font-weight-bold"> <span class="text-muted font-weight-bold">Ac Code : </span>  ${item.acount_detail.bank_code ? item.acount_detail.bank_code : ""}</span>
                                     `:' <span class="d-block  mb-1">Account Detail Getting</span>'}
                                </td>
                                <td><span class="d-block font-weight-bold">${item.is_get ? item.is_get : ""}</span> </td>
                                <td><span class="d-block font-weight-bold">${item.is_running ? item.is_running : ""}</span> </td>
                                <td><span class="d-block font-weight-bold">${item.total_count ? item.total_count : ""}</span> </td>
                                <td><span class="d-block font-weight-bold"> ${setProgressBarAttribute(item.progress ,item.total_count)}</span> </td>
                                <td><span class="d-block font-weight-bold">${item.total_added_utr ? item.total_added_utr : ""}</span> </td>
                                <td><span class="d-block font-weight-bold">${item.remark ? item.remark : ""}</span> </td>
                                <td><span class="d-block font-weight-bold">${item.file_size ? item.file_size : ""}</span> </td>
                                <td><span class="d-block font-weight-bold">${item.created_at_ist ? item.created_at_ist : ""}</span> </td>
                                <td>
                                    ${item.total_added_utr ? `<button class="btn btn-primary" onclick="showAddeUtr('${item.file_name}')" data-toggle="modal" data-target="#showAddedUtr">Show Add Utr</button>` : ""}
                                </td>
                            </tr>`;
            });
            $('.preLoader').hide()
            $("#PayoutStatementData").html(htmlData);
            setPaginateButton("payout-page-change-event", StatementPaginateData, StatementPostData,StatementPostData.PaginateId);
        } else {
            this.setErrorHtml();
        }
    }
    this.setErrorHtml = () => {
        FsHelper.unblockUi($("#PayoutStatement"));
        $('.preLoader').hide();
        $('#StatementPagination').hide();
        $("#PayoutStatementData").html(`
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

$("#PayoutStatementForm").on("submit", () => {
    const StatementFormData = getFormData($("#PayoutStatementForm"));
    StatementPostData.filter_data = {
        start_date:null,
        end_date:null,
    }
    StatementPostData.page_no=1;
    StatementPostData.limit = StatementFormData.Limit;

    if(StatementFormData.daterange) {
        let splitDate = StatementFormData.daterange.split(/-/);
        StatementPostData.filter_data.start_date = moment(splitDate[0], 'DD/MM/YYYY').format('YYYY-MM-DD 00:00:00');
        StatementPostData.filter_data.end_date = moment(splitDate[1], 'DD/MM/YYYY').format('YYYY-MM-DD 23:59:59');
    }
    (new PayoutStatement()).getstatement();
});

// EventListener.dispatch.on("page-change-event", (event, callback) => {
//     StatementPostData.page_no = callback.page_number;
//     (new PayoutStatement()).getstatement();
// });

function resetPayoutStatementForm(){
    StatementPostData.filter_data = {
        start_date:null,
        end_date:null,
    }
    StatementPostData.page_no=1;
    StatementPostData.limit=10;

    $('#PayoutStatementForm')[0].reset();
    DzpDatePickerService.init();
    (new PayoutStatement()).getstatement();
}


function setProgressBarAttribute(progress,count){
    if (progress < 0 ){
        return `-`;
    }
    let todata = ( progress/ count) * 100;
    $totalData=todata?todata:'0';
    return `<div class="progress mt-1">
                <div class="progress-bar progress-bar-striped ${progress !==count ? 'progress-bar-animated' :'active'}" role="progressbar" style="width: ${todata}%">
                     ${$totalData > 100 ? 100 :$totalData}%
                </div>
            </div>`
}

function showAddeUtr(file_name){
    FsHelper.blockUi($("#showAddedUtr"));
    let UtrData = {
        file_name: file_name,
    };
    let htmlData;
    FsClient.post('/get/added-utr',UtrData)
        .then(response => {
            let total = 0;
            let htmlData = "";
            response.data.forEach((item, index) => {
                htmlData += `<tr>
                        <td>
                            <span class="d-block font-weight-bold">${item.payout_id ? item.payout_id : "-"}</span>
                        </td>
                        <td>
                            <span class="d-block font-weight-bold">${item.merchant_id ? item.merchant_id : "-"}</span>
                        </td>
                        <td>
                            <span class="d-block font-weight-bold">${item.manual_pay_batch_id ? item.manual_pay_batch_id : "-"}</span>
                        </td>
                        <td>
                            <span class="d-block font-weight-bold">${item.bank_statement_id ? item.bank_statement_id : "-"}</span>
                        </td>
                        <td>
                            <span class="d-block font-weight-bold">${item.file_name ? item.file_name : "-"}</span>
                        </td>
                        <td>
                            <span class="d-block font-weight-bold">${item.is_solved ? item.is_solved : "-"}</span>
                        </td>
                        <td>
                            <span class="d-block font-weight-bold">${item.created_at ? item.created_at : "-"}</span>
                        </td>
                    `;
            });
            $("#addedUtrData").html(htmlData);

        }).catch(error => {
        htmlData+=`
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
            `;
        $("#addedUtrData").html(htmlData);
    })
        .finally(() => {
            FsHelper.unblockUi($("#showAddedUtr"));
        })
}

$(document).on('submit','#fileDataForm',function (e) {
    e.preventDefault();
    FsHelper.blockUi($("#fileDataForm"))
    const PostData1 = getFormData($("#fileDataForm"));
    const formData = new FormData();
    formData.append('account_file', $('input[type=file]')[0].files[0]);
    formData.append('account_number',PostData1.account_number);
    formData.append('bank_name',PostData1.bank_name);

    FsClient.post2("/payout/upload-statement", formData)
        .then(res => {
            toastr.success(res.message, "success", options);
            $("#fileDataForm")[0].reset();
            $("#close_btn").click();
            (new Statement()).getStatement();
        })
        .catch(err => {
            toastr.error(err.responseJSON.message,"error", toastOption);
            console.log(err);
        })
        .finally(() => {
            FsHelper.unblockUi($("#fileDataForm"))
        });
});


let autoRefreshInterval = null;
let refreshCnt = 0;
$("#refreshTitle").html("Auto Refresh");
function autoRefreshStatement() {
    $("#refreshTitle").html("Auto Refresh Off");
    if($("#autRefreshBtn").hasClass("active")) {
        $("#autRefreshBtn").removeClass("active");
        clearInterval(autoRefreshInterval);
        console.log(`Transaction Refresh Reset`)
        refreshCnt = 0;
    } else {
        $("#refreshTitle").html("Auto Refresh On");
        $("#autRefreshBtn").addClass("active");
        autoRefreshInterval = setInterval(() => {
            (new PayoutStatement()).getstatement();
            refreshCnt++;
            console.log(`Transaction Refresh: ${refreshCnt}`)
        }, 2000);
    }
}

EventListener.dispatch.on("payout-page-change-event", (event, callback) => {
    StatementPostData.page_no = callback.page_number;
    (new PayoutStatement()).getstatement();
});
