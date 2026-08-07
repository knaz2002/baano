<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Favorite;
use App\Models\Listing;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function __invoke(): JsonResponse
    {
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

        $categories = Category::query()
            ->whereIn('id', collect($categoryDefinitions)->pluck('id'))
            ->get()
            ->keyBy('id');

        $parentCategories = [];

        foreach ($categoryDefinitions as $definition) {
            $category = $categories->get($definition['id']);

            if (! $category) {
                continue;
            }

            $parentCategories[] = [
                'id' => $category->id,
                'name' => $definition['name'],
                'listings_count' => $this->countListingsInCategory($category->id),
                'icon' => $definition['icon'],
                'color' => $definition['color'],
            ];
        }

        $gridListings = Listing::query()
            ->where('is_active', true)
            ->with(['category', 'user'])
            ->inRandomOrder()
            ->get();

        $favoritedIds = Auth::check()
            ? Favorite::query()
                ->where('user_id', Auth::id())
                ->where('favoritable_type', Listing::class)
                ->whereIn('favoritable_id', $gridListings->pluck('id'))
                ->pluck('favoritable_id')
            : collect();

        $gridListingsData = $gridListings->map(fn (Listing $listing) => [
            'id' => $listing->id,
            'title' => $listing->title,
            'description' => $listing->description ?? '',
            'price' => $listing->price,
            'location' => $listing->location ?? '',
            'image' => $this->absoluteMediaUrl($listing->getFirstMediaUrl('images', 'thumb')),
            'category' => $listing->category ? ['name' => $listing->category->name] : null,
            'rating' => 4.8,
            'reviews_count' => random_int(50, 300),
            'is_favorited' => $favoritedIds->contains($listing->id),
        ])->values();

        $vipListings = Listing::query()
            ->where('is_active', true)
            ->with(['category', 'user'])
            ->inRandomOrder()
            ->take(4)
            ->get()
            ->map(fn (Listing $listing) => [
                'id' => $listing->id,
                'title' => $listing->title,
                'description' => $listing->description ?? '',
                'price' => $listing->price,
                'location' => $listing->location ?? '',
                'image' => $this->absoluteMediaUrl($listing->getFirstMediaUrl('images', 'thumb')),
                'category' => $listing->category ? ['name' => $listing->category->name] : null,
                'rating' => 4.9,
                'reviews_count' => random_int(100, 500),
            ])
            ->values();

        return response()->json([
            'data' => [
                'parent_categories' => $parentCategories,
                'grid_listings' => $gridListingsData,
                'vip_listings' => $vipListings,
            ],
        ]);
    }

    private function countListingsInCategory(int $categoryId): int
    {
        $categoryIds = [$categoryId];

        $children = Category::query()->where('parent_id', $categoryId)->get();
        foreach ($children as $child) {
            $categoryIds[] = $child->id;
            $grandchildren = Category::query()->where('parent_id', $child->id)->pluck('id');
            foreach ($grandchildren as $grandchildId) {
                $categoryIds[] = $grandchildId;
            }
        }

        return Listing::query()
            ->whereIn('category_id', $categoryIds)
            ->where('is_active', true)
            ->count();
    }

    private function absoluteMediaUrl(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        return rtrim((string) config('app.url'), '/').'/'.ltrim($url, '/');
    }
}
