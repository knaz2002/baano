<?php

namespace Tests\Unit;

use App\Enums\ModerationStatus;
use App\Services\Moderation\OpenAiModerator;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OpenAiModeratorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'moderation.openai.enabled' => true,
            'moderation.openai.api_key' => 'test-key',
            'moderation.openai.model' => 'omni-moderation-latest',
            'moderation.thresholds.manual_review' => 0.35,
            'moderation.thresholds.rejected' => 0.85,
        ]);
    }

    public function test_low_risk_text_is_approved(): void
    {
        Http::fake([
            'api.openai.com/*' => Http::response([
                'model' => 'omni-moderation-latest',
                'results' => [
                    [
                        'flagged' => false,
                        'categories' => [],
                        'category_scores' => [
                            'harassment' => 0.02,
                            'sexual/minors' => 0.001,
                            'violence' => 0.03,
                        ],
                    ],
                ],
            ]),
        ]);

        $result = app(OpenAiModerator::class)
            ->checkText('Сдам квартиру на длительный срок.');

        $this->assertSame(
            ModerationStatus::Approved,
            $result->status
        );
    }

    public function test_medium_risk_requires_manual_review(): void
    {
        Http::fake([
            'api.openai.com/*' => Http::response([
                'model' => 'omni-moderation-latest',
                'results' => [
                    [
                        'flagged' => false,
                        'categories' => [],
                        'category_scores' => [
                            'harassment' => 0.56,
                            'violence' => 0.12,
                        ],
                    ],
                ],
            ]),
        ]);

        $result = app(OpenAiModerator::class)
            ->checkText('Пограничный текст.');

        $this->assertSame(
            ModerationStatus::ManualReview,
            $result->status
        );

        $this->assertContains(
            'harassment',
            $result->categories
        );
    }

    public function test_high_risk_category_is_rejected_even_when_not_flagged(): void
    {
        Http::fake([
            'api.openai.com/*' => Http::response([
                'model' => 'omni-moderation-latest',
                'results' => [
                    [
                        'flagged' => false,
                        'categories' => [],
                        'category_scores' => [
                            'sexual/minors' => 0.97,
                            'violence' => 0.04,
                        ],
                    ],
                ],
            ]),
        ]);

        $result = app(OpenAiModerator::class)
            ->checkText('Тестовый текст.');

        $this->assertSame(
            ModerationStatus::Rejected,
            $result->status
        );

        $this->assertContains(
            'sexual_minors',
            $result->categories
        );
    }

    public function test_image_request_has_expected_structure(): void
    {
        Http::fake([
            'api.openai.com/*' => Http::response([
                'model' => 'omni-moderation-latest',
                'results' => [
                    [
                        'flagged' => false,
                        'categories' => [],
                        'category_scores' => [
                            'sexual' => 0.01,
                            'violence' => 0.02,
                        ],
                    ],
                ],
            ]),
        ]);

        app(OpenAiModerator::class)
            ->checkImageUrl('https://example.com/image.jpg');

        Http::assertSent(function ($request) {
            return $request->url()
                === 'https://api.openai.com/v1/moderations'
                && $request['input'][0]['type'] === 'image_url'
                && $request['input'][0]['image_url']['url']
                    === 'https://example.com/image.jpg';
        });
    }
}
