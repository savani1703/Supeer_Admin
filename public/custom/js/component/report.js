let PostData = {
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


(new Report()).getReport();
DzpDatePickerService.init();
function Report() {
    this.getReport = () => {
        FsHelper.blockUi($("#Report"));
        FsClient.post("/support/GetGeneratedReport", PostData).then(this.handleResponse).catch(this.handleError);
    }

    this.handleResponse = (data) => {
        FsHelper.unblockUi($("#Report"));
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

    this.handleError = (error) => {
        console.log(error)
        this.setErrorHtml();
    }
    this.setTxnHtmlData = (data) => {
        if(data && data.length > 0) {
            let htmlData = "";
            data.forEach((item, index) => {
                htmlData += `<tr>
                                <td>
                                     <span class="d-block"> <span class="text-muted font-weight-bold"> Create: </span> ${item.created_at_ist ? item.created_at_ist : ""}</span>
                                     <span class="d-block"> <span class="text-muted font-weight-bold"> Update: </span> ${item.updated_at_ist ? item.updated_at_ist : ""}</span>
                                </td>
                                <td>  ${item.email_id ? item.email_id : ""}   </td>
                                <td> ${item.file_name ? item.file_name : ""}  </td>
                                <td> ${item.expire_at_ist ? item.expire_at_ist : ""}  </td>
                                <td>
                                    <span class="badge ${this.getStatusClass(item.status)}"> ${item.status ? item.status : ""}</span>
                                </td>
                                <td> ${item.report_type ? item.report_type : ""} </td>
                                <td> ${item.count ? item.count : ""}</span> </td>
                                <td> ${setProgressBarAttribute(item.progress, item.count)}  </td>
                                <td> ${item.download_id ? item.download_id : ""}</span> </td>
                                <td>
                                   ${setDownloadButtonAttribute(item.is_expire,item.download_url)}
                                </td>
                            </tr>`;
            });
            $('.preLoader').hide()
            $("#reportData").html(htmlData);
            setPaginateButton("page-change-event", PaginateData, PostData);
        } else {
            this.setErrorHtml();
        }
    }

    this.getStatusClass = (status) => {
        let badge = "badge-danger-muted";
        if(status === "Success") {
            badge = "badge-success";
        }
        if(status === "Failed") {
            badge = "badge-danger";
        }
        if(status === "Initialized") {
            badge = "badge-warning";

        } if(status === "Processing") {
            badge = "badge-info";
        }
        if(status === "Expired") {
            badge = "badge-info";
        }
        if(status === "Pending") {
            badge = "badge-outlineprimary";
        }
        if(status === "Not Attempted") {
            badge = "badge-outlineinfo";
        }
        return badge;
    }
    this.setErrorHtml = () => {
        $('.preLoader').hide();
        $('#pagination').hide();
        $("#reportData").html(`
            <tr>
                <td colspan="12">
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


EventListener.dispatch.on("page-change-event", (event, callback) => {
    PostData.page_no = callback.page_number;
    (new Report()).getReport();
});

function refreshReport(){
    (new Report()).getReport();
}
function setProgressBarAttribute(progress,count){
    let todata = ( progress/ count) * 100;
    return `<div class="progress mt-1">
                <div class="progress-bar progress-bar-striped active" role="progressbar" style="width: ${todata}%">
                    ${todata > 100 ? 100 :todata}%
                </div>
            </div>`
}

function setDownloadButtonAttribute(is_expire,download_url){
    if(is_expire){
        return `<span class="badge badge-danger">Expired</span>`;
    }else if(download_url){
        return ` <a href="${download_url}" class="btn btn-primary border-0" type="submit" >Download Report</a>`
    }else{
        return ` `
    }
}
