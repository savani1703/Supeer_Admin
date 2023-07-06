class PayoutBankDown {
    load() {
        this.#getBouncerData();
    }

    #getBouncerData() {
        FsHelper.blockUi($("#bouncerData"));
        FsClient.post("/developer/dashboard/GetPayoutDownBanks", "")
            .then((response) => {
                this.#setPayoutBankDownDataHtml(response.data);
            })
            .catch((error) => {
                this.#setPayoutBankDownErrorDataHtml();
            })
            .finally(() => {
                FsHelper.unblockUi($("#bankDownData"));
            });
    }

    deleteAllData() {
        FsHelper.blockUi($("#bankDownData"));
        FsClient.post("/developer/dashboard/DeletePayoutDownBank", "")
            .then(response => {
                toastr.success(response.message,"success",toastOption);
                this.#getBouncerData();
            })
            .catch(error => {
                toastr.error(error.responseJSON.message,"error",toastOption);
            })
            .finally(() => {
                FsHelper.unblockUi($("#bankDownData"));
            })
    }

    #deleteData(listId) {
        FsHelper.blockUi($("#bankDownData"));
        FsClient.post("/developer/dashboard/DeleteByIdPayoutDownBank", {list_id: listId})
            .then(response => {
                toastr.success(response.message,"success",toastOption);
                this.#getBouncerData();
            })
            .catch(error => {
                toastr.error(error.responseJSON.message,"error",toastOption);
            })
            .finally(() => {
                FsHelper.unblockUi($("#bankDownData"));
            })
    }

    #setPayoutBankDownDataHtml(data) {
        let htmlData = "";
        data.forEach((item) => {
            htmlData += `<tr>
                            <td>${item.bank_name}</td>
                            <td>${item.ifsc_prefix}</td>
                            <td>${item.is_down ? "Yes" : "No"}</td>
                            <td>${item.total_count}</td>
                            <td>${item.total_amount}</td>
                            <td>${item.last_down_at}</td>
                            <td><button class="btn btn-sm btn-primary btn-delete-data" data-bank-list-id="${item.id}">Delete</button></td>
                        </tr>`;
        })
        $("#bankDownData").html(htmlData);
        $(".btn-delete-data").click((e) => {
            let fd = e.target.attributes['data-bank-list-id'].value;
            this.#deleteData(fd);
        })
    }

    #setPayoutBankDownErrorDataHtml() {
        $("#bankDownData").html(`<tr>
                                <td colspan="10" class="text-center">No Data To Display</td></tr>`);
    }
}

let bouncerData = new PayoutBankDown();
bouncerData.load();

function deleteAllBank() {
    new PayoutBankDown().deleteAllData();
}
