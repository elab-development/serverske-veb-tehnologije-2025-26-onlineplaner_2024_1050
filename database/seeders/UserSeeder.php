<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userCases = [
            [
                'name' => 'Admin User',
                'email' => 'admin@planner.com',
                'role' => User::ROLE_ADMIN,
            ],
            [
                'name' => 'Mila Novak',
                'email' => 'mila.novak@example.com',
                'role' => User::ROLE_USER,
            ],
            [
                'name' => 'Luka Petrovic',
                'email' => 'luka.petrovic@example.com',
                'role' => User::ROLE_USER,
            ],
            [
                'name' => 'Sara Jovanovic',
                'email' => 'sara.jovanovic@example.com',
                'role' => User::ROLE_USER,
            ],
        ];

        foreach ($userCases as $userCase) {
            User::factory()->create($userCase);
        }

        User::factory(7)->create();
    }
}
