<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Category;
use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ListingController extends Controller
{
public function index(Request $request)
{
    $query = Listing::with(['user', 'category'])
        ->where('is_active', true); // ← должно быть true, не 'is_active'

    if ($request->filled('category')) {
        $query->where('category_id', $request->category);
    }

    if ($request->filled('search')) {
        $query->where('title', 'like', '%' . $request->search . '%');
    }

    $listings = $query->latest()->paginate(12);
    
    $categories = Category::whereNull('parent_id')
        ->with('children.children')
        ->get();

    if (Auth::check()) {
        foreach ($listings as $listing) {
            $listing->is_favorited = Favorite::where('user_id', Auth::id())
                ->where('favoritable_type', Listing::class)
                ->where('favoritable_id', $listing->id)
                ->exists();
        }
    }

    return view('public.listings.index', compact('listings', 'categories'));
}

public function show(Listing $listing)
{
    // Исправлено: проверяем is_active вместо status
    if (!$listing->is_active) {
        abort(404);
    }

    if (Auth::check()) {
        $listing->is_favorited = Favorite::where('user_id', Auth::id())
            ->where('favoritable_type', Listing::class)
            ->where('favoritable_id', $listing->id)
            ->exists();
    } else {
        $listing->is_favorited = false;
    }

    $reviews = $listing->reviews()
        ->where('is_active', true)
        ->with('user')
        ->latest()
        ->paginate(10);

    return view('public.listings.show', compact('listing', 'reviews'));
}

}
