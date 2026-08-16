<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPassword extends BaseResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $expire = (int) config(
            'auth.passwords.'.config('auth.defaults.passwords').'.expire',
            60
        );

        return (new MailMessage)
            ->subject('Сброс пароля')
            ->line('Вы получили это письмо, потому что мы получили запрос на сброс пароля для вашего аккаунта.')
            ->action('Сбросить пароль', $this->resetUrl($notifiable))
            ->line("Ссылка действительна {$expire} минут.")
            ->line('Если вы не запрашивали сброс пароля, никаких действий не требуется.');
    }

    protected function resetUrl($notifiable): string
    {
        $frontend = rtrim((string) env('FRONTEND_URL', 'http://127.0.0.1:3000'), '/');

        return $frontend.'/reset-password/'.$this->token
            .'?email='.urlencode($notifiable->getEmailForPasswordReset());
    }
}
