<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Создать объявление') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('user.listings.store') }}">
                        @csrf

                        <!-- Название объявления -->
                        <div>
                            <x-input-label for="title" :value="__('Название')" />
                            <x-text-input id="title" class="block mt-1 w-full" type="text" name="title" :value="old('title')" required autofocus />
                            <x-input-error :messages="$errors->get('title')" class="mt-2" />
                        </div>

                        <!-- Категория -->
                        <div class="mt-4">
                            <x-input-label for="category_id" :value="__('Категория')" />
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
</select>                          <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                        </div>

                        <!-- Описание -->
                        <div class="mt-4">
                            <x-input-label for="description" :value="__('Описание')" />
                            <textarea 
                                id="description" 
                                name="description" 
                                rows="4"
                                class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            >{{ old('description') }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <!-- Цена -->
                        <div class="mt-4">
                            <x-input-label for="price" :value="__('Цена (₽)')" />
                            <x-text-input 
                                id="price" 
                                class="block mt-1 w-full" 
                                type="number" 
                                name="price" 
                                :value="old('price')" 
                                min="0" 
                                step="0.01"
                                required 
                            />
                            <x-input-error :messages="$errors->get('price')" class="mt-2" />
                        </div>

                        <!-- Кнопка -->
                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button class="ms-4">
                                {{ __('Создать объявление') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>