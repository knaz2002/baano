<?php

namespace App\Enums;

enum ModerationStatus: string
{
    case Draft = 'draft';
    case PendingModeration = 'pending_moderation';
    case Approved = 'approved';
    case ManualReview = 'manual_review';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Черновик',
            self::PendingModeration => 'На модерации',
            self::Approved => 'Одобрено',
            self::ManualReview => 'Ручная проверка',
            self::Rejected => 'Отклонено',
        };
    }
}
