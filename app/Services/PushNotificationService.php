<?php

namespace App\Services;

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    protected $webPush;

    public function __construct()
    {
        $this->webPush = new WebPush([
            'VAPID' => [
                'subject' => config('services.vapid.subject'),
                'publicKey' => config('services.vapid.public_key'),
                'privateKey' => config('services.vapid.private_key'),
            ],
        ]);
    }

    public function sendVerificationCode(string $endpoint, string $code): bool
    {
        $subscription = Subscription::create([
            'endpoint' => $endpoint,
        ]);

        $payload = json_encode([
            'title' => 'Код подтверждения Baano',
            'body' => "Ваш код: {$code}",
            'code' => $code,
        ]);

        $this->webPush->queueNotification($subscription, $payload);

        $success = false;
        foreach ($this->webPush->flush() as $report) {
            if ($report->isSuccess()) {
                $success = true;
                Log::info("Push отправлен успешно");
            } else {
                Log::error("Push не отправлен: " . $report->getReason());
            }
        }

        return $success;
    }
}