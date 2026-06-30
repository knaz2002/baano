<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Baano')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Навигация -->
    <nav class="bg-white shadow-md">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <a href="{{ route('listings.index') }}" class="text-2xl font-bold text-blue-600">
                Baano
            </a>
            
            <div class="flex items-center gap-4">
                @auth
                    <a href="{{ route('user.listings.index') }}" class="text-gray-700 hover:text-blue-600">
                        Мои объявления
                    </a>
                    <a href="{{ route('user.favorites.index') }}" class="text-gray-700 hover:text-blue-600">
                        Избранное
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-gray-700 hover:text-red-600">
                            Выйти ({{ Auth::user()->name }})
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="text-gray-700 hover:text-blue-600">
                        Войти
                    </a>
                    <a href="{{ route('register') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Регистрация
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Контент -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t mt-8 py-6">
        <div class="container mx-auto px-4 text-center text-gray-500">
            © 2026 Baano. Все права защищены.
        </div>
    </footer>
</body>
</html>