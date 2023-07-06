class PgDashboard {

    PgDashboardPayload = {
        pg_type: "PAYIN",
        pg_name: "ALL",
        pg_account: "ALL",
        start_date: moment().format("YYYY-MM-DD"),
        end_date: moment().format("YYYY-MM-DD"),
    };

    load() {
        this.#getPgDashboardData();
    }

    #getPgDashboardData() {
        FsHelper.blockUi($("#dashboardData"));
        FsClient.post("/pg-management-dashboard/GetSummary", this.PgDashboardPayload)
            .then((response) => {
                let htmlData = `<table class="table table-hover table-bordered">
                                    <thead>
                                        <tr>
                                            <th>PG</th>
                                            ${this.PgDashboardPayload.pg_type === "PAYIN" ? `<th>Net Banking</th>` : ""}
                                            ${this.PgDashboardPayload.pg_type === "PAYIN" ? `<th>Card</th>` : ""}
                                            ${this.PgDashboardPayload.pg_type === "PAYIN" ? `<th>UPI</th>` : ""}
                                            <th>Total Turnover</th>
                                            <th>Total Limit</th>
                                            <th>Remaining Limit</th>
                                        </tr>
                                    </thead>
                                    <tbody id="pgDashboardData">`;
                (response.data).forEach((item, index) => {
                    htmlData += `<tr class="${this.#checkIsLimitOverLimit(item.total_turnover, (item.pgMeta ? (item.pgMeta.hasOwnProperty('turn_over') ? (item.pgMeta.turn_over ? item.pgMeta.turn_over : "-") : 0) : ""))}">
                                    <td>
                                        <span class="d-block mb-1">${item.pgMeta ? item.pgMeta.label : "-"} (${item.pgMeta ? item.pgMeta.account_id : "-"})</span>
                                        <span class="d-block">${item.pg_name}</span>
                                    </td>
                                    ${item.hasOwnProperty('total_nb_amount') ? `<td>₹ ${item.total_nb_amount}</td>` : ""}
                                    ${item.hasOwnProperty('total_card_amount') ? `<td>₹ ${item.total_card_amount}</td>` : ""}
                                    ${item.hasOwnProperty('total_upi_amount') ? `<td>₹ ${item.total_upi_amount}</td>` : ""}
                                    <td>₹ ${item.total_turnover}</td>
                                    <td>₹ ${item.pgMeta ? (item.pgMeta.hasOwnProperty('turn_over') ? (item.pgMeta.turn_over ? item.pgMeta.turn_over : "-") : 0) : 0}</td>
                                    <td>₹ ${item.pgMeta ? (this.#getRemainingLimit(item.total_turnover, (item.pgMeta.hasOwnProperty('turn_over') ? (item.pgMeta.turn_over ? item.pgMeta.turn_over : "-") : 0))) : 0}</td>
                                </tr>`;
                });
                htmlData += `</tbody>
                        </table>`;
                $("#dashboardData").html(htmlData);
            })
            .catch((error) => {
                console.log(error);
                this.#PgDashboardErrorDataHtml();
            })
            .finally(() => {
                FsHelper.unblockUi($("#dashboardData"));
            });
    }

    getPgAccountList(pgName) {
        FsHelper.blockUi($("#pgDashboardForm"));
        FsClient.post(`/payment-gateway/GetPaymentMetaLabelList/${this.PgDashboardPayload.pg_type}/${pgName}`, "")
            .then(response => {
                let htmlData = `<option value="ALL">ALL</option>`;
                if(response.data) {
                    (response.data).forEach((item, index) => {
                        htmlData += `<option value="${item.account_id}">${item.label}</option>`;
                    });
                }
                $("#pg_account").html(htmlData);
            })
            .catch(error => {
                console.log(error);
                $("#pg_account").html(`<option value="ALL">ALL</option>`);
            })
            .finally(() => {
                FsHelper.unblockUi($("#pgDashboardForm"));
            })
    }

    getPgList() {
        const payload = {
            pg_type: this.PgDashboardPayload.pg_type
        }
        FsHelper.blockUi($("#pgDashboardForm"));
        FsClient.post(`/pg-management-dashboard/GetPgList`, payload)
            .then(response => {
                let htmlData = `<option value="ALL">ALL</option>`;
                if(response.data) {
                    (response.data).forEach((item, index) => {
                        htmlData += `<option value="${item}">${item}</option>`;
                    });
                }
                $("#pg_name").html(htmlData);
            })
            .catch(error => {
                console.log(error);
                $("#pg_name").html(`<option value="ALL">ALL</option>`);
            })
            .finally(() => {
                FsHelper.unblockUi($("#pgDashboardForm"));
            })
    }

    #PgDashboardErrorDataHtml() {
        $("#dashboardData").html(`<tr>
                                <td colspan="10" class="text-center">No Data To Display</td></tr>`);
    }

    #getRemainingLimit(usedLimit, totalLimit) {
        if(totalLimit > 0) {
            let remainLimit = (totalLimit - usedLimit).toFixed(2);
            let remainLimitInPercentage = ((remainLimit * 100) / totalLimit).toFixed(2);
            return `<span class="">${remainLimit} (${remainLimitInPercentage}%)</span>`;
        }
        return "-";
    }

    #checkIsLimitOverLimit(usedLimit, totalLimit) {

        if(totalLimit > 0) {
            usedLimit = parseFloat(usedLimit);
            totalLimit = parseFloat(totalLimit);
            if(usedLimit >= totalLimit) {
                return "bg-danger-light"
            }
        }
        return "";
    }
}
let pgDashboard = new PgDashboard();
pgDashboard.load();

$('.dashboard-daterange').daterangepicker({
    "autoApply": true,
    "autoUpdateInput": true,
    "locale": {
        "format": "DD/MM/YYYY",
        "separator": " - ",
        "applyLabel": "Apply",
        "cancelLabel": "Cancel",
        "fromLabel": "From",
        "toLabel": "To",
        "customRangeLabel": "Custom",
    },
    "linkedCalendars": false,
    "showCustomRangeLabel": false,
    "startDate": moment(),
    "endDate": moment(),
    "maxDate": moment(),
    "maxSpan": {
        "days": 30
    },
}, function(start, end, label,item) {
    $('input[name="daterange1"]').val(start.format('DD/MM/YYYY') + ' - ' + end.format('DD/MM/YYYY'));
});

$("#pg_system").change(() => {
    console.log($("#pg_system").val())
    pgDashboard.PgDashboardPayload.pg_type = $("#pg_system").val()
    pgDashboard.getPgList();
});

$("#pg_name").change(() => {
    pgDashboard.getPgAccountList($("#pg_name").val());
});

$("#pgDashboardForm").submit(() => {
    const pgDashboardFormData = getFormData($("#pgDashboardForm"));
    pgDashboard.PgDashboardPayload.pg_type = pgDashboardFormData.pg_system;
    pgDashboard.PgDashboardPayload.pg_name = pgDashboardFormData.pg_name;
    pgDashboard.PgDashboardPayload.pg_account = pgDashboardFormData.pg_account;
    if(pgDashboardFormData.daterange1) {
        let splitDate = pgDashboardFormData.daterange1.split(/-/);
        pgDashboard.PgDashboardPayload.start_date = moment(splitDate[0], 'DD/MM/YYYY').format('YYYY-MM-DD 00:00:00');
        pgDashboard.PgDashboardPayload.end_date = moment(splitDate[1], 'DD/MM/YYYY').format('YYYY-MM-DD 23:59:59');
    }
    pgDashboard.load();
});

function resetPgDashboardForm() {
    window.location.reload();
}
