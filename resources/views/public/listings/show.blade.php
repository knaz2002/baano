@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <a href="{{ route('listings.index') }}" class="text-blue-600 hover:underline mb-4 inline-block">
            ← Назад к объявлениям
        </a>

        <div class="bg-white rounded-lg shadow-md p-6">
            <!-- Галерея изображений -->
            @if($listing->getMedia('images')->count() > 0)
                <div class="mb-6">
                    @if($listing->getMedia('images')->count() > 1)
                        <!-- Несколько изображений -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-2 mb-4">
                            @foreach($listing->getMedia('images') as $media)
                                <img src="{{ $media->getUrl('thumb') }}" 
                                     alt="{{ $listing->title }}" 
                                     class="w-full h-32 object-cover rounded-lg cursor-pointer hover:opacity-80 transition"
                                     onclick="document.getElementById('main-image').src = '{{ $media->getUrl() }}'">
                            @endforeach
                        </div>
                        <!-- Главное изображение -->
                        <img id="main-image" 
                             src="{{ $listing->getFirstMedia('images')->getUrl() }}" 
                             alt="{{ $listing->title }}" 
                             class="w-full h-96 object-cover rounded-lg">
                    @else
                        <!-- Одно изображение -->
                        <img src="{{ $listing->getFirstMedia('images')->getUrl() }}" 
                             alt="{{ $listing->title }}" 
                             class="w-full h-96 object-cover rounded-lg">
                    @endif
                </div>
            @else
                <div class="w-full h-96 bg-gray-200 flex items-center justify-center rounded-lg mb-6">
                    <span class="text-gray-400 text-xl">Нет изображений</span>
                </div>
            @endif

            <!-- Заголовок и избранное -->
            <div class="flex justify-between items-start mb-4">
                <h1 class="text-3xl font-bold">{{ $listing->title }}</h1>
                
                @auth
                    <form method="POST" action="{{ route('user.favorites.toggle') }}">
                        @csrf
                        <input type="hidden" name="listing_id" value="{{ $listing->id }}">
                        <button type="submit" class="text-2xl {{ $listing->is_favorited ? 'text-red-500' : 'text-gray-400' }} hover:text-red-600 transition">
                            {{ $listing->is_favorited ? '❤️' : '🤍' }}
                        </button>
                    </form>
                @endauth
            </div>

            <!-- Категория -->
            <div class="mb-4">
                <span class="inline-block bg-gray-200 rounded-full px-3 py-1 text-sm text-gray-700">
                    {{ $listing->category->name ?? 'Без категории' }}
                </span>
            </div>

            <!-- Цена -->
            <p class="text-3xl font-bold text-green-600 mb-6">
                {{ number_format($listing->price, 0, ',', ' ') }} ₽
            </p>

            <!-- Описание -->
            <div class="prose max-w-none mb-6">
                <h3 class="text-xl font-semibold mb-2">Описание</h3>
                <div class="text-gray-700 whitespace-pre-line">
                    {{ $listing->description }}
                </div>
            </div>

            <!-- Местоположение -->
            @if($listing->location)
                <div class="mb-4">
                    <span class="text-gray-600">📍 {{ $listing->location }}</span>
                </div>
            @endif

            <!-- Информация о продавце -->
            <div class="border-t pt-4">
                <p class="text-gray-600">
                    <strong>Продавец:</strong> {{ $listing->user->name ?? 'Аноним' }}
                </p>
                <p class="text-gray-500 text-sm">
                    Размещено: {{ $listing->created_at->format('d.m.Y H:i') }}
                </p>
            </div>
        </div>

        <!-- Отзывы -->
        <div class="mt-8 bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold mb-4">Отзывы ({{ $reviews->total() }})</h2>

            @auth
    {{-- Вывод сообщений --}}
    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    {{-- Проверка: оставлял ли пользователь отзыв --}}
    @php
        $userReview = $listing->reviews()->where('user_id', Auth::id())->first();
    @endphp

    @if(!$userReview)
        {{-- Форма для нового отзыва --}}
        <form method="POST" action="{{ route('reviews.store', $listing) }}" class="mb-6">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Оценка</label>
                <select name="rating" class="w-full px-3 py-2 border rounded-lg" required>
                    <option value="5">⭐⭐⭐⭐⭐ (5)</option>
                    <option value="4">⭐⭐⭐⭐ (4)</option>
                    <option value="3">⭐⭐⭐ (3)</option>
                    <option value="2">⭐⭐ (2)</option>
                    <option value="1">⭐ (1)</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Комментарий</label>
                <textarea name="comment" rows="3" class="w-full px-3 py-2 border rounded-lg" placeholder="Напишите ваш отзыв..."></textarea>
            </div>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                Оставить отзыв
            </button>
        </form>
    @else
        {{-- Сообщение о существующем отзыве --}}
        <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-6">
            <p><strong>✅ Вы уже оставили отзыв на это объявление</strong></p>
            @if(!$userReview->is_active)
                <p class="text-sm mt-1">🕐 Ваш отзыв находится на модерации</p>
            @else
                <p class="text-sm mt-1">Ваш отзыв опубликован</p>
            @endif
        </div>
    @endif
@else
    <p class="text-gray-500 mb-6">
        <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Войдите</a>, чтобы оставить отзыв
    </p>
@endauth
            @forelse($reviews as $review)
                <div class="border-b pb-4 mb-4">
                    <div class="flex justify-between items-center mb-2">
                        <strong>{{ $review->user->name ?? 'Аноним' }}</strong>
                        <span class="text-yellow-500">
                            @for($i = 0; $i < $review->rating; $i++)⭐@endfor
                        </span>
                    </div>
                    <p class="text-gray-700">{{ $review->comment }}</p>
                    <p class="text-gray-400 text-sm mt-1">{{ $review->created_at->format('d.m.Y H:i') }}</p>
                </div>
            @empty
                <p class="text-gray-500">Отзывов пока нет</p>
            @endforelse

            {{ $reviews->links() }}
        </div>
    </div>
</div>
@endsection