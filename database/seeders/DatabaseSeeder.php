<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Tutor;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Arr;
use App\Models\TutorSession;
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

            $tutor = Tutor::create([
                'user_id' => $user->id,
                'education_background' => 'BSc Computer Science',
                'teaching_experience' => rand(1, 10) . ' years',
                'about_me' => "I am Tutor {$i}, a passionate educator with extensive experience.",
                'instagram' => 'https://www.instagram.com/l33_y1_yan6?igsh=MTBheG0zMjU3NmkzOA==',
                'linkedln' => 'https://my.linkedin.com/in/sim-boon-xun-913766287',
                'whatsapp' => 'https://wa.me/60123231859',
                'status' => Arr::random(['pending', 'approved']),
                'title_image' => '/storage/profile_pictures/84YR1cA4krXDgjmFvfiGNdIWRIfBcBSSzVjM7OYR.png',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            TutorSession::create([
                'tutor_id' => $tutor->id,
                'user_id' => $user->id,
                'title' => "Session {$i}",
                'description' => "This is a test session for Tutor {$i}",
                'course_language' => 'English',
                'price' => rand(10, 100),
                'session_month' => 'February',
                'session_day' => json_encode([
                    'monday'    => (bool) rand(0, 1),
                    'tuesday'   => (bool) rand(0, 1),
                    'wednesday' => (bool) rand(0, 1),
                    'thursday'  => (bool) rand(0, 1),
                    'friday'    => (bool) rand(0, 1),
                ]),
                'session_time' => Arr::random([
                    '10:00-12:00',
                    '13:00-15:00',
                    '16:00-18:00',
                ]),
                'teaching_mode' => 'Online',
                'teaching_location' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
