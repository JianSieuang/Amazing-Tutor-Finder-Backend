<?php

namespace Database\Seeders;

use App\Models\LinkedAccount;
use App\Models\Parents;
use App\Models\SocialMedia;
use App\Models\Student;
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
        // Student
        $studentUser = User::create([
            'name'             => 'Test Student',
            'email'            => 'student@example.com',
            'phone'            => '1234567890',
            'password'         => Hash::make('password'),
            'role'             => 'student',
            'email_verified_at' => now(),
            'remember_token'   => 'testtoken',
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        $student = Student::create([
            'user_id'    => $studentUser->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Parent
        $parentUser = User::create([
            'name'             => 'Parent User',
            'email'            => 'parent@example.com',
            'phone'            => '1234567890',
            'password'         => Hash::make('password'),
            'role'             => 'parent',
            'email_verified_at' => now(),
            'remember_token'   => 'testtoken',
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        $parent = Parents::create([
            'user_id'    => $parentUser->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Link student and parent
        LinkedAccount::create([
            'student_id' => $student->id,
            'parent_id'  => $parent->id,
            'status'     => 'approved',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Admin
        User::create([
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

        // Tutor
        for ($i = 1; $i <= 25; $i++) {
            $user = User::create([
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
                'title_image' => '/storage/profile_pictures/' . Arr::random([
                    '5XJ3wD7BsGH9iUZoY8RdKlV2PzMfB1D9T1kA6VRF.png',
                    'Cz7Kp1NbVR4jLQ9iXaFhZ3G2tYoJ6s8W0kB0E5Y8.png',
                    'W2Qc3M4T5eHv9F7ZKrGD4YuG5J1Nn8LqY6Xi6K9T.png',
                    'L8qR3s9N2Dk6VdF1BzG7M6QpTfW4J0Y9rB0z7WJX.png'
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Tutor Sessions
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

        // Admin Side Setting Social Media
        SocialMedia::create([
            'facebook' => 'https://www.facebook.com/mmumalaysia/',
            'instagram' => 'https://www.instagram.com/l33_y1_yan6?igsh=MTBheG0zMjU3NmkzOA==',
            'linkedin' => 'https://my.linkedin.com/in/sim-boon-xun-913766287',
        ]);
    }
}
