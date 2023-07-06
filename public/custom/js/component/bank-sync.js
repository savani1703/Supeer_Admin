let autoRefreshInterval = null;
let refreshCnt = 0;
(new BankSync()).getBank();
function BankSync() {
    this.getBank = () => {
        FsHelper.blockUi($("#bankTransactionPage"));
        FsClient.post("/support/GetBankSync", "").then(this.handleResponse).catch(this.handleError);
    }

    this.handleResponse = (data) => {
        FsHelper.unblockUi($("#bankTransactionPage"));
        console.log(data);
        if(data.status) {
            $('#totalbal').text(data.bank_bal);
            this.setTxnHtmlData(data.data,data.data2);
            $('#pagination').show();
        } else {
            this.setErrorHtml();
        }
    }

    this.handleError = (error) => {
        if (error.status === 401){
            FsHelper.unauthorizedUserPage("unauthorized_user");
            $("#supportLogsDetail").html('');
        }else {
            this.setErrorHtml();
        }
    }
    this.setTxnHtmlData = (data,data2) => {
        if(data && data.length > 0){
            let htmlData = "";
            console.log(data);
              data.forEach((item, index) => {

                if(item.last_bank_sync_mindeff_ist>15) {
                    htmlData += `<tr style="background-color:#f7948d">`;
                }else if(item.last_bank_sync_mindeff_ist<=15 && item.last_bank_sync_mindeff_ist > 2) {
                    htmlData += `<tr style="background-color:#FBC11C">`;
                }else
                {
                    if(item.is_sync_active) {
                        htmlData += `<tr>`;
                        if (item.merchant_rid=='MID_3UOP4XZR4OO17D' && item.is_level1===1){
                            htmlData += `<tr style="background-color:#ffefd5">`;
                        }
                    }
                    else
                    {
                        if(item.last_success_mindeff_ist>20) {
                            htmlData += `<tr style="background-color:#e0b5ff">`;
                        }
                    }
                }

                htmlData +=`<td>
                    <span class="d-block font-weight-bold mt-1 text-primary"> ${item.merchant_rid=='MID_3UOP4XZR4OO17D' && item.is_level1===1 ? 'lavel 1' : ""}</span>
                    <span class="d-block font-weight-bold mt-1"> ${item.av_bank_id ? item.av_bank_id : ""}</span>
                    <span class="d-block font-weight-bold mt-1"> ${item.account_holder_name ? item.account_holder_name : ""}</span>
                    <span class="d-block font-weight-bold mt-1">A/C: ${item.account_number ? item.account_number : ""}</span>
                    <span class="d-block font-weight-bold mt-1">IFSC: ${item.ifsc_code ? item.ifsc_code : ""}</span>
                    </td>`;
                if(item.is_sync_active) {
                    htmlData +=` <td><span class="d-block position-relative mb-1"><div class="led-box" title="Meta Active Now"><div class="led-green"></div></div></span></td>`;
                }
                else
                {
                    htmlData +=` <td><span class="d-block position-relative mb-1"><div class="led-box" title="Meta is Not Active"><div class="led-red"></div></div></span></td>`;
                }
                htmlData +=`
                <td>
                    <span class="d-block font-weight-bold">${item.live_bank_balance ? item.live_bank_balance : "0"}</span>
                </td>
                 if(item.turnover > 0) {
                         <td  class="${(item.turnover > (item.daily_limit-150000)) && item.is_sync_active && item.turnover!==0 ? 'invalid' :''}">
                            <span class="d-block font-weight-bold pt-2"><span class="text-muted">Current Turnover :</span> <span> ${item.turnover ? item.turnover : "0"}</span> </span>
                            <span class="d-block font-weight-bold pt-2"><span class="text-muted">Daily Limit :</span><strong>
                                               ${item.daily_limit ? item.daily_limit : "0"}</strong>
                                                </span>
                           <span class="d-block font-weight-bold pt-2"><span class="text-muted">In Per. :</span><strong>
                                               ${item.daily_limit_per ? item.daily_limit_per : "0"} %</strong>
                                                </span>
                        </td>
                }else
                {
                        <!--<td></td>-->
                }
                <td>
                    <span class="d-block font-weight-bold mt-1"> ${item.last_bank_sync_ist ? item.last_bank_sync_ist : ""}</span>
                </td>
                <td>
                    <span class="d-block font-weight-bold mt-1"> ${item.last_success ? item.last_success : ""}</span>
                </td>
                 <td>
                    <span class="d-block font-weight-bold mt-1"> ${item.last_success_ago ? item.last_success_ago : ""}</span>
                </td>
                <td>
                    <span class="d-block font-weight-bold mt-1"> ${item.note ? item.note : ""}</span>
                </td>
            </tr>`;
            });
            $('.preLoader').hide()
            $("#BankData").html(htmlData);
        } else {
            this.setErrorHtml();
        }
    }
    this.setErrorHtml = () => {
        $('.preLoader').hide();
        FsHelper.unblockUi($("#bankTransactionPage"));
        $('#pagination').hide();
        $("#BankData").html(`
            <tr>
                <td colspan="12">
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

$("#refreshTitle").html("Auto Refresh");
function autoRefreshbank() {
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
            (new BankSync()).getBank();
            refreshCnt++;
            console.log(`Transaction Refresh: ${refreshCnt}`)
        }, 10000);
    }
}

$(function() {
    var on = false;
    window.setInterval(function() {
        on = !on;
        if (on) {
            $('.invalid').addClass('invalid-blink')
        } else {
            $('.invalid-blink').removeClass('invalid-blink')
        }
    }, 1000);
});
