<?php

namespace App\Services\Moderation;

use App\DTO\ModerationResult;
use App\Enums\ModerationStatus;
use App\Models\ModerationCheck;
use App\Models\Review;
use Illuminate\Support\Facades\Log;
use Throwable;

class ReviewModerationService
{
    public function __construct(
        private readonly LocalTextModerator $localModerator,
        private readonly OpenAiModerator $openAiModerator,
        private readonly ModerationResultAggregator $aggregator,
        private readonly ModerationCheckService $checkService,
    ) {
    }

    public function moderate(Review $review): ModerationResult
    {
        $content = trim((string) $review->comment);

        $results = [
            $this->runLocalCheck($review, $content),
        ];

        if ($this->openAiModerator->isEnabled()) {
            $results[] = $this->runOpenAiCheck(
                $review,
                $content
            );
        }

        $result = $this->aggregator->aggregate($results);

        $review->update([
            'moderation_status' => $result->status,
            'moderation_reason' => $result->reason,
            'moderated_at' => now(),
            'is_active' => (
                $result->status === ModerationStatus::Approved
            ),
        ]);

        return $result;
    }

    private function runLocalCheck(
        Review $review,
        string $content,
    ): ModerationResult {
        $provider = 'local_rules';
        $model = 'baano-local-v1';

        $existingCheck = $this->checkService->findReusable(
            moderatable: $review,
            contentType: 'text',
            content: $content,
            provider: $provider,
            contentReference: 'comment',
            model: $model,
        );

        if ($existingCheck !== null) {
            return $this->resultFromCheck($existingCheck);
        }

        $result = $this->localModerator->check(
            $content,
            'review'
        );

        $this->checkService->record(
            moderatable: $review,
            contentType: 'text',
            content: $content,
            provider: $provider,
            result: $result,
            contentReference: 'comment',
            model: $model,
        );

        return $result;
    }

    private function runOpenAiCheck(
        Review $review,
        string $content,
    ): ModerationResult {
        $provider = 'openai';
        $model = (string) config(
            'moderation.openai.model',
            'omni-moderation-latest'
        );

        $existingCheck = $this->checkService->findReusable(
            moderatable: $review,
            contentType: 'text',
            content: $content,
            provider: $provider,
            contentReference: 'comment',
            model: $model,
        );

        if ($existingCheck !== null) {
            return $this->resultFromCheck($existingCheck);
        }

        try {
            $result = $this->openAiModerator->checkText(
                $content
            );
        } catch (Throwable $exception) {
            Log::error(
                'Ошибка OpenAI при модерации отзыва.',
                [
                    'review_id' => $review->id,
                    'exception' => $exception,
                ]
            );

            $result = ModerationResult::manualReview(
                categories: ['provider_unavailable'],
                reason: 'Автоматическая проверка временно недоступна.',
            );
        }

        $this->checkService->record(
            moderatable: $review,
            contentType: 'text',
            content: $content,
            provider: $provider,
            result: $result,
            contentReference: 'comment',
            model: $model,
        );

        return $result;
    }

    private function resultFromCheck(
        ModerationCheck $check,
    ): ModerationResult {
        return new ModerationResult(
            status: ModerationStatus::from($check->status),
            categories: $check->categories ?? [],
            scores: $check->scores ?? [],
            reason: $check->reason,
        );
    }
}
