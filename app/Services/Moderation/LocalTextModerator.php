<?php

namespace App\Services\Moderation;

use App\DTO\ModerationResult;

class LocalTextModerator
{
    public function check(
        string $text,
        string $context = 'text',
    ): ModerationResult {
        $normalizedText = $this->normalize($text);

        if ($normalizedText === '') {
            return ModerationResult::approved();
        }

        $rejectedCategories = $this->findRejectedCategories(
            $normalizedText
        );

        if ($rejectedCategories !== []) {
            return ModerationResult::rejected(
                categories: $rejectedCategories,
                reason: 'Локальный фильтр обнаружил явное нарушение.',
            );
        }

        if ($this->containsProfanity($normalizedText)) {
            if (in_array($context, ['name', 'title'], true)) {
                return ModerationResult::rejected(
                    categories: [
                        $context === 'name'
                            ? 'explicit_profanity_in_name'
                            : 'explicit_profanity_in_title',
                    ],
                    reason: 'Обнаружена явная нецензурная лексика.',
                );
            }

            return ModerationResult::manualReview(
                categories: ['quoted_profanity'],
                reason: 'Обнаружена нецензурная лексика, требуется проверка контекста.',
            );
        }

        return ModerationResult::approved();
    }

    private function findRejectedCategories(string $text): array
    {
        $categories = [];

        $groups = [
            'direct_threat' => config(
                'moderation_dictionary.direct_threat_patterns',
                []
            ),
            'drug_sale' => config(
                'moderation_dictionary.drug_sale_patterns',
                []
            ),
            'terrorism_call_to_action' => config(
                'moderation_dictionary.terrorism_patterns',
                []
            ),
            'illegal_instructions' => config(
                'moderation_dictionary.illegal_instruction_patterns',
                []
            ),
        ];

        foreach ($groups as $category => $patterns) {
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $text) === 1) {
                    $categories[] = $category;
                    break;
                }
            }
        }

        return array_values(array_unique($categories));
    }

    private function containsProfanity(string $text): bool
    {
        $compactText = preg_replace(
            '/[^\p{L}\p{N}]+/u',
            '',
            $text
        ) ?? $text;

        foreach (
            config('moderation_dictionary.profanity', [])
            as $fragment
        ) {
            if (
                mb_stripos($compactText, $fragment, 0, 'UTF-8')
                !== false
            ) {
                return true;
            }
        }

        return false;
    }

    private function normalize(string $text): string
    {
        $text = mb_strtolower(trim($text), 'UTF-8');

        if (class_exists(\Normalizer::class)) {
            $text = \Normalizer::normalize(
                $text,
                \Normalizer::FORM_KC
            ) ?: $text;
        }

        $text = strtr(
            $text,
            config(
                'moderation_dictionary.character_replacements',
                []
            )
        );

        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return trim($text);
    }
}
