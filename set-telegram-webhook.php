<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$token = config('services.telegram.bot_token');
$webhookUrl = env('APP_URL') . '/telegram/webhook';

$response = Http::post("https://api.telegram.org/bot{$token}/setWebhook", [
    'url' => $webhookUrl,
]);

$result = $response->json();

if ($result['ok']) {
    echo "✅ Webhook установлен: {$webhookUrl}\n";
} else {
    echo "❌ Ошибка: " . json_encode($result, JSON_UNESCAPED_UNICODE) . "\n";
}