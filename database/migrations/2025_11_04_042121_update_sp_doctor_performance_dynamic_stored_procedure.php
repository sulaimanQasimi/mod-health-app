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
        // Drop the procedure if it exists, then create/update it using DB::unprepared
        DB::unprepared('
            DROP PROCEDURE IF EXISTS sp_user_performance_dynamic;
            
            CREATE PROCEDURE sp_user_performance_dynamic(
                IN startDate DATE,
                IN endDate DATE,
                IN userId INT
            )
            BEGIN
                SELECT 
                    u.name AS User,
                    
                    -- Appointments count
                    (SELECT COUNT(*) 
                     FROM appointments a 
                     WHERE a.doctor_id = u.id 
                       AND a.date BETWEEN startDate AND endDate) AS Appointments,
                    
                    -- Prescriptions count
                    (SELECT COUNT(*) 
                     FROM prescriptions p 
                     WHERE p.created_by = u.id 
                       AND p.created_at BETWEEN startDate AND endDate) AS Prescriptions,
                    
                    -- Lab tests count
                    (SELECT COUNT(*) 
                     FROM patient_test_registrations ptr 
                     WHERE ptr.created_by = u.id 
                       AND ptr.registration_date BETWEEN startDate AND endDate) AS LabTests,
                    
                    -- Anesthesias count
                    (SELECT COUNT(*) 
                     FROM anesthesias an 
                     WHERE an.created_by = u.id 
                       AND an.created_at BETWEEN startDate AND endDate) AS Anesthesias,
                    
                    -- Total
                    (
                        (SELECT COUNT(*) 
                         FROM appointments a 
                         WHERE a.doctor_id = u.id 
                           AND a.date BETWEEN startDate AND endDate)
                        + (SELECT COUNT(*) 
                           FROM prescriptions p 
                           WHERE p.created_by = u.id 
                             AND p.created_at BETWEEN startDate AND endDate)
                        + (SELECT COUNT(*) 
                           FROM patient_test_registrations ptr 
                           WHERE ptr.created_by = u.id 
                             AND ptr.registration_date BETWEEN startDate AND endDate)
                        + (SELECT COUNT(*) 
                           FROM anesthesias an 
                           WHERE an.created_by = u.id 
                             AND an.created_at BETWEEN startDate AND endDate)
                    ) AS Total

                FROM users u
                WHERE userId IS NULL OR u.id = userId
                ORDER BY u.name;
            END
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the procedure if it exists
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_user_performance_dynamic');
    }
};
