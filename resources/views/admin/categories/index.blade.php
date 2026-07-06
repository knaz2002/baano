<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-2xl font-bold">📂 Управление категориями</h1>
                        <a href="{{ route('admin.dashboard') }}" class="text-blue-600 hover:underline">← Назад в админку</a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Название</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Slug</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Родитель</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Тип</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Действия</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($categories as $category)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $category->id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $category->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $category->slug }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $category->parent ? $category->parent->name : '—' }}
                                    </td>

				   <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
					    <a href="{{ route('admin.categories.edit', $category) }}" class="text-blue-600 hover:text-blue-900 mr-3">Изменить</a>
					    <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="inline" onsubmit="return confirm('Удалить категорию?')">
					    @csrf
					    @method('DELETE')
					   <button type="submit" class="text-red-600 hover:text-red-900">Удалить</button>
					   </Form>
				  </td>		                    
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <button class="text-blue-600 hover:text-blue-900 mr-3">Изменить</button>
                                        <button class="text-red-600 hover:text-red-900">Удалить</button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 text-sm text-gray-500">
                        Всего категорий: {{ $categories->count() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
