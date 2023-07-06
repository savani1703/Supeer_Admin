let BatchTPostData = {
    filter_data: null,
    page_no: 1,
    limit: 50,
};
let BatchTPaginateData = {
    link_limit: 2,
    from: 2,
    to: 2,
    total: null,
    is_last: null,
    current_item_count: null,
    current_page: null,
    last_page: null,
};

(new BatchTransfer()).getBatchTransfer();
(new BatchTransfer()).getPayoutConfig();
function BatchTransfer() {
    this.getBatchTransfer = () => {
        // /get/manual-payout/list
        FsHelper.blockUi($("#manualPayoutZone"));
        FsClient.post("/get/manual-payout/list", BatchTPostData)
            .then(response => {
                BatchTPaginateData.current_page = response.current_page;
                BatchTPaginateData.last_page = response.last_page;
                BatchTPaginateData.is_last_page = response.is_last_page;
                BatchTPaginateData.total = response.total_item;
                BatchTPaginateData.current_item_count = response.current_item_count;
                this.setBatchTransferHtmlData(response.data);
            })
            .catch(error => {
                console.log(error);
            })
            .finally(() => {
                FsHelper.unblockUi($("#manualPayoutZone"));
            });
    }

    this.getAccountLoad = () => {
        FsHelper.blockUi($("#accountLoad"));
        FsClient.post("/get/payout/account/load",null).then(this.ResponseModal).catch(this.setErrorHtml2);
    }

    this.setBatchTransferHtmlData = (data) => {
        if(data) {
            let htmlData = "";
            data.forEach((item, index) => {
                htmlData += `<tr>
                                <td>${item.created_at_ist}</td>
                                <td>${item.batch_id}
                                <span>${getMerchantName(item.merchantList)}</span>
                                </td>
                                <td>
                                    <span class="d-block">Bank: ${item.bank_name}</span>
                                    <span class="d-block">A/C: ${item.debit_account}</span>
                                    <span class="d-block">Label : ${item.account_holder}</span>
                                </td>
                                <td>₹ ${item.payout_amount}</td>
                                <td>
                                    <span class="d-block pb-1"> <b class="text-muted">Total Record :</b> ${item.payout_record ? item.payout_record : "0"}</span>
                                    <span class="d-block pb-1"> <b class="text-muted">Success:</b> ${item.Count.total_success_payout ? item.Count.total_success_payout : "0"}</span>
                                    <span class="d-block pt-1"> <b class="text-muted">Pending:</b> ${item.Count.total_Pending_payout ? item.Count.total_Pending_payout : "0"}</span>
                                    <span class="d-block pt-1"> <b class="text-muted">Processing:</b> ${item.Count.total_Processing_payout ? item.Count.total_Processing_payout : "0"}</span>
                                    <span class="d-block pt-1"> <b class="text-muted">Initialized :</b> ${item.Count.total_Initialized_payout ? item.Count.total_Initialized_payout : "0"}</span>
                                    <span class="d-block pt-1"> <b class="text-muted">Failed:</b> ${item.Count.total_Failed_payout ? item.Count.total_Failed_payout : "0"}</span>
                                    <span class="d-block pt-1"> <b class="text-muted">Total Return:</b> ${item.total_return ? item.total_return : "0"}</span>
                                </td>
                                <td>
                                    <span class="d-block"><small>${item.file_data ? item.file_name : "Pending"}</small></span>
                                </td>
                                <td>${getDownloadBtnAtr(item.batch_id, item.mark_as_used)}</td>
                                <td>${getBtnAtr(item.batch_id, item.mark_as_used)}</td>
                            </tr>`;
            })
            $("#ManualPayoutData").html(htmlData);
            setPaginateButton("page-change-event", BatchTPaginateData, BatchTPostData);
            $(".btn-batch-file-download").click(e => {
                const batchId = e.target.attributes['data-batch'].value;
                this.downloadBatchFile(batchId);
            });
            $(".btn-batch-file-mark-as-used").click(e => {
                const batchId = e.target.attributes['data-batch'].value;
                this.markAsUsedBatchFile(batchId);
            });
        }
    }

    this.downloadBatchFile = (batchId) => {
        FsHelper.blockUi($("#manualPayoutZone"));
        const pd = {batch_id: batchId};

        FsClient.post("/download/batch/file", pd)
            .then(res => {
                if(res.data){
                    if(res.data.is_seamless){
                        downloadSeamless(res.data.file_data);
                    }
                }
                download(res.data.file_name, atob(res.data.file_data));
                (new BatchTransfer()).getBatchTransfer();
            })
            .catch(err => {
                FsHelper.unblockUi($("#manualPayoutZone"));
            })
            .finally(() => {
                FsHelper.unblockUi($("#manualPayoutZone"));
            });
    }

    this.uploadStatusFile = (fd) => {
        FsHelper.blockUi($("#ManualPayoutStatusForm"));

        $.ajax({
            url : '/upload/status/file',
            type : 'POST',
            data : fd,
            processData: false,
            contentType: false,
            success : function(data) {
                FsHelper.unblockUi($("#ManualPayoutStatusForm"))
                if(data.data.success.length > 0) {
                    let successHtmlData = "";
                    data.data.success.forEach((item, index) => {
                        successHtmlData += `<tr><td>${item.payout_id}</td>
                                            <td>₹ ${item.payout_amount}</td>
                                            <td>${item.payout_status}</td>
                                            <td>${item.bank_rrn}</td>
                                            <td>${item.created_at_ist}</td></tr>`;
                    });
                    $("#payoutSuccessOperationData").html(successHtmlData);
                } else {
                    $("#payoutSuccessOperationData").html(`<tr><td class="text-center" colspan="5">No Success Operation</td></tr>`);
                }
                if(data.data.error.length > 0) {
                    let errorHtmlData = "";
                    data.data.error.forEach((item, index) => {
                        errorHtmlData += `<tr><td>${item}</td></tr>`;
                    })
                    $("#payoutErrorOperationData").html(errorHtmlData);
                } else {
                    $("#payoutErrorOperationData").html(`<tr><td class="text-center">No Error</td></tr>`);
                }
                $("#payoutSuccessOperationZone").show();
                console.log(data);
            },
            error: function (error) {
                FsHelper.unblockUi($("#ManualPayoutStatusForm"))
                console.log(error)
            }
        });

    }

    this.getPayoutConfig = (isLoading = false) => {
       if(isLoading) FsHelper.blockUi($("#updateBankTransferConfigForm"));

        FsClient.post("/get/config", "")
            .then(res => {
                const data = res.data;
                $.each(data, (index, item) => {
                    if(index === "is_auto_transfer_enable") {
                        $(`#${index}`).prop("checked", item)
                    } else {
                        $(`#${index}`).val(item)
                    }
                })
            })
            .catch(err => {
                toastr.error(err.responseJSON.message, "Error", options);
                console.log(err);
            })
            .finally(() => {
                if(isLoading) FsHelper.unblockUi($("#updateBankTransferConfigForm"))
            });
    }

    this.setPayoutConfig = (postData) => {
        FsHelper.blockUi($("#updateBankTransferConfigForm"));
        FsClient.post("/set/config", postData)
            .then(res => {
                this.getPayoutConfig(true);
                toastr.success(res.message, "Success", options);
            })
            .catch(err => {
                toastr.error(err.responseJSON.message, "Error", options);
                console.log(err);
            })
            .finally(() => {
                FsHelper.unblockUi($("#updateBankTransferConfigForm"))
            });
    }

    this.getPayoutInitAmount = () => {
        FsHelper.blockUi($("#addManualPayoutForm"));
        FsClient.post("/payout/inti/total", "")
            .then(res => {
                $("#payoutInitAmountText").text(`Total Init Amount: ${res.payout_amount ?
                    (res.payout_amount).toLocaleString('en-US', {
                        style: 'currency',
                        currency: 'INR',
                    })
                    : 0}`);
            })
            .catch(err => {
                console.log(err);
            })
            .finally(() => {
                FsHelper.unblockUi($("#addManualPayoutForm"))
            });
    }
    this.markAsUsedBatchFile = (batchId) => {
        FsHelper.blockUi($("#payout_page"));
        var myModal =  new jBox('Confirm', {
            confirmButton: 'YES',
            cancelButton: 'No',
            content: `Are You Sure Do you want to mark as complete ?`,
            confirm: function () {
                let postData = {
                    batch_id: batchId,
                }
                FsClient.post("/mark-as-used/manual-payout", postData).then(
                    Response => {
                        toastr.success(Response.message, "success", toastOption);
                        FsHelper.unblockUi($("#payout_page"));
                        (new BatchTransfer()).getBatchTransfer();
                    }
                ).catch(Error=>{
                    toastr.error(Error.responseJSON.message, "error", toastOption);
                    FsHelper.unblockUi($("#payout_page"));
                    (new BatchTransfer()).getBatchTransfer();
                });
            },
            cancel : function (){
                FsHelper.unblockUi($("#payout_page"));
            }
        });
        myModal.open();
    }
    this.getMerchantPayoutInitAmount = (merchant_id) => {
        FsHelper.blockUi($("#addManualPayoutForm"));
        let postData = {
            merchant_id: merchant_id,
        }
        FsClient.post("/payout/init/total/merchant", postData)
            .then(res => {
                $("#payoutInitAmountText").text(`Total Init Amount: ${res.data.total_init_amount ?
                    (res.data.total_init_amount).toLocaleString('en-US', {
                        style: 'currency',
                        currency: 'INR',
                    })
                    : 0}`
                );
                $("#initCountText").text(`Total Init Count : ${res.data.total_init_count ? "( "+res.data.total_init_count + " )" : ''}`);
                $("#payout_count").val(`${res.data.total_init_count ? +res.data.total_init_count  : '' }`);
                FsHelper.unblockUi($("#addManualPayoutForm"))
            })
            .catch(err => {
                FsHelper.unblockUi($("#addManualPayoutForm"))
                console.log(err);
            })
            .finally(() => {
                FsHelper.unblockUi($("#addManualPayoutForm"))
            });
    }
    this.getLogicalInitPayoutDetails = (merchant_id, login_key, logic_amount) => {
        FsHelper.blockUi($("#addManualPayoutForm"));
        let postData = {
            merchant_id: merchant_id,
            login_key: login_key,
            logic_amount: logic_amount,
        }
        FsClient.post("/payout/init/logical/total/merchant", postData)
            .then(res => {
                $("#total_logical_init_count").text(`Total Count : ${res.data.total_logical_init_count}`);
                $("#total_logical_init_amount").text(`Total Amount: ${res.data.total_logical_init_amount ?
                    (res.data.total_logical_init_amount).toLocaleString('en-US', {
                        style: 'currency',
                        currency: 'INR',
                    })
                    : 0}`
                );
                FsHelper.unblockUi($("#addManualPayoutForm"))
            })
            .catch(err => {
                FsHelper.unblockUi($("#addManualPayoutForm"))
                $("#payout_count").val();
            })
            .finally(() => {
                FsHelper.unblockUi($("#addManualPayoutForm"));
                $("#payout_count").val();
            });
    }
    this.ResponseModal = (data) => {
        FsHelper.unblockUi($("#accountLoad"));
        if (data.status) {
            let onlyData = data.data;
            if(onlyData) {
                let htmlData = "";
                $.each(onlyData, (index, item) => {
                    htmlData += `<tr">
                                    <td>
                                        <span class="d-block font-weight-bold mt-1"> ${item.label ? item.label : ""}</span>
                                        <span class="d-block font-weight-bold mt-1"> ${item.pg_name ? item.pg_name : ""}</span>
                                        <span class="d-block font-weight-bold mt-1"> ${item.meta_id ? item.meta_id : ""}</span>
                                    </td>
                                     <td>
                                        <span class="d-block font-weight-bold mt-1"> ${item.total_load ? item.total_load : ""}</span>
                                    </td>
                                    <td>
                                        <span class="d-block font-weight-bold mt-1"> ${item.total_count ? item.total_count : ""}</span>
                                    </td>
                                <tr>`;
                });
                $('.preLoader').hide();
                $("#accountLoadDetails").html(htmlData);
            }else {
                this.setErrorHtml2();
            }
        }
    }

    this.setErrorHtml2 = () => {
        $('.preLoader').hide();
        FsHelper.unblockUi($("#accountLoad"));
        $("#accountLoadDetails").html(`
            <tr>
                <td colspan="5">
                    <div class="text-center pt-5 pb-5" style="width: 475px; margin: 0 auto; margin-top: 50px; background: transparent; box-shadow: none;">
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

$("#addManualPayoutForm").on("submit", () => {
    const FormData = getFormData($("#addManualPayoutForm"));
    FsHelper.blockUi($("#addManualPayoutForm"));
    FsClient.post("/add/manual-payout", FormData)
        .then(res => {
            toastr.success(res.message, "success", options);
            $("#addManualPayoutForm")[0].reset();
            $("#generateBankTransferFile").modal('hide');
            (new BatchTransfer()).getBatchTransfer();
         })
        .catch(err => {
            toastr.error(err.responseJSON.message, "Error", options);
            console.log(err);
        })
        .finally(() => {
            FsHelper.unblockUi($("#addManualPayoutForm"));
        });
});

function download(filename, text) {
    var element = document.createElement('a');
    element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(text));
    element.setAttribute('download', filename);

    element.style.display = 'none';
    document.body.appendChild(element);

    element.click();

    document.body.removeChild(element);
}
function downloadSeamless(file_data) {
    window.location.href = file_data;
    (new BatchTransfer()).getBatchTransfer();
}

$('#generateBankTransferFile').on('hidden.bs.modal', function () {
    $("#addManualPayoutForm")[0].reset();
})

$('#generateBankTransferFile').on('shown.bs.modal', function () {
    (new BatchTransfer()).getPayoutInitAmount() // /payout/inti/total
})

$('#BankTransferStatusFileModal').on('hidden.bs.modal', function () {
    $("#ManualPayoutStatusForm")[0].reset();
    $("#payoutSuccessOperationData").html("");
    $("#payoutErrorOperationData").html("");
    $("#payoutSuccessOperationZone").hide();
})

$("#ManualPayoutStatusForm").submit(() => {
    const bankId = $("#bank_file_id").val()
    let bankStatusFile = $("#bank_file")[0].files[0];

    let bankStatusFormData = new FormData();
    bankStatusFormData.append("bank_id", bankId);
    bankStatusFormData.append("bank_file", bankStatusFile);
    (new BatchTransfer()).uploadStatusFile(bankStatusFormData);

    console.log(bankId, bankStatusFile)
});


$("#updateBankTransferConfigForm").submit(() => {
    const FormData = getFormData($("#updateBankTransferConfigForm"));
    if(FormData.is_auto_transfer_enable) {
        if(FormData.is_auto_transfer_enable === "on") {
            FormData.is_auto_transfer_enable = true;
        }
    } else {
        FormData.is_auto_transfer_enable = false;
    }
    (new BatchTransfer()).setPayoutConfig(FormData);

});


EventListener.dispatch.on("page-change-event", (event, callback) => {
    BatchTPostData.page_no = callback.page_number;
    (new BatchTransfer()).getBatchTransfer();
});

function getDownloadBtnAtr(batch_id, markAsUsed) {
    if(markAsUsed){
        return ``;
    }else{
        return `<button class="btn btn-sm btn-primary btn-batch-file-download" data-batch="${batch_id}">Download File</button>`;
    }
}

function getBtnAtr(batch_id, markAsUsed) {
    if(markAsUsed){
        return ``;
    }else{
        return `<button class="btn btn-sm btn-success btn-batch-file-mark-as-used" data-batch="${batch_id}">Mark As Complete</button>`;
    }
}

DzpDatePickerService.init();

$("#ManualPayoutForm").on("submit", () => {
    const FormData = getFormData($("#ManualPayoutForm"));
    BatchTPostData.filter_data = {
        batch_id: null,
        bank_name: null,
        debit_account: null,
        start_date:null,
        end_date:null,
    }
    BatchTPostData.filter_data[FormData.FilterKey] = FormData.FilterValue;
    BatchTPostData.filter_data.debit_account = FormData.debit_account;
    BatchTPostData.limit = FormData.limit;
    BatchTPostData.page_no=1;

    if(FormData.daterange) {
        let splitDate = FormData.daterange.split(/-/);
        BatchTPostData.filter_data.start_date = moment(splitDate[0], 'DD/MM/YYYY').format('YYYY-MM-DD 00:00:00');
        BatchTPostData.filter_data.end_date = moment(splitDate[1], 'DD/MM/YYYY').format('YYYY-MM-DD 23:59:59');
    }
    (new BatchTransfer()).getBatchTransfer();
});

function resetPayoutForm(){
    BatchTPostData.filter_data = {
        batch_id: null,
        bank_name: null,
        start_date:null,
        end_date:null,
    }
    BatchTPostData.page_no=1;
    BatchTPostData.limit=50;

    $('#ManualPayoutForm')[0].reset();
    DzpDatePickerService.init();
    (new BatchTransfer()).getBatchTransfer();
}

$('#merchant_id').change(function(){
    $('#logic_amount').val('');
    $('#login_key').prop('selectedIndex',0);
    (new BatchTransfer()).getMerchantPayoutInitAmount($(this).val());
});

$('#logic_key').change(function(){
    let merchant_id = $('#merchant_id').val();
    let logic_amount = $('#logic_amount').val();
    (new BatchTransfer()).getLogicalInitPayoutDetails(merchant_id, $(this).val(), logic_amount);
});

function getMerchantName(merchantList){
    let htmlData = "";
    if(merchantList){
        merchantList.forEach((item, index) => {
            htmlData += `<br><span>@ : ${item.merchant_details.merchant_name ? item.merchant_details.merchant_name : ''}</span>`;
        });
    }
    return htmlData;
}

$('#is_manual_level_active').change(function(){
    let is_manual_level_active = $("#is_manual_level_active").is(':checked') ? 1 : 0;
    let postData = {
        is_manual_level_active: is_manual_level_active,
    };
    FsHelper.blockUi($("#generateBankTransferFile"));
    FsClient.post("/manual-payout/UpdateManualLevel", postData)
        .then(res => {
            console.log(res)
            this.getConfig(true);
            toastr.success(res.message, "Success", options);
        })
        .catch(err => {
            toastr.error(err.responseJSON.message, "Error", options);
            console.log(err);
        })
        .finally(() => {
            FsHelper.unblockUi($("#generateBankTransferFile"))
        });
});


$('#accountLoad').on('shown.bs.modal', function () {
    (new BatchTransfer()).getAccountLoad();
})

