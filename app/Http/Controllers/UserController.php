<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Update the user's profile
        $user->name = $request->input('name');
        $user->address = $request->input('address');
        $user->save();

        return $request->user();
    }
}
