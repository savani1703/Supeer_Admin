class ProxyList {
    load() {
        this.#getProxyList();
    }

    #getProxyList() {
        FsHelper.blockUi($("#blockAreA"));
        FsClient.post("/developer/dashboard/GetProxyList", "")
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
        FsClient.post("/developer/dashboard/AddProxy", pd)
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
                                    ${item.is_get === "1" ? "YES" : "NO"}
                                </td>
                                <td>
                                    ${item.created_at}
                                </td>
                            </tr>`;
        });
        $("#proxyData").html(htmlData);
        $('#dataTableExample').DataTable({
            destroy: true,
            "aLengthMenu": [
                [-1,10, 30, 50],
                ["All",10, 30, 50]
            ],
            "pageLength": -1,
            "iDisplayLength": 10,
            "language": {
                search: ""
            }
        });
        $('#dataTableExample').each(function() {
            var datatable = $(this);
            // SEARCH - Add the placeholder for Search and Turn this into in-line form control
            var search_input = datatable.closest('.dataTables_wrapper').find('div[id$=_filter] input');
            search_input.attr('placeholder', 'Search');
            search_input.removeClass('form-control-sm');
            // LENGTH - Inline-Form control
            var length_sel = datatable.closest('.dataTables_wrapper').find('div[id$=_length] select');
            length_sel.removeClass('form-control-sm');
        });
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
