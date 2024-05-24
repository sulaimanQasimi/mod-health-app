<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([

            ProvinceSeeder::class,
            DistrictSeeder::class,
            BranchSeeder::class,
            DepartmentSeeder::class,
            SectionSeeder::class,
            UserSeeder::class,
            DoctorSeeder::class,
            RecipientSeeder::class,
            LabTypeSectionSeeder::class,
            LabTypeSeeder::class,
            FloorSeeder::class,
            RoomSeeder::class,
            BedSeeder::class,
            RelationSeeder::class,
            PatientSeeder::class,
            OperationTypeSeeder::class,
            RoleSeeder::class,
            ModalHasRoleSeeder::class,
            PermissionSeeder::class,
            RoleHasPermissionSeeder::class,



        ]);
    }
}
