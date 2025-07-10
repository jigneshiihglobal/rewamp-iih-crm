<!-- BEGIN: Header-->
{{-- <nav class="header-navbar navbar navbar-expand-lg align-items-center floating-nav navbar-light navbar-shadow container-xxl"> --}}
<nav class="header-navbar navbar navbar-expand-lg align-items-center floating-nav navbar-light navbar-shadow">
    <div class="navbar-container d-flex content">
        <div class="bookmark-wrapper d-flex align-items-center">
            <ul class="nav navbar-nav d-xl-none">
                <li class="nav-item"><a class="nav-link menu-toggle" href="#"><i class="ficon"
                            data-feather="menu"></i></a></li>
            </ul>
        </div>
        @auth
            <ul class="nav navbar-nav align-items-center ms-auto">
                {{-- <li class="nav-item dropdown dropdown-notification me-25">
                    <a class="nav-link" href="#" data-bs-toggle="dropdown">
                        <i class="ficon" data-feather="bell"></i>
                        @if ($unread_notification_count)
                            <span class="badge rounded-pill bg-danger badge-up notification_count_badge">{{ $unread_notification_count }}</span>
                        @endif
                    </a>
                    <ul class="dropdown-menu dropdown-menu-media dropdown-menu-end">
                        <li class="dropdown-menu-header">
                            <div class="dropdown-header d-flex">
                                <h4 class="notification-title mb-0 me-auto">Notifications</h4>
                                @if ($unread_notification_count)
                                    <div class="badge rounded-pill badge-light-primary">
                                        {{ $unread_notification_count }} New
                                    </div>
                                @endif
                            </div>
                        </li>
                        @if ($notification_count)
                            <li class="scrollable-container media-list" id="notification_list">
                                @foreach ($notifications as $notification)
                                    @include('partials.notification')
                                @endforeach
                            </li>
                            @if ($notification_count > 10)
                                <li class="dropdown-menu-footer"><a class="btn btn-primary w-100" id="loadMoreNotificationBtn" >Show more</a></li>
                            @endif
                        @else
                            <li class="dropdown-menu-footer">
                                <p class="text-center">
                                    You have no notifications!
                                </p>
                            </li>
                        @endif
                    </ul>
                </li> --}}
                <li class="nav-item dropdown dropdown-user"><a class="nav-link dropdown-toggle dropdown-user-link"
                        id="dropdown-user" href="#" data-bs-toggle="dropdown" aria-haspopup="true"
                        aria-expanded="false">
                        <div class="user-nav d-sm-flex d-none">
                            <span class="user-name fw-bolder">{{ Auth::user()->full_name }}</span>
                            <span class="user-status">
                                @if (Auth::user()->hasRole('Superadmin'))
                                    Superadmin
                                @elseif (Auth::user()->hasRole('Admin'))
                                    Admin
                                @elseif (Auth::user()->hasRole('Marketing'))
                                    Marketing
                                @else
                                    User
                                @endif
                            </span>
                        </div>
                        <span class="avatar">
                            @if ($profilePic = Auth::user()->pic)
                                <img class="round" src="{{ url('storage/' . $profilePic) }}" alt="avatar" height="40"
                                    width="40" id="header_profile_pic">
                            @else
                                <img class="round" src="{{ asset('app-assets/images/svg/user.svg') }}" alt="avatar"
                                    height="40" width="40" id="header_profile_pic">
                            @endif
                            <span class="avatar-status-online"></span>
                        </span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdown-user">
                        @if (auth()->user()->workspaces->count() != 1 ||
                                auth()->user()->workspaces->first()->id != auth()->user()->workspace_id)
                            <div class="dropdown-header">
                                Go to Workspace
                            </div>
                            @foreach (auth()->user()->workspaces as $workspace)
                                @if ($workspace->id != Auth::user()->workspace_id)
                                    <a href="{{ route('workspaces.change', $workspace->encrypted_id) }}"
                                        class='dropdown-item active {{ $workspace->slug }}'>
                                        {{ $workspace->name }}
                                        <i class="me-50" data-feather="arrow-up-right"></i>
                                    </a>
                                @endif
                            @endforeach
                            <div class="dropdown-divider"></div>
                        @endif
                        @role('Admin|Superadmin')
                            @workspace('iih-global')
                                <a class="dropdown-item" href="{{ route('users.show', auth()->user()->encrypted_id) }}"><i
                                        class="me-50" data-feather="user"></i>
                                    Profile
                                </a>
                            @else
                                <a class="dropdown-item" href="{{ route('profile.index') }}"><i class="me-50"
                                        data-feather="user"></i>
                                    Profile
                                </a>
                            @endworkspace
                        @else
                            <a class="dropdown-item" href="{{ route('profile.index') }}"><i class="me-50"
                                    data-feather="user"></i>
                                Profile
                            </a>
                        @endrole
                        @workspace('iih-global')
                            <a class="dropdown-item" href="{{ route('activities.index', []) }}"><i class="me-50"
                                    data-feather="activity"></i>
                                Activities
                            </a>
                        @endworkspace
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="{{ route('logout') }}"
                            onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                            <i class="me-50" data-feather="power"></i>
                            {{ __('Logout') }}
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none"> @csrf
                        </form>
                    </div>
                </li>
            </ul>
        @endauth
    </div>
</nav>
<!-- END: Header-->
