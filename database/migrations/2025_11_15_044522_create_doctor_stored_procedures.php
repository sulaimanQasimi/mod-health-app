<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create stored procedure for creating a doctor
        DB::unprepared('
            DROP PROCEDURE IF EXISTS sp_doctor_create;
            
            CREATE PROCEDURE sp_doctor_create(
                IN p_name VARCHAR(255),
                IN p_gender ENUM("Male", "Female", "Other"),
                IN p_father_name VARCHAR(255),
                IN p_contact_number VARCHAR(255),
                IN p_address TEXT,
                IN p_specialization VARCHAR(255),
                IN p_qualification VARCHAR(255),
                IN p_room_no VARCHAR(255),
                IN p_account_type VARCHAR(255),
                IN p_clinic_type ENUM("hospital", "clinic"),
                IN p_join_date DATE,
                IN p_active_status BOOLEAN,
                IN p_branch_id BIGINT UNSIGNED,
                IN p_department_id BIGINT UNSIGNED,
                IN p_section_id BIGINT UNSIGNED,
                IN p_created_by INT,
                OUT p_doctor_id BIGINT UNSIGNED
            )
            BEGIN
                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    RESIGNAL;
                END;
                
                START TRANSACTION;
                
                -- Validation: Required fields
                IF p_name IS NULL OR p_name = "" THEN
                    SIGNAL SQLSTATE "45000" 
                    SET MESSAGE_TEXT = "Doctor name is required";
                END IF;
                
                IF p_branch_id IS NULL THEN
                    SIGNAL SQLSTATE "45000" 
                    SET MESSAGE_TEXT = "Branch ID is required";
                END IF;
                
                IF p_department_id IS NULL THEN
                    SIGNAL SQLSTATE "45000" 
                    SET MESSAGE_TEXT = "Department ID is required";
                END IF;
                
                IF p_section_id IS NULL THEN
                    SIGNAL SQLSTATE "45000" 
                    SET MESSAGE_TEXT = "Section ID is required";
                END IF;
                
                -- Validation: Foreign key constraints
                IF NOT EXISTS (SELECT 1 FROM branches WHERE id = p_branch_id AND deleted_at IS NULL) THEN
                    SIGNAL SQLSTATE "45000" 
                    SET MESSAGE_TEXT = "Invalid branch ID";
                END IF;
                
                IF NOT EXISTS (SELECT 1 FROM departments WHERE id = p_department_id AND deleted_at IS NULL) THEN
                    SIGNAL SQLSTATE "45000" 
                    SET MESSAGE_TEXT = "Invalid department ID";
                END IF;
                
                IF NOT EXISTS (SELECT 1 FROM sections WHERE id = p_section_id AND deleted_at IS NULL) THEN
                    SIGNAL SQLSTATE "45000" 
                    SET MESSAGE_TEXT = "Invalid section ID";
                END IF;
                
                -- Validation: Gender enum
                IF p_gender IS NOT NULL AND p_gender NOT IN ("Male", "Female", "Other") THEN
                    SIGNAL SQLSTATE "45000" 
                    SET MESSAGE_TEXT = "Invalid gender. Must be Male, Female, or Other";
                END IF;
                
                -- Validation: Clinic type enum
                IF p_clinic_type IS NOT NULL AND p_clinic_type NOT IN ("hospital", "clinic") THEN
                    SIGNAL SQLSTATE "45000" 
                    SET MESSAGE_TEXT = "Invalid clinic_type. Must be either hospital or clinic";
                END IF;
                
                -- Insert doctor
                INSERT INTO doctors (
                    name, gender, father_name, contact_number, address,
                    specialization, qualification, room_no, account_type,
                    clinic_type, join_date, active_status,
                    branch_id, department_id, section_id, created_by, created_at, updated_at
                ) VALUES (
                    p_name, p_gender, p_father_name, p_contact_number, p_address,
                    p_specialization, p_qualification, p_room_no, p_account_type,
                    p_clinic_type, p_join_date, COALESCE(p_active_status, TRUE),
                    p_branch_id, p_department_id, p_section_id, p_created_by, NOW(), NOW()
                );
                
                SET p_doctor_id = LAST_INSERT_ID();
                
                COMMIT;
            END
        ');
        
        // Create stored procedure for updating a doctor
        DB::unprepared('
            DROP PROCEDURE IF EXISTS sp_doctor_update;
            
            CREATE PROCEDURE sp_doctor_update(
                IN p_doctor_id BIGINT UNSIGNED,
                IN p_name VARCHAR(255),
                IN p_gender ENUM("Male", "Female", "Other"),
                IN p_father_name VARCHAR(255),
                IN p_contact_number VARCHAR(255),
                IN p_address TEXT,
                IN p_specialization VARCHAR(255),
                IN p_qualification VARCHAR(255),
                IN p_room_no VARCHAR(255),
                IN p_account_type VARCHAR(255),
                IN p_clinic_type ENUM("hospital", "clinic"),
                IN p_join_date DATE,
                IN p_active_status BOOLEAN,
                IN p_branch_id BIGINT UNSIGNED,
                IN p_department_id BIGINT UNSIGNED,
                IN p_section_id BIGINT UNSIGNED,
                IN p_updated_by INT,
                OUT p_success BOOLEAN
            )
            BEGIN
                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    RESIGNAL;
                END;
                
                START TRANSACTION;
                
                -- Validation: Doctor exists
                IF NOT EXISTS (SELECT 1 FROM doctors WHERE id = p_doctor_id AND deleted_at IS NULL) THEN
                    SIGNAL SQLSTATE "45000" 
                    SET MESSAGE_TEXT = "Doctor not found";
                END IF;
                
                -- Validation: Required fields
                IF p_name IS NULL OR p_name = "" THEN
                    SIGNAL SQLSTATE "45000" 
                    SET MESSAGE_TEXT = "Doctor name is required";
                END IF;
                
                IF p_branch_id IS NULL THEN
                    SIGNAL SQLSTATE "45000" 
                    SET MESSAGE_TEXT = "Branch ID is required";
                END IF;
                
                IF p_department_id IS NULL THEN
                    SIGNAL SQLSTATE "45000" 
                    SET MESSAGE_TEXT = "Department ID is required";
                END IF;
                
                IF p_section_id IS NULL THEN
                    SIGNAL SQLSTATE "45000" 
                    SET MESSAGE_TEXT = "Section ID is required";
                END IF;
                
                -- Validation: Foreign key constraints
                IF NOT EXISTS (SELECT 1 FROM branches WHERE id = p_branch_id AND deleted_at IS NULL) THEN
                    SIGNAL SQLSTATE "45000" 
                    SET MESSAGE_TEXT = "Invalid branch ID";
                END IF;
                
                IF NOT EXISTS (SELECT 1 FROM departments WHERE id = p_department_id AND deleted_at IS NULL) THEN
                    SIGNAL SQLSTATE "45000" 
                    SET MESSAGE_TEXT = "Invalid department ID";
                END IF;
                
                IF NOT EXISTS (SELECT 1 FROM sections WHERE id = p_section_id AND deleted_at IS NULL) THEN
                    SIGNAL SQLSTATE "45000" 
                    SET MESSAGE_TEXT = "Invalid section ID";
                END IF;
                
                -- Validation: Gender enum
                IF p_gender IS NOT NULL AND p_gender NOT IN ("Male", "Female", "Other") THEN
                    SIGNAL SQLSTATE "45000" 
                    SET MESSAGE_TEXT = "Invalid gender. Must be Male, Female, or Other";
                END IF;
                
                -- Validation: Clinic type enum
                IF p_clinic_type IS NOT NULL AND p_clinic_type NOT IN ("hospital", "clinic") THEN
                    SIGNAL SQLSTATE "45000" 
                    SET MESSAGE_TEXT = "Invalid clinic_type. Must be either hospital or clinic";
                END IF;
                
                -- Update doctor
                UPDATE doctors SET
                    name = p_name,
                    gender = p_gender,
                    father_name = p_father_name,
                    contact_number = p_contact_number,
                    address = p_address,
                    specialization = p_specialization,
                    qualification = p_qualification,
                    room_no = p_room_no,
                    account_type = p_account_type,
                    clinic_type = p_clinic_type,
                    join_date = p_join_date,
                    active_status = COALESCE(p_active_status, active_status),
                    branch_id = p_branch_id,
                    department_id = p_department_id,
                    section_id = p_section_id,
                    updated_by = p_updated_by,
                    updated_at = NOW()
                WHERE id = p_doctor_id AND deleted_at IS NULL;
                
                IF ROW_COUNT() = 0 THEN
                    SIGNAL SQLSTATE "45000" 
                    SET MESSAGE_TEXT = "No rows updated. Doctor may have been deleted.";
                END IF;
                
                SET p_success = TRUE;
                
                COMMIT;
            END
        ');
        
        // Create stored procedure for deleting a doctor (soft delete)
        DB::unprepared('
            DROP PROCEDURE IF EXISTS sp_doctor_delete;
            
            CREATE PROCEDURE sp_doctor_delete(
                IN p_doctor_id BIGINT UNSIGNED,
                IN p_deleted_by INT,
                OUT p_success BOOLEAN
            )
            BEGIN
                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    RESIGNAL;
                END;
                
                START TRANSACTION;
                
                -- Validation: Doctor exists
                IF NOT EXISTS (SELECT 1 FROM doctors WHERE id = p_doctor_id AND deleted_at IS NULL) THEN
                    SIGNAL SQLSTATE "45000" 
                    SET MESSAGE_TEXT = "Doctor not found or already deleted";
                END IF;
                
                -- Check if doctor has active appointments
                IF EXISTS (SELECT 1 FROM appointments WHERE doctor_id = p_doctor_id AND is_completed = 0 AND deleted_at IS NULL) THEN
                    SIGNAL SQLSTATE "45000" 
                    SET MESSAGE_TEXT = "Cannot delete doctor with active appointments";
                END IF;
                
                -- Soft delete doctor
                UPDATE doctors SET
                    deleted_at = NOW(),
                    deleted_by = p_deleted_by,
                    updated_at = NOW()
                WHERE id = p_doctor_id AND deleted_at IS NULL;
                
                IF ROW_COUNT() = 0 THEN
                    SIGNAL SQLSTATE "45000" 
                    SET MESSAGE_TEXT = "Failed to delete doctor";
                END IF;
                
                SET p_success = TRUE;
                
                COMMIT;
            END
        ');
        
        // Create stored procedure for changing doctor status
        DB::unprepared('
            DROP PROCEDURE IF EXISTS sp_doctor_change_status;
            
            CREATE PROCEDURE sp_doctor_change_status(
                IN p_doctor_id BIGINT UNSIGNED,
                IN p_active_status BOOLEAN,
                IN p_updated_by INT,
                OUT p_success BOOLEAN
            )
            BEGIN
                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    RESIGNAL;
                END;
                
                START TRANSACTION;
                
                -- Validation: Doctor exists
                IF NOT EXISTS (SELECT 1 FROM doctors WHERE id = p_doctor_id AND deleted_at IS NULL) THEN
                    SIGNAL SQLSTATE "45000" 
                    SET MESSAGE_TEXT = "Doctor not found";
                END IF;
                
                -- Validation: Status is not NULL
                IF p_active_status IS NULL THEN
                    SIGNAL SQLSTATE "45000" 
                    SET MESSAGE_TEXT = "Active status is required";
                END IF;
                
                -- Update status
                UPDATE doctors SET
                    active_status = p_active_status,
                    updated_by = p_updated_by,
                    updated_at = NOW()
                WHERE id = p_doctor_id AND deleted_at IS NULL;
                
                IF ROW_COUNT() = 0 THEN
                    SIGNAL SQLSTATE "45000" 
                    SET MESSAGE_TEXT = "Failed to update doctor status";
                END IF;
                
                SET p_success = TRUE;
                
                COMMIT;
            END
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_doctor_create');
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_doctor_update');
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_doctor_delete');
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_doctor_change_status');
    }
};
