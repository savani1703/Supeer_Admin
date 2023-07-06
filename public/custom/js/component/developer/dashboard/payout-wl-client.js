class PayoutWhiteListClient {
    load() {
        this.#getPayoutWhiteListClientData();
    }

    #getPayoutWhiteListClientData() {
        FsHelper.blockUi($("#payoutWlDownData"));
        FsClient.post("/developer/dashboard/GetPayoutWhiteListClient", "")
            .then((response) => {
                this.#setPayoutWhiteListClientDataHtml(response.data);
            })
            .catch((error) => {
                console.log(error)
                this.#setPayoutWhiteListClientErrorDataHtml();
            })
            .finally(() => {
                FsHelper.unblockUi($("#payoutWlDownData"));
            });
    }

    #updateData(payload) {
        FsHelper.blockUi($("#payoutWlDownData"));
        FsClient.post("/developer/dashboard/UpdatePayoutWhiteListClientStatus", payload)
            .then(response => {
                toastr.success(response.message,"success",toastOption);
                this.#getPayoutWhiteListClientData();
            })
            .catch(error => {
                toastr.error(error.responseJSON.message,"error",toastOption);
            })
            .finally(() => {
                FsHelper.unblockUi($("#payoutWlDownData"));
            })
    }
    #updateDataManual(payload) {
        FsHelper.blockUi($("#payoutWlDownData"));
        FsClient.post("/developer/dashboard/UpdatePayoutWhiteListClientStatus/manual", payload)
            .then(response => {
                toastr.success(response.message,"success",toastOption);
                this.#getPayoutWhiteListClientData();
            })
            .catch(error => {
                toastr.error(error.responseJSON.message,"error",toastOption);
            })
            .finally(() => {
                FsHelper.unblockUi($("#payoutWlDownData"));
            })
    }

    #setPayoutWhiteListClientDataHtml(data) {
        let htmlData = "";
        data.forEach((item) => {
            htmlData += `<tr>
                            <td>
                                <span class="d-block font-weight-bold mb-1">${item.merchant_details ? item.merchant_details.merchant_name : ""}</span>
                                <span class="d-block"><small>${item.merchant_id ? item.merchant_id : ""}</small></span>
                            </td>
                            <td>
                                <input type="checkbox" class="toggle-client" data-toggle="switchbutton" id="${item.merchant_id}" ${item.is_active ? "checked" : ""} data-onstyle="primary">
                            </td>
                            <td>
                                <span class="d-block font-weight-bold pt-2">
                                     <a href="#" class="clienwhitelistminlimit"
                                       data-type="text"
                                       data-pk="${item.merchant_id}"
                                       data-column_name="min_auto_limit"
                                       data-abc="true">${item.min_auto_limit ? item.min_auto_limit : "0"}</a>
                                    </span>
                            </td>
                             <td>
                                <span class="d-block font-weight-bold pt-2">
                                 <a href="#" class="clienwhitelistminlimit"
                                   data-type="text"
                                   data-pk="${item.merchant_id}"
                                   data-column_name="max_auto_limit"
                                   data-abc="true">${item.max_auto_limit ? item.max_auto_limit : "0"}</a>
                                </span>
                            </td>
                            <td>
                                <input type="checkbox" class="toggle-client-manual" data-toggle="switchbutton" id="${item.merchant_id}" ${item.is_manual_payout ? "checked" : ""} data-onstyle="primary">
                            </td>
                        </tr>`;

        })



        $("#payoutWlDownData").html(htmlData);
        document.querySelectorAll(".toggle-client").forEach(it => {
            it.switchButton({
                onlabel: 'Active',
                offlabel: 'Deactive'
            });
        })
        $(".toggle-client").change((e) => {
            const payload = {
                merchant_id: e.target.id,
                status: $(e.target).prop('checked') ? 1 : 0
            };
            this.#updateData(payload)
        })
        document.querySelectorAll(".toggle-client-manual").forEach(it => {
            it.switchButton({
                onlabel: 'Active',
                offlabel: 'Deactive'
            });
        })
        $(".toggle-client-manual").change((e) => {
            const payload = {
                merchant_id: e.target.id,
                is_manual_payout: $(e.target).prop('checked') ? 1 : 0
            };
            this.#updateDataManual(payload)
        })
        $('.clienwhitelistminlimit').editable({
            params: function(params) {
                params.column_name = $(this).attr("data-column_name");
                params.pk = $(this).attr("data-pk");
                return params;
            },
            type: 'text',
            url: '/developer/dashboard/editPayoutWhiteListClientLimit',
            title: 'clienwhitelistminlimit',
            name: 'clienwhitelistminlimit',
            success: function (response) {
                toastr.success("success", response.message, toastOption);
                bouncerData.load();
            }, error: function (error) {
                toastr.error("error", error.responseJSON.message, toastOption);
                getPayin();
            },
        });
    }
    #setPayoutWhiteListClientErrorDataHtml() {
        $("#payoutWlDownData").html(`<tr>
                                <td colspan="4" class="text-center">No Data To Display</td></tr>`);
    }
}

let bouncerData = new PayoutWhiteListClient();
bouncerData.load();

