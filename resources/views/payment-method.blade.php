@extends('layout.master')

@section('title', 'Payment Method')

@section('customStyle')

@endsection

@section('content')
    <div class="card">
        <div class="row">
            <div class="row w-100 mx-0 auth-page" id="unauthorized_user"></div>
            <div class="col-md-12" id="paymentMethodDetail">
                <div class="card">
                    <div class="card card-outline-info mb-0">
                        <div class="p-3">
                            <h6 class="font-weight-bold">Payment Method </h6>
                            <button type="button"  onclick="loadAvailableMethods()" class="float-right btn btn-primary mdi mdi-plus " data-toggle="modal" data-target="#addPaymentMethod" data-whatever="@mdo"> Add Payment Method</button>
                        </div>
                        <div class="card-body shadow-sm">
                            <form action="javascript:void(0)" id="FilerForm">
                                <div class="row">
                                    <div class="col-auto">
                                        <div class="form-group">
                                            <div class="d-flex">
                                                <select name="FilterKey"  class="form-control border-right-0 ">
                                                    <option value="pg_name">PG Name</option>
                                                    <option value="meta_code">Meta Code</option>
                                                    <option value="method_name">Method Name</option>
                                                    <option value="method_code">Method Code</option>
                                                    <option value="currency">Currency</option>
                                                </select>
                                                <input type="text" name="FilterValue" class="form-control" placeholder="Enter Search Value">
                                            </div>
                                        </div>
                                    </div><!-- Col -->

                                    <div class="col-auto">
                                        <div class="form-group">
                                            <select name="Limit" id="Limit" class="form-control">
                                                <option value="50" selected>50</option>
                                                <option value="100">100</option>
                                                <option value="200">200</option>
                                                <option value="300">300</option>
                                                <option value="400">400</option>
                                                <option value="500">500</option>
                                            </select>
                                        </div>
                                    </div><!-- Col -->
                                    <div class="col-auto">
                                        <div class="form-group">
                                            <select name="status" id="status" class="form-control">
                                                <option value="">All</option>
                                                <option value="active">Active</option>
                                                <option value="deActive">DeActive</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <label class="control-label"></label>
                                        <button class="btn btn-primary" type="submit">Apply</button>
                                        <button class="btn btn-danger" type="button"  onclick="resetPaymentMethodForm()">Clear</button>
                                    </div><!-- Col -->
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card mt-1">
                    <div class="table-responsive mt-5">
                        <table class="table table-hover mb-0">
                            <thead>
                            <tr>
                                <th class="pt-0">Pg Method</th>
                                <th class="pt-0">PG Name</th>
                                <th class="pt-0">method code</th>
                                <th class="pt-0">meta code</th>
                                <th class="pt-0">Seamless</th>
                                <th class="pt-0">isActive</th>
                                <th class="pt-0">has sub method</th>
                                <th class="pt-0">created at</th>
                                <th class="pt-0">updated at</th>
                            </tr>
                            </thead>
                            <tbody id="paymentMethodData">

                            </tbody>
                        </table>
                        <div class="pl-3" id="pagination"></div>
                        <a href="#" id="scroll" style="display: none;"><span></span></a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addPaymentMethod" tabindex="-1"  aria-labelledby="addPaymentMethod" data-backdrop="static" data-keyboard="false" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-lg">
                <div class="modal-header">
                    <h5 class="modal-title">Add Payment Method</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="paymentMetaForm" action="javascript:void(0)">
                    <div class="modal-body">
                        <div class="form-group">
                            <label  class="col-form-label">Pg Method Id</label>
                            <select name="pg_method_id" id="availableMethodData"></select>
                        </div>


                        <div class="row">
                            <div class="form-group mr-3 ml-5">
                                <label  class="col-form-label">Enter Pg Name</label>
                                <input type="text" class="form-control" name="pg_name" placeholder="Enter Pg Name">
                            </div>
                            <div class="form-group">
                                <label class="col-form-label">Enter Meta Code</label>
                                <input type="text" class="form-control" name="meta_code" placeholder="Enter Meta Code">
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group mr-3 ml-5">
                                <label  class="col-form-label">Enter Method Name</label>
                                <input type="text" class="form-control" name="method_name" placeholder="Enter Method Name">
                            </div>
                            <div class="form-group">
                                <label class="col-form-label">Enter Method Code</label>
                                <input type="text" class="form-control" name="method_code" placeholder="Enter Method Code">
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group mr-3 ml-5">
                                <label  class="col-form-label">Is Seamless</label>
                                <select name="is_seamless" id="is_seamless">
                                    <option value="1">Seamless</option>
                                    <option value="0">Hosted</option>
                                </select>
                            </div>
                            <div class="form-group" style="margin-left:88px;">
                                <label class="col-form-label">Has Sub Method</label>
                                <select name="has_sub_method" id="has_sub_method">
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button id="close_btn" type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button  type="submit" class="btn btn-primary">Add Merchant</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@section('customJs')
    <script src="{{URL::asset('custom/js/component/payment-method.js?v=2')}}"></script>
@endsection
