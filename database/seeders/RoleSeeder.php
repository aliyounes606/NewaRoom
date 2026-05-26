<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds users with each of the three roles: admin, writer, reader.
 * Creates known users for testing + additional random users per role.
 */
class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Named admin user for testing
        User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@newsroom.test',
        ]);

        // Named writer users for testing
        User::factory()->writer()->create([
            'name' => 'Writer One',
            'email' => 'writer1@newsroom.test',
        ]);

        User::factory()->writer()->create([
            'name' => 'Writer Two',
            'email' => 'writer2@newsroom.test',
        ]);

        // Additional random writers
        User::factory()->writer()->count(3)->create();

        // Named reader user for testing
        User::factory()->reader()->create([
            'name' => 'Reader User',
            'email' => 'reader@newsroom.test',
        ]);

        // Additional random readers
        User::factory()->reader()->count(5)->create();
    }
}
