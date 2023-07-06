@extends('developer.layout.master')

@section('title', 'Bank Proxy')

@section('customStyle')
    <link href="//cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.0.3/css/font-awesome.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/css/bootstrap-editable.css" rel="stylesheet" />
    <link href="/editor/style.css" rel="stylesheet" />
@endsection

@section('content')
    <div class="page-content" id="blockAreA" style="margin-top: 0px">
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h6 class="card-title">Bank Proxy</h6>
                        <button class="btn btn-primary" data-toggle="modal" data-target="#addProxyModal">Add Bank Proxy</button>
                    </div>
                    <div class="card-body">

                        <div class="table-responsive">
                            <table class="table table-bordered" id="dataTableExample">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>label</th>
                                    <th>proxy ip</th>
                                    <th>Active</th>
                                    <th>date</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody id="proxyData">

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <a href="#" id="scroll" style="display: none;"><span></span></a>

        <!-- Modal -->
        <div class="modal fade" id="addProxyModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Add New Bank Proxy</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="javascript:void(0)" id="addProxyForm">
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="proxyLabel">Label</label>
                                <input type="text" name="label_name" class="form-control text-uppercase" id="proxyLabel" required>
                            </div>
                            <div class="form-group">
                                <label for="proxyIp">Proxy</label>
                                <input type="text" name="ip_proxy" class="form-control" id="proxy_ip" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Add</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('customJs')
    <script src="//cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/js/bootstrap-editable.min.js"></script>
    <script src="/editor/custom.js"></script>
    <script src="{{URL::asset('custom/js/component/merchant/editable-load.js?v=1')}}"></script>
    <script src="{{URL::asset('custom/js/component/developer/dashboard/bank-proxy.js?v=2')}}"></script>
@endsection

