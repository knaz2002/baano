<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h1 class="text-3xl font-bold mb-4 text-red-600">🔧 Панель администратора</h1>
                    
                    <!-- Статистика -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                        <div class="p-6 bg-blue-50 border border-blue-200 rounded-lg">
                            <p class="text-sm text-blue-600">Пользователей</p>
                            <p class="text-3xl font-bold text-blue-900">{{ $stats['users'] }}</p>
                        </div>
                        <div class="p-6 bg-green-50 border border-green-200 rounded-lg">
                            <p class="text-sm text-green-600">Объявлений</p>
                            <p class="text-3xl font-bold text-green-900">{{ $stats['listings'] }}</p>
                        </div>
                        <div class="p-6 bg-purple-50 border border-purple-200 rounded-lg">
                            <p class="text-sm text-purple-600">Категорий</p>
                            <p class="text-3xl font-bold text-purple-900">{{ $stats['categories'] }}</p>
                        </div>
                        <div class="p-6 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <p class="text-sm text-yellow-600">На модерации</p>
                            <p class="text-3xl font-bold text-yellow-900">{{ $stats['pending'] }}</p>
                        </div>
                    </div>

                    <!-- Навигация -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <a href="{{ route('admin.users.index') }}" class="block p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-100 transition-all">
                            <h3 class="text-xl font-semibold mb-2">👥 Пользователи</h3>
                            <p class="text-gray-600">Управление пользователями</p>
                        </a>

                        <a href="{{ route('admin.listings.index') }}" class="block p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-100 transition-all">
                            <h3 class="text-xl font-semibold mb-2">📋 Объявления</h3>
                            <p class="text-gray-600">Модерация объявлений</p>
                        </a>

                        <a href="{{ route('admin.categories.index') }}" class="block p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-100 transition-all">
                            <h3 class="text-xl font-semibold mb-2"> Категории</h3>
                            <p class="text-gray-600">Управление категориями</p>
                        </a>

                        <a href="{{ route('admin.settings') }}" class="block p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-100 transition-all">
                            <h3 class="text-xl font-semibold mb-2">⚙️ Настройки</h3>
                            <p class="text-gray-600">Настройки сайта</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
