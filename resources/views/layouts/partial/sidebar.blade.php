<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="{{route('home')}}" class="app-brand-link">
            <div class="d-flex">
            <span class="app-brand-logo demo">
                  <svg class="w-[45px] h-[45px] text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="red" viewBox="0 0 24 24"><path d="M15 2.013H9V9H2v6h7v6.987h6V15h7V9h-7z"></path></svg>
            </span>
            <span class="app-brand-text demo menu-text fw-bolder ms-2">{{ localize('global.system_name') }}</span>
        </div>
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
                <i class="menu-icon tf-icons bx bx-home text-primary"></i>
                <div>{{ localize('global.dashboard') }}</div>
            </a>
        </li>

        <!-- Layouts -->

        <li
            class="menu-item {{ Route::is('patients.*') || Route::is('scanCode') || Route::is('appointments.index') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-info-circle text-info"></i>
                <div>{{ localize('global.reception') }}</div>
            </a>

            <ul class="menu-sub">
                <li class="menu-item {{ Route::is('scanCode') ? 'active' : '' }}">
                    <a href="{{ route('scanCode') }}" class="menu-link">
                        <div>{{ localize('global.scan_qrcode') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ Route::is('patients.create') ? 'active' : '' }}">
                    <a href="{{ route('patients.create') }}" class="menu-link">
                        <div>{{ localize('global.create_patient') }}</div>
                    </a>
                </li>

                <li class="menu-item {{ Route::is('patients.index') ? 'active' : '' }}">
                    <a href="{{ route('patients.index') }}" class="menu-link">
                        <div>{{ localize('global.patients_list') }}</div>
                    </a>
                </li>

                <li class="menu-item {{ Route::is('appointments.index') ? 'active' : '' }}">
                    <a href="{{ route('appointments.index') }}" class="menu-link">
                        <div>{{ localize('global.all_appointments') }}</div>
                    </a>
                </li>
            </ul>
        </li>

        <li class="menu-item {{ Route::is('appointments.doctorAppointments') ? 'active' : '' }}">
            <a href="{{ route('appointments.doctorAppointments') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-time-five text-primary"></i>
                <div>{{ localize('global.my_appointments') }}</div>
            </a>
        </li>
{{--
        <li class="menu-item {{ Route::is('diagnoses.index') ? 'active' : '' }}">
            <a href="{{ route('diagnoses.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-popsicle"></i>
                <div>{{ localize('global.diagnoses') }}</div>
            </a>
        </li> --}}
        <li class="menu-item {{ Route::is('consultations.index') ? 'active' : '' }}">
            <a href="{{ route('consultations.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-chat text-primary"></i>
                <div>{{ localize('global.my_consultations') }}</div>
            </a>
        </li>

        <li
            class="menu-item {{ Route::is('prescriptions.*') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-receipt text-primary"></i>
                <div>{{ localize('global.prescriptions') }}</div>
            </a>

            <ul class="menu-sub">
                <li class="menu-item {{ Route::is('prescriptions.index') ? 'active' : '' }}">
                    <a href="{{ route('prescriptions.index') }}" class="menu-link">
                        <div>{{ localize('global.undelivered_prescriptions') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ Route::is('prescriptions.delivered') ? 'active' : '' }}">
                    <a href="{{ route('prescriptions.delivered') }}" class="menu-link">
                        <div>{{ localize('global.delivered_prescriptions') }}</div>
                    </a>
                </li>
            </ul>
        </li>

        <li class="menu-item {{ Route::is('visits.index') ? 'active' : '' }}">
            <a href="{{ route('visits.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-bed text-primary"></i>
                <div>{{ localize('global.hospitalized_patients') }}</div>
            </a>
        </li>

        <li class="menu-item {{ Route::is('lab_tests.index') ? 'active' : '' }}">
            <a href="{{ route('lab_tests.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-test-tube text-primary"></i>
                <div>{{ localize('global.lab_tests') }}</div>
            </a>
        </li>
        <li class="menu-item {{ Route::is('lab_tests.index') ? 'active' : '' }}">
            <a href="{{ route('lab_tests.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-first-aid text-danger"></i>
                <div>{{ localize('global.anastasia') }}</div>
            </a>
        </li>
        <li class="menu-item {{ Route::is('lab_tests.index') ? 'active' : '' }}">
            <a href="{{ route('lab_tests.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-cut text-danger"></i>
                <div>{{ localize('global.operations') }}</div>
            </a>
        </li>
        <li
        class="menu-item {{ Route::is('reports.*') ? 'active open' : '' }}">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon tf-icons bx bx-line-chart text-primary"></i>
            <div>{{ localize('global.reports') }}</div>
        </a>

        <ul class="menu-sub">
            <li class="menu-item {{ Route::is('reports.index') ? 'active' : '' }}">
                <a href="#" class="menu-link">
                    <div>{{ localize('global.reports') }}</div>
                </a>
            </li>
            <li class="menu-item {{ Route::is('reports.index') ? 'active' : '' }}">
                <a href="#" class="menu-link">
                    <div>{{ localize('global.reports') }}</div>
                </a>
            </li>

            <li class="menu-item {{ Route::is('reports.index') ? 'active' : '' }}">
                <a href="#" class="menu-link">
                    <div>{{ localize('global.reports') }}</div>
                </a>
            </li>

            <li class="menu-item {{ Route::is('reports.index') ? 'active' : '' }}">
                <a href="#" class="menu-link">
                    <div>{{ localize('global.reports') }}</div>
                </a>
            </li>
        </ul>
    </li>



        <li
            class="menu-item {{ Route::is('users.index') || Route::is('roles.index') || Route::is('permissions.index') || Route::is('document-type-columns.index') || Route::is('notices.index') || Route::is('sectors.index') || Route::is('recipients.index') || Route::is('hukums.index') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-cog text-primary"></i>
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
                <li class="menu-item {{ Route::is('relations.index') ? 'active' : '' }}">
                    <a href="{{ route('relations.index') }}" class="menu-link">
                        <div>{{ localize('global.relations') }}</div>
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
                <li class="menu-item {{ Route::is('floors.index') ? 'active' : '' }}">
                    <a href="{{ route('floors.index') }}" class="menu-link">
                        <div>{{ localize('global.floors') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ Route::is('rooms.index') ? 'active' : '' }}">
                    <a href="{{ route('rooms.index') }}" class="menu-link">
                        <div>{{ localize('global.rooms') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ Route::is('beds.index') ? 'active' : '' }}">
                    <a href="{{ route('beds.index') }}" class="menu-link">
                        <div>{{ localize('global.beds') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ Route::is('lab_type_sections.index') ? 'active' : '' }}">
                    <a href="{{ route('lab_type_sections.index') }}" class="menu-link">
                        <div>{{ localize('global.lab_type_sections') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ Route::is('lab_types.index') ? 'active' : '' }}">
                    <a href="{{ route('lab_types.index') }}" class="menu-link">
                        <div>{{ localize('global.lab_types') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ Route::is('operation_types.index') ? 'active' : '' }}">
                    <a href="{{ route('operation_types.index') }}" class="menu-link">
                        <div>{{ localize('global.operation_types') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ Route::is('branches.index') ? 'active' : '' }}">
                    <a href="{{ route('branches.index') }}" class="menu-link">
                        <div>{{ localize('global.branches') }}</div>
                    </a>
                </li>

            </ul>
        </li>
    </ul>
</aside>
