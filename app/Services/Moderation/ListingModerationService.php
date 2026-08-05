<?php

namespace App\Services\Moderation;

use App\DTO\ModerationResult;
use App\Enums\ModerationStatus;
use App\Models\Listing;
use App\Models\ModerationCheck;
use Illuminate\Support\Facades\Log;
use Throwable;

class ListingModerationService
{
    public function __construct(
        private readonly LocalTextModerator $localModerator,
        private readonly OpenAiModerator $openAiModerator,
        private readonly ListingImageModerationService $imageModerationService,
        private readonly ListingOcrModerationService $ocrModerationService,
        private readonly ModerationResultAggregator $aggregator,
        private readonly ModerationCheckService $checkService,
    ) {
    }

    public function moderate(Listing $listing): ModerationResult
    {
        $listing->loadMissing('media');

        $textPackage = $this->buildTextPackage($listing);

        $titleResult = $this->runLocalCheck(
            listing: $listing,
            content: $listing->title,
            context: 'title',
            reference: 'title',
        );

        $textPackageResults = [
            $this->runLocalCheck(
                listing: $listing,
                content: $textPackage,
                context: 'description',
                reference: 'text_package',
            ),
        ];

        if ($this->openAiModerator->isEnabled()) {
            $textPackageResults[] = $this->runOpenAiCheck(
                $listing,
                $textPackage
            );
        }

        $componentResults = [
            $titleResult,
            $this->aggregator->aggregate(
                $textPackageResults
            ),
        ];

        foreach ($listing->getMedia('images') as $media) {
            $componentResults[] = $this->imageModerationService
                ->moderate($listing, $media);

            $componentResults[] = $this->ocrModerationService
                ->moderate($listing, $media);
        }

        $result = $this->aggregator->aggregateComponents(
            $componentResults
        );

        $this->applyDecision($listing, $result);

        return $result;
    }

    private function runLocalCheck(
        Listing $listing,
        string $content,
        string $context,
        string $reference,
    ): ModerationResult {
        $provider = 'local_rules';
        $model = 'baano-local-v1';

        $existingCheck = $this->checkService->findReusable(
            moderatable: $listing,
            contentType: 'text',
            content: $content,
            provider: $provider,
            contentReference: $reference,
            model: $model,
        );

        if ($existingCheck !== null) {
            return $this->resultFromCheck($existingCheck);
        }

        $result = $this->localModerator->check(
            $content,
            $context
        );

        $this->checkService->record(
            moderatable: $listing,
            contentType: 'text',
            content: $content,
            provider: $provider,
            result: $result,
            contentReference: $reference,
            model: $model,
        );

        return $result;
    }

    private function runOpenAiCheck(
        Listing $listing,
        string $content,
    ): ModerationResult {
        $provider = 'openai';
        $model = (string) config(
            'moderation.openai.model',
            'omni-moderation-latest'
        );

        $existingCheck = $this->checkService->findReusable(
            moderatable: $listing,
            contentType: 'text',
            content: $content,
            provider: $provider,
            contentReference: 'text_package',
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
                'Ошибка OpenAI при модерации объявления.',
                [
                    'listing_id' => $listing->id,
                    'exception' => $exception,
                ]
            );

            $result = ModerationResult::manualReview(
                categories: ['provider_unavailable'],
                reason: 'Автоматическая проверка временно недоступна.',
            );
        }

        $this->checkService->record(
            moderatable: $listing,
            contentType: 'text',
            content: $content,
            provider: $provider,
            result: $result,
            contentReference: 'text_package',
            model: $model,
        );

        return $result;
    }

    private function applyDecision(
        Listing $listing,
        ModerationResult $result,
    ): void {
        $data = [
            'moderation_status' => $result->status,
            'moderation_reason' => $result->reason,
            'moderated_at' => now(),
        ];

        if ($result->status === ModerationStatus::Approved) {
            $publish = (bool) $listing->requested_is_active;

            $data['is_active'] = $publish;
            $data['status'] = $publish
                ? 'active'
                : 'inactive';
            $data['requested_is_active'] = null;
        } else {
            $data['is_active'] = false;
            $data['status'] = 'pending';
        }

        $listing->update($data);
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

    private function buildTextPackage(Listing $listing): string
    {
        $attributes = $listing->listing_attributes;

        if (is_array($attributes)) {
            $attributes = json_encode(
                $attributes,
                JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
            );
        }

        $fileNames = $listing->getMedia('images')
            ->pluck('file_name')
            ->filter()
            ->implode(', ');

        return collect([
            'Заголовок: ' . $listing->title,
            'Описание: ' . $listing->description,
            filled($listing->location)
                ? 'Адрес: ' . $listing->location
                : null,
            filled($listing->city)
                ? 'Город: ' . $listing->city
                : null,
            filled($attributes)
                ? 'Характеристики: ' . $attributes
                : null,
            filled($fileNames)
                ? 'Названия файлов: ' . $fileNames
                : null,
        ])
            ->filter()
            ->implode("\n");
    }
}
