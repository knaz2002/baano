<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected $token;
    protected $botUsername;

    public function __construct()
    {
        $this->token = config('services.telegram.bot_token');
        $this->botUsername = config('services.telegram.bot_username');
    }

    /**
     * Отправить код подтверждения через Telegram
     */
    public function sendVerificationCode(string $phone, string $code): bool
    {
        // Ищем chat_id по номеру телефона в базе
        // Для демо просто логируем
        Log::info("Telegram код для {$phone}: {$code}");
        
        // В production здесь будет отправка через Telegram API
        // Но для этого нужна связь phone -> chat_id
        
        return true;
    }

    /**
     * Получить updates от бота (для получения chat_id)
     */
    public function getUpdates()
    {
        $response = Http::get("https://api.telegram.org/bot{$this->token}/getUpdates");
        return $response->json();
    }

    /**
     * Отправить сообщение пользователю
     */
    public function sendMessage(string $chatId, string $message): bool
    {
        $response = Http::post("https://api.telegram.org/bot{$this->token}/sendMessage", [
            'chat_id' => $chatId,
            'text' => $message,
        ]);

        return $response->successful();
    }

    /**
     * Сохранить chat_id для телефона
     */
    public function linkPhoneToChat(string $phone, string $chatId): void
    {
        // Сохраняем в базу или кэш
        cache(["telegram_{$phone}" => $chatId], now()->addDays(30));
    }

    /**
     * Получить chat_id по телефону
     */
    public function getChatIdByPhone(string $phone): ?string
    {
        return cache("telegram_{$phone}");
    }
}