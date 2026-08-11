<?php

namespace App\Filament\Pages;

use App\Enums\ModerationStatus;
use App\Models\Listing;
use App\Models\Review;
use App\Models\User;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ModerationQueue extends Page
{
    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedShieldExclamation;

    protected static string|UnitEnum|null $navigationGroup =
        'Безопасность';

    protected static ?string $navigationLabel =
        'Очередь модерации';

    protected static ?string $title =
        'Очередь модерации';

    protected static ?string $slug =
        'moderation-queue';

    protected static ?int $navigationSort = 0;

    protected string $view =
        'filament.pages.moderation-queue';

    public function getQueueCounts(): array
    {
        $statuses = [
            ModerationStatus::PendingModeration->value,
            ModerationStatus::ManualReview->value,
        ];

        return [
            'listings' => Listing::query()
                ->whereIn('moderation_status', $statuses)
                ->count(),

            'reviews' => Review::query()
                ->whereIn('moderation_status', $statuses)
                ->count(),

            'profiles' => User::query()
                ->whereNotNull('pending_name')
                ->whereIn('moderation_status', $statuses)
                ->count(),
        ];
    }
}
