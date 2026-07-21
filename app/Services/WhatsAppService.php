<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    private string $apiUrl;

    private string $phoneNumberId;

    private string $accessToken;

    public function __construct()
    {
        $this->apiUrl = config('services.whatsapp.api_url', 'https://graph.facebook.com/v21.0');
        $this->phoneNumberId = config('services.whatsapp.phone_number_id', '');
        $this->accessToken = config('services.whatsapp.access_token', '');
    }

    /**
     * Enviar mensaje de template a un numero de WhatsApp.
     *
     * @param  string  $to  Numero con codigo de pais (ej: 573001234567)
     * @param  string  $templateName  Nombre del template aprobado por Meta
     * @param  array  $parameters  Parametros del template [{type: text, text: value}]
     * @param  string  $language  Codigo de idioma (default: es)
     */
    public function sendTemplate(string $to, string $templateName, array $parameters = [], string $language = 'es'): bool
    {
        if (! $this->isConfigured()) {
            Log::warning('WhatsApp: servicio no configurado. Mensaje no enviado.', [
                'to' => $to,
                'template' => $templateName,
            ]);

            return false;
        }

        // Limpiar numero: solo digitos
        $to = preg_replace('/\D/', '', $to);

        $body = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => ['code' => $language],
            ],
        ];

        // Agregar parametros si hay
        if (! empty($parameters)) {
            $body['template']['components'] = [
                [
                    'type' => 'body',
                    'parameters' => collect($parameters)->map(fn ($param) => [
                        'type' => 'text',
                        'text' => (string) $param,
                    ])->all(),
                ],
            ];
        }

        try {
            $response = Http::withToken($this->accessToken)
                ->post("{$this->apiUrl}/{$this->phoneNumberId}/messages", $body);

            if ($response->successful()) {
                Log::info('WhatsApp: mensaje enviado.', [
                    'to' => $to,
                    'template' => $templateName,
                    'message_id' => $response->json('messages.0.id'),
                ]);

                return true;
            }

            Log::error('WhatsApp: error al enviar mensaje.', [
                'to' => $to,
                'template' => $templateName,
                'status' => $response->status(),
                'error' => $response->json('error'),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('WhatsApp: excepcion al enviar mensaje.', [
                'to' => $to,
                'template' => $templateName,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function isConfigured(): bool
    {
        return ! empty($this->phoneNumberId) && ! empty($this->accessToken);
    }
}
