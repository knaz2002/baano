<?php

namespace Tests\Unit;

use App\DTO\ModerationResult;
use App\Enums\ModerationStatus;
use App\Services\Moderation\ModerationResultAggregator;
use Tests\TestCase;

class ModerationResultAggregatorTest extends TestCase
{
    public function test_all_approved_results_are_approved(): void
    {
        $result = app(ModerationResultAggregator::class)->aggregate([
            ModerationResult::approved(),
            ModerationResult::approved(),
        ]);

        $this->assertSame(
            ModerationStatus::Approved,
            $result->status
        );
    }

    public function test_manual_review_has_priority_over_approved(): void
    {
        $result = app(ModerationResultAggregator::class)->aggregate([
            ModerationResult::approved(),
            ModerationResult::manualReview(
                ['quoted_profanity']
            ),
        ]);

        $this->assertSame(
            ModerationStatus::ManualReview,
            $result->status
        );
    }

    public function test_rejected_result_is_preserved(): void
    {
        $result = app(ModerationResultAggregator::class)->aggregate([
            ModerationResult::rejected(['direct_threat']),
            ModerationResult::rejected(['direct_threat']),
        ]);

        $this->assertSame(
            ModerationStatus::Rejected,
            $result->status
        );
    }

    public function test_conflicting_results_require_manual_review(): void
    {
        $result = app(ModerationResultAggregator::class)->aggregate([
            ModerationResult::approved(),
            ModerationResult::rejected(['illegal_instructions']),
        ]);

        $this->assertSame(
            ModerationStatus::ManualReview,
            $result->status
        );

        $this->assertContains(
            'conflicting_results',
            $result->categories
        );
    }
    public function test_rejected_component_rejects_whole_material(): void
    {
        $result = app(ModerationResultAggregator::class)
            ->aggregateComponents([
                ModerationResult::approved(),
                ModerationResult::rejected(['sexual_minors']),
            ]);

        $this->assertSame(
            ModerationStatus::Rejected,
            $result->status
        );
    }

    public function test_manual_component_sends_whole_material_to_review(): void
    {
        $result = app(ModerationResultAggregator::class)
            ->aggregateComponents([
                ModerationResult::approved(),
                ModerationResult::manualReview(['legal_weapons']),
            ]);

        $this->assertSame(
            ModerationStatus::ManualReview,
            $result->status
        );
    }

}
