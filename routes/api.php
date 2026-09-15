<?php

use App\Http\Controllers\Admin\AdminListingController;
use App\Http\Controllers\Admin\AdminPaymentController;
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\Admin\AdminReportController;
use App\Http\Controllers\Admin\AdminStatsController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\ListingController;
use App\Http\Controllers\Api\ListingImageController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RatingController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\VerificationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Site status (public)
Route::get('/site/status', [AdminSettingsController::class, 'index']);

// Auth
Route::post('/auth/register', [RegisterController::class, 'register']);
Route::post('/auth/login', [LoginController::class, 'login']);
Route::post('/auth/forgot-password', [PasswordController::class, 'forgotPassword']);
Route::post('/auth/reset-password', [PasswordController::class, 'resetPassword']);

// Categories
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);

// Listings (public)
Route::get('/listings', [ListingController::class, 'index']);
Route::get('/listings/{slug}', [ListingController::class, 'show'])->where('slug', '[a-zA-Z0-9-]+');

// Search
Route::get('/search', [SearchController::class, 'search']);

// Public profiles
Route::get('/users/{user}', [ProfileController::class, 'show']);
Route::get('/users/{user}/listings', [ProfileController::class, 'userListings']);
Route::get('/users/{user}/ratings', [RatingController::class, 'index']);

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/auth/logout', [LoginController::class, 'logout']);
    Route::get('/auth/user', [LoginController::class, 'user']);
    Route::post('/auth/verify-phone', [VerificationController::class, 'verifyPhone']);
    Route::post('/auth/resend-code', [VerificationController::class, 'resendCode']);

    // Profile
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::get('/profile/listings', [ProfileController::class, 'myListings']);

    // Listings CRUD
    Route::post('/listings', [ListingController::class, 'store']);
    Route::put('/listings/{listing}', [ListingController::class, 'update']);
    Route::delete('/listings/{listing}', [ListingController::class, 'destroy']);
    Route::post('/listings/{listing}/renew', [ListingController::class, 'renew']);
    Route::post('/listings/{listing}/mark-sold', [ListingController::class, 'markSold']);

    // Listing images
    Route::post('/listings/{listing}/images', [ListingImageController::class, 'store']);
    Route::delete('/listings/{listing}/images/{image}', [ListingImageController::class, 'destroy']);

    // Favorites
    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/favorites/{listing}', [FavoriteController::class, 'toggle']);

    // Messages
    Route::get('/conversations', [MessageController::class, 'conversations']);
    Route::get('/conversations/{user}/{listing}', [MessageController::class, 'thread']);
    Route::post('/messages', [MessageController::class, 'store']);
    Route::patch('/messages/{message}/read', [MessageController::class, 'markRead']);
    Route::get('/messages/unread-count', [MessageController::class, 'unreadCount']);

    // Reports
    Route::post('/listings/{listing}/report', [ReportController::class, 'store']);

    // Ratings
    Route::post('/users/{user}/rate', [RatingController::class, 'store']);

    /*
    |--------------------------------------------------------------------------
    | Admin Routes
    |--------------------------------------------------------------------------
    */

    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('/stats', [AdminStatsController::class, 'index']);
        Route::get('/settings', [AdminSettingsController::class, 'index']);
        Route::patch('/settings', [AdminSettingsController::class, 'update']);

        Route::get('/listings', [AdminListingController::class, 'index']);
        Route::patch('/listings/{listing}', [AdminListingController::class, 'update']);
        Route::delete('/listings/{listing}', [AdminListingController::class, 'destroy']);

        Route::get('/reports', [AdminReportController::class, 'index']);
        Route::patch('/reports/{report}', [AdminReportController::class, 'update']);

        Route::get('/payments', [AdminPaymentController::class, 'index']);
        Route::patch('/payments/{payment}', [AdminPaymentController::class, 'update']);

        Route::get('/users', [AdminUserController::class, 'index']);
        Route::get('/users/{user}', [AdminUserController::class, 'show']);
        Route::patch('/users/{user}', [AdminUserController::class, 'update']);
    });

    // Super Admin only
    Route::middleware('super_admin')->prefix('admin')->group(function () {
        Route::get('/admins', [AdminUserController::class, 'admins']);
        Route::post('/users', [AdminUserController::class, 'store']);
        Route::patch('/users/{user}/role', [AdminUserController::class, 'setRole']);
    });
});
