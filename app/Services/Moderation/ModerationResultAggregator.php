<?php

namespace App\Services\Moderation;

use App\DTO\ModerationResult;
use App\Enums\ModerationStatus;
use InvalidArgumentException;

class ModerationResultAggregator
{
    /**
     * @param array<int, ModerationResult> $results
     */
    public function aggregate(array $results): ModerationResult
    {
        if ($results === []) {
            throw new InvalidArgumentException(
                'Для объединения требуется хотя бы один результат модерации.'
            );
        }

        $statuses = array_map(
            fn (ModerationResult $result) => $result->status,
            $results
        );

        $categories = [];
        $scores = [];
        $reasons = [];

        foreach ($results as $result) {
            $categories = array_merge(
                $categories,
                $result->categories
            );

            foreach ($result->scores as $category => $score) {
                $scores[$category] = max(
                    (float) ($scores[$category] ?? 0),
                    (float) $score
                );
            }

            if (filled($result->reason)) {
                $reasons[] = $result->reason;
            }
        }

        $categories = array_values(array_unique($categories));
        $reasons = array_values(array_unique($reasons));

        $hasApproved = in_array(
            ModerationStatus::Approved,
            $statuses,
            true
        );

        $hasManualReview = in_array(
            ModerationStatus::ManualReview,
            $statuses,
            true
        );

        $hasRejected = in_array(
            ModerationStatus::Rejected,
            $statuses,
            true
        );

        if ($hasApproved && $hasRejected) {
            $categories[] = 'conflicting_results';

            return ModerationResult::manualReview(
                categories: array_values(array_unique($categories)),
                scores: $scores,
                reason: 'Фильтры дали противоречивые результаты.',
            );
        }

        if ($hasManualReview) {
            return ModerationResult::manualReview(
                categories: $categories,
                scores: $scores,
                reason: $this->combineReasons($reasons)
                    ?? 'Требуется ручная проверка.',
            );
        }

        if ($hasRejected) {
            return ModerationResult::rejected(
                categories: $categories,
                scores: $scores,
                reason: $this->combineReasons($reasons)
                    ?? 'Обнаружено явное нарушение.',
            );
        }

        return ModerationResult::approved(
            categories: $categories,
            scores: $scores,
        );
    }

    /**
     * Объединяет решения по разным обязательным элементам материала.
     *
     * @param array<int, ModerationResult> $results
     */
    public function aggregateComponents(
        array $results,
    ): ModerationResult {
        if ($results === []) {
            throw new InvalidArgumentException(
                'Для объединения требуется хотя бы один результат модерации.'
            );
        }

        $categories = [];
        $scores = [];
        $reasons = [];
        $hasRejected = false;
        $hasManualReview = false;

        foreach ($results as $result) {
            $categories = array_merge(
                $categories,
                $result->categories
            );

            foreach ($result->scores as $category => $score) {
                $scores[$category] = max(
                    (float) ($scores[$category] ?? 0),
                    (float) $score
                );
            }

            if (filled($result->reason)) {
                $reasons[] = $result->reason;
            }

            $hasRejected = $hasRejected
                || $result->status === ModerationStatus::Rejected;

            $hasManualReview = $hasManualReview
                || $result->status === ModerationStatus::ManualReview;
        }

        $categories = array_values(array_unique($categories));
        $reasons = array_values(array_unique($reasons));

        if ($hasRejected) {
            return ModerationResult::rejected(
                categories: $categories,
                scores: $scores,
                reason: $this->combineReasons($reasons)
                    ?? 'Один из обязательных элементов содержит нарушение.',
            );
        }

        if ($hasManualReview) {
            return ModerationResult::manualReview(
                categories: $categories,
                scores: $scores,
                reason: $this->combineReasons($reasons)
                    ?? 'Один из обязательных элементов требует проверки.',
            );
        }

        return ModerationResult::approved(
            categories: $categories,
            scores: $scores,
        );
    }

    private function combineReasons(array $reasons): ?string
    {
        if ($reasons === []) {
            return null;
        }

        return implode(' ', $reasons);
    }
}
