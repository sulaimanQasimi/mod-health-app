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
            class="menu-item {{ Route::is('documents.index') || Route::is('followup.index') || Route::is('completed_documents.index') || Route::is('report.index') || Route::is('report.followup-report') ? 'active open' : '' }}">
            @can('show-asnad-ertebat-menu')
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-layout"></i>
                <div>{{ localize('global.documents_communication') }}</div>
            </a>
            @endcan

            <ul class="menu-sub">
                @can('show-dc-asnad-menu')
                <li class="menu-item {{ Route::is('documents.index') ? 'active' : '' }}">
                    <a href="{{ route('documents.index') }}" class="menu-link">
                        <div>{{ localize('global.document') }}</div>
                    </a>
                </li>
                @endcan
                @can('show-dc-followup-menu')
                <li class="menu-item {{ Route::is('followup.index') ? 'active' : '' }}">
                    <a href="{{ route('followup.index') }}" class="menu-link">
                        <div>{{ localize('global.documents_followup') }}</div>
                    </a>
                </li>
                @endcan
                @can('show-dc-completed-menu')
                <li class="menu-item {{ Route::is('completed_documents.index') ? 'active' : '' }}">
                    <a href="{{ route('completed_documents.index') }}" class="menu-link">
                        <div>{{ localize('global.completed_documents') }}</div>
                    </a>
                </li>
                @endcan
                @can('show-dc-reports-menu')
                <li class="menu-item {{ Route::is('report.index') ? 'active' : '' }}">
                    <a href="{{ route('report.index') }}" class="menu-link">
                        <div>{{ localize('global.documents_report') }}</div>
                    </a>
                </li>
                @endcan
                @can('show-dc-followup-reports-menu')
                <li class="menu-item {{ Route::is('report.followup-report') ? 'active' : '' }}">
                    <a href="{{ route('report.followup-report') }}" class="menu-link">
                        <div>{{ localize('global.followup_report') }}</div>
                    </a>
                </li>
                @endcan
            </ul>
        </li>

        <li
            class="menu-item {{ Route::is('orders.create') || Route::is('orders.index') || Route::is('orders.completed') || Route::is('orders.hefzia') ? 'active open' : '' }}">
            @can('show-odf-menu')
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-detail"></i>
                <div>{{ localize('global.orders') }}</div>
            </a>
            @endcan
            <ul class="menu-sub">
                @can('show-odf-create-documents-menu')
                <li class="menu-item {{ Route::is('orders.create') ? 'active' : '' }}">
                    <a href="{{ route('orders.create') }}" class="menu-link">
                        <div>{{ localize('global.create_order') }}</div>
                    </a>
                </li>
                @endcan
                @can('show-odf-followup-menu')
                <li class="menu-item {{ Route::is('orders.index') ? 'active' : '' }}">
                    <a href="{{ route('orders.index') }}" class="menu-link">
                        <div>{{ localize('global.with_followups') }}</div>
                    </a>
                </li>
                @endcan
                @can('show-odf-completed-menu')
                <li class="menu-item {{ Route::is('orders.completed') ? 'active' : '' }}">
                    <a href="{{ route('orders.completed') }}" class="menu-link">
                        <div>{{ localize('global.completed_documents') }}</div>
                    </a>
                </li>
                @endcan
                @can('show-odf-archived-menu')
                <li class="menu-item {{ Route::is('orders.hefzia') ? 'active' : '' }}">
                    <a href="{{ route('orders.hefzia') }}" class="menu-link">
                        <div>{{ localize('global.hefzia') }}</div>
                    </a>
                </li>
                @endcan
                @can('show-odf-reports-menu')
                <li class="menu-item {{ Route::is('report.internal-document-report') ? 'active' : '' }}">
                    <a href="{{ route('report.internal-document-report') }}" class="menu-link">
                        <div>{{ localize('global.documents_report') }}</div>
                    </a>
                </li>
                @endcan
                @can('show-odf-followup-reports-menu')
                <li class="menu-item {{ Route::is('report.internal-followup-report') ? 'active' : '' }}">
                    <a href="{{ route('report.internal-followup-report') }}" class="menu-link">
                        <div>{{ localize('global.followup_report') }}</div>
                    </a>
                </li>
                @endcan
                @can('show-odf-delete-requests-menu')
                <li class=" menu-item {{ Route::is('action-approval-requests/0') ? 'active' : '' }}">
                    <a href="{{ route('action-approval-requests', '0') }}" class="menu-link">

                        <div>{{ localize('global.delete_requests') }}</div>
                    </a>
                </li>
                @endcan
                @can('show-odf-return-documents-menu')
                <li class=" menu-item {{ Route::is('action-approval-requests/1') ? 'active' : '' }}">
                    <a href="{{ route('action-approval-requests', '1') }}" class="menu-link">

                        <div>{{ localize('global.return_requests') }}</div>
                    </a>
                </li>
                @endcan
            </ul>
        </li>

        <li
            class="menu-item {{ Route::is('users.index') || Route::is('roles.index') || Route::is('permissions.index') || Route::is('document-type-columns.index') || Route::is('notices.index') || Route::is('sectors.index') || Route::is('recipients.index') || Route::is('hukums.index') ? 'active open' : '' }}">
            @can('show-settings-menu')
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-wrench"></i>
                <div>{{ localize('global.settings') }}</div>
            </a>
            @endcan
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
                @can('show-logs-menu')
                <li class="menu-item">
                    <a href="{{ route('log-viewer') }}" class="menu-link">
                        <div>{{ localize('global.logs') }}</div>
                    </a>
                </li>
                @endcan
                @can('show-document-types-menu')
                <li class="menu-item {{ Route::is('document-type-columns.index') ? 'active' : '' }}">
                    <a href="{{ route('document-type-columns.index') }}" class="menu-link">
                        <div>{{ localize('global.document_type_columns') }}</div>
                    </a>
                </li>
                @endcan
                @can('show-notices-menu')
                <li class="menu-item {{ Route::is('notices.index') ? 'active' : '' }}">
                    <a href="{{ route('notices.index') }}" class="menu-link">
                        <div>{{ localize('global.notices') }}</div>
                    </a>
                </li>
                @endcan
                @can('show-sectors-menu')
                <li class="menu-item {{ Route::is('sectors.index') ? 'active' : '' }}">
                    <a href="{{ route('sectors.index') }}" class="menu-link">
                        <div>{{ localize('global.sectors') }}</div>
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
                @can('show-marja-sudor-hukum-menu')
                <li class="menu-item {{ Route::is('hukums.index') ? 'active' : '' }}">
                    <a href="{{ route('hukums.index') }}" class="menu-link">
                        <div>{{ localize('global.hukums') }}</div>
                    </a>
                </li>
                @endcan
                @can('show-recipient-types-menu')
                <li class="menu-item {{ Route::is('recipient-types.index') ? 'active' : '' }}">
                    <a href="{{ route('recipient-types.index') }}" class="menu-link">
                        <div>{{ localize('global.recipientTypes') }}</div>
                    </a>
                </li>
                @endcan
            </ul>
        </li>
    </ul>
</aside>
