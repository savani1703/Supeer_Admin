@extends('layout.master')
@section('title', 'Block Info')
@section('customStyle')
@endsection
@section('content')
<div class="card">
    <div class="row">
        <div class="row w-100 mx-0 auth-page"></div>
         <div class="col-md-12" id="reconPage">
            <div class="card">
            <div class="card card-outline-info mb-0">
                <div class="p-3">
                    <h6 class="font-weight-bold">Block Info</h6>
                </div>
            <div class="card-body shadow-sm mt-4">
                <form action="javascript:void(0)" id="BlockInfoForm">
                    <div class="row mt-4">
                        <div class="col-auto">
                            <div class="form-group">
                                <div class="d-flex ">
                                    <select name="FilterKey" id="FilterKey" class="form-control border-right-0 ">
                                        <option value="block_data">Search Data</option>
                                    </select>
                                    <input type="text" name="FilterValue" class="form-control" placeholder="Enter Search Value">
                                </div>
                            </div>
                        </div><!-- Col -->
                        <div class="col-auto">
                            <div class="form-group">
                                <select name="Limit" id="Limit" class="form-control">
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                    <option value="200">200</option>
                                    <option value="300">300</option>
                                    <option value="400">400</option>
                                    <option value="500">500</option>
                                </select>
                            </div>
                        </div><!-- Col -->
                        <div class="col-auto">
                            <label class="control-label"></label>
                            <button class="btn btn-primary border-0" type="submit">Apply</button>
                            <button class="btn btn-danger border-0" type="reset"  onclick="resetBlockInfoForm()">Clear</button>
                        </div><!-- Col -->
                        @if((new \App\Plugin\AccessControl\AccessControl())->hasAccessModule(\App\Plugin\AccessControl\Utils\AccessModule::BANK_TRANSACTION_REPORT))
                            <div class="col-auto">
                                <button class="btn btn-primary" type="button"  onclick="generateBlockInfoReport()">Generate Report</button>
                            </div>
                        @endif
                    </div>
                </form>
            </div>
        <div class="mt-1">
            <div class="mt-5 table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                    <tr>
                        <th class="pt-0">Data</th>
                        <th class="pt-0">Identify</th>
                        <th class="pt-0">Block Type</th>
                        <th class="pt-0">created at</th>
                        <th class="pt-0">Action</th>
                    </tr>
                    </thead>
                    <tr class="Loader" id="#preloader">
                        <td colspan="9" align="center">
                            <div class="spinner-grow  text-primary" role="status">
                            </div>
                        </td>
                    </tr>
                    <tbody id="BlockInfo"></tbody>
                </table>
                <div class="ml-2 " id="pagination"></div>
            </div>
            <a href="#" id="scroll" style="display: none;"><span></span></a>
        </div>
     </div>
    </div>
  </div>
</div>
@endsection
@section('customJs')
    <script src="{{URL::asset('custom/js/component/block_info.js?v=11')}}"></script>
@endsection


