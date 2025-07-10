@extends('layouts.guest')

@section('page-css')
    <link rel="stylesheet" type="text/css" href="{{ asset("app-assets/css/core/menu/menu-types/vertical-menu.css?v=".config("versions.css")) }}" >
    <link rel="stylesheet" type="text/css" href="{{ asset("app-assets/css/plugins/forms/form-validation.css?v=".config("versions.css")) }}" >
    <link rel="stylesheet" type="text/css" href="{{ asset("app-assets/css/pages/authentication.css?v=".config("versions.css")) }}" >
@endsection

@section('custom-css')
@endsection

@section('content')
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
                        <!-- Brand logo--><a class="brand-logo" href="{{route('login')}}">
                            <img src="{{asset('app-assets/images/logo/logo.png')}}" style="width:150px;">
                        </a>
                        <!-- /Brand logo-->
                        <!-- Left Text-->
                        <div class="d-none d-lg-flex col-lg-8 align-items-center p-5">
                            <div class="w-100 d-lg-flex align-items-center justify-content-center px-5"><img class="img-fluid" src="{{ asset("app-assets/images/pages/forgot-password-v2.svg")}}" alt="Forgot password V2" /></div>
                        </div>
                        <!-- /Left Text-->
                        <!-- Forgot password-->
                        <div class="d-flex col-lg-4 align-items-center auth-bg px-2 p-lg-5">
                            <div class="col-12 col-sm-8 col-md-6 col-lg-12 px-xl-2 mx-auto">
                                <h2 class="card-title fw-bold mb-1">Forgot Password? 🔒</h2>
                                <p class="card-text mb-2">Enter your email and we'll send you instructions to reset your password</p>
                                <form class="auth-forgot-password-form mt-2" action="{{ route('password.email', []) }}" method="POST">
                                    @csrf
                                    <div class="mb-1">
                                        <label class="form-label" for="forgot-password-email">Email</label>
                                        <input
                                            class="form-control"
                                            id="forgot-password-email"
                                            type="text"
                                            name="email"
                                            placeholder="Enter email"
                                            aria-describedby="forgot-password-email"
                                            autofocus=""
                                            tabindex="1"
                                            value="{{ old("email") }}" />
                                        @error('email')
                                            <span id="forgot-password-email-error" class="error">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <button class="btn btn-primary w-100" tabindex="2" type="submit">Send reset link</button>
                                </form>
                                <p class="text-center mt-2"><a href="{{ route('login', []) }}"><i data-feather="chevron-left"></i> Back to login</a></p>
                            </div>
                        </div>
                        <!-- /Forgot password-->
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Content-->
@endsection


@section('page-vendor-js')
    <script src="{{ asset("app-assets/vendors/js/forms/validation/jquery.validate.min.js?v=".config("versions.js")) }}" ></script>
@endsection

@section('page-js')
    <script src="{{ asset("app-assets/js/scripts/pages/auth-forgot-password.js?v=".config("versions.js")) }}" ></script>
@endsection

@section('custom-js')
    <script>
        $(window).on('load', function() {
            if (feather) {
                feather.replace({
                    width: 14,
                    height: 14
                });
            }
        })
    </script>
@endsection
