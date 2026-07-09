<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class AdminCategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('parent')->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        $parents = Category::whereNull('parent_id')->get();
        return view('admin.categories.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:categories',
            'parent_id' => 'nullable|exists:categories,id',
            'type' => 'required|in:main,sub',
        ]);

        Category::create($request->all());

        return redirect()->route('admin.categories.index')->with('success', 'Категория создана');
    }

    public function edit(Category $category)
    {
        $parents = Category::whereNull('parent_id')->get();
        return view('admin.categories.edit', compact('category', 'parents'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:categories,slug,' . $category->id,
            'parent_id' => 'nullable|exists:categories,id',
            'type' => 'required|in:main,sub',
        ]);

        $category->update($request->all());

        return redirect()->route('admin.categories.index')->with('success', 'Категория обновлена');
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return redirect()->route('admin.categories.index')->with('success', 'Категория удалена');
    }
}
