let riskDate = {
    filter_data: {
        start_date:todayStartDate,
        end_date:todayEndDate,
    }
};

DzpDatePickerService.init();
load()
    function load(){
    console.log(riskDate)
        FsHelper.blockUi("#custSummery");
        FsClient.post('/risk/getCustSummery', riskDate) .then(response => {
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
                                <span class="pb-2 pr-2 font-weight-bold" style="text-align: right"><a href="#">More</a></span>
                            </div>
                        </div>
                        ` })
                $("#cust_Summery").html(summery);
              }
            })
            .catch(error => {
                toastr.error(error.responseJSON.message, "error", toastOption);
            }).finally(function () {
            FsHelper.unblockUi("#custSummery");
        });
}



$("#custDateFilter").on("submit", () => {
    const CustFilter = getFormData($("#custDateFilter"));
    riskDate.filter_data = {
        start_date: null,
        end_date: null,
    };
    if(CustFilter.daterange) {
        let splitDate = CustFilter.daterange.split(/-/);
        riskDate.filter_data.start_date = moment(splitDate[0], 'DD/MM/YYYY').format('YYYY-MM-DD 00:00:00');
        riskDate.filter_data.end_date = moment(splitDate[1], 'DD/MM/YYYY').format('YYYY-MM-DD 23:59:59');
    }
    load()
});

function resetDateFilter(){
    riskDate.filter_data = {
        start_date:todayStartDate,
        end_date:todayEndDate,
    }
    load()
}
