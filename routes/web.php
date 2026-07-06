<?php

use App\Http\Controllers\Public\ListingController as PublicListingController;
use App\Http\Controllers\User\ListingController as UserListingController;
use App\Http\Controllers\User\FavoriteController;
use App\Http\Controllers\User\ReviewController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Api\PushNotificationController;
use App\Http\Controllers\Api\TelegramWebhookController;
use App\Http\Controllers\ListingController;
use Illuminate\Support\Facades\Route;

// ============================================
// ГЛАВНАЯ СТРАНИЦА (Vue + Inertia)
// ============================================
Route::get('/', [ListingController::class, 'index'])->name('listings.index');
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
// ИЗБРАННОЕ
// ============================================
Route::middleware(['auth'])->group(function () {
    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/favorites/{listing}', [FavoriteController::class, 'toggle'])->name('favorites.toggle');
});

// ============================================
// СОЗДАНИЕ ОБЪЯВЛЕНИЯ (только для авторизованных)
// ============================================
Route::middleware(['auth'])->group(function () {
//    Route::get('/listings/create', [ListingController::class, 'create'])->name('listings.create');
//    Route::post('/listings', [ListingController::class, 'store'])->name('listings.store');
//    Route::get('/listings/{listing}', [ListingController::class, 'show'])->name('listings.show');
});

// ============================================
// АДМИНКА (только для авторизованных)
// ============================================
Route::middleware(['auth'])->prefix('manage')->name('admin.')->group(function () {    
    // Главная админки
    Route::get('/', function () {
        $stats = [
            'users' => \App\Models\User::count(),
            'listings' => \App\Models\Listing::count(),
            'categories' => \App\Models\Category::count(),
            'pending' => \App\Models\Listing::where('is_active', false)->count(),
        ];
        return view('admin.dashboard', compact('stats'));
    })->name('dashboard');
    
    // Ресурсные маршруты
    Route::resource('users', \App\Http\Controllers\Admin\AdminUserController::class);
    Route::resource('listings', \App\Http\Controllers\Admin\AdminListingController::class);
    Route::resource('categories', \App\Http\Controllers\Admin\AdminCategoryController::class);
    
    // Настройки
    Route::get('/settings', function () {
        return view('admin.settings');
    })->name('settings');
});

// ============================================
// АУТЕНТИФИКАЦИЯ (Breeze)
// ============================================

// ============================================
// КАТЕГОРИИ (публичные)
// ============================================
Route::get('/categories/{category}', function(\App\Models\Category $category) {
    $listings = $category->listings()->where('is_active', true)->latest()->paginate(12);
    return view('public.categories.show', compact('category', 'listings'));
})->name('categories.show');

require __DIR__.'/auth.php';
