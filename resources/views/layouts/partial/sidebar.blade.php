<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="{{ route('home') }}" class="app-brand-link">
            <div class="d-flex">
                <span class="app-brand-logo demo">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        style="fill: rgb(17, 170, 4);transform: ;msFilter:;">
                        <path
                            d="m22 3.41-.12-1.26-1.2.4a13.84 13.84 0 0 1-6.41.64 11.87 11.87 0 0 0-6.68.9A7.23 7.23 0 0 0 3.3 9.5a9 9 0 0 0 .39 4.58 16.6 16.6 0 0 1 1.18-2.2 9.85 9.85 0 0 1 4.07-3.43 11.16 11.16 0 0 1 5.06-1A12.08 12.08 0 0 0 9.34 9.2a9.48 9.48 0 0 0-1.86 1.53 11.38 11.38 0 0 0-1.39 1.91 16.39 16.39 0 0 0-1.57 4.54A26.42 26.42 0 0 0 4 22h2a30.69 30.69 0 0 1 .59-4.32 9.25 9.25 0 0 0 4.52 1.11 11 11 0 0 0 4.28-.87C23 14.67 22 3.86 22 3.41z">
                        </path>
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
                <i class="menu-icon tf-icons bx bx-home"></i>
                <div>{{ localize('global.dashboard') }}</div>
            </a>
        </li>

        <!-- Layouts -->
        @can('show-information-menu')
            <li
                class="menu-item {{ Route::is('patients.*') || Route::is('scanCode') || Route::is('appointments.index') || Route::is('appointments.department-report') || Route::is('doctor-performance-report.*') ? 'active open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons bx bx-info-circle"></i>
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
                    <li class="menu-item {{ Route::is('appointments.department-report') ? 'active' : '' }}">
                        <a href="{{ route('appointments.department-report') }}" class="menu-link">
                            <div>{{ localize('global.department_report') }}</div>
                        </a>
                    </li>
                    <li class="menu-item {{ Route::is('patients.report') ? 'active' : '' }}">
                        <a href="{{ route('patients.report') }}" class="menu-link">
                            <div>{{ localize('global.reports') }}</div>
                        </a>
                    </li>
                    <li class="menu-item {{ Route::is('doctor-performance-report.*') ? 'active' : '' }}">
                        <a href="{{ route('doctor-performance-report.performance') }}" class="menu-link">
                            <div>{{ localize('global.user_performance_report') }}</div>
                        </a>
                    </li>

                </ul>
            </li>
        @endcan

        @can('show-my-visits-menu')
            <li
                class="menu-item {{ Route::is('appointments.doctorAppointments') || Route::is('appointments.completedAppointments') || Route::is('appointments.departmentAppointments') ? 'active open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons bx bx-time-five"></i>
                    <div>{{ localize('global.my_appointments') }}</div>
                </a>

                <ul class="menu-sub">

                    <li class="menu-item {{ Route::is('appointments.departmentAppointments') ? 'active' : '' }}">
                        <a href="{{ route('appointments.departmentAppointments') }}" class="menu-link">
                            <div>{{ localize('global.department_appointments') }}</div>
                        </a>
                    </li>
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
                    <li class="menu-item {{ Route::is('appointments.report') ? 'active' : '' }}">
                        <a href="{{ route('appointments.report') }}" class="menu-link">
                            <div>{{ localize('global.reports') }}</div>
                        </a>
                    </li>
                </ul>
            </li>
        @endcan
        @can('show-physiotherapy-menu')
            <li class="menu-item {{ Route::is('physiotherapy-procedures.*') ? 'active open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons bx bx-heart"></i>
                    <div>{{ localize('global.physiotherapy') }}</div>
                </a>
                <ul class="menu-sub">
                    @can('show-physiotherapy-procedures')
                        <li class="menu-item {{ Route::is('physiotherapy-procedures.index') ? 'active' : '' }}">
                            <a href="{{ route('physiotherapy-procedures.index') }}" class="menu-link">
                                <div>{{ localize('global.all_procedures') }}</div>
                            </a>
                        </li>
                    @endcan

                    @can('show-own-physiotherapy-procedures')
                        <li class="menu-item {{ Route::is('physiotherapy-procedures.my-procedures') ? 'active' : '' }}">
                            <a href="{{ route('physiotherapy-procedures.my-procedures') }}" class="menu-link">
                                <div>{{ localize('global.my_procedures') }}</div>
                            </a>
                        </li>
                    @endcan

                    @can('show-physiotherapy-reports')
                        <li class="menu-item {{ Route::is('physiotherapy-reports.index') ? 'active' : '' }}">
                            <a href="{{ route('physiotherapy-reports.index') }}" class="menu-link">
                                <div>{{ localize('global.reports') }}</div>
                            </a>
                        </li>
                    @endcan

                    @can('show-physiotherapy-types')
                        <li class="menu-item {{ Route::is('physiotherapy-types.index') ? 'active' : '' }}">
                            <a href="{{ route('physiotherapy-types.index') }}" class="menu-link">
                                <div>{{ localize('global.physiotherapy_types') }}</div>
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcan

        <!-- Dentist Department Menu -->
        @can('access-dentist-registrations')
            <li class="menu-item {{ Route::is('dentist-registrations.*') ? 'active open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                        viewBox="0 0 24 24">
                        <path
                            d="M19.03 2.13a4.75 4.75 0 0 0-5.54 1.55l-.68.91c-.38.5-1.23.5-1.6 0l-.68-.91a4.72 4.72 0 0 0-5.54-1.55 4.7 4.7 0 0 0-2.97 4.39c0 1.72.21 3.44.63 5.12l2.04 8.18A2.88 2.88 0 0 0 7.48 22h.12c1.28 0 2.41-.86 2.76-2.08l1.2-4.2c.12-.42.51-.72.95-.72s.83.29.95.72l1.2 4.2A2.88 2.88 0 0 0 17.42 22c1.41 0 2.6-1.01 2.83-2.4l1.33-7.99c.28-1.67.42-3.38.42-5.08 0-1.95-1.17-3.67-2.97-4.39Zm.58 9.14-1.33 7.99a.87.87 0 0 1-.86.73c-.39 0-.73-.26-.84-.63l-1.2-4.2c-.37-1.28-1.55-2.17-2.87-2.17s-2.51.89-2.87 2.17l-1.2 4.2c-.11.37-.45.63-.84.63h-.12c-.4 0-.75-.27-.85-.66l-2.04-8.18c-.38-1.51-.57-3.07-.57-4.63 0-1.12.67-2.12 1.72-2.54 1.15-.46 2.46-.09 3.2.9l.69.91c.56.75 1.46 1.2 2.4 1.2s1.84-.45 2.4-1.2l.68-.91c.74-.99 2.05-1.36 3.2-.9 1.04.42 1.72 1.41 1.72 2.54 0 1.59-.13 3.18-.39 4.75Z">
                        </path>
                    </svg>
                    <div>{{ localize('global.dentist_department') }}</div>
                </a>
                <ul class="menu-sub">
                    <li class="menu-item {{ Route::is('dentist-registrations.index') ? 'active' : '' }}">
                        <a href="{{ route('dentist-registrations.index') }}" class="menu-link">
                            <div>{{ localize('global.dentist_registrations') }}</div>
                        </a>
                    </li>
                </ul>
            </li>
        @endcan

        @can('access-nephrology-registrations')
            <li class="menu-item {{ Route::is('nephrology-registrations.*') ? 'active open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                        viewBox="0 0 24 24">
                        <path
                            d="M8 22h8c1.1 0 2-.9 2-2V8c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2H6c-1.1 0-2 .9-2 2v2c0 1.1.9 2 2 2v12c0 1.1.9 2 2 2M6 4h12v2H6zm10 4v3h-5v6h5v3H8V8z">
                        </path>
                    </svg>
                    <div>{{ localize('global.nephrology_department') }}</div>
                </a>
                <ul class="menu-sub">
                    <li class="menu-item {{ Route::is('nephrology-registrations.index') ? 'active' : '' }}">
                        <a href="{{ route('nephrology-registrations.index') }}" class="menu-link">
                            <div>{{ localize('global.nephrology_registrations') }}</div>
                        </a>
                    </li>
                </ul>
            </li>
        @endcan

        @can('show-my-consultations-menu')
            <li class="menu-item {{ Route::is('consultations.index') ? 'active' : '' }}">
                <a href="{{ route('consultations.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-chat"></i>
                    <div>{{ localize('global.my_consultations') }}</div>
                </a>
            </li>
        @endcan
        @php
            $sidebarUser = auth()->user();
            $canSeePrescriptionsMenu =
                $sidebarUser?->hasRole(['admin', 'super_admin']) ||
                $sidebarUser?->can('show-prescriptions-menu') ||
                $sidebarUser?->hasActivePharmacyRole(['manager', 'staff']);

            $canSeePharmacyStockMenu =
                $sidebarUser?->hasRole(['admin', 'super_admin']) ||
                $sidebarUser?->hasActivePharmacyRole(['manager', 'procurement']);
            $isPharmacyManager = $sidebarUser?->hasActivePharmacyRole(['manager']) ?? false;
            $isPharmacyProcurement = $sidebarUser?->hasActivePharmacyRole(['procurement']) ?? false;
        @endphp

        @if ($canSeePrescriptionsMenu)
            <li class="menu-item {{ Route::is('prescriptions.*') ? 'active open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons bx bx-receipt"></i>
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
                    @if ($isPharmacyManager || $sidebarUser?->hasRole(['admin', 'super_admin']))
                        <li class="menu-item {{ Route::is('prescriptions.report') ? 'active' : '' }}">
                            <a href="{{ route('prescriptions.report') }}" class="menu-link">
                                <div>{{ localize('global.reports') }}</div>
                            </a>
                        </li>
                    @endif
                </ul>
            </li>
        @endif

        @if ($canSeePharmacyStockMenu)
            <li
                class="menu-item {{ Route::is('prescription_stocks.*') || Route::is('pharmacy_fulfillments.*') || Route::is('pharmacies.*') ? 'active open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons bx bx-package"></i>
                    <div>{{ localize('global.prescription_stocks') }}</div>
                </a>

                <ul class="menu-sub">
                    @if ($isPharmacyManager || $sidebarUser?->hasRole(['admin', 'super_admin']))
                        <li class="menu-item {{ Route::is('pharmacies.*') ? 'active' : '' }}">
                            <a href="{{ route('pharmacies.index') }}" class="menu-link">
                                <div>{{ localize('global.pharmacies') }}</div>
                            </a>
                        </li>
                    @endif

                    @if ($isPharmacyManager || $sidebarUser?->hasRole(['admin', 'super_admin']))
                        <li class="menu-item {{ Route::is('prescription_stocks.index') ? 'active' : '' }}">
                            <a href="{{ route('prescription_stocks.index') }}" class="menu-link">
                                <div>{{ localize('global.stock_overview') }}</div>
                            </a>
                        </li>
                    @endif

                    @if ($isPharmacyManager || $isPharmacyProcurement || $sidebarUser?->hasRole(['admin', 'super_admin']))
                        <li class="menu-item {{ Route::is('pharmacy_fulfillments.*') ? 'active' : '' }}">
                            <a href="{{ route('pharmacy_fulfillments.index') }}" class="menu-link">
                                <div>{{ localize('global.pharmacy_fulfillments') }}</div>
                            </a>
                        </li>
                        <li class="menu-item {{ Route::is('pharmacy_fulfillments.stock') ? 'active' : '' }}">
                            <a href="{{ route('pharmacy_fulfillments.stock') }}" class="menu-link">
                                <div>{{ localize('global.pharmacy_stock') }}</div>
                            </a>
                        </li>
                    @endif

                    @if ($isPharmacyManager || $sidebarUser?->hasRole(['admin', 'super_admin']))
                        <li class="menu-item {{ Route::is('incomes.*') ? 'active' : '' }}">
                            <a href="{{ route('incomes.index') }}" class="menu-link">
                                <div>{{ localize('global.stock_income') }}</div>
                            </a>
                        </li>
                        <li class="menu-item {{ Route::is('outcomes.index') ? 'active' : '' }}">
                            <a href="{{ route('outcomes.index') }}" class="menu-link">
                                <div>{{ localize('global.stock_outcome') }}</div>
                            </a>
                        </li>
                        <li class="menu-item {{ Route::is('outcomes.report') ? 'active' : '' }}">
                            <a href="{{ route('outcomes.report') }}" class="menu-link">
                                <div>{{ localize('global.outcome_reports') }}</div>
                            </a>
                        </li>
                        <li class="menu-item {{ Route::is('medicine_types.*') ? 'active' : '' }}">
                            <a href="{{ route('medicine_types.index') }}" class="menu-link">
                                <div>{{ localize('global.medicine_types') }}</div>
                            </a>
                        </li>
                    @endif
                </ul>
            </li>
        @endif
        <li class="menu-item {{ Route::is('depots.*') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-store"></i>
                <div>{{ localize('global.depot.title') }}</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ Route::is('depots.index') ? 'active' : '' }}">
                    <a href="{{ route('depots.index') }}" class="menu-link">
                        <div>{{ localize('global.depot.list') }}</div>
                    </a>
                </li>
            </ul>
        </li>

        @can('show-blood-bank-menu')
            <li class="menu-item {{ Route::is('blood_banks.*') ? 'active open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons bx bx-donate-blood"></i>
                    <div>{{ localize('global.blood_bank') }}</div>
                </a>

                <ul class="menu-sub">
                    <li class="menu-item {{ Route::is('blood_banks.dashboard') ? 'active' : '' }}">
                        <a href="{{ route('blood_banks.dashboard') }}" class="menu-link">
                            <div>{{ localize('global.blood_bank_dashboard') }}</div>
                        </a>
                    </li>
                    <li class="menu-item {{ Route::is('blood_banks.new') ? 'active' : '' }}">
                        <a href="{{ route('blood_banks.new') }}" class="menu-link">
                            <div>{{ localize('global.new_blood_requests') }}</div>
                        </a>
                    </li>
                    <li class="menu-item {{ Route::is('blood_banks.approved') ? 'active' : '' }}">
                        <a href="{{ route('blood_banks.approved') }}" class="menu-link">
                            <div>{{ localize('global.approved_blood_requests') }}</div>
                        </a>
                    </li>
                    <li class="menu-item {{ Route::is('blood_banks.delivered') ? 'active' : '' }}">
                        <a href="{{ route('blood_banks.delivered') }}" class="menu-link">
                            <div>{{ localize('global.delivered_blood_requests') }}</div>
                        </a>
                    </li>
                    <li class="menu-item {{ Route::is('blood_banks.rejected') ? 'active' : '' }}">
                        <a href="{{ route('blood_banks.rejected') }}" class="menu-link">
                            <div>{{ localize('global.rejected_blood_requests') }}</div>
                        </a>
                    </li>
                    <li
                        class="menu-item {{ Route::is('blood_banks.inventory') || Route::is('blood_banks.inventory.*') ? 'active' : '' }}">
                        <a href="{{ route('blood_banks.inventory') }}" class="menu-link">
                            <div>{{ localize('global.blood_inventory') }}</div>
                        </a>
                    </li>
                    <li class="menu-item {{ Route::is('blood_banks.movements') ? 'active' : '' }}">
                        <a href="{{ route('blood_banks.movements') }}" class="menu-link">
                            <div>{{ localize('global.stock_movement_audit') }}</div>
                        </a>
                    </li>
                    <li class="menu-item {{ Route::is('blood_banks.branch_transfers.*') ? 'active' : '' }}">
                        <a href="{{ route('blood_banks.branch_transfers.index') }}" class="menu-link">
                            <div>{{ localize('global.blood_branch_transfers') }}</div>
                        </a>
                    </li>
                    <li class="menu-item {{ Route::is('blood_banks.report') ? 'active' : '' }}">
                        <a href="{{ route('blood_banks.report') }}" class="menu-link">
                            <div>{{ localize('global.reports') }}</div>
                        </a>
                    </li>
                </ul>
            </li>
        @endcan

        @can('show-prosthetics-menu')
            <li class="menu-item {{ Route::is('prosthetics.*') ? 'active open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons bx bx-body"></i>
                    <div>{{ localize('global.prosthetics_module') }}</div>
                </a>
                <ul class="menu-sub">
                    <li class="menu-item {{ Route::is('prosthetics.dashboard') ? 'active' : '' }}">
                        <a href="{{ route('prosthetics.dashboard') }}" class="menu-link">
                            <div>{{ localize('global.prosthetics_dashboard') }}</div>
                        </a>
                    </li>
                    <li class="menu-item {{ Route::is('prosthetics.referrals.*') ? 'active' : '' }}">
                        <a href="{{ route('prosthetics.referrals.index') }}" class="menu-link">
                            <div>{{ localize('global.prosthetics_referrals') }}</div>
                        </a>
                    </li>
                    <li class="menu-item {{ Route::is('prosthetics.cases.*') ? 'active' : '' }}">
                        <a href="{{ route('prosthetics.cases.index') }}" class="menu-link">
                            <div>{{ localize('global.prosthetics_cases') }}</div>
                        </a>
                    </li>
                    <li class="menu-item {{ Route::is('prosthetics.catalog.*') ? 'active' : '' }}">
                        <a href="{{ route('prosthetics.catalog.index') }}" class="menu-link">
                            <div>{{ localize('global.prosthetics_catalog') }}</div>
                        </a>
                    </li>
                    <li class="menu-item {{ Route::is('prosthetics.stock.*') ? 'active' : '' }}">
                        <a href="{{ route('prosthetics.stock.index') }}" class="menu-link">
                            <div>{{ localize('global.prosthetics_stock') }}</div>
                        </a>
                    </li>
                    <li class="menu-item {{ Route::is('prosthetics.reports.*') ? 'active' : '' }}">
                        <a href="{{ route('prosthetics.reports.index') }}" class="menu-link">
                            <div>{{ localize('global.reports') }}</div>
                        </a>
                    </li>
                </ul>
            </li>
        @endcan

        @can('show-under-review-menu')
            <li class="menu-item {{ Route::is('under_reviews.index') ? 'active' : '' }}">
                <a href="{{ route('under_reviews.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-revision"></i>
                    <div>{{ localize('global.under_review_patients') }}</div>
                </a>
            </li>
        @endcan

        @can('show-hospitalizations-menu')
            <li
                class="menu-item {{ Route::is('hospitalizations.*') || Route::is('vital-sign-types.*') || Route::is('vital-signs.*') ? 'active open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons bx bx-bed"></i>
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
                    @if (auth()->user()->hasRole(['admin', 'super_admin']))
                        <li class="menu-item {{ Route::is('hospitalizations.roomManagement') ? 'active' : '' }}">
                            <a href="{{ route('hospitalizations.roomManagement') }}" class="menu-link">
                                <div>{{ localize('global.room_management') }}</div>
                            </a>
                        </li>
                    @endif
                    <li class="menu-item {{ Route::is('hospitalizations.report') ? 'active' : '' }}">
                        <a href="{{ route('hospitalizations.report') }}" class="menu-link">
                            <div>{{ localize('global.reports') }}</div>
                        </a>
                    </li>

                    <!-- Vital Signs Management -->
                    @can('show-vital-sign-types-menu')
                        <li class="menu-item {{ Route::is('vital-sign-types.*') ? 'active' : '' }}">
                            <a href="{{ route('vital-sign-types.index') }}" class="menu-link">
                                <div>{{ localize('global.vital_sign_types') }}</div>
                            </a>
                        </li>
                    @endcan
                    @can('show-vital-signs-menu')
                        <li class="menu-item {{ Route::is('vital-signs.*') ? 'active' : '' }}">
                            <a href="{{ route('vital-signs.index') }}" class="menu-link">
                                <div>{{ localize('global.vital_signs') }}</div>
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcan

        @can('show-labs-menu')
            <li
                class="menu-item {{ Route::is('lab_tests.completed') || Route::is('lab_tests.report') || Route::is('laboratory.*') ? 'active open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons bx bx-hard-hat"></i>
                    <div>{{ localize('global.checkups') }}</div>
                </a>

                <ul class="menu-sub">
                    <!-- Test Operations - Daily Workflow -->
                    <li class="menu-item {{ Route::is('laboratory.results.pending') ? 'active' : '' }}">
                        <a href="{{ route('laboratory.results.pending') }}" class="menu-link">
                            <div>{{ localize('global.pending_tests') }}</div>
                        </a>
                    </li>
                    <li class="menu-item {{ Route::is('laboratory.results.in-progress') ? 'active' : '' }}">
                        <a href="{{ route('laboratory.results.in-progress') }}" class="menu-link">
                            <div>{{ localize('global.in_progress_tests') }}</div>
                        </a>
                    </li>
                    <li class="menu-item {{ Route::is('laboratory.results.completed') ? 'active' : '' }}">
                        <a href="{{ route('laboratory.results.completed') }}" class="menu-link">
                            <div>{{ localize('global.completed_tests') }}</div>
                        </a>
                    </li>

                    <!-- Test Tools and Reports -->
                    @can('show-laboratory-menu')
                        <li class="menu-item {{ Route::is('laboratory.scan') ? 'active' : '' }}">
                            <a href="{{ route('laboratory.scan') }}" class="menu-link">
                                <div>{{ localize('global.scan_test') }}</div>
                            </a>
                        </li>
                        <li class="menu-item {{ Route::is('laboratory.results.grouped') ? 'active' : '' }}">
                            <a href="{{ route('laboratory.results.grouped') }}" class="menu-link">
                                <div>{{ localize('global.grouped_test_results') }}</div>
                            </a>
                        </li>
                        <li class="menu-item {{ Route::is('laboratory.registrations.report') ? 'active' : '' }}">
                            <a href="{{ route('laboratory.registrations.report') }}" class="menu-link">
                                <div>{{ localize('global.test_registration_report') }}</div>
                            </a>
                        </li>
                        <li class="menu-item {{ Route::is('laboratory.registrations.report-detailed') ? 'active' : '' }}">
                            <a href="{{ route('laboratory.registrations.report-detailed') }}" class="menu-link">
                                <div>{{ localize('global.test_registration_report_detailed') ?? 'Full Detailed Test Report' }}
                                </div>
                            </a>
                        </li>
                    @endcan

                    <!-- System Management -->
                    @can('show-categories-menu')
                        <li class="menu-item {{ Route::is('categories.index') ? 'active' : '' }}">
                            <a href="{{ route('categories.index') }}" class="menu-link">
                                <div>{{ localize('global.categories') }}</div>
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
                </ul>
            </li>
        @endcan

        @can('show-icu-menu')
            <li class="menu-item {{ Route::is('icus.*') ? 'active open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons bx bx-tv"></i>
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
                    <li class="menu-item {{ Route::is('icus.report') ? 'active' : '' }}">
                        <a href="{{ route('icus.report') }}" class="menu-link">
                            <div>{{ localize('global.reports') }}</div>
                        </a>
                    </li>
                </ul>
            </li>
        @endcan
        @can('show-pacu-menu')
            <li class="menu-item {{ Route::is('pacus.*') ? 'active open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons bx bx-tv"></i>
                    <div>{{ localize('global.pacus') }}</div>
                </a>

                <ul class="menu-sub">
                    <li class="menu-item {{ Route::is('pacus.index') ? 'active' : '' }}">
                        <a href="{{ route('pacus.index') }}" class="menu-link">
                            <div>{{ localize('global.new_pacus') }}</div>
                        </a>
                    </li>
                    <li class="menu-item {{ Route::is('pacus.completed') ? 'active' : '' }}">
                        <a href="{{ route('pacus.completed') }}" class="menu-link">
                            <div>{{ localize('global.completed_pacus') }}</div>
                        </a>
                    </li>
                    <li class="menu-item {{ Route::is('pacus.report') ? 'active' : '' }}">
                        <a href="{{ route('pacus.report') }}" class="menu-link">
                            <div>{{ localize('global.reports') }}</div>
                        </a>
                    </li>
                </ul>
            </li>
        @endcan
        @can('show-anesthesias-menu')
            <li class="menu-item {{ Route::is('anesthesias.*') ? 'active open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons bx bx-first-aid"></i>
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
                    <li class="menu-item {{ Route::is('anesthesias.report') ? 'active' : '' }}">
                        <a href="{{ route('anesthesias.report') }}" class="menu-link">
                            <div>{{ localize('global.reports') }}</div>
                        </a>
                    </li>
                </ul>
            </li>
        @endcan

        @can('show-operations-menu')
            <li class="menu-item {{ Route::is('operations.*') ? 'active open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons bx bx-cut"></i>
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
                    <li class="menu-item {{ Route::is('operations.reserved') ? 'active' : '' }}">
                        <a href="{{ route('operations.reserved') }}" class="menu-link">
                            <div>{{ localize('global.reserved_operations') }}</div>
                        </a>
                    </li>
                    <li class="menu-item {{ Route::is('operations.completed') ? 'active' : '' }}">
                        <a href="{{ route('operations.completed') }}" class="menu-link">
                            <div>{{ localize('global.completed_operations') }}</div>
                        </a>
                    </li>
                    <li class="menu-item {{ Route::is('operations.report') ? 'active' : '' }}">
                        <a href="{{ route('operations.report') }}" class="menu-link">
                            <div>{{ localize('global.reports') }}</div>
                        </a>
                    </li>
                </ul>
            </li>
        @endcan

        @can('show-settings-menu')
            <li
                class="menu-item {{ Route::is('users.index') || Route::is('doctors.*') || Route::is('roles.index') || Route::is('permissions.index') || Route::is('document-type-columns.index') || Route::is('notices.index') || Route::is('sectors.index') || Route::is('recipients.index') || Route::is('hukums.index') || Route::is('categories.index') ? 'active open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons bx bx-cog"></i>
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
                    @if (auth()->user()->hasRole(['admin', 'super_admin']))
                        <li class="menu-item {{ Route::is('doctors.*') ? 'active' : '' }}">
                            <a href="{{ route('doctors.index') }}" class="menu-link">
                                <div>{{ localize('global.doctors') }}</div>
                            </a>
                        </li>
                    @endif
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
                    @role('admin')
                        <li class="menu-item {{ Route::is('backups.*') ? 'active' : '' }}">
                            <a href="{{ route('backups.index') }}" class="menu-link">
                                <div>{{ __('Backups') }}</div>
                            </a>
                        </li>
                    @endrole
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
                    <li class="menu-item {{ Route::is('militery_types.index') ? 'active' : '' }}">
                        <a href="{{ route('militery_types.index') }}" class="menu-link">
                            <div>{{ localize('global.militery_types') }}</div>
                        </a>
                    </li>

                    @can('show-add-icu-procedures-menu')
                        <li class="menu-item {{ Route::is('procedure_types.*') ? 'active' : '' }}">
                            <a href="{{ route('procedure_types.index') }}" class="menu-link">
                                <div>{{ localize('global.procedure_types') }}</div>
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
                    @can('show-medicine-usage-menu')
                        <li class="menu-item {{ Route::is('medicine_usage_types.*') ? 'active' : '' }}">
                            <a href="{{ route('medicine_usage_types.index') }}" class="menu-link">
                                <div>{{ localize('global.medicine_usage_types') }}</div>
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
                    @can('show-disease-menu')
                        <li class="menu-item {{ Route::is('diseases.*') ? 'active' : '' }}">
                            <a href="{{ route('diseases.index') }}" class="menu-link">
                                <div>{{ localize('global.diseases') }}</div>
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
                    @can('show-nurses-menu')
                        <li class="menu-item {{ Route::is('nurses.index') ? 'active' : '' }}">
                            <a href="{{ route('nurses.index') }}" class="menu-link">
                                <div>{{ localize('global.nurses') }}</div>
                            </a>
                        </li>
                    @endcan


                    <!-- Vital Signs Management -->
                    @can('show-vital-sign-types-menu')
                        <li class="menu-item {{ Route::is('vital-sign-types.*') ? 'active' : '' }}">
                            <a href="{{ route('vital-sign-types.index') }}" class="menu-link">
                                <div>{{ localize('global.vital_sign_types') }}</div>
                            </a>
                        </li>
                    @endcan
                    @can('show-vital-signs-menu')
                        <li class="menu-item {{ Route::is('vital-signs.*') ? 'active' : '' }}">
                            <a href="{{ route('vital-signs.index') }}" class="menu-link">
                                <div>{{ localize('global.vital_signs') }}</div>
                            </a>
                        </li>
                    @endcan


                    @role('super_admin')
                        <li class="menu-item {{ Route::is('backups.*') ? 'active' : '' }}">
                            <a href="{{ route('backups.index') }}" class="menu-link">
                                <div>{{ localize('global.backups') }}</div>
                            </a>
                        </li>
                    @endrole
                </ul>
            </li>
        @endcan
    </ul>
</aside>
