<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Listing;
use App\Models\Category;
use App\Models\Favorite;
use App\Models\User;
use App\Models\Review;

// === СООБЩЕНИЯ (Маршруты для SPA-чата) ===
Route::prefix('messages')->name('messages.')->middleware('auth')->group(function () {
    // Главная страница сообщений (загружает список)
    Route::get('/', [\App\Http\Controllers\DashboardController::class, 'messages'])->name('index');
    
    // API для фоновой загрузки сообщений конкретного диалога (без перезагрузки страницы)
    Route::get('/api/{conversation}', [\App\Http\Controllers\DashboardController::class, 'getConversationMessages'])->name('api.show');
    
    // Отправка сообщения (принимает и JSON, и обычные запросы)
    Route::post('/{conversation}', [\App\Http\Controllers\DashboardController::class, 'sendMessage'])->name('store');
});

Route::post('/message-user/{user}', [\App\Http\Controllers\MessageController::class, 'messageUser'])
    ->middleware('auth')
    ->name('message-user');

// === ГЛАВНАЯ И ОБЪЯВЛЕНИЯ ===
Route::get('/', [\App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/listings', [\App\Http\Controllers\ListingController::class, 'index'])->name('listings.index');

Route::get('/listings/{listing}', function (Listing $listing) {
    $listing->load(['user', 'category']);
    
    $isFavorited = Auth::check() ? Favorite::where('user_id', Auth::id())
        ->where('favoritable_type', 'App\\Models\\Listing')
        ->where('favoritable_id', $listing->id)->exists() : false;
    
    $reviews = Review::where('listing_id', $listing->id)
        ->where('is_active', true)->with('user')->latest()->get();
    
    $similarListings = Listing::where('is_active', true)
        ->where('category_id', $listing->category_id)
        ->where('id', '!=', $listing->id)
        ->with(['category', 'user'])
        ->inRandomOrder()
        ->take(8)
        ->get()
        ->map(fn($l) => [
            'id' => $l->id, 'title' => $l->title, 'description' => $l->description ?? '',
            'price' => $l->price, 'location' => $l->location ?? '',
            'image' => $l->getFirstMediaUrl('images', 'thumb'),
            'category' => $l->category ? ['id' => $l->category->id, 'name' => $l->category->name] : null,
        ]);

    return Inertia::render('Listing/Show', [
        'listing' => [
            'id' => $listing->id, 'title' => $listing->title, 'description' => $listing->description,
            'price' => $listing->price, 'price_type' => $listing->price_type, 'location' => $listing->location,
            'images' => $listing->getMedia('images')->map(fn($m) => $m->getUrl()),
            'category' => $listing->category ? ['id' => $listing->category->id, 'name' => $listing->category->name] : null,
            'user_id' => $listing->user_id,
            'user' => $listing->user ? ['id' => $listing->user->id, 'name' => $listing->user->name] : null,
        ],
        'reviews' => $reviews->map(fn($r) => [
            'id' => $r->id, 'rating' => $r->rating, 'comment' => $r->comment,
            'created_at' => $r->created_at,
            'user' => $r->user ? ['id' => $r->user->id, 'name' => $r->user->name] : null,
        ]),
        'isFavorited' => $isFavorited,
        'auth' => ['user' => Auth::user()],
        'similarListings' => $similarListings,
    ]);
})->name('listings.show');

Route::post('/telegram/webhook', [\App\Http\Controllers\Api\TelegramWebhookController::class, 'handle']);

// === АВТОРИЗАЦИЯ ===
Route::middleware('guest')->group(function () {
    Route::get('/login', function () { return Inertia::render('Auth/Login'); })->name('login');
    
    Route::post('/login', function (Request $request) {
        $credentials = $request->validate(['email' => 'required|email', 'password' => 'required']);
        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended('/');
        }
        return back()->withErrors(['email' => 'Неверные учетные данные']);
    });

    Route::get('/register', function () {
        session()->forget('errors');
        return Inertia::render('Auth/Register');
    })->name('register');

    Route::post('/register', function (Request $request) {
        try {
            $phone = preg_replace('/\D/', '', $request->phone);
            $formattedPhone = '+' . $phone;
            
            if (User::where('phone', $formattedPhone)->exists()) {
                return back()->withErrors(['phone' => 'Этот телефон уже зарегистрирован']);
            }
            if (User::where('email', $request->email)->exists()) {
                return back()->withErrors(['email' => 'Этот email уже зарегистрирован']);
            }
            if ($request->password !== $request->password_confirmation) {
                return back()->withErrors(['password' => 'Пароли не совпадают']);
            }
            
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255',
                'password' => 'required|string|min:8',
            ]);
            
            $user = User::create([
                'name' => $validated['name'], 'phone' => $formattedPhone,
                'email' => $validated['email'], 'password' => bcrypt($validated['password']),
            ]);
            
            Auth::login($user);
            $user->sendEmailVerificationNotification();
            return redirect('/verify-email');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors());
        } catch (\Exception $e) {
            \Log::error('Registration error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Ошибка регистрации']);
        }
    });
});

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/verify-email', [\App\Http\Controllers\Auth\EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [\App\Http\Controllers\Auth\EmailVerificationController::class, 'verify'])->name('verification.verify');
    Route::post('/email/verification-notification', [\App\Http\Controllers\Auth\EmailVerificationController::class, 'resend'])->name('verification.send');
});

// === ЛИЧНЫЙ КАБИНЕТ ПОЛЬЗОВАТЕЛЯ ===
Route::middleware(['auth'])->group(function () {
    Route::get('/user/favorites', function () {
        $favorites = Favorite::where('user_id', Auth::id())->with(['favoritable.user', 'favoritable.category'])->latest()->get();
        $favoritesData = $favorites->map(function ($favorite) {
            $listing = $favorite->favoritable;
            return [
                'id' => $favorite->id,
                'favoritable' => $listing ? [
                    'id' => $listing->id, 'title' => $listing->title, 'price' => $listing->price,
                    'image' => $listing->getFirstMediaUrl('images', 'thumb'),
                    'category' => $listing->category ? ['id' => $listing->category->id, 'name' => $listing->category->name] : null,
                ] : null,
            ];
        })->filter(fn($item) => $item['favoritable'] !== null);
        
        return Inertia::render('Favorites', ['favorites' => $favoritesData->values(), 'auth' => ['user' => Auth::user()]]);
    })->name('user.favorites.index');
    
    Route::middleware(['email.verified'])->group(function () {
        Route::post('/user/favorites/toggle', function (Request $request) {
            $validated = $request->validate(['listing_id' => 'required|exists:listings,id']);
            $listing = Listing::findOrFail($validated['listing_id']);
            if ($listing->user_id === Auth::id()) return back()->with('error', 'Нельзя добавить своё объявление в избранное');
            
            $favorite = Favorite::where('user_id', Auth::id())->where('favoritable_id', $listing->id)->where('favoritable_type', 'App\\Models\\Listing')->first();
            if ($favorite) {
                $favorite->delete();
                return back()->with('success', 'Удалено из избранного');
            } else {
                Favorite::create(['user_id' => Auth::id(), 'favoritable_id' => $listing->id, 'favoritable_type' => 'App\\Models\\Listing']);
                return back()->with('success', 'Добавлено в избранное');
            }
        })->name('user.favorites.toggle');
        
        Route::delete('/user/favorites/{id}', function ($id) {
            Favorite::where('user_id', Auth::id())->where('id', $id)->firstOrFail()->delete();
            return back()->with('success', 'Удалено из избранного');
        })->name('user.favorites.destroy');
    });
    
    Route::get('/user/listings', function () {
        $listings = Listing::where('user_id', Auth::id())->with('category')->latest()->get();
        return Inertia::render('Listing/Index', [
            'listings' => $listings->map(fn($l) => [
                'id' => $l->id, 'title' => $l->title, 'price' => $l->price,
                'status' => $l->is_active ? 'active' : 'pending',
                'category' => $l->category ? ['name' => $l->category->name] : null,
            ]),
            'auth' => ['user' => Auth::user()],
        ]);
    })->name('user.listings.index');
    
    Route::middleware(['email.verified'])->group(function () {
        Route::get('/user/listings/create', function () {
            $categories = Category::with('children.children')->whereNull('parent_id')->get();
            return Inertia::render('Listing/Create', ['categories' => $categories, 'auth' => ['user' => Auth::user()]]);
        })->name('user.listings.create');
        
        Route::post('/user/listings', function (Request $request) {
            $validated = $request->validate([
                'category_id' => 'required|exists:categories,id', 'title' => 'required|max:255',
                'description' => 'required', 'price' => 'required|numeric|min:0',
                'price_type' => 'required|in:fixed,hourly,daily,monthly,negotiable',
                'location' => 'nullable|string|max:255', 'images' => 'nullable|array|max:10', 'images.*' => 'image|max:2048',
            ]);
            
            $listing = Listing::create([
                'user_id' => Auth::id(), 'category_id' => $validated['category_id'],
                'title' => $validated['title'], 'description' => $validated['description'],
                'price' => $validated['price'], 'price_type' => $validated['price_type'],
                'location' => $validated['location'] ?? null, 'is_active' => false,
            ]);
            
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $listing->addMedia($image)->toMediaCollection('images');
                }
            }
            return redirect('/user/listings')->with('success', 'Объявление создано');
        })->name('user.listings.store');
        
        Route::get('/user/listings/{listing}/edit', function (Listing $listing) {
            if ($listing->user_id !== Auth::id()) abort(403);
            return Inertia::render('Listing/Edit', [
                'listing' => [
                    'id' => $listing->id, 'title' => $listing->title, 'description' => $listing->description,
                    'price' => $listing->price, 'price_type' => $listing->price_type,
                    'location' => $listing->location, 'category_id' => $listing->category_id,
                    'images' => $listing->getMedia('images')->map(fn($m) => $m->getUrl()),
                ],
                'categories' => Category::all()->map(fn($c) => ['id' => $c->id, 'name' => $c->name]),
                'auth' => ['user' => Auth::user()],
            ]);
        })->name('user.listings.edit');
        
        Route::post('/user/listings/{listing}', function (Request $request, Listing $listing) {
            if ($listing->user_id !== Auth::id()) abort(403);
            $validated = $request->validate([
                'category_id' => 'required|exists:categories,id', 'title' => 'required|max:255',
                'description' => 'required', 'price' => 'required|numeric|min:0',
                'price_type' => 'required|in:fixed,hourly,daily,monthly,negotiable',
                'location' => 'nullable|string|max:255', 'images' => 'nullable|array|max:10', 'images.*' => 'image|max:2048',
            ]);
            
            $listing->update([
                'category_id' => $validated['category_id'], 'title' => $validated['title'],
                'description' => $validated['description'], 'price' => $validated['price'],
                'price_type' => $validated['price_type'], 'location' => $validated['location'] ?? null,
            ]);
            
            if ($request->hasFile('images')) {
                $listing->clearMediaCollection('images');
                foreach ($request->file('images') as $image) {
                    $listing->addMedia($image)->toMediaCollection('images');
                }
            }
            return redirect('/user/listings')->with('success', 'Объявление обновлено');
        })->name('user.listings.update');
        
        Route::delete('/user/listings/{listing}', function (Listing $listing) {
            if ($listing->user_id !== Auth::id()) abort(403);
            $listing->delete();
            return back()->with('success', 'Объявление удалено');
        })->name('user.listings.destroy');
        
        Route::post('/listings/{listing}/reviews', [\App\Http\Controllers\User\ReviewController::class, 'store'])->name('reviews.store');
        Route::put('/reviews/{review}', [\App\Http\Controllers\User\ReviewController::class, 'update'])->name('reviews.update');
        Route::delete('/reviews/{review}', [\App\Http\Controllers\User\ReviewController::class, 'destroy'])->name('reviews.destroy');
    });
});

// === АДМИН-ПАНЕЛЬ ===
Route::middleware(['auth'])->prefix('manage')->name('admin.')->group(function () {
    Route::get('/', function () {
        $stats = ['users' => User::count(), 'listings' => Listing::count(), 'categories' => Category::count(), 'pending' => Listing::where('is_active', false)->count()];
        return view('admin.dashboard', compact('stats'));
    })->name('dashboard');
    Route::resource('users', \App\Http\Controllers\Admin\AdminUserController::class);
    Route::resource('listings', \App\Http\Controllers\Admin\AdminListingController::class);
    Route::resource('categories', \App\Http\Controllers\Admin\AdminCategoryController::class);
    Route::get('/settings', function () { return view('admin.settings'); })->name('settings');
});

// === DASHBOARD (Личный кабинет с боковой панелью) ===
Route::middleware(['auth'])->prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('/', [\App\Http\Controllers\DashboardController::class, 'index'])->name('index');
    Route::get('/listings', [\App\Http\Controllers\DashboardController::class, 'listings'])->name('listings');
    Route::get('/favorites', [\App\Http\Controllers\DashboardController::class, 'favorites'])->name('favorites');
    Route::get('/messages', [\App\Http\Controllers\DashboardController::class, 'messages'])->name('messages');
    Route::get('/messages/{conversation?}', [\App\Http\Controllers\DashboardController::class, 'messages'])->name('messages.show');
    Route::post('/messages/{conversation}', [\App\Http\Controllers\DashboardController::class, 'sendMessage'])->name('messages.send');
    Route::get('/reviews', [\App\Http\Controllers\DashboardController::class, 'reviews'])->name('reviews');
});

// Профиль
Route::middleware(['auth'])->group(function () {
    Route::get('/profile/edit', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
});
