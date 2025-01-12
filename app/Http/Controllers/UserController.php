<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function update(Request $request)
    {
        // Retrieve the authenticated user
        $user = Auth::user();

        // Check if user is an instance of the User model
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Update the user's profile

        // Check if the request has first_name
        if ($request->has('first_name')) {
            $user->first_name = $request->input('first_name');
        }

        // Check if the request has last_name
        if ($request->has('last_name')) {
            $user->last_name = $request->input('last_name');
        }

        $user->name = $request->input('name');
        $user->email = $request->input('email');

        $user->save();

        return $request->user();
    }

    public function updateImage(Request $request, $user_id)
    {
        if ($request->hasFile('profile_picture') && $request->file('profile_picture')->getSize() > 1024 * 1024) {
            return response()->json(['error' => 'Image size should not be greater than 1MB'], 400);
        }

        $request->validate([
            'profile_picture' => 'nullable|file|image|max:1024'
        ]);

        if ($request->hasFile('profile_picture')) {
            $path = $request->file('profile_picture')->store('profile_pictures', 'public');
            $imageUrl = Storage::url($path);

            $user = User::find($user_id);

            if ($user) {
                $user->image = $imageUrl;
                $user->save();
                return response()->json(['message' => 'Image updated successfully', 'user' => $user], 200);
            } else {
                return response()->json(['error' => 'User not found'], 404);
            }
        }

        return response()->json(['error' => 'Image not found'], 404);
    }

    public function changePassword(Request $request)
    {
        $user = Auth::user();

        if (strlen($request->input('new_password')) < 8) {
            return response()->json(['message' => 'Password should be at least 8 characters'], 400);
        }

        if (!Hash::check($request->input('current_password'), $user->password)) {
            return response()->json(['message' => 'Current password is incorrect'], 400);
        }

        $user->password = Hash::make($request->input('new_password'));
        $user->save();

        return response()->json(['message' => 'Password changed successfully'], 200);
    }
}
