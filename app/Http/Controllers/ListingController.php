<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class ListingController extends Controller
{
    public function create()
    {
        $categories = Category::with('children')->whereNull('parent_id')->get();
        return view('listings.create', compact('categories'));
    }
}