<?php

use App\Http\Controllers\Public\ListingController as PublicListingController;
use App\Http\Controllers\User\ListingController as UserListingController;
use App\Http\Controllers\User\FavoriteController;
use App\Http\Controllers\User\ReviewController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Api\PushNotificationController;
use App\Http\Controllers\Api\TelegramWebhookController;
use Illuminate\Support\Facades\Route;

// ============================================
// ПУБЛИЧНЫЕ СТРАНИЦЫ (без авторизации)
// ============================================
Route::get('/', [PublicListingController::class, 'index'])->name('listings.index');
Route::get('/listings/{listing}', [PublicListingController::class, 'show'])->name('listings.show');

// ============================================
// TELEGRAM WEBHOOK (без авторизации!)
// ============================================
Route::post('/telegram/webhook', [TelegramWebhookController::class, 'handle']);

// ============================================
// АВТОРИЗОВАННЫЕ ПОЛЬЗОВАТЕЛИ
// ============================================
Route::middleware(['auth'])->group(function () {

    // Дашборд
    Route::get('/dashboard', function () {
        return view('user.dashboard');
    })->name('dashboard');

    // Профиль
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Личный кабинет пользователя (префикс user/)
    Route::prefix('user')->name('user.')->group(function () {
        // Объявления
        Route::resource('listings', UserListingController::class);

        // Избранное
        Route::get('favorites', [FavoriteController::class, 'index'])->name('favorites.index');
        Route::post('favorites/toggle', [FavoriteController::class, 'toggle'])->name('favorites.toggle');
        Route::delete('favorites/{id}', [FavoriteController::class, 'destroy'])->name('favorites.destroy');
    });

    // Отзывы (только для авторизованных)
    Route::post('/listings/{listing}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::put('/reviews/{review}', [ReviewController::class, 'update'])->name('reviews.update');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');

    // Push-уведомления
    Route::post('/api/push/subscribe', [PushNotificationController::class, 'subscribe']);
});

// ============================================
// АУТЕНТИФИКАЦИЯ (Breeze)
// ============================================
require __DIR__.'/auth.php';