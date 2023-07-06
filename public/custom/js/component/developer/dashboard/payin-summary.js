class PayInSummary {
    PGPayInPostData = {
        merchant_id: "ALL",
        start_date: moment().format("YYYY-MM-DD"),
        end_date: moment().format("YYYY-MM-DD")
    };

    load() {
        this.#getBouncerData();
    }

    #getBouncerData() {
        FsHelper.blockUi($("#dashboard_page"));
        FsClient.post("/developer/dashboard/GetPgPayInSummary", this.PGPayInPostData)
            .then((response) => {
                this.#setPgPayInSummaryDataHtml(response.data);
            })
            .catch((error) => {
                console.log(error)
                this.#setPgPayInSummaryErrorDataHtml();
            })
            .finally(() => {
                FsHelper.unblockUi($("#dashboard_page"));
            });
    }

    #setPgPayInSummaryDataHtml(data) {
        let htmlData = "";

        let totalPending = 0;
        let totalFailed = 0;
        let totalProcessing = 0;
        let totalSuccess = 0;
        let totalTxn = 0;

        data.forEach((item, index) => {
            totalPending = totalPending + (item['Pending'] ? parseInt(item['Pending']) : 0);
            totalFailed = totalFailed + (item['Failed'] ? parseInt(item['Failed']) : 0);
            totalProcessing = totalProcessing + (item['Processing'] ? parseInt(item['Processing']) : 0);
            totalSuccess = totalSuccess + (item['Success'] ? parseInt(item['Success']) : 0);
            totalTxn = totalTxn + (item['total_txn'] ? parseInt(item['total_txn']) : 0);

            htmlData += `<tr class="${this.#checkIsHigherFailedRatio(item['Success'], item['total_txn'], item['Pending'])}">
                            <td>${index + 1}</td>
                            <td><span class="font-weight-bold">${item.label}</span> (${item.pg_name}) (${item.meta_id})</td>
                            <td>${item['Processing'] ? item['Processing'] : 0} ${this.#getPercentage(item['Processing'], item['total_txn'])}</td>
                            <td>${item['Pending'] ? item['Pending'] : 0} ${this.#getPercentage(item['Pending'], item['total_txn'])}</td>
                            <td>${item['Success'] ? item['Success'] : 0} ${this.#getPercentage(item['Success'], item['total_txn'])}</td>
                            <td>${item['Failed'] ? item['Failed'] : 0} ${this.#getPercentage(item['Failed'], item['total_txn'])}</td>
                            <td>${item['last_success_txn_date'] ? item['last_success_txn_date'] : '-'}</td>
                            <td>${item['total_txn'] ? item['total_txn'] : 0}</td>
                        </tr>`;
        });
        htmlData += `<tr>
                        <td colspan="2">Total</td>
                        <td>${totalProcessing}</td>
                        <td>${totalPending}</td>
                        <td>${totalSuccess}</td>
                        <td>${totalFailed}</td>
                        <td> - </td>
                        <td>${totalTxn}</td>
                    </tr>`;
        $("#pgSummaryData").html(htmlData);
    }

    #setPgPayInSummaryErrorDataHtml() {
        $("#pgSummaryData").html(`<tr>
                                <td colspan="6" class="text-center">No Data To Display</td></tr>`);
    }

    #checkIsHigherFailedRatio(failed, total, pending) {
        failed = failed ? failed : 0;
        total = total ? total : 0;
        let isFailedDetect = false;
        let isPendingDetect = false;
        if(((failed * 100) / total) < 25) {
            isFailedDetect = true;
        }
        if(!isFailedDetect) {
            if(((pending * 100) / total) > 20) {
                isPendingDetect = true;
            }
        }

        if(isFailedDetect) {
            return "bg-danger text-white";
        }
        if(isPendingDetect) {
            return "bg-warning text-white";
        }
    }

    #getPercentage(status, total) {
        status = status ? status : 0;
        total = total ? total : 0;
        if(status > 0) {
            return "(" +((status * 100) / total).toFixed(2) + "%)";
        }
        return "";
    }
}

let payInSummary = new PayInSummary();
payInSummary.load();

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

$("#payINSummaryForm").submit(() => {
    const PGPayInPostData = getFormData($("#payINSummaryForm"));
    payInSummary.PGPayInPostData.merchant_id = PGPayInPostData.merchant_id;
    if(PGPayInPostData.daterange1) {
        let splitDate = PGPayInPostData.daterange1.split(/-/);
        payInSummary.PGPayInPostData.start_date = moment(splitDate[0], 'DD/MM/YYYY').format('YYYY-MM-DD 00:00:00');
        payInSummary.PGPayInPostData.end_date = moment(splitDate[1], 'DD/MM/YYYY').format('YYYY-MM-DD 23:59:59');
    }
    payInSummary.load();
});

function resetPayInSummaryForm() {
    window.location.reload();
}
