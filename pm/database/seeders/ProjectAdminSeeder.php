<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class ProjectAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create or get admin user
        $adminUser = User::firstOrCreate(
            ['username' => 'admin'],
            [
                'username' => 'admin',
                'password' => bcrypt('password123'),
                'full_name' => 'Project Administrator',
                'email' => 'admin@example.com',
                'current_task_status' => 'idle'
            ]
        );

        // Create a default project
        $projectId = DB::table('projects')->insertGetId([
            'project_name' => 'Default Admin Project',
            'description' => 'Default project for admin access',
            'created_by' => $adminUser->user_id,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Assign admin user as Project Admin
        DB::table('project_members')->updateOrInsert(
            [
                'project_id' => $projectId,
                'user_id' => $adminUser->user_id
            ],
            [
                'project_id' => $projectId,
                'user_id' => $adminUser->user_id,
                'role' => 'Project Admin',
                'joined_at' => now()
            ]
        );

        $this->command->info('✅ Project Admin setup completed!');
        $this->command->info("Username: admin");
        $this->command->info("Password: password123");
        $this->command->info("Role: Project Admin");
    }
}



