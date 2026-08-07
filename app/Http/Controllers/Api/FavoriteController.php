<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Listing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class FavoriteController extends Controller
{
    public function index(): JsonResponse
    {
        $favorites = Favorite::query()
            ->where('user_id', Auth::id())
            ->with(['favoritable.user', 'favoritable.category'])
            ->latest()
            ->get()
            ->map(function (Favorite $favorite) {
                $listing = $favorite->favoritable;

                if (! $listing instanceof Listing) {
                    return null;
                }

                return [
                    'id' => $favorite->id,
                    'listing' => [
                        'id' => $listing->id,
                        'title' => $listing->title,
                        'price' => $listing->price,
                        'location' => $listing->location ?? '',
                        'image' => $this->absoluteMediaUrl($listing->getFirstMediaUrl('images', 'thumb')),
                        'category' => $listing->category
                            ? ['id' => $listing->category->id, 'name' => $listing->category->name]
                            : null,
                    ],
                ];
            })
            ->filter()
            ->values();

        return response()->json([
            'data' => $favorites,
        ]);
    }

    public function toggle(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'listing_id' => ['required', 'integer', 'exists:listings,id'],
        ]);

        $listing = Listing::query()->findOrFail($validated['listing_id']);

        if ($listing->user_id === Auth::id()) {
            throw ValidationException::withMessages([
                'listing_id' => ['Нельзя добавить своё объявление в избранное.'],
            ]);
        }

        $favorite = Favorite::query()
            ->where('user_id', Auth::id())
            ->where('favoritable_id', $listing->id)
            ->where('favoritable_type', Listing::class)
            ->first();

        if ($favorite) {
            $favorite->delete();

            return response()->json([
                'data' => [
                    'is_favorited' => false,
                    'listing_id' => $listing->id,
                ],
                'message' => 'Удалено из избранного',
            ]);
        }

        Favorite::query()->create([
            'user_id' => Auth::id(),
            'favoritable_id' => $listing->id,
            'favoritable_type' => Listing::class,
        ]);

        return response()->json([
            'data' => [
                'is_favorited' => true,
                'listing_id' => $listing->id,
            ],
            'message' => 'Добавлено в избранное',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        Favorite::query()
            ->where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail()
            ->delete();

        return response()->json([
            'ok' => true,
            'message' => 'Удалено из избранного',
        ]);
    }

    private function absoluteMediaUrl(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        return rtrim((string) config('app.url'), '/').'/'.ltrim($url, '/');
    }
}
