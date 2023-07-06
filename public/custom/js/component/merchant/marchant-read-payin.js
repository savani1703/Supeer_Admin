ReadPayinData()

function  ReadPayinData(){
    FsHelper.blockUi($("#Merchant_page"));
    let htmlData = null;
    FsClient.post("/merchant-read-payin").then(
        Response => {
            if (Response) {
                console.log(Response.data.UPIPAY);
                let htmlData = "";
                Response.data.UPIPAY.forEach((item, index) => {
                    htmlData += `<tr>
                                <td>
                                    <span class="d-block mb-1"> <span class="font-weight-bold text-muted">Merchant Id :</span> ${item.merchant_id ? item.merchant_id : ""}</span>
                                    <span class="d-block"><span class="font-weight-bold text-muted">Bank Name :</span>${item.pg_label ? item.pg_label : ""}</span>
                                    <span class="d-block"><span class="font-weight-bold text-muted">UPI :</span>${item.upi_id ? item.upi_id : ""}</span>
                                    <span class="d-block"><span class="font-weight-bold text-muted">Merchant Name :</span>  <span class="font-weight-bold">${item.merchant_name ? item.merchant_name : ""}</span> </span>
                                </td>
                                <td>
                                    <span class="font-weight-bold badge- ${item.is_active ? "text-success":"badge-danger rounded-top p-1"}">${item.is_active ? "YES" : "NO"}</span>
                                </td>
                                <td>
                                    <span class="d-block ">${item.created_at_ist ? item.created_at_ist : "-"}</span>
                                </td>
                                <td>
                                    <span class="d-block ">${item.updated_at_ist ? item.updated_at_ist : "-"}</span>
                                </td>
                           </tr>`;
                    $("#readpayinData").html(htmlData);
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
        FsHelper.unblockUi($("#Merchant_page"));
    });
}
