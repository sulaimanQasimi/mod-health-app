<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add missing secondary indexes across clinical / inventory tables.
 *
 * Based on a live SHOW INDEX audit of unindexed *_id, status/flag, and
 * common listing filter columns. Idempotent via named-index existence checks.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->addIndexes('users', [
            ['columns' => ['branch_id'], 'name' => 'users_branch_id_index'],
            ['columns' => ['status'], 'name' => 'users_status_index'],
            ['columns' => ['branch_id', 'status'], 'name' => 'users_branch_id_status_index'],
        ]);

        $this->addIndexes('districts', [
            ['columns' => ['province_id'], 'name' => 'districts_province_id_index'],
        ]);

        $this->addIndexes('departments', [
            ['columns' => ['branch_id'], 'name' => 'departments_branch_id_index'],
        ]);

        $this->addIndexes('sections', [
            ['columns' => ['branch_id'], 'name' => 'sections_branch_id_index'],
        ]);

        $this->addIndexes('depots', [
            ['columns' => ['department_id'], 'name' => 'depots_department_id_index'],
            ['columns' => ['branch_id'], 'name' => 'depots_branch_id_index'],
            ['columns' => ['pharmacy_id'], 'name' => 'depots_pharmacy_id_index'],
            ['columns' => ['parent_depot_id'], 'name' => 'depots_parent_depot_id_index'],
            ['columns' => ['is_active'], 'name' => 'depots_is_active_index'],
            ['columns' => ['branch_id', 'is_active'], 'name' => 'depots_branch_id_is_active_index'],
        ]);

        $this->addIndexes('depot_users', [
            ['columns' => ['is_active'], 'name' => 'depot_users_is_active_index'],
        ]);

        $this->addIndexes('patients', [
            ['columns' => ['militery_type_id'], 'name' => 'patients_militery_type_id_index'],
            ['columns' => ['status'], 'name' => 'patients_status_index'],
            ['columns' => ['phone'], 'name' => 'patients_phone_index'],
            ['columns' => ['nid'], 'name' => 'patients_nid_index'],
            ['columns' => ['branch_id', 'created_at'], 'name' => 'patients_branch_id_created_at_index'],
        ]);

        // referred_by may exist without FK
        if (Schema::hasColumn('patients', 'referred_by')) {
            $this->addIndexes('patients', [
                ['columns' => ['referred_by'], 'name' => 'patients_referred_by_index'],
            ]);
        }

        $this->addIndexes('appointments', [
            ['columns' => ['is_completed'], 'name' => 'appointments_is_completed_index'],
            ['columns' => ['date'], 'name' => 'appointments_date_index'],
            ['columns' => ['branch_id', 'is_completed'], 'name' => 'appointments_branch_id_is_completed_index'],
            ['columns' => ['branch_id', 'doctor_id'], 'name' => 'appointments_branch_id_doctor_id_index'],
            ['columns' => ['branch_id', 'department_id'], 'name' => 'appointments_branch_id_department_id_index'],
            ['columns' => ['branch_id', 'date'], 'name' => 'appointments_branch_id_date_index'],
        ]);

        $this->addIndexes('hospitalizations', [
            ['columns' => ['is_discharged'], 'name' => 'hospitalizations_is_discharged_index'],
            ['columns' => ['branch_id', 'is_discharged'], 'name' => 'hospitalizations_branch_id_is_discharged_index'],
            ['columns' => ['patient_id', 'is_discharged'], 'name' => 'hospitalizations_patient_id_is_discharged_index'],
        ]);

        if (Schema::hasColumn('hospitalizations', 'i_c_u_id')) {
            $this->addIndexes('hospitalizations', [
                ['columns' => ['i_c_u_id'], 'name' => 'hospitalizations_i_c_u_id_index'],
            ]);
        }

        if (Schema::hasColumn('hospitalizations', 'food_type_id')) {
            $this->addIndexes('hospitalizations', [
                ['columns' => ['food_type_id'], 'name' => 'hospitalizations_food_type_id_index'],
            ]);
        }

        $this->addIndexes('under_reviews', [
            ['columns' => ['is_discharged'], 'name' => 'under_reviews_is_discharged_index'],
            ['columns' => ['operation_id'], 'name' => 'under_reviews_operation_id_index'],
            ['columns' => ['prescription_id'], 'name' => 'under_reviews_prescription_id_index'],
            ['columns' => ['hospitalization_id'], 'name' => 'under_reviews_hospitalization_id_index'],
            ['columns' => ['branch_id', 'is_discharged'], 'name' => 'under_reviews_branch_id_is_discharged_index'],
        ]);

        $this->addIndexes('i_c_u_s', [
            ['columns' => ['status'], 'name' => 'i_c_u_s_status_index'],
            ['columns' => ['is_discharged'], 'name' => 'i_c_u_s_is_discharged_index'],
            ['columns' => ['branch_id', 'status'], 'name' => 'i_c_u_s_branch_id_status_index'],
            ['columns' => ['branch_id', 'is_discharged'], 'name' => 'i_c_u_s_branch_id_is_discharged_index'],
        ]);

        $this->addIndexes('anesthesias', [
            ['columns' => ['status'], 'name' => 'anesthesias_status_index'],
            ['columns' => ['date'], 'name' => 'anesthesias_date_index'],
            ['columns' => ['branch_id', 'status'], 'name' => 'anesthesias_branch_id_status_index'],
        ]);

        if (Schema::hasColumn('anesthesias', 'operation_assistants_id')) {
            $this->addIndexes('anesthesias', [
                ['columns' => ['operation_assistants_id'], 'name' => 'anesthesias_operation_assistants_id_index'],
            ]);
        }

        $this->addIndexes('p_a_c_u_s', [
            ['columns' => ['status'], 'name' => 'p_a_c_u_s_status_index'],
            ['columns' => ['branch_id', 'status'], 'name' => 'p_a_c_u_s_branch_id_status_index'],
        ]);

        $this->addIndexes('blood_banks', [
            ['columns' => ['status'], 'name' => 'blood_banks_status_index'],
            ['columns' => ['branch_id', 'status'], 'name' => 'blood_banks_branch_id_status_index'],
        ]);

        $this->addIndexes('prescriptions', [
            ['columns' => ['is_completed'], 'name' => 'prescriptions_is_completed_index'],
            ['columns' => ['branch_id', 'is_completed'], 'name' => 'prescriptions_branch_id_is_completed_index'],
        ]);

        if (Schema::hasColumn('prescriptions', 'pharmacy_id')) {
            $this->addIndexes('prescriptions', [
                ['columns' => ['pharmacy_id', 'is_completed'], 'name' => 'prescriptions_pharmacy_id_is_completed_index'],
            ]);
        }

        $this->addIndexes('prescription_stocks', [
            ['columns' => ['medicine_id'], 'name' => 'prescription_stocks_medicine_id_index'],
            ['columns' => ['branch_id'], 'name' => 'prescription_stocks_branch_id_index'],
            ['columns' => ['pharmacy_id'], 'name' => 'prescription_stocks_pharmacy_id_index'],
        ]);

        $this->addIndexes('printed_numbers', [
            ['columns' => ['date'], 'name' => 'printed_numbers_date_index'],
            ['columns' => ['department_id', 'date'], 'name' => 'printed_numbers_department_id_date_index'],
            ['columns' => ['patient_id', 'date', 'department_id'], 'name' => 'printed_numbers_patient_date_department_index'],
        ]);

        $this->addIndexes('beds', [
            ['columns' => ['is_occupied'], 'name' => 'beds_is_occupied_index'],
            ['columns' => ['room_id', 'is_occupied'], 'name' => 'beds_room_id_is_occupied_index'],
        ]);

        $this->addIndexes('consultations', [
            ['columns' => ['department_id'], 'name' => 'consultations_department_id_index'],
            ['columns' => ['doctor_id'], 'name' => 'consultations_doctor_id_index'],
            ['columns' => ['date'], 'name' => 'consultations_date_index'],
        ]);

        $this->addIndexes('dentist_registrations', [
            ['columns' => ['status'], 'name' => 'dentist_registrations_status_index'],
            ['columns' => ['branch_id', 'status'], 'name' => 'dentist_registrations_branch_id_status_index'],
        ]);

        $this->addIndexes('dental_treatments', [
            ['columns' => ['status'], 'name' => 'dental_treatments_status_index'],
        ]);

        $this->addIndexes('nephrology_registrations', [
            ['columns' => ['status'], 'name' => 'nephrology_registrations_status_index'],
            ['columns' => ['branch_id', 'status'], 'name' => 'nephrology_registrations_branch_id_status_index'],
        ]);

        $this->addIndexes('hemodialysis_sessions', [
            ['columns' => ['status'], 'name' => 'hemodialysis_sessions_status_index'],
            ['columns' => ['branch_id', 'status'], 'name' => 'hemodialysis_sessions_branch_id_status_index'],
        ]);

        $this->addIndexes('physiotherapy_procedures', [
            ['columns' => ['status'], 'name' => 'physiotherapy_procedures_status_index'],
            ['columns' => ['appointment_id', 'status'], 'name' => 'physiotherapy_procedures_appointment_id_status_index'],
        ]);

        $this->addIndexes('physiotherapy_procedure_reviews', [
            ['columns' => ['status'], 'name' => 'physiotherapy_procedure_reviews_status_index'],
        ]);

        $this->addIndexes('prosthetic_prescriptions', [
            ['columns' => ['status'], 'name' => 'prosthetic_prescriptions_status_index'],
        ]);

        $this->addIndexes('prosthetic_estimates', [
            ['columns' => ['status'], 'name' => 'prosthetic_estimates_status_index'],
        ]);

        $this->addIndexes('prosthetic_work_orders', [
            ['columns' => ['status'], 'name' => 'prosthetic_work_orders_status_index'],
            ['columns' => ['technician_user_id'], 'name' => 'prosthetic_work_orders_technician_user_id_index'],
        ]);

        $this->addIndexes('prosthetic_cases', [
            ['columns' => ['priority'], 'name' => 'prosthetic_cases_priority_index'],
        ]);

        $this->addIndexes('prosthetic_component_catalog', [
            ['columns' => ['is_active'], 'name' => 'prosthetic_component_catalog_is_active_index'],
        ]);

        $this->addIndexes('patient_test_registrations', [
            ['columns' => ['ref_no'], 'name' => 'patient_test_registrations_ref_no_index'],
            ['columns' => ['branch_id'], 'name' => 'patient_test_registrations_branch_id_index'],
            ['columns' => ['branch_id', 'status'], 'name' => 'patient_test_registrations_branch_id_status_index'],
        ]);

        $this->addIndexes('patient_test_results', [
            ['columns' => ['ref_no'], 'name' => 'patient_test_results_ref_no_index'],
        ]);

        $this->addIndexes('incomes', [
            ['columns' => ['pharmacy_id'], 'name' => 'incomes_pharmacy_id_index'],
        ]);

        if (Schema::hasTable('outcome')) {
            $this->addIndexes('outcome', [
                ['columns' => ['pharmacy_id'], 'name' => 'outcome_pharmacy_id_index'],
                ['columns' => ['medicine_id'], 'name' => 'outcome_medicine_id_index'],
            ]);
        }

        $this->addIndexes('doctors', [
            ['columns' => ['status'], 'name' => 'doctors_status_index'],
        ]);

        $this->addIndexes('tools', [
            ['columns' => ['is_active'], 'name' => 'tools_is_active_index'],
        ]);

        $this->addIndexes('units', [
            ['columns' => ['is_active'], 'name' => 'units_is_active_index'],
        ]);

        if (Schema::hasColumn('visits', 'food_type_id')) {
            $this->addIndexes('visits', [
                ['columns' => ['food_type_id'], 'name' => 'visits_food_type_id_index'],
            ]);
        }

        $this->addIndexes('vital_sign_schedules', [
            ['columns' => ['date'], 'name' => 'vital_sign_schedules_date_index'],
        ]);

        if (Schema::hasColumn('blood_patient_samples', 'sample_id')) {
            $this->addIndexes('blood_patient_samples', [
                ['columns' => ['sample_id'], 'name' => 'blood_patient_samples_sample_id_index'],
            ]);
        }

        if (Schema::hasTable('spiete_backups') && Schema::hasColumn('spiete_backups', 'status')) {
            $this->addIndexes('spiete_backups', [
                ['columns' => ['status'], 'name' => 'spiete_backups_status_index'],
            ]);
        }

        // Lookup tables that were PK-only but are joined frequently
        if (Schema::hasTable('medicines') && Schema::hasColumn('medicines', 'medicine_type_id')) {
            $this->addIndexes('medicines', [
                ['columns' => ['medicine_type_id'], 'name' => 'medicines_medicine_type_id_index'],
            ]);
        }

        if (Schema::hasTable('pharmacies') && Schema::hasColumn('pharmacies', 'branch_id')) {
            $this->addIndexes('pharmacies', [
                ['columns' => ['branch_id'], 'name' => 'pharmacies_branch_id_index'],
            ]);
        }
    }

    public function down(): void
    {
        foreach ($this->allIndexNames() as $table => $indexes) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($table, $indexes) {
                foreach ($indexes as $index) {
                    if ($this->indexExists($table, $index)) {
                        $blueprint->dropIndex($index);
                    }
                }
            });
        }
    }

    /**
     * @param  array<int, array{columns: array<int, string>, name: string}>  $indexes
     */
    private function addIndexes(string $table, array $indexes): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($table, $indexes) {
            foreach ($indexes as $index) {
                foreach ($index['columns'] as $column) {
                    if (! Schema::hasColumn($table, $column)) {
                        continue 2;
                    }

                    if (! $this->columnIsIndexable($table, $column)) {
                        continue 2;
                    }
                }

                if ($this->indexExists($table, $index['name'])) {
                    continue;
                }

                $blueprint->index($index['columns'], $index['name']);
            }
        });
    }

    private function columnIsIndexable(string $table, string $column): bool
    {
        $connection = Schema::getConnection();
        $result = $connection->select(
            'SHOW COLUMNS FROM '.$connection->getTablePrefix().$table.' WHERE Field = ?',
            [$column]
        );

        if ($result === []) {
            return false;
        }

        $type = strtolower((string) ($result[0]->Type ?? ''));

        // MySQL cannot index unbounded TEXT/BLOB without a prefix length.
        if (str_contains($type, 'text') || str_contains($type, 'blob')) {
            return false;
        }

        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function allIndexNames(): array
    {
        return [
            'users' => ['users_branch_id_index', 'users_status_index', 'users_branch_id_status_index'],
            'districts' => ['districts_province_id_index'],
            'departments' => ['departments_branch_id_index'],
            'sections' => ['sections_branch_id_index'],
            'depots' => [
                'depots_department_id_index',
                'depots_branch_id_index',
                'depots_pharmacy_id_index',
                'depots_parent_depot_id_index',
                'depots_is_active_index',
                'depots_branch_id_is_active_index',
            ],
            'depot_users' => ['depot_users_is_active_index'],
            'patients' => [
                'patients_militery_type_id_index',
                'patients_status_index',
                'patients_phone_index',
                'patients_nid_index',
                'patients_branch_id_created_at_index',
                'patients_referred_by_index',
            ],
            'appointments' => [
                'appointments_is_completed_index',
                'appointments_date_index',
                'appointments_branch_id_is_completed_index',
                'appointments_branch_id_doctor_id_index',
                'appointments_branch_id_department_id_index',
                'appointments_branch_id_date_index',
            ],
            'hospitalizations' => [
                'hospitalizations_is_discharged_index',
                'hospitalizations_branch_id_is_discharged_index',
                'hospitalizations_patient_id_is_discharged_index',
                'hospitalizations_i_c_u_id_index',
                'hospitalizations_food_type_id_index',
            ],
            'under_reviews' => [
                'under_reviews_is_discharged_index',
                'under_reviews_operation_id_index',
                'under_reviews_prescription_id_index',
                'under_reviews_hospitalization_id_index',
                'under_reviews_branch_id_is_discharged_index',
            ],
            'i_c_u_s' => [
                'i_c_u_s_status_index',
                'i_c_u_s_is_discharged_index',
                'i_c_u_s_branch_id_status_index',
                'i_c_u_s_branch_id_is_discharged_index',
            ],
            'anesthesias' => [
                'anesthesias_status_index',
                'anesthesias_date_index',
                'anesthesias_branch_id_status_index',
                'anesthesias_operation_assistants_id_index',
            ],
            'p_a_c_u_s' => ['p_a_c_u_s_status_index', 'p_a_c_u_s_branch_id_status_index'],
            'blood_banks' => ['blood_banks_status_index', 'blood_banks_branch_id_status_index'],
            'prescriptions' => [
                'prescriptions_is_completed_index',
                'prescriptions_branch_id_is_completed_index',
                'prescriptions_pharmacy_id_is_completed_index',
            ],
            'prescription_stocks' => [
                'prescription_stocks_medicine_id_index',
                'prescription_stocks_branch_id_index',
                'prescription_stocks_pharmacy_id_index',
            ],
            'printed_numbers' => [
                'printed_numbers_date_index',
                'printed_numbers_department_id_date_index',
                'printed_numbers_patient_date_department_index',
            ],
            'beds' => ['beds_is_occupied_index', 'beds_room_id_is_occupied_index'],
            'consultations' => [
                'consultations_department_id_index',
                'consultations_doctor_id_index',
                'consultations_date_index',
            ],
            'dentist_registrations' => [
                'dentist_registrations_status_index',
                'dentist_registrations_branch_id_status_index',
            ],
            'dental_treatments' => ['dental_treatments_status_index'],
            'nephrology_registrations' => [
                'nephrology_registrations_status_index',
                'nephrology_registrations_branch_id_status_index',
            ],
            'hemodialysis_sessions' => [
                'hemodialysis_sessions_status_index',
                'hemodialysis_sessions_branch_id_status_index',
            ],
            'physiotherapy_procedures' => [
                'physiotherapy_procedures_status_index',
                'physiotherapy_procedures_appointment_id_status_index',
            ],
            'physiotherapy_procedure_reviews' => ['physiotherapy_procedure_reviews_status_index'],
            'prosthetic_prescriptions' => ['prosthetic_prescriptions_status_index'],
            'prosthetic_estimates' => ['prosthetic_estimates_status_index'],
            'prosthetic_work_orders' => [
                'prosthetic_work_orders_status_index',
                'prosthetic_work_orders_technician_user_id_index',
            ],
            'prosthetic_cases' => ['prosthetic_cases_priority_index'],
            'prosthetic_component_catalog' => ['prosthetic_component_catalog_is_active_index'],
            'patient_test_registrations' => [
                'patient_test_registrations_ref_no_index',
                'patient_test_registrations_branch_id_index',
                'patient_test_registrations_branch_id_status_index',
            ],
            'patient_test_results' => ['patient_test_results_ref_no_index'],
            'incomes' => ['incomes_pharmacy_id_index'],
            'outcome' => ['outcome_pharmacy_id_index', 'outcome_medicine_id_index'],
            'doctors' => ['doctors_status_index'],
            'tools' => ['tools_is_active_index'],
            'units' => ['units_is_active_index'],
            'visits' => ['visits_food_type_id_index'],
            'vital_sign_schedules' => ['vital_sign_schedules_date_index'],
            'blood_patient_samples' => ['blood_patient_samples_sample_id_index'],
            'spiete_backups' => ['spiete_backups_status_index'],
            'medicines' => ['medicines_medicine_type_id_index'],
            'pharmacies' => ['pharmacies_branch_id_index'],
        ];
    }

    private function indexExists(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'mysql') {
            $result = $connection->select(
                'SHOW INDEX FROM '.$connection->getTablePrefix().$table.' WHERE Key_name = ?',
                [$index]
            );

            return count($result) > 0;
        }

        if ($driver === 'pgsql') {
            $result = $connection->select(
                'SELECT 1 FROM pg_indexes WHERE tablename = ? AND indexname = ?',
                [$table, $index]
            );

            return count($result) > 0;
        }

        if ($driver === 'sqlite') {
            $result = $connection->select("PRAGMA index_list('{$table}')");

            foreach ($result as $row) {
                if (($row->name ?? null) === $index) {
                    return true;
                }
            }

            return false;
        }

        return false;
    }
};
