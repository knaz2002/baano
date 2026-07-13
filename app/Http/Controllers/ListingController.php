<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\Category;
use App\Models\Favorite;
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

        if ($request->filled('price_min') && $request->price_min > $priceMinGlobal) {
            $query->where('price', '>=', $request->price_min);
        }
        if ($request->filled('price_max') && $request->price_max < $priceMaxGlobal) {
            $query->where('price', '<=', $request->price_max);
        }

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

        $categories = Category::with('children')->whereNull('parent_id')->get();

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
                'children' => $c->children->map(fn($ch) => ['id' => $ch->id, 'name' => $ch->name]),
            ]),
            'currentCategory' => $currentCategory ? [
                'id' => $currentCategory->id,
                'name' => $currentCategory->name,
            ] : null,
            'priceRange' => [
                'min' => $priceMinGlobal,
                'max' => $priceMaxGlobal,
            ],
            'filters' => [
                'category' => $request->category,
                'search' => $request->search,
                'sort' => $request->get('sort', 'latest'),
                'price_min' => $request->price_min,
                'price_max' => $request->price_max,
            ],
            'pagination' => [
                'current_page' => $listings->currentPage(),
                'last_page' => $listings->lastPage(),
                'total' => $listings->total(),
            ],
        ]);
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
}
