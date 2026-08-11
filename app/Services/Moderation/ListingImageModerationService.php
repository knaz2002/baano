<?php

namespace App\Services\Moderation;

use App\DTO\ModerationResult;
use App\Enums\ModerationStatus;
use App\Models\Listing;
use App\Models\ModerationCheck;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

class ListingImageModerationService
{
    public function __construct(
        private readonly OpenAiModerator $openAiModerator,
        private readonly ModerationCheckService $checkService,
    ) {
    }

    public function moderate(
        Listing $listing,
        Media $media,
    ): ModerationResult {
        if (!$this->openAiModerator->isEnabled()) {
            return ModerationResult::manualReview(
                categories: ['image_provider_unavailable'],
                reason: 'Автоматическая проверка изображения не настроена.',
            );
        }

        $path = $media->getPath();

        if (!is_file($path) || !is_readable($path)) {
            return ModerationResult::manualReview(
                categories: ['image_unavailable'],
                reason: 'Файл изображения недоступен для проверки.',
            );
        }

        $contentDescriptor = [
            'sha256' => hash_file('sha256', $path),
            'size' => filesize($path),
            'mime_type' => $media->mime_type,
        ];

        $provider = 'openai';
        $model = (string) config(
            'moderation.openai.model',
            'omni-moderation-latest'
        );

        $reference = 'media:' . $media->id;

        $existingCheck = $this->checkService->findReusable(
            moderatable: $listing,
            contentType: 'image',
            content: $contentDescriptor,
            provider: $provider,
            contentReference: $reference,
            model: $model,
        );

        if ($existingCheck !== null) {
            return $this->resultFromCheck($existingCheck);
        }

        try {
            $dataUrl = $this->makeDataUrl(
                $path,
                $media->mime_type
            );

            $result = $this->openAiModerator->checkImageUrl(
                $dataUrl
            );
        } catch (Throwable $exception) {
            Log::error(
                'Ошибка OpenAI при модерации изображения.',
                [
                    'listing_id' => $listing->id,
                    'media_id' => $media->id,
                    'exception' => $exception,
                ]
            );

            $result = ModerationResult::manualReview(
                categories: ['image_provider_unavailable'],
                reason: 'Автоматическая проверка изображения временно недоступна.',
            );
        }

        $this->checkService->record(
            moderatable: $listing,
            contentType: 'image',
            content: $contentDescriptor,
            provider: $provider,
            result: $result,
            contentReference: $reference,
            model: $model,
        );

        return $result;
    }

    private function makeDataUrl(
        string $path,
        ?string $mimeType,
    ): string {
        $mimeType = filled($mimeType)
            ? $mimeType
            : 'application/octet-stream';

        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new \RuntimeException(
                'Не удалось прочитать изображение.'
            );
        }

        return sprintf(
            'data:%s;base64,%s',
            $mimeType,
            base64_encode($contents)
        );
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
