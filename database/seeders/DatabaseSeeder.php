<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::firstOrCreate([
            'slug' => 'default-org',
        ], [
            'name' => 'Default Organization',
            'timezone' => 'UTC',
        ]);

        $defaultUsers = [
            [
                'email' => 'owner@example.com',
                'full_name' => 'Owner User',
                'role' => 'OWNER',
            ],
            [
                'email' => 'admin@example.com',
                'full_name' => 'Admin User',
                'role' => 'ADMIN',
            ],
        ];

        foreach ($defaultUsers as $defaultUser) {
            User::updateOrCreate(
                [
                    'organization_id' => $organization->id,
                    'email' => $defaultUser['email'],
                ],
                [
                    'full_name' => $defaultUser['full_name'],
                    'role' => $defaultUser['role'],
                    'status' => 'ACTIVE',
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                ]
            );
        }
    }
}
