let autoRefreshInterval = null;
let refreshCnt = 0;
(new MobileSync()).getData();
function MobileSync() {
    this.getData = () => {
        FsHelper.blockUi($("#MobileTxnPage"));
        FsClient.post("/support/GetMobileSync", "").then(this.handleResponse).catch(this.handleError);
    }

    this.handleResponse = (data) => {
        FsHelper.unblockUi($("#MobileTxnPage"));
        console.log(data);
        if(data.status) {
            this.setTxnHtmlData(data.data);
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
    this.setTxnHtmlData = (data) => {
        if(data && data.length > 0){
            let htmlData = "";
            console.log(data);
              data.forEach((item, index) => {
                if(item.last_success_mindeff_ist_ago>15) {
                    htmlData += `<tr style="background-color:#f7948d">`;
                }else if(item.last_success_mindeff_ist_ago<=15 && item.last_success_mindeff_ist_ago > 2) {
                    htmlData += `<tr style="background-color:#FBC11C">`;
                }
                else
                {
                    htmlData += `<tr>`;
                }
                htmlData +=`<td>
                    <span class="d-block font-weight-bold mt-1 "> ${item.hardware_id}</span>

                    </td><td>
                      <span class="d-block font-weight-bold mt-1"> ${item.account_number ? item.account_number : ""}</span>

</td><td>
 <span class="d-block font-weight-bold mt-1"> ${item.battery ? item.battery : ""}</span>

</td>
<td>
 <span class="d-block font-weight-bold mt-1"> ${item.last_sync_date_ist ? item.last_sync_date_ist : ""}</span>
</td>
<td>
 <span class="d-block font-weight-bold mt-1"> ${item.last_success_mindeff_ist ? item.last_success_mindeff_ist : ""}</span>
</td>
<td>
 <span class="d-block font-weight-bold mt-1"> ${item.created_at_ist ? item.created_at_ist : ""}</span>
</td>
            </tr>`;
            });
            $('.preLoader').hide()
            $("#MobileData").html(htmlData);
        } else {
            this.setErrorHtml();
        }
    }
    this.setErrorHtml = () => {
        $('.preLoader').hide();
        FsHelper.unblockUi($("#MobileTxnPage"));
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
            (new MobileSync()).getData();
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
