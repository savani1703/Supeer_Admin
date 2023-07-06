getPayin();
function getPayin() {
    FsHelper.blockUi($("#Merchant_page"));
    let postData = {
        merchant_id: getMerchantId(),
        page_no: 1,
        limit: 50,
    }
    let htmlData = null;
    FsClient.post("/merchant/GetPayInMeta", postData).then(
        response => {
            if (response) {
                let htmlData = "";
                htmlData += `<tr class=""><td colspan="12" style="user-select: none"><div class="d-block" id="merchantPgFilter" style="user-select: none"></div></td></tr>`;
                let availablePgName = [];
                $.each(response.data, (index, _item) => {
                    availablePgName.push(index);
                    htmlData += `<tr class="mb-2 mt-2 filter-m-pg-1" data-tr-target="tr-${index}"><td colspan="12"><p class="font-weight-bold">${index}</p></td></tr>`;
                    _item.forEach((item, __index) => {
                        htmlData += `<tr class="tr-m-filter tr-${index}">
                                    <td>  ${item.id ? item.id : ""}  </td>
                                    <td>
                                      <span class="d-block font-weight-bold mt-1"><span class="text-muted">PG :</span> ${item.pg_name ? item.pg_name : ""} ${item.pg_id ? `(${item.pg_id})` : ""}</span>
                                      <span class="d-block font-weight-bold mt-1"><span class="text-muted">Label :</span>${item.pg_label ? item.pg_label : ""}</span>
                                      ${item.account_number ? `<span class="d-block font-weight-bold mt-1"><span class="text-muted">A/C: </span>${item.account_number ? item.account_number : ""}</span>` : ""}
                                      ${item.ifsc_code ? `<span class="d-block font-weight-bold mt-1"><span class="text-muted">IFSC: </span>${item.ifsc_code ? item.ifsc_code : ""}</span>` : ""}
                                      ${item.bank_name ? `<span class="d-block font-weight-bold mt-1"><span class="text-muted">Bank: </span>${item.bank_name ? item.bank_name : ""}</span>` : ""}
                                      ${item.upi_id ? `<span class="d-block font-weight-bold mt-1"><span class="text-muted">UPI: </span>${item.upi_id ? item.upi_id : ""}</span>` : ""}
                                      ${item.is_account_flow_active ? `<div><span class="badge badge-primary" style="margin-top: 6px;padding: 5px;">Account Flow</span></div>` : ""}
                                    </td>
                                    <td>  ${item.payment_method ? item.payment_method : ""}  </td>
                                    <td>
                                    <a href="#" class="payin_metaStatus_loadXeditable badge ${this.getStatusClass(item.is_active)} "
                                       data-type="select"
                                       data-pk="${item.pg_id}"
                                       data-pg_name="${item.pg_name}"
                                       data-merchant_id="${item.merchant_id}"
                                       data-id="${item.id}"
                                       data-abc="true">${item.is_active ? "Yes" : "No"}</a>
                                    </td>
                                    <td>
                                      <span class="d-block font-weight-bold pt-2"><span class="text-muted">Min :</span>
                                            <a href="#" class="payin_minLimit_loadXeditable"
                                           data-type="text"
                                           data-pk="${item.pg_id}"
                                           data-pg_name="${item.pg_name}"
                                           data-merchant_id="${item.merchant_id}"
                                           data-id="${item.id}"
                                           data-abc="true">${item.min_amount ? item.min_amount : "No Set"}</a>
                                        </span>

                                        <span class="d-block font-weight-bold pt-2"><span class="text-muted">Max :</span>
                                             <a href="#" class="payin_maxLimit_loadXeditable"
                                           data-type="text"
                                           data-pk="${item.pg_id}"
                                           data-pg_name="${item.pg_name}"
                                           data-merchant_id="${item.merchant_id}"
                                           data-id="${item.id}"
                                           data-abc="true">${item.max_amount ? item.max_amount : "No Set"}</a>
                                        </span>

                                        <span class="d-block font-weight-bold pt-2"><span class="text-muted">Current Turnover :</span> <span class="${item.current_turnover > (item.daily_limit-100000) ? 'text-danger' :'text-muted'}"> ${item.current_turnover ? item.current_turnover : 0}</span> </span>

                                        <span class="d-block font-weight-bold pt-2"><span class="text-muted">Daily Limit :</span>
                                         <a href="#" class="payin_dailyLimit_loadXeditable"
                                           data-type="text"
                                           data-pk="${item.pg_id}"
                                           data-pg_name="${item.pg_name}"
                                           data-merchant_id="${item.merchant_id}"
                                           data-id="${item.id}"
                                           data-abc="true">${item.daily_limit ? item.daily_limit : "No Set"}</a>
                                        </span>
                                          <span class="d-block font-weight-bold pt-2"><span class="text-muted">% Limit :</span>
                                         <a href="#" class="payin_perLimit_loadXeditable"
                                           data-type="text"
                                           data-pk="${item.pg_id}"
                                           data-pg_name="${item.pg_name}"
                                           data-merchant_id="${item.merchant_id}"
                                           data-id="${item.id}"
                                           data-abc="true">${item.per_limit ? item.per_limit : "No Set"}</a>
                                        </span>
                                    </td>
                                   <td>
                                     <span class="d-block font-weight-bold pt-2"><span class="text-muted">Lavel 1
                                         <div class="form-check form-check-inline">
                                             <input type="checkbox" class="form-check-input levelBox"  ${item.is_level1 === 1 ? "checked" : ""}
                                                id="${item.id}"
                                                data-merchant_id="${item.merchant_id}"
                                                data-pg_name="${item.pg_name}"
                                                data-pg_id="${item.pg_id}"
                                                data-status="${item.is_level1}"
                                                data-level_key="${'is_level1'}"
                                               >
                                         </div>
                                     </span>
                                     </td><td>
                                     <span class="d-block font-weight-bold pt-2"><span class="text-muted">Lavel 2
                                      <div class="form-check form-check-inline">
                                             <input type="checkbox" class="form-check-input levelBox " ${item.is_level2 === 1 ? "checked" : ""}
                                                id="${item.id}"
                                                data-merchant_id="${item.merchant_id}"
                                                data-pg_name="${item.pg_name}"
                                                data-pg_id="${item.pg_id}"
                                                data-status="${item.is_level2}"
                                                data-level_key="${'is_level2'}"
                                               >
                                         </div>
                                         </span>
                                         </td><td>
                                     <span class="d-block font-weight-bold pt-2"><span class="text-muted">Lavel 3
                                       <div class="form-check form-check-inline">
                                             <input type="checkbox" class="form-check-input levelBox " ${item.is_level3 === 1 ? "checked" : ""}
                                                id="${item.id}"
                                                data-merchant_id="${item.merchant_id}"
                                                data-pg_name="${item.pg_name}"
                                                data-pg_id="${item.pg_id}"
                                                data-status="${item.is_level3}"
                                                data-level_key="${'is_level3'}"
                                               >
                                         </div>
                                         </span></td><td>
                                     <span class="d-block font-weight-bold pt-2"><span class="text-muted">Lavel 4
                                         <div class="form-check form-check-inline">
                                             <input type="checkbox" class="form-check-input levelBox " ${item.is_level4 === 1 ? "checked" : ""}
                                                id="${item.id}"
                                                data-merchant_id="${item.merchant_id}"
                                                data-pg_name="${item.pg_name}"
                                                data-pg_id="${item.pg_id}"
                                                data-status="${item.is_level4}"
                                                data-level_key="${'is_level4'}"
                                               >
                                         </div>
                                        </span>
                                   </td>
                                   <td> ${item.is_seamless === 1 ? "Seamless" : "Hosted"}  </td>
                                   <td>
                                       <span class="d-block font-weight-bold pt-2"><span class="text-muted">Created :</span> ${item.created_at_ist ? item.created_at_ist : ""}</span>
                                       <span class="d-block font-weight-bold pt-2"><span class="text-muted">Updated :</span> ${item.updated_at_ist ? item.updated_at_ist : ""}</span>
                                    </td>
                                   <td>
                                       <button  onclick="deleteMeta('${item.merchant_id}','${item.pg_name}','${item.id}','${item.pg_id}')" class="btn btn-danger btn-sm">  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg></button>
                                    </td>
                         </tr>`;
                        $("#payinData").html(htmlData);
                        if(availablePgName.length > 0) {
                            let availablePgNameButtons = `<button id="m-pg-all" data-tr-target="tr-all" class="btn btn-primary btn-sm mr-1 filter-m-pg">All</button>`;
                            availablePgName.forEach((item) => {
                                availablePgNameButtons += `<button id="m-pg-${item}" data-tr-target="tr-${item}" class="btn btn-default btn-sm mr-1 filter-m-pg">${item}</button>`;
                            });
                            $("#merchantPgFilter").html(availablePgNameButtons);
                            $(".filter-m-pg").click((e) => {
                                console.log(e)
                                let trId = "";
                                if(e.target.nodeName === "BUTTON") {
                                    trId = e.target.attributes['data-tr-target'].value;
                                } else {
                                    trId = e.target.parentElement.closest("tr").attributes['data-tr-target'].value;
                                }

                                $(".filter-m-pg").removeClass("active");
                                e.target.classList.add("active");
                                if(trId === "tr-all") {
                                    $(".tr-m-filter").show();
                                } else {
                                    $(".tr-m-filter").hide();
                                    $("." + trId).show();
                                }
                            });
                        }
                        FsHelper.unblockUi($("#Merchant_page"));
                        loadEdiTable();
                    })
                })
                FsHelper.unblockUi($("#Merchant_page"));
            }
            $('.levelBox').on('change', function () {
                let id = $(this).attr("id");
                let merchantId = $(this).data("merchant_id");
                let pgName = $(this).data("pg_name");
                let pgMetaId = $(this).data("pg_id");
                let status = $(this).data("status");
                let levelKey = $(this).data("level_key");
                if ($(this).is(':checked')) {
                    if (status === 0) {
                        status = 1
                    }
                } else {
                    if (status === 1) {
                        status = 0
                    }
                }
                let UpdatePayInMetaLevelPayload = {
                    merchant_id: merchantId,
                    pg_name: pgName,
                    pg_id: pgMetaId,
                    id: id,
                    level_key: levelKey,
                    status: status,
                };

                FsHelper.blockUi($("#payinDataTab"));
                FsClient.post('/merchant/UpdatePayInMetaLevel', UpdatePayInMetaLevelPayload)
                    .then(response => {
                        getPayin()
                        toastr.success("Success", response.message, toastOption);
                    })
                    .catch(error => {
                        console.log(error)
                        toastr.error("Error", error.responseJSON.message, toastOption);
                    })
                    .finally(() => {
                        FsHelper.unblockUi($("#payinDataTab"));
                    });
            });
        }
    ).catch(Error => {
        console.log(Error)
        FsHelper.unblockUi($("#Merchant_page"));
        $("#payinData").html(`
                <tr>
                    <td colspan="16">
                        <div class="text-center pt-5 pb-5">
                            <img src="/assets/images/record-not-found.svg" class="record-not-found">
                            <div class="mt-2">
                                <span>Record does not exist.</span>
                            </div>
                        </div>
                    </td>
                </tr>
            `);
    }).finally(function () {
         FsHelper.unblockUi($("#Merchant_page"));
        loadAvailableMeta();
    });

    // AVAILABLE META LOAD
    this.getStatusClass = (status) => {
        if (status === 1) {
            badge = "badge-success";
        }
        if (status === 0) {
            badge = "badge-danger";
        }
        return badge;
    }

}

function AddpayinMeta(merchantId, pgName, pgMetaId, method) {
    let options = `<div class="row mb-2">`;
    method = method.split(",");
    const isAllowedFirstCheck = method.length === 1;
    method.forEach((item) => {
        options += `<div class="form-group col-3"><input type="checkbox" class="form-control c-check-input" name="method" id="${item}-method" value="${item}" ${isAllowedFirstCheck ? "checked" : ''}><label class="c-check-method" for="${item}-method">${item}</label></div>`;
    });
    options += `</div>`;
    const htmlContent = `
                        <form id="addPayoutForm" class="formName">
                            ${options}
                            <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr class="text-center">
                                        <th class="ft-16-px">L1</th>
                                        <th class="ft-16-px">L2</th>
                                        <th class="ft-16-px">L3</th>
                                        <th class="ft-16-px">L4</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <div class="squaredOne">
                                              <input type="checkbox" class="level-checkbox" value="1" id="level1" name="level" />
                                              <label for="level1"></label>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="squaredOne">
                                              <input type="checkbox" class="level-checkbox" value="2" id="level2" name="level" />
                                              <label for="level2"></label>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="squaredOne">
                                              <input type="checkbox" class="level-checkbox" value="3" id="level3" name="level" />
                                              <label for="level3"></label>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="squaredOne">
                                              <input type="checkbox" class="level-checkbox" value="4" id="level4" name="level" />
                                              <label for="level4"></label>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            </div>
                        </form>`;
    $.confirm({
        title: `${pgName}(${pgMetaId}) Select Method And Level`,
        content: htmlContent,
        buttons: {
            formSubmit: {
                text: 'Submit',
                btnClass: 'btn-blue',
                action: function () {
                    FsHelper.blockUi($("#Merchant_page"));
                    const formData = getFormData($("#addPayoutForm "));
                    let postData = {
                        merchant_id: getMerchantId(),
                        pg_name: pgName,
                        pg_id: pgMetaId,
                        payment_method: $(".c-check-input:checked").map((_, e) => e.value).get(),
                        level: $(".level-checkbox:checked").map((_, e) => e.value).get(),
                    }
                    if(postData.payment_method.length < 1) {
                        toastr.error("Payment Method Required", "Validation", toastOption);
                        return 0;
                    }
                    if(postData.level.length < 1) {
                        toastr.error("Select At-least 1 Level", "Validation", toastOption);
                        return 0;
                    }

                    FsClient.post("/meta/AddPayInMetaToMerchantCollection", postData).then(
                        response => {
                            FsHelper.unblockUi($("#Merchant_page"));
                            toastr.success(response.message,"success",toastOption);
                            getPayin();
                        }
                    ).catch(error => {
                        toastr.error(error.responseJSON.message,"error",toastOption);
                        FsHelper.unblockUi($("#Merchant_page"));
                    });
                }
            },
            cancel: function () {
                FsHelper.unblockUi($("#Merchant_page"));
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

function loadAvailableMeta() {
    FsHelper.blockUi($("#merchantAutoDataTable"));
    FsHelper.blockUi($("#merchantManualDataTable"));
    let postData = {
        merchant_id: getMerchantId(),
    }
    FsClient.post("/meta/GetAvailablePaymentMeta", postData).then(response => {
            if (response) {
                let htmlDataAuto = "";
                if (response.data.auto && response.data.auto.length > 0) {
                    response.data.auto.forEach((item, index) => {
                        htmlDataAuto += `<tr>
                                <td>
                                    <span class="d-block font-weight-bold pt-2"><span class="text-muted">Pg :</span> ${item.pg_name ? item.pg_name : ""}</span>
                                    <span class="d-block font-weight-bold pt-2"><span class="text-muted">Account # :</span> ${item.account_id ? item.account_id : ""}</span>
                                 </td>
                                <td>
                                    <span class="d-block font-weight-bold pt-2">${item.label ? item.label : ""}</span>
                                    <span class="d-block font-weight-bold pt-2"><span class="text-muted">Merchant # :</span> ${item.merchant_id ? item.merchant_id : ""}</span>
                                </td>
                                <td>${item.is_seamless ? "Seamless" : "Hosted"}  </td>
                                <td><button class="btn btn-primary btn-sm" onclick="AddpayinMeta('${item.merchant_id}','${item.pg_name}','${item.account_id}','${item.methods}')">Add Meta</button> </td>
                         </tr>`;
                        $("#payinAvailableData").html(htmlDataAuto);
                        loadEdiTable();
                    });
                } else {
                    $("#payinAvailableData").html(getErrorAvailableErrorHtml("payinAvailableData"));
                }

                let htmlDataManual = "";
                if (response.data.manual && response.data.manual.length > 0) {
                    response.data.manual.forEach((item, index) => {
                        htmlDataManual += `<tr>
                                <td>
                                    <span class="d-block font-weight-bold mt-1"><span class="text-muted">Pg :</span> ${item.pg_name ? item.pg_name : ""}</span>
                                    <span class="d-block font-weight-bold mt-1"><span class="text-muted">Account # :</span> ${item.account_id ? item.account_id : ""}</span>
                                 </td>
                                <td>
                                    <span class="d-block font-weight-bold mt-1"><span class="text-muted">Label :</span> ${item.label ? item.label : ""}</span>
                                    <span class="d-block font-weight-bold mt-1"><span class="text-muted">Account :</span> ${item.account_number ? item.account_number : ""}</span>
                                    <span class="d-block font-weight-bold mt-1"><span class="text-muted">UPI :</span> ${item.upi_id ? item.upi_id : ""}</span>
                                    <span class="d-block font-weight-bold mt-1"><span class="text-muted">Bank :</span> ${item.bank_name ? item.bank_name : ""}</span>
                                </td>
                                <td><button class="btn btn-primary btn-sm" onclick="AddpayinMeta('${item.merchant_id}','${item.pg_name}','${item.account_id}','${item.methods}')">Add Meta</button> </td>
                         </tr>`;
                        $("#payinAvailableDataManual").html(htmlDataManual);
                        loadEdiTable();
                    });
                } else {
                    $("#payinAvailableDataManual").html(getErrorAvailableErrorHtml("payinAvailableDataManual"));
                }
            }
        }
    ).catch(Error => {
        console.log(Error)
        $("#payinAvailableData").html(getErrorAvailableErrorHtml("merchantAutoDataTable"));
        $("#payinAvailableDataManual").html(getErrorAvailableErrorHtml("merchantManualDataTable"));
    }).finally(function () {
        FsHelper.unblockUi($("#merchantAutoDataTable"));
        FsHelper.unblockUi($("#merchantManualDataTable"));
    });
}

function getErrorAvailableErrorHtml(tableId) {
    let element = document.getElementById(tableId);
    const numberOfChildren = element.getElementsByTagName('th').length;
    return `<tr>
                <td colspan="${numberOfChildren}">
                    <div class="text-center pt-5 pb-5">
                        <img src="/assets/images/record-not-found.svg" class="record-not-found">
                        <div class="mt-2">
                            <span>Record does not exist.</span>
                        </div>
                    </div>
                </td>
            </tr>`;
}

function deleteMeta(MerchantId, Pg, Id, PgId) {
    $.confirm({
        title: 'Delete Meta !',
        content: 'Are You Sure To Delete Payin Meta !',
        buttons: {
            confirm: function () {
                FsHelper.blockUi($("#Merchant_page"));
                let postData = {
                    merchant_id: MerchantId,
                    pg_name: Pg,
                    id: Id,
                    pg_id: PgId,
                }
                FsClient.post("/merchant/DeletePayInMeta", postData).then(
                    Response => {
                        FsHelper.unblockUi($("#Merchant_page"));
                        toastr.success(Response.message, "success", toastOption);
                        getPayin()
                    }
                ).catch(Error => {
                    toastr.error(Error.responseJSON.message, "error", toastOption);
                    FsHelper.unblockUi($("#Merchant_page"));
                    getPayin()
                });

            },
            cancel: function () {
                $.alert('Canceled Delete Payin Meta !');
            },
        }
    });
}

function getMerchantId() {
    return (window.location.pathname).split("/")[2];
}
let autoRefreshInterval = null;
let refreshCnt = 0;

$("#refreshTitle").html("Auto Refresh");
function autoRefreshTransaction() {
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
            getPayin();
            refreshCnt++;
            console.log(`Transaction Refresh: ${refreshCnt}`)
        }, 15000);
    }
}

function startFed() {
    const data = document.getElementById("fedStartBtn");
        let postData = {
            merchant_id: data.dataset.merchantId,
            action: data.dataset.value,
            bank_name: "FEDERAL",
        }
        FsClient.post("/meta/update/merchant/all", postData).then(
            Response => {
                FsHelper.unblockUi($("#Merchant_page"));
                toastr.success(Response.message, "success", toastOption);
                getPayin()
            }
        ).catch(Error => {
            toastr.error(Error.responseJSON.message, "error", toastOption);
            FsHelper.unblockUi($("#Merchant_page"));
            getPayin()
        });
    }


function stopFed() {
    $.confirm({
        title: 'Stop All Fed !',
        content: 'Are You Sure You Want To Stop All Fedral Bank !',
        buttons: {
            confirm: function () {
                const data = document.getElementById("fedStopBtn");
                let postData = {
                    merchant_id: data.dataset.merchantId,
                    action: data.dataset.value,
                    bank_name: "FEDERAL",
                }
                FsClient.post("/meta/update/merchant/all", postData).then(
                    Response => {
                        FsHelper.unblockUi($("#Merchant_page"));
                        toastr.success(Response.message, "success", toastOption);
                        getPayin()
                    }
                ).catch(Error => {
                    toastr.error(Error.responseJSON.message, "error", toastOption);
                    FsHelper.unblockUi($("#Merchant_page"));
                    getPayin()
                });
            },
            cancel: function () {
                $.alert('Canceled Stop Fed!');
            },
        }
    });
}
