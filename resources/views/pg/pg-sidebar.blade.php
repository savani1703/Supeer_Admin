<li class="nav-item nav-category">PG Meta</li>
@foreach($data as $pgRoute)
<li class="nav-item">
    <a class="nav-link collapsed" data-toggle="collapse" href="#{{$pgRoute['pg']}}Meta" role="button" aria-expanded="false" aria-controls="{{$pgRoute['pg']}}Meta">
        <span class="link-title ml-0">{{ucfirst(strtolower($pgRoute['pg']))}}</span>
{{--        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-down link-arrow"><polyline points="6 9 12 15 18 9"></polyline></svg>--}}
    </a>
    <div class="collapse" id="{{$pgRoute['pg']}}Meta" style="">
        <ul class="nav sub-menu">
            @if(isset($pgRoute['payin_route']))
            <li class="nav-item">
                <a href="{{$pgRoute['payin_route']}}" class="nav-link">Payin Meta</a>
            </li>
            @endif
            @if(isset($pgRoute['payout_route']))
            <li class="nav-item">
                <a href="{{$pgRoute['payout_route']}}" class="nav-link">Payout Meta</a>
            </li>
            @endif
        </ul>
    </div>
</li>
@endforeach
