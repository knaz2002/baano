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
use App\Http\Controllers\MessageController;

Route::post('/message-user/{user}', [MessageController::class, 'messageUser'])
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

    $conversation = null;
    $chatMessages = [];

    if (Auth::check() && $listing->user_id !== Auth::id()) {
        $userId = Auth::id();
        $conversation = \App\Models\Conversation::where(
            'listing_id',
            $listing->id
        )
            ->where(function ($query) use ($listing, $userId) {
                $query
                    ->where(function ($q) use ($listing, $userId) {
                        $q->where('user_one_id', $userId)
                            ->where('user_two_id', $listing->user_id);
                    })
                    ->orWhere(function ($q) use ($listing, $userId) {
                        $q->where('user_one_id', $listing->user_id)
                            ->where('user_two_id', $userId);
                    });
            })
            ->first();

        if ($conversation) {
            $chatMessages = $conversation->messages()
                ->with('sender:id,name')
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(fn($m) => [
                    'id' => $m->id, 'body' => $m->body, 'sender_id' => $m->sender_id,
                    'sender_name' => $m->sender->name, 'is_mine' => $m->sender_id === Auth::id(),
                    'created_at' => $m->created_at->format('H:i'),
                ]);
        }
    }

    $userReview = null;
    $canReview = false;

    if (Auth::check()) {
        $userReview = Review::where('listing_id', $listing->id)->where('user_id', Auth::id())->first();
        if ($listing->user_id !== Auth::id() && !$userReview) {
            $canReview = true;
        }
    }

    return Inertia::render('Listing/Show', [
        'listing' => [
            'id' => $listing->id, 'title' => $listing->title, 'description' => $listing->description,
            'price' => $listing->price,
            'price_type' => $listing->price_type,
            'location' => $listing->location,
            'custom_attributes' => $listing->listing_attributes ?? [],
            'images' => $listing->getMedia('images')
                        ->map(fn($media) => [
                            'id' => $media->id,
                            'url' => $media->getUrl(),
                        ])
                        ->values(),
            'category' => $listing->category ? ['id' => $listing->category->id, 'name' => $listing->category->name] : null,
            'user_id' => $listing->user_id,
            'user' => $listing->user ? ['id' => $listing->user->id, 'name' => $listing->user->name] : null,
            'created_at' => $listing->created_at, 'views' => $listing->views ?? 0,
        ],
        'reviews' => $reviews->map(fn($r) => [
            'id' => $r->id, 'rating' => $r->rating, 'comment' => $r->comment,
            'created_at' => $r->created_at,
            'user' => $r->user ? ['id' => $r->user->id, 'name' => $r->user->name] : null,
        ]),
        'isFavorited' => $isFavorited,
        'auth' => ['user' => Auth::user()],
        'conversation' => $conversation ? ['id' => $conversation->id] : null,
        'chatMessages' => $chatMessages,
        'similarListings' => $similarListings,
        'canReview' => $canReview,
        'userReview' => $userReview ? [
            'id' => $userReview->id, 'rating' => $userReview->rating,
            'comment' => $userReview->comment, 'is_active' => $userReview->is_active,
        ] : null,
    ]);
})->name('listings.show');

Route::post('/telegram/webhook', [\App\Http\Controllers\Api\TelegramWebhookController::class, 'handle']);

// === АВТОРИЗАЦИЯ (GUEST) ===
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
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'phone' => [
                    'required',
                    'string',
                    'regex:/^\\+7 \\(\\d{3}\\) \\d{3}-\\d{2}-\\d{2}$/',
                ],
                'email' => ['required', 'string', 'email', 'max:255'],
                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'confirmed',
                ],
                'password_confirmation' => [
                    'required',
                    'string',
                ],
                'personal_data_consent' => [
                'required',
                'accepted',
            ],
        ], [
                'phone.regex' => 'Введите телефон полностью.',
                'password.confirmed' => 'Пароли не совпадают.',
                'personal_data_consent.required' => 'Необходимо согласие на обработку персональных данных.',
            'personal_data_consent.accepted' => 'Необходимо согласие на обработку персональных данных.',
        ]);

            $phone = preg_replace('/\\D/', '', $validated['phone']);
            $formattedPhone = '+' . $phone;

            if (User::where('phone', $formattedPhone)->exists()) {
                return back()->withErrors([
                    'phone' => 'Этот телефон уже зарегистрирован',
                ]);
            }

            if (User::where('email', $validated['email'])->exists()) {
                return back()->withErrors([
                    'email' => 'Этот email уже зарегистрирован',
                ]);
            }

            $user = User::create([
                'name' => $validated['name'],
                'phone' => $formattedPhone,
                'email' => $validated['email'],
                'password' => bcrypt($validated['password']),
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

// === ВЕРИФИКАЦИЯ EMAIL ===
Route::middleware(['auth'])->group(function () {
    Route::get('/verify-email', [\App\Http\Controllers\Auth\EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [\App\Http\Controllers\Auth\EmailVerificationController::class, 'verify'])->middleware('signed')->name('verification.verify');
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
            if ($listing->user_id === Auth::id()) {
                return back()->with('error', 'Нельзя добавить своё объявление в избранное');
            }
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
                'id' => $l->id,
                'title' => $l->title,
                'price' => $l->price,
                'status' => $l->status,
                'is_active' => $l->is_active,
                'requested_is_active' => $l->requested_is_active,
                'category' => $l->category
                    ? ['name' => $l->category->name]
                    : null,
                'image' => $l->getFirstMediaUrl('images', 'thumb'),
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
                'category_id' => 'required|exists:categories,id',
                'title' => 'required|max:255',
                'description' => 'required',
                'price' => 'required|numeric|min:0',
                'price_type' => 'required|in:fixed,hourly,daily,monthly,negotiable',
                'location' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:120',
                'attributes' => 'nullable|string', // <-- ДОБАВЛЕНО
                'images' => 'required|array|min:1|max:10',
                'images.*' => 'image|max:2048',
            ]);

            $listing = Listing::create([
                'user_id' => Auth::id(),
                'category_id' => $validated['category_id'],
                'title' => $validated['title'],
                'description' => $validated['description'],
                'price' => $validated['price'],
                'price_type' => $validated['price_type'],
                'location' => $validated['location'] ?? null,
                'city' => $validated['city'] ?? null,
                'listing_attributes' => $request->filled('attributes') ? json_decode($request->input('attributes'), true) : null, // <-- ДОБАВЛЕНО
                'status' => 'pending',
                'moderation_status' => \App\Enums\ModerationStatus::PendingModeration,
                'moderation_reason' => null,
                'moderated_at' => null,
                'is_active' => false,
                'requested_is_active' => true,
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
            $categories = Category::all();
            return Inertia::render('Listing/Edit', [
                'listing' => [
                    'id' => $listing->id, 'title' => $listing->title, 'description' => $listing->description,
                    'price' => $listing->price,
                    'price_type' => $listing->price_type,
                    'location' => $listing->location,
                    'city' => $listing->city,
                    'category_id' => $listing->category_id,
                    'attributes' => $listing->listing_attributes ?? [],
                    'images' => $listing->getMedia('images')
                        ->map(fn($media) => [
                            'id' => $media->id,
                            'url' => $media->getUrl(),
                        ])
                        ->values(),
                ],
                'categories' => $categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name]),
                'auth' => ['user' => Auth::user()],
            ]);
        })->name('user.listings.edit');

        Route::put('/user/listings/{listing}', function (Request $request, Listing $listing) {
            if ($listing->user_id !== Auth::id()) abort(403);

            $validated = $request->validate([
                'category_id' => 'required|exists:categories,id',
                'title' => 'required|max:255',
                'description' => 'required',
                'price' => 'required|numeric|min:0',
                'price_type' => 'required|in:fixed,hourly,daily,monthly,negotiable',
                'location' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:120',
                'attributes' => 'nullable|string', // <-- ДОБАВЛЕНО
                'images' => 'nullable|array|max:10',
                'images.*' => 'image|max:2048',
                'removed_media_ids' => 'nullable|array',
                'removed_media_ids.*' => 'integer',
            ]);

            $existingMediaIds = $listing->getMedia('images')
                ->pluck('id')
                ->map(fn($id) => (int) $id);

            $removedMediaIds = collect(
                $validated['removed_media_ids'] ?? []
            )
                ->map(fn($id) => (int) $id)
                ->intersect($existingMediaIds)
                ->unique();

            $remainingImagesCount = $existingMediaIds
                ->diff($removedMediaIds)
                ->count();

            $newImagesCount = count(
                $request->file('images', [])
            );

            $finalImagesCount = (
                $remainingImagesCount
                + $newImagesCount
            );

            if ($finalImagesCount < 1) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'images' => 'В объявлении должна остаться хотя бы одна фотография.',
                ]);
            }

            if ($finalImagesCount > 10) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'images' => 'В объявлении может быть не более 10 фотографий.',
                ]);
            }

            $requestedIsActive = (
                $listing->status === 'pending'
                && $listing->requested_is_active !== null
            )
                ? $listing->requested_is_active
                : $listing->is_active;

            $listing->update([
                'category_id' => $validated['category_id'],
                'title' => $validated['title'],
                'description' => $validated['description'],
                'price' => $validated['price'],
                'price_type' => $validated['price_type'],
                'location' => $validated['location'] ?? null,
                'city' => $validated['city'] ?? null,
                'listing_attributes' => $request->filled('attributes') ? json_decode($request->input('attributes'), true) : null, // <-- ДОБАВЛЕНО
                'status' => 'pending',
                'moderation_status' => \App\Enums\ModerationStatus::PendingModeration,
                'moderation_reason' => null,
                'moderated_at' => null,
                'is_active' => false,
                'requested_is_active' => $requestedIsActive,
            ]);

            $listing->getMedia('images')
                ->whereIn('id', $removedMediaIds)
                ->each(fn($media) => $media->delete());

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $listing->addMedia($image)
                        ->toMediaCollection('images');
                }
            }

            return redirect('/user/listings')->with('success', 'Объявление обновлено');
        })->name('user.listings.update');

        Route::patch('/user/listings/{listing}/publication', function (Request $request, Listing $listing) {
            if ($listing->user_id !== Auth::id()) {
                abort(403);
            }

            $validated = $request->validate([
                'publish' => ['required', 'boolean'],
            ]);

            $publish = (bool) $validated['publish'];

            $listing->update([
                'status' => 'pending',
                'moderation_status' => \App\Enums\ModerationStatus::PendingModeration,
                'moderation_reason' => null,
                'moderated_at' => null,
                'is_active' => false,
                'requested_is_active' => $publish,
            ]);

            return back()->with(
                'success',
                $publish
                    ? 'Объявление отправлено на модерацию для публикации'
                    : 'Объявление снято с публикации и отправлено на модерацию'
            );
        })->name('user.listings.publication');

        Route::delete('/user/listings/{listing}', function (Listing $listing) {
            if ($listing->user_id !== Auth::id()) {
                abort(403);
            }

            \Illuminate\Support\Facades\DB::transaction(
                function () use ($listing) {
                    $listing->favorites()->delete();
                    $listing->delete();
                }
            );

            return back()->with(
                'success',
                'Объявление и связанные с ним данные удалены'
            );
        })->name('user.listings.destroy');
    });

    Route::middleware(['email.verified'])->group(function () {
        Route::post('/listings/{listing}/reviews', [\App\Http\Controllers\User\ReviewController::class, 'store'])->name('reviews.store');
        Route::put('/reviews/{review}', [\App\Http\Controllers\User\ReviewController::class, 'update'])->name('reviews.update');
        Route::delete('/reviews/{review}', [\App\Http\Controllers\User\ReviewController::class, 'destroy'])->name('reviews.destroy');
    });
});

// === АДМИН-ПАНЕЛЬ ===
Route::middleware(['auth'])->prefix('manage')->name('admin.')->group(function () {
    Route::get('/', function () {
        $stats = [
            'users' => User::count(),
            'listings' => Listing::count(),
            'categories' => Category::count(),
            'pending' => Listing::where('is_active', false)->count(),
        ];
        return view('admin.dashboard', compact('stats'));
    })->name('dashboard');

    Route::resource('users', \App\Http\Controllers\Admin\AdminUserController::class);
    Route::patch(
        'listings/{listing}/approve',
        [\App\Http\Controllers\Admin\AdminListingController::class, 'approve']
    )->name('listings.approve');

    Route::resource(
        'listings',
        \App\Http\Controllers\Admin\AdminListingController::class
    )->only(['index', 'destroy']);
    Route::resource('categories', \App\Http\Controllers\Admin\AdminCategoryController::class);
    Route::get('/settings', function () { return view('admin.settings'); })->name('settings');
});

// === DASHBOARD ===
Route::middleware(['auth'])->prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('/', [\App\Http\Controllers\DashboardController::class, 'index'])->name('index');
    Route::get('/listings', [\App\Http\Controllers\DashboardController::class, 'listings'])->name('listings');
    Route::get('/favorites', [\App\Http\Controllers\DashboardController::class, 'favorites'])->name('favorites');
    Route::get('/messages/api/{conversation}', [\App\Http\Controllers\DashboardController::class, 'getConversationMessages'])->name('messages.api'); // <-- ДОБАВИТЬ ЭТУ СТРОКУ
    Route::get('/messages', [\App\Http\Controllers\DashboardController::class, 'messages'])->name('messages');
    Route::get('/messages/{conversation?}', [\App\Http\Controllers\DashboardController::class, 'messages'])->name('messages.show');
    Route::post('/messages/{conversation}', [\App\Http\Controllers\DashboardController::class, 'sendMessage'])->name('messages.send');
Route::post('/messages/{conversation}/review-invite', [\App\Http\Controllers\ReviewInviteController::class, 'store'])
    ->middleware('email.verified')
    ->name('review-invites.store');
Route::get('/review-invites/{reviewInvite:token}', [\App\Http\Controllers\ReviewInviteController::class, 'show'])
    ->middleware(['email.verified', 'signed'])
    ->name('review-invites.show');
Route::post('/review-invites/{reviewInvite:token}', [\App\Http\Controllers\ReviewInviteController::class, 'submit'])
    ->middleware(['email.verified', 'signed'])
    ->name('review-invites.submit');
    Route::delete('/messages/{conversation}', [\App\Http\Controllers\DashboardController::class, 'hideConversation'])->name('messages.hide');
    Route::get('/reviews', [\App\Http\Controllers\DashboardController::class, 'reviews'])->name('reviews');
});

// === ПРОФИЛЬ ===
Route::middleware(['auth'])->group(function () {
    Route::get('/profile/edit', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
});