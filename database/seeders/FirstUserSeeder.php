<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class FirstUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            [
                'name' => 'Hazem Tayara',
                'email' => 'hazemtayara36@gmail.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
        
        $this->command->info('Admin user created successfully!');
    }
}