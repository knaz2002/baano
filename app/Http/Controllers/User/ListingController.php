<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ListingController extends Controller
{
    public function index()
    {
        $listings = Listing::where('user_id', Auth::id())
            ->with('category')
            ->latest()
            ->paginate(10);

        return view('user.listings.index', compact('listings'));
    }

    public function create()
    {
        $categories = Category::whereNull('parent_id')
            ->with('children.children')  // ← 3 уровня!
            ->get();
    
        return view('user.listings.create', compact('categories'));
    }

 public function store(Request $request)
{
    $validated = $request->validate([
        'category_id' => 'required|exists:categories,id',
        'title' => 'required|string|max:255',
        'description' => 'required|string',
        'price' => 'nullable|numeric|min:0',
        'location' => 'nullable|string|max:255',
        'images.*' => 'nullable|image|max:2048',
    ]);

    $listing = new Listing($validated);
    $listing->user_id = Auth::id();
    $listing->status = 'pending';
    $listing->is_active = false; // ← ДОБАВЛЕНО
    $listing->save();

    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $image) {
            $listing->addMedia($image)->toMediaCollection('images');
        }
    }

    return redirect()->route('user.listings.index')
        ->with('success', 'Объявление создано и отправлено на модерацию');
}

    public function edit(Listing $listing)
    {
        if ($listing->user_id !== Auth::id()) {
            abort(403);
        }

        $categories = Category::where('is_active', true)->get();
        return view('user.listings.edit', compact('listing', 'categories'));
    }

    public function update(Request $request, Listing $listing)
    {
        if ($listing->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|max:255',
            'description' => 'required',
            'price' => 'required|numeric|min:0',
            'price_type' => 'required|in:fixed,hourly,daily,monthly,negotiable',
            'images.*' => 'image|max:2048',
        ]);

        $listing->update($validated);

        if ($request->hasFile('images')) {
            $listing->clearMediaCollection('images');
            foreach ($request->file('images') as $image) {
                $listing->addMedia($image)->toMediaCollection('images');
            }
        }

        return redirect()->route('user.listings.index')
            ->with('success', 'Объявление обновлено');
    }

    public function destroy(Listing $listing)
    {
        if ($listing->user_id !== Auth::id()) {
            abort(403);
        }

        $listing->delete();

        return redirect()->route('user.listings.index')
            ->with('success', 'Объявление удалено');
    }
}