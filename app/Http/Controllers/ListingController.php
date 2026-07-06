<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\Category;
use Inertia\Inertia;

class ListingController extends Controller
{
public function index()
{
    // Родительские категории (для каталога)
    $parentCategories = Category::with('children.children')
        ->whereNull('parent_id')
        ->get();
    
    // Подкатегории 2-го уровня (первые 9 для главной)
    $subcategories = Category::with('children')
        ->whereNotNull('parent_id')
        ->take(9)
        ->get();
    
    // Для каждой подкатегории берём 1 объявление (если есть)
    $categoryCards = [];
    foreach ($subcategories as $sub) {
        $listing = Listing::with(['user', 'media'])
            ->where('is_active', true)
            ->where('category_id', $sub->id)
            ->latest()
            ->first();
        
        $categoryCards[] = [
            'category' => [
                'id' => $sub->id,
                'name' => $sub->name,
                'slug' => $sub->slug,
                'parent' => $sub->parent ? ['name' => $sub->parent->name] : null,
            ],
            'listing' => $listing ? [
                'id' => $listing->id,
                'title' => $listing->title,
                'description' => $listing->description,
                'price' => $listing->price,
                'image' => $listing->getFirstMediaUrl('images', 'thumb'),
                'user' => $listing->user ? ['name' => $listing->user->name] : null,
            ] : null,
        ];
    }

    return Inertia::render('Home', [
        'categoryCards' => $categoryCards,
        'allParentCategories' => $parentCategories,
    ]);
}
}
