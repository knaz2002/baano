<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Админка') - Baano</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .btn-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .btn-gradient:hover {
            background: linear-gradient(135deg, #5568d3 0%, #653a8f 100%);
        }
    </style>
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <a href="{{ route('admin.dashboard') }}" class="text-xl font-bold text-gray-800">Админка Baano</a>
            <div class="space-x-4">
                <a href="{{ route('admin.dashboard') }}" class="text-gray-600 hover:text-gray-900">Дашборд</a>
                <a href="{{ route('admin.users.index') }}" class="text-gray-600 hover:text-gray-900">Пользователи</a>
                <a href="{{ route('admin.listings.index') }}" class="text-gray-600 hover:text-gray-900">Объявления</a>
                <a href="{{ route('admin.categories.index') }}" class="text-gray-600 hover:text-gray-900">Категории</a>
                <a href="{{ route('admin.settings') }}" class="text-gray-600 hover:text-gray-900">Настройки</a>
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="text-red-600 hover:text-red-800">Выйти</button>
                </form>
            </div>
        </div>
    </nav>

    @yield('content')
</body>
</html>
