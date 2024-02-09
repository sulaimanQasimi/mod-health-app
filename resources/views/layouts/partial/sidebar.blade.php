<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="{{route('home')}}" class="app-brand-link">
            <span class="app-brand-logo demo">
                <svg class="w-[45px] h-[45px] text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M6 18H2V3h3v1a1 1 0 0 0 0 2h2.758l2-2H7V2h3v1.779c.546-.5 1.26-.777 2-.779h5a2 2 0 0 0-2-2h-3.278A1.992 1.992 0 0 0 10 0H7a1.993 1.993 0 0 0-1.722 1H2a2 2 0 0 0-2 2v15a2 2 0 0 0 2 2h4a.972.972 0 0 0 .474-.136A4.01 4.01 0 0 1 6 18Z"/>
                    <path d="M12 5a1 1 0 0 0-.707.293l-3 3A1 1 0 0 0 8 9h4V5Z"/>
                    <path d="M18.067 5H14v4a2 2 0 0 1-2 2H8v7a1.969 1.969 0 0 0 1.933 2h8.134A1.97 1.97 0 0 0 20 18V7a1.97 1.97 0 0 0-1.933-2Z"/>
                  </svg>
            </span>
            <span class="app-brand-text demo menu-text fw-bolder ms-2">{{ localize('global.system_name') }}</span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        <!-- Dashboards -->
        <li class="menu-item {{ Route::is('home') ? 'active' : '' }}">
            <a href="{{ route('home') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <div>{{ localize('global.dashboard') }}</div>
            </a>
        </li>

        <!-- Layouts -->

        <li
            class="menu-item {{ Route::is('users.index') || Route::is('roles.index') || Route::is('permissions.index') || Route::is('document-type-columns.index') || Route::is('notices.index') || Route::is('sectors.index') || Route::is('recipients.index') || Route::is('hukums.index') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-wrench"></i>
                <div>{{ localize('global.reception') }}</div>
            </a>

            <ul class="menu-sub">
                <li class="menu-item {{ Route::is('patients.create') ? 'active' : '' }}">
                    <a href="{{ route('patients.create') }}" class="menu-link">
                        <div>{{ localize('global.create_patient') }}</div>
                    </a>
                </li>


            </ul>
        </li>

        <li class="menu-item {{ Route::is('appointments.index') ? 'active' : '' }}">
            <a href="{{ route('appointments.index') }}" class="menu-link">
                <div>{{ localize('global.appointments') }}</div>
            </a>
        </li>

        <li
            class="menu-item {{ Route::is('users.index') || Route::is('roles.index') || Route::is('permissions.index') || Route::is('document-type-columns.index') || Route::is('notices.index') || Route::is('sectors.index') || Route::is('recipients.index') || Route::is('hukums.index') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-wrench"></i>
                <div>{{ localize('global.settings') }}</div>
            </a>

            <ul class="menu-sub">
                <li class="menu-item {{ Route::is('users.index') ? 'active' : '' }}">
                    <a href="{{ route('users.index') }}" class="menu-link">
                        <div>{{ localize('global.users') }}</div>
                    </a>
                </li>

                <li class="menu-item {{ Route::is('roles.index') ? 'active' : '' }}">
                    <a href="{{ route('roles.index') }}" class="menu-link">
                        <div>{{ localize('global.roles') }}</div>
                    </a>
                </li>


                <li class="menu-item {{ Route::is('permissions.index') ? 'active' : '' }}">
                    <a href="{{ route('permissions.index') }}" class="menu-link">
                        <div>{{ localize('global.permissions') }}</div>
                    </a>
                </li>


                <li class="menu-item {{ Route::is('recipients.index') ? 'active' : '' }}">
                    <a href="{{ route('recipients.index') }}" class="menu-link">
                        <div>{{ localize('global.recipients') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ Route::is('doctors.index') ? 'active' : '' }}">
                    <a href="{{ route('doctors.index') }}" class="menu-link">
                        <div>{{ localize('global.doctors') }}</div>
                    </a>
                </li>

                <li class="menu-item {{ Route::is('departments.index') ? 'active' : '' }}">
                    <a href="{{ route('departments.index') }}" class="menu-link">
                        <div>{{ localize('global.departments') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ Route::is('sections.index') ? 'active' : '' }}">
                    <a href="{{ route('sections.index') }}" class="menu-link">
                        <div>{{ localize('global.sections') }}</div>
                    </a>
                </li>

            </ul>
        </li>
    </ul>
</aside>
