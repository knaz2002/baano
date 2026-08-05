<?php

namespace Tests\Unit;

use App\Enums\ModerationStatus;
use App\Services\Moderation\LocalTextModerator;
use Tests\TestCase;

class LocalTextModeratorTest extends TestCase
{
    private LocalTextModerator $moderator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->moderator = app(LocalTextModerator::class);
    }

    public function test_safe_text_is_approved(): void
    {
        $result = $this->moderator->check(
            'Сдам светлую квартиру на длительный срок.',
            'description'
        );

        $this->assertSame(
            ModerationStatus::Approved,
            $result->status
        );
    }

    public function test_profanity_in_title_is_rejected(): void
    {
        $result = $this->moderator->check(
            'Х.у.й вам, а не аренда',
            'title'
        );

        $this->assertSame(
            ModerationStatus::Rejected,
            $result->status
        );

        $this->assertContains(
            'explicit_profanity_in_title',
            $result->categories
        );
    }

    public function test_profanity_in_description_requires_manual_review(): void
    {
        $result = $this->moderator->check(
            'В отзыве клиент написал: это полный пиздец.',
            'description'
        );

        $this->assertSame(
            ModerationStatus::ManualReview,
            $result->status
        );

        $this->assertContains(
            'quoted_profanity',
            $result->categories
        );
    }

    public function test_direct_threat_is_rejected(): void
    {
        $result = $this->moderator->check(
            'Я тебя убью после встречи.',
            'description'
        );

        $this->assertSame(
            ModerationStatus::Rejected,
            $result->status
        );

        $this->assertContains(
            'direct_threat',
            $result->categories
        );
    }

    public function test_drug_sale_offer_is_rejected(): void
    {
        $result = $this->moderator->check(
            'Продам мефедрон, доставка по городу.',
            'description'
        );

        $this->assertSame(
            ModerationStatus::Rejected,
            $result->status
        );

        $this->assertContains(
            'drug_sale',
            $result->categories
        );
    }
}
