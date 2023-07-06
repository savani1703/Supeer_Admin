@extends('layout.master')

@section('title', 'Recon System')

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
                            <h6 class="font-weight-bold">Recon System</h6>
                        </div>
                        <div class="card-body shadow-sm pt-0">
                            <form action="javascript:void(0)" id="reconForm">
                                <div class="row mt-4">
                                    <div class="col-auto">
                                        <div class="form-group">
                                            <div class="d-flex ">
                                                <select name="FilterKey"  class="form-control border-right-0 ">
                                                    @if(sizeof($options) > 0)
                                                        @foreach($options as $option)
                                                            <option value="{{$option['value']}}">{{$option['label']}}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                                <input type="text" name="FilterValue" class="form-control" placeholder="Enter Search Value">
                                            </div>
                                        </div>
                                    </div><!-- Col -->
                                    <div class="col-auto">
                                        <label class="control-label"></label>
                                        <button class="btn btn-primary" type="submit">Apply</button>
                                        <button class="btn btn-danger" type="button"  onclick="resetReconForm()">Clear</button>
                                    </div><!-- Col -->
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="row mt-2" id="responseData">

                </div>
            </div>
        </div>
    </div>

@endsection

@section('customJs')
    <script src="{{URL::asset('custom/js/component/recon.js?v=3')}}"></script>

@endsection


