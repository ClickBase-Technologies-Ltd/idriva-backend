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


// Route::middleware(['cors'])->group(function () {
// Public routes
Route::post('/resend-otp', [OtpController::class, 'resendOtp']);
Route::post('/verify-otp', [OtpController::class, 'verifyOtp']);
Route::post('/setup-password', [AuthController::class, 'setupPassword']);

Route::post('/signup', [AuthController::class, 'signup2']);
Route::post('/signin', [AuthController::class, 'signin']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::post('/refresh', [AuthController::class, 'refresh']);
Route::get('/users/profile', [AuthController::class, 'profile'])->middleware('auth.jwt'); // Use auth.jwt instead of auth:api
Route::get('/roles', [RolesController::class, 'index']);





Route::middleware(['auth.jwt'])->group(function () {
Route::get('/user', function () {
    $user = auth()->user(); // Use the 'api' guard for JWT

    return response()->json([
        'user' => [
            'id' => (string) $user->id,
            'full_name' => trim($user->firstName . ' ' . $user->lastName),
            'role' => $user->user_role->roleName ?? null,
        ],
        'profile' => [
            'profile_picture' => '/avatar.png', // Replace with $user->profile_picture if exists
            'cover_photo' => '/cover_photo.jpg',   // Replace with $user->cover_photo if exists
        ],
        'followersCount' => 42, // Replace with actual count from DB
        'followingCount' => 128, // Replace with actual count from DB
        'suggestedUsers' => [
            [
                'id' => '2',
                'full_name' => 'Jane Smith',
                'profile_picture' => '/avatar.png',
                'is_following' => false,
            ],
            [
                'id' => '3',
                'full_name' => 'Mike Johnson',
                'profile_picture' => '/avatar.png',
                'is_following' => true,
            ],
        ],
        'unreadCount' => 3, // Replace with actual unread notifications count
    ]);
});


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

Route::get('companies', [CompanyController::class, 'index']);
Route::post('companies', [CompanyController::class, 'store']);
Route::get('companies/{id}', [CompanyController::class, 'show']);
Route::post('companies/{id}', [CompanyController::class, 'update']);
Route::delete('companies/{id}', [CompanyController::class, 'destroy']);
Route::get('my-companies', [CompanyController::class, 'myCompanies']);

Route::post('jobs', [JobController::class, 'store']);
Route::get('jobs', [JobController::class, 'index']);
Route::get('jobs/{id}', [JobController::class, 'show']);
Route::post('jobs/{id}', [JobController::class, 'update']);
Route::delete('jobs/{id}', [JobController::class, 'destroy']);
Route::get('my-jobs', [JobController::class, 'myJobs']);
Route::post('jobs/{id}/apply', [RecruitmentJobApplicationsController::class, 'store']);
Route::get('jobs/{id}/application-status', [RecruitmentJobApplicationsController::class, 'checkApplicationStatus']);
});
