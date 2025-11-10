<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class TeamMembersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Team Lead
        User::create([
            'username' => 'teamlead',
            'email' => 'teamlead@example.com',
            'password' => Hash::make('password'),
            'full_name' => 'John Team Lead',
            'current_task_status' => 'idle',
        ]);

        // Create Developer
        User::create([
            'username' => 'developer',
            'email' => 'developer@example.com',
            'password' => Hash::make('password'),
            'full_name' => 'Sarah Developer',
            'current_task_status' => 'idle',
        ]);

        // Create Designer
        User::create([
            'username' => 'designer',
            'email' => 'designer@example.com',
            'password' => Hash::make('password'),
            'full_name' => 'Mike Designer',
            'current_task_status' => 'idle',
        ]);

        $this->command->info('✅ Team members created successfully!');
        $this->command->info('👥 Users created:');
        $this->command->info('   - Team Lead: teamlead / password');
        $this->command->info('   - Developer: developer / password');
        $this->command->info('   - Designer: designer / password');
    }
}
