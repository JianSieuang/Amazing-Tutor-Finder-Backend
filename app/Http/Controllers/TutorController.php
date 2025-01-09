<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Tutor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class TutorController extends Controller
{   
    public function index()
    {
        $tutors = Tutor::with('user')
            ->where('status', 'approved')
            ->get();

        return response()->json([
            'message' => 'Pending tutors retrieved successfully!',
            'tutors' => $tutors
        ], 200);
    }

    public function pendingTutors()
    {
        $tutors = Tutor::with('user')
            ->where('status', 'pending')
            ->get();


        return response()->json([
            'message' => 'Pending tutors retrieved successfully!',
            'tutors' => $tutors
        ], 200);
    }

    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'fullname' => 'required|string|max:255',
            'phone_number' => 'nullable|string',
            'email' => 'required|string|email|max:255',
            'education_background' => 'required|string|max:255',
            'profile_picture' => 'nullable',
            'teaching_experience' => 'required|string',
            'about_me' => 'required|string',
            'instagram' => 'nullable|string',
            'linkedln' => 'nullable|string',
            'whatsapp' => 'nullable|string',
        ]);

        if ($request->hasFile('profile_picture')) {
            $path = $request->file('profile_picture')->store('profile_pictures', 'public');
            $imageUrl = Storage::url($path);
            $validatedData['profile_picture'] = $imageUrl;
        }

        if (User::where('email', $validatedData['email'])->exists()) {
            return response()->json(['message' => 'Email already exists!'], 400);
        }

        if (User::where('phone', $validatedData['phone_number'])->exists()) {
            return response()->json(['message' => 'Phone number already exists!'], 400);
        }

        $user = User::create([
            'name' => $validatedData['fullname'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['phone_number']),
            'role' => 'tutor',
        ]);

        $user->phone = $validatedData['phone_number'];
        $user->image = $validatedData['profile_picture'];
        $user->save();

        $tutor = Tutor::create([
            'user_id' => $user->id,
            'education_background' => $validatedData['education_background'],
            'teaching_experience' => $validatedData['teaching_experience'],
            'about_me' => $validatedData['about_me'],
            'instagram' => $validatedData['instagram'],
            'linkedln' => $validatedData['linkedln'],
            'whatsapp' => $validatedData['whatsapp'],
        ]);

        return response()->json(['message' => 'Tutor registered successfully!', 'user' => $user, 'tutor' => $tutor], 201);
    }
}
