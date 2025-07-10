<!DOCTYPE html>
<html class="loading" lang="en" data-textdirection="ltr">
<!-- BEGIN: Head-->

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=0,minimal-ui">
    <title>{{ config('app.name', 'IIH CRM') }}</title>
    {{-- <link rel="apple-touch-icon" href="{{ asset('app-assets/images/ico/apple-icon-120.png') }}"> --}}
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('app-assets/images/ico/favicon.ico') }}">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,300;0,400;0,500;0,600;1,400;1,500;1,600"
        rel="stylesheet">

    <!-- BEGIN: Vendor CSS-->
    <link rel="stylesheet" type="text/css"
        href="{{ asset('app-assets/vendors/css/vendors.min.css?v=' . config('versions.css')) }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('app-assets/vendors/css/extensions/toastr.min.css?v=' . config('versions.css')) }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('app-assets/css/plugins/extensions/ext-component-toastr.css?v=' . config('versions.css')) }}">
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
    @yield('page-css')
    <!-- END: Page CSS-->

    <!-- BEGIN: Custom CSS-->
    @yield('custom-css')
    <!-- END: Custom CSS-->

</head>
<!-- END: Head-->

<!-- BEGIN: Body-->

<body class="vertical-layout vertical-menu-modern blank-page navbar-floating footer-static" data-open="click"
    data-menu="vertical-menu-modern" data-col="blank-page">
    <!-- BEGIN: Content-->
    @yield('content')
    <!-- END: Content-->


    <!-- BEGIN: Vendor JS-->
    <script src="{{ asset('app-assets/vendors/js/vendors.min.js?v=' . config('versions.js')) }}"></script>
    <!-- BEGIN Vendor JS-->

    <!-- BEGIN: Page Vendor JS-->
    <script src="{{ asset('app-assets/vendors/js/extensions/toastr.min.js?v=' . config('versions.js')) }}"></script>
    <script src="{{ asset('app-assets/js/scripts/extensions/ext-component-toastr.js?v=' . config('versions.js')) }}">
    </script>
    @yield('page-vendor-js')
    <!-- END: Page Vendor JS-->

    <!-- BEGIN: Theme JS-->
    <script src="{{ asset('app-assets/js/core/app-menu.js?v=' . config('versions.js')) }}"></script>
    <script src="{{ asset('app-assets/js/core/app.js?v=' . config('versions.js')) }}"></script>
    <!-- END: Theme JS-->

    <!-- BEGIN: Page JS-->
    @yield('page-js')
    <!-- END: Page JS-->

    <script>
        $(document).ready(function() {
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
        });
    </script>
    @yield('custom-js')
</body>
<!-- END: Body-->

</html>
