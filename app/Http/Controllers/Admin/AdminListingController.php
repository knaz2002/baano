<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Category;
use Illuminate\Http\Request;

class AdminListingController extends Controller
{
    public function index()
    {
        $listings = Listing::with(['category', 'user'])->latest()->get();
        return view('admin.listings.index', compact('listings'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('admin.listings.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'category_id' => 'required|exists:categories,id',
        ]);

        Listing::create([
            'title' => $request->title,
            'description' => $request->description,
            'price' => $request->price,
            'category_id' => $request->category_id,
            'user_id' => auth()->id(),
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.listings.index')->with('success', 'Объявление создано');
    }

    public function edit(Listing $listing)
    {
        $categories = Category::all();
        return view('admin.listings.edit', compact('listing', 'categories'));
    }

    public function update(Request $request, Listing $listing)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'category_id' => 'required|exists:categories,id',
        ]);

        $listing->update([
            'title' => $request->title,
            'description' => $request->description,
            'price' => $request->price,
            'category_id' => $request->category_id,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.listings.index')->with('success', 'Объявление обновлено');
    }

    public function destroy(Listing $listing)
    {
        $listing->delete();
        return redirect()->route('admin.listings.index')->with('success', 'Объявление удалено');
    }
}
