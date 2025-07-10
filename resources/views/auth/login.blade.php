<!DOCTYPE html>
<html class="loading" lang="en" data-textdirection="ltr">
<!-- BEGIN: Head-->

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=0,minimal-ui">
    <title>Login Page</title>
    {{-- <link rel="apple-touch-icon" href="{{ asset('app-assets/images/ico/apple-icon-120.png') }}"> --}}
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('app-assets/images/ico/favicon.ico') }}">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,300;0,400;0,500;0,600;1,400;1,500;1,600"
        rel="stylesheet">

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
    <!-- END: Custom CSS-->

</head>
<!-- END: Head-->

<!-- BEGIN: Body-->

<body class="vertical-layout vertical-menu-modern blank-page navbar-floating footer-static" data-open="click"
    data-menu="vertical-menu-modern" data-col="blank-page">
    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper">
            <div class="content-header row">
            </div>
            <div class="content-body">
                <div class="auth-wrapper auth-cover">
                    <div class="auth-inner row m-0">
                        <!-- Brand logo--><a class="brand-logo" href="{{ route('login') }}">
                            <img src="{{ asset('app-assets/images/logo/logo.png') }}" style="width:150px;">
                            <!-- <h2 class="brand-text text-primary ms-1">{{ config('app.name') }}</h2> -->
                        </a>
                        <!-- /Brand logo-->
                        <!-- Left Text-->
                        <div class="d-none d-lg-flex col-lg-8 align-items-center p-5">
                            <div class="w-100 d-lg-flex align-items-center justify-content-center px-5"><img
                                    class="img-fluid" src="{{ asset('app-assets/images/pages/login-v2.svg') }}"
                                    alt="Login V2" /></div>
                        </div>
                        <!-- /Left Text-->
                        <!-- Login-->
                        <div class="d-flex col-lg-4 align-items-center auth-bg px-2 p-lg-5">
                            <div class="col-12 col-sm-8 col-md-6 col-lg-12 px-xl-2 mx-auto">
                                <h2 class="card-title fw-bold mb-1">Welcome to {{ config('app.name') }} 👋</h2>
                                <p class="card-text mb-2">Please sign-in to your account and start the adventure</p>
                                <form class="auth-login-form mt-2" action="{{ route('login') }}" method="POST">
                                    @csrf
                                    <div class="mb-1">
                                        <label class="form-label" for="login-email">Email</label>
                                        <input class="form-control @error('email') error @enderror" id="login-email"
                                            type="text" name="email" placeholder="Enter email"
                                            aria-describedby="login-email" autofocus="" tabindex="1"
                                            value="{{ old('email') }}" autocomplete="on" />
                                        @error('email')
                                            <span id="login-email-error" class="error">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="mb-1">
                                        <div class="d-flex justify-content-between">
                                            <label class="form-label" for="login-password">Password</label><a
                                                href="{{ route('password.request') }}"><small>Forgot
                                                    Password?</small></a>
                                        </div>
                                        <div class="input-group input-group-merge form-password-toggle">
                                            <input class="form-control form-control-merge" id="login-password"
                                                type="password" name="password" placeholder="Enter password"
                                                aria-describedby="login-password" tabindex="2"
                                                autocomplete="on" /><span class="input-group-text cursor-pointer"><i
                                                    data-feather="eye"></i></span>
                                        </div>
                                        @error('password')
                                            <span id="login-password-error" class="error">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    {{-- <div class="mb-1">
                                        <div class="form-check">
                                            <input class="form-check-input" id="remember-me" type="checkbox"
                                                tabindex="3" name="remember" checked />
                                            <label class="form-check-label" for="remember-me"> Remember Me</label>
                                        </div>
                                    </div> --}}
                                    <button id="loginSubmitBtn" class="btn btn-primary w-100" tabindex="4">Sign in</button>
                                </form>
                                <div class="alert alert-danger mt-2" role="alert">
                                    <div class="alert-body d-flex align-items-center">
                                        <i data-feather="alert-circle" class="me-1 font-medium-1"></i>
                                        Please allow location permission in browser to login.
                                    </div>
                                </div>
                                <!-- <p class="text-center mt-2"><span>New on our platform?</span><a href="auth-register-cover.html"><span>&nbsp;Create an account</span></a></p>
                                <div class="divider my-2">
                                    <div class="divider-text">or</div>
                                </div>
                                <div class="auth-footer-btn d-flex justify-content-center"><a class="btn btn-facebook" href="#"><i data-feather="facebook"></i></a><a class="btn btn-twitter white" href="#"><i data-feather="twitter"></i></a><a class="btn btn-google" href="#"><i data-feather="mail"></i></a><a class="btn btn-github" href="#"><i data-feather="github"></i></a></div> -->
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

    <script>
        $(window).on('load', function() {
            if (feather) {
                feather.replace({
                    width: 14,
                    height: 14
                });
            }
            @if (session()->has('status'))
                @switch(session('type'))
                    @case('success')
                    toastr.success('{{ session('message') }}', '{{ session('status') }}');
                    @break

                    @case('error')
                    toastr.error('{{ session('message') }}', '{{ session('status') }}');
                    @break

                    @case('warning')
                    toastr.warning('{{ session('message') }}', '{{ session('status') }}');
                    @break

                    @default
                    toastr.info('{{ session('message') }}', '{{ session('status') }}');
                @endswitch
            @endif

            const session_lifetime_in_minutes = @json((int) config('session.lifetime')),
                is_authenticated = @json(auth()->check());
            var watch_id;
            askLocation();

            function askLocation() {
                if (!("geolocation" in navigator)) {
                    setCoOrdinateCookies();
                    redirectToLogin();
                }
                watch();
            }

            function watch() {
                if (watch_id) navigator.geolocation.clearWatch(watch_id);

                watch_id = navigator.geolocation.watchPosition(
                    function(pos) {
                        let lat = pos?.coords?.latitude ?? '';
                        let lon = pos?.coords?.longitude ?? '';
                        setCoOrdinateCookies(lat, lon);
                    },
                    function(err) {
                        console.error(err);
                        setCoOrdinateCookies();
                        redirectToLogin();
                    }, {
                        enableHighAccuracy: true,
                    });
            }

            function setCoOrdinateCookies(lat = '', long = '') {
                let d = new Date();
                d.setTime(d.getTime() + (session_lifetime_in_minutes * 60 * 1000));
                document.cookie = `posLat=${lat}; expires=${d.toUTCString()};`;
                document.cookie = `posLon=${long}; expires=${d.toUTCString()};`;
            }

            function redirectToLogin() {
                if (is_authenticated) {
                    window.location.href = route('logout.public');
                } else {
                    window.location.href = route('login');
                }
            }

            navigator.permissions
                .query({
                    name: "geolocation"
                })
                .then((permissionStatus) => {
                    permissionStatus.onchange = () => {
                        if (['prompt', 'denied'].includes(permissionStatus.state)) {
                            setCoOrdinateCookies();
                            redirectToLogin();
                        } else {
                            watch();
                        }
                    };
                })
        })
    </script>
</body>
<!-- END: Body-->

</html>
