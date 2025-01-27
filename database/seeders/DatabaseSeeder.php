<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Tutor;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Arr;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test Student',
            'email' => 'student@example.com',
            'phone' => '1234567890',
            'password' => Hash::make('password'),
            'role' => 'student',
            'email_verified_at' => now(),
            'remember_token' => 'testtoken',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@gmail.com',
            'phone' => '1234567890',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
            'remember_token' => 'testtoken',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        for ($i = 1; $i <= 25; $i++) {
            $user = User::factory()->create([
                'name' => "Tutor{$i} User",
                'email' => "tutor{$i}@example.com",
                'phone' => "123456789{$i}",
                'password' => Hash::make('password'),
                'role' => 'tutor',
                'email_verified_at' => now(),
                'remember_token' => 'testtoken',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Tutor::create([
                'user_id' => $user->id,
                'education_background' => 'BSc Computer Science',
                'teaching_experience' => rand(1, 10) . ' years', // Random experience between 1 to 10 years
                'about_me' => "I am Tutor {$i}, a passionate educator with extensive experience.",
                'status' => Arr::random(['pending', 'approved']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
