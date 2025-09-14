<?php

namespace Database\Seeders;

use App\Models\Nurse;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class NurseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first admin user for created_by field
        $adminUser = User::whereHas('roles', function ($query) {
            $query->where('name', 'super_admin');
        })->first();

        if (!$adminUser) {
            $adminUser = User::first();
        }

        if (!$adminUser) {
            // If no users exist, skip seeding
            $this->command->warn('No users found. Skipping nurse seeding.');
            return;
        }

        // Get departments for assignment
        $departments = Department::all();

        $nurses = [
            [
                'first_name' => 'Sarah',
                'last_name' => 'Johnson',
                'gender' => 'female',
                'date_of_birth' => '1985-03-15',
                'phone' => '+1-555-0101',
                'email' => 'sarah.johnson@hospital.com',
                'address' => '123 Main St, City, State 12345',
                'employee_id' => 'NUR001',
                'department_id' => $departments->first()?->id,
                'specialization' => 'ICU',
                'shift' => 'morning',
                'employment_status' => 'active',
                'date_of_joining' => '2020-01-15',
                'created_by' => $adminUser->id,
            ],
            [
                'first_name' => 'Michael',
                'last_name' => 'Brown',
                'gender' => 'male',
                'date_of_birth' => '1988-07-22',
                'phone' => '+1-555-0102',
                'email' => 'michael.brown@hospital.com',
                'address' => '456 Oak Ave, City, State 12345',
                'employee_id' => 'NUR002',
                'department_id' => $departments->skip(1)->first()?->id,
                'specialization' => 'Pediatrics',
                'shift' => 'evening',
                'employment_status' => 'active',
                'date_of_joining' => '2019-06-10',
                'created_by' => $adminUser->id,
            ],
            [
                'first_name' => 'Emily',
                'last_name' => 'Davis',
                'gender' => 'female',
                'date_of_birth' => '1990-11-08',
                'phone' => '+1-555-0103',
                'email' => 'emily.davis@hospital.com',
                'address' => '789 Pine St, City, State 12345',
                'employee_id' => 'NUR003',
                'department_id' => $departments->first()?->id,
                'specialization' => 'Surgery',
                'shift' => 'night',
                'employment_status' => 'active',
                'date_of_joining' => '2021-03-20',
                'created_by' => $adminUser->id,
            ],
            [
                'first_name' => 'David',
                'last_name' => 'Wilson',
                'gender' => 'male',
                'date_of_birth' => '1983-05-12',
                'phone' => '+1-555-0104',
                'email' => 'david.wilson@hospital.com',
                'address' => '321 Elm St, City, State 12345',
                'employee_id' => 'NUR004',
                'department_id' => $departments->skip(2)->first()?->id,
                'specialization' => 'Emergency',
                'shift' => 'morning',
                'employment_status' => 'on_leave',
                'date_of_joining' => '2018-09-05',
                'created_by' => $adminUser->id,
            ],
            [
                'first_name' => 'Lisa',
                'last_name' => 'Anderson',
                'gender' => 'female',
                'date_of_birth' => '1987-12-30',
                'phone' => '+1-555-0105',
                'email' => 'lisa.anderson@hospital.com',
                'address' => '654 Maple Dr, City, State 12345',
                'employee_id' => 'NUR005',
                'department_id' => $departments->first()?->id,
                'specialization' => 'Cardiology',
                'shift' => 'evening',
                'employment_status' => 'active',
                'date_of_joining' => '2020-08-12',
                'created_by' => $adminUser->id,
            ],
        ];

        foreach ($nurses as $nurseData) {
            // Temporarily disable the boot method for seeding
            Nurse::unsetEventDispatcher();
            Nurse::create($nurseData);
            Nurse::setEventDispatcher(app('events'));
        }
    }
}
