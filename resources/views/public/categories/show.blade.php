<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold mb-8">{{ $category->name }}</h1>
            
            @if($listings->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach($listings as $listing)
                        <a href="{{ route('listings.show', $listing) }}" class="card p-6 block">
                            <h3 class="text-xl font-semibold">{{ $listing->title }}</h3>
                            <p class="text-gray-600 mt-2">{{ Str::limit($listing->description, 100) }}</p>
                            <p class="text-2xl font-bold text-blue-600 mt-4">{{ number_format($listing->price) }} ₽</p>
                        </a>
                    @endforeach
                </div>
            @else
                <p class="text-gray-600">Объявлений пока нет</p>
            @endif
        </div>
    </div>
</x-app-layout>
