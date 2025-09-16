<?php

namespace App\Console\Commands;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;

class TestNursingAssessmentPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:nursing-assessment-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test nursing assessment permissions and role assignments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Nursing Assessment Permissions...');
        $this->newLine();

        // Test permissions creation
        $permissions = Permission::where('name', 'like', '%nursing_assessment%')
            ->orWhere('name', 'like', '%nursing-assessments%')
            ->get();

        $this->info('Found ' . $permissions->count() . ' nursing assessment permissions:');
        foreach ($permissions as $permission) {
            $this->line('- ' . $permission->name . ' (' . $permission->name_dr . ')');
        }
        $this->newLine();

        // Show all roles first
        $this->info('Available Roles:');
        $allRoles = Role::all(['id', 'name']);
        foreach ($allRoles as $role) {
            $this->line('- ID: ' . $role->id . ' - Name: ' . $role->name);
        }
        $this->newLine();

        // Test role assignments
        $roles = Role::whereIn('id', [1, 2, 3, 4, 10])->get();
        
        foreach ($roles as $role) {
            $rolePermissions = $role->permissions()
                ->where('name', 'like', '%nursing_assessment%')
                ->orWhere('name', 'like', '%nursing-assessments%')
                ->get();
            
            $this->info($role->name . ' (' . $role->id . ') has ' . $rolePermissions->count() . ' nursing assessment permissions:');
            foreach ($rolePermissions as $permission) {
                $this->line('  - ' . $permission->name);
            }
            $this->newLine();
        }

        // Test policy authorization
        $this->info('Testing Policy Authorization...');
        
        // Get a test user with nurse role
        $nurseUser = User::whereHas('roles', function($query) {
            $query->where('name', 'nurse');
        })->first();

        if ($nurseUser) {
            $this->info('Testing with nurse user: ' . $nurseUser->name);
            
            // Test create permission
            $canCreate = $nurseUser->can('create', \App\Models\NursingAssessment::class);
            $this->line('Can create nursing assessment: ' . ($canCreate ? 'YES' : 'NO'));
            
            // Test view permission
            $canView = $nurseUser->can('viewAny', \App\Models\NursingAssessment::class);
            $this->line('Can view nursing assessments: ' . ($canView ? 'YES' : 'NO'));
        } else {
            $this->warn('No nurse user found for testing');
        }

        $this->newLine();
        $this->info('Permission testing completed!');
    }
}