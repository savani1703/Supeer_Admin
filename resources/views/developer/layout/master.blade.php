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
    <link rel="stylesheet" href="//cdn.materialdesignicons.com/5.4.55/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="{{URL::asset('assets/css/demo_1/style.css?v=1')}}">
    <link rel="stylesheet" href="{{URL::asset('/custom/plugin/snackbar.min.css')}}">
    <link rel="stylesheet" href="{{URL::asset('custom/css/style.css?v=1')}}">
    <link rel="stylesheet" href="{{URL::asset('assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.css')}}">
    <link rel="stylesheet" href="{{URL::asset('custom/css/media.css')}}">
    <link href="https://cdn.jsdelivr.net/gh/StephanWagner/jBox@v1.3.2/dist/jBox.all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
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
                    <a href="#" class="sidebar-brand">
                        Developer<span></span>
                     </a>
                    <div class="sidebar-toggler not-active">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>

                <div class="sidebar-body">
                    <ul class="nav">
                        <li class="nav-item nav-category">Main</li>
                        <li class="nav-item">
                            <a href="/developer/dashboard" class="nav-link">
                                <i class="link-icon" data-feather="box"></i>
                                <span class="link-title">Dashboard</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="/developer/pg-routers" class="nav-link">
                                <i class="link-icon" data-feather="git-branch"></i>
                                <span class="link-title">PG Routers</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="/developer/proxy" class="nav-link">
                                <i class="link-icon" data-feather="git-commit"></i>
                                <span class="link-title">Proxy</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="/developer/bouncer" class="nav-link">
                                <i class="link-icon" data-feather="git-pull-request"></i>
                                <span class="link-title">Bouncer</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="/developer/payout-bank-down" class="nav-link">
                                <i class="link-icon" data-feather="trending-down"></i>
                                <span class="link-title">Payout Bank Down</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="/developer/payout-whitelist-client" class="nav-link">
                                <i class="link-icon" data-feather="user-check"></i>
                                <span class="link-title">Payout Clients</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="/developer/payin/summary" class="nav-link">
                                <i class="link-icon" data-feather="trending-up"></i>
                                <span class="link-title">PayIn Summary</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="/developer/sms-logs" class="nav-link">
                                <i class="link-icon" data-feather="sliders"></i>
                                <span class="link-title">SMS Logs</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="/developer/mail-reader" class="nav-link">
                                <i class="link-icon" data-feather="mail"></i>
                                <span class="link-title">Mail Reader</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="/developer/bank-proxy" class="nav-link">
                                <i class="link-icon" data-feather="git-commit"></i>
                                <span class="link-title">Bank Proxy</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/developer/swift-customer" class="nav-link">
                                <i class="link-icon" data-feather="refresh-ccw"></i>
                                <span class="link-title">Swift Customer</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="/developer/idfc-mail-webhook" class="nav-link">
                                <i class="link-icon" data-feather="mail"></i>
                                <span class="link-title">IDFC Mail Webhook</span>
                            </a>
                        </li>
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
                            <li class="nav-item">
                                <a href="/" class="nav-link">
                                    <h4><i class="mdi mdi-airplane-takeoff"></i></h4>
                                    <span class="link-title font-weight-bold">Main</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="/risk" class="nav-link">
                                    <h4><i class="mdi mdi-account-alert"></i></h4>
                                    <span class="link-title font-weight-bold">Risk</span>
                                </a>
                            </li>
                            <li class="nav-item dropdown nav-messages">
                                <a class="nav-link" href="/logout" id="messageDropdown" role="button">
                                    <i data-feather="log-out"></i>
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
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.blockUI/2.70/jquery.blockUI.min.js"></script>
        <script src="https://cdn.jsdelivr.net/gh/StephanWagner/jBox@v1.3.2/dist/jBox.all.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
        <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
        <!-- custom js for this page -->
        <script src="{{URL::asset('/custom/js/toastr.min.js')}}"></script>
        <script src="{{URL::asset('/custom/js/fs-client.js?v=2')}}"></script>
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
