@extends('layout.master')

@section('title', 'UTR Recon System')

@section('customStyle')
@endsection

@section('content')
    <div class="card" style="padding-bottom: 30px">
        <div class="card-body" id="transaction_page">
            <div class="d-flex justify-content-between align-items-baseline mb-2">
                <h6 class="card-title mb-0">UTR Recon System</h6>
            </div>
            <div class="mb-4">
                <form action="javascript:void(0)" id="txnFilerForm">
                    <div class="row mt-4">
                        <div class="col-auto mt-4">
                            <button class="btn btn-primary" type="button"  onclick="setAllUTR()">Set All UTR</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="table-responsive" id="UtrDataZoneForMark" style="padding-top: 30px">
                <table class="table table-hover mb-0" data-show-toggle="false">
                    <thead>
                    <tr>
                        <th class="pt-0">Transaction Date </th>
                        <th class="pt-0">Customer ID </th>
                        <th class="pt-0">Transaction ID </th>
                        <th class="pt-0">Order Amount</th>
                        <th class="pt-0">UPI ID</th>
                        <th class="pt-0">Bank UTR</th>
                        <th class="pt-0">Bank Temp UTR</th>
                        <th class="pt-0">Payment Amount</th>
                        <th class="pt-0">Bank Date</th>
                        <th class="pt-0">Action</th>
                    </tr>
                    </thead>
                    <tbody id="UtrData">
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card" style="padding-top: 10px;padding-bottom: 30px;margin-top: 10px;">
        <div class="card-body" id="transaction_page">
            <div class="d-flex justify-content-between align-items-baseline mb-2">
                <h6 class="card-title mb-0">UTR Late Success</h6>
            </div>
            <div class="mb-4">
                <form action="javascript:void(0)" id="txnFilerForm">
                    <div class="row mt-4">
                        <div class="col-auto">
                            <span class="text-muted">Date</span>
                            <div class="form-group">
                                <div class="input-group date datepicker dashboard-date mr-2 mb-2 mb-0 d-xl-flex">
                                    <span class="input-group-addon bg-transparent"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-calendar  text-primary"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg></span>
                                    <input type="text" class="form-control  form-control-sm"  name="daterange" autocomplete="off">
                                </div>
                            </div>
                        </div>
                        <div class="col-auto mt-4">
                            <label class="control-label"></label>
                            <button id="apply" class="btn  btn-sm btn-primary" type="submit">Apply</button>
                            <button class="btn btn-danger btn-sm" type="button"  onclick="resetTransaction()">Clear</button>
                        </div>
                        <div class="col-auto mt-4">
                            <button class="btn btn-primary" type="button"  onclick="exportReportToExcel(this)">Generate Report</button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="table-responsive" id="UtrDataZone"  style="padding-top: 30px;margin-top: 10px;">
                <table class="table table-hover mb-0" data-show-toggle="false" id="UtrDataZoneTbl">
                    <thead>
                    <tr>
                        <th class="pt-0">Transaction Date </th>
                        <th class="pt-0">Customer ID </th>
                        <th class="pt-0">Transaction ID </th>
                        <th class="pt-0">Merchant Order ID </th>
                        <th class="pt-0">Order Amount</th>
                        <th class="pt-0">Bank UTR</th>
                        <th class="pt-0">Payment Amount</th>
                        <th class="pt-0">Bank Date</th>
                    </tr>
                    </thead>
                    <tbody id="UtrDataMarked">
                    </tbody>
                </table>
            </div>


        </div>
    </div>

    <div class="card" style="padding-top: 10px;padding-bottom: 30px;margin-top: 10px;">
        <div class="card-body" id="transaction_page">
            <div class="d-flex justify-content-between align-items-baseline mb-2">
                <h6 class="card-title mb-0">Amount Mismatched </h6>
            </div>
            <div class="mb-4">
                <form action="javascript:void(0)" id="txnFilerForm">
                    <div class="row mt-4">

                        <div class="col-auto mt-4">
                            <button class="btn btn-primary" type="button"  onclick="exportReportToExcelMismatched(this)">Generate Report</button>
                        </div>

                    </div>
                </form>
            </div>
            <div class="table-responsive" id="UtrDataZone"  style="padding-top: 30px">
                <table class="table table-hover mb-0" data-show-toggle="false" id="UtrDataZoneTblMismatched">
                    <thead>
                    <tr>
                        <th class="pt-0">Transaction Date </th>
                        <th class="pt-0">Customer ID </th>
                        <th class="pt-0">Transaction ID </th>
                        <th class="pt-0">Merchant Order ID </th>
                        <th class="pt-0">Order Amount</th>
                        <th class="pt-0">Bank UTR</th>
                        <th class="pt-0">Bank Temp UTR</th>
                        <th class="pt-0">Payment Amount</th>
                        <th class="pt-0">Bank Date</th>
                    </tr>
                    </thead>
                    <tbody id="UtrDataMarkedMismatched">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('customJs')
    <script src="https://cdn.jsdelivr.net/gh/linways/table-to-excel@v1.0.4/dist/tableToExcel.js"></script>
    <script src="{{URL::asset('custom/js/component/reconutr.js?v=12')}}"></script>
    <script>
        /*! @preserve
 * TableCheckAll.js
 * version: 1.0
 * author: Ronard Cauba <ronard@ecomnuggets.com>
 * license: MIT
 * https://codeanddeploy.com/blog/jquery-plugins/jquery-table-check-all-plugin
 */
        $.fn.TableCheckAll = function (options) {
            // Default options
            var settings = $.extend({
                checkAllCheckboxClass: '.check-all',
                checkboxClass: '.check'
            }, options);
            return this.each(function () {
                $(this).find(settings.checkAllCheckboxClass).on('click', function () {
                    if ($(this).is(':checked')) {
                        $.each($(this).parents("table").find(settings.checkboxClass), function () {
                            $(this).prop('checked', true);
                        });
                    } else {
                        $.each($(this).parents("table").find(settings.checkboxClass), function () {
                            $(this).prop('checked', false);
                        });
                    }
                });
                $(this).find(settings.checkboxClass).on('click', function () {
                    var totalCheckbox = $(this).parents("table").find(settings.checkboxClass).length;
                    var totalChecked = $(this).parents("table").find(settings.checkboxClass + ":checked").length;

                    if (totalCheckbox == totalChecked) {
                        if (!$(this).parents("table").find(settings.checkAllCheckboxClass).is(':checked')) {
                            $(this).parents("table").find(settings.checkAllCheckboxClass).prop('checked', true);
                        }
                    }

                    if (totalCheckbox != totalChecked && !$(this).is(':checked')) {
                        $(this).parents("table").find(settings.checkAllCheckboxClass).prop('checked', false);
                    }
                });
            });
        };
    </script>
    <script>
        $(document).ready(function() {
            $( '#UtrDataZoneForMark' ).TableCheckAll({
                checkAllCheckboxClass: '.check-all',
                checkboxClass: '.check'
            });
        });
        function exportReportToExcel() {
            let table = document.getElementById("UtrDataZoneTbl"); // you can use document.getElementById('tableId') as well by providing id to the table tag
            TableToExcel.convert(table, { // html code may contain multiple tables so here we are refering to 1st table tag
                name: `LateSuccess.xlsx`, // fileName you could use any name
                sheet: {
                    name: 'Sheet 1' // sheetName
                }
            });
        }
        function exportReportToExcelMismatched() {
            let table = document.getElementById("UtrDataZoneTblMismatched"); // you can use document.getElementById('tableId') as well by providing id to the table tag
            TableToExcel.convert(table, { // html code may contain multiple tables so here we are refering to 1st table tag
                name: `AmountMismatched.xlsx`, // fileName you could use any name
                sheet: {
                    name: 'Sheet 1' // sheetName
                }
            });
        }
        function setAllUTR() {
            (new Digipay()).setAllUTR();
        }
    </script>

@endsection


