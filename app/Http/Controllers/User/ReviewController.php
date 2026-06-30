<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(Request $request, Listing $listing)
    {
        // ✅ Проверка: оставлял ли пользователь уже отзыв
        $existingReview = Review::where('listing_id', $listing->id)
            ->where('user_id', Auth::id())
            ->first();

        if ($existingReview) {
            return redirect()->back()
                ->with('error', 'Вы уже оставили отзыв на это объявление');
        }

        // ✅ Проверка: не является ли пользователь автором объявления
        if ($listing->user_id === Auth::id()) {
            return redirect()->back()
                ->with('error', 'Вы не можете оставить отзыв на своё объявление');
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        Review::create([
            'listing_id' => $listing->id,
            'user_id' => Auth::id(),
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
            'is_active' => false, // ← ОТЗЫВ НА МОДЕРИРАЦИИ
        ]);

        return redirect()->back()->with('success', 'Отзыв отправлен на модерацию');
    }

    public function update(Request $request, Review $review)
    {
        if ($review->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $review->update([
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
            'is_active' => false, // ← Снова на модерацию после редактирования
        ]);

        return redirect()->back()->with('success', 'Отзыв обновлён и отправлен на модерацию');
    }

    public function destroy(Review $review)
    {
        if ($review->user_id !== Auth::id()) {
            abort(403);
        }

        $review->delete();

        return redirect()->back()->with('success', 'Отзыв удалён');
    }
}