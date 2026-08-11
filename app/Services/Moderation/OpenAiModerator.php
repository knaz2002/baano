<?php

namespace App\Services\Moderation;

use App\DTO\ModerationResult;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAiModerator
{
    public function isEnabled(): bool
    {
        return (bool) config('moderation.openai.enabled')
            && filled(config('moderation.openai.api_key'));
    }

    public function checkText(string $text): ModerationResult
    {
        return $this->checkInput($text);
    }

    public function checkImageUrl(string $imageUrl): ModerationResult
    {
        return $this->checkInput([
            [
                'type' => 'image_url',
                'image_url' => [
                    'url' => $imageUrl,
                ],
            ],
        ]);
    }

    public function checkTextAndImage(
        string $text,
        string $imageUrl,
    ): ModerationResult {
        return $this->checkInput([
            [
                'type' => 'text',
                'text' => $text,
            ],
            [
                'type' => 'image_url',
                'image_url' => [
                    'url' => $imageUrl,
                ],
            ],
        ]);
    }

    private function checkInput(
        string|array $input,
    ): ModerationResult {
        if (!$this->isEnabled()) {
            throw new RuntimeException(
                'OpenAI Moderation API не настроен.'
            );
        }

        $response = $this->request()->post(
            'https://api.openai.com/v1/moderations',
            [
                'model' => config(
                    'moderation.openai.model',
                    'omni-moderation-latest'
                ),
                'input' => $input,
            ]
        );

        $response->throw();

        $result = $response->json('results.0');

        if (!is_array($result)) {
            throw new RuntimeException(
                'OpenAI вернул некорректный результат модерации.'
            );
        }

        $scores = $result['category_scores'] ?? [];

        if (!is_array($scores)) {
            $scores = [];
        }

        return $this->classify($scores);
    }

    private function classify(array $scores): ModerationResult
    {
        $manualThreshold = (float) config(
            'moderation.thresholds.manual_review',
            0.35
        );

        $rejectThreshold = (float) config(
            'moderation.thresholds.rejected',
            0.85
        );

        $matchedCategories = [];
        $automaticRejectionDetected = false;
        $highRiskDetected = false;

        foreach ($scores as $providerCategory => $score) {
            $score = (float) $score;

            if ($score < $manualThreshold) {
                continue;
            }

            $category = $this->mapCategory(
                (string) $providerCategory
            );

            $matchedCategories[] = $category;

            if ($score >= $rejectThreshold) {
                $highRiskDetected = true;

                if ($this->isAutomaticRejectionCategory($category)) {
                    $automaticRejectionDetected = true;
                }
            }
        }

        $matchedCategories = array_values(
            array_unique($matchedCategories)
        );

        if ($automaticRejectionDetected) {
            return ModerationResult::rejected(
                categories: $matchedCategories,
                scores: $scores,
                reason: 'OpenAI обнаружил нарушение с высоким уровнем риска.',
            );
        }

        if ($highRiskDetected || $matchedCategories !== []) {
            return ModerationResult::manualReview(
                categories: $matchedCategories,
                scores: $scores,
                reason: $highRiskDetected
                    ? 'Обнаружен высокий риск, требующий решения модератора.'
                    : 'Обнаружен пограничный риск.',
            );
        }

        return ModerationResult::approved(
            scores: $scores
        );
    }

    private function mapCategory(string $category): string
    {
        return match ($category) {
            'sexual/minors' => 'sexual_minors',
            'harassment/threatening' => 'direct_threat',
            'illicit',
            'illicit/violent' => 'illegal_instructions',
            'self-harm' => 'self_harm',
            'self-harm/intent' => 'self_harm_intent',
            'self-harm/instructions' => 'self_harm_instructions',
            'violence/graphic' => 'graphic_violence',
            default => str_replace('/', '_', $category),
        };
    }

    private function isAutomaticRejectionCategory(
        string $category,
    ): bool {
        return in_array(
            $category,
            config(
                'moderation.automatic_rejection_categories',
                []
            ),
            true
        );
    }

    private function request(): PendingRequest
    {
        return Http::withToken(
            (string) config('moderation.openai.api_key')
        )
            ->acceptJson()
            ->asJson()
            ->connectTimeout(
                (int) config(
                    'moderation.openai.connect_timeout',
                    10
                )
            )
            ->timeout(
                (int) config(
                    'moderation.openai.timeout',
                    30
                )
            )
            ->retry(2, 500);
    }
}
