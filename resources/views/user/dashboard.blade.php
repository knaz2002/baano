<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Мой кабинет') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Мои объявления -->
                        <div class="bg-blue-50 p-6 rounded-lg">
                            <h3 class="text-lg font-semibold mb-2">Мои объявления</h3>
                            <p class="text-gray-600 mb-4">Управление вашими объявлениями</p>
                            <a href="/user/listings" class="text-blue-600 hover:underline">
                                Перейти →
                            </a>
                        </div>

                        <!-- Избранное -->
                        <div class="bg-red-50 p-6 rounded-lg">
                            <h3 class="text-lg font-semibold mb-2">Избранное</h3>
                            <p class="text-gray-600 mb-4">Сохраненные объявления</p>
                            <a href="/user/favorites" class="text-red-600 hover:underline">
                                Перейти →
                            </a>
                        </div>

                        <!-- На главную -->
                        <div class="bg-green-50 p-6 rounded-lg">
                            <h3 class="text-lg font-semibold mb-2">Главная страница</h3>
                            <p class="text-gray-600 mb-4">Просмотр всех объявлений</p>
                            <a href="/" class="text-green-600 hover:underline">
                                Перейти →
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>