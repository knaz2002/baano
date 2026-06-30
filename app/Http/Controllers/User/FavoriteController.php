<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    public function index()
    {
        $favorites = Favorite::where('user_id', Auth::id())
            ->with(['favoritable.user', 'favoritable.category'])
            ->latest()
            ->paginate(12);

        return view('user.favorites.index', compact('favorites'));
    }

    public function toggle(Request $request)
    {
        $validated = $request->validate([
            'listing_id' => 'required|exists:listings,id',
        ]);

        $listing = Listing::findOrFail($validated['listing_id']);

        $favorite = Favorite::where('user_id', Auth::id())
            ->where('favoritable_id', $listing->id)
            ->where('favoritable_type', Listing::class)
            ->first();

        if ($favorite) {
            $favorite->delete();
            $message = 'Удалено из избранного';
        } else {
            Favorite::create([
                'user_id' => Auth::id(),
                'favoritable_id' => $listing->id,
                'favoritable_type' => Listing::class,
            ]);
            $message = 'Добавлено в избранное';
        }

        return redirect()->back()->with('success', $message);
    }

    public function destroy($id)
    {
        $favorite = Favorite::where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        $favorite->delete();

        return redirect()->route('user.favorites.index')
            ->with('success', 'Удалено из избранного');
    }
}