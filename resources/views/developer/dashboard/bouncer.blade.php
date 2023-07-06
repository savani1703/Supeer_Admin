@extends('developer.layout.master')

@section('title', 'Bouncer')

@section('customStyle')
@endsection

@section('content')
    <div class="page-content" id="dashboard_page" style="margin-top: 0px">
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title d-flex justify-content-between">
                            Bouncer Data
                        </h6>

                        <div class="card-body shadow-sm">
                            <form action="javascript:void(0)" id="FilerForm">
                                <div class="row">
                                    <div class="col-auto">
                                        <span class="text-muted">Filter</span>
                                        <div class="form-group">
                                            <div class="d-flex ">
                                                <select name="FilterKey"  class="form-control border-right-0 ">
                                                    <option value="transaction_id">Transaction ID</option>
                                                    <option value="token">Token</option>
                                                    <option value="pg_name">PG Name</option>
                                                    <option value="is_call">is Call</option>
                                                </select>
                                                <input type="text" name="FilterValue" class="form-control" placeholder="Enter Search Value">
                                            </div>
                                        </div>
                                    </div><!-- Col -->
                                    <div class="col-auto" id="PGName">
                                        <span class="text-muted">PG</span>
                                        <div class="form-group">
                                            <select name="pg_name" id="pg_name" class="form-control">
                                                <option value="All">ALL</option>
                                                @if(isset($payInPgList))
                                                    @foreach($payInPgList as $pg)
                                                        <option value="{{$pg}}">{{$pg}}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <span class="text-muted">IS Call</span>
                                        <div class="form-group">
                                            <select name="is_call" id="is_call" class="form-control">
                                                <option value="ALL" selected>ALL</option>
                                                <option value="yes">Yes</option>
                                                <option value="no">No</option>
                                            </select>
                                        </div>
                                    </div><!-- Col -->
                                    <div class="col-auto">
                                        <span class="text-muted">Limit</span>
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
                                    <div class="col-auto mt-4">
                                        <button class="btn btn-primary border-0" type="submit">Apply</button>
                                        <button class="btn btn-danger border-0" type="reset"  onclick="resetBouncerFilterForm()">Clear</button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="table-responsive pt-3">
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <th>token</th>
                                    <th>transaction id</th>
                                    <th>PG</th>
                                    <th>is call</th>
                                    <th>ip</th>
                                    <th>redirect url</th>
                                    <th>action</th>
                                </tr>
                                </thead>
                                <tbody id="bouncerData">

                                </tbody>
                            </table>
                            <div class="pl-3" id="pagination"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <a href="#" id="scroll" style="display: none;"><span></span></a>

@endsection

@section('customJs')
    <script src="{{URL::asset('custom/js/component/developer/dashboard/bouncer-data.js?v=2')}}"></script>
@endsection


