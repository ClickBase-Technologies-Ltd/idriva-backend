<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class LearningController extends Controller
{
    public function index(): JsonResponse
    {
        // Replace with real DB fetch
        $courses = [
            ['id'=>1,'title'=>'Defensive Driving','description'=>'...','price'=>49.99,'thumbnail'=>null],
            // ...
        ];
        return response()->json($courses);
    }

    public function show($id): JsonResponse
    {
        // Replace with DB lookup and include modules/lessons and enrollment state for auth user
        $course = [
            'id'=>$id,
            'title'=>'Sample Course',
            'description'=>'Course details',
            'price'=>49.99,
            'enrolled'=> false,
            'modules'=> []
        ];
        return response()->json($course);
    }

    public function createCheckoutSession(Request $request, $id): JsonResponse
    {
        $user = auth()->user();
        // Load course from DB; fallback example:
        $course = ['id'=>$id, 'title'=>'Course '.$id, 'price'=> ($request->input('price') ?? 0)];

        Stripe::setApiKey(config('services.stripe.secret') ?? env('STRIPE_SECRET_KEY'));

        $session = Session::create([
            'payment_method_types' => ['card'],
            'mode' => 'payment',
            'customer_email' => $user->email ?? null,
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => ['name' => $course['title']],
                    'unit_amount' => intval(($course['price'] ?? 0) * 100),
                ],
                'quantity' => 1,
            ]],
            'success_url' => env('APP_URL') . "/dashboard/learning/{$id}?session_id={CHECKOUT_SESSION_ID}",
            'cancel_url' => env('APP_URL') . "/dashboard/learning/{$id}",
            'metadata' => [
                'course_id' => $id,
                'user_id' => $user->id ?? null
            ],
        ]);

        return response()->json(['url' => $session->url]);
    }
}