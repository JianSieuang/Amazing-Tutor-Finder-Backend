<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Tutor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules;

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
        $request->validate([
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
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        if ($request->hasFile('profile_picture')) {
            $path = $request->file('profile_picture')->store('profile_pictures', 'public');
            $imageUrl = Storage::url($path);
        }

        if (User::where('email', $request['email'])->exists()) {
            return response()->json(['message' => 'Email already exists!'], 400);
        }

        if (User::where('phone', $request['phone_number'])->exists()) {
            return response()->json(['message' => 'Phone number already exists!'], 400);
        }

        $user = User::create([
            'name' => $request['fullname'],
            'email' => $request['email'],
            'password' => Hash::make($request['password']),
            'role' => 'tutor',
        ]);

        $user->phone = $request['phone_number'];
        $user->image = $imageUrl;
        $user->save();

        $tutor = Tutor::create([
            'user_id' => $user->id,
            'education_background' => $request['education_background'],
            'teaching_experience' => $request['teaching_experience'],
            'about_me' => $request['about_me'],
            'instagram' => $request['instagram'],
            'linkedln' => $request['linkedln'],
            'whatsapp' => $request['whatsapp'],
        ]);

        return response()->json(['message' => 'Tutor registered successfully!', 'user' => $user, 'tutor' => $tutor], 201);
    }

    public function updateStatus(Request $request, $tutor_id)
    {
        $request->validate([
            'status' => 'required|string|in:approved,rejected',
        ]);

        $tutor = Tutor::find($tutor_id);

        if (!$tutor) {
            return response()->json(['message' => 'Tutor not found!'], 404);
        }

        $tutor->status = $request['status'];
        $tutor->save();

        return response()->json(['message' => 'Tutor status updated successfully!', 'tutor' => $tutor], 200);
    }
}
