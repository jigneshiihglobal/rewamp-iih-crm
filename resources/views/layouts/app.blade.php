<!DOCTYPE html>
<html class="loading" data-textdirection="ltr" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<!-- BEGIN: Head-->

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=0,minimal-ui">
    <title>{{ config('app.name') }}</title>
    @workspace('shalin-designs')
        <link rel="shortcut icon" type="image/x-icon" href="{{ asset('shalin-designs/img/ico/favicon.ico') }}">
    @else
        <link rel="shortcut icon" type="image/x-icon" href="{{ asset('app-assets/images/ico/favicon.ico') }}">
    @endworkspace
    <!-- BEGIN: Vendor CSS-->
    <link rel="stylesheet" type="text/css"
        href="{{ asset('app-assets/vendors/css/vendors.min.css?v=' . config('versions.css')) }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('app-assets/vendors/css/extensions/toastr.min.css?v=' . config('versions.css')) }}">
    <link rel="stylesheet"
        href="{{ asset('app-assets/vendors/css/forms/select/select2.min.css?v=' . config('versions.css')) }}">
    <link rel="stylesheet"
        href="{{ asset('app-assets/vendors/css/tables/datatable/dataTables.bootstrap5.min.css?v=' . config('versions.css')) }}">
    <link rel="stylesheet"
        href="{{ asset('app-assets/vendors/css/tables/datatable/responsive.bootstrap5.min.css?v=' . config('versions.css')) }}">
    <link rel="stylesheet"
        href="{{ asset('app-assets/vendors/css/tables/datatable/buttons.bootstrap5.min.css?v=' . config('versions.css')) }}">
    <link rel="stylesheet"
        href="{{ asset('app-assets/vendors/css/tables/datatable/rowGroup.bootstrap5.min.css?v=' . config('versions.css')) }}">
    <link rel="stylesheet"
        href="{{ asset('app-assets/vendors/css/extensions/sweetalert2.min.css?v=' . config('versions.css')) }}">
    @yield('vendor-css')
    <!-- END: Vendor CSS-->

    <!-- BEGIN: Theme CSS-->
    <link rel="stylesheet" type="text/css"
        href="{{ asset('app-assets/css/bootstrap.css?v=' . config('versions.css')) }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('app-assets/css/bootstrap-extended.css?v=' . config('versions.css')) }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('app-assets/css/colors.css?v=' . config('versions.css')) }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('app-assets/css/components.css?v=' . config('versions.css')) }}">
    {{-- <link rel="stylesheet" type="text/css" href="{{ asset('app-assets/css/themes/dark-layout.css?v='.config('versions.css')) }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('app-assets/css/themes/bordered-layout.css?v='.config('versions.css')) }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('app-assets/css/themes/semi-dark-layout.css?v='.config('versions.css')) }}"> --}}

    <!-- BEGIN: Page CSS-->
    <link rel="stylesheet" type="text/css"
        href="{{ asset('app-assets/css/core/menu/menu-types/vertical-menu.css?v=' . config('versions.css')) }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('app-assets/css/plugins/extensions/ext-component-toastr.css?v=' . config('versions.css')) }}">
    <link rel="stylesheet"
        href="{{ asset('app-assets/css/plugins/forms/form-validation.css?v=' . config('versions.css')) }}">
    @yield('page-css')
    <!-- END: Page CSS-->

    <link rel="stylesheet" type="text/css" href="{{ asset('app-assets/css/style.css?v=' . config('versions.css')) }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('app-assets/css/custom/custom-colors.css?v=' . config('versions.css')) }}">

    <!-- BEGIN: Custom CSS-->
    @yield('custom-css')
    @workspace('shalin-designs')
        <link rel="stylesheet" type="text/css"
            href="{{ asset('shalin-designs/css/style.css?v=' . config('versions.css')) }}">
    @endworkspace
    <!-- END: Custom CSS-->
    @yield('head-js')

</head>
<!-- END: Head-->

<!-- BEGIN: Body-->

<body class="vertical-layout vertical-menu-modern  navbar-floating footer-static  " data-open="click"
    data-menu="vertical-menu-modern" data-col="">

    @include('partials.header')

    @include('partials.menu')
    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper">
            <div class="content-header row">
            </div>
            <div class="content-body">
                @yield('content')
            </div>
        </div>
    </div>
    <!-- END: Content-->

    <div class="sidenav-overlay"></div>
    <div class="drag-target"></div>

    @include('partials.footer')

    @routes
    <!-- BEGIN: Vendor JS-->
    <script src="{{ asset('app-assets/vendors/js/vendors.min.js?v=' . config('versions.js')) }}"></script>
    <!-- BEGIN Vendor JS-->

    <!-- BEGIN: Page Vendor JS-->
    @yield('page-vendor-js')
    <script src="{{ asset('app-assets/vendors/js/extensions/toastr.min.js?v=' . config('versions.js')) }}"></script>
    <script src="{{ asset('app-assets/vendors/js/extensions/sweetalert2.all.min.js?v=' . config('versions.js')) }}">
    </script>
    <script src="{{ asset('app-assets/vendors/js/forms/select/select2.full.min.js?v=' . config('versions.js')) }}">
    </script>
    <script
        src="{{ asset('app-assets/vendors/js/tables/datatable/jquery.dataTables.min.js?v=' . config('versions.js')) }}">
    </script>
    <script
        src="{{ asset('app-assets/vendors/js/tables/datatable/dataTables.bootstrap5.min.js?v=' . config('versions.js')) }}">
    </script>
    <script
        src="{{ asset('app-assets/vendors/js/tables/datatable/dataTables.responsive.min.js?v=' . config('versions.js')) }}">
    </script>
    <script
        src="{{ asset('app-assets/vendors/js/tables/datatable/responsive.bootstrap5.js?v=' . config('versions.js')) }}">
    </script>
    <script
        src="{{ asset('app-assets/vendors/js/tables/datatable/datatables.buttons.min.js?v=' . config('versions.js')) }}">
    </script>
    <script src="{{ asset('app-assets/vendors/js/tables/datatable/jszip.min.js?v=' . config('versions.js')) }}"></script>
    <script src="{{ asset('app-assets/vendors/js/tables/datatable/pdfmake.min.js?v=' . config('versions.js')) }}"></script>
    <script src="{{ asset('app-assets/vendors/js/tables/datatable/vfs_fonts.js?v=' . config('versions.js')) }}"></script>
    <script src="{{ asset('app-assets/vendors/js/tables/datatable/buttons.html5.min.js?v=' . config('versions.js')) }}">
    </script>
    <script src="{{ asset('app-assets/vendors/js/tables/datatable/buttons.print.min.js?v=' . config('versions.js')) }}">
    </script>
    <script
        src="{{ asset('app-assets/vendors/js/tables/datatable/dataTables.rowGroup.min.js?v=' . config('versions.js')) }}">
    </script>
    <script src="{{ asset('app-assets/vendors/js/forms/validation/jquery.validate.min.js?v=' . config('versions.js')) }}">
    </script>
    <!-- END: Page Vendor JS-->

    <!-- BEGIN: Theme JS-->
    <script src="{{ asset('app-assets/js/core/app-menu.js?v=' . config('versions.js')) }}"></script>
    <script src="{{ asset('app-assets/js/core/app.js?v=' . config('versions.js')) }}"></script>
    <!-- END: Theme JS-->
    <script src="{{ asset('app-assets/js/app.js?v=' . config('versions.js')) }}"></script>
    <!-- BEGIN: Page JS-->
    @yield('page-js')
    <!-- END: Page JS-->

    <script>
        $(window).on('load', function() {
            // toastr config
            localStorage.setItem('menuCollapsed', true);
            window.toastr = toastr;

            toastr.options.showDuration = 250;
            toastr.options.timeOut = 2000;
            toastr.options.closeDuration = 250;
            toastr.options.hideMethod = 'slideUp';
            toastr.options.showMethod = 'slideDown';
            // toastr config
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

            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });

            var csrf_token = () => $('meta[name="csrf-token"]').attr('content');

            $.validator.addMethod('filesizeMax', function(value, element, param) {
                var files = element.files;
                for (var i = 0; i < files.length; i++) {
                    if (files[i].size > param) {
                        return false;
                    }
                }
                return true;
            }, function(param, element) {
                return "The file size must be less than " + param / (1024 * 1024) + " MB";
            });


            $.validator.addMethod('extensionArr', function(value, element, param) {
                var files = element.files;
                var exts = param
                    .split(',')
                    .join('|')
                    .split('|');

                for (var i = 0; i < files.length; i++) {
                    var fileExt = files[i].name.split('.').pop().toLowerCase();
                    if ($.inArray(fileExt, exts) === -1) {
                        return false;
                    }
                }
                return true;
            }, 'Supported file types are: {0}.');

            $.validator.addMethod('maxFiles', function(value, element, params) {
                return this.optional(element) || element.files.length <= params;
            }, 'You can only upload up to {0} files.');

            $.validator.addMethod('inArray', function(value, element, params) {
                return Array.isArray(params) &&
                    params.length > 0 &&
                    params.find(param => param.toString().toLowerCase() === value.toString()
                        .toLowerCase());
            });

            $.validator.addMethod("validEmails", function(value, element) {
                for (var i = 0; i < value.length; i++) {
                    var email = $.trim(value[i]);
                    if (email !== "" && !/^[\w-]+(\.[\w-]+)*@([\w-]+\.)+[a-zA-Z]{2,}$/.test(email)) {
                        return false;
                    }
                }
                return true;
            }, "Please enter valid email addresses");

            $.validator.addMethod("validPhones", function(value, element) {
                for (var i = 0; i < value.length; i++) {
                    var phones = $.trim(value[i]);
                    if (phones !== "" && !/^(\+)?([0-9]+(\s)?)+$/.test(phones)) {
                        return false;
                    }
                }
                return true;
            }, "Please enter valid phone numbers");

            $.validator.addMethod("validIps", function(value, element) {
                let pattern = /^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/;
                for (var i = 0; i < value.length; i++) {
                    var ip = $.trim(value[i]);
                    if (ip !== "" && !pattern.test(ip)) {
                        return false;
                    }
                }
                return true;
            }, "Please enter valid IP addresses");

            // $(document).on('select2:open', '.select2', function() {
            //     $(this).find('.select2-search__field').focus();
            // });

            // $(document).on('focus', '.select2-container', function(e) {
            //     let $select2 = $(this).siblings('select.select2');
            //     $select2.select2('open');
            //     $select2.find('.select2-search__field').trigger('input');
            // });

            @if (!Route::is('invoices.dashboard'))
            $(document).on('focus', '.select2-selection.select2-selection--single', function(e) {
                $(this).closest(".select2-container").siblings('select:enabled').select2('open');
            });
            @endif

            $('select.select2').on('select2:closing', function(e) {
                $(e.target).data("select2").$selection.one('focus focusin', function(e) {
                    e.stopPropagation();
                });
            });

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

    @yield('custom-js')
</body>
<!-- END: Body-->

</html>
