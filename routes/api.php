<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\PasswordResetController;
use App\Http\Controllers\Api\Auth\PhoneVerificationController;
use App\Http\Controllers\Api\ConversationController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\ListingController;
use App\Http\Controllers\Api\MyListingController;
use App\Http\Controllers\Api\MyReviewController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ReviewInviteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| JSON API for Nuxt (and later mobile). Prefix: /api
|
*/

Route::get('/health', function () {
    return response()->json([
        'ok' => true,
        'service' => 'baano-api',
    ]);
});

Route::get('/home', HomeController::class);
Route::get('/listings', [ListingController::class, 'index']);
Route::get('/listings/{listing}', [ListingController::class, 'show']);
Route::get('/categories', [MyListingController::class, 'categories']);

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/forgot-password', [PasswordResetController::class, 'forgot'])
    ->middleware('throttle:6,1');
Route::post('/reset-password', [PasswordResetController::class, 'reset'])
    ->middleware('throttle:6,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::put('/profile', [ProfileController::class, 'update']);

    Route::get('/phone/verification-status', [PhoneVerificationController::class, 'status']);
    Route::post('/phone/verification-notification', [PhoneVerificationController::class, 'resend'])
        ->middleware('throttle:6,1');
    Route::post('/phone/verify', [PhoneVerificationController::class, 'verify'])
        ->middleware('throttle:10,1');

    Route::get('/email/verification-status', [\App\Http\Controllers\Api\Auth\EmailVerificationController::class, 'notice']);
    Route::post('/email/verification-notification', [\App\Http\Controllers\Api\Auth\EmailVerificationController::class, 'resend'])
        ->middleware('throttle:6,1');

    Route::middleware('email.verified')->group(function () {
        Route::get('/favorites', [FavoriteController::class, 'index']);
        Route::post('/favorites/toggle', [FavoriteController::class, 'toggle']);
        Route::delete('/favorites/{id}', [FavoriteController::class, 'destroy']);

        Route::get('/my/listings', [MyListingController::class, 'index']);
        Route::post('/my/listings', [MyListingController::class, 'store']);
        Route::get('/my/listings/{listing}', [MyListingController::class, 'show']);
        Route::post('/my/listings/{listing}', [MyListingController::class, 'update']);
        Route::patch('/my/listings/{listing}/publication', [MyListingController::class, 'publication']);
        Route::delete('/my/listings/{listing}', [MyListingController::class, 'destroy']);

        Route::get('/conversations', [ConversationController::class, 'index']);
        Route::post('/conversations', [ConversationController::class, 'start']);
        Route::get('/conversations/{conversation}/messages', [ConversationController::class, 'messages']);
        Route::post('/conversations/{conversation}/messages', [ConversationController::class, 'send']);
        Route::delete('/conversations/{conversation}', [ConversationController::class, 'hide']);
        Route::post('/conversations/{conversation}/review-invite', [ReviewInviteController::class, 'store']);

        Route::get('/review-invites/{token}', [ReviewInviteController::class, 'show']);
        Route::post('/review-invites/{token}', [ReviewInviteController::class, 'submit']);

        Route::get('/my/reviews', [MyReviewController::class, 'index']);
    });
});
