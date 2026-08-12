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
        // Категории главной страницы в заданном порядке.
        $categoryDefinitions = [
            [
                'id' => 2,
                'name' => 'Аренда жилья',
                'icon' => 'residential',
                'color' => 'red',
            ],
            [
                'id' => 19,
                'name' => 'Аренда техники и оборудования',
                'icon' => 'equipment',
                'color' => 'green',
            ],
            [
                'id' => 6,
                'name' => 'Коммерческая аренда',
                'icon' => 'commercial',
                'color' => 'red',
            ],
            [
                'id' => 24,
                'name' => 'Услуги и специалисты',
                'icon' => 'services',
                'color' => 'green',
            ],
            [
                'id' => 14,
                'name' => 'Транспорт',
                'icon' => 'transport',
                'color' => 'red',
            ],
        ];

        $categories = Category::with('children.children')
            ->whereIn(
                'id',
                collect($categoryDefinitions)->pluck('id')
            )
            ->get()
            ->keyBy('id');

        $parentCategoriesData = [];

        foreach ($categoryDefinitions as $definition) {
            $category = $categories->get($definition['id']);

            if (!$category) {
                continue;
            }

            $parentCategoriesData[] = [
                'id' => $category->id,
                'name' => $definition['name'],
                'listings_count' => $this->countListingsInCategory(
                    $category->id
                ),
                'icon' => $definition['icon'],
                'color' => $definition['color'],
            ];
        }

        // === СЕТКА ОБЪЯВЛЕНИЙ (все объявления в СЛУЧАЙНОМ порядке) ===
        $gridListings = Listing::where('is_active', true)
                ->with(['category', 'user'])
                ->inRandomOrder()
                ->get()
                ->shuffle(); // Дополнительная рандомизация на уровне PHP

        $favoritedIds = Auth::check()
            ? Favorite::where('user_id', Auth::id())
                ->where('favoritable_type', 'App\\Models\\Listing')
                ->whereIn('favoritable_id', $gridListings->pluck('id'))
                ->pluck('favoritable_id')
            : collect();

        $gridListingsData = $gridListings->map(fn($l) => [
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

        // === VIP ОБЪЯВЛЕНИЯ ===
        $vipListings = Listing::where('is_active', true)
            ->where('is_premium', true)
            ->where('premium_until', '>', now())
            ->with(['category', 'user'])
            ->orderByDesc('premium_until')
            ->take(4)
            ->get()
            ->map(fn($l) => [
                'id' => $l->id,
                'title' => $l->title,
                'description' => $l->description ?? '',
                'price' => $l->price,
                'location' => $l->location ?? '',
                'city' => $l->city ?: ($l->location ?? ''),
                // Для широкого мобильного баннера используем исходное изображение,
                // чтобы оно не растягивалось из миниатюры 300x200.
                'image' => $l->getFirstMediaUrl('images'),
                'category' => $l->category ? ['name' => $l->category->name] : null,
                'rating' => 4.9,
                'reviews_count' => rand(100, 500),
            ]);

        return Inertia::render('Home', [
            'parentCategories' => $parentCategoriesData,
            'gridListings' => $gridListingsData, // <-- ПЕРЕДАЁМ gridListings
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
