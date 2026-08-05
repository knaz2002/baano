<?php

namespace App\Jobs;

use App\Enums\ModerationStatus;
use App\Models\Listing;
use App\Services\Moderation\ListingModerationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ModerateListing implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(
        public readonly int $listingId,
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
        ListingModerationService $moderationService,
    ): void {
        $listing = Listing::find($this->listingId);

        if ($listing === null) {
            return;
        }

        if (
            $listing->moderation_status
            !== ModerationStatus::PendingModeration
        ) {
            return;
        }

        $moderationService->moderate($listing);
    }

    public function backoff(): array
    {
        return [30, 120, 300];
    }
}
