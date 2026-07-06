<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-2xl font-bold">📋 Модерация объявлений</h1>
                        <a href="{{ route('admin.dashboard') }}" class="text-blue-600 hover:underline">← Назад в админку</a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Название</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Категория</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Автор</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Цена</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Статус</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Действия</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($listings as $listing)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $listing->id }}</td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $listing->title }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $listing->category ? $listing->category->name : '—' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $listing->user ? $listing->user->name : '—' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ number_format($listing->price, 0, ',', ' ') }} ₽
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($listing->is_active)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Опубликовано
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                На модерации
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <button class="text-blue-600 hover:text-blue-900 mr-3">Изменить</button>
                                        <button class="text-red-600 hover:text-red-900">Удалить</button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                        Объявлений пока нет
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 text-sm text-gray-500">
                        Всего объявлений: {{ $listings->count() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
