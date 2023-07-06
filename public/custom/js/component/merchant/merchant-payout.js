getMeta()
availablePayoutMeta()


function getMeta() {
    FsHelper.blockUi($("#Payout_Meta"));
    let postData = {
        merchant_id: getMerchantId(),
        page_no: 1,
        limit: 50,
    }
    let htmlData = null;
    FsClient.post("/merchant/GetPayoutMeta", postData).then(
    Response => {
            if (Response) {
                let htmlData = "";
                Response.data.forEach((item, index) => {
                    htmlData += `<tr>
                                <td>${item.id ? item.id:""}</td>
                                <td>${item.merchant_id ? item.merchant_id:""}</td>
                                <td>
                                   <span class="d-block font-weight-bold pt-2"><span class="text-muted">PG :</span> ${item.pg_name ? item.pg_name : ""}</span>
                                   <span class="d-block font-weight-bold pt-2"><span class="text-muted">PG# :</span> ${item.pg_id ? item.pg_id : ""}</span>
                                   <span class="d-block font-weight-bold pt-2"><span class="text-muted">PG Name # :</span> ${item.pglabel ? item.pglabel : ""}</span>
                                 </td>
                                <td>
                                   <span class="d-block font-weight-bold pt-2"><span class="text-muted">Current Bal :</span> ${item.pgavailable_balance ? item.pgavailable_balance : "0"}</span>
                                   <span class="d-block font-weight-bold pt-2"><span class="text-muted">Current TurnOver :</span> ${item.current_turnover ? item.current_turnover : "0"}</span>
                                   <span class="d-block font-weight-bold pt-2"><span class="text-muted">Dailay Limit :</span> ${item.daily_limit ? item.daily_limit : "0"}</span>
                                </td>
                                <td>

                                    <a href="#" class="payout_status_loadXeditable"
                                           data-type="select"
                                           data-pk="${item.pg_id}"
                                           data-pg_name="${item.pg_name}"
                                           data-merchant_id="${item.merchant_id}"
                                           data-id="${item.id}"
                                           data-abc="true">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                            ${item.is_active===true ? "Yes" :"No"}
                                        </a>
                                </td>
                                <td>
                                   <span class="d-block font-weight-bold pt-2"><span class="text-muted">Created At :</span> ${item.created_at_ist ? item.created_at_ist : ""}</span>
                                   <span class="d-block font-weight-bold pt-2"><span class="text-muted">Updated At :</span> ${item.updated_at_ist ? item.updated_at_ist : ""}</span>
                                </td>
                           </tr>`;
                    $("#merchant_Payout").html(htmlData);
                    loadEdiTable()
                });
            }
        }
    ).catch(Error => {
        console.log(Error)
        $("#merchant_Payout").html(`
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
        }).finally(() => {
        FsHelper.unblockUi($("#Payout_Meta"));
    });
}


function availablePayoutMeta() {
    FsHelper.blockUi($("#available_payout"));
    let postData = {
        merchant_id: getMerchantId(),
        page_no: 1,
        limit: 50,
    }
    let htmlData = null;
    FsClient.post("/meta/GetAvailablePayoutMeta", postData).then(
    Response => {
            if (Response) {
                let htmlData = "";
                Response.data.forEach((item, index) => {
                    htmlData += `<tr>
                                <td>  ${item.merchant_id ? item.merchant_id :""}  </td>
                                <td>  ${item.pg_name ? item.pg_name :""}  </td>
                                <td>  ${item.account_id ? item.account_id :""}  </td>
                                <td>  ${item.label ? item.label :""}  </td>
                                <td>  <button class="btn btn-primary" onclick="addPayoutMeta('${item.merchant_id}','${item.pg_name}','${item.account_id}')">Add Meta </button>   </td>
                           </tr>`;
                    $("#available_merchant_Payout").html(htmlData);
                });
            }
        }
    ).catch(Error => {
        console.log(Error)
        $("#available_merchant_Payout").html(`
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
        }).finally(() => {
        FsHelper.unblockUi($("#available_payout"));
    });
}

function addPayoutMeta(mid,Pg,accountId){
    let postData = {
        merchant_id: getMerchantId(),
        pg_name: Pg,
        pg_id: accountId,
    }
    FsHelper.blockUi($("#merchant_payoutPage"));
    FsClient.post('/meta/AddPayoutMetaToMerchantWithdrawal', postData)
        .then(response => {
            getMeta()
            availablePayoutMeta()
            toastr.success("Success", response.message, toastOption);
        })
        .catch(error => {
            console.log(error)
            toastr.error("Error", error.responseJSON.message, toastOption);
        })
        .finally(() => {
            FsHelper.unblockUi($("#merchant_payoutPage"));
        });
}

function getMerchantId() {
    return (window.location.pathname).split("/")[2];
}
