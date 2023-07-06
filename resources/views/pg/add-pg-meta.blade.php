<div class="ml-2 mr-2 modal-body-merchant">
    <div class="row">
    @if(isset($columns))
        @foreach($columns as $column)
                @if(strcmp($column, "proxy_id") === 0)
                    <div class="form-group col-6">
                        <label for="Callback Url" class="col-form-label mb-0">Bouncer</label>
                        <select name="{{$column}}" id="" required>
                            <option disabled selected> --- Select Bouncer --- </option>
                            @if(isset($proxyList))
                                @foreach($proxyList as $proxy)
                                    <option value="{{$proxy->id}}">{{$proxy->label_name}}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                @elseif(strcmp($column, "is_seamless") === 0)
                    <div class="form-group col-6">
                        <label for="Callback Url" class="col-form-label mb-0">Is Seamless</label>
                        <select name="{{$column}}" class="form-control col-6" required>
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>
                @else
                    <div class="form-group col-6">
                        <label for="Callback Url" class="col-form-label mb-0">{{ucwords(str_replace("_", " ", $column))}}</label>
                        <input type="text" class="form-control" name="{{$column}}" required>
                    </div>
                @endif
        @endforeach
    @endif
    </div>
</div>
