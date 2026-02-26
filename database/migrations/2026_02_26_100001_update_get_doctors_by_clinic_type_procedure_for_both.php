<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * When p_clinic_type = 'both', return all doctors (hospital and clinic).
     */
    public function up(): void
    {
        DB::unprepared('
            DROP PROCEDURE IF EXISTS only_get_docters_base_on_clinic_type;

            CREATE PROCEDURE only_get_docters_base_on_clinic_type(
                IN p_clinic_type VARCHAR(20),
                IN p_status INT,
                IN p_branch_id INT,
                IN p_department_id INT
            )
            BEGIN
                IF p_status IS NULL THEN
                    SET p_status = 1;
                END IF;

                IF p_clinic_type IS NULL OR p_clinic_type = "" THEN
                    SIGNAL SQLSTATE "45000"
                    SET MESSAGE_TEXT = "Clinic type parameter cannot be NULL or empty";
                END IF;

                IF p_clinic_type NOT IN ("hospital", "clinic", "both") THEN
                    SIGNAL SQLSTATE "45000"
                    SET MESSAGE_TEXT = "Invalid clinic_type. Must be hospital, clinic, or both";
                END IF;

                SELECT
                    u.id,
                    u.name,
                    u.last_name,
                    CONCAT(COALESCE(u.name, ""), " ", COALESCE(u.last_name, "")) AS full_name,
                    u.email,
                    u.clinic_type,
                    u.status,
                    u.branch_id,
                    b.name AS branch_name,
                    u.department_id,
                    d.name AS department_name,
                    u.section_id,
                    s.name AS section_name,
                    u.created_at,
                    u.updated_at
                FROM users u
                LEFT JOIN branches b ON u.branch_id = b.id
                LEFT JOIN departments d ON u.department_id = d.id
                LEFT JOIN sections s ON u.section_id = s.id
                WHERE u.is_doctor = 1
                  AND (p_clinic_type = "both" OR u.clinic_type = p_clinic_type)
                  AND u.status = p_status
                  AND u.deleted_at IS NULL
                  AND (p_branch_id IS NULL OR u.branch_id = p_branch_id)
                  AND (p_department_id IS NULL OR u.department_id = p_department_id)
                ORDER BY u.name ASC, u.last_name ASC;
            END
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('
            DROP PROCEDURE IF EXISTS only_get_docters_base_on_clinic_type;

            CREATE PROCEDURE only_get_docters_base_on_clinic_type(
                IN p_clinic_type VARCHAR(20),
                IN p_status INT,
                IN p_branch_id INT,
                IN p_department_id INT
            )
            BEGIN
                IF p_status IS NULL THEN
                    SET p_status = 1;
                END IF;

                IF p_clinic_type IS NULL OR p_clinic_type = "" THEN
                    SIGNAL SQLSTATE "45000"
                    SET MESSAGE_TEXT = "Clinic type parameter cannot be NULL or empty";
                END IF;

                IF p_clinic_type NOT IN ("hospital", "clinic") THEN
                    SIGNAL SQLSTATE "45000"
                    SET MESSAGE_TEXT = "Invalid clinic_type. Must be either \'hospital\' or \'clinic\'";
                END IF;

                SELECT
                    u.id,
                    u.name,
                    u.last_name,
                    CONCAT(COALESCE(u.name, ""), " ", COALESCE(u.last_name, "")) AS full_name,
                    u.email,
                    u.clinic_type,
                    u.status,
                    u.branch_id,
                    b.name AS branch_name,
                    u.department_id,
                    d.name AS department_name,
                    u.section_id,
                    s.name AS section_name,
                    u.created_at,
                    u.updated_at
                FROM users u
                LEFT JOIN branches b ON u.branch_id = b.id
                LEFT JOIN departments d ON u.department_id = d.id
                LEFT JOIN sections s ON u.section_id = s.id
                WHERE u.is_doctor = 1
                  AND u.clinic_type = p_clinic_type
                  AND u.status = p_status
                  AND u.deleted_at IS NULL
                  AND (p_branch_id IS NULL OR u.branch_id = p_branch_id)
                  AND (p_department_id IS NULL OR u.department_id = p_department_id)
                ORDER BY u.name ASC, u.last_name ASC;
            END
        ');
    }
};
