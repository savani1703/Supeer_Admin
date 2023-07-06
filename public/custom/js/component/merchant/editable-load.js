function loadEdiTable() {

    $('.payout_Enable_loadXeditable').editable({
        type: 'select',
        url: '/merchant/action/UpdateIsPayoutEnable',
        title: 'payout_Enable_loadXeditable',
        name: 'payout_Enable_loadXeditable',
        source: [
            {
                value: '1',
                text: 'true'
            },
            {
                value: '0',
                text: 'false'
            },
        ],
        success: function (response) {
            toastr.success("success", response.message, toastOption);
            (new MerchantData()).getMerchant();
        }, error: function (error) {
            toastr.error("error", error.responseJSON.message, toastOption);
            (new MerchantData()).getMerchant();
        },
    });

    $('.account_status_loadXeditable').editable({
        type: 'select',
        url: '/merchant/action/UpdateAccountStatus',
        title: 'account_status_loadXeditable',
        name: 'account_status_loadXeditable',
        source: [
            {
                value: 'New',
                text: 'New'
            },
            {
                value: 'Hold',
                text: 'Hold'
            },
            {
                value: 'Approved',
                text: 'Approved'
            },
            {
                value: 'Suspended',
                text: 'Suspended'
            },
        ],
        success: function (response) {
            toastr.success("success", response.message, toastOption);
            (new MerchantData()).getMerchant();
        }, error: function (error) {
            toastr.error("error", error.responseJSON.message, toastOption);
            (new MerchantData()).getMerchant();
        },
    });

    $('.min_transaction_limit_loadXeditable').editable({
        type: 'text',
        url: '/merchant/action/updateMinLimit',
        title: 'min_transaction_limit_loadXeditable',
        name: 'min_transaction_limit_loadXeditable',
        success: function (response) {
            toastr.success("success", response.message, toastOption);
            (new MerchantData()).getMerchant();
        }, error: function (error) {
            toastr.error("error", error.responseJSON.message, toastOption);
            (new MerchantData()).getMerchant();
        },
    });

    $('.max_transaction_limit_loadXeditable').editable({
        type: 'text',
        url: '/merchant/action/updateMaxLimit',
        title: 'max_transaction_limit_loadXeditable',
        name: 'max_transaction_limit_loadXeditable',
        success: function (response) {
            toastr.success("success", response.message, toastOption);
            (new MerchantData()).getMerchant();
        }, error: function (error) {
            toastr.error("error", error.responseJSON.message, toastOption);
            (new MerchantData()).getMerchant();
        },
    });


    $('.is_required_payment_failed_webhook_loadXeditable').editable({
        type: 'select',
        url: '/merchant/action/UpdateIsFailedWebhookRequired',
        title: 'is_required_payment_failed_webhook_loadXeditable',
        name: 'is_required_payment_failed_webhook_loadXeditable',
        source: [
            {
                value: '1',
                text: 'YES'
            },
            {
                value: '0',
                text: 'NO'
            },
        ],
        success: function (response) {
            toastr.success("success", response.message, toastOption);
            (new MerchantData()).getMerchant();
        }, error: function (error) {
            toastr.error("error", error.responseJSON.message, toastOption);
            (new MerchantData()).getMerchant();
        },
    });

    $('.is_enable_browser_check_loadXeditable').editable({
        type: 'select',
        url: '/merchant/action/UpdateIsEnableBrowserCheck',
        title: 'is_enable_browser_check_loadXeditable',
        name: 'is_enable_browser_check_loadXeditable',
        source: [
            {
                value: '1',
                text: 'YES'
            },
            {
                value: '0',
                text: 'NO'
            },
        ],
        success: function (response) {
            toastr.success("success", response.message, toastOption);
            (new MerchantData()).getMerchant();
        }, error: function (error) {
            toastr.error("error", error.responseJSON.message, toastOption);
            (new MerchantData()).getMerchant();
        },
    });


    $('.is_balance_check_enable_loadXeditable').editable({
        type: 'select',
        url: '/merchant/action/UpdateIsEnablePayoutBalanceCheck',
        title: 'is_balance_check_enable_loadXeditable',
        name: 'is_balance_check_enable_loadXeditable',
        source: [
            {
                value: '1',
                text: 'YES'
            },
            {
                value: '0',
                text: 'NO'
            },
        ],
        success: function (response) {
            toastr.success("success", response.message, toastOption);
            (new MerchantData()).getMerchant();
        }, error: function (error) {
            toastr.error("error", error.responseJSON.message, toastOption);
            (new MerchantData()).getMerchant();
        },
    });

    $('.webhook_url_loadXeditable').editable({
        type: 'text',
        url: '/merchant/action/UpdatePayInWebhook',
        title: 'webhook_url_loadXeditable',
        name: 'webhook_url_loadXeditable',
        success: function (response) {
            toastr.success("success", response.message, toastOption);
            (new MerchantData()).getMerchant();
        }, error: function (error) {
            toastr.error("error", error.responseJSON.message, toastOption);
            (new MerchantData()).getMerchant();
        },
    });

    $('.payout_webhook_url_loadXeditable').editable({
        type: 'text',
        url: '/merchant/action/UpdatePayoutWebhook',
        title: 'payout_webhook_url_loadXeditable',
        name: 'payout_webhook_url_loadXeditable',
        success: function (response) {
            toastr.success("success", response.message, toastOption);
            (new MerchantData()).getMerchant();
        }, error: function (error) {
            toastr.error("error", error.responseJSON.message, toastOption);
            (new MerchantData()).getMerchant();
        },
    });


    $('.is_dashboard_payout_enable_loadXeditable').editable({
        type: 'select',
        url: '/merchant/action/UpdateIsDashboardPayoutEnable',
        title: 'is_dashboard_payout_enable_loadXeditable',
        name: 'is_dashboard_payout_enable_loadXeditable',
        source: [
            {
                value: '1',
                text: 'YES'
            },
            {
                value: '0',
                text: 'NO'
            },
        ],
        success: function (response) {
            toastr.success("success", response.message, toastOption);
            (new MerchantData()).getMerchant();
        }, error: function (error) {
            toastr.error("error", error.responseJSON.message, toastOption);
            (new MerchantData()).getMerchant();
        },
    });


    $('.pay_in_auto_fees_loadXeditable').editable({
        type: 'text',
        url: '/merchant/action/UpdatePayInAutoFees',
        title: 'pay_in_auto_fees_loadXeditable',
        name: 'pay_in_auto_fees_loadXeditable',
        success: function (response) {
            toastr.success("success", response.message, toastOption);
            (new MerchantData()).getMerchant();
        }, error: function (error) {
            toastr.error("error", error.responseJSON.message, toastOption);
            (new MerchantData()).getMerchant();
        },
    });

    $('.pay_in_manual_fees_loadXeditable').editable({
        type: 'text',
        url: '/merchant/action/UpdatePayInManualFees',
        title: 'pay_in_manual_fees_loadXeditable',
        name: 'pay_in_manual_fees_loadXeditable',
        success: function (response) {
            toastr.success("success", response.message, toastOption);
            (new MerchantData()).getMerchant();
        }, error: function (error) {
            toastr.error("error", error.responseJSON.message, toastOption);
            (new MerchantData()).getMerchant();
        },
    });

    $('.payin_associate_fees_loadXeditable').editable({
        type: 'text',
        url: '/merchant/action/UpdatePayInAssociateFees',
        title: 'payin_associate_fees_loadXeditable',
        name: 'payin_associate_fees_loadXeditable',
        success: function (response) {
            toastr.success("success", response.message, toastOption);
            (new MerchantData()).getMerchant();
        }, error: function (error) {
            toastr.error("error", error.responseJSON.message, toastOption);
            (new MerchantData()).getMerchant();
        },
    });

    $('.payout_fees_loadXeditable').editable({
        type: 'text',
        url: '/merchant/action/UpdatePayoutFees',
        title: 'payout_fees_loadXeditable',
        name: 'payout_fees_loadXeditable',
        success: function (response) {
            toastr.success("success", response.message, toastOption);
            (new MerchantData()).getMerchant();
        }, error: function (error) {
            toastr.error("error", error.responseJSON.message, toastOption);
            (new MerchantData()).getMerchant();
        },
    });

    $('.payout_associate_fees_loadXeditable').editable({
        type: 'text',
        url: '/merchant/action/UpdatePayoutAssociateFees',
        title: 'payout_associate_fees_loadXeditable',
        name: 'payout_associate_fees_loadXeditable',
        success: function (response) {
            toastr.success("success", response.message, toastOption);
            (new MerchantData()).getMerchant();
        }, error: function (error) {
            toastr.error("error", error.responseJSON.message, toastOption);
            (new MerchantData()).getMerchant();
        },
    });

    $('.settlement_cycle_loadXeditable').editable({
        type: 'text',
        url: '/merchant/action/UpdateSettlementCycle',
        title: 'settlement_cycle_loadXeditable',
        name: 'settlement_cycle_loadXeditable',
        success: function (response) {
            toastr.success("success", response.message, toastOption);
            (new MerchantData()).getMerchant();
        }, error: function (error) {
            toastr.error("error", error.responseJSON.message, toastOption);
            (new MerchantData()).getMerchant();
        },
    });

    $('.payout_delayed_time_loadXeditable').editable({
        type: 'text',
        url: '/merchant/action/UpdatePayoutDelayedTime',
        title: 'payout_delayed_time_loadXeditable',
        name: 'payout_delayed_time_loadXeditable',
        success: function (response) {
            toastr.success("success", response.message, toastOption);
            (new MerchantData()).getMerchant();
        }, error: function (error) {
            toastr.error("error", error.responseJSON.message, toastOption);
            (new MerchantData()).getMerchant();
        },
    });


    $('.is_auto_approved_payout_loadXeditable').editable({
        type: 'select',
        url: '/merchant/action/UpdateIsAutoApprovedPayout',
        title: 'is_auto_approved_payout_loadXeditable',
        name: 'is_auto_approved_payout_loadXeditable',
        source: [
            {
                value: '1',
                text: 'YES'
            },
            {
                value: '0',
                text: 'NO'
            },
        ],
        success: function (response) {
            toastr.success("success", response.message, toastOption);
            (new MerchantData()).getMerchant();
        }, error: function (error) {
            toastr.error("error", error.responseJSON.message, toastOption);
            (new MerchantData()).getMerchant();
        },
    });


    $('.have_customer_details_loadXeditable').editable({
        type: 'select',
        url: '/merchant/action/UpdateShowCustomerDetailsPage',
        title: 'have_customer_details_loadXeditable',
        name: 'have_customer_details_loadXeditable',
        source: [
            {
                value: '1',
                text: 'YES'
            },
            {
                value: '0',
                text: 'NO'
            },
        ],
        success: function (response) {
            toastr.success("success", response.message, toastOption);
            (new MerchantData()).getMerchant();
        }, error: function (error) {
            toastr.error("error", error.responseJSON.message, toastOption);
            (new MerchantData()).getMerchant();
        },
    });


    $('.have_customer_details_api_loadXeditable').editable({
        type: 'select',
        url: '/merchant/action/UpdateIsCustomerDetailsRequired',
        title: 'have_customer_details_loadXeditable',
        name: 'have_customer_details_loadXeditable',
        source: [
            {
                value: '1',
                text: 'YES'
            },
            {
                value: '0',
                text: 'NO'
            },
        ],
        success: function (response) {
            toastr.success("success", response.message, toastOption);
            (new MerchantData()).getMerchant();
        }, error: function (error) {
            toastr.error("error", error.responseJSON.message, toastOption);
            (new MerchantData()).getMerchant();
        },
    });


    $('.is_settlement_enable_loadXeditable').editable({
        type: 'select',
        url: '/merchant/action/UpdateIsSettlementEnable',
        title: 'is_settlement_enable_loadXeditable',
        name: 'is_settlement_enable_loadXeditable',
        source: [
            {
                value: '1',
                text: 'YES'
            },
            {
                value: '0',
                text: 'NO'
            },
        ],
        success: function (response) {
            toastr.success("success", response.message, toastOption);
            (new MerchantData()).getMerchant();
        }, error: function (error) {
            toastr.error("error", error.responseJSON.message, toastOption);
            (new MerchantData()).getMerchant();
        },
    });

    $('.old_users_days_loadXeditable').editable({
        type: 'text',
        url: '/merchant/action/UpdateOldUsersDays',
        title: 'old_users_days_loadXeditable',
        name: 'old_users_days_loadXeditable',
        success: function (response) {
            toastr.success("success", response.message, toastOption);
            (new MerchantData()).getMerchant();
        }, error: function (error) {
            toastr.error("error", error.responseJSON.message, toastOption);
            (new MerchantData()).getMerchant();
        },
    });

    $('.checkout_color_loadXeditable').editable({
        type: 'text',
        url: '/merchant/action/UpdateCheckoutColor',
        title: 'checkout_color_loadXeditable',
        name: 'checkout_color_loadXeditable',
        success: function (response) {
            toastr.success("success", response.message, toastOption);
            (new MerchantData()).getMerchant();
        }, error: function (error) {
            toastr.error("error", error.responseJSON.message, toastOption);
            (new MerchantData()).getMerchant();
        },
    });

    $('.checkout_theme_url_loadXeditable').editable({
        type: 'text',
        url: '/merchant/action/UpdateCheckoutThemeUrl',
        title: 'checkout_theme_url_loadXeditable',
        name: 'checkout_theme_url_loadXeditable',
        success: function (response) {
            toastr.success("success", response.message, toastOption);
            (new MerchantData()).getMerchant();
        }, error: function (error) {
            toastr.error("error", error.responseJSON.message, toastOption);
            (new MerchantData()).getMerchant();
        },
    });

    $('.payin_metaStatus_loadXeditable').editable({
        params: function(params) {
            params.pg_name = $(this).attr("data-pg_name");
            params.merchant_id = $(this).attr("data-merchant_id");
            params.id = $(this).attr("data-id");
            return params;
        },
        type: 'select',
        url: '/merchant/UpdatePayInMetaStatus',
        title: 'payin_metaStatus_loadXeditable',
        name: 'payin_metaStatus_loadXeditable',
        source: [{value: '1',text: 'YES'},{value: '0',text: 'NO'},],
        success: function (response) {
            toastr.success("success", response.message, toastOption);
            getPayin();
        }, error: function (error) {
            toastr.error("error", error.responseJSON.message, toastOption);
        },
    });

    $('.payin_minLimit_loadXeditable').editable({
        params: function(params) {
            params.pg_name = $(this).attr("data-pg_name");
            params.merchant_id = $(this).attr("data-merchant_id");
            params.id = $(this).attr("data-id");
            return params;
        },
        type: 'text',
        url: '/merchant/UpdatePayInMetaMinLimit',
        title: 'payin_minLimit_loadXeditable',
        name: 'payin_minLimit_loadXeditable',
        success: function (response) {
            toastr.success("success", response.message, toastOption);
            getPayin();
        }, error: function (error) {
            toastr.error("error", error.responseJSON.message, toastOption);
            getPayin();
        },
    });

    $('.payin_maxLimit_loadXeditable').editable({
        params: function(params) {
            params.pg_name = $(this).attr("data-pg_name");
            params.merchant_id = $(this).attr("data-merchant_id");
            params.id = $(this).attr("data-id");
            return params;
        },
        type: 'text',
        url: '/merchant/UpdatePayInMetaMaxLimit',
        title: 'payin_maxLimit_loadXeditable',
        name: 'payin_maxLimit_loadXeditable',
        success: function (response) {
            toastr.success("success", response.message, toastOption);
            getPayin();
        }, error: function (error) {
            toastr.error("error", error.responseJSON.message, toastOption);
            getPayin();
        },
    });

    $('.payin_dailyLimit_loadXeditable').editable({
        params: function(params) {
            params.pg_name = $(this).attr("data-pg_name");
            params.merchant_id = $(this).attr("data-merchant_id");
            params.id = $(this).attr("data-id");
            return params;
        },
        type: 'text',
        url: '/merchant/UpdatePayInMetaDailyLimit',
        title: 'payin_dailyLimit_loadXeditable',
        name: 'payin_dailyLimit_loadXeditable',
        success: function (response) {
            toastr.success("success", response.message, toastOption);
            getPayin();
        }, error: function (error) {
            toastr.error("error", error.responseJSON.message, toastOption);
            getPayin();
        },
    });
    $('.bank_txn_mobile_loadXeditable').editable({
        params: function(params) {
            params.id = $(this).attr("data-id");
            return params;
        },
        type: 'text',
        url: '/bank-transaction/UpdateMobileNumber',
        title: 'bank_txn_mobile_loadXeditable',
        name: 'bank_txn_mobile_loadXeditable',
        success: function (response) {
            toastr.success("success", response.message, toastOption);
            (new BankTransaction()).getBank();
        }, error: function (error) {
            toastr.error("error", error.responseJSON.message, toastOption);
            (new BankTransaction()).getBank();
        },
    });
    $('.payin_perLimit_loadXeditable').editable({
        params: function(params) {
            params.pg_name = $(this).attr("data-pg_name");
            params.merchant_id = $(this).attr("data-merchant_id");
            params.id = $(this).attr("data-id");
            return params;
        },
        type: 'text',
        url: '/merchant/UpdatePayInMetaPerLimit',
        title: 'payin_perLimit_loadXeditable',
        name: 'payin_perLimit_loadXeditable',
        success: function (response) {
            toastr.success("success", response.message, toastOption);
            getPayin();
        }, error: function (error) {
            toastr.error("error", error.responseJSON.message, toastOption);
            getPayin();
        },
    });

    $('.payout_status_loadXeditable').editable({
        type: 'select',
        params: function(params) {
            params.pg_name = $(this).attr("data-pg_name");
            params.merchant_id = $(this).attr("data-merchant_id");
            params.id = $(this).attr("data-id");
            return params;
        },
        url: '/merchant/UpdatePayoutMetaStatus',
        title: 'payout_status_loadXeditable',
        name: 'payout_status_loadXeditable',
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
            getMeta()
        }, error: function (error) {
            toastr.error("error", error.responseJSON.message, toastOption);
        },
    });

}



