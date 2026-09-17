<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Config;

class UserInvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private string $token,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        // Vigencia del enlace (broker 'invitations'), en minutos → días para el texto.
        $minutes = Config::get('auth.passwords.invitations.expire', 10080);
        $days = max(1, (int) round($minutes / 1440));

        // El enlace apunta a la vista SPA de "establecer contraseña".
        $url = rtrim(config('app.url'), '/')
            .'/activar/'.$this->token
            .'?email='.urlencode($notifiable->getEmailForPasswordReset());

        return (new MailMessage)
            ->subject('Tu acceso a Ascensores Tzion')
            ->greeting('Te damos acceso')
            ->line('Creamos una cuenta para ti en la plataforma de Ascensores Tzion.')
            ->line('Para entrar por primera vez, define tu contraseña con el siguiente botón.')
            ->action('Establecer contraseña', $url)
            ->line("Este enlace es válido durante {$days} días.")
            ->line('Si no esperabas este correo, puedes ignorarlo de forma segura.')
            ->salutation('Equipo de Ascensores Tzion');
    }
}
