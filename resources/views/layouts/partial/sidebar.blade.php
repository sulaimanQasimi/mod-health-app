<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="{{ route('home') }}" class="app-brand-link">
            <div class="d-flex">
                <span class="app-brand-logo demo">
                    <svg class="w-[45px] h-[45px] text-gray-800 dark:text-white" aria-hidden="true"
                        xmlns="http://www.w3.org/2000/svg" fill="red" viewBox="0 0 24 24">
                        <path d="M15 2.013H9V9H2v6h7v6.987h6V15h7V9h-7z"></path>
                    </svg>
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
        @can('show-information-menu')
            <li
                class="menu-item {{ Route::is('patients.*') || Route::is('scanCode') || Route::is('appointments.index') ? 'active open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons bx bx-info-circle text-primary"></i>
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
        @endcan

        @can('show-my-visits-menu')
            <li
                class="menu-item {{ Route::is('appointments.doctorAppointments') || Route::is('appointments.completedAppointments') ? 'active open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons bx bx-time-five text-primary"></i>
                    <div>{{ localize('global.my_appointments') }}</div>
                </a>

                <ul class="menu-sub">
                    <li class="menu-item {{ Route::is('appointments.doctorAppointments') ? 'active' : '' }}">
                        <a href="{{ route('appointments.doctorAppointments') }}" class="menu-link">
                            <div>{{ localize('global.ongoing_appointments') }}</div>
                        </a>
                    </li>
                    <li class="menu-item {{ Route::is('appointments.completedAppointments') ? 'active' : '' }}">
                        <a href="{{ route('appointments.completedAppointments') }}" class="menu-link">
                            <div>{{ localize('global.completed_appointments') }}</div>
                        </a>
                    </li>
                </ul>
            </li>
        @endcan

        @can('show-my-consultations-menu')
            <li class="menu-item {{ Route::is('consultations.index') ? 'active' : '' }}">
                <a href="{{ route('consultations.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-chat text-primary"></i>
                    <div>{{ localize('global.my_consultations') }}</div>
                </a>
            </li>
        @endcan
        @can('show-prescriptions-menu')
            <li class="menu-item {{ Route::is('prescriptions.*') ? 'active open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons bx bx-receipt text-primary"></i>
                    <div>{{ localize('global.prescriptions') }}</div>
                </a>

                <ul class="menu-sub">
                    <li class="menu-item {{ Route::is('prescriptions.scanCode') ? 'active' : '' }}">
                        <a href="{{ route('prescriptions.scanCode') }}" class="menu-link">
                            <div>{{ localize('global.scan_prescription') }}</div>
                        </a>
                    </li>
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
        @endcan
        @can('show-under-review-menu')
            <li class="menu-item {{ Route::is('under_reviews.index') ? 'active' : '' }}">
                <a href="{{ route('under_reviews.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-revision text-primary"></i>
                    <div>{{ localize('global.under_review_patients') }}</div>
                </a>
            </li>
        @endcan

        @can('show-hospitalizations-menu')
        <li class="menu-item {{ Route::is('hospitalizations.*') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-bed text-primary"></i>
                <div>{{ localize('global.hospitalizations') }}</div>
            </a>

            <ul class="menu-sub">
                <li class="menu-item {{ Route::is('hospitalizations.index') ? 'active' : '' }}">
                    <a href="{{ route('hospitalizations.index') }}" class="menu-link">
                        <div>{{ localize('global.under_hospitalizations') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ Route::is('hospitalizations.discharged') ? 'active' : '' }}">
                    <a href="{{ route('hospitalizations.discharged') }}" class="menu-link">
                        <div>{{ localize('global.discharged_hospitalizations') }}</div>
                    </a>
                </li>
            </ul>
        </li>
        @endcan

        @can('show-labs-menu')
            <li class="menu-item {{ Route::is('lab_tests.index') ? 'active' : '' }}">
                <a href="{{ route('lab_tests.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-test-tube text-primary"></i>
                    <div>{{ localize('global.checkups') }}</div>
                </a>
            </li>
        @endcan

        @can('show-icu-menu')
        <li class="menu-item {{ Route::is('icus.*') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-tv text-primary"></i>
                <div>{{ localize('global.icus') }}</div>
            </a>

            <ul class="menu-sub">
                <li class="menu-item {{ Route::is('icus.new') ? 'active' : '' }}">
                    <a href="{{ route('icus.new') }}" class="menu-link">
                        <div>{{ localize('global.new_icus') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ Route::is('icus.approved') ? 'active' : '' }}">
                    <a href="{{ route('icus.approved') }}" class="menu-link">
                        <div>{{ localize('global.approved_icus') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ Route::is('icus.rejected') ? 'active' : '' }}">
                    <a href="{{ route('icus.rejected') }}" class="menu-link">
                        <div>{{ localize('global.rejected_icus') }}</div>
                    </a>
                </li>
            </ul>
        </li>
        @endcan
        @can('show-anesthesias-menu')
            <li class="menu-item {{ Route::is('anesthesias.*') ? 'active open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons bx bx-first-aid text-primary"></i>
                    <div>{{ localize('global.anesthesias') }}</div>
                </a>

                <ul class="menu-sub">
                    <li class="menu-item {{ Route::is('anesthesias.new') ? 'active' : '' }}">
                        <a href="{{ route('anesthesias.new') }}" class="menu-link">
                            <div>{{ localize('global.new_anesthesias') }}</div>
                        </a>
                    </li>
                    <li class="menu-item {{ Route::is('anesthesias.approved') ? 'active' : '' }}">
                        <a href="{{ route('anesthesias.approved') }}" class="menu-link">
                            <div>{{ localize('global.approved_anesthesias') }}</div>
                        </a>
                    </li>
                    <li class="menu-item {{ Route::is('anesthesias.rejected') ? 'active' : '' }}">
                        <a href="{{ route('anesthesias.rejected') }}" class="menu-link">
                            <div>{{ localize('global.rejected_anesthesias') }}</div>
                        </a>
                    </li>
                </ul>
            </li>
        @endcan

        @can('show-operations-menu')
            <li class="menu-item {{ Route::is('operations.*') ? 'active open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons bx bx-cut text-primary"></i>
                    <div>{{ localize('global.operations') }}</div>
                </a>

                <ul class="menu-sub">
                    <li class="menu-item {{ Route::is('operations.new') ? 'active' : '' }}">
                        <a href="{{ route('operations.new') }}" class="menu-link">
                            <div>{{ localize('global.new_operations') }}</div>
                        </a>
                    </li>
                    <li class="menu-item {{ Route::is('operations.approved') ? 'active' : '' }}">
                        <a href="{{ route('operations.approved') }}" class="menu-link">
                            <div>{{ localize('global.approved_operations') }}</div>
                        </a>
                    </li>
                    <li class="menu-item {{ Route::is('operations.completed') ? 'active' : '' }}">
                        <a href="{{ route('operations.completed') }}" class="menu-link">
                            <div>{{ localize('global.completed_operations') }}</div>
                        </a>
                    </li>
                </ul>
            </li>
        @endcan

        @can('show-reports-menu')
            <li class="menu-item {{ Route::is('reports.*') ? 'active open' : '' }}">
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
        @endcan

        @can('show-settings-menu')
            <li
                class="menu-item {{ Route::is('users.index') || Route::is('roles.index') || Route::is('permissions.index') || Route::is('document-type-columns.index') || Route::is('notices.index') || Route::is('sectors.index') || Route::is('recipients.index') || Route::is('hukums.index') ? 'active open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons bx bx-cog text-primary"></i>
                    <div>{{ localize('global.settings') }}</div>
                </a>

                <ul class="menu-sub">
                    @can('show-users-menu')
                    <li class="menu-item {{ Route::is('users.index') ? 'active' : '' }}">
                        <a href="{{ route('users.index') }}" class="menu-link">
                            <div>{{ localize('global.users') }}</div>
                        </a>
                    </li>
                    @endcan
                    @can('show-roles-menu')
                    <li class="menu-item {{ Route::is('roles.index') ? 'active' : '' }}">
                        <a href="{{ route('roles.index') }}" class="menu-link">
                            <div>{{ localize('global.roles') }}</div>
                        </a>
                    </li>
                    @endcan
                    @can('show-permissions-menu')
                    <li class="menu-item {{ Route::is('permissions.index') ? 'active' : '' }}">
                        <a href="{{ route('permissions.index') }}" class="menu-link">
                            <div>{{ localize('global.permissions') }}</div>
                        </a>
                    </li>
                    @endcan
                    @can('show-recipients-menu')

                    <li class="menu-item {{ Route::is('recipients.index') ? 'active' : '' }}">
                        <a href="{{ route('recipients.index') }}" class="menu-link">
                            <div>{{ localize('global.recipients') }}</div>
                        </a>
                    </li>
                    @endcan
                    @can('show-relations-menu')
                    <li class="menu-item {{ Route::is('relations.index') ? 'active' : '' }}">
                        <a href="{{ route('relations.index') }}" class="menu-link">
                            <div>{{ localize('global.relations') }}</div>
                        </a>
                    </li>
                    @endcan
                    @can('show-departments-menu')
                    <li class="menu-item {{ Route::is('departments.index') ? 'active' : '' }}">
                        <a href="{{ route('departments.index') }}" class="menu-link">
                            <div>{{ localize('global.departments') }}</div>
                        </a>
                    </li>
                    @endcan
                    @can('show-sections-menu')
                    <li class="menu-item {{ Route::is('sections.index') ? 'active' : '' }}">
                        <a href="{{ route('sections.index') }}" class="menu-link">
                            <div>{{ localize('global.sections') }}</div>
                        </a>
                    </li>
                    @endcan
                    @can('show-floors-menu')
                    <li class="menu-item {{ Route::is('floors.*') ? 'active' : '' }}">
                        <a href="{{ route('floors.index') }}" class="menu-link">
                            <div>{{ localize('global.floors') }}</div>
                        </a>
                    </li>
                    @endcan
                    @can('show-rooms-menu')
                    <li class="menu-item {{ Route::is('rooms.*') ? 'active' : '' }}">
                        <a href="{{ route('rooms.index') }}" class="menu-link">
                            <div>{{ localize('global.rooms') }}</div>
                        </a>
                    </li>
                    @endcan
                    @can('show-beds-menu')
                    <li class="menu-item {{ Route::is('beds.*') ? 'active' : '' }}">
                        <a href="{{ route('beds.index') }}" class="menu-link">
                            <div>{{ localize('global.beds') }}</div>
                        </a>
                    </li>
                    @endcan
                    @can('show-labs-types-menu')
                    <li class="menu-item {{ Route::is('lab_type_sections.*') ? 'active' : '' }}">
                        <a href="{{ route('lab_type_sections.index') }}" class="menu-link">
                            <div>{{ localize('global.lab_type_sections') }}</div>
                        </a>
                    </li>
                    @endcan
                    @can('show-test-types-menu')
                    <li class="menu-item {{ Route::is('lab_types.*') ? 'active' : '' }}">
                        <a href="{{ route('lab_types.index') }}" class="menu-link">
                            <div>{{ localize('global.lab_types') }}</div>
                        </a>
                    </li>
                    @endcan
                    @can('show-operation-types-menu')
                    <li class="menu-item {{ Route::is('operation_types.*') ? 'active' : '' }}">
                        <a href="{{ route('operation_types.index') }}" class="menu-link">
                            <div>{{ localize('global.operation_types') }}</div>
                        </a>
                    </li>
                    @endcan
                    @can('show-medicine-types-menu')
                    <li class="menu-item {{ Route::is('medicine_types.*') ? 'active' : '' }}">
                        <a href="{{ route('medicine_types.index') }}" class="menu-link">
                            <div>{{ localize('global.medicine_types') }}</div>
                        </a>
                    </li>
                    @endcan
                    @can('show-medicine-menu')
                    <li class="menu-item {{ Route::is('medicines.*') ? 'active' : '' }}">
                        <a href="{{ route('medicines.index') }}" class="menu-link">
                            <div>{{ localize('global.medicines') }}</div>
                        </a>
                    </li>
                    @endcan
                    @can('show-food-types-menu')
                    <li class="menu-item {{ Route::is('food_types.*') ? 'active' : '' }}">
                        <a href="{{ route('food_types.index') }}" class="menu-link">
                            <div>{{ localize('global.food_types') }}</div>
                        </a>
                    </li>
                    @endcan
                    @can('show-branches-menu')
                    <li class="menu-item {{ Route::is('branches.index') ? 'active' : '' }}">
                        <a href="{{ route('branches.index') }}" class="menu-link">
                            <div>{{ localize('global.branches') }}</div>
                        </a>
                    </li>
                    @endcan
                </ul>
            </li>
        @endcan
    </ul>
</aside>
