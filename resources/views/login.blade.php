<html lang="en"><head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Login</title>
    <link rel="stylesheet" href="{{URL::asset('assets/vendors/core/core.css')}}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="{{URL::asset('assets/css/demo_1/style.css')}}">
    <style>
        .keepays-logo {
            position: relative;
            background: rgb(50 76 197);
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 25px !important;
        }
    </style>
</head>
<body>
<div class="main-wrapper">
    <div class="page-wrapper full-page">
        <div class="page-content d-flex align-items-center justify-content-center">

            <div class="auth-form-wrapper px-4 py-5" style="width: 375px;">

                <form class="forms-sample" action="javascript:void(0)" id="authForm">
                    <div class="form-group">
                        <label for="txtUsername">Username</label>
                        <input type="email" class="form-control" id="email_id" name="email_id" autocomplete="off" placeholder="Username">
                    </div>
                    <div class="form-group">
                        <label for="txtPassword">Password</label>
                        <input type="password" class="form-control" id="password" name="password" autocomplete="off" placeholder="Password">
                    </div>
                    <div class="form-group" id="newPasswordField" style="display: none">
                        <label for="txtNewPassword">New Password</label>
                        <input type="password" class="form-control" id="txtNewPassword" name="txtNewPassword" autocomplete="off" placeholder="New Password">
                    </div>
                    <div class="mt-3">
                        <button class="btn btn-primary mr-2 mb-2 mb-md-0 text-white" type="submit">Login</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<!-- core:js -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
<script src="{{URL::asset('custom/js/toastr.min.js')}}"></script>
<script src="{{URL::asset('custom/js/fs-client.js ')}}"></script>
<script src="{{URL::asset('custom/js/component/login.js?v=1')}}"></script>

</body></html>
