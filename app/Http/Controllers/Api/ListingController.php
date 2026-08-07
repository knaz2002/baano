<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Favorite;
use App\Models\Listing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ListingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $baseQuery = Listing::query()
            ->with(['user', 'category'])
            ->where('is_active', true);

        $categoryIds = [];
        if ($request->filled('category')) {
            $categoryIds = $this->getCategoryIdsWithChildren((int) $request->category);
            $baseQuery->whereIn('category_id', $categoryIds);
        }

        if ($request->filled('city')) {
            $baseQuery->where('city', trim((string) $request->city));
        }

        $priceStats = (clone $baseQuery)
            ->selectRaw('MIN(price) as min_price, MAX(price) as max_price')
            ->first();

        $priceMinGlobal = (float) ($priceStats->min_price ?? 0);
        $priceMaxGlobal = (float) ($priceStats->max_price ?? 0);

        $query = Listing::query()
            ->with(['user', 'category'])
            ->where('is_active', true);

        if ($request->filled('category')) {
            $query->whereIn('category_id', $categoryIds);
        }

        if ($request->filled('city')) {
            $query->where('city', trim((string) $request->city));
        }

        if ($request->filled('price_min') && (float) $request->price_min > $priceMinGlobal) {
            $query->where('price', '>=', (float) $request->price_min);
        }

        if ($request->filled('price_max') && (float) $request->price_max < $priceMaxGlobal) {
            $query->where('price', '<=', (float) $request->price_max);
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', '%'.$request->search.'%');
        }

        $sortBy = $request->get('sort', 'latest');
        switch ($sortBy) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'popular':
                $query->orderBy('views', 'desc');
                break;
            default:
                $query->latest();
        }

        $listings = $query->paginate(20)->withQueryString();

        $categories = Category::query()
            ->with('children.children')
            ->whereNull('parent_id')
            ->get();

        $currentCategory = null;
        if ($request->filled('category')) {
            $currentCategory = Category::query()->find($request->category);
        }

        $favoritedIds = Auth::check()
            ? Favorite::query()
                ->where('user_id', Auth::id())
                ->where('favoritable_type', Listing::class)
                ->whereIn('favoritable_id', $listings->pluck('id'))
                ->pluck('favoritable_id')
            : collect();

        $cities = Listing::query()
            ->where('is_active', true)
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->distinct()
            ->orderBy('city')
            ->pluck('city')
            ->values();

        return response()->json([
            'data' => [
                'listings' => $listings->getCollection()->map(fn (Listing $listing) => [
                    'id' => $listing->id,
                    'title' => $listing->title,
                    'description' => $listing->description ?? '',
                    'price' => $listing->price,
                    'location' => $listing->location ?? '',
                    'city' => $listing->city ?? '',
                    'image' => $this->absoluteMediaUrl($listing->getFirstMediaUrl('images', 'thumb')),
                    'category' => $listing->category
                        ? ['id' => $listing->category->id, 'name' => $listing->category->name]
                        : null,
                    'user' => $listing->user
                        ? ['id' => $listing->user->id, 'name' => $listing->user->name]
                        : null,
                    'rating' => 4.8,
                    'reviews_count' => random_int(50, 300),
                    'is_favorited' => $favoritedIds->contains($listing->id),
                ])->values(),
                'categories' => $categories->map(fn (Category $category) => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'children' => $category->children->map(fn (Category $child) => [
                        'id' => $child->id,
                        'name' => $child->name,
                        'children' => $child->children->map(fn (Category $grandchild) => [
                            'id' => $grandchild->id,
                            'name' => $grandchild->name,
                        ])->values(),
                    ])->values(),
                ])->values(),
                'cities' => $cities,
                'current_category' => $currentCategory
                    ? ['id' => $currentCategory->id, 'name' => $currentCategory->name]
                    : null,
                'price_range' => [
                    'min' => $priceMinGlobal,
                    'max' => $priceMaxGlobal,
                ],
                'filters' => [
                    'category' => $request->category,
                    'city' => $request->city,
                    'search' => $request->search,
                    'sort' => $sortBy,
                    'price_min' => $request->price_min,
                    'price_max' => $request->price_max,
                ],
            ],
            'meta' => [
                'current_page' => $listings->currentPage(),
                'last_page' => $listings->lastPage(),
                'per_page' => $listings->perPage(),
                'total' => $listings->total(),
            ],
        ]);
    }

    public function show(Listing $listing): JsonResponse
    {
        if (! $listing->is_active) {
            abort(404);
        }

        $listing->load(['category', 'user']);

        $isFavorited = Auth::check()
            && Favorite::query()
                ->where('user_id', Auth::id())
                ->where('favoritable_type', Listing::class)
                ->where('favoritable_id', $listing->id)
                ->exists();

        $similarListings = Listing::query()
            ->where('is_active', true)
            ->where('category_id', $listing->category_id)
            ->where('id', '!=', $listing->id)
            ->with(['category', 'user'])
            ->inRandomOrder()
            ->take(8)
            ->get()
            ->map(fn (Listing $item) => [
                'id' => $item->id,
                'title' => $item->title,
                'description' => $item->description ?? '',
                'price' => $item->price,
                'location' => $item->location ?? '',
                'image' => $this->absoluteMediaUrl($item->getFirstMediaUrl('images', 'thumb')),
                'category' => $item->category
                    ? ['id' => $item->category->id, 'name' => $item->category->name]
                    : null,
            ])
            ->values();

        return response()->json([
            'data' => [
                'listing' => [
                    'id' => $listing->id,
                    'title' => $listing->title,
                    'description' => $listing->description,
                    'price' => $listing->price,
                    'price_type' => $listing->price_type,
                    'location' => $listing->location,
                    'city' => $listing->city,
                    'custom_attributes' => $listing->listing_attributes ?? [],
                    'images' => $listing->getMedia('images')
                        ->map(fn ($media) => [
                            'id' => $media->id,
                            'url' => $this->absoluteMediaUrl($media->getUrl()),
                        ])
                        ->values(),
                    'category' => $listing->category
                        ? ['id' => $listing->category->id, 'name' => $listing->category->name]
                        : null,
                    'user_id' => $listing->user_id,
                    'user' => $listing->user
                        ? [
                            'id' => $listing->user->id,
                            'name' => $listing->user->name,
                            'phone' => $listing->user->phone,
                        ]
                        : null,
                    'created_at' => optional($listing->created_at)?->format('d.m.Y'),
                    'views' => $listing->views ?? 0,
                    'is_active' => $listing->is_active,
                ],
                'is_favorited' => $isFavorited,
                'similar_listings' => $similarListings,
            ],
        ]);
    }

    private function getCategoryIdsWithChildren(int $categoryId): array
    {
        $ids = [$categoryId];

        $children = Category::query()->where('parent_id', $categoryId)->get();
        foreach ($children as $child) {
            $ids[] = $child->id;
            $grandchildren = Category::query()->where('parent_id', $child->id)->get();
            foreach ($grandchildren as $grandchild) {
                $ids[] = $grandchild->id;
            }
        }

        return $ids;
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
