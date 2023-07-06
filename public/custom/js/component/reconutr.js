(new Digipay()).getTxn();
(new Digipay()).getTxnReport();

function Digipay() {
    this.getTxn = (isLoading = true) => {
        if (isLoading) {
            FsHelper.blockUi($("#UtrData"));
        }
        FsClient.post("/GetUtrReconciliation").then(this.handleResponse).catch(this.handleError);
    }
    this.setAllUTR = (isLoading = true) => {
        if (isLoading) {
            FsHelper.blockUi($("#UtrData"));
        }
        FsClient.post("/SetUtrReconciliation").then(this.handleResponseSetAll).catch(this.handleError);
    }
    this.getTxnReport = (isLoading = true) => {
        if (isLoading) {
            FsHelper.blockUi($("#UtrData"));
        }
        FsClient.post("/GetUtrReconciliationReport").then(this.handleResponseReport).catch(this.handleErrorReport);
    }
    this.handleError = (error) => {
        FsHelper.unblockUi($("#UtrData"));
        if (error.status === 401){
            FsHelper.unauthorizedUserPage("unauthorized_user");
            $("#UtrData").html('');
        }else {
            this.setErrorHtml();
        }
    }
    this.handleResponse = (data) => {
        FsHelper.unblockUi($("#UtrData"));
        if (data.status) {
            this.setTxnHtmlData(data.data);
        } else {
            this.setErrorHtml();
        }
    }
    this.handleResponseSetAll = (data) => {
        FsHelper.unblockUi($("#UtrData"));
        if (data.status) {
            toastr.success(Response.message, "success", toastOption);
            (new Digipay()).getTxn();
            (new Digipay()).getTxnReport();

        } else {
            toastr.error("Error", "error", toastOption);
        }
    }
    this.handleErrorReport = (error) => {
        FsHelper.unblockUi($("#UtrDataMarked"));
        if (error.status === 401){
            FsHelper.unauthorizedUserPage("unauthorized_user");
            $("#UtrDataMarked").html('');
        }else {
            //this.setErrorHtml();
        }
    }
    this.handleResponseReport = (data) => {
        FsHelper.unblockUi($("#UtrDataMarked"));
        if (data.status) {
            this.setTxnHtmlDataReport(data.data);
        } else {
            this.setErrorHtmlReport();
        }
    }
    this.setTxnHtmlData = (data) => {
        console.log(data);
        if(data && data.length > 0) {
            let htmlData = "";
            data.forEach((item, index) => {
                //console.log(item.customer_level_details.user_security_level)
                htmlData += `<tr>
                                <td>
                                    <span class="d-block ">${item.transaction_date ? item.transaction_date : ""}</span>
                                </td>
                                  <td>
                                    <span class="d-block ">${item.customer_id ? item.customer_id : ""}</span>
                                </td>
                                  <td>
                                    <span class="d-block ">${item.transaction_id ? item.transaction_id : ""}</span>
                                </td>
                                   <td>
                                    <span class="d-block ">${item.order_amount ? item.order_amount : ""}</span>
                                </td>
                                  <td>
                                    <span class="d-block ">${item.upi_id ? item.upi_id : ""}</span>
                                </td>
                                   <td>
                                    <span class="d-block ">${item.payment_utr ? item.payment_utr : ""}</span>
                                </td>

                                 <td>
                                    <span class="d-block ">${item.payment_tmp_utr ? item.payment_tmp_utr : ""}</span>
                                </td>
                                   <td>
                                    <span class="d-block ">${item.payment_amount ? item.payment_amount : ""}</span>
                                </td>
                                <td>
                                    <span class="d-block ">${item.bank_txn_date ? item.bank_txn_date : ""}</span>
                                </td>
                                <td>
                                   <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="tempUtr(${item.transaction_id ? item.transaction_id : ""}, ${item.payment_utr ? item.payment_utr : ""})">Set UTR</button>
                                </td>
                        </tr>`;

            });
            $('.preLoader').hide();
            $("#UtrData").html(htmlData);

        } else {

            this.setErrorHtml();
        }
    }
    this.setTxnHtmlDataReport = (data) => {
        console.log(data);
        if(data && data.length > 0) {
            let htmlData = "";
            let htmlData2 = "";
            data.forEach((item, index) => {
                //console.log(item.customer_level_details.user_security_level)
                if(item.order_amount===item.payment_amount) {
                    htmlData += `<tr>
                                <td>
                                    <span class="d-block ">${item.transaction_date ? item.transaction_date : ""}</span>
                                </td>
                                  <td>
                                    <span class="d-block ">${item.customer_id ? item.customer_id : ""}</span>
                                </td>
                                  <td>
                                    <span class="d-block ">${item.transaction_id ? item.transaction_id : ""}</span>
                                </td>
                                 <td>
                                    <span class="d-block ">${item.merchant_order_id ? item.merchant_order_id : ""}</span>
                                </td>
                                   <td>
                                    <span class="d-block ">${item.order_amount ? item.order_amount : ""}</span>
                                </td>
                                   <td>
                                    <span class="d-block ">${item.payment_utr ? item.payment_utr : ""}</span>
                                </td>
                                   <td>
                                    <span class="d-block ">${item.payment_amount ? item.payment_amount : ""}</span>
                                </td>
                                <td>
                                    <span class="d-block ">${item.bank_txn_date ? item.bank_txn_date : ""}</span>
                                </td>
                        </tr>`;
                }else
                {
                    htmlData2 += `<tr>
                                <td>
                                    <span class="d-block ">${item.transaction_date ? item.transaction_date : ""}</span>
                                </td>
                                  <td>
                                    <span class="d-block ">${item.customer_id ? item.customer_id : ""}</span>
                                </td>
                                 <td>
                                    <span class="d-block ">${item.transaction_id ? item.transaction_id : ""}</span>
                                </td>
                                  <td>
                                    <span class="d-block ">${item.merchant_order_id ? item.merchant_order_id : ""}</span>
                                </td>
                                   <td>
                                    <span class="d-block ">${item.order_amount ? item.order_amount : ""}</span>
                                </td>
                                   <td>
                                    <span class="d-block ">${item.payment_utr ? item.payment_utr : ""}</span>
                                </td>
                                 <td>
                                    <span class="d-block ">${item.payment_tmp_utr ? item.payment_tmp_utr : ""}</span>
                                </td>
                                   <td>
                                    <span class="d-block ">${item.payment_amount ? item.payment_amount : ""}</span>
                                </td>
                                <td>
                                    <span class="d-block ">${item.bank_txn_date ? item.bank_txn_date : ""}</span>
                                </td>
                        </tr>`;
                }

            });
            $('.preLoader').hide();
            $("#UtrDataMarked").html(htmlData);
            $("#UtrDataMarkedMismatched").html(htmlData2);

        } else {

            this.setErrorHtmlReport();
        }
    }

    this.setErrorHtml = () => {
        $('.preLoader').hide();
        FsHelper.unblockUi($("#UtrData"));
        $("#UtrData").html(`
            <tr>
                <td colspan="16">
                    <div class="text-center pt-5 pb-5">
                        <img src="/assets/images/record-not-found.svg" class="record-not-found" style="max-height: 50px;max-width: 50px">
                        <div class="mt-2">
                            <span>Record does not exist.</span>
                        </div>
                    </div>
                </td>
            </tr>
        `);
    }
    this.setErrorHtmlReport = () => {
        $('.preLoader').hide();
        FsHelper.unblockUi($("#UtrDataMarked"));
        $("#UtrDataMarked").html(`
            <tr>
                <td colspan="16">
                    <div class="text-center pt-5 pb-5">
                        <img src="/assets/images/record-not-found.svg" class="record-not-found" style="max-height: 50px;max-width: 50px">
                        <div class="mt-2">
                            <span>Record does not exist.</span>
                        </div>
                    </div>
                </td>
            </tr>
        `);
    }
}


function tempUtr(transaction_id, payment_utr) {
    FsHelper.blockUi($("#bankpage"));
    const tempUtr = getFormData($("#utrForm"));
    var myModal =  new jBox('Confirm', {
        confirmButton: 'YES',
        cancelButton: 'No',
        content: `Are You Sure Do you want to Set Temp UTR (${payment_utr}) ?`,
        confirm: function () {
            let postData = {
                transaction_id: transaction_id,
                payment_utr: payment_utr,
            }
            FsClient.post("/transaction/set/utr", postData).then(
                Response => {
                    toastr.success(Response.message, "success", toastOption);
                    FsHelper.unblockUi($("#transaction_page"));
                    (new Digipay()).getTxn();
                }
            ).catch(Error=>{
                toastr.error(Error.responseJSON.message, "error", toastOption);
                FsHelper.unblockUi($("#transaction_page"));
                (new Digipay()).getTxn();
            });
        },
        cancel : function (){
            FsHelper.unblockUi($("#transaction_page"));
        }
    });
    myModal.open();
}

/*
function tempUtr(transaction_id) {
    $.confirm({
        title: 'Set Temp Utr!',
        content: '' +
            '<form action="" class="formName" id="utrForm">' +
            '<div class="form-group">' +
            '<label>Enter Temp Utr</label>' +
            '<input type="text" placeholder="Temp Utr"  name="temp_utr" class="name form-control" required />' +
            '</div>' +
            '</form>',
        buttons: {
            formSubmit: {
                text: 'Submit',
                btnClass: 'btn-blue',
                action: function () {
                    var name = this.$content.find('.name').val();
                    if(!name){
                        $.alert('provide a valid Utr');
                        return false;
                    }
                    FsHelper.blockUi($("#transaction_page"));
                    const tempUtr = getFormData($("#utrForm"));
                    let postData = {
                        transaction_id: transaction_id,
                        payment_utr: tempUtr.temp_utr,
                    }
                    FsClient.post("/transaction/set/utr", postData).then(
                        Response => {
                            toastr.success(Response.message,"success",toastOption);
                            FsHelper.unblockUi($("#transaction_page"));
                            (new Digipay()).getTxn();
                        }
                    ).catch(Error => {
                        toastr.error(Error.responseJSON.message,"error",toastOption);
                        FsHelper.unblockUi($("#transaction_page"));
                        (new Digipay()).getTxn();

                    });
                }
            },
            cancel: function () {
                //close
            },
        },
        onContentReady: function () {
            // bind to events
            var jc = this;
            this.$content.find('form').on('submit', function (e) {
                // if the user submits the form by pressing enter in the field.
                e.preventDefault();
                jc.$$formSubmit.trigger('click'); // reference the button and click it
            });
        }
    });
    FsHelper.unblockUi($("#transaction_page"));
}
*/
