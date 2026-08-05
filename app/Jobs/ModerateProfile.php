<?php

namespace App\Jobs;

use App\Enums\ModerationStatus;
use App\Models\User;
use App\Services\Moderation\ProfileModerationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ModerateProfile implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(
        public readonly int $userId,
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
        ProfileModerationService $moderationService,
    ): void {
        $user = User::find($this->userId);

        if ($user === null) {
            return;
        }

        if (
            $user->moderation_status
            !== ModerationStatus::PendingModeration
        ) {
            return;
        }

        $moderationService->moderate($user);
    }

    public function backoff(): array
    {
        return [30, 120, 300];
    }
}
