<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class LearningController extends Controller
{
    /**
     * GET /learning
     * Return all published courses with instructor info and modules count.
     */
    public function index(): JsonResponse
    {
        $courses = Course::with('instructor')
            ->withCount('modules')
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

        // Check if authenticated user is enrolled
        $user = Auth::user();
        $course->enrolled = $user
            ? $user->enrolledCourses()->where('course_id', $id)->exists()
            : false;

        return response()->json($course);
    }

    /**
     * POST /learning/{id}/checkout
     * Create Paystack payment session for course purchase (NGN).
     */
    public function createCheckoutSession(Request $request, $id): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $course = Course::findOrFail($id);
        if (!$course->price || $course->price <= 0) {
            return response()->json(['message' => 'Course is free. No payment needed.']);
        }

        $paystackSecret = env('PAYSTACK_SECRET_KEY');
        $callbackUrl = env('APP_URL') . "/dashboard/learning/{$id}/payment-callback";

        $response = Http::withToken($paystackSecret)
            ->post('https://api.paystack.co/transaction/initialize', [
                'email' => $user->email,
                'amount' => intval($course->price * 100), // Naira to kobo
                'currency' => 'NGN',
                'callback_url' => $callbackUrl,
                'metadata' => [
                    'course_id' => $id,
                    'user_id' => $user->id,
                    'course_title' => $course->title,
                ],
            ]);

        if ($response->successful()) {
            $data = $response->json();
            return response()->json(['authorization_url' => $data['data']['authorization_url']]);
        }

        return response()->json([
            'message' => 'Failed to initiate payment. Try again later.',
            'error' => $response->body()
        ], 500);
    }

    /**
     * POST /learning/{id}/enroll
     * Enroll the authenticated user in a course (free enrollment or after payment).
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
            'payment_reference' => $request->input('payment_reference') ?? null,
        ]);

        return response()->json(['enrolled' => true, 'message' => 'Enrollment successful']);
    }
}
