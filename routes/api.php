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
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SuggestedFollowersController;
use App\Http\Controllers\ChatController;
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
use App\Http\Controllers\FollowController;
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
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('/resend-otp', [OtpController::class, 'resendOtp']);
Route::post('/verify-otp', [OtpController::class, 'verifyOtp']);
Route::post('/setup-password', [AuthController::class, 'setupPassword']);

Route::post('/signup', [AuthController::class, 'signup2']);
Route::post('/signin', [AuthController::class, 'signin']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::post('/refresh', [AuthController::class, 'refresh']);
Route::get('/users/profile', [AuthController::class, 'profile'])->middleware('auth.jwt');
Route::get('/roles', [RolesController::class, 'index']);

// Stripe webhook (public)
Route::post('stripe/webhook', [StripeWebhookController::class, 'handle']);

Route::middleware(['auth.jwt'])->group(function () {

    Route::get('/user', function () {
        $user = auth()->user(); // Use the 'api' guard for JWT

        return response()->json([
            'user' => [
                // 'id' => (string) $user->id,
                'id' => $user->id,
                'full_name' => trim($user->firstName . ' ' . $user->lastName . ' ' . ($user->otherNames ?? '')),
                'role' => $user->user_role->roleName ?? null,
                'phoneNumber' => $user->phoneNumber,
                'email' => $user->email,
                
            ],
            'profile' => [
                'profileImage' => $user->profileImage ?? '/avatar.png',
                'coverImage' => $user->coverImage ?? '/cover_photo.jpg',
            ],
            'followersCount' => $user->followersCount,
            'followingCount' => $user->followingCount,
            'suggestedUsers' => [
                [
                    'id' => '2',
                    'full_name' => 'Jane Smith',
                    'profile_picture' => '/avatar.png',
                    'is_following' => false,
                ],
            ],
            'unreadCount' => 3,
        ]);
    });

    // User profile routes
    Route::get('profile/biodata', [UsersController::class, 'userBiodataProfile']);
    Route::get('profile/education', [UsersController::class, 'userEducationProfile']);
    Route::get('profile/experience', [UsersController::class, 'userExperienceProfile']);
    Route::get('profile/skills', [UsersController::class, 'userSkillsProfile']);
    Route::get('profile/drivers-license', [UsersController::class, 'userDriversLicenseProfile']);

    Route::post('profile/biodata', [UsersController::class, 'storeUserBiodata']);
    Route::post('profile/education', [UsersController::class, 'storeUserEducation']);
    Route::post('profile/experience', [UsersController::class, 'storeUserExperience']);
    Route::post('profile/skills', [UsersController::class, 'storeUserSkills']);
    Route::post('profile/drivers-license', [UsersController::class, 'storeUserDriversLicense']);

    Route::delete('profile/education/{id}', [UsersController::class, 'deleteUserEducation']);
    Route::delete('profile/experience/{id}', [UsersController::class, 'deleteUserExperience']);
    Route::delete('profile/skills/{id}', [UsersController::class, 'deleteUserSkills']);
    Route::delete('profile/drivers-license/{id}', [UsersController::class, 'deleteUserDriversLicense']);

    Route::post('profile/upload-image', [UsersController::class, 'uploadProfileImage']);
    Route::post('profile/upload-cover-image', [UsersController::class, 'uploadCoverImage']);

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
    Route::get('jobs/{id}/applications', [RecruitmentJobApplicationsController::class, 'getJobApplications']);
    Route::get('my-applications', [JobController::class, 'myApplications']);
    
    // Learning endpoints (authenticated)
    Route::get('learning', [LearningController::class, 'index']);
    Route::get('learning/{id}', [LearningController::class, 'show']);
    Route::post('learning/{id}/checkout', [LearningController::class, 'createCheckoutSession']);

    // **Added payment verification route**
    Route::get('learning/{id}/payment-verify', [LearningController::class, 'paymentVerify']);

    // Instructor API - courses / modules / lessons
    Route::get('instructor/courses', [InstructorCourseController::class, 'index']);
    Route::post('instructor/courses', [InstructorCourseController::class, 'store']);
    Route::get('instructor/courses/{id}', [InstructorCourseController::class, 'show']);
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

    // Fetch a single lesson for a course
    Route::get('learning/{courseId}/lessons/{lessonId}', [LessonController::class, 'show']);

      // Chat routes
    Route::prefix('chat')->group(function () {
        Route::get('/users', [ChatController::class, 'getChatUsers']);
        Route::get('/messages/{userId}', [ChatController::class, 'getMessages']);
        Route::post('/send', [ChatController::class, 'sendMessage']);
        Route::post('/mark-read', [ChatController::class, 'markAsRead']);
        Route::get('/unread-count', [ChatController::class, 'getUnreadCount']);
        Route::post('/update-last-seen', [ChatController::class, 'updateLastSeen']);
    });



     // Follow routes
    Route::post('/users/{userId}/follow', [FollowController::class, 'follow']);
    Route::post('/users/{userId}/unfollow', [FollowController::class, 'unfollow']);
    Route::delete('/users/{userId}/followers/{followerId}', [FollowController::class, 'removeFollower']);
    
    // Get followers/following
    Route::get('/users/{userId}/followers', [FollowController::class, 'getFollowers']);
    Route::get('/users/{userId}/following', [FollowController::class, 'getFollowing']);
    Route::get('/users/{userId}/follow-stats', [FollowController::class, 'getFollowStats']);
    
    // Check follow status
    Route::get('/users/{userId}/follow-status', [FollowController::class, 'getFollowStatus']);
    
    // Search followers/following
    Route::get('/users/{userId}/follow-search', [FollowController::class, 'searchFollowers']);
    Route::get('/users/suggested', [FollowController::class, 'suggestedUsers']);


    // Route::get('/suggested-followers', [SuggestedFollowersController::class, 'index']);
    
    // Alternative with Laravel paginator
    Route::get('/suggested-followers-paginator', [FollowController::class, 'suggestedUsersPaginated']);
    
    // Advanced algorithm
    Route::get('/suggested-followers-advanced', [SuggestedFollowersController::class, 'advancedSuggestions']);

    Route::post('/follow', [FollowController::class, 'toggleFollow']);
    
    // Get followers
    // Route::get('/user', [UsersController::class, 'currentUser']);
    Route::get('/suggested-followers', [SuggestedFollowersController::class, 'getFollowers']);
    Route::get('/following', [SuggestedFollowersController::class, 'getFollowing']);


    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/stats', [NotificationController::class, 'stats']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
    Route::delete('/notifications/clear-all', [NotificationController::class, 'clearAll']);

    Route::get('/users/{id}/profile', [UsersController::class, 'profile']);
});
