<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Избранное - Baano</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <!-- Навигация -->
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('listings.index') }}" class="text-2xl font-bold text-orange-600">
                        Baano
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('listings.index') }}" class="text-gray-700 hover:text-gray-900">
                        Главная
                    </a>
                    <a href="{{ route('user.listings.index') }}" class="text-gray-700 hover:text-gray-900">
                        Мои объявления
                    </a>
                    <a href="{{ route('user.favorites.index') }}" class="text-gray-700 hover:text-gray-900">
                        Избранное
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-gray-700 hover:text-gray-900">
                            Выйти
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Контент -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Избранное</h1>

        @if($favorites->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($favorites as $favorite)
                    @php
                        $listing = $favorite->favoritable;
                    @endphp
                    
                    @if($listing)
                        <div class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow">
                            <a href="{{ route('listings.show', $listing) }}">
                                <div class="h-48 bg-gray-200 flex items-center justify-center">
                                    @if($listing->getFirstMediaUrl('images'))
                                        <img src="{{ $listing->getFirstMediaUrl('images') }}" alt="{{ $listing->title }}" class="w-full h-full object-cover">
                                    @else
                                        <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    @endif
                                </div>
                                <div class="p-4">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $listing->title }}</h3>
                                    <p class="text-sm text-gray-600 mb-2">{{ $listing->category->name }}</p>
                                    <div class="flex justify-between items-center">
                                        <span class="text-xl font-bold text-orange-600">
                                            {{ number_format($listing->price, 0, ',', ' ') }} ₽
                                            @if($listing->price_type === 'daily')
                                                <span class="text-sm text-gray-500">/сутки</span>
                                            @elseif($listing->price_type === 'hourly')
                                                <span class="text-sm text-gray-500">/час</span>
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </a>
                            <div class="px-4 py-3 bg-gray-50 border-t">
                                <button onclick="removeFavorite({{ $favorite->id }})" 
                                        class="w-full text-red-600 hover:text-red-800 text-sm font-medium">
                                    Удалить из избранного
                                </button>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            <div class="mt-8">
                {{ $favorites->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Нет избранных объявлений</h3>
                <p class="mt-1 text-sm text-gray-500">Начните добавлять объявления в избранное, чтобы сохранить их здесь.</p>
                <div class="mt-6">
                    <a href="{{ route('listings.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700">
                        Смотреть объявления
                    </a>
                </div>
            </div>
        @endif
    </div>

    <script>
        function removeFavorite(id) {
            if (confirm('Удалить из избранного?')) {
                fetch('{{ route("user.favorites.index") }}/' + id, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                });
            }
        }
    </script>
</body>
</html>