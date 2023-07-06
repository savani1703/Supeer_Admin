const pgName = location.pathname.split("/")[3];
const pgType = location.pathname.split("/")[2];

let metaModule = new PaymentMetaModule(pgName, pgType);

let PgMetaPostData = {
    filter_data: {
        start_date:null,
        end_date:null,
    },
    page_no: 1,
    limit: 50,
};

let PgMetaPaginateData = {
    link_limit: 2,
    from: 2,
    to: 2,
    total: null,
    is_last: null,
    current_item_count: null,
    current_page: null,
    last_page: null,
};

(new PgMeta()).loadPgMeta();

function PgMeta() {

    this.loadPgMeta = () => {
        FsHelper.blockUi($("#pgMetaTable"));
        FsClient.post(`/payment-gateway/GetPaymentMeta/${pgType}/${pgName}`, PgMetaPostData)
            .then((response) => {
                PgMetaPaginateData.current_page = response.current_page;
                PgMetaPaginateData.last_page = response.last_page;
                PgMetaPaginateData.is_last_page = response.is_last_page;
                PgMetaPaginateData.total = response.total_item;
                PgMetaPaginateData.current_item_count = response.current_item_count;
                $("#pgMetaData").html(metaModule.renderMetaTemplate(response.data, response.config))
                setPaginateButton("page-change-event", PgMetaPaginateData, PgMetaPostData);
                $('.pgMetaInLineEdit').editable({
                    success: function(response, newValue) {
                        toastr.success(response.message,"success",toastOption);
                        (new PgMeta()).loadPgMeta();
                    },
                    error: function(error) {
                        toastr.error(error.responseJSON.message,"error", toastOption);
                        (new PgMeta()).loadPgMeta();
                    }
                });
            })
            .catch((error) => {
                this.setErrorHtml();
            }).finally(() => FsHelper.unblockUi($("#pgMetaTable")));
    }

    this.addPaymentMeta = (formData) => {
        FsHelper.blockUi($("#addMetaForm"));
        const __formData = {
            form_data: formData
        };
        FsClient.post(`/payment-gateway/AddPaymentMeta/${pgType}/${pgName}`, __formData)
            .then((response) => {
                toastr.success(response.message,"success",toastOption);
                $("#addMetaForm")[0].reset();
                $("#addMetaModal").modal("hide");
                resetMetaForm();
            })
            .catch((error) => {
                toastr.error(error.responseJSON.message,"error", toastOption);
            })
            .finally(() => FsHelper.unblockUi($("#addMetaForm")))
    }

    this.setErrorHtml = () => {
        $('#pagination').hide();
        let element = document.getElementById("dynamicColspan");
        const numberOfChildren = element.getElementsByTagName('th').length;
        $("#pgMetaData").html(`
            <tr>
                <td colspan="${numberOfChildren > 0 ? numberOfChildren : 10 }">
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

function resetMetaForm() {
    PgMetaPostData = {
        filter_data: null,
        page_no: 1,
        limit: 50,
    };
    (new PgMeta()).loadPgMeta();
}

$("#addMetaForm").on("submit", (e) => {
    (new PgMeta()).addPaymentMeta(FsHelper.serializeObject($("#addMetaForm")));
});

EventListener.dispatch.on("page-change-event", (event, callback) => {
    PgMetaPostData.page_no = callback.page_number;
    (new PgMeta()).loadPgMeta();
});

$("#FilterForm").on("submit", () => {
    const FormData = getFormData($("#FilterForm"));
    console.log(FormData.FilterValue);
    PgMetaPostData = {
        filter_data: {
            account_number: null,
            upi_id: null,
            account_id: null,
            merchant_id: null,
            email_id: null,
        },
        page_no: 1,
        limit: 50,
    };

    PgMetaPostData.filter_data[FormData.FilterKey] = FormData.FilterValue;
    PgMetaPostData.limit = FormData.Limit;

    (new PgMeta()).loadPgMeta();
});


