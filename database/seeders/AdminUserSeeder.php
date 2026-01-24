<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Creates first admin user for the system.
     */
    public function run(): void
    {
        // Check if admin already exists
        if (User::where('is_admin', true)->exists()) {
            $this->command->info('Admin user already exists. Skipping...');
            return;
        }

        // Create admin user
        User::create([
            'name' => 'Admin',
            'email' => 'admin@satsetui.com',
            'password' => Hash::make('admin123'),
            'credits' => 1000,
            'is_premium' => true,
            'is_admin' => true,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->command->info('Admin user created successfully!');
        $this->command->info('Email: admin@satsetui.com');
        $this->command->info('Password: admin123');
        $this->command->warn('Please change the password after first login!');
    }
}
