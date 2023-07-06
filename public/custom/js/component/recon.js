$("#reconForm").on("submit", (e) => {
    e.preventDefault();
    let reconData = getFormData($("#reconForm"));
    localStorage.setItem('transactionKey',(reconData.FilterValue));
    let type= reconData.FilterKey === "transaction_id" ? "PAYIN" : 'PAYOUT'
    let postData = {
        id:reconData.FilterValue,
        type:type
    };
    FsClient.post('/payment/ReconciliationPayment',postData)
        .then(response => {
            $("#responseData").html(atob(response.data))
            let element = $("#pg_org_res");
            try {
                let obj = JSON.parse(element.text());
                element.html(JSON.stringify(obj, undefined, 2));
            } catch (e) {

            }
        })
        .catch(error => {
            console.log(error);
            $('#recon_data').hide();
            $('#pg_org_res').hide();
            toastr.error(error.responseJSON.message,"error",toastOption);
            $(".preLoader").hide();
        });
});


function transactionRecon(action,type){
    $(".preLoader").show();
    let id = localStorage.getItem('transactionKey');
    let transactionReconDetails = {
        id : id,
        action          : action,
        type          : type,
    }
    FsClient.post('/payment/ReconciliationPaymentAction',transactionReconDetails)
        .then(response => {
            toastr.success(response.message,"success",toastOption);
            $(".preLoader").hide();
        })
        .catch(error => {
            toastr.error(error.responseJSON.message,"error",toastOption);
            $(".preLoader").hide();
        });
}

function resetReconForm() {
    $("#reconForm")[0].reset();
    $("#responseData").html("");
}
