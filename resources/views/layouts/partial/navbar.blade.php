<nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
     id="layout-navbar">
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
            <i class="bx bx-menu bx-sm"></i>
        </a>
    </div>

    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">

        <ul class="navbar-nav flex-row align-items-center ms-auto">
            <!-- Language -->
            <li class="nav-item dropdown-language dropdown me-2 me-xl-0">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <img src="{{ asset('assets/img/icons/language-icon.png') }}" style="width: 30px; height:30px;" />
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="{{ route('change_language', 'en') }}" id="en">

                            <span class="align-middle">English</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('change_language', 'dr') }}" id="dr">

                            <span class="align-middle">دری</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('change_language', 'ps') }}" id="ps">

                            <span class="align-middle">پشتو</span>
                        </a>
                    </li>
                </ul>
            </li>
            <!--/ Language -->

            <!-- Style Switcher -->
            <li class="nav-item me-2 me-xl-0">
                <a class="nav-link style-switcher-toggle hide-arrow" href="javascript:void(0);">
                    <i class="bx bx-sm"></i>
                </a>
            </li>
            <!--/ Style Switcher -->
            @if (auth()->check())
            <!-- Notification -->
            <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-3 me-xl-1">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown"
                   data-bs-auto-close="outside" aria-expanded="false">
                    <i class="bx bx-bell bx-sm"></i>
                    <span class="badge bg-primary rounded-2 badge-notifications"
                          style="font-size: small">{{ auth()->user()->unreadNotifications->count() }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end py-0">
                    <li class="dropdown-menu-header border-bottom">
                        <div class="dropdown-header d-flex align-items-center py-3">
                            <h5 class="text-body mb-0 me-auto">{{ localize('global.notifications') }}</h5>
                            @if (auth()->user()->unreadNotifications->count() > 0)
                                <i class="bx fs-4 bx-envelope" style="color: red;"></i>
                            @else
                                <i class="bx fs-4 bx-envelope-open" style="color: #696cff;"></i>
                            @endif
                        </div>

                    </li>

                    <li class="dropdown-notifications-list scrollable-container">
                        <ul class="list-group list-group-flush">
                            @forelse (auth()->user()->unreadNotifications as $notification)
                                <li class="list-group-item list-group-item-action dropdown-notifications-item">
                                    <a href="{{ route('notification.mark_as_read', $notification->id) }}">
                                        <div class="d-flex">
                                            <div class="flex-shrink-0 me-3">
                                                <div class="avatar">
                                                  <img src="{{\App\Models\User::find($notification->data['message'])->avatar != null ? asset('storage/' . \App\Models\User::find($notification->data['message'])->avatar) : asset('assets/img/avatars/1.png')}}" alt="" class="w-px-40 h-px-40 rounded-circle">
                                                </div>
                                              </div>
                                            <div class="flex-grow-1">
                                                <span
                                                      class="text-muted">{{ \App\Models\User::find($notification->data['message'])->name }}</span>
                                                <p class="mb-0">
                                                    {{ preg_replace('/^\d+/','', $notification->data['message']) }}
                                                </p>
                                                @php
                                                    $date = \Carbon\Carbon::parse($notification->created_at);
                                                    $time = $date->diffForHumans(\Carbon\Carbon::now());
                                                @endphp
                                                <small class="text-muted" dir="ltr">{{ $time }}</small>
                                            </div>
                                        </div>
                                    </a>
                                </li>

                            @empty
                                <li class="list-group-item d-flex justify-content-center display-6">
                                    <p class="text-center badge bg-warning">
                                        <i class='bx bx-bell-off'></i>
                                    </p>&nbsp;
                                    <p class="text-center badge bg-primary">

                                        {{ localize('global.no_new_notifications') }}
                                    </p>
                                </li>
                            @endforelse
                        </ul>
                    </li>
                    <li class="dropdown-menu-footer border-top">
                        <a href="{{route('mark_all_as_read')}}" class="dropdown-item d-flex justify-content-center p-3">{{localize('global.mark_all_as_read')}}</a>
                      </li>
                </ul>
            </li>
            <!--/ Notification -->
            <!-- User -->
            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                        <img src="{{ auth()->user()->avatar != null ? asset('storage/' . Auth::user()->avatar) : asset('assets/img/avatars/1.png') }}"
                             alt class="w-px-40 h-px-40 rounded-circle" />
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="#">
                            <div class="d-flex">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar avatar-online">
                                        <img src="{{ auth()->user()->avatar != null ? asset('storage/' . Auth::user()->avatar) : asset('assets/img/avatars/1.png') }}"
                                             alt class="w-px-40 h-px-40 rounded-circle" />
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <span class="fw-semibold d-block">{{ auth()->user()->name_dr }}</span>
                                    <small class="text-muted">{{ auth()->user()->email }}</small>
                                </div>
                            </div>
                        </a>
                    </li>
                    <li>
                        <div class="dropdown-divider"></div>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('users.profile') }}">
                            <i class="bx bx-user me-2"></i>
                            <span class="align-middle">{{ localize('global.my_profile') }}</span>
                        </a>
                    </li>
                    {{-- <li>
                        <a class="dropdown-item" href="{{route('users.account', Auth::user()->id)}}">
                            <i class="bx bx-cog me-2"></i>
                            <span class="align-middle">{{localize('global.my_account')}}</span>
                        </a>
                    </li> --}}

                    <li>
                        <div class="dropdown-divider"></div>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('logout') }}"
                           onclick="event.preventDefault();
                                      document.getElementById('logout-form').submit();">
                            <i class="bx bx-log-out me-2"></i>
                            <span class="align-middle">{{ localize('global.logout') }}</span>
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </li>
                </ul>
            </li>
            @endif
            <!--/ User -->
        </ul>
    </div>
</nav>
