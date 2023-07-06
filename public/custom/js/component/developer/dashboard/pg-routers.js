let PostData = {
    filter_data: {
        start_date:null,
        end_date:null,
    },
    page_no: 1,
    limit: 50,
};



const badge_color = {
    Up: 'badge badge-success',
    Down: 'badge badge-danger',
};

(new PgRouters()).getRouters();

function PgRouters() {
    this.getRouters = () => {
        FsHelper.blockUi($("#custTablw"));
        FsClient.post("/developer/dashboard/GetPgRouters", PostData).then(this.setHtmlData).catch(this.setErrorHtml);
    }

    this.setHtmlData = (data) => {
        if(data.data && data.data.length > 0) {
            let htmlData = "";
            data.data.forEach((item, index) => {
                let payinBadge= badge_color[item.is_payin_down ? "Down" : "Up"];
                htmlData += `<tr>
                                <td>
                                   <span class="d-block font-weight-bold  mt-1"><span class="text-muted">PG :</span> ${item.pg ? item.pg : ""}</span>
                                   <span class="d-block font-weight-bold  mt-1"><span class="text-muted">PG Type:</span> ${item.pg_type ? item.pg_type : ""}</span>
                                </td>

                                 <td>
                                    <span class="d-block mt-1 font-weight-bold"><span class="text-muted">Module:</span> ${item.payin_router ? item.payin_router : "-"}</span>
                                    <span class="d-block mt-1 font-weight-bold"><span class="text-muted">Meta:</span> ${item.payin_meta_router ? item.payin_meta_router : "-"}</span>
                                </td>

                                <td>
                                    <span class="d-block mt-1 font-weight-bold"><span class="text-muted">Module:</span> ${item.payout_router ? item.payout_router : "-"}</span>
                                    <span class="d-block mt-1 font-weight-bold"><span class="text-muted">Meta:</span> ${item.payout_meta_router ? item.payout_meta_router : "-"}</span>
                                </td>

                                <td>
                                     <a href="#" class="payindownXeditable ${payinBadge}" data-type="select" data-pk="${item.pg}">
                                        ${item.is_payin_down===1 ? "Down" : "Up"}
                                     </a>
                                </td>
                                <td>
                                   <span class="d-block font-weight-bold  mt-1"><span class="text-muted">Created :</span> ${item.created_at_ist ? item.created_at_ist : ""}</span>
                                   <span class="d-block font-weight-bold  mt-1"><span class="text-muted">Updated :</span> ${item.updated_at_ist ? item.updated_at_ist : ""}</span>
                                </td>
                            </tr>`;
            });
            $('.preLoader').hide()
            $("#PgRoutersData").html(htmlData);
            payinDownXeditable()
        } else {
            this.setErrorHtml();
        }
        function payinDownXeditable(){
            $('.payindownXeditable').editable({
                params: function(params) {
                    params.pg_name = $(this).attr("data-pg_name");
                    params.merchant_id = $(this).attr("data-merchant_id");
                    params.id = $(this).attr("data-id");
                    return params;
                },
                type: 'select',
                url: '/',
                title: 'payindownXeditable',
                name: 'payindownXeditable',
                source: [
                    {
                        value: '1',
                        text: 'Down'
                    },
                    {
                        value: '0',
                        text: 'Up'
                    },
                ],
                success: function (response) {
                    toastr.success("success", response.message, toastOption);
                    getPayin();
                }, error: function (error) {
                    toastr.error("error", error.responseJSON.message, toastOption);
                },
            });
        }

    }
    this.setErrorHtml = () => {
        $('.preLoader').hide();
        FsHelper.unblockUi($("#custTablw"));
         $("#PgRoutersData").html(`
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

