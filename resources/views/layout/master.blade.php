<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title')</title>
    <link rel="stylesheet" href="{{URL::asset('assets/vendors/core/core.css')}}">
    <link rel="stylesheet" href="{{URL::asset('assets/fonts/feather-font/css/iconfont.css')}}">
    <link rel="stylesheet" href="{{URL::asset('assets/vendors/flag-icon-css/css/flag-icon.min.css')}}">
    <link rel="stylesheet" href="{{URL::asset('assets/css/demo_1/style.css?v=3')}}">

    {{--<link rel="stylesheet" href="{{URL::asset('assets/css/demo2/app.css?v=3')}}">--}}

    <link rel="stylesheet" href="//cdn.materialdesignicons.com/5.4.55/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="{{URL::asset('/custom/plugin/snackbar.min.css')}}">
    <link rel="stylesheet" href="{{URL::asset('/custom/css/style.css?v=3')}}">
    <link rel="stylesheet" href="{{URL::asset('assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.css')}}">
    <link rel="stylesheet" href="{{URL::asset('custom/css/media.css')}}">
    <link href="https://cdn.jsdelivr.net/gh/StephanWagner/jBox@v1.3.2/dist/jBox.all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Mulish:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">


    <style>
        .otp.form-control {
            width: 40px;
            height: 40px;
            text-align: center;
            margin: auto;
            padding: 0;
        }

    </style>
    @yield('customStyle')
</head>
    <body class="sidebar-folded">
        <div class="main-wrapper">
            <nav class="sidebar">

                <div class="sidebar-header">

                    <div class="sidebar-toggler not-active">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>

                <div class="sidebar-body">
                    <ul class="nav">
                        @if(isset($sidebarData))
                            @foreach($sidebarData as $group => $sidebar)
                                @if(isset($group) && !empty($group))
                                    <li class="nav-item nav-category">{{$group}}</li>
                                @endif
                                @foreach($sidebar as $_sidebar)
                                    <li class="nav-item">
                                        <a href="{{$_sidebar['is_child'] ? '#'.str_replace(" ", "", $_sidebar['name']).'collapse' : $_sidebar['route']}}"
                                           class="nav-link {{$_sidebar['is_child'] ? "collapsed" : ''}}"
                                        {{$_sidebar['is_child'] ? "data-toggle=collapse" : ''}}
                                     >
                                         <i class="link-icon"
                                            {{$_sidebar['icon_class'] ? "data-feather={$_sidebar['icon_class']}" : ''}}></i>
                                            <span class="link-title">{{$_sidebar['name']}}</span>
                                        </a>
                                        @if($_sidebar['is_child'])

                                            <div class="collapse" id="{{str_replace(" ", "", $_sidebar['name']).'collapse'}}" style="">
                                                <ul class="nav sub-menu">
                                                    @if(isset($_sidebar['child']))
                                                    @foreach($_sidebar['child'] as $childSidebar)
                                                        <li class="nav-item">
                                                            <a href="{{$childSidebar['child_route']}}" class="nav-link">{{$childSidebar['name']}}</a>
                                                        </li>
                                                    @endforeach
                                                    @endif
                                                </ul>
                                            </div>
                                        @endif
                                    </li>
                                @endforeach
                            @endforeach
                        @endif

                    </ul>
                </div>
            </nav>

            <div class="page-wrapper">

                <!-- partial:partials/_navbar.html -->
                <nav class="navbar">
                    <a href="#" class="sidebar-toggler">
                        <i data-feather="menu"></i>
                    </a>
                    <div class="navbar-content">
                        <ul class="navbar-nav">
                            @if((new App\Plugin\AccessControl\AccessControl())->hasAccessModule(App\Plugin\AccessControl\Utils\AccessModule::DEVELOPER_MODULE))
                            <li class="nav-item">
                                <a href="/developer" class="nav-link">
                                    <h4><i class="mdi mdi-code-braces"></i></h4>
                                    <span class="link-title font-weight-bold">Dev</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="/risk" class="nav-link">
                                    <h4><i class="mdi mdi-account-alert"></i></h4>
                                    <span class="link-title font-weight-bold">Risk</span>
                                </a>
                            </li>
                            @endif
                            <li class="nav-item">
                                <a class="nav-link" href="/logout" id="messageDropdown" role="button">
                                    <h4><i class="mdi mdi-logout-variant"></i></h4>
                                    <span>Log Out</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </nav>
                <!-- partial -->

                <div class="page-content">

                    @yield('content')

                </div>

                <!-- partial:partials/_footer.html -->
                <footer class="footer d-flex flex-column flex-md-row align-items-center justify-content-between">

                </footer>
                <!-- partial -->
            </div>
        </div>





        <div class="modal fade" id="GAuthenticatorOtpModal" tabindex="-1" aria-labelledby="GAuthenticatorOtpModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-sm modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="GAuthenticatorOtpModalLabel">Verify Google Authenticator OTP</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="btn-close"></button>
                    </div>
                    <form action="javascript:void(0)" id="GAuthenticatorOtpForm">
                        <div class="modal-body text-center">
                            <div class="form-group d-flex">
                                <input class="otp form-control mr-1" name="otp" type="text" oninput='DpzGAuthService.digitValidate(this)' onkeyup='DpzGAuthService.tabChange(1)' maxlength=1 >
                                <input class="otp form-control mr-1" name="otp" type="text" oninput='DpzGAuthService.digitValidate(this)' onkeyup='DpzGAuthService.tabChange(2)' maxlength=1 >
                                <input class="otp form-control mr-1" name="otp" type="text" oninput='DpzGAuthService.digitValidate(this)' onkeyup='DpzGAuthService.tabChange(3)' maxlength=1 >
                                <input class="otp form-control mr-1" name="otp" type="text" oninput='DpzGAuthService.digitValidate(this)' onkeyup='DpzGAuthService.tabChange(4)' maxlength=1 >
                                <input class="otp form-control mr-1" name="otp" type="text" oninput='DpzGAuthService.digitValidate(this)' onkeyup='DpzGAuthService.tabChange(5)' maxlength=1 >
                                <input class="otp form-control mr-1" name="otp" type="text" oninput='DpzGAuthService.digitValidate(this)' onkeyup='DpzGAuthService.tabChange(6)' maxlength=1 >
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Verify</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>



        <script src="{{URL::asset('assets/vendors/core/core.js')}}"></script>
        <script src="{{URL::asset('assets/vendors/chartjs/Chart.min.js')}}"></script>
        <script src="{{URL::asset('assets/vendors/jquery.flot/jquery.flot.js')}}"></script>
        <script src="{{URL::asset('assets/vendors/jquery.flot/jquery.flot.resize.js')}}"></script>
        <script src="{{URL::asset('assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.js')}}"></script>
        <script src="{{URL::asset('assets/vendors/apexcharts/apexcharts.min.js')}}"></script>
        <script src="{{URL::asset('assets/vendors/progressbar.js/progressbar.min.js')}}"></script>
        <script src="{{URL::asset('assets/vendors/feather-icons/feather.min.js')}}"></script>
        <script src="{{URL::asset('assets/js/template.js?v=1')}}"></script>
        <script src="{{URL::asset('assets/js/jquery.blockUI.min.js')}}"></script>
        <script src="{{URL::asset('assets/js/jBox.all.min.js')}}"></script>
        <script src="{{URL::asset('assets/js/jquery-confirm.min.js')}}"></script>
        <script src="{{URL::asset('assets/js/moment.min.js')}}"></script>
        <script src="{{URL::asset('assets/js/daterangepicker.min.js')}}"></script>
        <!-- custom js for this page -->
        <script src="{{URL::asset('/custom/js/toastr.min.js')}}"></script>
        <script src="{{URL::asset('/custom/js/fs-client.js?v=3')}}"></script>
        <script src="{{URL::asset('/custom/js/fs-paginate.js?v=1')}}"></script>
        <script>
            $(document).ready(function(){
                $(window).scroll(function(){
                    if ($(this).scrollTop() > 100) {
                        $('#scroll').fadeIn();
                    } else {
                        $('#scroll').fadeOut();
                    }
                });
                $('#scroll').click(function(){
                    $("html, body").animate({ scrollTop: 0 }, 600);
                    return false;
                });
            });
        </script>

        @yield('customJs')
    </body>
</html>
