let PostData = {
    filter_data: {
    },
    page_no: 1,
    limit: 50,
};

let PaginateData = {
    link_limit: 2,
    from: 2,
    to: 2,
    total: null,
    is_last: null,
    current_item_count: null,
    current_page: null,
    last_page: null,
};
const active_color = {
    1: 'badge-success',
    0: 'badge-danger',
};
const active_status = {
    1: 'Active',
    0: 'Deactivate',
};
const is_seamless = {
    1: 'Seamless',
    0: 'Hosted',
};
const has_sub_method = {
    1: 'Yes',
    0: 'No',
};

(new PaymentMethodData()).getPaymentMethod();
$("#FilerForm").on("submit", () => {
    const FormData = getFormData($("#FilerForm"));
    PostData.filter_data = {
        pxn_meta: null,
        pg_name: null,
        meta_code: null,
        method_name: null,
        method_code: null,
        currency: null,
        status:null,
    }
    PostData.filter_data[FormData.FilterKey] = FormData.FilterValue;
    PostData.filter_data["status"] = FormData.status;
    PostData.limit = FormData.Limit;
    PostData.page_no=1;
    (new PaymentMethodData()).getPaymentMethod();
});
EventListener.dispatch.on("txn-logs-page-change-event", (event, callback) => {
    PostData.page_no = callback.page_number;
    (new PaymentMethodData()).getPaymentMethod();
});

function PaymentMethodData() {
    this.getPaymentMethod = () => {
        FsHelper.blockUi($("#paymentMethodDetail"));
        FsClient.post("/support/GetPaymentMethods", PostData).then(this.handleResponse).catch(this.handleError);
    }
    this.handleResponse = (data) => {
        FsHelper.unblockUi($("#paymentMethodDetail"));
        if(data.status) {
            PaginateData.current_page = data.current_page;
            PaginateData.last_page = data.last_page;
            PaginateData.is_last_page = data.is_last_page;
            PaginateData.total = data.total_item;
            PaginateData.current_item_count = data.current_item_count;
            this.setTxnHtmlData(data.data);
            $('#pagination').show();
        } else {
            this.setErrorHtml();
        }
    }
    this.setTxnHtmlData = (data) => {
        if(data && data.length > 0) {
            let htmlData = "";
            data.forEach((item, index) => {
                let badgeColor = active_color[item.is_active];
                let isActive =  active_status[item.is_active];
                let IsSeamless =  is_seamless[item.is_seamless];
                let HasSubMethod =  has_sub_method[item.has_sub_method];

                htmlData += `<tr>
                <td>
                    <span><img class="pr-2" src="${item.available_payment_methods.sub_method_icon_url ? 'https://checkout.payin247.com/'+item.available_payment_methods.sub_method_icon_url : "Bank Logo"}" alt="BankLogo" style="height:35px;width:auto;border-radius: 0px;"></span>
                    <span class="font-weight-bold">${item.available_payment_methods.pg_method_name ? item.available_payment_methods.pg_method_name :""}</span>
                </td>
                <td>  ${item.pg_name ? item.pg_name :""}  </td>
                 <td> ${item.method_code ? item.method_code :""}</td>
                 <td> ${item.meta_code ? item.meta_code :""}</td>
                 <td> ${IsSeamless ? IsSeamless :""}</td>
                 <td><span class="badge ${badgeColor ? badgeColor: "badge-primary"}">  ${isActive ? isActive :""}</td></span>
                 <td> ${HasSubMethod ? HasSubMethod :""}</td>
                 <td> ${item.created_at_ist ? item.created_at_ist :""}</td>
                 <td> ${item.updated_at_ist ? item.updated_at_ist :""}</td>
            </tr>`;
            });
            $("#paymentMethodData").html(htmlData);
            setPaginateButton("txn-logs-page-change-event", PaginateData, PostData);

        } else {
            this.setErrorHtml();
        }
    }
    this.handleError = (error) => {
        if (error.status === 401){
            // FsHelper.unauthorizedUserPage("unauthorized_user");
            // $("#paykunMetaDetail").html('');
        }else {
            this.setErrorHtml();
        }
    }
    this.setErrorHtml = () => {
        $('#pagination').html('');
        FsHelper.unblockUi($("#paymentMethodDetail"));
        $("#paymentMethodData").html(`
            <tr>
                <td colspan="9">
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
function resetPaymentMethodForm(){
    PostData.filter_data = {
        pxn_meta: null,
        pg_name: null,
        meta_code: null,
        method_name: null,
        method_code: null,
        currency: null,
        status:null,
    }
    PostData.page_no=1;
    PostData.limit=50;
    $('#FilerForm')[0].reset();
    (new PaymentMethodData()).getPaymentMethod();
}

$("#paymentMetaForm").submit(() => {
    FsHelper.blockUi($("#paymentMetaForm"))
    const addPaymentMethodFormData = FsHelper.serializeObject($("#paymentMetaForm"))
    FsClient.post("/support/AddPaymentMethod", addPaymentMethodFormData)
        .then((response) => {
            toastr.success(response.message,"success",toastOption);
            $("#paymentMetaForm")[0].reset();
            $("#addPaymentMethod").modal("hide");
            (new PaymentMethodData()).getPaymentMethod();
        })
        .catch((error) => {
            toastr.error(error.responseJSON.message,"error",toastOption);
        })
        .finally(() => {
            FsHelper.unblockUi($("#paymentMetaForm"))
        })

});

function loadAvailableMethods(){
    FsHelper.blockUi($("#paymentMetaForm"))
    FsClient.post('/support/GetAvailableMethod').then(response => {
            let availableMethod = "";
            if (response.data) {
                $.each(response.data, (index, item) => {
                    availableMethod += `
                        <option value="${item.pg_method_id}">${item.pg_method_name}</option>
                `})
                $("#availableMethodData").html(availableMethod);
            }
        })
        .catch(error => {
            console.log(error)
            toastr.error(error.responseJSON.message,"error",toastOption);
        }).finally(function (){
        FsHelper.unblockUi($("#paymentMetaForm"))

    });
}

