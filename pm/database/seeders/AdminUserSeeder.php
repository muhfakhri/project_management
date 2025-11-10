<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Create Admin User
        $adminId = DB::table('users')->insertGetId([
            'username' => 'admin',
            'full_name' => 'Admin User',
            'email' => 'admin@pm.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'current_task_status' => 'idle',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create Team Lead User
        $teamLeadId = DB::table('users')->insertGetId([
            'username' => 'teamlead',
            'full_name' => 'Team Lead',
            'email' => 'teamlead@pm.com',
            'password' => Hash::make('teamlead123'),
            'role' => 'developer',
            'current_task_status' => 'idle',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create Developer 1
        $dev1Id = DB::table('users')->insertGetId([
            'username' => 'john',
            'full_name' => 'John Doe',
            'email' => 'john@pm.com',
            'password' => Hash::make('john123'),
            'role' => 'developer',
            'current_task_status' => 'idle',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create Developer 2
        $dev2Id = DB::table('users')->insertGetId([
            'username' => 'jane',
            'full_name' => 'Jane Smith',
            'email' => 'jane@pm.com',
            'password' => Hash::make('jane123'),
            'role' => 'developer',
            'current_task_status' => 'idle',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create Designer
        $designerId = DB::table('users')->insertGetId([
            'username' => 'alice',
            'full_name' => 'Alice Designer',
            'email' => 'alice@pm.com',
            'password' => Hash::make('alice123'),
            'role' => 'designer',
            'current_task_status' => 'idle',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        echo "✅ Created 5 users:\n";
        echo "   - Admin: admin / admin123\n";
        echo "   - Team Lead: teamlead / teamlead123\n";
        echo "   - Developer 1: john / john123\n";
        echo "   - Developer 2: jane / jane123\n";
        echo "   - Designer: alice / alice123\n";
    }
}
