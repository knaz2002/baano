<?php

namespace App\Services\Moderation;

use App\DTO\ModerationResult;
use App\Enums\ModerationStatus;
use App\Models\Listing;
use App\Models\ModerationCheck;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

class ListingOcrModerationService
{
    public function __construct(
        private readonly TesseractOcrService $ocrService,
        private readonly LocalTextModerator $localModerator,
        private readonly OpenAiModerator $openAiModerator,
        private readonly ModerationResultAggregator $aggregator,
        private readonly ModerationCheckService $checkService,
    ) {
    }

    public function moderate(
        Listing $listing,
        Media $media,
    ): ModerationResult {
        if (!$this->ocrService->isEnabled()) {
            if ((bool) config('moderation.ocr.required', false)) {
                return ModerationResult::manualReview(
                    categories: ['ocr_disabled'],
                    reason: 'Обязательное распознавание текста не настроено.',
                );
            }

            return ModerationResult::approved();
        }

        try {
            $ocrText = $this->ocrService->extractText(
                $media->getPath()
            );
        } catch (Throwable $exception) {
            Log::error(
                'Ошибка OCR изображения объявления.',
                [
                    'listing_id' => $listing->id,
                    'media_id' => $media->id,
                    'exception' => $exception,
                ]
            );

            return ModerationResult::manualReview(
                categories: ['ocr_unavailable'],
                reason: 'Не удалось распознать текст на изображении.',
            );
        }

        if ($ocrText === '') {
            return $this->recordEmptyResult(
                $listing,
                $media
            );
        }

        $results = [
            $this->runLocalCheck(
                $listing,
                $media,
                $ocrText
            ),
        ];

        if ($this->openAiModerator->isEnabled()) {
            $results[] = $this->runOpenAiCheck(
                $listing,
                $media,
                $ocrText
            );
        }

        return $this->aggregator->aggregate($results);
    }

    private function runLocalCheck(
        Listing $listing,
        Media $media,
        string $ocrText,
    ): ModerationResult {
        $provider = 'local_rules';
        $model = 'baano-local-v1';
        $reference = 'media:' . $media->id;

        $existingCheck = $this->checkService->findReusable(
            moderatable: $listing,
            contentType: 'ocr',
            content: $ocrText,
            provider: $provider,
            contentReference: $reference,
            model: $model,
        );

        if ($existingCheck !== null) {
            return $this->resultFromCheck($existingCheck);
        }

        $result = $this->localModerator->check(
            $ocrText,
            'ocr'
        );

        $this->checkService->record(
            moderatable: $listing,
            contentType: 'ocr',
            content: $ocrText,
            provider: $provider,
            result: $result,
            contentReference: $reference,
            model: $model,
        );

        return $result;
    }

    private function runOpenAiCheck(
        Listing $listing,
        Media $media,
        string $ocrText,
    ): ModerationResult {
        $provider = 'openai';
        $model = (string) config(
            'moderation.openai.model',
            'omni-moderation-latest'
        );

        $reference = 'media:' . $media->id;

        $existingCheck = $this->checkService->findReusable(
            moderatable: $listing,
            contentType: 'ocr',
            content: $ocrText,
            provider: $provider,
            contentReference: $reference,
            model: $model,
        );

        if ($existingCheck !== null) {
            return $this->resultFromCheck($existingCheck);
        }

        try {
            $result = $this->openAiModerator->checkText(
                $ocrText
            );
        } catch (Throwable $exception) {
            Log::error(
                'Ошибка OpenAI при проверке OCR-текста.',
                [
                    'listing_id' => $listing->id,
                    'media_id' => $media->id,
                    'exception' => $exception,
                ]
            );

            $result = ModerationResult::manualReview(
                categories: ['provider_unavailable'],
                reason: 'Проверка распознанного текста временно недоступна.',
            );
        }

        $this->checkService->record(
            moderatable: $listing,
            contentType: 'ocr',
            content: $ocrText,
            provider: $provider,
            result: $result,
            contentReference: $reference,
            model: $model,
        );

        return $result;
    }

    private function recordEmptyResult(
        Listing $listing,
        Media $media,
    ): ModerationResult {
        $result = ModerationResult::approved();

        $this->checkService->record(
            moderatable: $listing,
            contentType: 'ocr',
            content: '',
            provider: 'tesseract',
            result: $result,
            contentReference: 'media:' . $media->id,
            model: (string) config(
                'moderation.ocr.languages',
                'rus+eng'
            ),
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
