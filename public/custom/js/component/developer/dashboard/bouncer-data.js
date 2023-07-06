class BouncerData {
    BouncerPostData = {
        filter_data: {
            start_date:null,
            end_date:null,
        },
        page_no: 1,
        limit: 50,
    };

    #BouncerPaginateData = {
        link_limit: 2,
        from: 2,
        to: 2,
        total: null,
        is_last: null,
        current_item_count: null,
        current_page: null,
        last_page: null,
    };

    load() {
        this.#getBouncerData();
    }

    #getBouncerData() {
        FsHelper.blockUi($("#bouncerData"));
        FsClient.post("/developer/dashboard/GetBouncerData", this.BouncerPostData)
            .then((response) => {
                this.#BouncerPaginateData.current_page = response.current_page;
                this.#BouncerPaginateData.last_page = response.last_page;
                this.#BouncerPaginateData.is_last_page = response.is_last_page;
                this.#BouncerPaginateData.total = response.total_item;
                this.#BouncerPaginateData.current_item_count = response.current_item_count;
                this.#setBouncerDataHtml(response.data);
            })
            .catch((error) => {
                this.#setBouncerErrorDataHtml();
            })
            .finally(() => {
                FsHelper.unblockUi($("#bouncerData"));
            });
    }

    #setBouncerDataHtml(data) {
        let htmlData = "";
        data.forEach((item) => {
            htmlData += `<tr>
                            <td>${item.token}</td>
                            <td>${item.transaction_id}</td>
                            <td>${item.pg_name}</td>
                            <td>${item.is_call ? "Yes" : "No"}</td>
                            <td>${item.ip ? item.ip : "-"}</td>
                            <td>${item.redirect_url}</td>
                            <td><button class="btn btn-sm btn-primary btn-show-data" data-formd="${item.form_data}">Show Form Data</button></td>
                        </tr>`;
        })
        $("#bouncerData").html(htmlData);
        setPaginateButton("page-change-event", this.#BouncerPaginateData, this.BouncerPostData)
        $(".btn-show-data").click((e) => {
            let fd = e.target.attributes['data-formd'].value;
            fd = atob(fd);
            fd = JSON.parse(fd);
            $.dialog({
                columnClass: 'l',
                title: ' Bouncer Form Data',
                content:  DzpJsonViewer(fd, true),
            });
        })
    }

    #setBouncerErrorDataHtml() {
        $("#bouncerData").html(`<tr>
                                <td colspan="10" class="text-center">No Data To Display</td></tr>`);
        $('#pagination').html("");
    }
}

let bouncerData = new BouncerData();
bouncerData.load();

EventListener.dispatch.on("page-change-event", (event, callback) => {
    bouncerData.BouncerPostData.page_no = callback.page_number;
    bouncerData.load();
});

function resetBouncerFilterForm() {
    bouncerData.BouncerPostData.filter_data = {
        token: null,
        pg_name: null,
        is_call: null,
    };
    bouncerData.BouncerPostData.limit = 50;
    bouncerData.BouncerPostData.page_no = 1;
    $("#FilerForm")[0].reset();
    bouncerData.load();
}

$("#FilerForm").on("submit", () => {
    const FormData = getFormData($("#FilerForm"));
    bouncerData.BouncerPostData.filter_data = {
        token: null,
        pg_name: null,
        is_call: "All",
    }
    bouncerData.BouncerPostData.filter_data[FormData.FilterKey] = FormData.FilterValue;
    bouncerData.BouncerPostData.filter_data.pg_name = FormData.pg_name;
    bouncerData.BouncerPostData.filter_data.is_call = FormData.is_call;
    bouncerData.BouncerPostData.limit = FormData.Limit;
    bouncerData.BouncerPostData.page_no = 1;
    bouncerData.load();
});

