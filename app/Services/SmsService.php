<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SmsService
{
    protected string $driver;

    protected ?string $apiId;

    protected bool $test;

    protected ?string $sender;

    protected ?string $debugMail;

    public function __construct()
    {
        $this->driver = config('services.sms.driver', 'log');
        $this->apiId = config('services.sms.smsru.api_id');
        $this->test = (bool) config('services.sms.smsru.test', true);
        $this->sender = config('services.sms.sender');
        $this->debugMail = config('services.sms.debug_mail')
            ?: config('mail.debug_to');
    }

    /**
     * Отправить код подтверждения телефона.
     */
    public function sendVerificationCode(string $phone, string $code): bool
    {
        $message = "Код подтверждения Baano: {$code}";

        return match ($this->driver) {
            'smsru' => $this->sendViaSmsRu($phone, $message),
            'mail' => $this->sendViaMail($phone, $code, $message),
            default => $this->sendViaLog($phone, $code, $message),
        };
    }

    protected function sendViaLog(string $phone, string $code, string $message): bool
    {
        if (app()->isProduction()) {
            Log::info('SMS verification (log driver)', [
                'phone' => $phone,
                'note' => 'code hidden in production',
            ]);
        } else {
            Log::info('SMS verification (log driver)', [
                'phone' => $phone,
                'code' => $code,
                'message' => $message,
            ]);
        }

        return true;
    }

    /**
     * Тестовая отправка кода на email (не на телефон пользователя).
     */
    protected function sendViaMail(string $phone, string $code, string $message): bool
    {
        if (blank($this->debugMail)) {
            Log::error('SMS mail driver: SMS_DEBUG_MAIL is not configured');

            return false;
        }

        try {
            Mail::raw(
                "Тестовый код подтверждения телефона Baano\n\nТелефон: {$phone}\nКод: {$code}\n\n{$message}",
                function ($mail) use ($phone) {
                    $mail->to($this->debugMail)
                        ->subject("Baano: код для {$phone}");
                }
            );

            Log::info('SMS verification sent via mail', [
                'phone' => $phone,
                'to' => $this->debugMail,
                'code' => app()->isProduction() ? '[hidden]' : $code,
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error('SMS mail driver failed', [
                'phone' => $phone,
                'to' => $this->debugMail,
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    protected function sendViaSmsRu(string $phone, string $message): bool
    {
        if (blank($this->apiId)) {
            Log::error('SMS.ru: SMS_RU_API_ID is not configured');

            return false;
        }

        $to = $this->normalizePhone($phone);

        $payload = [
            'api_id' => $this->apiId,
            'to' => $to,
            'msg' => $message,
            'json' => 1,
        ];

        if ($this->test) {
            $payload['test'] = 1;
        }

        if (filled($this->sender)) {
            $payload['from'] = $this->sender;
        }

        try {
            $response = Http::asForm()
                ->timeout(15)
                ->post('https://sms.ru/sms/send', $payload);

            $data = $response->json();

            if (! $response->successful() || ! is_array($data)) {
                Log::error('SMS.ru: HTTP request failed', [
                    'phone' => $to,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return false;
            }

            // status_code 100 = запрос принят
            if (($data['status_code'] ?? null) !== 100) {
                Log::error('SMS.ru: API error', [
                    'phone' => $to,
                    'response' => $data,
                ]);

                return false;
            }

            $smsStatus = data_get($data, "sms.{$to}.status_code");
            if ($smsStatus !== null && (int) $smsStatus !== 100) {
                Log::error('SMS.ru: message rejected', [
                    'phone' => $to,
                    'response' => $data,
                ]);

                return false;
            }

            Log::info('SMS.ru: message accepted', [
                'phone' => $to,
                'test' => $this->test,
                'sms_id' => data_get($data, "sms.{$to}.sms_id"),
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error('SMS.ru: exception', [
                'phone' => $to,
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * SMS.ru ожидает номер вида 79001234567.
     */
    protected function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (str_starts_with($digits, '8') && strlen($digits) === 11) {
            $digits = '7'.substr($digits, 1);
        }

        return $digits;
    }
}
