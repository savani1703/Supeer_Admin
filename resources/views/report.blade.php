@extends('layout.master')

@section('title', 'Merchant Management Dashboard')

@section('customStyle')
@endsection
@section('content')
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-baseline mb-2">
                <h6 class="card-title mb-5">Report</h6>
            </div>
            <div class="table-responsive" id="Report">
                <table class="table table-hover mb-0" data-show-toggle="false">
                    <thead>
                    <tr>
                        <th class="pt-0">Date</th>
                        <th class="pt-0">Email Id</th>
                        <th class="pt-0">file name</th>
                        <th class="pt-0">expire at</th>
                        <th class="pt-0">status</th>
                        <th class="pt-0">report type</th>
                        <th class="pt-0">count</th>
                        <th class="pt-0">progress</th>
                        <th class="pt-0">download Id</th>
                        <th class="pt-0">Download</th>
                    </tr>
                    </thead>
                    <tbody id="reportData">
                    </tbody>
                </table>
                <div id="pagination">
                </div>
                <a href="#" id="scroll" style="display: none;"><span></span></a>
            </div>
        </div>
    </div>
@endsection

@section('customJs')
    <script src="{{URL::asset('custom/js/component/report.js?v=2')}}"></script>

@endsection



