<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Config;

class ResetPasswordNotification extends Notification
{
    public function __construct(
        private string $token,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $expire = Config::get('auth.passwords.users.expire', 60);

        // El enlace apunta a la vista SPA de restablecimiento.
        $url = rtrim(config('app.url'), '/')
            .'/restablecer/'.$this->token
            .'?email='.urlencode($notifiable->getEmailForPasswordReset());

        return (new MailMessage)
            ->subject('Restablece tu contraseña — Ascensores Tzion')
            ->greeting('Restablecer contraseña')
            ->line('Recibimos una solicitud para restablecer la contraseña de tu cuenta.')
            ->action('Restablecer contraseña', $url)
            ->line("Este enlace expira en {$expire} minutos.")
            ->line('Si no solicitaste este cambio, puedes ignorar este correo de forma segura.')
            ->salutation('Equipo de Ascensores Tzion');
    }
}
