<?php

namespace App\Services;

use App\Models\User;
use App\Support\DepotRolePermissions;
use Illuminate\Http\Request;

class SidebarMenuService
{
    public function build(Request $request): array
    {
        $user = $request->user();

        if (! $user) {
            return [];
        }

        $items = [
            $this->item('dashboard', 'global.dashboard', 'bx-home', 'react.dashboard'),
        ];

        if ($user->can('show-information-menu')) {
            $items[] = $this->group('reception', 'global.reception', 'bx-info-circle', [
                'react.scan-code',
                'react.patients.*',
                'react.appointments.index',
                'react.appointments.department-report',
                'react.patients.report',
                'react.doctor-performance-report',
            ], [
                $this->item('scan-code', 'global.scan_qrcode', null, 'react.scan-code'),
                $this->item('patients-create', 'global.create_patient', null, 'react.patients.create'),
                $this->item('patients-index', 'global.patients_list', null, 'react.patients.index'),
                $this->item('appointments-index', 'global.all_appointments', null, 'react.appointments.index'),
                $this->item('appointments-department-report', 'global.department_report', null, 'react.appointments.department-report'),
                $this->item('patients-report', 'global.reports', null, 'react.patients.report'),
                $this->item('doctor-performance-report', 'global.user_performance_report', null, 'react.doctor-performance-report'),
            ]);
        }

        if ($user->can('show-my-visits-menu')) {
            $items[] = $this->group('my-appointments', 'global.my_appointments', 'bx-time-five', [
                'react.appointments.department',
                'react.appointments.doctor',
                'react.appointments.completed',
                'react.appointments.report',
            ], [
                $this->item('appointments-department', 'global.department_appointments', null, 'react.appointments.department'),
                $this->item('appointments-doctor', 'global.ongoing_appointments', null, 'react.appointments.doctor'),
                $this->item('appointments-completed', 'global.completed_appointments', null, 'react.appointments.completed'),
                $this->item('appointments-report', 'global.reports', null, 'react.appointments.report'),
            ]);
        }

        if ($user->can('show-physiotherapy-menu')) {
            $children = [];
            if ($user->can('show-physiotherapy-procedures')) {
                $children[] = $this->item('physiotherapy-procedures', 'global.all_procedures', null, 'react.physiotherapy-procedures.index');
            }
            if ($user->can('show-own-physiotherapy-procedures')) {
                $children[] = $this->item('physiotherapy-my-procedures', 'global.my_procedures', null, 'react.physiotherapy-procedures.my-procedures');
            }
            if ($user->can('show-physiotherapy-reports')) {
                $children[] = $this->item('physiotherapy-reports', 'global.reports', null, 'react.physiotherapy-reports.index');
            }
            if ($user->can('show-physiotherapy-types')) {
                $children[] = $this->item('physiotherapy-types', 'global.physiotherapy_types', null, 'react.physiotherapy-types.index');
            }
            if ($children) {
                $items[] = $this->group('physiotherapy', 'global.physiotherapy', 'bx-heart', [
                    'react.physiotherapy-procedures.*',
                    'react.physiotherapy-reports.*',
                    'react.physiotherapy-types.*',
                ], $children);
            }
        }

        if ($user->can('access-dentist-registrations')) {
            $items[] = $this->group('dentist', 'global.dentist_department', 'dentist', [
                'react.dentist-registrations.*',
            ], [
                $this->item('dentist-registrations', 'global.dentist_registrations', null, 'react.dentist-registrations.index'),
            ]);
        }

        if ($user->can('access-nephrology-registrations')) {
            $items[] = $this->group('nephrology', 'global.nephrology_department', 'nephrology', [
                'react.nephrology-registrations.*',
                'react.hemodialysis-sessions.*',
            ], [
                $this->item('nephrology-registrations', 'global.nephrology_registrations', null, 'react.nephrology-registrations.index'),
                $this->item('hemodialysis-sessions', 'global.hemodialysis', null, 'react.hemodialysis-sessions.index'),
            ]);
        }

        if ($user->can('show-my-consultations-menu')) {
            $items[] = $this->item('consultations', 'global.my_consultations', 'bx-chat', 'react.consultations.index');
        }

        if ($this->canSeePrescriptionsMenu($user)) {
            $children = [
                $this->item('prescriptions-scan', 'global.scan_prescription', null, 'react.prescriptions.scan-code'),
                $this->item('prescriptions-index', 'global.undelivered_prescriptions', null, 'react.prescriptions.index'),
                $this->item('prescriptions-delivered', 'global.delivered_prescriptions', null, 'react.prescriptions.delivered'),
            ];
            if ($user->hasRole(['admin', 'super_admin']) || $user->hasActivePharmacyRole(['manager'])) {
                $children[] = $this->item('prescriptions-report', 'global.reports', null, 'react.prescriptions.report');
            }
            $items[] = $this->group('prescriptions', 'global.prescriptions', 'bx-receipt', [
                'react.prescriptions.*',
            ], $children);
        }

        if ($this->canSeePharmacyStockMenu($user)) {
            $children = [];
            if ($user->hasRole(['admin', 'super_admin']) || $user->hasActivePharmacyRole(['manager'])) {
                $children[] = $this->item('pharmacies', 'global.pharmacies', null, 'react.pharmacies.index');
                $children[] = $this->item('prescription-stocks', 'global.stock_overview', null, 'react.prescription-stocks.index');
            }
            if ($user->hasRole(['admin', 'super_admin']) || $user->hasActivePharmacyRole(['manager', 'procurement'])) {
                $children[] = $this->item('depot-requests', 'global.depot.requests', null, 'react.depots.requests.index');
                $children[] = $this->item('pharmacy-stock', 'global.pharmacy_stock', null, 'react.pharmacy-stock.index');
            }
            if ($user->hasRole(['admin', 'super_admin']) || $user->hasActivePharmacyRole(['manager'])) {
                $children[] = $this->item('incomes', 'global.stock_income', null, 'react.incomes.index');
                $children[] = $this->item('outcomes', 'global.stock_outcome', null, 'react.outcomes.index');
                $children[] = $this->item('outcomes-report', 'global.outcome_reports', null, 'react.outcomes.report');
            }
            if ($children) {
                $items[] = $this->group('pharmacy-stock', 'global.prescription_stocks', 'bx-package', [
                    'react.prescription-stocks.*',
                    'react.pharmacy-stock.*',
                    'react.pharmacies.*',
                    'react.incomes.*',
                    'react.outcomes.*',
                ], $children);
            }
        }

        if ($this->canSeeDepotMenu($user)) {
            $depotChildren = [];

            if ($this->userCanDepotMenuAction($user, DepotRolePermissions::ACTION_VIEW)) {
                $depotChildren[] = $this->item('depots', 'global.depot.list', null, 'react.depots.index', ['global.depot', 'list']);
            }
            if ($this->userCanDepotMenuAction($user, DepotRolePermissions::ACTION_TRANSACTION_VIEW)) {
                $depotChildren[] = $this->item('depot-transactions', 'global.depot.transactions', null, 'react.depots.transactions.index', ['global.depot', 'transactions']);
            }
            // if ($this->canSeeDepotRequestsMenu($user)) {
            //     $depotChildren[] = $this->item('depot-requests', 'global.depot.requests', null, 'react.depots.requests.index', ['global.depot', 'requests']);
            // }
            if ($this->userCanDepotMenuAction($user, DepotRolePermissions::ACTION_MOVEMENT_DEPOT_TO_DEPOT)) {
                $depotChildren[] = $this->item('depot-to-depot', 'global.depot.depot_to_depot', null, 'react.depots.movements.depot-to-depot', ['global.depot', 'depot_to_depot']);
            }
            if ($this->userCanDepotMenuAction($user, DepotRolePermissions::ACTION_VIEW)) {
                $depotChildren[] = $this->item('tools', 'global.depot.tools', null, 'react.tools.index', ['global.depot', 'tools']);
            }
            if ($this->userCanDepotMenuAction($user, DepotRolePermissions::ACTION_REPORT_EXPORT)) {
                $depotChildren[] = $this->item('depot-reports', 'global.depot.reports', null, 'react.depots.reports.index', ['global.depot', 'reports']);
            }

            if ($depotChildren !== []) {
                $items[] = $this->group('depots', 'global.depot.title', 'bx-store', [
                    'react.depots.*',
                    'react.tools.*',
                ], $depotChildren, ['global.depot', 'title']);
            }
        }

        if ($user->can('show-blood-bank-menu')) {
            $items[] = $this->group('blood-bank', 'global.blood_bank', 'bx-donate-blood', [
                'react.blood-banks.*',
            ], [
                $this->item('blood-bank-dashboard', 'global.blood_bank_dashboard', null, 'react.blood-banks.dashboard'),
                $this->item('blood-bank-new', 'global.new_blood_requests', null, 'react.blood-banks.new'),
                $this->item('blood-bank-approved', 'global.approved_blood_requests', null, 'react.blood-banks.approved'),
                $this->item('blood-bank-delivered', 'global.delivered_blood_requests', null, 'react.blood-banks.delivered'),
                $this->item('blood-bank-rejected', 'global.rejected_blood_requests', null, 'react.blood-banks.rejected'),
                $this->item('blood-bank-inventory', 'global.blood_inventory', null, 'react.blood-banks.inventory'),
                $this->item('blood-bank-movements', 'global.stock_movement_audit', null, 'react.blood-banks.movements'),
                $this->item('blood-bank-transfers', 'global.blood_branch_transfers', null, 'react.blood-banks.branch-transfers.index'),
                $this->item('blood-bank-report', 'global.reports', null, 'react.blood-banks.report'),
            ]);
        }

        if ($user->can('show-prosthetics-menu')) {
            $items[] = $this->group('prosthetics', 'global.prosthetics_module', 'bx-body', [
                'react.prosthetics.*',
            ], [
                $this->item('prosthetics-dashboard', 'global.prosthetics_dashboard', null, 'react.prosthetics.dashboard'),
                $this->item('prosthetics-referrals', 'global.prosthetics_referrals', null, 'react.prosthetics.referrals.index'),
                $this->item('prosthetics-cases', 'global.prosthetics_cases', null, 'react.prosthetics.cases.index'),
                $this->item('prosthetics-catalog', 'global.prosthetics_catalog', null, 'react.prosthetics.catalog.index'),
                $this->item('prosthetics-stock', 'global.prosthetics_stock', null, 'react.prosthetics.stock.index'),
                $this->item('prosthetics-reports', 'global.reports', null, 'react.prosthetics.reports.index'),
            ]);
        }

        if ($user->can('show-under-review-menu')) {
            $items[] = $this->item('under-reviews', 'global.under_review_patients', 'bx-revision', 'react.under-reviews.index');
        }

        if ($user->can('show-hospitalizations-menu')) {
            $children = [
                $this->item('hospitalizations', 'global.under_hospitalizations', null, 'react.hospitalizations.index'),
                $this->item('hospitalizations-discharged', 'global.discharged_hospitalizations', null, 'react.hospitalizations.discharged'),
            ];
            if ($user->can('manageAny', \App\Models\Room::class)) {
                $children[] = $this->item('hospitalizations-rooms', 'global.room_management', null, 'react.hospitalizations.room-management');
            }
            $children[] = $this->item('hospitalizations-report', 'global.reports', null, 'react.hospitalizations.report');
            $items[] = $this->group('hospitalizations', 'global.hospitalizations', 'bx-bed', [
                'react.hospitalizations.*',
            ], $children);
        }

        if ($user->can('show-labs-menu')) {
            $children = [
                $this->item('lab-pending', 'global.pending_tests', null, 'react.laboratory.results.pending'),
                $this->item('lab-in-progress', 'global.in_progress_tests', null, 'react.laboratory.results.in-progress'),
                $this->item('lab-completed', 'global.completed_tests', null, 'react.laboratory.results.completed'),
            ];
            if ($user->can('show-laboratory-menu')) {
                $children[] = $this->item('lab-scan', 'global.scan_test', null, 'react.laboratory.scan');
                $children[] = $this->item('lab-grouped', 'global.grouped_test_results', null, 'react.laboratory.results.grouped');
                $children[] = $this->item('lab-report', 'global.test_registration_report', null, 'react.laboratory.registrations.report');
                $children[] = $this->item('lab-report-detailed', 'global.test_registration_report_detailed', null, 'react.laboratory.registrations.report-detailed');
            }
            $items[] = $this->group('labs', 'global.checkups', 'bx-hard-hat', [
                'react.laboratory.*',
            ], $children);
        }

        if ($user->can('show-icu-menu')) {
            $items[] = $this->group('icus', 'global.icus', 'bx-tv', [
                'react.icus.*',
            ], [
                $this->item('icus-new', 'global.new_icus', null, 'react.icus.new'),
                $this->item('icus-approved', 'global.approved_icus', null, 'react.icus.approved'),
                $this->item('icus-rejected', 'global.rejected_icus', null, 'react.icus.rejected'),
                $this->item('icus-report', 'global.reports', null, 'react.icus.report'),
            ]);
        }

        if ($user->can('show-pacu-menu')) {
            $items[] = $this->group('pacus', 'global.pacus', 'bx-tv', [
                'react.pacus.*',
            ], [
                $this->item('pacus-index', 'global.new_pacus', null, 'react.pacus.index'),
                $this->item('pacus-completed', 'global.completed_pacus', null, 'react.pacus.completed'),
                $this->item('pacus-report', 'global.reports', null, 'react.pacus.report'),
            ]);
        }

        if ($user->can('show-anesthesias-menu')) {
            $items[] = $this->group('anesthesias', 'global.anesthesias', 'bx-first-aid', [
                'react.anesthesias.*',
            ], [
                $this->item('anesthesias-new', 'global.new_anesthesias', null, 'react.anesthesias.new'),
                $this->item('anesthesias-approved', 'global.approved_anesthesias', null, 'react.anesthesias.approved'),
                $this->item('anesthesias-rejected', 'global.rejected_anesthesias', null, 'react.anesthesias.rejected'),
                $this->item('anesthesias-report', 'global.reports', null, 'react.anesthesias.report'),
            ]);
        }

        if ($user->can('show-operations-menu')) {
            $items[] = $this->group('operations', 'global.operations', 'bx-cut', [
                'react.operations.*',
            ], [
                $this->item('operations-new', 'global.new_operations', null, 'react.operations.new'),
                $this->item('operations-approved', 'global.approved_operations', null, 'react.operations.approved'),
                $this->item('operations-reserved', 'global.reserved_operations', null, 'react.operations.reserved'),
                $this->item('operations-completed', 'global.completed_operations', null, 'react.operations.completed'),
                $this->item('operations-report', 'global.reports', null, 'react.operations.report'),
            ]);
        }

        if ($user->hasRole(['admin', 'super_admin']) || $user->can('show-reports-menu')) {
            $items[] = $this->item('general-report', 'global.general_report', 'bx-bar-chart-alt-2', 'react.reports.general.index');
        }

        if ($user->can('show-settings-menu')) {
            $children = [];
            if ($user->can('show-users-menu')) {
                $children[] = $this->item('users', 'global.users', null, 'react.users.index');
            }
            if ($user->hasRole(['admin', 'super_admin'])) {
                $children[] = $this->item('doctors', 'global.doctors', null, 'react.doctors.index');
            }
            if ($user->can('show-roles-menu')) {
                $children[] = $this->item('roles', 'global.roles', null, 'react.roles.index');
            }
            if ($user->can('show-permissions-menu')) {
                $children[] = $this->item('permissions', 'global.permissions', null, 'react.permissions.index');
            }
            if ($user->hasRole('admin')) {
                $children[] = $this->item('backups-admin', 'Backups', null, 'react.backups.index');
            }
            if ($user->can('show-recipients-menu')) {
                $children[] = $this->item('recipients', 'global.recipients', null, 'react.recipients.index');
            }
            if ($user->can('show-recipient-parts-menu')) {
                $children[] = $this->item('recipient-parts', 'global.recipient_parts', null, 'react.recipient-parts.index');
            }
            if ($user->can('show-relations-menu')) {
                $children[] = $this->item('relations', 'global.relations', null, 'react.relations.index');
            }
            if ($user->can('show-departments-menu')) {
                $children[] = $this->item('departments', 'global.departments', null, 'react.departments.index');
            }
            if ($user->can('show-sections-menu')) {
                $children[] = $this->item('sections', 'global.sections', null, 'react.sections.index');
            }
            if ($user->can('show-floors-menu')) {
                $children[] = $this->item('floors', 'global.floors', null, 'react.floors.index');
            }
            if ($user->can('show-rooms-menu')) {
                $children[] = $this->item('rooms', 'global.rooms', null, 'react.rooms.index');
            }
            if ($user->can('show-beds-menu')) {
                $children[] = $this->item('beds', 'global.beds', null, 'react.beds.index');
            }
            $children[] = $this->item('militery-types', 'global.militery_types', null, 'react.militery-types.index');
            if ($user->can('show-add-icu-procedures-menu')) {
                $children[] = $this->item('procedure-types', 'global.procedure_types', null, 'react.procedure-types.index');
            }
            if ($user->can('show-operation-types-menu')) {
                $children[] = $this->item('operation-types', 'global.operation_types', null, 'react.operation-types.index');
            }
            if ($user->can('show-medicine-types-menu')) {
                $children[] = $this->item('medicine-types-settings', 'global.medicine_types', null, 'react.medicine-types.index');
            }
            if ($user->can('show-medicine-menu')) {
                $children[] = $this->item('medicines', 'global.medicines', null, 'react.medicines.index');
            }
            if ($user->can('show-medicine-usage-menu')) {
                $children[] = $this->item('medicine-usage-types', 'global.medicine_usage_types', null, 'react.medicine-usage-types.index');
            }
            if ($user->can('show-food-types-menu')) {
                $children[] = $this->item('food-types', 'global.food_types', null, 'react.food-types.index');
            }
            if ($user->can('show-disease-menu')) {
                $children[] = $this->item('diseases', 'global.diseases', null, 'react.diseases.index');
            }
            if ($user->can('show-branches-menu')) {
                $children[] = $this->item('branches', 'global.branches', null, 'react.branches.index');
            }
            if ($user->can('show-nurses-menu')) {
                $children[] = $this->item('nurses', 'global.nurses', null, 'react.nurses.index');
            }
            if ($user->can('show-vital-sign-types-menu')) {
                $children[] = $this->item('vital-sign-types-settings', 'global.vital_sign_types', null, 'react.vital-sign-types.index');
            }
            if ($user->can('show-test-types-menu')) {
                $children[] = $this->item('lab-types', 'global.lab_types', null, 'react.lab-types.index');
            }
            if ($user->hasRole('super_admin')) {
                $children[] = $this->item('backups-super', 'global.backups', null, 'react.backups.index');
                $children[] = $this->item('activity-logs', 'activity_log.title', null, 'react.activity-logs.index');
            }
            $items[] = $this->group('settings', 'global.settings', 'bx-cog', [
                'react.users.*',
                'react.doctors.*',
                'react.roles.*',
                'react.permissions.*',
                'react.backups.*',
                'react.activity-logs.*',
                'react.recipients.*',
                'react.relations.*',
                'react.departments.*',
                'react.sections.*',
                'react.floors.*',
                'react.rooms.*',
                'react.beds.*',
                'react.militery-types.*',
                'react.procedure-types.*',
                'react.operation-types.*',
                'react.medicine-types.*',
                'react.medicines.*',
                'react.medicine-usage-types.*',
                'react.food-types.*',
                'react.diseases.*',
                'react.branches.*',
                'react.nurses.*',
                'react.vital-sign-types.*',
                'react.lab-types.*',
            ], $children);
        }

        return $items;
    }

    private function item(string $key, string $label, ?string $icon, string $route, ?array $labelParts = null): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'labelParts' => $labelParts,
            'icon' => $icon,
            'route' => $route,
            'href' => route($route),
            'children' => [],
        ];
    }

    private function group(string $key, string $label, string $icon, array $activePatterns, array $children, ?array $labelParts = null): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'labelParts' => $labelParts,
            'icon' => $icon,
            'route' => null,
            'href' => null,
            'activePatterns' => $activePatterns,
            'children' => $children,
        ];
    }

    private function canSeePrescriptionsMenu(User $user): bool
    {
        return $user->hasRole(['admin', 'super_admin'])
            || $user->can('show-prescriptions-menu')
            || $user->hasActivePharmacyRole(['manager', 'staff']);
    }

    private function canSeePharmacyStockMenu(User $user): bool
    {
        return $user->hasRole(['admin', 'super_admin'])
            || $user->hasActivePharmacyRole(['manager', 'procurement']);
    }

    private function canSeeDepotMenu(User $user): bool
    {
        if ($user->hasRole(['admin', 'super_admin'])) {
            return true;
        }

        return $user->activeDepots()->exists();
    }

    private function userCanDepotMenuAction(User $user, string $action): bool
    {
        if ($user->hasRole(['admin', 'super_admin'])) {
            return true;
        }

        return $user->canPerformDepotActionOnAny($action);
    }

    private function canSeeDepotRequestsMenu(User $user): bool
    {
        if ($user->hasRole(['admin', 'super_admin'])) {
            return true;
        }

        return $this->userCanDepotMenuAction($user, DepotRolePermissions::ACTION_REQUEST_CREATE)
            || $this->userCanDepotMenuAction($user, DepotRolePermissions::ACTION_REQUEST_APPROVE)
            || $this->userCanDepotMenuAction($user, DepotRolePermissions::ACTION_REQUEST_FULFILL)
            || $this->userCanDepotMenuAction($user, DepotRolePermissions::ACTION_VIEW);
    }
}
