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

    public function tutorDetails($tutor_id)
    {
        $tutorDetail = Tutor::where('user_id', $tutor_id)->firstOrFail();

        if (!$tutorDetail) {
            return response()->json(['message' => 'Tutor not found!'], 404);
        }

        return response()->json(['message' => 'Tutor details retrieved successfully!', 'tutorDetail' => $tutorDetail], 200);
    }

    public function editTutor(Request $request, $user_id)
    {
        return response()->json(['message' => 'HERE !', 'title_image' => $request->file('title_image')], 200);

        $request->validate([
            'fullname' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string',
            'email' => 'nullable|string|email|max:255',
            'profile_picture' => 'nullable',

            'education_background' => 'nullable|string|max:255',
            'teaching_experience' => 'nullable|string',
            'about_me' => 'nullable|string',
            'instagram' => 'nullable|string',
            'linkedln' => 'nullable|string',
            'whatsapp' => 'nullable|string',
            'title_image' => 'nullable',
        ]);

        $tutor = Tutor::where('user_id', $user_id)->first();

        if (!$tutor) {
            return response()->json(['message' => 'Tutor not found!'], 404);
        }

        $user = User::find($user_id);

        if (!$user) {
            return response()->json(['message' => 'User not found!'], 404);
        }

        if ($request->hasFile('profile_picture')) {
            $path = $request->file('profile_picture')->store('profile_pictures', 'public');
            $imageUrl = Storage::url($path);
        }

        if ($request->hasFile('title_image')) {
            $path = $request->file('title_image')->store('profile_pictures', 'public');
            $titleImageUrl = Storage::url($path);
        }

        $user->name = $request['name'] ?? $user->name;
        $user->email = $request['email'] ?? $user->email;
        $user->phone = $request['phone_number'] ?? $user->phone;
        $user->image = $imageUrl ?? $user->image;
        $user->save();

        $tutor->education_background = $request['education_background'] ?? $tutor->education_background;
        $tutor->teaching_experience = $request['teaching_experience'] ?? $tutor->teaching_experience;
        $tutor->about_me = $request['about_me'] ?? $tutor->about_me;
        $tutor->instagram = $request['instagram'] ?? $tutor->instagram;
        $tutor->linkedln = $request['linkedln'] ?? $tutor->linkedln;
        $tutor->whatsapp = $request['whatsapp'] ?? $tutor->whatsapp;
        $titleImageUrl = $request->hasFile('title_image')
            ? Storage::url($request->file('title_image')->store('title_images', 'public'))
            : $tutor->title_image;
        $tutor->save();

        return response()->json(['message' => 'Tutor updated successfully!', 'user' => $user, 'tutor' => $tutor], 200);
    }
}
