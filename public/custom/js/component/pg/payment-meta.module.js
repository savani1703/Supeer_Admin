class PaymentMetaModule {

    constructor(pgName, pgType) {
        this.pgName = pgName.toUpperCase();
        this.pgType = pgType.toUpperCase();
    }

    static PgVariable = {
        PAYIN: "PAYIN",
        PAYOUT: "PAYOUT",
        UPIPAY: "UPIPAY",
        STATUS_URL: "/payment-gateway/UpdatePaymentMetaStatus",
        MIN_LIMIT_URL: "/payment-gateway/UpdatePaymentMetaMinLimit",
        MAX_LIMIT_URL: "/payment-gateway/UpdatePaymentMetaMaxLimit",
        MAX_COUNT_LIMIT_URL: "/payment-gateway/UpdatePaymentMetaMaxCountLimit",
        TURN_OVER_URL: "/payment-gateway/UpdatePaymentMetaTurnOver",
        ALLOWED_METHOD_URL: "/payment-gateway/UpdatePaymentMetaMethod",
        AUTO_LOGIN_STATUS: "/payment-gateway/UpdateMetaAutoLoginStatus",
        PRODUCT_INFO: "/payment-gateway/UpdateMetaProductInfo",
    };

    renderMetaTemplate(data, configs) {
        if(this.pgType === PaymentMetaModule.PgVariable.PAYIN) {
            if(this.pgName === PaymentMetaModule.PgVariable.UPIPAY) {
                return PaymentMetaModule.renderPayInUPIMetaTemplate(data, configs, this.pgName, this.pgType);
            }
            return PaymentMetaModule.renderPayInMetaTemplate(data, configs, this.pgName, this.pgType);
        }
        if(this.pgType === PaymentMetaModule.PgVariable.PAYOUT) {
            return PaymentMetaModule.renderPayoutMetaTemplate(data, configs, this.pgName, this.pgType);
        }
    }

    static renderPayInUPIMetaTemplate(data, configs, pgName, pgType) {
        let htmlTemplate = "";
        if(data) {
            data.forEach((item, index) => {
                console.log(item)

                htmlTemplate += `<tr>
                                    <td>
                                        <span class="d-block mt-1">BANK#:  ${item.account_id}</span>
                                        <span class="d-block mt-1">MID:  ${item.merchant_id}</span>
                                    </td>
                                    <td>
                                        <span class="d-block mt-1">Holder: ${item.label}</span>
                                        <span class="d-block mt-1">A/C: ${item.account_number}</span>
                                        <span class="d-block mt-1">IFSC: ${item.ifsc_code}</span>
                                        <span class="d-block mt-1">UPI: ${item.upi_id}</span>
                                        <span class="d-block mt-1">BANK: ${item.bank_name}</span>
                                        <span class="d-block mt-1">${item.is_account_flow_active ? `<div><span class="badge badge-primary" style="margin-top: 6px;padding: 5px;">Account Flow</span></div>` : ""}</span>
                                    </td>
                                    <td>
                                        <span class="d-block mt-1">ID: ${item.vendor_id}</span>
                                        <span class="d-block mt-1">VENDOR: ${item.vendor_name}</span>
                                    </td>
                                    <td>
                                        ${
                                            PaymentMetaModule.checkConfigKey("is_active", configs) ?
                                            `<span class="d-block mb-1"> Bank Status: <a href="#" class="pgMetaInLineEdit mb-1" data-type="select"
                                                data-value="${item.is_active}"
                                                data-url="${this.PgVariable.STATUS_URL}/${pgType}/${pgName}"
                                                data-source="[{value: 1, text: 'Active'}, {value: 0, text: 'Deactive'}]"
                                                data-pk="${item.account_id}">
                                                ${item.is_active ? 'Active' : 'Deactive'}
                                            </a></span>` :
                                            `<span class="d-block mb-1">${item.is_active ? 'Active' : 'Deactive'}</span>`
                                        }
                                        ${
                                            PaymentMetaModule.checkConfigKey("is_auto_login", configs) ?
                                            `<span class="d-block mb-1"> Auto Login:<a href="#" class="pgMetaInLineEdit mb-1" data-type="select"
                                                data-value="${item.is_auto_login}"
                                                data-url="${this.PgVariable.AUTO_LOGIN_STATUS}/${pgType}/${pgName}"
                                                data-source="[{value: 1, text: 'Active'}, {value: 0, text: 'Deactive'}]"
                                                data-pk="${item.account_id}">
                                                ${item.is_auto_login ? 'Active' : 'Deactive'}
                                            </a></span>` :
                                            `<span class="d-block mb-1">Auto Login: ${item.is_auto_login ? 'Active' : 'Deactive'}</span>`
                                        }
                                    </td>
                                    <td>
                                        ${
                                            PaymentMetaModule.checkConfigKey("min_limit", configs) ?
                                                `<span class="d-block mb-1"> Min Limit:
                                                    <a href="#" class="pgMetaInLineEdit"
                                                     data-type="text"
                                                     data-url="${this.PgVariable.MIN_LIMIT_URL}/${pgType}/${pgName}"
                                                     data-pk="${item.account_id}">
                                                        ${item.min_limit ? item.min_limit : ""}
                                                    </a>
                                                </span>` :
                                                `<span class="d-block mb-1">Min Limit: ${item.min_limit ? item.min_limit : 0}</span>`
                                        }
                                        ${
                                            PaymentMetaModule.checkConfigKey("max_limit", configs) ?
                                                `<span class="d-block mb-1"> Max Limit :
                                                    <a href="#" class="pgMetaInLineEdit"
                                                     data-type="text"
                                                     data-url="${this.PgVariable.MAX_LIMIT_URL}/${pgType}/${pgName}"
                                                     data-pk="${item.account_id}">
                                                        ${item.max_limit ? item.max_limit : ""}
                                                    </a>
                                                </span>` :
                                                `<span class="d-block mb-1">Max Limit : ${item.max_limit ? item.max_limit : 0}</span>`
                                        }  ${
                                            PaymentMetaModule.checkConfigKey("max_count_limit", configs) ?
                                                `<span class="d-block mb-1"> Max Count Limit :
                                                    <a href="#" class="pgMetaInLineEdit"
                                                     data-type="text"
                                                     data-url="${this.PgVariable.MAX_COUNT_LIMIT_URL}/${pgType}/${pgName}"
                                                     data-pk="${item.account_id}">
                                                        ${item.max_count_limit ? item.max_count_limit : ""}
                                                    </a>
                                                </span>` :
                                                `<span class="d-block mb-1">Max Count Limit : ${item.max_count_limit ? item.max_count_limit : 0}</span>`
                                        }
                                        ${
                                            PaymentMetaModule.checkConfigKey("turn_over", configs) ?
                                                `<span class="d-block mb-1"> Turn Over :
                                                    <a href="#" class="pgMetaInLineEdit"
                                                     data-type="text"
                                                     data-url="${this.PgVariable.TURN_OVER_URL}/${pgType}/${pgName}"
                                                     data-pk="${item.account_id}">
                                                        ${item.turn_over ? item.turn_over : ""}
                                                    </a>
                                                </span>` :
                                                `<span class="d-block mb-1">Turn Over :${item.turn_over ? item.turn_over : 0}</span>`
                                        }
                                        ${
                                            PaymentMetaModule.checkConfigKey("current_turn_over", configs) ?
                                                `<span class="d-block mb-1"> Curr. Turn Over :
                                                    <a href="#" class="pgMetaInLineEdit"
                                                     data-type="text"
                                                     data-url="${this.PgVariable.TURN_OVER_URL}/${pgType}/${pgName}"
                                                     data-pk="${item.account_id}">
                                                        ${item.current_turn_over ? item.current_turn_over : ""}
                                                    </a>
                                                </span>` :
                                                `<span class="d-block mb-1">Curr. Turn Over :${item.current_turn_over ? item.current_turn_over : 0}</span>`
                                        }
                                        <span class="d-block mt-1">LIVE BAL:  ${item.live_bank_balance}</span>
                                    </td>
                                    <td>
                                        ${
                                            PaymentMetaModule.checkConfigKey("available_method", configs) ?
                                                `<span class="d-block mb-1">
                                                    <a href="#" class="pgMetaInLineEdit"
                                                     data-type="text"
                                                     data-url="${this.PgVariable.ALLOWED_METHOD_URL}/${pgType}/${pgName}"
                                                     data-pk="${item.account_id}">
                                                        ${item.available_method ? item.available_method : ""}
                                                    </a>
                                                </span>` :
                                                `<span class="d-block mb-1">${item.available_method ? item.available_method : ''}</span>`
                                        }
                                    </td>
                                    <td>
                                        <span class="d-block mb-1">Create: ${item.created_at_ist ? item.created_at_ist : '-'}</span>
                                        <span class="d-block mb-1">Update: ${item.updated_at_ist ? item.updated_at_ist : '-'}</span>
                                    </td>
                                    <td><button class="btn btn-sm btn-primary btn-pg-tester" onclick="DigiPayPgTester('${pgName.toUpperCase()}', '${item.account_id}')">TEST</button></td>
                                </tr>`;
            });
        }
        return htmlTemplate;
    }

    static renderPayInMetaTemplate(data, configs, pgName, pgType) {
        let htmlTemplate = "";
        if(data) {
            data.forEach((item, index) => {

                htmlTemplate += `<tr>
                                    ${item.id ? `<td>${item.id}</td>` : ''}
                                    <td>
                                        <span class="d-block mb-1">${item.account_id}</span>
                                        ${item.hasOwnProperty("is_seamless") ? `<span class="d-block">${item.is_seamless ? "Seamless" : "Hosted"}</span>` : ""}
                                    </td>
                                    <td>
                                        <span  class="d-block mb-1">${item.merchant_id}</span>
                                        ${ item.product_info ?
                                            PaymentMetaModule.checkConfigKey("is_active", configs) ?
                                                `<span class="d-block mb-1"> Product Info:
                                                    <a href="#" class="pgMetaInLineEdit mb-1" data-type="text"
                                                        data-value="${item.product_info}"
                                                        data-url="${this.PgVariable.PRODUCT_INFO}/${pgType}/${pgName}"
                                                        data-pk="${item.account_id}">
                                                        ${item.product_info}
                                                    </a></span>` :
                                                `<span class="d-block mb-1">${item.product_info}</span>` : ''
                                        }
                                    </td>
                                    <td>
                                        <span class="d-block">${item.label}</span>
                                        <span class="d-block">${item.email_id}</span>
                                    </td>
                                    <td>
                                        ${item.bouncer_sub_domain_url ? `<span class="d-block mb-1">Bouncer: ${item.bouncer_sub_domain_url}</span>` : ''}
                                        ${item.callback_sub_domain_url ? `<span class="d-block mb-1">Callback: ${item.callback_sub_domain_url}</span>` : ''}
                                        ${item.proxy_id ? `<span class="d-block mb-1">Proxy ID: ${item.proxy_id}</span>` : ''}
                                        ${item.proxy_id ? `<span class="d-block mb-1">Proxy IP: ${item.proxy_list ? item.proxy_list.ip_proxy : ''}</span>` : ''}
                                        ${item.white_listed_ip ? `<span class="d-block mb-1">Whitelist IP: ${item.white_listed_ip ? item.white_listed_ip : ''}</span>` : ''}
                                    </td>
                                    <td>
                                        ${
                                            PaymentMetaModule.checkConfigKey("is_active", configs) ?
                                                `<a href="#" class="pgMetaInLineEdit mb-1" data-type="select"
                                                    data-value="${item.is_active}"
                                                    data-url="${this.PgVariable.STATUS_URL}/${pgType}/${pgName}"
                                                    data-source="[{value: 1, text: 'Active'}, {value: 0, text: 'Deactive'}]"
                                                    data-pk="${item.account_id}">
                                                    ${item.is_active ? 'Active' : 'Deactive'}
                                                </a>` :
                                                `<span class="d-block mb-1">${item.is_active ? 'Active' : 'Deactive'}</span>`
                                        }
                                    </td>
                                    <td>
                                        ${
                                            PaymentMetaModule.checkConfigKey("min_limit", configs) ?
                                                `<span class="d-block mb-1"> Min Limit:
                                                    <a href="#" class="pgMetaInLineEdit"
                                                     data-type="text"
                                                     data-url="${this.PgVariable.MIN_LIMIT_URL}/${pgType}/${pgName}"
                                                     data-pk="${item.account_id}">
                                                        ${item.min_limit ? item.min_limit : ""}
                                                    </a>
                                                </span>` :
                                                `<span class="d-block mb-1">Min Limit: ${item.min_limit ? item.min_limit : 0}</span>`
                                        }
                                        ${
                                            PaymentMetaModule.checkConfigKey("max_limit", configs) ?
                                                `<span class="d-block mb-1"> Max Limit :
                                                    <a href="#" class="pgMetaInLineEdit"
                                                     data-type="text"
                                                     data-url="${this.PgVariable.MAX_LIMIT_URL}/${pgType}/${pgName}"
                                                     data-pk="${item.account_id}">
                                                        ${item.max_limit ? item.max_limit : ""}
                                                    </a>
                                                </span>` :
                                                `<span class="d-block mb-1">Max Limit : ${item.max_limit ? item.max_limit : 0}</span>`
                                        }
                                        ${
                                            PaymentMetaModule.checkConfigKey("max_count_limit", configs) ?
                                                `<span class="d-block mb-1"> Max Count Limit :
                                                    <a href="#" class="pgMetaInLineEdit"
                                                     data-type="text"
                                                     data-url="${this.PgVariable.MAX_COUNT_LIMIT_URL}/${pgType}/${pgName}"
                                                     data-pk="${item.account_id}">
                                                        ${item.max_count_limit ? item.max_count_limit : ""}
                                                    </a>
                                                </span>` :
                                                `<span class="d-block mb-1">Max Count Limit : ${item.max_count_limit ? item.max_count_limit : 0}</span>`
                                        }
                                        ${
                                            PaymentMetaModule.checkConfigKey("turn_over", configs) ?
                                                `<span class="d-block mb-1"> Turn Over :
                                                    <a href="#" class="pgMetaInLineEdit"
                                                     data-type="text"
                                                     data-url="${this.PgVariable.TURN_OVER_URL}/${pgType}/${pgName}"
                                                     data-pk="${item.account_id}">
                                                        ${item.turn_over ? item.turn_over : ""}
                                                    </a>
                                                </span>` :
                                                `<span class="d-block mb-1">Turn Over :${item.turn_over ? item.turn_over : 0}</span>`
                                        }
                                        ${
                                            PaymentMetaModule.checkConfigKey("current_turn_over", configs) ?
                                                `<span class="d-block mb-1"> Curr. Turn Over :
                                                    <a href="#" class="pgMetaInLineEdit"
                                                     data-type="text"
                                                     data-url="${this.PgVariable.TURN_OVER_URL}/${pgType}/${pgName}"
                                                     data-pk="${item.account_id}">
                                                        ${item.current_turn_over ? item.current_turn_over : ""}
                                                    </a>
                                                </span>` :
                                                `<span class="d-block mb-1">Curr. Turn Over :${item.current_turn_over ? item.current_turn_over : 0}</span>`
                                        }
                                    </td>
                                    <td>
                                        ${
                                            PaymentMetaModule.checkConfigKey("available_method", configs) ?
                                                `<span class="d-block mb-1">
                                                    <a href="#" class="pgMetaInLineEdit"
                                                     data-type="text"
                                                     data-url="${this.PgVariable.ALLOWED_METHOD_URL}/${pgType}/${pgName}"
                                                     data-pk="${item.account_id}">
                                                        ${item.available_method ? item.available_method : ""}
                                                    </a>
                                                </span>` :
                                                `<span class="d-block mb-1">${item.available_method ? item.available_method : ''}</span>`
                                        }
                                    </td>
                                    <td>
                                        <span class="d-block mb-1">Create: ${item.created_at_ist ? item.created_at_ist : '-'}</span>
                                        <span class="d-block mb-1">Update: ${item.updated_at_ist ? item.updated_at_ist : '-'}</span>
                                    </td>
                                    <td><button class="btn btn-sm btn-primary btn-pg-tester" onclick="DigiPayPgTester('${pgName.toUpperCase()}', '${item.account_id}')">TEST</button></td>
                                </tr>`;
            });
        }
        return htmlTemplate;
    }

    static renderPayoutMetaTemplate(data, configs, pgName, pgType) {
        let htmlTemplate = "";
        if(data) {
            data.forEach((item) => {
                console.log(item)
                htmlTemplate += `<tr>
                                    <td>${item.id}</td>
                                    <td>${item.account_id}</td>
                                    <td>
                                        <span class="d-block mb-1">MID: ${item.merchant_id}</span>
                                        ${item.available_balance ? `<span class="d-block mb-1">Balance: ${item.available_balance}</span>` : ''}
                                        ${item.last_check_balance_at_ist ? `<span class="d-block mb-1">Last Update: ${item.last_check_balance_at_ist}</span>` : ''}
                                    </td>
                                    <td>
                                        <span class="d-block mb-1">${item.label}</span>
                                        ${item.email_id ? `<span class="d-block mb-1">Email: ${item.email_id}</span>` : ''}
                                        ${item.debit_account ? `<span class="d-block mb-1">Account: ${item.debit_account}</span>` : ''}
                                        ${item.ifsc_code ? `<span class="d-block mb-1">IFSC: ${item.ifsc_code}</span>` : ''}
                                    </td>
                                    <td>
                                        ${item.proxy_id ? `<span class="d-block mb-1">Proxy ID: ${item.proxy_id}</span>` : ''}
                                        ${item.proxy_id ? `<span class="d-block mb-1">Proxy IP: ${item.proxy_list ? item.proxy_list.ip_proxy : ''}</span>` : ''}
                                    </td>
                                    <td>
                                        ${
                                            PaymentMetaModule.checkConfigKey("is_active", configs) ?
                                                `<a href="#" class="pgMetaInLineEdit mb-1" data-type="select"
                                                    data-value="${item.is_active}"
                                                    data-url="${this.PgVariable.STATUS_URL}/${pgType}/${pgName}"
                                                    data-source="[{value: 1, text: 'Active'}, {value: 0, text: 'Deactive'}]"
                                                    data-pk="${item.account_id}">
                                                    ${item.is_active ? 'Active' : 'Deactive'}
                                                </a>` :
                                                `<span class="d-block mb-1">${item.is_active ? 'Active' : 'Deactive'}</span>`
                                        }
                                    </td>
                                    <td>
                                        ${
                                            PaymentMetaModule.checkConfigKey("min_limit", configs) ?
                                                `<span class="d-block mb-1"> Min Limit:
                                                    <a href="#" class="pgMetaInLineEdit"
                                                     data-type="text"
                                                     data-url="${this.PgVariable.MIN_LIMIT_URL}/${pgType}/${pgName}"
                                                     data-pk="${item.account_id}">
                                                        ${item.min_limit ? item.min_limit : ""}
                                                    </a>
                                                </span>` :
                                                `<span class="d-block mb-1">Min Limit: ${item.min_limit ? item.min_limit : 0}</span>`
                                        }
                                        ${
                                            PaymentMetaModule.checkConfigKey("max_limit", configs) ?
                                                `<span class="d-block mb-1"> Max Limit :
                                                    <a href="#" class="pgMetaInLineEdit"
                                                     data-type="text"
                                                     data-url="${this.PgVariable.MAX_LIMIT_URL}/${pgType}/${pgName}"
                                                     data-pk="${item.account_id}">
                                                        ${item.max_limit ? item.max_limit : ""}
                                                    </a>
                                                </span>` :
                                                `<span class="d-block mb-1">Max Limit : ${item.max_limit ? item.max_limit : 0}</span>`
                                        }
                                        ${
                                            PaymentMetaModule.checkConfigKey("max_count_limit", configs) ?
                                                `<span class="d-block mb-1"> Max Count Limit :
                                                     <a href="#" class="pgMetaInLineEdit"
                                                        data-type="text"
                                                         data-url="${this.PgVariable.MAX_COUNT_LIMIT_URL}/${pgType}/${pgName}"
                                                         data-pk="${item.account_id}">
                                                        ${item.max_count_limit ? item.max_count_limit : ""}
                                                        </a>
                                                </span>` :
                                         `<span class="d-block mb-1">Max Count Limit : ${item.max_count_limit ? item.max_count_limit : 0}</span>`
                                        }

                                    </td>
                                    <td>
                                        <span class="d-block mb-1">Create: ${item.created_at_ist ? item.created_at_ist : '-'}</span>
                                        <span class="d-block mb-1">Update: ${item.updated_at_ist ? item.updated_at_ist : '-'}</span>
                                    </td>
                                     <td>${setButton(pgName, item.account_id)}</td>
                                </tr>`;
            });
        }
        return htmlTemplate;
    }
    static checkConfigKey(key, configs) {
        return configs.includes(key);
    }
}

function setButton(pgName, account_id) {
    if(pgName === 'BULKPE'){
        return `<button class="btn btn-sm btn-primary btn-pg-tester" onClick="emptyBank('${account_id}')">Empty Bank</button>`;
    }
    return '';
}

function emptyBank(account_id){
    var myModal =  new jBox('Confirm', {
        confirmButton: 'YES',
        cancelButton: 'No',
        content: 'Are You Sure Do you want to empty bank ?',
        confirm: function () {
            FsHelper.blockUi($("#BlockInfo"));
            const postData = {
                account_id: account_id
            };
            FsClient.post("/empty/bank", postData).then(
                response => {
                    if(response.status){
                        toastr.success(response.message,"success",toastOption);
                    }else{
                        toastr.error(response.message,"error",toastOption);
                    }
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
