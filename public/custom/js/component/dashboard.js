let MerchantSummaryPostData = {
    start_date: todayStartDate,
    end_date: todayEndDate,
};
let PgSummaryPostData = {
    start_date: todayStartDate,
    end_date: todayEndDate,
};

let CrPostData = {
    filter_data: {
        start_date:null,
        end_date:null,
    },
    page_no: 1,
    limit: 10,
};

let CrPaginateData = {
    link_limit: 2,
    from: 2,
    to: 2,
    total: null,
    is_last: null,
    current_item_count: null,
    current_page: null,
    last_page: null,
};

DzpDatePickerService.init();

let ChartPostData = {
    start_date: todayStartDate,
    end_date: todayEndDate,
    merchant_id: null,
    cust_level: null,
};

let txnSummeryPostData = {
    start_date: todayStartDate,
    end_date: todayEndDate,
    merchant_id: null,
};



let txnChartObject = null;
(new MerchantDashboard()).CrData();
(new MerchantDashboard()).getCount();
(new MerchantDashboard()).getPayoutCount();
(new MerchantDashboard()).getData();
(new MerchantDashboard()).getPgSummary();
(new MerchantDashboard()).getTxnSummary();
(new MerchantDashboard()).getPayoutSummary();

function MerchantDashboard() {
    this.getCount = () => {
        FsHelper.blockUi("#dashboardSummery");
        FsClient.post('/dashboard/GetMMDashboardSummary', "") .then(response => {
                let summery = "";
                if (response.data) {
                    $.each(response.data, (index, item) => {
                        summery += `
                                <div class="col-md-3 grid-margin stretch-card">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-baseline mb-2">
                                                <h6 class="card-title mb-0">
                                                    ${index}
                                                </h6>
                                            </div>
                                            <div class="row">
                                                <div class="col-12 col-md-12 col-xl-12">
                                                    <h5 class="mb-2 dz-responsive-text">${item}</h5>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        ` })
                    $("#dashboard_summery").html(summery);
                }
            })
            .catch(error => {
                toastr.error(error.responseJSON.message, "error", toastOption);
            }).finally(function () {
            FsHelper.unblockUi("#dashboardSummery");
        });
    }
    this.getTxnSummary = () => {
        FsHelper.blockUi("#transactionSummery");
        FsClient.post('/dashboard/GetMMTransactionSummary', txnSummeryPostData) .then(response => {
            console.log(response);
            let summery = "";
            if (response.data) {
                $.each(response.data, (index, item) => {
                    summery += `
                                <div class="col-md-2 grid-margin stretch-card">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-baseline mb-2">
                                                <h6 class="card-title mb-0">
                                                    ${index} ( ${item.txn_count} ) | ${item.txn_count_per}%
                                                </h6>
                                            </div>
                                            <div class="row">
                                                <div class="col-12 col-md-12 col-xl-12">
                                                    <h5 class="mb-2 dz-responsive-text">${item.total_amount}</h5>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        ` })
                $("#transactionSummerydata").html(summery);
            }
        })
            .catch(error => {
                toastr.error(error.responseJSON.message, "error", toastOption);
            }).finally(function () {
            FsHelper.unblockUi("#transactionSummery");
        });
    }
    this.getPayoutSummary = () => {
        FsHelper.blockUi("#payouttxnSummerydata");
        FsClient.post('/dashboard/GetMMPayoutSummary', "") .then(response => {
            console.log(response);
            let summery = "";
            if (response.data) {
                $.each(response.data, (index, item) => {
                    summery += `
                                <div class="col-md-2 grid-margin stretch-card">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-baseline mb-2">
                                                <h6 class="card-title mb-0">
                                                    ${index} ( ${item.txn_count} ) | ${item.txn_count_per}%
                                                </h6>
                                            </div>
                                            <div class="row">
                                                <div class="col-12 col-md-12 col-xl-12">
                                                    <h5 class="mb-2 dz-responsive-text">${item.total_amount}</h5>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        ` })
                $("#payouttxnSummerydata").html(summery);
            }
        })
            .catch(error => {
                toastr.error(error.responseJSON.message, "error", toastOption);
            }).finally(function () {
            FsHelper.unblockUi("#payouttxnSummerydata");
        });
    }
    this.getPayoutCount = () => {
        FsHelper.blockUi("#payoutsummery");
        FsClient.post('/payout/summery', "") .then(response => {
            let summery = "";
            if (response.data) {
                $.each(response.data, (index, item) => {
                    summery += `
                                <div class="col-md-2 grid-margin stretch-card">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-baseline mb-2">
                                                <h6 class="card-title mb-0">
                                                    ${index}
                                                </h6>
                                            </div>
                                            <div class="row">
                                                <div class="col-12 col-md-12 col-xl-12">
                                                    <h5 class="mb-2 dz-responsive-text">${item}</h5>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        ` })
                $("#payout_summery").html(summery);
            }
        })
            .catch(error => {
                toastr.error(error.responseJSON.message, "error", toastOption);
            }).finally(function () {
            FsHelper.unblockUi("#payoutsummery");
        });
    }


    this.getData = () => {
        FsHelper.blockUi("#dashboardData");
        FsClient.post('/dashboard/GetMMDashboardData', MerchantSummaryPostData) .then(response => {
                let MmdData="";
                if(response.data) {
                    console.log(response.data)
                    $.each(response.data, (index, item) => {
                        MmdData += `
                                 <tr>
                                      <td>${item.merchant_details.merchant_name ? item.merchant_details.merchant_name : "-"}</td>
                                      <td>${item.payin ? item.payin : "0"}</td>
                                      <td>${item.payout ? item.payout : "0"}</td>
                                      <td>${item.closing_balance ? item.closing_balance : "0"}</td>
                                      <td>${item.un_settled_balance ? item.un_settled_balance : "0"}</td>
                                      <td>${item.min_ticket ? item.min_ticket : "0"}</td>
                                      <td>${item.max_ticket ? item.max_ticket : "0"}</td>
                                 </tr>
                     `})
                    $("#mmDashboardData").html(MmdData);
                }
            })
            .catch(error => {
                console.log(error)
                toastr.error(error.responseJSON.message,"error",toastOption);
            }).finally(function (){
            FsHelper.unblockUi("#dashboardData");
        });
    }

    this.getPgSummary = () => {
        FsHelper.blockUi("#pgSummaryZone");
        FsClient.post('/dashboard/GetPgSummary', PgSummaryPostData) .then(response => {
                let pgCollectionHtmlData = "";
                let pgCollectionFilterHtmlData = `<button class="btn btn-sm btn-default active btn-pg-summary-filter mr-1" data-tr-id="tr-all">ALL</button>`;
                let pgCollectionFiler = [];
                if(response.data.collection) {
                    let totalAutoCollection = 0;
                    let totalManualCollection = 0;
                    (response.data.collection).forEach((item) => {
                        totalAutoCollection = parseFloat(item.total_auto_collection) + totalAutoCollection;
                        totalManualCollection = parseFloat(item.total_manual_collection) + totalManualCollection;
                        pgCollectionFiler.push(item.pg_type);
                        pgCollectionHtmlData += `
                                 <tr class="pg-summary-filter tr-${item.pg_type}">
                                      <td>
                                        ${item.pg_detail ? (item.pg_detail.pg_name ? `<span class="d-block mt-1 font-weight-bold">${item.pg_detail ? item.pg_detail.pg_name : '-'}</span>` : '') : ''}
                                        ${item.pg_detail ? (item.pg_detail.label ? `<span class="d-block mt-1 font-weight-bold">${item.pg_detail ? item.pg_detail.label : '-'}</span>` : '') : ''}
                                        ${item.pg_detail ? (item.pg_detail.bank_name ? `<span class="d-block mt-1 font-weight-bold"><span class="text-muted">Bank:</span> ${item.pg_detail ? item.pg_detail.bank_name : '-'}</span>` : '') : ''}
                                        ${item.pg_detail ? (item.pg_detail.account_number ? `<span class="d-block mt-1 font-weight-bold"><span class="text-muted">A/C:</span> ${item.pg_detail ? item.pg_detail.account_number : '-'}</span>` : '') : ''}
                                      </td>
                                      <td>${item.total_auto_collection ? item.total_auto_collection : "0"}</td>
                                      <td>${item.total_manual_collection ? item.total_manual_collection : "0"}</td>
                                 </tr>`;
                    });

                    pgCollectionHtmlData += `
                                 <tr>
                                      <td>
                                        <span class="d-block mt-1 font-weight-bold">Total</span>
                                      </td>
                                      <td>${totalAutoCollection}</td>
                                      <td>${totalManualCollection}</td>
                                 </tr>`;

                    $("#pgCollectionSummary").html(pgCollectionHtmlData);
                    pgCollectionFiler = pgCollectionFiler.filter((v, i, a) => a.indexOf(v) === i);
                    if(pgCollectionFiler.length > 0) {
                        $.each(pgCollectionFiler, (index, item) => {
                            pgCollectionFilterHtmlData += `<button class="btn btn-sm btn-default btn-pg-summary-filter mr-1" data-tr-id="tr-${item}">${item}</button>`;
                        })
                    }
                    $("#pgCollectionFilter").html(pgCollectionFilterHtmlData);
                    $(".btn-pg-summary-filter").click((e) => {
                        const trId = e.target.attributes['data-tr-id'].value;
                        if(trId === "tr-all") {
                            $(".pg-summary-filter").show();
                        } else {
                            $(".pg-summary-filter").hide();
                            $("." + trId).show();
                        }
                        $(".btn-pg-summary-filter").removeClass("active");
                        e.target.classList.add("active")
                    })
                } else {
                    $("#pgCollectionSummary").html(`<tr><td colspan="3">No Data Found</td></tr>`);
                    $("#pgCollectionFilter").html(`<button class="btn btn-sm btn-default active mr-1 btn-pg-summary-filter" data-tr-id="tr-all">ALL</button>`);
                }

                let pgWithdrawalHtmlData = "";
                let pgWithdrawalFilterHtmlData = `<button class="btn btn-sm btn-default active btn-pg-summary-filter-wi mr-1" data-tr-id="tr-all">ALL</button>`;
                let pgWithdrawalFilter = [];

                if(response.data.withdrawal) {
                    let totalAutoWithdrawal = 0;
                    let totalManualWithdrawal = 0;
                    (response.data.withdrawal).forEach((item) => {
                        totalAutoWithdrawal = parseFloat(item.total_auto_withdrawal) + totalAutoWithdrawal;
                        totalManualWithdrawal = parseFloat(item.total_manual_withdrawal) + totalManualWithdrawal;
                        pgWithdrawalFilter.push(item.pg_type);
                        pgWithdrawalHtmlData += `
                                 <tr class="pg-summary-filter-wi tr-${item.pg_type}">
                                      <td>
                                        ${item.pg_detail ? (item.pg_detail.pg_name ? `<span class="d-block mt-1 font-weight-bold">${item.pg_detail ? item.pg_detail.pg_name : '-'}</span>` : '') : ''}
                                        ${item.pg_detail ? (item.pg_detail.label ? `<span class="d-block mt-1 font-weight-bold">${item.pg_detail ? item.pg_detail.label : '-'}</span>` : '') : ''}
                                        ${item.pg_detail ? (item.pg_detail.account_number ? `<span class="d-block mt-1 font-weight-bold"><span class="text-muted">A/C: </span>${item.pg_detail ? item.pg_detail.account_number : '-'}</span>` : '') : ''}
                                      </td>
                                      <td>${item.total_auto_withdrawal ? item.total_auto_withdrawal : "0"}</td>
                                      <td>${item.total_manual_withdrawal ? item.total_manual_withdrawal : "0"}</td>
                                 </tr>
                     `})
                    pgWithdrawalHtmlData += `
                                 <tr>
                                      <td>
                                        <span class="d-block mt-1 font-weight-bold">Total</span>
                                      </td>
                                      <td>${totalAutoWithdrawal}</td>
                                      <td>${totalManualWithdrawal}</td>
                                 </tr>`;
                    $("#pgWithdrawalSummary").html(pgWithdrawalHtmlData);
                    pgWithdrawalFilter = pgWithdrawalFilter.filter((v, i, a) => a.indexOf(v) === i);
                    if(pgWithdrawalFilter.length > 0) {
                        $.each(pgWithdrawalFilter, (index, item) => {
                            pgWithdrawalFilterHtmlData += `<button class="btn btn-sm btn-default btn-pg-summary-filter-wi mr-1" data-tr-id="tr-${item}">${item}</button>`;
                        })
                    }
                    $("#pgWithdrawalFilter").html(pgWithdrawalFilterHtmlData);
                    $(".btn-pg-summary-filter-wi").click((e) => {
                        const trId = e.target.attributes['data-tr-id'].value;
                        if(trId === "tr-all") {
                            $(".pg-summary-filter-wi").show();
                        } else {
                            $(".pg-summary-filter-wi").hide();
                            $("." + trId).show();
                        }
                        $(".btn-pg-summary-filter-wi").removeClass("active");
                        e.target.classList.add("active")
                    })
                } else {
                    $("#pgWithdrawalSummary").html(`<tr><td colspan="3">No Data Found</td></tr>`);
                    $("#pgWithdrawalFilter").html(`<button class="btn btn-sm btn-default active mr-1 btn-pg-summary-filter-wi" data-tr-id="tr-all">ALL</button>`);
                }

            })
            .catch(error => {
                console.log(error)
                toastr.error(error.responseJSON.message, "error", toastOption);
            })
            .finally(function (){
                FsHelper.unblockUi("#pgSummaryZone");
            });
    }

        this.CrData = () => {
            FsHelper.blockUi($("#payoutCrData"));
            FsClient.post("/dashboard/payoutCRData", CrPostData).then(this.handleResponse).catch(this.handleError);
        }

        this.handleResponse = (data) => {
            FsHelper.unblockUi($("#payoutCrData"));
            if(data.status) {
                console.log(data)
                CrPaginateData.current_page = data.current_page;
                CrPaginateData.last_page = data.last_page;
                CrPaginateData.is_last_page = data.is_last_page;
                CrPaginateData.total = data.total_item;
                CrPaginateData.current_item_count = data.current_item_count;
                this.setCRHtmlData(data.data);
                $('#pagination').show();
            } else {
                this.setErrorHtml();
            }
        }

        this.handleError = (error) => {
            this.setErrorHtml();
        }
        this.setCRHtmlData = (data) => {
            if(data && data.length > 0) {
                let htmlData = "";
                data.forEach((item, index) => {
                    htmlData += `<tr>
                                <td>
                                    <span class="d-block font-weight-bold">${item.id ? item.id : "-"}</span>
                                </td>

                                <td>
                                    <span class="d-block mb-1"><span class="font-weight-bold text-muted"># : </span> ${item.account_id ? item.account_id : ""}</span>
                                    <span class="d-block"><span class="font-weight-bold text-muted">No : </span> ${item.account_number ? item.account_number : ""}</span>
                                </td>
                                <td>
                                     <span class="d-block mb-1"><span class="font-weight-bold text-muted">Name : </span> ${item.pg_name ? item.pg_name : ""}</span>
                                     <span class="d-block"><span class="font-weight-bold text-muted">Ref Id : </span> ${item.pg_ref_id ? item.pg_ref_id : ""}</span>
                                </td>

                                <td>
                                    <span> ${item.utr_number ? item.utr_number : "0"}</span>
                                </td>
                                <td>
                                     <span class="d-block mb-1"><span class="font-weight-bold text-muted">Added : </span> ${item.added_bal ? item.added_bal : ""}</span>
                                     <span class="d-block"><span class="font-weight-bold text-muted">Closing : </span> ${item.closing_bal ? item.closing_bal : ""}</span>
                                </td>
                                <td>
                                     <span class="d-block mb-1"><span class="font-weight-bold text-muted">Create : </span> ${item.created_at_ist ? item.created_at_ist : ""}</span>
                                     <span class="d-block"><span class="font-weight-bold text-muted">Update : </span> ${item.updated_at_ist ? item.updated_at_ist : ""}</span>
                                </td>

                            </tr>`;
                });
                $('.preLoader').hide()
                $("#GetPayoutCrData").html(htmlData);
                setPaginateButton("page-change-event", CrPaginateData, CrPostData);
            } else {
                this.setErrorHtml();
            }
        }
        this.setErrorHtml = () => {
            FsHelper.unblockUi($("#payoutCrData"));
            $('.preLoader').hide();
            $('#pagination').hide();
            $("#GetPayoutCrData").html(`
            <tr>
                <td colspan="8">
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

$("#dashboardForm").on("submit", () => {
    FsHelper.blockUi("#dashboard_page");
    const dashboardForm = getFormData($("#dashboardForm"));
    if(dashboardForm) {
        let splitDate = dashboardForm.daterange1.split(/-/);
        if(splitDate) {
            MerchantSummaryPostData.start_date = moment(splitDate[0], 'DD/MM/YYYY').format('YYYY-MM-DD 00:00:00');
            MerchantSummaryPostData.end_date = moment(splitDate[1], 'DD/MM/YYYY').format('YYYY-MM-DD 23:59:59');
        } else {
            MerchantSummaryPostData.start_date = moment().format('YYYY-MM-DD 00:00:00');
            MerchantSummaryPostData.end_date = moment().format('YYYY-MM-DD 23:59:59');
        }
        (new MerchantDashboard()).getData();
    }
});

$("#pgSummaryForm").on("submit", () => {
    FsHelper.blockUi("#dashboard_page");
    const dashboardForm = getFormData($("#dashboardForm"));
    if(dashboardForm) {
        let splitDate = dashboardForm.daterange1.split(/-/);
        if(splitDate) {
            PgSummaryPostData.start_date = moment(splitDate[0], 'DD/MM/YYYY').format('YYYY-MM-DD 00:00:00');
            PgSummaryPostData.end_date = moment(splitDate[1], 'DD/MM/YYYY').format('YYYY-MM-DD 23:59:59');
        } else {
            PgSummaryPostData.start_date = moment().format('YYYY-MM-DD 00:00:00');
            PgSummaryPostData.end_date = moment().format('YYYY-MM-DD 23:59:59');
        }
        (new MerchantDashboard()).getPgSummary();
    }
});

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


$("#chartFormData").on("submit", () => {
    FsHelper.blockUi("#chartSec");
    const chartForm = getFormData($("#chartFormData"));
    console.log(chartForm);
    if(chartForm) {
        let splitDate = chartForm.daterange1.split(/-/);
        if(splitDate) {
            ChartPostData.start_date = moment(splitDate[0], 'DD/MM/YYYY').format('YYYY-MM-DD 00:00:00');
            ChartPostData.end_date = moment(splitDate[1], 'DD/MM/YYYY').format('YYYY-MM-DD 23:59:59');
        } else {
            ChartPostData.start_date = moment().format('YYYY-MM-DD 00:00:00');
            ChartPostData.end_date = moment().format('YYYY-MM-DD 23:59:59');
        }
        if(chartForm.merchant_id !== "All") {
            ChartPostData.merchant_id = removeItem($("#merchantList").val(), "All");
        }
        if(chartForm.cust_level !== "All") {
            ChartPostData.cust_level = chartForm.cust_level;
        }
        getDashboardTxnChartData();
    }
});

$("#tSummeryForm").on("submit", () => {
    // FsHelper.blockUi("#chartSec");
    const Data = getFormData($("#tSummeryForm"));
    console.log(Data);
    if(Data) {
        let splitDate = Data.daterange1.split(/-/);
        if(splitDate) {
            txnSummeryPostData.start_date = moment(splitDate[0], 'DD/MM/YYYY').format('YYYY-MM-DD 00:00:00');
            txnSummeryPostData.end_date = moment(splitDate[1], 'DD/MM/YYYY').format('YYYY-MM-DD 23:59:59');
        } else {
            txnSummeryPostData.start_date = moment().format('YYYY-MM-DD 00:00:00');
            txnSummeryPostData.end_date = moment().format('YYYY-MM-DD 23:59:59');
        }
        if(Data.merchant_id !== "All") {
            txnSummeryPostData.merchant_id = removeItem($("#merchantListforSummery").val(), "All");
        }
        (new MerchantDashboard()).getTxnSummary();
    }
});

function resetTSummeryFilter(){
    txnSummeryPostData = {
        start_date: todayStartDate,
        end_date: todayEndDate,
        merchant_id: null,
    }
    $('#tSummeryForm')[0].reset();
    (new MerchantDashboard()).getTxnSummary();
}

function resetChartFilter(){
    ChartPostData = {
        start_date: todayStartDate,
        end_date: todayEndDate,
        merchant_id: null,
        cust_level: null,
    }
    $('#chartFormData')[0].reset();
    getDashboardTxnChartData();
}

function removeItem(arr, value) {
    const index = arr.indexOf(value);
    if (index > -1) {
        arr.splice(index, 1);
    }
    return arr;
}



getDashboardTxnChartData();

function getDashboardTxnChartData() {
    FsHelper.blockUi("#chartSec");
    FsClient.post('/dashboard-txn-chart', ChartPostData)
        .then(response => {
            setDashboardTxnData(response.data);
            console.log(response)
        })
        .catch(error => {
            console.log(error);
            toastr.error(error.responseJSON.message, "error", toastOption);
        })
        .finally(() => {
            FsHelper.unblockUi("#chartSec");
        });
}




function setDashboardTxnData(data) {
    var options = {
        series: data.series,
        chart: {
            type: 'bar',
            height: 350,
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '70%',
                endingShape: 'rounded',
            },
        },
        colors: ['#3c9822', '#ea0c0c', '#debd1d', '#175bc1', '#ff6600'],
        dataLabels: {
            enabled: false,
        },
        stroke: {
            show: true,
            width: 2,
            colors: ['transparent']
        },
        xaxis: {
            categories: data.category,
        },
        yaxis: {
            title: {
                text: 'Count'
            }
        },
        fill: {
            opacity: 1,
            colors: ['#3c9822', '#ea0c0c', '#debd1d', '#175bc2', '#ff6600']
        },
        tooltip: {
            y: {
                formatter: function (val) {
                    return val
                }
            }
        }
    };

    if(txnChartObject) txnChartObject.destroy();
    txnChartObject = new ApexCharts(document.querySelector("#txnHoursChart"), options);
    txnChartObject.render();

}

EventListener.dispatch.on("page-change-event", (event, callback) => {
    CrPostData.page_no = callback.page_number;
    (new MerchantDashboard()).CrData();
});



$("#PayoutCrDataForm").on("submit", () => {
    const FormData = getFormData($("#PayoutCrDataForm"));
    CrPostData.filter_data = {
        start_date:null,
        end_date:null,
    }
    CrPostData.limit = FormData.Limit;
    CrPostData.page_no=1;

    if(FormData.daterange) {
        let splitDate = FormData.daterange.split(/-/);
        CrPostData.filter_data.start_date = moment(splitDate[0], 'DD/MM/YYYY').format('YYYY-MM-DD 00:00:00');
        CrPostData.filter_data.end_date = moment(splitDate[1], 'DD/MM/YYYY').format('YYYY-MM-DD 23:59:59');
    }
    (new MerchantDashboard()).CrData();
});



function resetPCrForm(){
    CrPostData.filter_data = {
        start_date:null,
        end_date:null,
    }
    CrPostData.page_no=1;
    CrPostData.limit=10;

    $('#PayoutCrDataForm')[0].reset();
    DzpDatePickerService.init();
    (new MerchantDashboard()).CrData();
}
