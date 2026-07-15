<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Listing;
use App\Models\Favorite;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        $parentCategories = Category::with('children.children')
            ->whereNull('parent_id')
            ->get();

        $iconMap = [
            'Услуги' => ['icon' => 'services', 'color' => 'green'],
            'Аренда жилая' => ['icon' => 'residential', 'color' => 'red'],
            'Недвижимость' => ['icon' => 'residential', 'color' => 'red'],
            'Коммерческая недвижимость' => ['icon' => 'commercial', 'color' => 'blue'],
            'Транспорт' => ['icon' => 'transport', 'color' => 'orange'],
            'Оборудование' => ['icon' => 'equipment', 'color' => 'purple'],
        ];

        $parentCategoriesData = [];
        foreach ($parentCategories as $category) {
            $count = $this->countListingsInCategory($category->id);
            $iconInfo = $iconMap[$category->name] ?? ['icon' => 'services', 'color' => 'green'];

            $parentCategoriesData[] = [
                'id' => $category->id,
                'name' => $category->name,
                'listings_count' => $count,
                'icon' => $iconInfo['icon'],
                'color' => $iconInfo['color'],
            ];
        }

        $sliderListings = Listing::where('is_active', true)
            ->with(['category', 'user'])
            ->latest()
            ->take(20)
            ->get();

        // Получаем ID избранных объявлений для текущего пользователя
        $favoritedIds = Auth::check()
            ? Favorite::where('user_id', Auth::id())
                ->where('favoritable_type', 'App\\Models\\Listing')
                ->whereIn('favoritable_id', $sliderListings->pluck('id'))
                ->pluck('favoritable_id')
            : collect();

        $sliderListingsData = $sliderListings->map(fn($l) => [
            'id' => $l->id,
            'title' => $l->title,
            'description' => $l->description ?? '',
            'price' => $l->price,
            'location' => $l->location ?? '',
            'image' => $l->getFirstMediaUrl('images', 'thumb'),
            'category' => $l->category ? ['name' => $l->category->name] : null,
            'rating' => 4.8,
            'reviews_count' => rand(50, 300),
            'is_favorited' => $favoritedIds->contains($l->id),
        ]);

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
            'parentCategories' => $parentCategoriesData,
            'sliderListings' => $sliderListingsData,
            'vipListings' => $vipListings,
        ]);
    }

    private function countListingsInCategory($categoryId)
    {
        $categoryIds = [$categoryId];

        $children = Category::where('parent_id', $categoryId)->get();
        foreach ($children as $child) {
            $categoryIds[] = $child->id;
            $grandchildren = Category::where('parent_id', $child->id)->get();
            foreach ($grandchildren as $grandchild) {
                $categoryIds[] = $grandchild->id;
            }
        }

        return Listing::whereIn('category_id', $categoryIds)
            ->where('is_active', true)
            ->count();
    }
}
