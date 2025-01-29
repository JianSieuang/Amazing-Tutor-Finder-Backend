<?php

namespace App\Http\Controllers;

use App\Models\BookedTime;
use App\Models\Payment;
use App\Models\Student;
use Illuminate\Http\Request;
use Stripe\Stripe;

class StripeController extends Controller
{
    public function index()
    {
        return view('stripe.index');
    }

    public function checkout(Request $request)
    {
        Stripe::setApiKey(env("STRIPE_SECRET_KEY"));

        $session = \Stripe\Checkout\Session::create([
            'line_items' => [[
                'price_data' => [
                    'currency' => 'myr',
                    'product_data' => [
                        'name' => 'Tutoring Sessions - ' . $request->month,
                    ],
                    'unit_amount' => $request->total_price * 100,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('success', [
                'tutor_id' => $request->tutor_id,
                'user_id' => $request->user_id,
                'month' => $request->month,
                'sessions' => $request->sessions,
                'price' => $request->price,
                'service_charge' => $request->service_charge,
                'subtotal' => $request->subtotal,
                'total_price' => $request->total_price,
                'title' => $request->title,
            ]),
            'cancel_url' => env('FRONTEND_URL') . '/payment/cancel',
        ]);

        return response()->json(['url' => $session->url]);
    }

    public function success(Request $request)
    {
        if ($request->has('sessions') && is_array($request->sessions)) {
            foreach ($request->sessions as $session) {
                $bookedTime = new BookedTime();
                $bookedTime->tutor_id = $request->tutor_id;
                $bookedTime->student_id = Student::where('user_id', $request->user_id)->first()->id;
                $bookedTime->month = $request->month;
                $bookedTime->day = explode(' ', $session)[0];
                $bookedTime->time_slot = explode(' ', $session)[1];
                $bookedTime->save();

                $payment = new Payment();
                $payment->user_id = $request->user_id;
                $payment->booked_time_id = $bookedTime->id;
                $payment->amount = ($request->price * ($request->service_charge + 1));
                $payment->status = 'success';
                $payment->save();
            }
        }

        $session = md5(uniqid(rand(), true) . time() . rand(1000, 9999));
        return redirect(
            env('FRONTEND_URL') . '/success?session=' . $session . '&sessions=' . base64_encode($session)
        );
    }
}
