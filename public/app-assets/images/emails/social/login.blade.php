<!DOCTYPE html>
<html class="loading" lang="en" data-textdirection="ltr">
<!-- BEGIN: Head-->
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=0,minimal-ui">
    <title>Login Page</title>
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/images/favicon.svg') }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- BEGIN: Vendor CSS-->
    <link rel="stylesheet" type="text/css"
          href="{{ asset('app-assets/vendors/css/vendors.min.css?v=' . config('versions.css')) }}">
    <link rel="stylesheet" type="text/css"
          href="{{ asset('app-assets/vendors/css/extensions/toastr.min.css?v=' . config('versions.css')) }}">
    <!-- END: Vendor CSS-->

    <!-- BEGIN: Theme CSS-->
    <link rel="stylesheet" type="text/css"
          href="{{ asset('app-assets/css/bootstrap.css?v=' . config('versions.css')) }}">
    <link rel="stylesheet" type="text/css"
          href="{{ asset('app-assets/css/bootstrap-extended.css?v=' . config('versions.css')) }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('app-assets/css/colors.css?v=' . config('versions.css')) }}">
    <link rel="stylesheet" type="text/css"
          href="{{ asset('app-assets/css/components.css?v=' . config('versions.css')) }}">
    <link rel="stylesheet" type="text/css"
          href="{{ asset('app-assets/css/themes/dark-layout.css?v=' . config('versions.css')) }}">
    <link rel="stylesheet" type="text/css"
          href="{{ asset('app-assets/css/themes/bordered-layout.css?v=' . config('versions.css')) }}">
    <link rel="stylesheet" type="text/css"
          href="{{ asset('app-assets/css/themes/semi-dark-layout.css?v=' . config('versions.css')) }}">

    <!-- BEGIN: Page CSS-->
    <link rel="stylesheet" type="text/css"
          href="{{ asset('app-assets/css/core/menu/menu-types/vertical-menu.css?v=' . config('versions.css')) }}">
    <link rel="stylesheet" type="text/css"
          href="{{ asset('app-assets/css/plugins/forms/form-validation.css?v=' . config('versions.css')) }}">
    <link rel="stylesheet" type="text/css"
          href="{{ asset('app-assets/css/pages/authentication.css?v=' . config('versions.css')) }}">
    <link rel="stylesheet" type="text/css"
          href="{{ asset('app-assets/css/plugins/extensions/ext-component-toastr.css?v=' . config('versions.css')) }}">
    <!-- END: Page CSS-->

    <!-- BEGIN: Custom CSS-->
    <link rel="stylesheet" type="text/css" href="{{ asset('app-assets/css/style.css?v=' . config('versions.css')) }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/style.css?v=' . config('versions.css')) }}">
    <!-- END: Custom CSS-->

    <style>
        .fullscreen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            justify-content: center;
            align-items: center;
            }
        .hidden {
            display: none;
        }
        #introVideo{
            object-fit: cover;
        }
    </style>

</head>
<!-- END: Head-->

<!-- BEGIN: Body-->

<body class="vertical-layout vertical-menu-modern blank-page navbar-floating footer-static" data-open="click"
      data-menu="vertical-menu-modern" data-col="blank-page">
      {{-- <div  id="videoContainer">
            <div class="fullscreen">
                <video id="introVideo" width="100%" height="100%" autoplay muted>
                    <source src="{{ asset('assets/video/Gennie_Intro.mp4') }}" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            </div>
        </div> --}}
<!-- BEGIN: Content-->
<div class="app-content content login-wrapper" id="loginContainer">

    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper">
        <div class="content-header row">
        </div>
        <div class="content-body"><div class="auth-wrapper auth-cover">
                <div class="auth-inner row m-0">
                    <!-- Left Text-->
                    <div class="d-none d-lg-flex col-lg-8 p-0 login-bg-img">
                    </div>
                    <!-- Login-->
                    <div class="d-flex col-lg-4 align-items-center auth-bg px-2 p-lg-5">
                        <div class="col-12 col-sm-8 col-md-6 col-lg-12 px-xl-2 m-auto login-right d-block">
                            <div class="text-center ">
                                <img src="{{ asset('assets/images/logo_black.svg') }}" style="width:180px;">
                                <h3 style="margin-top:10px;"><b>Login To Your Account </b></h3>
                            </div>
                            @include('layouts.flash-message')
                            <form class="auth-login-form mt-2" action="{{route('check-login')}}" method="POST">
                                @csrf
                                <div class="mb-1 email_input">
                                    <input class="form-control @error('email') error @enderror" id="email"
                                           type="email" name="email" placeholder="Enter your email"
                                           aria-describedby="login-email" autofocus="" tabindex="1"
                                           value="{{ old('email') }}" autocomplete="on" />
                                    <img src="{{ asset('assets/images/email-icon.svg') }}" alt="">
                                    @error('email')
                                    <span id="login-email-error" class="error">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="email_input">
                                    <div class="input-group input-group-merge form-password-toggle">
                                        <input class="form-control form-control-merge" id="password"
                                               type="password" name="password" placeholder="Password"
                                               aria-describedby="login-password" tabindex="2"
                                               autocomplete="on" />
                                               <!-- <span class="input-group-text cursor-pointer"><i
                                                data-feather="eye"></i></span> -->
                                        <img src="{{ asset('assets/images/Password.svg') }}" alt="">
                                    </div>
                                    @error('password')
                                    <span id="login-password-error" class="error">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="d-flex forgot_password_position mt-0 justify-content-end" >
                                    <label></label>
                                    <a href="{{ route('forget-password') }}" class="forgot_pwd_link text-decoration-underline">Forgot Password?</a>
                                </div>
                                <button id="loginSubmitBtn" class="btn primary-btn w-100" type="submit" >Login</button>
                            </form>
                        </div>
                    </div>
                    <!-- /Login-->
                </div>
            </div>
        </div>
    </div>

</div>
<!-- END: Content-->


<!-- BEGIN: Vendor JS-->
<script src="{{ asset('app-assets/vendors/js/vendors.min.js?v=' . config('versions.js')) }}"></script>
<!-- BEGIN Vendor JS-->

<!-- BEGIN: Page Vendor JS-->
<script src="{{ asset('app-assets/vendors/js/forms/validation/jquery.validate.min.js?v=' . config('versions.js')) }}">
</script>
<script src="{{ asset('app-assets/vendors/js/extensions/toastr.min.js?v=' . config('versions.js')) }}"></script>
<!-- END: Page Vendor JS-->

<!-- BEGIN: Theme JS-->
<script src="{{ asset('app-assets/js/core/app-menu.js?v=' . config('versions.js')) }}"></script>
<script src="{{ asset('app-assets/js/core/app.js?v=' . config('versions.js')) }}"></script>
<!-- END: Theme JS-->

<!-- BEGIN: Page JS-->
<script src="{{ asset('app-assets/js/scripts/pages/auth-login.js?v=' . config('versions.js')) }}"></script>
<script src="{{ asset('app-assets/js/scripts/extensions/ext-component-toastr.js?v=' . config('versions.js')) }}">
</script>
<!-- END: Page JS-->

<!-- Vendors JS -->
<script src="{{ asset('assets/vendor/libs/@form-validation/umd/bundle/popular.min.js?v=' . config('versions.js')) }}"></script>
<script src="{{ asset('assets/vendor/libs/@form-validation/umd/plugin-bootstrap5/index.min.js?v=' . config('versions.js')) }}"></script>
<script src="{{ asset('assets/vendor/libs/@form-validation/umd/plugin-auto-focus/index.min.js?v=' . config('versions.js')) }}"></script>
<!-- END: Vendors JS -->

</body>
<!-- END: Body-->
<script type="text/javascript">
// $(document).ready(function() {

//     function checkWidth() {
//         var maxWidth = 767; // Maximum width for which you want to apply the class
//         if ($(window).width() <= maxWidth) {
//             $('#loginContainer').addClass('hidden');
//         }else{
//             $('#videoContainer').addClass('hidden');
//         }
//     }

//     checkWidth();



//     // $(window).resize(function() {
//     //     checkWidth();
//     // });
// });
// document.addEventListener('DOMContentLoaded', function() {
//         var video = document.getElementById('introVideo');
//         var videoContainer = document.getElementById('videoContainer');
//         var loginContainer = document.getElementById('loginContainer');
//         video.addEventListener('ended', function() {
//             // Hide the video container
//             videoContainer.style.display = 'none';
//             // Show the login container
//             $('#loginContainer').removeClass('hidden');
//             $('.content-header').addClass('hidden');
//         });
//     });
</script>

</html>
