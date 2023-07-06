const  FsClient = {
    post(url, postData = []) {
        this.csrf();
        return new Promise(function (resolve, reject) {
            $.ajax({
                url: url,
                method: 'post',
                data: postData,
            }).done(response => {
                resolve(response);
            }).fail(error => {
                reject(error);
            });
        });
    },
    post2(url, postData = []) {
        this.csrf();
        return new Promise(function (resolve, reject) {
            $.ajax({
                url: url,
                method: 'post',
                data: postData,
                processData: false,
                contentType: false,
            }).done(response => {
                resolve(response);
            }).fail(error => {
                reject(error);
            });
        });
    },

    get(url, jsonData = []) {

        if(jsonData.length > 0) {
            url += '?' +
                Object.keys(jsonData).map(function(key) {
                    return encodeURIComponent(key) + '=' +
                        encodeURIComponent(jsonData[key]);
                }).join('&');
        }

        this.csrf();
        return new Promise(function (resolve, reject) {
            $.ajax({
                url: url,
                method: 'get'
            }).done(response => {
                resolve(response);
            }).fail(error => {
                reject(error);
            });
        });
    },

    csrf() {
        return $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    }
}

const FsHelper = {
    serializeObject: (data) => {
        let o = {};
        let a = data.serializeArray();
        $.each(a, function() {
            if (o[this.name]) {
                if (!o[this.name].push) {
                    o[this.name] = [o[this.name]];
                }
                o[this.name].push(this.value || '');
            } else {
                o[this.name] = this.value || '';
            }
        });
        return o;
    },
    successSnack: (data) => {
        Snackbar.show({
            showAction: false,
            text: data,
            duration: 3000,
            customClass: 'customeSnackSuccess',
            pos: 'top-center'
        });
    },
    errorSnack: (data) => {
        Snackbar.show({
            showAction: false,
            text: data,
            duration: 3000,
            customClass: 'customeSnackFailed',
            pos: 'top-center'
        });
    },
    blockUi: (bloclEl) => {
        if(Array.isArray(bloclEl)) {
            console.log(bloclEl)
            bloclEl.forEach((item) => {
                console.log(item)
                $(item).block({
                    message: '<div class="ft-refresh-cw font-medium-2"><i class="fa fa-circle-o-notch fa-spin"></i> <span style="font-size: 12px;font-weight: 600;">Please Wait...</span></div>',
                    overlayCSS: {
                        backgroundColor: '#fff',
                        opacity: 0.8,
                        cursor: 'wait'
                    },
                    css: {
                        border: 0,
                        padding: 0,
                        backgroundColor: 'transparent'
                    }
                });
            });
        } else {
            $(bloclEl).block({
                message: '<div class="ft-refresh-cw font-medium-2"><i class="fa fa-circle-o-notch fa-spin"></i> <span style="font-size: 12px;font-weight: 600;">Please Wait...</span></div>',
                overlayCSS: {
                    backgroundColor: '#fff',
                    opacity: 0.8,
                    cursor: 'wait'
                },
                css: {
                    border: 0,
                    padding: 0,
                    backgroundColor: 'transparent'
                }
            });
        }

    },
    unblockUi: (bloclEl) => {
        if(Array.isArray(bloclEl)) {
            bloclEl.forEach((item) => {
                $(item).unblock();
            });
        } else {
            $(bloclEl).unblock();
        }
    },
    unauthorizedUserPage: (idName) => {
        $("#"+idName).html(`
            <div class="col-md-8 col-xl-6 mx-auto d-flex flex-column align-items-center">
                    <img src="assets/images/401.svg" class="img-fluid mb-2" alt="404">
                    <h1 class="font-weight-bold mb-22 mt-2 tx-80 text-muted">401</h1>
                    <h4 class="mb-2">Unauthorized User</h4>
                    <h6 class="text-muted mb-3 text-center">Oopps!! You are not authorized to access this Page.</h6>
                </div>
            `);
    },
    getStatusBadge(status) {
        let badge = "badge-danger-muted";
        if(status === "Success") {
            badge = "badge-success";
        }
        if(status === "NEW") {
            badge = "badge-success";
        }
        if(status === "Full Refund") {
            badge = "badge-info";
        }
        if(status === "Failed") {
            badge = "badge-danger";
        }
        if(status === "Expired") {
            badge = "badge-outlineinfo";
        }
        if(status === "Initialized") {
            badge = "badge-warning";
        }
        if(status === "Pending") {
            badge = "badge-outlineprimary";
        }
        if(status === "Partial Refund") {
            badge = "badge-info";
        }
        if(status === "Processing") {
            badge = "badge-outlinewarning";
        }
        return badge;
    }
};

function EventBus() {
    this.dispatch = $({});
}

EventBus.prototype = {
    emitter: function(c) {
        this.dispatch.trigger(c.e, c.c);
    }
};
const EventListener = new EventBus();

EventListener.dispatch.on("sampleEvent", (e, c) => {
    console.log(c.a);
});


const getFormData = ($form) => {
    let unindexed_array = $form.serializeArray();
    let indexed_array = {};

    $.map(unindexed_array, function(n, i){
        indexed_array[n['name']] = n['value'];
    });

    return indexed_array;
}


function copy(that,id){
    var inp =document.createElement('input');
    document.body.appendChild(inp)
    inp.value =that.textContent
    inp.select();
    toastr.info(id,"Info",options)
    document.execCommand('copy',false);
    inp.remove();
}

var toastOption = {
    "closeButton": false,
    "debug": false,
    "newestOnTop": false,
    "progressBar": true,
    "positionClass": "toast-top-right",
    "preventDuplicates": true,
    "onclick": null,
    "showDuration": "300",
    "hideDuration": "10000",
    "timeOut": "5000",
    "extendedTimeOut": "1000",
    "showEasing": "swing",
    "hideEasing": "linear",
    "showMethod": "fadeIn",
    "hideMethod": "fadeOut",
};

// var today = new Date();
// var dd = today.getDate()-7;
// var ddd = today.getDate();
// var mm = today.getMonth()+1;
// var yyyy = today.getFullYear();
// if(dd<10){dd='0'+dd;}
// if(mm<10){mm='0'+mm;}
// StartDate = yyyy+'-'+mm+'-'+dd +' '+'00:00:00';
// EndDate = yyyy+'-'+mm+'-'+ddd +' '+'23:59:59';


let StartDate = moment().subtract(7, 'd').format('YYYY-MM-DD')+' '+'00:00:00';
let EndDate = moment().format('YYYY-MM-DD')+' '+'23:59:59';

let todayStartDate = moment().subtract().format('YYYY-MM-DD')+' '+'00:00:00';
let todayEndDate = moment().format('YYYY-MM-DD')+' '+'23:59:59';

DzpDatePickerService = {
    init: () => {
        let findDatePickerElement = document.querySelectorAll('input[name="daterange"]');
        findDatePickerElement.forEach((item, index) => {
            $(item).daterangepicker({
                "autoApply": true,
                "autoUpdateInput": false,
                "locale": {
                    "format": "DD/MM/YYYY",
                    "separator": " - ",
                    "applyLabel": "Apply",
                    "cancelLabel": "Cancel",
                    "fromLabel": "From",
                    "toLabel": "To",
                    "customRangeLabel": "Custom",
                    "weekLabel": "W",
                    "daysOfWeek": [
                        "Su",
                        "Mo",
                        "Tu",
                        "We",
                        "Th",
                        "Fr",
                        "Sa"
                    ],
                    "monthNames": [
                        "January",
                        "February",
                        "March",
                        "April",
                        "May",
                        "June",
                        "July",
                        "August",
                        "September",
                        "October",
                        "November",
                        "December"
                    ],
                    "firstDay": 1
                },
                "linkedCalendars": false,
                "showCustomRangeLabel": false,
                "startDate": moment().subtract(7, 'd'),
                "endDate": moment(),
                "maxDate": moment(),
                "maxSpan": {
                    "days": 30
                },
            }, function(start, end, label,item) {
                $('input[name="daterange"]').val(start.format('DD/MM/YYYY') + ' - ' + end.format('DD/MM/YYYY'));
            });
        });

        $('.player_register_date_range').daterangepicker({
            "autoApply": true,
            "autoUpdateInput": false,
            "locale": {
                "format": "DD/MM/YYYY",
                "separator": " - ",
                "applyLabel": "Apply",
                "cancelLabel": "Cancel",
                "fromLabel": "From",
                "toLabel": "To",
                "customRangeLabel": "Custom",
                "weekLabel": "W",
                "daysOfWeek": [
                    "Su",
                    "Mo",
                    "Tu",
                    "We",
                    "Th",
                    "Fr",
                    "Sa"
                ],
                "monthNames": [
                    "January",
                    "February",
                    "March",
                    "April",
                    "May",
                    "June",
                    "July",
                    "August",
                    "September",
                    "October",
                    "November",
                    "December"
                ],
                "firstDay": 1
            },
            "linkedCalendars": false,
            "showCustomRangeLabel": false,
            "startDate": moment().subtract(7, 'd'),
            "endDate": moment(),
            "maxDate": moment(),
            "maxSpan": {
                "days": 30
            },
        }, function(start, end, label,item) {
            $('.player_register_date_range').val(start.format('DD/MM/YYYY') + ' - ' + end.format('DD/MM/YYYY'));
        });
    },
    mFilter: () => {
        let findDatePickerElement = document.querySelectorAll('input[name="daterange"]');
        findDatePickerElement.forEach((item, index) => {
            $(item).val("")
            $(item).daterangepicker({
                "autoApply": true,
                "autoUpdateInput": false,
                "locale": {
                    "format": "DD/MM/YYYY",
                    "separator": " - ",
                    "applyLabel": "Apply",
                    "cancelLabel": "Cancel",
                    "fromLabel": "From",
                    "toLabel": "To",
                    "customRangeLabel": "Custom",
                    "weekLabel": "W",
                    "daysOfWeek": [
                        "Su",
                        "Mo",
                        "Tu",
                        "We",
                        "Th",
                        "Fr",
                        "Sa"
                    ],
                    "monthNames": [
                        "January",
                        "February",
                        "March",
                        "April",
                        "May",
                        "June",
                        "July",
                        "August",
                        "September",
                        "October",
                        "November",
                        "December"
                    ],
                    "firstDay": 1
                },
                "linkedCalendars": false,
                "showCustomRangeLabel": false,
                "startDate": moment().subtract(7, 'd'),
                "endDate": moment(),
                "maxDate": moment(),
                "maxSpan": {
                    "days": 30
                },
            }, function(start, end, label,item) {
                $('input[name="daterange"]').val(start.format('DD/MM/YYYY') + ' - ' + end.format('DD/MM/YYYY'));
            });
        });
    }
};

const DzpJsonViewer = (json, collapsible=false) => {
    var TEMPLATES = {
        item: '<div class="json__item"><div class="json__key">%KEY%</div><div class="json__value json__value--%TYPE%">%VALUE%</div></div>',
        itemCollapsible: '<label class="json__item json__item--collapsible"><input type="checkbox" class="json__toggle"/><div class="json__key">%KEY%</div><div class="json__value json__value--type-%TYPE%">%VALUE%</div>%CHILDREN%</label>',
        itemCollapsibleOpen: '<label class="json__item json__item--collapsible"><input type="checkbox" checked class="json__toggle"/><div class="json__key">%KEY%</div><div class="json__value json__value--type-%TYPE%">%VALUE%</div>%CHILDREN%</label>'
    };

    function createItem(key, value, type){
        var element = TEMPLATES.item.replace('%KEY%', key);

        if(type == 'string') {
            element = element.replace('%VALUE%', '"' + value + '"');
        } else {
            element = element.replace('%VALUE%', value);
        }

        element = element.replace('%TYPE%', type);

        return element;
    }

    function createCollapsibleItem(key, value, type, children){
        var tpl = 'itemCollapsible';

        if(collapsible) {
            tpl = 'itemCollapsibleOpen';
        }

        var element = TEMPLATES[tpl].replace('%KEY%', key);

        element = element.replace('%VALUE%', type);
        element = element.replace('%TYPE%', type);
        element = element.replace('%CHILDREN%', children);

        return element;
    }

    function handleChildren(key, value, type) {
        var html = '';

        for(var item in value) {
            var _key = item,
                _val = value[item];

            html += handleItem(_key, _val);
        }

        return createCollapsibleItem(key, value, type, html);
    }

    function handleItem(key, value) {
        var type = typeof value;

        if(typeof value === 'object') {
            return handleChildren(key, value, type);
        }

        return createItem(key, value, type);
    }

    function parseObject(obj) {
        _result = '<div class="json">';

        for(var item in obj) {
            var key = item,
                value = obj[item];

            _result += handleItem(key, value);
        }

        _result += '</div>';

        return _result;
    }

    return parseObject(json);
};

const DigiPayPgTester = (pg_name, meta_id) => {

    let postData = {
        pg_name: pg_name,
        meta_id: meta_id
    };

    $.confirm({
        title: pg_name + ' Test Transaction!',
        content: '' +
            '<form action="" class="formName">' +
            '<div class="form-group">' +
            '<label>Enter Amount</label>' +
            '<input type="number" class="test_payment_amount form-control" required />' +
            '</div>' +
            '</form>',
        buttons: {
            formSubmit: {
                text: 'Submit',
                btnClass: 'btn-blue',
                action: function () {
                    let payment_amount = this.$content.find('.test_payment_amount').val();
                    if(payment_amount < 1){
                        $.alert('provide a valid amount');
                        return false;
                    }
                    postData.payment_amount = payment_amount;

                    FsHelper.blockUi($("body"))
                    FsClient.post("/payment-gateway/TestPaymentAccount", postData)
                        .then((response) => {
                            toastr.success(response.message,"success",toastOption);
                            window.open(response.checkout_url)
                        })
                        .catch(error => {
                            toastr.error(error.responseJSON.message,"error",toastOption);
                        })
                        .finally(() => {
                            FsHelper.unblockUi($("body"))
                        })
                }
            },
            cancel: function () {
                //close
            },
        },
        onContentReady: function () {
            // bind to events
            var jc = this;
            this.$content.find('form').on('submit', function (e) {
                // if the user submits the form by pressing enter in the field.
                e.preventDefault();
                jc.$$formSubmit.trigger('click'); // reference the button and click it
            });
        }
    });
};

const DigiPayProxyList = (element) => {
    FsClient.post("/d-dashboard/getProxyList", "")
        .then((response) => {
            let htmlData = "";
            const data = response.data;
            data.forEach((item) => {
                htmlData += `<option value="${item.id}">${item.label_name}</option>`;
            });
            element.html(htmlData);
        })
        .catch((error) => {
            console.log(error);
            element.html("");
        })
}

const DigiPayMenuServices = {
    initService: () => {
        const isHiddenMenuShow = parseInt(localStorage.getItem("is_hidden_menu_show") ?? 0);
        if(isHiddenMenuShow > 0) {
            $(".btn-hidden-menu").removeClass("mdi-eye").addClass("mdi-eye-off").attr("title", "Hide Menu");
            $(".hidden-menu").show();
        } else {
            $(".btn-hidden-menu").addClass("mdi-eye").removeClass("mdi-eye-off").attr("title", "Show Menu");
            $(".hidden-menu").hide();
        }
    },
    showHiddenMenu: () => {
        const isHiddenMenuShow = $(".btn-hidden-menu").hasClass("mdi-eye");
        if(!isHiddenMenuShow) {
            $(".btn-hidden-menu").removeClass("mdi-eye").addClass("mdi-eye-off").attr("title", "Hide Menu");
            localStorage.removeItem("is_hidden_menu_show");
        } else {
            $(".btn-hidden-menu").addClass("mdi-eye").removeClass("mdi-eye-off").attr("title", "Show Menu");
            localStorage.setItem("is_hidden_menu_show", "1");
        }

        DigiPayMenuServices.initService();
    }
}

function showTime(){
    let date = new Date();
    let h = date.getHours(); // 0 - 23
    let m = date.getMinutes(); // 0 - 59
    let s = date.getSeconds(); // 0 - 59
    let session = "AM";

    if(h === 0){
        h = 12;
    }
    if(h > 12 || (h === 12 && m > 0)){
        if(h > 12 ) {
            h = h - 12;
        }
        session = "PM";
    }

    h = (h < 10) ? "0" + h : h;
    m = (m < 10) ? "0" + m : m;
    s = (s < 10) ? "0" + s : s;

    let time = h + ":" + m + ":" + s + " " + session;
    document.getElementById("DashboardClock").innerText = time;
    document.getElementById("DashboardClock").textContent = time;

    setTimeout(showTime, 1000);
}

DigiPayMenuServices.initService();
$(".btn-hidden-menu").click(() => {
    DigiPayMenuServices.showHiddenMenu();
});



