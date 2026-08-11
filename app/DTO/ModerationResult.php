<?php

namespace App\DTO;

use App\Enums\ModerationStatus;

final class ModerationResult
{
    public function __construct(
        public readonly ModerationStatus $status,
        public readonly array $categories = [],
        public readonly array $scores = [],
        public readonly ?string $reason = null,
    ) {
    }

    public static function approved(
        array $categories = [],
        array $scores = [],
    ): self {
        return new self(
            ModerationStatus::Approved,
            $categories,
            $scores,
        );
    }

    public static function manualReview(
        array $categories,
        array $scores = [],
        ?string $reason = null,
    ): self {
        return new self(
            ModerationStatus::ManualReview,
            $categories,
            $scores,
            $reason,
        );
    }

    public static function rejected(
        array $categories,
        array $scores = [],
        ?string $reason = null,
    ): self {
        return new self(
            ModerationStatus::Rejected,
            $categories,
            $scores,
            $reason,
        );
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status->value,
            'categories' => $this->categories,
            'scores' => $this->scores,
            'reason' => $this->reason,
        ];
    }
}
