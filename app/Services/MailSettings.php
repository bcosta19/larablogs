<?php

namespace App\Services;

use App\Models\MailSetting;
use Illuminate\Support\Facades\Mail;

class MailSettings
{
    public function configure(): void
    {
        $settings = MailSetting::query()->first();
        if (! $settings) {
            return;
        }

        config([
            'mail.mailers.larablogs_resend' => [
                'transport' => 'smtp',
                'scheme' => 'smtps',
                'host' => 'smtp.resend.com',
                'port' => 465,
                'username' => 'resend',
                'password' => $settings->api_key,
                'timeout' => 15,
            ],
            'mail.default' => 'larablogs_resend',
            'mail.from.address' => $settings->from_address,
            'mail.from.name' => 'Larablogs',
        ]);
        Mail::purge('larablogs_resend');
    }

    public function sendTest(string $recipient, string $locale): void
    {
        $this->configure();
        $messages = [
            'pt-BR' => ['Larablogs: teste de conexão de e-mail', 'A configuração de e-mail do Larablogs foi aceita pelo serviço. Novas publicações serão verificadas pelo agendador.'],
            'en' => ['Larablogs: email connection test', 'Your Larablogs email settings were accepted by the service. The scheduler will check for new posts.'],
            'es' => ['Larablogs: prueba de conexión de correo', 'El servicio aceptó la configuración de correo de Larablogs. El programador comprobará las nuevas publicaciones.'],
            'fr' => ['Larablogs : test de connexion e-mail', 'Le service a accepté la configuration e-mail de Larablogs. Le planificateur recherchera les nouvelles publications.'],
            'de' => ['Larablogs: E-Mail-Verbindungstest', 'Der Dienst hat die E-Mail-Einstellungen von Larablogs akzeptiert. Der Zeitplaner prüft auf neue Beiträge.'],
        ];
        [$subject, $body] = $messages[$locale] ?? $messages['en'];
        Mail::raw($body, function ($message) use ($recipient, $subject): void {
            $message->to($recipient)->subject($subject);
        });
    }
}
