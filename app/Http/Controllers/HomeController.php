<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Listing;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        // Жестко заданные 5 категорий с иконками
        $parentCategories = [
            [
                'id' => 1,
                'name' => 'Услуги',
                'listings_count' => Category::find(1)?->listings()->count() ?? 0,
                'icon' => 'services',
                'color' => 'green',
            ],
            [
                'id' => 2,
                'name' => 'Аренда жилая',
                'listings_count' => Category::find(2)?->listings()->count() ?? 0,
                'icon' => 'residential',
                'color' => 'red',
            ],
            [
                'id' => 3,
                'name' => 'Коммерческая недвижимость',
                'listings_count' => Category::find(3)?->listings()->count() ?? 0,
                'icon' => 'commercial',
                'color' => 'blue',
            ],
            [
                'id' => 4,
                'name' => 'Транспорт',
                'listings_count' => Category::find(4)?->listings()->count() ?? 0,
                'icon' => 'transport',
                'color' => 'orange',
            ],
            [
                'id' => 5,
                'name' => 'Оборудование',
                'listings_count' => Category::find(5)?->listings()->count() ?? 0,
                'icon' => 'equipment',
                'color' => 'purple',
            ],
        ];

        // Объявления для слайдера (последние 20)
        $sliderListings = Listing::where('is_active', true)
            ->with(['category', 'user'])
            ->latest()
            ->take(20)
            ->get()
            ->map(fn($l) => [
                'id' => $l->id,
                'title' => $l->title,
                'description' => $l->description ?? '',
                'price' => $l->price,
                'location' => $l->location ?? '',
                'image' => $l->getFirstMediaUrl('images', 'thumb'),
                'category' => $l->category ? ['name' => $l->category->name] : null,
                'rating' => 4.8,
                'reviews_count' => rand(50, 300),
            ]);

        // VIP объявления (4 шт)
        $vipListings = Listing::where('is_active', true)
            ->with(['category', 'user'])
            ->inRandomOrder()
            ->take(4)
            ->get()
            ->map(fn($l) => [
                'id' => $l->id,
                'title' => $l->title,
                'description' => $l->description ?? '',
                'price' => $l->price,
                'location' => $l->location ?? '',
                'image' => $l->getFirstMediaUrl('images', 'thumb'),
                'category' => $l->category ? ['name' => $l->category->name] : null,
                'rating' => 4.9,
                'reviews_count' => rand(100, 500),
            ]);

        return Inertia::render('Home', [
            'parentCategories' => $parentCategories,
            'sliderListings' => $sliderListings,
            'vipListings' => $vipListings,
        ]);
    }
}
