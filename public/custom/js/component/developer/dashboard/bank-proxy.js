let isLoadded=false;
class ProxyList {
    load() {
        this.#getProxyList();
    }

    #getProxyList() {
        FsHelper.blockUi($("#blockAreA"));
        FsClient.post("/developer/dashboard/GetBankProxyList", "")
            .then(response => {
                this.#setHtmlData(response.data);
            })
            .catch(error => {
                console.log(error)
                this.#setErrotHtmlData();
            })
            .finally(() => {
                FsHelper.unblockUi($("#blockAreA"));
            })
    }

    addProxy(pd) {
        FsHelper.blockUi($("#addProxyForm"));
        FsClient.post("/developer/dashboard/AddBankProxy", pd)
            .then(response => {
                toastr.success(response.message,"success",toastOption);
                window.location.reload();
            })
            .catch(error => {
                toastr.error(error.responseJSON.message,"error",toastOption);
            })
            .finally(() => {
                FsHelper.unblockUi($("#addProxyForm"));
            })
    }

    #setHtmlData(data) {
        let htmlData = "";
        data.forEach((item, index) => {
            htmlData += ` <tr>
                                <td>${item.id}</td>
                                <td>${item.label_name}</td>
                                <td>${item.ip_proxy}</td>
                                <td>
                                    <a href="#" class="updateStatus"
                                         data-type="select"
                                         data-pk="${item.id}"
                                         data-abc="true">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                         ${item.is_active===1 ? "Yes"  : "No"}
                                    </a>
                                </td>
                                <td>
                                    ${item.created_at}
                                </td>
                               <td>
                                   <button onclick="deleteProxy('${item.id}')" class="btn btn-danger btn-sm">
                                         <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg></button>
                                </td>
                            </tr>`;
        });

        $("#proxyData").html(htmlData);
        if(!isLoadded) {
            isLoadded=true;
            $('#dataTableExample').DataTable({
                "aLengthMenu": [
                    [-1, 10, 30, 50],
                    ["All", 10, 30, 50]
                ],
                "pageLength": -1,
                "iDisplayLength": 10,
                "language": {
                    search: ""
                }
            });
            $('#dataTableExample').each(function () {
                var datatable = $(this);
                // SEARCH - Add the placeholder for Search and Turn this into in-line form control
                var search_input = datatable.closest('.dataTables_wrapper').find('div[id$=_filter] input');
                search_input.attr('placeholder', 'Search');
                search_input.removeClass('form-control-sm');
                // LENGTH - Inline-Form control
                var length_sel = datatable.closest('.dataTables_wrapper').find('div[id$=_length] select');
                length_sel.removeClass('form-control-sm');
            });
            loadIsBlockxEditable()
        }
    }

    #setErrotHtmlData() {
        $("#proxyData").html(`<tr>
                                <td colspan="10" class="text-center">No Data To Display</td></tr>`);
    }
}

let proxyList = new ProxyList();
proxyList.load();

$("#addProxyForm").submit(() => {
    const postData = FsHelper.serializeObject($("#addProxyForm"));
    proxyList.addProxy(postData)
});

$("#addProxyModal").on("hidden.bs.modal", () => {
    $("#addProxyForm")[0].reset();
});

function loadIsBlockxEditable() {
    $('.updateStatus').editable({
        type: 'select',
        url: '/developer/dashboard/EditBankProxyStatus',
        source: [
            {
                value: '1',
                text: 'Yes'
            },
            {
                value: '0',
                text: 'No'
            },
        ],
        success: function (response) {
            toastr.success("success", response.message, toastOption);
            proxyList.load();
         }, error: function (error) {
            console.log(error);
            //toastr.error("error", error.responseJSON.message, toastOption);
        },
    });
}

    function deleteProxy(id) {
             $.confirm({
            title: 'Delete Bank Proxy !',
            content: 'Are You Sure To Delete Bank Proxy  !',
            buttons: {
                confirm: function () {
                    FsHelper.blockUi($("#Merchant_page"));
                   let ProxyData = {
                        id: id,
                    }
                    FsClient.post("/developer/dashboard/DeleteByIdBankProxy", ProxyData).then(
                        Response => {
                            FsHelper.unblockUi($("#blockAreA"));
                            toastr.success(Response.message, "success", toastOption);
                            proxyList.load();
                            table.destroy();
                        }
                    ).catch(Error => {
                        console.log(error);
                       // toastr.error(Error.responseJSON.message, "error", toastOption);
                        FsHelper.unblockUi($("#blockAreA"));
                        proxyList.load();
                    });

                },
                cancel: function () {
                    $.alert('Canceled Delete Proxy Bank !');
                },
            }
        });
    }
