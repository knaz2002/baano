<?php

namespace App\Services\Moderation;

use App\DTO\ModerationResult;
use App\Enums\ModerationStatus;
use App\Models\ModerationCheck;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProfileModerationService
{
    public function __construct(
        private readonly LocalTextModerator $localModerator,
        private readonly OpenAiModerator $openAiModerator,
        private readonly ModerationResultAggregator $aggregator,
        private readonly ModerationCheckService $checkService,
    ) {
    }

    public function moderate(User $user): ModerationResult
    {
        $pendingName = trim((string) $user->pending_name);

        if ($pendingName === '') {
            $result = ModerationResult::approved();

            $user->update([
                'moderation_status' => ModerationStatus::Approved,
                'moderation_reason' => null,
                'moderated_at' => now(),
            ]);

            return $result;
        }

        $results = [
            $this->runLocalCheck($user, $pendingName),
        ];

        if ($this->openAiModerator->isEnabled()) {
            $results[] = $this->runOpenAiCheck(
                $user,
                $pendingName
            );
        }

        $result = $this->aggregator->aggregate($results);

        $this->applyDecision($user, $pendingName, $result);

        return $result;
    }

    private function runLocalCheck(
        User $user,
        string $content,
    ): ModerationResult {
        $provider = 'local_rules';
        $model = 'baano-local-v1';

        $existingCheck = $this->checkService->findReusable(
            moderatable: $user,
            contentType: 'profile',
            content: $content,
            provider: $provider,
            contentReference: 'name',
            model: $model,
        );

        if ($existingCheck !== null) {
            return $this->resultFromCheck($existingCheck);
        }

        $result = $this->localModerator->check(
            $content,
            'name'
        );

        $this->checkService->record(
            moderatable: $user,
            contentType: 'profile',
            content: $content,
            provider: $provider,
            result: $result,
            contentReference: 'name',
            model: $model,
        );

        return $result;
    }

    private function runOpenAiCheck(
        User $user,
        string $content,
    ): ModerationResult {
        $provider = 'openai';
        $model = (string) config(
            'moderation.openai.model',
            'omni-moderation-latest'
        );

        $existingCheck = $this->checkService->findReusable(
            moderatable: $user,
            contentType: 'profile',
            content: $content,
            provider: $provider,
            contentReference: 'name',
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
                'Ошибка OpenAI при модерации имени пользователя.',
                [
                    'user_id' => $user->id,
                    'exception' => $exception,
                ]
            );

            $result = ModerationResult::manualReview(
                categories: ['provider_unavailable'],
                reason: 'Автоматическая проверка временно недоступна.',
            );
        }

        $this->checkService->record(
            moderatable: $user,
            contentType: 'profile',
            content: $content,
            provider: $provider,
            result: $result,
            contentReference: 'name',
            model: $model,
        );

        return $result;
    }

    private function applyDecision(
        User $user,
        string $pendingName,
        ModerationResult $result,
    ): void {
        $data = [
            'moderation_status' => $result->status,
            'moderation_reason' => $result->reason,
            'moderated_at' => now(),
        ];

        if ($result->status === ModerationStatus::Approved) {
            $data['name'] = $pendingName;
            $data['pending_name'] = null;
            $data['moderation_reason'] = null;
        }

        $user->update($data);
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
