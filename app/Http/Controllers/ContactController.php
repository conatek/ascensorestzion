<?php

namespace App\Http\Controllers;

use App\Mail\ContactMessageMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class ContactController extends Controller
{
    /** Asuntos permitidos (whitelist) → etiqueta legible. */
    private const ASUNTOS = [
        'mantenimiento' => 'Mantenimiento',
        'reparacion' => 'Reparación',
        'modernizacion' => 'Modernización',
        'instalacion' => 'Instalación',
        'cotizacion' => 'Cotización',
        'otro' => 'Otro',
    ];

    public function send(Request $request): JsonResponse
    {
        // Honeypot: campo trampa oculto que solo los bots rellenan. Si viene con
        // contenido, fingimos éxito y no enviamos nada.
        if ($request->filled('website')) {
            return response()->json(['message' => 'Mensaje enviado. Gracias por contactarnos.']);
        }

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:150'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'asunto' => ['required', Rule::in(array_keys(self::ASUNTOS))],
            'mensaje' => ['required', 'string', 'max:2000'],
        ]);

        $data['asunto_label'] = self::ASUNTOS[$data['asunto']];

        try {
            Mail::to(config('mail.contact_to'))->send(new ContactMessageMail($data));
        } catch (\Throwable $e) {
            Log::error('Error al enviar mensaje de contacto', ['error' => $e->getMessage()]);

            return response()->json([
                'message' => 'No se pudo enviar el mensaje en este momento. Intenta más tarde.',
            ], 500);
        }

        return response()->json([
            'message' => 'Mensaje enviado correctamente. Te responderemos pronto.',
        ]);
    }
}
