<!-- BEGIN: Main Menu-->
<div class="main-menu menu-fixed menu-light menu-accordion menu-shadow" data-scroll-to-active="true">
    <div class="navbar-header">
        <ul class="nav navbar-nav flex-row">
            <li class="nav-item me-auto">
                <a class="navbar-brand" href="{{ route('leads.index', []) }}">
                    @workspace('shalin-designs')
                        <span class="brand-logo">
                            <img src="{{ asset('shalin-designs/img/icon-logo.png') }}">
                        </span>
                        <h2 class="brand-text">SHALIN DESIGNS</h2>
                    @else
                        <span class="brand-logo">
                            <img src="{{ asset('app-assets/images/logo/icon-logo.svg') }}">
                        </span>
                        <h2 class="brand-text">{{ config('app.name', 'IIH CRM') }}</h2>
                    @endworkspace
                </a>
            </li>
            <li class="nav-item nav-toggle d-none"><a class="nav-link modern-nav-toggle pe-0"
                    data-bs-toggle="collapse"><i class="d-block d-xl-none text-primary toggle-icon font-medium-4"
                        data-feather="x"></i><i
                        class="d-none d-xl-block collapse-toggle-icon font-medium-4  text-primary" data-feather="disc"
                        data-ticon="disc"></i></a></li>
        </ul>
    </div>
    <div class="shadow-bottom"></div>
    <div class="main-menu-content mt-2">
        <ul class="navigation navigation-main" id="main-menu-navigation" data-menu="menu-navigation">

            @workspace('iih-global|shalin-designs')
            @if(Auth::user()->hasRole(['Admin','Superadmin','Marketing','User']) && Auth::user()->active_workspace->slug === 'iih-global')
                <li @class([
                        'nav-item',
                        'active' => Route::currentRouteName() == 'dashboard',
                    ])>
                    <a class="d-flex align-items-center" href="{{ route('dashboard', []) }}"><i
                            data-feather="home"></i><span class="menu-title text-truncate"
                                                          data-i18n="Dashboard">Dashboard</span></a>
                </li>
            @elseif(Auth::user()->hasRole(['Admin','Superadmin','Marketing']) && Auth::user()->active_workspace->slug === 'shalin-designs')
                <li @class([
                        'nav-item',
                        'active' => Route::currentRouteName() == 'dashboard',
                    ])>
                    <a class="d-flex align-items-center" href="{{ route('dashboard', []) }}"><i
                            data-feather="home"></i><span class="menu-title text-truncate"
                                                          data-i18n="Dashboard">Dashboard</span></a>
                </li>
            @endif
               {{-- @role('Admin|Superadmin|Marketing|User')
                    <li @class([
                        'nav-item',
                        'active' => Route::currentRouteName() == 'dashboard',
                    ])>
                        <a class="d-flex align-items-center" href="{{ route('dashboard', []) }}"><i
                                data-feather="home"></i><span class="menu-title text-truncate"
                                data-i18n="Dashboard">Dashboard</span></a>
                    </li>
                @endrole--}}
            @endworkspace

            @workspace('iih-global|shalin-designs')
                <li @class([
                    'nav-item',
                    'active' => Route::currentRouteName() == 'leads.index',
                ])>
                    <a class="d-flex align-items-center" href="{{ route('leads.index', []) }}"><i
                            data-feather="filter"></i><span class="menu-title text-truncate"
                            data-i18n="Leads">Leads</span></a>
                </li>
            @endworkspace

            @workspace('iih-global|shalin-designs')
                @unlessrole('Superadmin|Marketing')
                <li @class(['nav-item', 'active' => Route::is('follow_ups.*')])>
                    <a class="d-flex align-items-center" href="{{ route('follow_ups.index') }}"><i
                        data-feather="chevrons-up"></i><span class="menu-title text-truncate"
                        data-i18n="Follow ups">Follow
                        ups</span>
                    </a>
                </li>
                @endunlessrole
            @endworkspace

            @workspace('iih-global')
                @role('User')
                    @if(Auth::user()->is_invoice_access == '1')
                        <li @class([
                                'nav-item',
                                'active' => request()->routeIs('sales_invoices.*'),
                            ])>
                            <a class="d-flex align-items-center" href="{{ route('sales_invoices.index') }}">
                                <i data-feather="clipboard" class="credit_card_side_menu"></i>{{-- or try "file", "edit-3", or "clipboard" --}}
                                <span class="menu-title text-truncate" data-i18n="Sales Invoice">Sales Invoice</span>
                            </a>
                        </li>
                    @endif
                @endrole
            @endworkspace

            @workspace('iih-global|shalin-designs')                
                @unlessrole('Admin|Superadmin')
                    <li @class([
                        'nav-item',
                        'active' => Route::currentRouteName() == 'profile.index',
                    ])>
                        <a class="d-flex align-items-center" href="{{ route('profile.index', []) }}"><i
                                data-feather="user"></i><span class="menu-title text-truncate"
                                data-i18n="Profile">Profile</span></a>
                    </li>
                @endunlessrole
            @elseworkspace('shalin-designs')
                <li @class([
                    'nav-item',
                    'active' => Route::currentRouteName() == 'profile.index',
                ])>
                    <a class="d-flex align-items-center" href="{{ route('profile.index', []) }}"><i
                            data-feather="user"></i><span class="menu-title text-truncate"
                            data-i18n="Profile">Profile</span></a>
                </li>
            @endworkspace

            @workspace('iih-global')
                @role('Admin|Superadmin')
                    <li @class(['nav-item', 'active' => Route::is('users.*')])>
                        <a class="d-flex align-items-center" href="{{ route('users.index') }}"><i data-feather="users"></i><span
                                class="menu-title text-truncate" data-i18n="Users">Users</span></a>
                    </li>
                @endrole
            @endworkspace
            @workspace('iih-global|shalin-designs')
                @role('Superadmin')
                    <li @class(['nav-item', 'active' => Route::is('clients.*')])>
                        <a class="d-flex align-items-center" href="{{ route('clients.index') }}"><i
                                data-feather="user-check"></i><span class="menu-title text-truncate"
                                data-i18n="Customers">Customers</span></a>
                    </li>
                    <li @class([
                        'nav-item',
                        'nav-item-invoices-menu',
                        'active' =>
                            Route::is('credit_notes.*') ||
                            (Route::is('invoices.*') && !Route::is('invoices.dashboard')),
                    ])>
                        <a class="d-flex align-items-center" href="{{ route('invoices.index') }}"><i
                                data-feather="file-text"></i><span class="menu-title text-truncate"
                                data-i18n="Invoices">Invoices</span></a>
                    </li>
                    {{-- <li @class(['nav-item'])>
                    <a class="d-flex align-items-center" href="#">
                        <i data-feather="settings"></i>
                        <span class="menu-title text-truncate" data-i18n="Settings">Settings</span></a>
                    <ul class="menu-content">

                        <li @class([
                            'active' => Route::is('lead_statuses.*'),
                        ])>
                            <a class="d-flex align-items-center" href="{{ route('lead_statuses.index') }}"><i
                                    data-feather="list"></i><span class="menu-title text-truncate"
                                    data-i18n="Lead Statuses">Lead Statuses</span></a>
                        </li>
                        <li @class([
                            'active' => Route::is('lead_sources.*'),
                        ])>
                            <a class="d-flex align-items-center" href="{{ route('lead_sources.index') }}"><i
                                    data-feather="help-circle"></i><span class="menu-title text-truncate"
                                    data-i18n="Lead Sources">Lead Sources</span></a>
                        </li>
                    </ul>
                </li> --}}
                @endrole
            @endworkspace

            @workspace('iih-global')
                @role('Superadmin')
                    <li @class(['nav-item', 'active' => Route::is(
                            'sales_invoice_index',
                            'sales_invoice_show',
                            'sales_invoice_create',
                            'sales_invoice_store_one_off',
                            'sales_invoice_store_sub',
                            'sales_invoice_destroy'
                        )])>
                        <a class="d-flex align-items-center" href="{{ route('sales_invoice_index') }}">
                            <div class="d-flex align-items-center icon-badge-wrapper">
                                @if(@$pending_invoice > 0 && !Route::is('sales_invoice_index'))
                                    <span class="badge">{{ $pending_invoice }}</span>
                                @endif
                                <i data-feather="clipboard" class="credit_card_side_menu"></i>
                                <span class="menu-title text-truncate" data-i18n="Sales Invoice">
                                    Sales Invoice
                                </span>
                            </div>
                        </a>
                    </li>
                @endrole
            @endworkspace

            @workspace('iih-global|shalin-designs')
                @role('Superadmin')
                    <li @class([
                        'nav-item',
                        'active' => Route::currentRouteName() == 'invoices.dashboard',
                    ])>
                        <a class="d-flex align-items-center" href="{{ route('invoices.dashboard', []) }}"><i
                                data-feather="bar-chart-2"></i><span class="menu-title text-truncate"
                                data-i18n="Sales Report">Sales Report</span></a>
                    </li>
                    <li @class(['nav-item', 'active' => Route::is('payment_detail_index')])>
                        <a class="d-flex align-items-center" href="{{ route('payment_detail_index') }}">
                            <div class="d-flex align-items-center icon-badge-wrapper">
                                    @if(@$not_linked > 0 && !Route::is('payment_detail_index'))
                                        <span class="badge">{{ $not_linked }}</span>
                                    @endif
                                    <i data-feather="credit-card" class="credit_card_side_menu"></i>
                                    <span class="menu-title text-truncate" data-i18n="Payment detail">
                                        Payment Received
                                    </span>
                            </div>
                        </a>
                    </li>
                @endrole
            @endworkspace

            @workspace('iih-global|shalin-designs')
                @role('Superadmin')
            <li @class(['nav-item', 'active' => Route::is('expenses.*')])>
                <a class="d-flex align-items-center" href="#">
                    <i data-feather="dollar-sign"></i>
                    <span class="menu-title text-truncate" data-i18n="System Settings">Expenses</span>
                </a>
                <ul>
                    <li class="{{ Route::is('marketing.*') ? 'active' : '' }}">
                        <a class="d-flex align-items-center" style="padding-left:35px" href="{{ route('marketing.expenses.index') }}">
                            <i data-feather="circle"></i>
                            <span class="menu-title text-truncate" data-i18n="Client Expenses">Marketing expenses</span>
                        </a>
                    </li>
                    <li class="{{ Route::is('expenses.*') ? 'active' : '' }}">
                        <a class="d-flex align-items-center" style="padding-left:35px" href="{{ route('expenses.index') }}">
                            <i data-feather="circle"></i>
                            <span class="menu-title text-truncate" data-i18n="Client Expenses">Client expenses</span>
                        </a>
                    </li>
                </ul>
            </li>
                @endrole
            @endworkspace

             @workspace('iih-global')
                @role('Superadmin')
            <li @class([
                        'nav-item',
                        'active' => Route::currentRouteName() == 'settings',
                    ])>

            <li @class(['nav-item', 'active' => Route::is('lead_sources.*','lead_statuses.*','system-settings.*')])>
                <a class="d-flex align-items-center" href="{{ route('lead_sources.index', []) }}"><i
                        data-feather="settings"></i><span class="menu-title text-truncate"
                                                          data-i18n="System Settings">Settings</span></a>
                <ul>
                    <li class="{{ in_array(Route::currentRouteName(),['system-settings.edit']) ? 'active' : '' }}">
                        <a class="d-flex align-items-center" style="padding-left:35px" href="{{ route('system-settings.edit') }}" >
                            <i data-feather="circle"></i>
                            <span class="menu-title text-truncate" data-i18n="Lead Sources">System setting</span>
                        </a>
                    </li>
                    <li class="{{ Route::is('lead_sources.*') ? 'active' : '' }}">
                        <a class="d-flex align-items-center" style="padding-left:35px" href="{{ route('lead_sources.index') }}" >
                            <i data-feather="circle"></i>
                            <span class="menu-title text-truncate" data-i18n="Lead Sources">Lead Source</span>
                        </a>
                    </li>
                    <li class="{{ Route::is('lead_statuses.*') ? 'active' : '' }}">
                        <a class="d-flex align-items-center" style="padding-left:35px" href="{{ route('lead_statuses.index') }}" >
                            <i data-feather="circle"></i>
                            <span class="menu-title text-truncate" data-i18n="Lead Statuses">Lead Status</span>
                        </a>
                    </li>
                </ul>
            </li>
            <li @class(['nav-item', 'active' => Route::is('contacted-lead.index')])>
                <a class="d-flex align-items-center" href="{{ route('contacted-lead.index', []) }}">
                    <i data-feather="mail"></i>
                    <span class="menu-title text-truncate" data-i18n="Contacted Lead Mail">Marketing Email Logs</span>
                </a>
            </li>

                @endrole
            @endworkspace

            @workspace('iih-global')
                <li @class(['nav-item', 'active' => Route::is('activities.index')])>
                    <a class="d-flex align-items-center" href="{{ route('activities.index', []) }}"><i
                            data-feather="activity"></i><span class="menu-title text-truncate"
                            data-i18n="Activities">Activities</span></a>
                </li>
            @endworkspace
        </ul>
    </div>
</div>
<!-- END: Main Menu-->