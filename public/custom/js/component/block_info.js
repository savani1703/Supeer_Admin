let BlockInfoPostData = {
    filter_data: {
    },
    report_type: 'BLOCK_INFO',
    page_no: 1,
    limit: 50,
};

let BlockInfoPaginateData = {
    link_limit: 2,
    from: 2,
    to: 2,
    total: null,
    is_last: null,
    current_item_count: null,
    current_page: null,
    last_page: null,
};

(new BlockInfo()).getBlockInfo();

function BlockInfo() {
    this.getBlockInfo = () => {
        FsHelper.blockUi($("#BlockInfo"));
        FsClient.post("/support/GetBlockInfo", BlockInfoPostData).then(this.handleResponse).catch(this.handleError);
    }
    this.handleResponse = (data) => {
        FsHelper.unblockUi($("#BlockInfo"));
        if(data.status) {
            BlockInfoPaginateData.current_page = data.current_page;
            BlockInfoPaginateData.last_page = data.last_page;
            BlockInfoPaginateData.is_last_page = data.is_last_page;
            BlockInfoPaginateData.total = data.total_item;
            BlockInfoPaginateData.current_item_count = data.current_item_count;
            this.setVpnHtmlData(data.data);
            $('#pagination').show();
        } else {
            this.setErrorHtml();
        }
    }

    this.handleError = (error) => {
        console.log(error)
        if (error.status === 401){
            FsHelper.unauthorizedUserPage("unauthorized_user");
            $("#payoutDetail").html('');
        }else {
            this.setErrorHtml();
        }
    }

    this.setVpnHtmlData = (data) => {
        if(data && data.length > 0) {
            let htmlData = "";
            data.forEach((item, index) => {
                htmlData += `<tr>
                                 <td>
                                    <span class="d-block font-weight-bold">${item.block_data ? item.block_data : ""}</span>
                                </td>
                                <td>
                                    <span class="d-block font-weight-bold">${item.column_name ? item.column_name : ""}</span>
                                </td>
                                <td>
                                    <span class="d-block font-weight-bold">${setBlockType(item.is_auto_block)}</span>
                                </td>
                                <td>
                                    <span class="d-block font-weight-bold">${item.created_at_ist ? item.created_at_ist : ""}</span>
                                </td>
                                <td>
                                    <button class="btn btn-danger btn-sm" onclick="deleteBlockCust('${item.block_data}')">Delete</button>
                                </td>
                            </tr>`;
            });
            $('.Loader').hide()
            $("#BlockInfo").html(htmlData);
            setPaginateButton("page-change-event", BlockInfoPaginateData, BlockInfoPostData);
        } else {
            this.setErrorHtml();
        }
    }
    this.setErrorHtml = () => {
        $('.Loader').hide();
        $('#pagination').hide();
        $("#BlockInfo").html(`
            <tr>
                <td colspan="10">
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

$("#BlockInfoForm").on("submit", () => {
    const FormData = getFormData($("#BlockInfoForm"));
    BlockInfoPostData.filter_data = {
        block_data: null,
    }
    BlockInfoPostData.filter_data[FormData.FilterKey] = FormData.FilterValue;
    BlockInfoPostData.limit = FormData.Limit;
    BlockInfoPostData.page_no=1;
    (new BlockInfo()).getBlockInfo();
});

EventListener.dispatch.on("page-change-event", (event, callback) => {
    BlockInfoPostData.page_no = callback.page_number;
    (new BlockInfo()).getBlockInfo();
});


function resetBlockInfoForm(){
    BlockInfoPostData.filter_data = {
        block_data: null
    }
    BlockInfoPostData.page_no = 1;
    BlockInfoPostData.limit = 50;

    $('#BlockInfoForm')[0].reset();
    (new BlockInfo()).getBlockInfo();
}


function deleteBlockCust(blockData){
    var myModal =  new jBox('Confirm', {
        confirmButton: 'YES',
        cancelButton: 'No',
        content: 'Are You Sure Do you want to Delete Block Customer?',
        confirm: function () {
            FsHelper.blockUi($("#BlockInfo"));
            const postData = {
                block_data: blockData
            };
            FsClient.post("/support/DeleteBlockInfo", postData).then(
                response => {
                    (new BlockInfo()).getBlockInfo();
                    toastr.success(response.message,"success",toastOption);
                }
            ).catch(error => {
                toastr.error(error.responseJSON.message,"error",toastOption);
            }).finally(function (){
                FsHelper.unblockUi($("#BlockInfo"));
            });
        },
        cancel : function (){
        }
    });
    myModal.open();
}

function generateBlockInfoReport() {
    FsClient.post("/support/GenerateReport", BlockInfoPostData).then(
        Response => {
            toastr.success(Response.message,"success",toastOption);
        }
    ).catch(Error => {
        console.log(Error)
        toastr.error(Error.responseJSON.message,"error",toastOption);
    });
}

function setBlockType(is_auto_block) {
    if(is_auto_block){
        return `<span class="badge badge-danger-muted" style="margin-top: 2px;">Auto Block</span>`;
    }
    return `<span class="badge badge-danger" style="margin-top: 2px;">Manually Block</span>`;
}
