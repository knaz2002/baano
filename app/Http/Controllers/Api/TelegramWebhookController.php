<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    protected $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    public function handle(Request $request)
    {
        $update = $request->all();
        Log::info('Telegram webhook:', $update);

        // Если пользователь нажал /start
        if (isset($update['message']['text'])) {
            $text = $update['message']['text'];
            $chatId = $update['message']['chat']['id'];
            
            if ($text === '/start') {
                $this->telegramService->sendMessage(
                    $chatId,
                    "👋 Привет! Отправь мне свой номер телефона в формате: +79090058855"
                );
                return response()->json(['status' => 'ok']);
            }

            // Если это номер телефона
            if (preg_match('/^\+?\d{10,15}$/', $text)) {
                $phone = $text;
                $this->telegramService->linkPhoneToChat($phone, $chatId);
                
                $this->telegramService->sendMessage(
                    $chatId,
                    "✅ Номер {$phone} привязан! Теперь ты будешь получать коды подтверждения здесь."
                );
                
                return response()->json(['status' => 'ok']);
            }
        }

        return response()->json(['status' => 'ok']);
    }
}