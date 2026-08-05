<?php

namespace App\Services\Moderation;

use App\Enums\ModerationStatus;
use App\Models\Listing;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ModerationDecisionService
{
    public function __construct(
        private readonly ModerationCheckService $checkService,
    ) {
    }

    public function approve(
        Model $moderatable,
        User $reviewer,
        ?string $reason = null,
    ): void {
        DB::transaction(function () use (
            $moderatable,
            $reviewer,
            $reason,
        ): void {
            match (true) {
                $moderatable instanceof Listing =>
                    $this->approveListing($moderatable, $reason),

                $moderatable instanceof Review =>
                    $this->approveReview($moderatable, $reason),

                $moderatable instanceof User =>
                    $this->approveProfile($moderatable, $reason),

                default => throw new InvalidArgumentException(
                    'Этот тип материала не поддерживает модерацию.'
                ),
            };

            $this->recordHumanDecision(
                moderatable: $moderatable,
                reviewer: $reviewer,
                status: ModerationStatus::Approved,
                reason: $reason,
            );
        });
    }

    public function reject(
        Model $moderatable,
        User $reviewer,
        string $reason,
    ): void {
        DB::transaction(function () use (
            $moderatable,
            $reviewer,
            $reason,
        ): void {
            match (true) {
                $moderatable instanceof Listing =>
                    $this->rejectListing($moderatable, $reason),

                $moderatable instanceof Review =>
                    $this->rejectReview($moderatable, $reason),

                $moderatable instanceof User =>
                    $this->rejectProfile($moderatable, $reason),

                default => throw new InvalidArgumentException(
                    'Этот тип материала не поддерживает модерацию.'
                ),
            };

            $this->recordHumanDecision(
                moderatable: $moderatable,
                reviewer: $reviewer,
                status: ModerationStatus::Rejected,
                reason: $reason,
            );
        });
    }

    private function approveListing(
        Listing $listing,
        ?string $reason,
    ): void {
        $publish = (bool) $listing->requested_is_active;

        $listing->update([
            'moderation_status' => ModerationStatus::Approved,
            'moderation_reason' => $reason,
            'moderated_at' => now(),
            'is_active' => $publish,
            'status' => $publish ? 'active' : 'inactive',
            'requested_is_active' => null,
        ]);
    }

    private function rejectListing(
        Listing $listing,
        string $reason,
    ): void {
        $listing->update([
            'moderation_status' => ModerationStatus::Rejected,
            'moderation_reason' => $reason,
            'moderated_at' => now(),
            'is_active' => false,
            'status' => 'pending',
        ]);
    }

    private function approveReview(
        Review $review,
        ?string $reason,
    ): void {
        $review->update([
            'moderation_status' => ModerationStatus::Approved,
            'moderation_reason' => $reason,
            'moderated_at' => now(),
            'is_active' => true,
        ]);
    }

    private function rejectReview(
        Review $review,
        string $reason,
    ): void {
        $review->update([
            'moderation_status' => ModerationStatus::Rejected,
            'moderation_reason' => $reason,
            'moderated_at' => now(),
            'is_active' => false,
        ]);
    }

    private function approveProfile(
        User $user,
        ?string $reason,
    ): void {
        $data = [
            'moderation_status' => ModerationStatus::Approved,
            'moderation_reason' => $reason,
            'moderated_at' => now(),
        ];

        if (filled($user->pending_name)) {
            $data['name'] = $user->pending_name;
            $data['pending_name'] = null;
        }

        $user->update($data);
    }

    private function rejectProfile(
        User $user,
        string $reason,
    ): void {
        $user->update([
            'moderation_status' => ModerationStatus::Rejected,
            'moderation_reason' => $reason,
            'moderated_at' => now(),
        ]);
    }

    private function recordHumanDecision(
        Model $moderatable,
        User $reviewer,
        ModerationStatus $status,
        ?string $reason,
    ): void {
        $content = $this->contentSnapshot($moderatable);

        $moderatable->moderationChecks()->create([
            'content_type' => 'human_review',
            'content_reference' => 'manual_decision',
            'content_hash' => $this->checkService
                ->makeContentHash($content),
            'content_snapshot' => [
                'content' => $content,
            ],
            'provider' => 'human',
            'model' => null,
            'status' => $status->value,
            'categories' => ['manual_decision'],
            'scores' => [],
            'reason' => $reason,
            'checked_at' => now(),
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
        ]);
    }

    private function contentSnapshot(Model $moderatable): array
    {
        return match (true) {
            $moderatable instanceof Listing => [
                'title' => $moderatable->title,
                'description' => $moderatable->description,
                'location' => $moderatable->location,
                'city' => $moderatable->city,
                'attributes' => $moderatable->listing_attributes,
                'media_ids' => $moderatable
                    ->getMedia('images')
                    ->pluck('id')
                    ->all(),
            ],

            $moderatable instanceof Review => [
                'rating' => $moderatable->rating,
                'comment' => $moderatable->comment,
            ],

            $moderatable instanceof User => [
                'approved_name' => $moderatable->name,
                'pending_name' => $moderatable->pending_name,
            ],

            default => throw new InvalidArgumentException(
                'Невозможно сформировать снимок материала.'
            ),
        };
    }
}
