<?php

namespace App\Services\Moderation;

use App\DTO\ModerationResult;
use App\Models\ModerationCheck;
use Illuminate\Database\Eloquent\Model;

class ModerationCheckService
{
    public function findReusable(
        Model $moderatable,
        string $contentType,
        mixed $content,
        string $provider,
        ?string $contentReference = null,
        ?string $model = null,
    ): ?ModerationCheck {
        $query = $moderatable->moderationChecks()
            ->where(
                'content_hash',
                $this->makeContentHash($content)
            )
            ->where('content_type', $contentType)
            ->where('provider', $provider)
            ->whereIn('status', [
                'approved',
                'manual_review',
                'rejected',
            ]);

        $contentReference === null
            ? $query->whereNull('content_reference')
            : $query->where(
                'content_reference',
                $contentReference
            );

        $model === null
            ? $query->whereNull('model')
            : $query->where('model', $model);

        return $query
            ->latest('checked_at')
            ->latest('id')
            ->first();
    }

    public function record(
        Model $moderatable,
        string $contentType,
        mixed $content,
        string $provider,
        ModerationResult $result,
        ?string $contentReference = null,
        ?string $model = null,
    ): ModerationCheck {
        return $moderatable->moderationChecks()->create([
            'content_type' => $contentType,
            'content_reference' => $contentReference,
            'content_hash' => $this->makeContentHash($content),
            'content_snapshot' => $this->makeSnapshot($content),
            'provider' => $provider,
            'model' => $model,
            'status' => $result->status->value,
            'categories' => $result->categories,
            'scores' => $result->scores,
            'reason' => $result->reason,
            'checked_at' => now(),
        ]);
    }

    public function makeContentHash(mixed $content): string
    {
        $normalized = $this->normalizeForHash($content);

        return hash(
            'sha256',
            json_encode(
                $normalized,
                JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
                | JSON_THROW_ON_ERROR
            )
        );
    }

    private function makeSnapshot(mixed $content): array
    {
        return [
            'content' => $this->normalizeForHash($content),
        ];
    }

    private function normalizeForHash(mixed $content): mixed
    {
        if (is_string($content)) {
            $content = preg_replace(
                '/\s+/u',
                ' ',
                trim($content)
            ) ?? trim($content);

            return $content;
        }

        if (!is_array($content)) {
            return $content;
        }

        if (!array_is_list($content)) {
            ksort($content);
        }

        foreach ($content as $key => $value) {
            $content[$key] = $this->normalizeForHash($value);
        }

        return $content;
    }
}
