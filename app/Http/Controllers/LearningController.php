<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Support\Facades\Auth;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class LearningController extends Controller
{
    /**
     * GET /learning
     * Return all published courses with instructor info.
     */
    public function index(): JsonResponse
    {
        $courses = Course::with('instructor')
            ->where('published', 1)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($courses);
    }

    /**
     * GET /learning/{id}
     * Return single course with modules, lessons, and enrolled status.
     */
    public function show($id): JsonResponse
    {
        $course = Course::with(['modules.lessons' => function ($query) {
            $query->orderBy('position');
        }, 'modules' => function ($query) {
            $query->orderBy('position');
        }, 'instructor'])->findOrFail($id);

        // Ensure modules and lessons are always arrays
        $course->modules = $course->modules->map(function ($module) {
            $module->lessons = $module->lessons ?? collect([]);
            return $module;
        });

        // Check if authenticated user is enrolled in this course
        $user = Auth::user();
        $course->enrolled = $user
            ? $user->enrolledCourses()->where('course_id', $id)->exists()
            : false;

        return response()->json($course);
    }

    /**
     * POST /learning/{id}/checkout
     * Create Stripe checkout session for course purchase.
     */
    public function createCheckoutSession(Request $request, $id): JsonResponse
    {
        $user = Auth::user();
        $course = Course::findOrFail($id);

        Stripe::setApiKey(config('services.stripe.secret') ?? env('STRIPE_SECRET_KEY'));

        $session = Session::create([
            'payment_method_types' => ['card'],
            'mode' => 'payment',
            'customer_email' => $user->email ?? null,
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => $course->title,
                    ],
                    'unit_amount' => intval(($course->price ?? 0) * 100),
                ],
                'quantity' => 1,
            ]],
            'success_url' => env('APP_URL') . "/dashboard/learning/{$id}?session_id={CHECKOUT_SESSION_ID}",
            'cancel_url' => env('APP_URL') . "/dashboard/learning/{$id}",
            'metadata' => [
                'course_id' => $id,
                'user_id' => $user->id ?? null,
            ],
        ]);

        return response()->json(['url' => $session->url]);
    }

    /**
     * POST /learning/{id}/enroll
     * Enroll the authenticated user in a course (free enrollment).
     */
    public function enroll(Request $request, $id): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $course = Course::findOrFail($id);

        // Check if already enrolled
        $existing = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if ($existing) {
            return response()->json(['enrolled' => true, 'message' => 'Already enrolled']);
        }

        // Create enrollment
        $enrollment = Enrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => 'enrolled',
            'started_at' => now(),
            'payment_reference' => null,
        ]);

        return response()->json(['enrolled' => true, 'message' => 'Enrollment successful']);
    }
}
