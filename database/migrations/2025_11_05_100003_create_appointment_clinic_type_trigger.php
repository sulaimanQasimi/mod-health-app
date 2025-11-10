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
        // Trigger for INSERT - sets clinic_type when appointment is created
        DB::unprepared('
            DROP TRIGGER IF EXISTS set_appointment_clinic_type_on_insert;
            
            CREATE TRIGGER set_appointment_clinic_type_on_insert
            BEFORE INSERT ON appointments
            FOR EACH ROW
            BEGIN
                -- Set clinic_type from doctor\'s clinic_type if created_by is provided
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

        // Trigger for UPDATE - updates clinic_type if doctor_id is changed
        DB::unprepared('
            DROP TRIGGER IF EXISTS set_appointment_clinic_type_on_update;
            
            CREATE TRIGGER set_appointment_clinic_type_on_update
            BEFORE UPDATE ON appointments
            FOR EACH ROW
            BEGIN
                -- Update clinic_type if doctor_id has changed
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS set_appointment_clinic_type_on_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS set_appointment_clinic_type_on_update');
    }
};

