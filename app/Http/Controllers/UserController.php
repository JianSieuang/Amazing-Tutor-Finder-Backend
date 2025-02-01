<?php

namespace App\Http\Controllers;

use App\Models\BookedTime;
use App\Models\User;
use App\Models\Tutor;
use App\Models\Report;
use App\Models\Parents;
use App\Models\Payment;
use App\Models\Student;
use Illuminate\Http\Request;
use App\Models\LinkedAccount;
use App\Models\Rate;
use App\Models\SocialMedia;
use App\Models\TutorSession;
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
            $linkedAccounts = LinkedAccount::where('student_id', Student::where('user_id', $user->id)->first()->id)->where('status', '!=', 'pending')->where('status', '!=', 'rejected')->get();
            $parent = $linkedAccounts->map(function ($linkedAccount) {
                return \App\Models\User::find(\App\Models\Parents::find($linkedAccount->parent_id)->user_id);
            })->first();

            return response()->json(['linkedEmail' => $parent], 200);
        }

        if ($user->role === 'parent') {
            $linkedAccounts = LinkedAccount::where('parent_id', Parents::where('user_id', $user->id)->first()->id)->where('status', '!=', 'pending')->where('status', '!=', 'rejected')->get();
            $students = $linkedAccounts->map(function ($linkedAccount) {
                return User::find(Student::find($linkedAccount->student_id)->user_id);
            });

            return response()->json(['linkedEmail' => $students], 200);
        }
    }

    public function unlinkAccount(Request $request, $user_id)
    {
        $user = User::find($user_id);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // for now unlink feature only can make it in parent side

        // if ($user->role === 'student') {
        //     $stu = Student::where('user_id', $user->id)->first();
        //     $par = Parents::where('user_id', $request->input('unlinkAccount'))->first();
        //     $linkedAccount = LinkedAccount::where('student_id', $stu->id)->where('parent_id', $par->id)->first();
        // }

        if ($user->role === 'parent') {
            $par = Parents::where('user_id', $user->id)->first();
            $stu = Student::where('user_id', $request->input('unlinkAccount'))->first();
            $linkedAccount = LinkedAccount::where('student_id', $stu->id)->where('parent_id', $par->id)->first();
        }

        if (!$linkedAccount) {
            return response()->json(['error' => 'Linked account not found'], 404);
        }

        $linkedAccount->delete();

        return response()->json(['message' => 'Email unlinked successfully'], 200);
    }

    public function getPurchaseHistory($user_id)
    {
        $history = Payment::where('parent_user_id', User::where('id', $user_id)->where('role', 'parent')->first()->id)->get();

        $bookedTime = BookedTime::whereIn('id', $history->pluck('booked_time_id'))->get();

        $tutorSessions = TutorSession::whereIn('user_id', $bookedTime->pluck('tutor_id'))->get();

        $grpSession = [];
        $sessionMap = [];

        foreach ($bookedTime as $booked) {
            $key = $booked->tutor_id . '-' . $booked->created_at;

            $sessionTitle = $tutorSessions->where('user_id', $booked->tutor_id)->first()->title;

            $tutor = Tutor::where('user_id', $booked->tutor_id)->first();

            // If this session is already mapped, store its index
            if (isset($sessionMap[$key])) {
                $sessionIndex = $sessionMap[$key];
                $grpSession[$sessionIndex]['grouped_ids'][] = $booked->id;

                $grpSession[$sessionIndex]['title_image'] = $tutor->title_image;
                $grpSession[$sessionIndex]['title'] = $sessionTitle;

                // Add corresponding session info (same index)
                $grpSession[$sessionIndex]['sessions'][] = [
                    'month' => $booked->month,
                    'day' => $booked->day,
                    'time_slot' => $booked->time_slot,
                ];
            } else {
                // Create a new group
                $sessionIndex = count($grpSession);
                $sessionMap[$key] = $sessionIndex;

                $grpSession[$sessionIndex] = [
                    'group_id' => $sessionIndex + 1, // Just a readable index
                    'grouped_ids' => [$booked->id], // Store related booked time IDs
                    'tutor_id' => $booked->tutor_id,
                    'title_image' => $tutor->title_image,
                    'title' => $sessionTitle,
                    'sessions' => [[ // Each session includes its time details
                        'month' => $booked->month,
                        'day' => $booked->day,
                        'time_slot' => $booked->time_slot,
                    ]]
                ];
            }
        }

        return response()->json(['paymentHistory' => $history, 'bookedTime' => $bookedTime, 'tutorSessions' => $tutorSessions, 'grpSession' => $grpSession], 200);
    }

    public function reportTutor(Request $request)
    {
        $report = new Report();
        $report->report_by = $request->input('report_by');
        $report->report_to = $request->input('report_to');
        $report->description = $request->input('description');
        $report->save();

        return response()->json(['message' => 'Report sent successfully'], 200);
    }

    public function getReport()
    {
        $report = Report::with(['user', 'reportTutor'])->get();

        return response()->json(['reports' => $report], 200);
    }

    public function getReportById($id)
    {
        $report = Report::with(['user', 'reportTutor', 'reportTutor.isTutor'])->find($id);

        return response()->json(['report' => $report], 200);
    }

    public function submitReport(Request $request, $id)
    {
        $report = Report::find($id);
        $report->feedback = $request->input('feedback');
        $report->status = 'replied';
        $report->save();

        return response()->json(['message' => 'Report submitted successfully'], 200);
    }

    // Admin
    public function getAdminDashboard()
    {
        $user = Auth::user();

        if ($user->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $account = User::where('role', '!=', 'admin')->count();
        $student = Student::all()->count();
        $parent = Parents::all()->count();
        $tutor = Tutor::all()->count();
        $tutor_application = Tutor::where('status', 'pending')->count();
        $report = Report::all()->count();
        $earn = Payment::all()->sum('amount');

        return response()->json([
            'account' => $account,
            'student' => $student,
            'parent' => $parent,
            'tutor' => $tutor,
            'tutor_application' => $tutor_application,
            'report' => $report,
            'earn' => $earn
        ], 200);
    }

    public function getSocialMedia()
    {
        $socialMedia = SocialMedia::first();

        if (!$socialMedia) {
            return response()->json(['error' => 'Social media not found'], 404);
        }

        return response()->json(['social_media' => $socialMedia], 200);
    }

    public function updateSocialMedia(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $socialMedia = SocialMedia::first();

        if (!$socialMedia) {
            $socialMedia = new SocialMedia();
            $socialMedia->instagram = $request->input('instagram');
            $socialMedia->facebook = $request->input('facebook');
            $socialMedia->linkedin = $request->input('linkedin');
            $socialMedia->save();
        } else {
            $socialMedia->update($request->all());
        }

        return response()->json(['message' => 'Social media updated successfully', 'social_media' => $socialMedia], 200);
    }

    public function ratingTutor(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rateBy' => 'required',
            'tutorId' => 'required',
            'rating' => 'nullable|numeric',
            'feedback' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $rating = new Rate();
        $rating->tutor_id = $request->tutorId;
        $rating->rate_by = $request->rateBy;
        $rating->rate = $request->rating;
        $rating->description = $request->feedback;
        $rating->save();

        return response()->json([
            'rating' => $rating
        ], 200);
    }

    public function getRating($tutor_id)
    {
        $rating = Rate::where('tutor_id', $tutor_id)->get();

        $user = User::whereIn('id', $rating->pluck('rate_by'))->get();

        $rating = $rating->map(function ($item) use ($user) {
            $item->user = $user->where('id', $item->rate_by)->first()->only(['name', 'image']);
            return $item;
        });

        $overallRating = $rating->avg('rate');

        return response()->json(['rating' => $rating, 'overall_rating' => $overallRating], 200);
    }

    public function getPayment()
    {
        $payments = Payment::all();

        $bookedTime = BookedTime::whereIn('id', $payments->pluck('booked_time_id'))->get();

        $tutors = User::whereIn('id', $bookedTime->pluck('tutor_id'))->get();

        $parents = User::whereIn('id', $payments->pluck('parent_user_id'))->get();

        $students = User::whereIn('id', $payments->pluck('student_user_id'))->get();

        return response()->json([
            'paymentHistory' => $payments,
            'bookSessions' => $bookedTime,
            'tutors' => $tutors,
            'parents' => $parents,
            'students' => $students
        ], 200);
    }
}
