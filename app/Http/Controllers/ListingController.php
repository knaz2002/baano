<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ListingController extends Controller
{
    public function index()
    {
        // Родительские категории (для каталога)
        $parentCategories = Category::with('children.children')
            ->whereNull('parent_id')
            ->get();
        
        // Получаем активные объявления, прошедшие модерацию
        $activeListings = Listing::with(['user', 'category', 'media'])
            ->where('is_active', true)
            ->latest()
            ->get();
        
        // Группируем по категориям и берем по одному из каждой
        $groupedListings = $activeListings->groupBy('category_id');
        
        $featuredListings = [];
        $count = 0;
        
        foreach ($groupedListings as $categoryId => $listings) {
            if ($count >= 12) break;
            
            // Берем самое новое объявление из каждой категории
            $listing = $listings->first();
            
            if ($listing) {
                $featuredListings[] = [
                    'id' => $listing->id,
                    'title' => $listing->title,
                    'description' => $listing->description,
                    'price' => $listing->price,
                    'price_type' => $listing->price_type,
                    'location' => $listing->location,
                    'image' => $listing->getFirstMediaUrl('images', 'thumb'),
                    'category' => $listing->category ? [
                        'id' => $listing->category->id,
                        'name' => $listing->category->name,
                    ] : null,
                    'user' => $listing->user ? [
                        'id' => $listing->user->id,
                        'name' => $listing->user->name,
                    ] : null,
                ];
                $count++;
            }
        }

        return Inertia::render('Home', [
            'featuredListings' => $featuredListings,
            'allParentCategories' => $parentCategories,
            'auth' => [
                'user' => Auth::user(),
            ],
        ]);
    }
}
