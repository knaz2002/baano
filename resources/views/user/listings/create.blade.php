<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Создать объявление - Baano</title>
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
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Создать объявление</h1>

        <form action="{{ route('user.listings.store') }}" method="POST" enctype="multipart/form-data" class="bg-white shadow-md rounded-lg p-6">
            @csrf

            <div class="space-y-6">
                <!-- Категория -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Категория</label>
<select name="category_id" class="w-full px-4 py-2 border rounded-lg" required>
    <option value="">Выберите категорию</option>
    
    @foreach($categories as $category)
        <optgroup label="{{ $category->name }}">
            @foreach($category->children as $child)
                @if($child->children->count() > 0)
                    <optgroup label="&nbsp;&nbsp;{{ $child->name }}">
                        @foreach($child->children as $subchild)
                            <option value="{{ $subchild->id }}">
                                &nbsp;&nbsp;&nbsp;&nbsp;{{ $subchild->name }}
                            </option>
                        @endforeach
                    </optgroup>
                @else
                    <option value="{{ $child->id }}">
                        &nbsp;&nbsp;{{ $child->name }}
                    </option>
                @endif
            @endforeach
        </optgroup>
    @endforeach
</select>
                    @error('category_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Заголовок -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Заголовок</label>
                    <input type="text" name="title" value="{{ old('title') }}" required maxlength="255"
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-orange-500 focus:border-orange-500">
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Описание -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Описание</label>
                    <textarea name="description" rows="6" required
                              class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-orange-500 focus:border-orange-500">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Цена и тип -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Цена</label>
                        <input type="number" name="price" value="{{ old('price') }}" required min="0" step="0.01"
                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-orange-500 focus:border-orange-500">
                        @error('price')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Тип цены</label>
                        <select name="price_type" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-orange-500 focus:border-orange-500">
                            <option value="fixed" {{ old('price_type') === 'fixed' ? 'selected' : '' }}>Фиксированная</option>
                            <option value="hourly" {{ old('price_type') === 'hourly' ? 'selected' : '' }}>За час</option>
                            <option value="daily" {{ old('price_type') === 'daily' ? 'selected' : '' }}>За сутки</option>
                            <option value="monthly" {{ old('price_type') === 'monthly' ? 'selected' : '' }}>За месяц</option>
                            <option value="negotiable" {{ old('price_type') === 'negotiable' ? 'selected' : '' }}>Договорная</option>
                        </select>
                        @error('price_type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Изображения -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Фотографии (максимум 10)</label>
                    <input type="file" name="images[]" multiple accept="image/*"
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-orange-500 focus:border-orange-500">
                    <p class="mt-1 text-sm text-gray-500">Поддерживаются форматы: JPG, PNG, GIF. Максимальный размер: 2MB</p>
                    @error('images.*')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Кнопки -->
            <div class="mt-8 flex space-x-4">
                <button type="submit" class="flex-1 bg-orange-600 text-white px-6 py-3 rounded-md hover:bg-orange-700 font-semibold">
                    Создать объявление
                </button>
                <a href="{{ route('user.listings.index') }}" class="px-6 py-3 border border-gray-300 rounded-md hover:bg-gray-50 text-center">
                    Отмена
                </a>
            </div>
        </form>
    </div>
</body>
</html>