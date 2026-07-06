<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h1 class="text-3xl font-bold mb-4">{{ $listing->title }}</h1>
                    
                    <!-- Галерея фото -->
                    @if($listing->hasMedia('images'))
                        <div class="mb-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach($listing->getMedia('images') as $media)
                                    <img src="{{ $media->getUrl() }}" 
                                         alt="{{ $listing->title }}" 
                                         class="w-full h-64 object-cover rounded-lg">
                                @endforeach
                            </div>
                        </div>
                    @endif
                    
                    <div class="mb-6">
                        <p class="text-4xl font-bold text-blue-600">{{ number_format($listing->price) }} ₽</p>
                    </div>
                    
                    <div class="mb-6">
                        <h2 class="text-xl font-semibold mb-2">Описание</h2>
                        <p class="text-gray-700">{{ $listing->description }}</p>
                    </div>
                    
                    <div class="mb-6">
                        <h2 class="text-xl font-semibold mb-2">Категория</h2>
                        <p class="text-gray-700">{{ $listing->category->name }}</p>
                    </div>
                    
                    <div class="mb-6">
                        <h2 class="text-xl font-semibold mb-2">Продавец</h2>
                        <p class="text-gray-700">{{ $listing->user->name }}</p>
                    </div>
                    
                    <a href="{{ route('listings.index') }}" class="inline-block px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                        ← Назад к объявлениям
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
