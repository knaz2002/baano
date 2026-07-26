<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::find(2);

if (!$user) {
    echo "Пользователь не найден\n";
    exit;
}

echo "User: " . $user->email . "\n";
echo "Verified: " . ($user->hasVerifiedEmail() ? 'YES' : 'NO') . "\n";

if ($user->hasVerifiedEmail()) {
    $user->email_verified_at = null;
    $user->save();
    echo "Email сброшен\n";
}

$user->sendEmailVerificationNotification();
echo "Письмо отправлено!\n";
