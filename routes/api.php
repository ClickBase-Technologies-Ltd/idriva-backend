<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CancerController;
use App\Http\Controllers\BeneficiariesController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\StateController;
use App\Http\Controllers\LgaController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\MinistryController;
use App\Http\Controllers\AgentsController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\ProductRequestController;
use App\Http\Controllers\TransactionsController;
use App\Http\Controllers\ProjectsController;
use App\Http\Controllers\HubsController;
use App\Http\Controllers\MSPsController;
use App\Http\Controllers\FarmersController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\CommodityController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\RecruitmentJobApplicationsController;
use App\Http\Controllers\LearningController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\InstructorCourseController;
use App\Http\Controllers\InstructorModuleController;
use App\Http\Controllers\InstructorLessonController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// --------------------
// Public routes
// --------------------
Route::post('/resend-otp', [OtpController::class, 'resendOtp']);
Route::post('/verify-otp', [OtpController::class, 'verifyOtp']);
Route::post('/setup-password', [AuthController::class, 'setupPassword']);

Route::post('/signup', [AuthController::class, 'signup2']);
Route::post('/signin', [AuthController::class, 'signin']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::post('/refresh', [AuthController::class, 'refresh']);

Route::get('/roles', [RolesController::class, 'index']);

// Stripe webhook (public)
Route::post('stripe/webhook', [StripeWebhookController::class, 'handle']);

// --------------------
// Public Learning routes
// --------------------
Route::get('learning', [LearningController::class, 'index']); // List all courses
Route::get('learning/{id}', [LearningController::class, 'show']); // Single course

// Public endpoint to fetch a lesson (must be public so frontend can load lessons without auth)
Route::get('learning/{course}/lessons/{lesson}', [LessonController::class, 'show']);

// --------------------
// Authenticated routes
// --------------------
Route::middleware(['auth.jwt'])->group(function () {

    // User profile
    Route::get('/user', function () {
        $user = auth()->user();
        return response()->json([
            'user' => [
                'id' => (string) $user->id,
                'full_name' => trim($user->firstName . ' ' . $user->lastName),
                'role' => $user->user_role->roleName ?? null,
                'phoneNumber' => $user->phoneNumber,
                'email' => $user->email,
            ],
            'profile' => [
                'profile_picture' => '/avatar.png',
                'cover_photo' => '/cover_photo.jpg',
            ],
            'followersCount' => 42,
            'followingCount' => 128,
            'suggestedUsers' => [
                [
                    'id' => '2',
                    'full_name' => 'Jane Smith',
                    'profile_picture' => '/avatar.png',
                    'is_following' => false,
                ],
                'profile' => [
                    'profile_picture' => '/avatar.png',
                    'cover_photo' => '/cover_photo.jpg',
                ],
            ],
            'unreadCount' => 3,
        ]);
    });

    Route::get('profile/biodata', [UsersController::class, 'userBiodataProfile']);
    Route::get('profile/education', [UsersController::class, 'userEducationProfile']);
    Route::get('profile/experience', [UsersController::class, 'userExperienceProfile']);
    Route::get('profile/skills', [UsersController::class, 'userSkillsProfile']);
    Route::post('profile/upload-image', [UsersController::class, 'uploadProfileImage']);

    // Applications
    Route::get('applications', [RecruitmentJobApplicationsController::class, 'index']);
    Route::put('applications/{applicantId}/status', [RecruitmentJobApplicationsController::class, 'updateApplicationStatus']);

    // Posts
    Route::post('posts', [PostController::class, 'store']);
    Route::get('posts', [PostController::class, 'index']);
    Route::get('posts/{id}', [PostController::class, 'show']);
    Route::put('posts/{id}', [PostController::class, 'update']);
    Route::delete('posts/{id}', [PostController::class, 'destroy']);
    Route::post('posts/{id}/like', [PostController::class, 'likePost']);
    Route::post('posts/{id}/unlike', [PostController::class, 'unlikePost']);
    Route::post('posts/{id}/share', [PostController::class, 'sharePost']);
    Route::post('posts/{id}/unshare', [PostController::class, 'unsharePost']);
    Route::post('posts/{id}/comment', [PostController::class, 'commentPost']);
    Route::post('posts/{id}/uncomment', [PostController::class, 'uncommentPost']);
    Route::get('posts/{id}/comments', [PostController::class, 'getComments']);
    Route::get('posts/{id}/likes', [PostController::class, 'getLikes']);
    Route::get('posts/{id}/shares', [PostController::class, 'getShares']);

    // Companies
    Route::get('companies', [CompanyController::class, 'index']);
    Route::post('companies', [CompanyController::class, 'store']);
    Route::get('companies/{id}', [CompanyController::class, 'show']);
    Route::post('companies/{id}', [CompanyController::class, 'update']);
    Route::delete('companies/{id}', [CompanyController::class, 'destroy']);
    Route::get('my-companies', [CompanyController::class, 'myCompanies']);

    // Jobs
    Route::post('jobs', [JobController::class, 'store']);
    Route::get('jobs', [JobController::class, 'index']);
    Route::get('jobs/{id}', [JobController::class, 'show']);
    Route::post('jobs/{id}', [JobController::class, 'update']);
    Route::delete('jobs/{id}', [JobController::class, 'destroy']);
    Route::get('my-jobs', [JobController::class, 'myJobs']);
    Route::post('jobs/{id}/apply', [RecruitmentJobApplicationsController::class, 'store']);
    Route::get('jobs/{id}/application-status', [RecruitmentJobApplicationsController::class, 'checkApplicationStatus']);

    // Authenticated Learning actions with Paystack
    Route::post('learning/{id}/checkout', [LearningController::class, 'createCheckoutSession']);
    Route::post('learning/{id}/enroll', [LearningController::class, 'enroll']);

    // Payment verification can be authenticated or public depending on your flow.
    // If your frontend calls the verify endpoint without auth (e.g., Paystack callback),
    // consider making it public. If you want it protected, keep it here.
    Route::get('learning/{id}/payment-verify', [LearningController::class, 'verifyPaymentApi']);

    // Instructor API
    Route::get('instructor/courses', [InstructorCourseController::class, 'index']);
    Route::get('instructor/courses/{id}', [InstructorCourseController::class, 'show']);
    Route::post('instructor/courses', [InstructorCourseController::class, 'store']);
    Route::put('instructor/courses/{id}', [InstructorCourseController::class, 'update']);
    Route::patch('instructor/courses/{id}', [InstructorCourseController::class, 'update']);
    Route::delete('instructor/courses/{id}', [InstructorCourseController::class, 'destroy']);

    Route::get('instructor/modules', [InstructorModuleController::class, 'index']);
    Route::post('instructor/modules', [InstructorModuleController::class, 'store']);

    Route::get('instructor/lessons', [InstructorLessonController::class, 'index']);
    Route::post('instructor/lessons', [InstructorLessonController::class, 'store']);
    Route::get('instructor/lessons/{id}', [InstructorLessonController::class, 'show']);
    Route::put('instructor/lessons/{id}', [InstructorLessonController::class, 'update']);
    Route::patch('instructor/lessons/{id}', [InstructorLessonController::class, 'update']);
    Route::delete('instructor/lessons/{id}', [InstructorLessonController::class, 'destroy']);
});
