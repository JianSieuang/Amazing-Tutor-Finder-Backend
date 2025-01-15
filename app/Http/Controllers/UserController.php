<?php

namespace App\Http\Controllers;

use App\Models\LinkedAccount;
use App\Models\Parents;
use App\Models\Student;
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

    public function linkEmail(Request $request)
    {
        $user = Auth::user();

        $linkUser = User::where('email', $request->input('email'))->first();

        if (!$linkUser) {
            return response()->json(['message' => 'Email not found'], 404);
        }

        if (($linkUser->role === 'tutor') || ($user->role === 'student' && $linkUser->role === 'student') || ($user->role === 'parent' && $linkUser->role === 'parent')) {
            $txt = $user->role === 'student' ? 'Parent' : 'Student / Child';
            return response()->json(['message' => 'Only accept ' . $txt . ' email'], 404);
        }

        if ($user->role === 'student') {
            $linkedAccount = LinkedAccount::where('student_id', $user->id)->where('parent_id', $linkUser->id)->first();
        } else {
            $linkedAccount = LinkedAccount::where('parent_id', $user->id)->where('student_id', $linkUser->id)->first();
        }

        if ($linkedAccount && $linkedAccount->created_at->diffInDays(now()) < 7 && $linkedAccount->status !== 'success') {
            $message = $linkedAccount->status === 'rejected' ? 'Your request has been rejected. Please wait for 7 days before linking again.' :
                'Link email requested. Please wait for 7 days before linking again.';

            return response()->json(['message' => $message], 400);
        }

        $linkAccountDB = new LinkedAccount();

        if ($user->role === 'student') {
            $linkAccountDB->student_id = Student::where('user_id', $user->id)->first()->id;
            $linkAccountDB->parent_id = Parents::where('user_id', $linkUser->id)->first()->id;
        } else {
            $linkAccountDB->parent_id = Parents::where('user_id', $user->id)->first()->id;
            $linkAccountDB->student_id = Student::where('user_id', $linkUser->id)->first()->id;
        }

        $linkAccountDB->save();

        return response()->json([
            'message' => 'Email linked successfully',
            'user' => $user,
            'linked_user' => $linkUser,
            'linked_account' => $linkAccountDB
        ], 200);

        return response()->json(['message' => 'Feature not implemented yet'], 501);
    }

    public function updateLinkAccountStatus(Request $request, $link_account_id)
    {
        $decodedStatus = base64_decode($request->input('status'));
        $decodedId = base64_decode($link_account_id);

        $linkAccount = LinkedAccount::find($decodedId);

        if (!$linkAccount) {
            return response()->json(['error' => 'Link account not found'], 404);
        }

        $linkAccount->status = $decodedStatus;
        $linkAccount->save();

        return response()->json([
            'message' => 'Status updated successfully',
            'student' => User::find(Student::find($linkAccount->student_id)->user_id),
            'parent' => User::find(Parents::find($linkAccount->parent_id)->user_id),
            'status' => $linkAccount->status
        ], 200);
    }

    public function getLinkedAccounts($user_id)
    {
        $user = User::find($user_id);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        if ($user->role === 'student') {
            $linkedAccounts = LinkedAccount::where('student_id', Student::where('user_id', $user->id)->first()->id)->get();
            $parent = $linkedAccounts->map(function ($linkedAccount) {
                return \App\Models\User::find(\App\Models\Parents::find($linkedAccount->parent_id)->user_id);
            })->first();

            return response()->json(['linkedEmail' => $parent], 200);
        }

        if ($user->role === 'parent') {
            $linkedAccounts = LinkedAccount::where('parent_id', Parents::where('user_id', $user->id)->first()->id)->get();
            $students = $linkedAccounts->map(function ($linkedAccount) {
                return User::find(Student::find($linkedAccount->student_id)->user_id);
            });

            return response()->json(['linkedEmail' => $students], 200);
        }
    }

    public function unlinkEmail(Request $request, $user_id)
    {
        $user = User::find($user_id);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        if ($user->role === 'student') {
            $stu = Student::where('user_id', $user->id)->first();
            $par = Parents::where('user_id', $request->input('unlinkEmail'))->first();
            $linkedAccount = LinkedAccount::where('student_id', $stu->id)->where('parent_id', $par->id)->first();
        }

        if ($user->role === 'parent') {
            $par = Parents::where('user_id', $user->id)->first();
            $stu = Student::where('user_id', $request->input('unlinkEmail'))->first();
            $linkedAccount = LinkedAccount::where('student_id', $stu->id)->where('parent_id', $par->id)->first();
        }

        if (!$linkedAccount) {
            return response()->json(['error' => 'Linked account not found'], 404);
        }

        $linkedAccount->delete();

        return response()->json(['message' => 'Email unlinked successfully'], 200);
    }
}
