@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Объявления</h1>

    <!-- Поиск и фильтр -->
    <form method="GET" action="{{ route('listings.index') }}" class="mb-8 flex gap-4">
        <input type="text" name="search" value="{{ request('search') }}" 
               placeholder="Поиск..." 
               class="flex-1 px-4 py-2 border rounded-lg">
        
<select name="category" class="px-4 py-2 border rounded-lg">
    <option value="">Все категории</option>
    @foreach($categories as $category)
        <optgroup label="{{ $category->name }}">
            @foreach($category->children as $child)
                @if($child->children->count() > 0)
                    <optgroup label="&nbsp;&nbsp;{{ $child->name }}">
                        @foreach($child->children as $subchild)
                            <option value="{{ $subchild->id }}" {{ request('category') == $subchild->id ? 'selected' : '' }}>
                                &nbsp;&nbsp;&nbsp;&nbsp;{{ $subchild->name }}
                            </option>
                        @endforeach
                    </optgroup>
                @else
                    <option value="{{ $child->id }}" {{ request('category') == $child->id ? 'selected' : '' }}>
                        &nbsp;&nbsp;{{ $child->name }}
                    </option>
                @endif
            @endforeach
        </optgroup>
    @endforeach
</select>
        
        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            Найти
        </button>
    </form>

    <!-- Список объявлений -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
@forelse($listings as $listing)
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <!-- Изображение -->
        @if($listing->getFirstMedia('images'))
            <img src="{{ $listing->getFirstMedia('images')->getUrl('thumb') }}" 
                 alt="{{ $listing->title }}" 
                 class="w-full h-48 object-cover">
        @else
            <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                <span class="text-gray-400">Нет изображения</span>
            </div>
        @endif
        
        <div class="p-4">
            <h2 class="text-xl font-semibold mb-2">
                <a href="{{ route('listings.show', $listing) }}" class="hover:text-blue-600">
                    {{ $listing->title }}
                </a>
            </h2>
            
            <p class="text-gray-600 text-sm mb-2">
                {{ $listing->category->name ?? 'Без категории' }}
            </p>
            
            <p class="text-2xl font-bold text-green-600 mb-2">
                {{ number_format($listing->price, 0, ',', ' ') }} ₽
            </p>
            
            <p class="text-gray-500 text-sm mb-4">
                {{ Str::limit($listing->description, 100) }}
            </p>
            
            <div class="flex justify-between items-center">
                <span class="text-gray-400 text-sm">
                    {{ $listing->user->name ?? 'Аноним' }}
                </span>
                
                @auth
                    <form method="POST" action="{{ route('user.favorites.toggle') }}">
                        @csrf
                        <input type="hidden" name="listing_id" value="{{ $listing->id }}">
                        <button type="submit" class="text-{{ $listing->is_favorited ? 'red' : 'gray' }}-500 hover:text-red-600">
                            {{ $listing->is_favorited ? '❤️' : '🤍' }}
                        </button>
                    </form>
                @endauth
            </div>
        </div>
    </div>
@empty
    <div class="col-span-3 text-center text-gray-500 py-12">
        Объявления не найдены
    </div>
@endforelse
/div>

    <!-- Пагинация -->
    <div class="mt-8">
        {{ $listings->links() }}
    </div>
</div>
@endsection