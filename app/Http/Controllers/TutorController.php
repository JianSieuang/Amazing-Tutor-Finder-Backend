<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Tutor;
use App\Models\BookedTime;
use App\Models\EnrollTutor;
use App\Models\Payment;
use App\Models\Rate;
use App\Models\Student;
use App\Models\TutorSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

use function PHPUnit\Framework\isEmpty;

class TutorController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('perPage', 12);
        $page = $request->input('page', 1);
        $sortBy = $request->input('sortBy', '');
        $sortOrder = $request->input('sortOrder', 'desc'); // 'asc' or 'desc'

        // Get total count of approved tutors
        $totalTutors = Tutor::where('status', 'approved')->count();

        // Build the base query
        $tutorsQuery = Tutor::query()->with('user')->where('status', 'approved');

        // If not admin, fetch tutors that have sessions.
        if (!strpos($request->header('X-Referer'), '/admin')) {
            $tutorsQuery = $tutorsQuery->whereHas('hasSessions');
        }

        // Apply sorting based on criteria
        if ($sortBy === 'price') {
            // Sorting by price using a join on tutor_sessions
            $tutorsQuery = $tutorsQuery->join('tutor_sessions', 'tutor_sessions.tutor_id', '=', 'tutors.id')
                ->orderBy('tutor_sessions.price', $sortOrder)
                ->select('tutors.*');
        } elseif ($sortBy === 'rating') {
            // Using withAvg to compute the average rating and order by it.
            $tutorsQuery = $tutorsQuery->withAvg('rates', 'rate')
                ->orderBy('rates_avg_rate', $sortOrder);
        }

        // Paginate the tutors
        $tutors = $tutorsQuery->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        // (Optional) Retrieve additional related data like tutor sessions, ratings, and enrolled students
        $tutorSessions = TutorSession::whereIn('tutor_id', $tutors->pluck('id')->toArray())->get();
        $ratings = Rate::whereIn('tutor_id', $tutors->pluck('user_id')->toArray())->get();
        $enrolledStudents = BookedTime::whereIn('tutor_id', $tutors->pluck('user_id')->toArray())->get();

        // Transform tutors to include the computed average rating and enrolled student count
        $tutors->transform(function ($tutor) use ($enrolledStudents) {
            // Here, the computed average rating is available as rates_avg_rate
            $tutor->overallRate = round($tutor->rates_avg_rate ?? 0, 1);
            $tutor->enrolledStudent = $enrolledStudents->where('tutor_id', $tutor->user_id)->count();
            return $tutor;
        });

        return response()->json([
            'message' => 'Tutors retrieved successfully!',
            'tutors' => $tutors,
            'sessions' => $tutorSessions,
            'ratings' => $ratings,
            'enrolledStudents' => $enrolledStudents,
            'totalTutors' => $totalTutors,
            'totalPages' => ceil($totalTutors / $perPage),
            'currentPage' => $page,
            'perPage' => $perPage,
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

    public function tutorDetails($user_id)
    {
        $tutorDetail = Tutor::where('user_id', $user_id)->firstOrFail();

        $userDetail = User::where('id', $user_id)->firstOrFail();

        if (!$tutorDetail) {
            return response()->json(['message' => 'Tutor not found!'], 404);
        }

        return response()->json(['message' => 'Tutor details retrieved successfully!', 'tutorDetail' => $tutorDetail, 'userDetail' => $userDetail], 200);
    }

    public function editTutor(Request $request, $user_id)
    {
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
            'title_picture' => 'nullable',
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

        if ($request->hasFile('title_picture')) {
            $path = $request->file('title_picture')->store('profile_pictures', 'public');
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
        $tutor->title_image = $titleImageUrl ?? $tutor->title_image;
        $tutor->save();

        return response()->json(['message' => 'Tutor updated successfully!', 'user' => $user, 'tutor' => $tutor, 'title_image' => $titleImageUrl ?? null], 200);
    }

    public function addSession(Request $request, $user_id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'course_language' => 'required|string',
            'price' => 'required|numeric',
            'session_month' => 'required|string',
            'session_day'      => 'required|array',
            'session_time' => 'required|string',
            'teaching_mode' => 'required|string',
            'teaching_location' => 'nullable|string',
        ]);

        $tutor = Tutor::where('user_id', $user_id)->first();

        if (!$tutor) {
            return response()->json(['message' => 'Tutor not found!'], 404);
        }

        $session = TutorSession::updateorCreate([
            'user_id' => $user_id,
        ], [
            'tutor_id' => $tutor->id,
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'course_language' => $request->input('course_language'),
            'price' => $request->input('price'),
            'session_month' => $request->input('session_month'),
            'session_day' => json_encode([
                'monday' => $request->session_day['monday'],
                'tuesday' => $request->session_day['tuesday'],
                'wednesday' => $request->session_day['wednesday'],
                'thursday' => $request->session_day['thursday'],
                'friday' => $request->session_day['friday'],
                'saturday' => $request->session_day['saturday'],
                'sunday' => $request->session_day['sunday'],
            ]),
            'session_time' => $request->input('session_time'),
            'teaching_mode' => $request->input('teaching_mode'),
            'teaching_location' => $request->input('teaching_location') ?? null,
        ]);

        if ($session->wasRecentlyCreated) {
            return response()->json(['message' => 'Session created successfully!', 'session' => $session], 201);
        } else {
            return response()->json(['message' => 'Session updated successfully!', 'session' => $session], 200);
        }
    }

    public function getSessions($user_id)
    {
        $sessions = TutorSession::where('user_id', $user_id)->first();

        return response()->json(['message' => 'Tutor sessions retrieved successfully!', 'sessions' => $sessions], 200);
    }

    public function getDashboard($user_id)
    {
        $tutor = Tutor::where('user_id', $user_id)->first();

        if (!$tutor) {
            return response()->json(['message' => 'Tutor not found!'], 404);
        }

        $students = EnrollTutor::where('tutor_id', $user_id)->get();
        $sessions = TutorSession::where('tutor_id', $tutor->id)->get();
        $bookedTimes = BookedTime::where('tutor_id', $user_id)->get();

        $totalAmount = Payment::whereIn('booked_time_id', $bookedTimes->pluck('id'))->sum('amount');

        return response()->json([
            'message' => 'Tutor students retrieved successfully!',
            'students' => $students,
            'amount' => $totalAmount,
            'tutorSessions' => $sessions,
        ], 200);
    }

    public function getEnrolledStudents($tutor_id)
    {
        $enrolledStudents = BookedTime::where('tutor_id', $tutor_id)->get();
        return response()->json(['enrolled_students' => $enrolledStudents], 200);
    }

    public function getEnrolledTutors(Request $request, $user_id)
    {
        $searchQuery = $request->input('search', '');

        $student = Student::where('user_id', $user_id)->first();

        $enrolledTutors = EnrollTutor::where('student_id', $student->id)->get();

        $tutorIds = $enrolledTutors->pluck('tutor_id');

        $tutors = Tutor::when($tutorIds, function ($query) use ($tutorIds) {
            $query->whereIn('user_id', $tutorIds);
        })
            ->when($searchQuery != null && $searchQuery != '', function ($query) use ($searchQuery) {
                $query->whereHas('user', function ($q) use ($searchQuery) {
                    $q->where('name', 'LIKE', '%' . strtolower($searchQuery) . '%')
                        ->orWhere('name', 'LIKE', '%' . ucfirst($searchQuery) . '%')
                        ->orWhere('name', 'LIKE', '%' . strtoupper($searchQuery) . '%');
                });
            })
            ->get();

        $userIds = $tutors->pluck('user_id');

        $sessions = TutorSession::whereIn('user_id', $userIds)->get();

        $rating = Rate::whereIn('tutor_id', $tutorIds)->get();

        $enrolledStudents = BookedTime::whereIn('tutor_id', $tutorIds)->get();

        $enrolledSessions = BookedTime::where('student_id', $student->id)->get();

        return response()->json([
            'message' => 'Enrolled tutors retrieved successfully!',
            'tutorsData' => $tutors,
            'sessionsData' => $sessions,
            'ratings' => $rating,
            'enrolledStudents' => $enrolledStudents,
            'enrolledSessions' => $enrolledSessions
        ], 200);
    }

    public function searchTutor(Request $request)
    {
        $searchQuery = $request->input('search', '');

        $tutors = Tutor::when($searchQuery != null && $searchQuery != '', function ($query) use ($searchQuery) {
            $query->whereHas('user', function ($q) use ($searchQuery) {
                $q->where('name', 'LIKE', '%' . strtolower($searchQuery) . '%')
                    ->orWhere('name', 'LIKE', '%' . ucfirst($searchQuery) . '%')
                    ->orWhere('name', 'LIKE', '%' . strtoupper($searchQuery) . '%');
            });
        })
            ->get();

        $userIds = $tutors->pluck('user_id');

        $users = User::whereIn('id', $userIds)->get();

        $sessions = TutorSession::whereIn('user_id', $userIds)->get();

        return response()->json(['tutorsData' => $tutors, 'usersData' => $users, 'sessionsData' => $sessions], 200);
    }

    public function getSchedule($user_id)
    {
        $schedules = BookedTime::where('tutor_id', $user_id)->get();

        $students = Student::whereIn('id', $schedules->pluck('student_id'))->get();

        $users = User::whereIn('id', $students->pluck('user_id'))->get();

        $sessions = TutorSession::where('user_id', $user_id)->get();

        $tutors = User::whereIn('id', $schedules->pluck('tutor_id'))->where('role', 'tutor')->get();

        return response()->json(['schedules' => $schedules, 'sessions' => $sessions, 'users' => $users, 'students' => $students, 'tutors' => $tutors], 200);
    }

    public function getPayments($user_id)
    {
        $bookedTimes = BookedTime::where('tutor_id', $user_id)->get();
        $payments = Payment::whereIn('booked_time_id', $bookedTimes->pluck('id'))->get();
        $students = User::whereIn('id', $payments->pluck('student_user_id'))->get();
        $parents = User::whereIn('id', $payments->pluck('parent_user_id'))->get();
        $tutors = User::whereIn('id', $bookedTimes->pluck('tutor_id'))->get();

        return response()->json([
            'paymentHistory' => $payments,
            'bookSessions' => $bookedTimes,
            'tutors' => $tutors,
            'parents' => $parents,
            'students' => $students
        ], 200);
    }
}
