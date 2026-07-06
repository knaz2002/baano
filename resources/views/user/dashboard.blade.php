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
                    <h1 class="text-3xl font-bold mb-4">Добро пожаловать, {{ Auth::user()->name }}!</h1>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
                        <!-- Мои объявления -->
                        <a href="{{ route('user.listings.index') }}" class="block p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-100">
                            <h3 class="text-xl font-semibold mb-2">Мои объявления</h3>
                            <p class="text-gray-600">Управление вашими объявлениями</p>
                        </a>

                        <!-- Избранное -->
                        <a href="{{ route('favorites.index') }}" class="block p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-100">
                            <h3 class="text-xl font-semibold mb-2">Избранное</h3>
                            <p class="text-gray-600">Ваши сохранённые объявления</p>
                        </a>

                        <!-- Профиль -->
                        <a href="{{ route('profile.edit') }}" class="block p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-100">
                            <h3 class="text-xl font-semibold mb-2">Профиль</h3>
                            <p class="text-gray-600">Настройки вашего аккаунта</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
