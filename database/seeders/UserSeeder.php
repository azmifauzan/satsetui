<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test user with Indonesian language
        User::create([
            'name' => 'User Indonesia',
            'email' => 'user@test.id',
            'password' => Hash::make('password'),
            'credits' => 25,
            'is_premium' => false,
            'language' => 'id',
        ]);

        // Create test user with English language
        User::create([
            'name' => 'English User',
            'email' => 'user@test.com',
            'password' => Hash::make('password'),
            'credits' => 25,
            'is_premium' => false,
            'language' => 'en',
        ]);

        // Create premium user
        User::create([
            'name' => 'Premium User',
            'email' => 'premium@test.com',
            'password' => Hash::make('password'),
            'credits' => 1000,
            'is_premium' => true,
            'preferred_model' => 'claude-sonnet',
            'language' => 'en',
        ]);
    }
}
