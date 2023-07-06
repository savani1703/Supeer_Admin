let riskDate = {
    filter_data: {
        start_date:todayStartDate,
        end_date:todayEndDate,
    }
};
CusatStateData()
DzpDatePickerService.init();
function CusatStateData(){
    console.log(riskDate)
    FsHelper.blockUi("#custStateDetail");
    FsClient.post('/risk/getStateData', riskDate) .then(response => {
        let summery = "";
        if (response.status) {
            response.data.forEach((item, index) => {
                summery += `
                           <tr>
                                <td>
                                     <span class="d-block">${item.cust_state ? item.cust_state : "--"}</span>
                                </td>
                                <td>
                                    <span class="d-block  mb-1"> <span class="text-muted font-weight-bold ">L1: </span>  ${item.Level1 ? item.Level1 : "0"}</span>
                                    <span class="d-block  mb-1"> <span class="text-muted font-weight-bold ">L2: </span>  ${item.Level2 ? item.Level2 : "0"}</span>
                                    <span class="d-block  mb-1"> <span class="text-muted font-weight-bold ">L3: </span>  ${item.Level3 ? item.Level3 : "0"}</span>
                                    <span class="d-block  mb-1"> <span class="text-muted font-weight-bold ">L4: </span>  ${item.Level4 ? item.Level4 : "0"}</span>
                                </td>
                                <td>
                                      <span class="d-block">${item.total_txn ? item.total_txn : "0"}</span>
                                </td>
                                <td>
                                      <span class="d-block"> <b>${item.total_success_txn ? item.total_success_txn : "0"}</b> (${item.success_sum ? item.success_sum :"0"}) </span>
                                </td>
                                <td>
                                      <span class="d-block"><b>${item.total_Processing_txn ? item.total_Processing_txn : "0"}</b> (${item.processing_sum ? item.processing_sum :"0"}) </span>
                                </td>
                                <td>
                                      <span class="d-block"><b>${item.total_Pending_txn ? item.total_Pending_txn : "0"}</b> (${item.pending_sum ? item.pending_sum :"0"}) </span>
                                </td>
                                <td>
                                      <span class="d-block"><b>${item.total_Initialized_txn ? item.total_Initialized_txn : "0"}</b> (${item.initialized_sum ? item.initialized_sum :"0"}) </span>
                                </td>
                                <td>
                                      <span class="d-block"><b>${item.total_Failed_txn ? item.total_Failed_txn : "0"}</b> (${item.failed_sum ? item.failed_sum :"0"}) </span>
                                </td>
                                <td>
                                      <span class="d-block">${NotAttempted(item.total_txn,item.total_success_txn,item.total_Processing_txn,item.total_Pending_txn,item.total_Initialized_txn,item.total_Failed_txn)} </span>
                                </td>
                             </tr>
                        ` })
            $("#CustStateData").html(summery);
        }
    })
        .catch(error => {
            toastr.error(error.responseJSON.message, "error", toastOption);
        }).finally(function () {
        $('.preLoader').hide()
        FsHelper.unblockUi("#custStateDetail");
    });
}

function NotAttempted(total,success,proccesing,pending,initialized,failed){
return not =total-success-proccesing-pending-initialized-failed;
}

$("#custDateFilter").on("submit", () => {
    const CustFilter = getFormData($("#custDateFilter"));
    console.log(CustFilter)
    riskDate.filter_data = {
        start_date: null,
        end_date: null,
        merchant_id: null,
    };
    riskDate.filter_data[CustFilter.FilterKey] = CustFilter.FilterValue;
    if(CustFilter.merchant_id !== "All") {
        riskDate.filter_data.merchant_id = CustFilter.merchant_id;
    }

    if(CustFilter.daterange) {
        let splitDate = CustFilter.daterange.split(/-/);
        riskDate.filter_data.start_date = moment(splitDate[0], 'DD/MM/YYYY').format('YYYY-MM-DD 00:00:00');
        riskDate.filter_data.end_date = moment(splitDate[1], 'DD/MM/YYYY').format('YYYY-MM-DD 23:59:59');
    }
    CusatStateData()
});

function resetFilter(){
    riskDate.filter_data = {
        start_date:todayStartDate,
        end_date:todayEndDate,
        merchant_id:null,
    }
    CusatStateData()
}
