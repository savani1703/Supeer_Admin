@extends('developer.layout.master')

@section('title', 'Proxy')

@section('customStyle')
    <link href="//cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css" rel="stylesheet" />
@endsection

@section('content')
    <div class="page-content" id="blockAreA" style="margin-top: 0px">
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h6 class="card-title">Proxy</h6>
                        <button class="btn btn-primary" data-toggle="modal" data-target="#addProxyModal">Add Proxy</button>
                    </div>
                    <div class="card-body">

                        <div class="table-responsive">
                            <table class="table table-bordered" id="dataTableExample">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>label</th>
                                    <th>proxy ip</th>
                                    <th>is get</th>
                                    <th>date</th>
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
                        <h5 class="modal-title" id="exampleModalLabel">Add New Proxy</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="javascript:void(0)" id="addProxyForm">
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="proxyLabel">Label</label>
                                <input type="text" name="label" class="form-control text-uppercase" id="proxyLabel" required>
                            </div>
                            <div class="form-group">
                                <label for="proxyIp">Proxy</label>
                                <input type="text" name="proxy_ip" class="form-control" id="proxy_ip" required>
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
    <script src="{{URL::asset('custom/js/component/developer/dashboard/proxy.js?v=1')}}"></script>
@endsection

