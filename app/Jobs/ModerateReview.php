<?php

namespace App\Jobs;

use App\Enums\ModerationStatus;
use App\Models\Review;
use App\Services\Moderation\ReviewModerationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ModerateReview implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(
        public readonly int $reviewId,
    ) {
        $this->onConnection(
            (string) config(
                'moderation.connection',
                'database'
            )
        );

        $this->onQueue(
            (string) config('moderation.queue', 'moderation')
        );
    }

    public function handle(
        ReviewModerationService $moderationService,
    ): void {
        $review = Review::find($this->reviewId);

        if ($review === null) {
            return;
        }

        if (
            $review->moderation_status
            !== ModerationStatus::PendingModeration
        ) {
            return;
        }

        $moderationService->moderate($review);
    }

    public function backoff(): array
    {
        return [30, 120, 300];
    }
}
