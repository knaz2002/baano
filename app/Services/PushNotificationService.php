<?php

namespace App\Services;

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    protected $webPush;
    protected $isConfigured = false;

    public function __construct()
    {
        $publicKey = config('services.vapid.public_key');
        $privateKey = config('services.vapid.private_key');
        $subject = config('services.vapid.subject');

        if ($publicKey && $privateKey && $subject) {
            $this->webPush = new WebPush([
                'VAPID' => [
                    'subject' => $subject,
                    'publicKey' => $publicKey,
                    'privateKey' => $privateKey,
                ],
            ]);
            $this->isConfigured = true;
        } else {
            Log::warning('VAPID not configured. Push notifications disabled.');
        }
    }

    public function sendVerificationCode(string $endpoint, string $code): bool
    {
        if (!$this->isConfigured) {
            Log::warning('Push notifications not configured. Skipping verification code.');
            return false;
        }

        try {
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
        } catch (\Exception $e) {
            Log::error('Push notification error: ' . $e->getMessage());
            return false;
        }
    }
}
