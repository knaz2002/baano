<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class MyReviewController extends Controller
{
    public function index(): JsonResponse
    {
        $reviews = Review::query()
            ->where('user_id', Auth::id())
            ->with(['listing:id,title', 'user:id,name'])
            ->latest()
            ->get()
            ->map(fn (Review $review) => [
                'id' => $review->id,
                'rating' => $review->rating,
                'comment' => $review->comment,
                'created_at' => optional($review->created_at)?->toIso8601String(),
                'listing' => $review->listing
                    ? ['id' => $review->listing->id, 'title' => $review->listing->title]
                    : null,
                'user' => $review->user
                    ? ['id' => $review->user->id, 'name' => $review->user->name]
                    : null,
            ]);

        return response()->json(['data' => $reviews]);
    }
}
