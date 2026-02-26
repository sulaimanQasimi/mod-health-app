<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * When created_by user has clinic_type 'both', do NOT overwrite appointment clinic_type
     * (appointment already has hospital/clinic from form). Only set from user when user is hospital or clinic.
     */
    public function up(): void
    {
        DB::unprepared('
            DROP TRIGGER IF EXISTS set_appointment_clinic_type_on_insert;

            CREATE TRIGGER set_appointment_clinic_type_on_insert
            BEFORE INSERT ON appointments
            FOR EACH ROW
            BEGIN
                IF NEW.created_by IS NOT NULL THEN
                    SET @user_clinic_type = (
                        SELECT clinic_type
                        FROM users
                        WHERE id = NEW.created_by
                        AND deleted_at IS NULL
                        LIMIT 1
                    );
                    -- Only overwrite when user has a single type (hospital or clinic). When user is "both", keep app-set value.
                    IF @user_clinic_type IN ("hospital", "clinic") THEN
                        SET NEW.clinic_type = @user_clinic_type;
                    END IF;
                END IF;
            END
        ');

        DB::unprepared('
            DROP TRIGGER IF EXISTS set_appointment_clinic_type_on_update;

            CREATE TRIGGER set_appointment_clinic_type_on_update
            BEFORE UPDATE ON appointments
            FOR EACH ROW
            BEGIN
                IF NEW.created_by IS NOT NULL AND (OLD.created_by IS NULL OR NEW.created_by != OLD.created_by) THEN
                    SET @user_clinic_type = (
                        SELECT clinic_type
                        FROM users
                        WHERE id = NEW.created_by
                        AND deleted_at IS NULL
                        LIMIT 1
                    );
                    IF @user_clinic_type IN ("hospital", "clinic") THEN
                        SET NEW.clinic_type = @user_clinic_type;
                    END IF;
                END IF;
            END
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore original triggers that always set from user
        DB::unprepared('
            DROP TRIGGER IF EXISTS set_appointment_clinic_type_on_insert;

            CREATE TRIGGER set_appointment_clinic_type_on_insert
            BEFORE INSERT ON appointments
            FOR EACH ROW
            BEGIN
                IF NEW.created_by IS NOT NULL THEN
                    SET NEW.clinic_type = (
                        SELECT clinic_type
                        FROM users
                        WHERE id = NEW.created_by
                        AND deleted_at IS NULL
                        LIMIT 1
                    );
                END IF;
            END
        ');

        DB::unprepared('
            DROP TRIGGER IF EXISTS set_appointment_clinic_type_on_update;

            CREATE TRIGGER set_appointment_clinic_type_on_update
            BEFORE UPDATE ON appointments
            FOR EACH ROW
            BEGIN
                IF NEW.created_by IS NOT NULL AND (OLD.created_by IS NULL OR NEW.created_by != OLD.created_by) THEN
                    SET NEW.clinic_type = (
                        SELECT clinic_type
                        FROM users
                        WHERE id = NEW.created_by
                        AND deleted_at IS NULL
                        LIMIT 1
                    );
                END IF;
            END
        ');
    }
};
