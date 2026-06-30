<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мои объявления - Baano</title>
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
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Мои объявления</h1>
            <a href="{{ route('user.listings.create') }}" class="bg-orange-600 text-white px-6 py-2 rounded-md hover:bg-orange-700">
                + Создать объявление
            </a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Объявление
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Категория
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Цена
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Статус
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Действия
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($listings as $listing)
                        <tr>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $listing->title }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $listing->category->name }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ number_format($listing->price, 0, ',', ' ') }} ₽
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @if($listing->status === 'active') bg-green-100 text-green-800
                                    @elseif($listing->status === 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($listing->status === 'draft') bg-gray-100 text-gray-800
                                    @else bg-red-100 text-red-800 @endif">
                                    {{ $listing->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium space-x-2">
                                <a href="{{ route('user.listings.edit', $listing) }}" class="text-indigo-600 hover:text-indigo-900">
                                    Редактировать
                                </a>
                                <form action="{{ route('user.listings.destroy', $listing) }}" method="POST" class="inline" 
                                      onsubmit="return confirm('Удалить объявление?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                        Удалить
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                У вас пока нет объявлений
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-8">
            {{ $listings->links() }}
        </div>
    </div>
</body>
</html>