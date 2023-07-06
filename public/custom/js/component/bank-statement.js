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


(new Statement()).getStatement();
DzpDatePickerService.init();
function Statement() {
    this.getStatement = () => {
        FsHelper.blockUi($("#bankStatement"));
        FsClient.post("/get-statement", PostData).then(this.handleResponse).catch(this.handleError);
    }

    this.handleResponse = (data) => {
        FsHelper.unblockUi($("#bankStatement"));
        if(data.status) {
            console.log(data.status)
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
                                    <span class="d-block font-weight-bold">${item.file_name ? item.file_name : "-"}</span>
                                </td>
                                 <td> ${item.account_detail ?`
                                    <span class="d-block font-weight-bold text-black mb-1">${item.account_detail.account_holder_name ? item.account_detail.account_holder_name : ""}</span>

                                     <span class="d-block mb-1">Number : ${item.account_detail.account_number ? item.account_detail.account_number : ""}</span>

                                     <span class="d-block  mb-1">IFSC : ${item.account_detail.ifsc_code ? item.account_detail.ifsc_code : ""}</span>

                                     <span class="d-block  mb-1">UPI : ${item.account_detail.upi_id ? item.account_detail.upi_id : ""}</span>
                                            `:' <span class="d-block  mb-1">Account Detail Getting</span>'}
                                </td>
                                <td>
                                    <span class="d-block font-weight-bold">${item.is_get ? item.is_get : "0"}</span>
                                </td>
                                <td>
                                    <span class="font-weight-bold mt-1"> ${item.is_running ? item.is_running : "0"}</span>
                                </td>
                                <td>
                                    <span class="font-weight-bold mt-1"> ${item.total_count ? item.total_count : "0"}</span>
                                </td>
                                 <td>
                                    <span class="font-weight-bold mt-1"> ${setProgressBarAttribute(item.progress ,item.total_count)}</span>
                                </td>
                                <td>
                                    <span class="font-weight-bold mt-1"> ${item.total_added_utr ? item.total_added_utr : "0"}</span>
                                </td>
                                <td>
                                    <span class="font-weight-bold mt-1"> ${item.remark ? item.remark : "-"}</span>
                                </td>
                                <td>
                                    <span class="font-weight-bold mt-1"> ${item.file_size ? item.file_size : "0"}</span>
                                </td>
                                 <td>
                                    <span class="font-weight-bold mt-1"> ${item.created_at_ist ? item.created_at_ist : "-"}</span>
                                </td>
                                <td>
                                    <span class="font-weight-bold mt-1"> ${item.updated_at_ist ? item.updated_at_ist : "-"}</span>
                                </td>
                                 <td>
                                    ${item.total_added_utr ? `<button class="btn btn-primary" onclick="showAddeUtr('${item.id}')" data-toggle="modal" data-target="#showAddedUtr">Show Add Utr</button>` : ""}
                                </td>
                            </tr>`;
            });
            $('.preLoader').hide()
            $("#statementData").html(htmlData);
            setPaginateButton("page-change-event", PaginateData, PostData);
        } else {
            this.setErrorHtml();
        }
    }
    this.setErrorHtml = () => {
        FsHelper.unblockUi($("#bankStatement"));
        $('.preLoader').hide();
        $('#pagination').hide();
        $("#statementData").html(`
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

$("#statementForm").on("submit", () => {
    const FormData = getFormData($("#statementForm"));
    PostData.filter_data = {
        start_date:null,
        end_date:null,
    }
    PostData.filter_data.is_get = FormData.is_get;
    PostData.limit = FormData.Limit;
    PostData.page_no=1;

    if(FormData.daterange) {
        let splitDate = FormData.daterange.split(/-/);
        PostData.filter_data.start_date = moment(splitDate[0], 'DD/MM/YYYY').format('YYYY-MM-DD 00:00:00');
        PostData.filter_data.end_date = moment(splitDate[1], 'DD/MM/YYYY').format('YYYY-MM-DD 23:59:59');
    }
    (new Statement()).getStatement();
});

EventListener.dispatch.on("page-change-event", (event, callback) => {
    PostData.page_no = callback.page_number;
    (new Statement()).getStatement();
});

function resetStatementForm(){
    PostData.filter_data = {
        is_get:null,
        start_date:null,
        end_date:null,
    }
    PostData.page_no=1;
    PostData.limit=50;

    $('#statementForm')[0].reset();
    DzpDatePickerService.init();
    (new Statement()).getStatement();
}

$(document).on('submit','#fileDataForm',function (e) {
    e.preventDefault();
    FsHelper.blockUi($("#fileDataForm"))
    const PostData1 = getFormData($("#fileDataForm"));
    const formData = new FormData();
    formData.append('account_file', $('input[type=file]')[0].files[0]);
    formData.append('account_number',PostData1.account_number);
    formData.append('bank_name',PostData1.bank_name);

    FsClient.post2("/upload-statement", formData)
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

$(function() {
    'use strict'

    if ($(".js-example-basic-single").length) {
        $(".js-example-basic-single").select2();
    }

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
            (new Statement()).getStatement();
            refreshCnt++;
            console.log(`Transaction Refresh: ${refreshCnt}`)
        }, 10000);
    }
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

function showAddeUtr(id){
    FsHelper.blockUi($("#showAddedUtr"));
    let UtrData = {
        id: id,
    };
    let htmlData;
    FsClient.post('/show-addedUtr',UtrData)
        .then(response => {
            let total = 0;
            let htmlData = "";
            response.data.forEach((item, index) => {
                htmlData += `<tr>
                        <td>
                            <span class="d-block font-weight-bold">${item.bank_statement_id ? item.bank_statement_id : "-"}</span>
                        </td>
                        <td>
                            <span class="d-block font-weight-bold">${item.file_name ? item.file_name : "-"}</span>
                        </td>
                        <td>
                            <span class="d-block font-weight-bold">${item.bank_utr ? item.bank_utr : "-"}</span>
                        </td>
                        <td>
                            <span class="d-block font-weight-bold">${item.amount ? item.amount : "-"}</span>
                        </td>
                        <td>
                            <span class="d-block font-weight-bold">${item.remark ? item.remark : "-"}</span>
                        </td>
                        <td>
                            <span class="d-block font-weight-bold">${item.created_at ? item.created_at : "-"}</span>
                        </td>
                    `;
                total+=item.amount
            });htmlData += `</tr><tr class="text-primary font-weight-bolder"><td class="f15">Total</td><td></td> <td></td> <td class="f15">${total.toFixed(1).replace(/\d(?=(\d{3})+\.)/g, '$&,')}</td></tr>`;
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
