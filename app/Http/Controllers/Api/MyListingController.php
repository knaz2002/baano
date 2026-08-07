<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Favorite;
use App\Models\Listing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MyListingController extends Controller
{
    public function index(): JsonResponse
    {
        $listings = Listing::query()
            ->where('user_id', Auth::id())
            ->with('category')
            ->latest()
            ->get()
            ->map(fn (Listing $listing) => $this->mapListing($listing));

        return response()->json(['data' => $listings]);
    }

    public function show(Listing $listing): JsonResponse
    {
        $this->authorizeOwner($listing);
        $listing->load('category');

        return response()->json([
            'data' => [
                'id' => $listing->id,
                'title' => $listing->title,
                'description' => $listing->description,
                'price' => $listing->price,
                'price_type' => $listing->price_type,
                'location' => $listing->location,
                'city' => $listing->city,
                'category_id' => $listing->category_id,
                'attributes' => $listing->listing_attributes ?? [],
                'status' => $listing->status,
                'is_active' => $listing->is_active,
                'requested_is_active' => $listing->requested_is_active,
                'images' => $listing->getMedia('images')->map(fn ($media) => [
                    'id' => $media->id,
                    'url' => $this->absoluteMediaUrl($media->getUrl()),
                ])->values(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'title' => ['required', 'max:255'],
            'description' => ['required'],
            'price' => ['required', 'numeric', 'min:0'],
            'price_type' => ['required', 'in:fixed,hourly,daily,monthly,negotiable'],
            'location' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'attributes' => ['nullable', 'string'],
            'images' => ['required', 'array', 'min:1', 'max:10'],
            'images.*' => ['image', 'max:2048'],
        ]);

        $listing = Listing::query()->create([
            'user_id' => Auth::id(),
            'category_id' => $validated['category_id'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'price_type' => $validated['price_type'],
            'location' => $validated['location'] ?? null,
            'city' => $validated['city'] ?? null,
            'listing_attributes' => $request->filled('attributes')
                ? json_decode((string) $request->input('attributes'), true)
                : null,
            'status' => 'pending',
            'is_active' => false,
            'requested_is_active' => true,
        ]);

        foreach ($request->file('images', []) as $image) {
            $listing->addMedia($image)->toMediaCollection('images');
        }

        return response()->json([
            'data' => $this->mapListing($listing->fresh('category')),
            'message' => 'Объявление создано',
        ], 201);
    }

    public function update(Request $request, Listing $listing): JsonResponse
    {
        $this->authorizeOwner($listing);

        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'title' => ['required', 'max:255'],
            'description' => ['required'],
            'price' => ['required', 'numeric', 'min:0'],
            'price_type' => ['required', 'in:fixed,hourly,daily,monthly,negotiable'],
            'location' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'attributes' => ['nullable', 'string'],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['image', 'max:2048'],
            'removed_media_ids' => ['nullable'],
        ]);

        $listing->update([
            'category_id' => $validated['category_id'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'price_type' => $validated['price_type'],
            'location' => $validated['location'] ?? null,
            'city' => $validated['city'] ?? null,
            'listing_attributes' => $request->filled('attributes')
                ? json_decode((string) $request->input('attributes'), true)
                : $listing->listing_attributes,
            'status' => 'pending',
            'is_active' => false,
            'requested_is_active' => true,
        ]);

        $removedIds = $request->input('removed_media_ids', []);
        if (is_string($removedIds)) {
            $removedIds = json_decode($removedIds, true) ?: [];
        }
        if (is_array($removedIds)) {
            foreach ($removedIds as $mediaId) {
                $media = $listing->getMedia('images')->firstWhere('id', (int) $mediaId);
                $media?->delete();
            }
        }

        foreach ($request->file('images', []) as $image) {
            $listing->addMedia($image)->toMediaCollection('images');
        }

        return response()->json([
            'data' => $this->mapListing($listing->fresh('category')),
            'message' => 'Объявление обновлено',
        ]);
    }

    public function publication(Request $request, Listing $listing): JsonResponse
    {
        $this->authorizeOwner($listing);

        $validated = $request->validate([
            'publish' => ['required', 'boolean'],
        ]);

        $listing->update([
            'status' => 'pending',
            'is_active' => false,
            'requested_is_active' => (bool) $validated['publish'],
        ]);

        return response()->json([
            'data' => $this->mapListing($listing->fresh('category')),
            'message' => 'Статус публикации обновлён',
        ]);
    }

    public function destroy(Listing $listing): JsonResponse
    {
        $this->authorizeOwner($listing);

        DB::transaction(function () use ($listing) {
            $listing->favorites()->delete();
            $listing->clearMediaCollection('images');
            $listing->delete();
        });

        return response()->json([
            'ok' => true,
            'message' => 'Объявление удалено',
        ]);
    }

    public function categories(): JsonResponse
    {
        $categories = Category::query()
            ->with('children.children')
            ->whereNull('parent_id')
            ->get()
            ->map(fn (Category $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'children' => $category->children->map(fn (Category $child) => [
                    'id' => $child->id,
                    'name' => $child->name,
                    'children' => $child->children->map(fn (Category $grandchild) => [
                        'id' => $grandchild->id,
                        'name' => $grandchild->name,
                    ])->values(),
                ])->values(),
            ]);

        return response()->json(['data' => $categories]);
    }

    private function authorizeOwner(Listing $listing): void
    {
        if ($listing->user_id !== Auth::id()) {
            abort(403);
        }
    }

    private function mapListing(Listing $listing): array
    {
        return [
            'id' => $listing->id,
            'title' => $listing->title,
            'price' => $listing->price,
            'status' => $listing->status,
            'is_active' => (bool) $listing->is_active,
            'requested_is_active' => (bool) $listing->requested_is_active,
            'category' => $listing->category ? ['name' => $listing->category->name] : null,
            'image' => $this->absoluteMediaUrl($listing->getFirstMediaUrl('images', 'thumb')),
            'favorites_count' => Favorite::query()
                ->where('favoritable_type', Listing::class)
                ->where('favoritable_id', $listing->id)
                ->count(),
        ];
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
