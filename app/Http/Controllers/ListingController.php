<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\Category;
use App\Models\Favorite;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ListingController extends Controller
{
    public function index(Request $request)
    {
        // Базовый запрос
        $baseQuery = Listing::with(['user', 'category'])
            ->where('is_active', true);

        // Если выбрана категория - собираем все ID (категория + подкатегории)
        $categoryIds = [];
        if ($request->filled('category')) {
            $categoryIds = $this->getCategoryIdsWithChildren($request->category);
            $baseQuery->whereIn('category_id', $categoryIds);
        }

        if ($request->filled('city')) {
            $baseQuery->where(
                'city',
                trim((string) $request->city)
            );
        }

        $filterType = $this->resolveFilterType(
            $request->filled('category')
                ? (int) $request->category
                : null
        );

        $filterOptions = $this->getFilterOptions(
            $baseQuery,
            $filterType
        );

        // Вычисляем min/max цены по отфильтрованным объявлениям
        $priceStats = $baseQuery->clone()
            ->selectRaw('MIN(price) as min_price, MAX(price) as max_price')
            ->first();

        $priceMinGlobal = (float) ($priceStats->min_price ?? 0);
        $priceMaxGlobal = (float) ($priceStats->max_price ?? 0);

        // Основной запрос с фильтрами
        $query = Listing::with(['user', 'category'])
            ->where('is_active', true);

        if ($request->filled('category')) {
            $query->whereIn('category_id', $categoryIds);
        }

        if ($request->filled('city')) {
            $query->where(
                'city',
                trim((string) $request->city)
            );
        }

        if ($request->filled('price_min') && $request->price_min > $priceMinGlobal) {
            $query->where('price', '>=', $request->price_min);
        }
        if ($request->filled('price_max') && $request->price_max < $priceMaxGlobal) {
            $query->where('price', '<=', $request->price_max);
        }

        $this->applyAttributeFilters(
            $query,
            $request,
            $filterType
        );

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
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

        $listings = $query->paginate(20);

        $categories = Category::with('children.children')->whereNull('parent_id')->get();

        $currentCategory = null;
        if ($request->filled('category')) {
            $currentCategory = Category::find($request->category);
        }

        $listingIds = $listings->pluck('id');
        $favoritedIds = Auth::check()
            ? Favorite::where('user_id', Auth::id())
                ->where('favoritable_type', 'App\\Models\\Listing')
                ->whereIn('favoritable_id', $listingIds)
                ->pluck('favoritable_id')
            : collect();

        return Inertia::render('Listings/Index', [
            'listings' => $listings->map(fn($l) => [
                'id' => $l->id,
                'title' => $l->title,
                'description' => $l->description ?? '',
                'price' => $l->price,
                'location' => $l->location ?? '',
                'city' => $l->city ?? '',
                'image' => $l->getFirstMediaUrl('images', 'thumb'),
                'category' => $l->category ? ['id' => $l->category->id, 'name' => $l->category->name] : null,
                'user' => $l->user ? ['id' => $l->user->id, 'name' => $l->user->name] : null,
                'rating' => 4.8,
                'reviews_count' => rand(50, 300),
                'is_favorited' => $favoritedIds->contains($l->id),
            ]),
            'categories' => $categories->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'children' => $c->children->map(fn($ch) => [
                    'id' => $ch->id,
                    'name' => $ch->name,
                    'children' => $ch->children->map(fn($grandchild) => [
                        'id' => $grandchild->id,
                        'name' => $grandchild->name,
                    ]),
                ]),
            ]),
            'currentCategory' => $currentCategory ? [
                'id' => $currentCategory->id,
                'name' => $currentCategory->name,
            ] : null,
            'priceRange' => [
                'min' => $priceMinGlobal,
                'max' => $priceMaxGlobal,
            ],
            'filterConfig' => [
                'type' => $filterType,
                'options' => $filterOptions,
            ],
            'filters' => [
                'category' => $request->category,
                'city' => $request->city,
                'search' => $request->search,
                'sort' => $request->get('sort', 'latest'),
                'price_min' => $request->price_min,
                'price_max' => $request->price_max,
                'area_min' => $request->area_min,
                'area_max' => $request->area_max,
                'rooms' => $request->input('rooms', []),
                'floor' => $request->floor,
                'brand' => $request->brand,
                'model' => $request->model,
                'year' => $request->year,
            ],
            'pagination' => [
                'current_page' => $listings->currentPage(),
                'last_page' => $listings->lastPage(),
                'total' => $listings->total(),
            ],
        ]);
    }

    private function resolveFilterType(
        ?int $categoryId
    ): ?string {
        if ($categoryId === null) {
            return null;
        }

        return match (true) {
            in_array(
                $categoryId,
                [6, 7, 8, 9, 10, 11, 12, 13],
                true
            ) => 'commercial',

            in_array(
                $categoryId,
                [2, 3],
                true
            ) => 'apartments',

            in_array(
                $categoryId,
                [14, 15, 16, 17, 18],
                true
            ) => 'transport',

            in_array(
                $categoryId,
                [19, 20, 21, 22, 23],
                true
            ) => 'equipment',

            default => null,
        };
    }

    private function getFilterOptions(
        Builder $query,
        ?string $filterType
    ): array {
        if ($filterType === 'commercial') {
            $areas = (clone $query)
                ->select([
                    'id',
                    'listing_attributes',
                ])
                ->whereNotNull('listing_attributes')
                ->get()
                ->map(function (Listing $listing): float {
                    $attributes = $listing->listing_attributes;

                    if (!is_array($attributes)) {
                        $attributes = json_decode(
                            (string) $attributes,
                            true
                        ) ?: [];
                    }

                    return (float) ($attributes['area'] ?? 0);
                })
                ->filter(
                    fn(float $area): bool => $area > 0
                );

            return [
                'area' => [
                    'min' => (float) ($areas->min() ?? 0),
                    'max' => (float) ($areas->max() ?? 0),
                ],
            ];
        }

        if ($filterType === 'apartments') {
            return [
                'rooms' => range(1, 5),
                'floors' => range(1, 30),
            ];
        }

        if (!in_array(
            $filterType,
            ['transport', 'equipment'],
            true
        )) {
            return [];
        }

        $pairs = (clone $query)
            ->select([
                'id',
                'listing_attributes',
            ])
            ->whereNotNull('listing_attributes')
            ->get()
            ->map(function (Listing $listing): array {
                $attributes = $listing->listing_attributes;

                if (!is_array($attributes)) {
                    $attributes = json_decode(
                        (string) $attributes,
                        true
                    ) ?: [];
                }

                return [
                    'brand' => trim(
                        (string) ($attributes['brand'] ?? '')
                    ),
                    'model' => trim(
                        (string) ($attributes['model'] ?? '')
                    ),
                ];
            });

        $brands = $pairs
            ->pluck('brand')
            ->filter(
                fn($value) =>
                    is_string($value)
                    && $value !== ''
            )
            ->unique()
            ->sort(
                SORT_NATURAL
                | SORT_FLAG_CASE
            )
            ->values();

        $modelsByBrand = [];

        foreach ($brands as $brand) {
            $modelsByBrand[$brand] = $pairs
                ->where('brand', $brand)
                ->pluck('model')
                ->filter(
                    fn($value) =>
                        is_string($value)
                        && $value !== ''
                )
                ->unique()
                ->sort(
                    SORT_NATURAL
                    | SORT_FLAG_CASE
                )
                ->values()
                ->all();
        }

        $options = [
            'brands' => $brands->all(),
            'modelsByBrand' => $modelsByBrand,
        ];

        if ($filterType === 'transport') {
            $options['years'] = range(
                2026,
                1980
            );
        }

        return $options;
    }

    private function applyAttributeFilters(
        Builder $query,
        Request $request,
        ?string $filterType
    ): void {
        if ($filterType === 'commercial') {
            if ($request->filled('area_min')) {
                $areaMin = (float) $request->input(
                    'area_min'
                );

                if ($areaMin >= 0) {
                    $query->where(
                        'listing_attributes->area',
                        '>=',
                        $areaMin
                    );
                }
            }

            if ($request->filled('area_max')) {
                $areaMax = (float) $request->input(
                    'area_max'
                );

                if ($areaMax > 0) {
                    $query->where(
                        'listing_attributes->area',
                        '<=',
                        $areaMax
                    );
                }
            }

            return;
        }

        if ($filterType === 'apartments') {
            $rooms = collect(
                $request->input('rooms', [])
            )
                ->map(fn($room) => (int) $room)
                ->filter(
                    fn($room) =>
                        $room >= 1
                        && $room <= 5
                )
                ->unique()
                ->values();

            if ($rooms->isNotEmpty()) {
                $query->where(
                    function (Builder $roomsQuery) use ($rooms): void {
                        foreach ($rooms as $room) {
                            $roomsQuery->orWhere(
                                'listing_attributes->rooms',
                                $room
                            );
                        }
                    }
                );
            }

            $floor = (int) $request->input(
                'floor',
                0
            );

            if ($floor >= 1 && $floor <= 30) {
                $query->where(
                    'listing_attributes->floor',
                    $floor
                );
            }

            return;
        }

        if (!in_array(
            $filterType,
            ['transport', 'equipment'],
            true
        )) {
            return;
        }

        $brand = trim(
            (string) $request->input(
                'brand',
                ''
            )
        );

        if ($brand !== '') {
            $query->where(
                'listing_attributes->brand',
                $brand
            );
        }

        $model = trim(
            (string) $request->input(
                'model',
                ''
            )
        );

        if ($model !== '') {
            $query->where(
                'listing_attributes->model',
                $model
            );
        }

        if ($filterType !== 'transport') {
            return;
        }

        $year = (int) $request->input(
            'year',
            0
        );

        if ($year >= 1980 && $year <= 2026) {
            $query->where(
                'listing_attributes->year',
                $year
            );
        }
    }

    /**
     * Получает ID категории и всех её подкатегорий (рекурсивно)
     */
    private function getCategoryIdsWithChildren($categoryId)
    {
        $ids = [$categoryId];

        $children = Category::where('parent_id', $categoryId)->get();
        foreach ($children as $child) {
            $ids[] = $child->id;
            $grandchildren = Category::where('parent_id', $child->id)->get();
            foreach ($grandchildren as $grandchild) {
                $ids[] = $grandchild->id;
            }
        }

        return $ids;
    }
public function show(Listing $listing)
{
    $listing->load(['category', 'user']);

    // Получаем атрибуты
    $attrs = $listing->listing_attributes ?? [];

    return Inertia::render('Listing/Show', [
        'listing' => [
            'id' => $listing->id,
            'title' => $listing->title,
            'description' => $listing->description,
            'price' => $listing->price,
            'price_type' => $listing->price_type,
            'location' => $listing->location,
            'custom_attributes' => $attrs, // <-- ПЕРЕИМЕНОВАЛИ! Не 'attributes'
            'category' => $listing->category ? [
                'id' => $listing->category->id,
                'name' => $listing->category->name,
            ] : null,
            'user' => $listing->user ? [
                'id' => $listing->user->id,
                'name' => $listing->user->name,
                'phone' => $listing->user->phone ?? null,
            ] : null,
            'images' => $listing->getMedia('images')->map(fn($m) => $m->getUrl()),
            'created_at' => $listing->created_at->format('d.m.Y'),
            'is_active' => $listing->is_active,
        ],
        'reviews' => [],
        'isFavorited' => false,
        'similarListings' => [],
        'canReview' => false,
        'userReview' => null,
        'auth' => auth()->check() ? auth()->user() : null,
    ]);
}
}
