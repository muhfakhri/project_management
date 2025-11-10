<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed admin and test users
        $this->call([
            AdminUserSeeder::class,
            SimpleBlockerSeeder::class, // Use simpler seeder that works with existing data
        ]);
    }
}
