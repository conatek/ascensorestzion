<?php

namespace Tests\Feature;

use App\Mail\ContactMessageMail;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactTest extends TestCase
{
    private array $valid = [
        'nombre' => 'Juan Pérez',
        'email' => 'juan@correo.com',
        'telefono' => '+57 300 000 0000',
        'asunto' => 'cotizacion',
        'mensaje' => 'Necesito una cotización para el mantenimiento.',
    ];

    public function test_envio_valido_manda_correo_al_destinatario_con_reply_to(): void
    {
        Mail::fake();

        $this->postJson('/api/contact', $this->valid)->assertOk();

        Mail::assertSent(ContactMessageMail::class, fn ($m) => $m->hasTo(config('mail.contact_to')) && $m->hasReplyTo('juan@correo.com'));
    }

    public function test_validacion_campos_requeridos(): void
    {
        Mail::fake();

        $this->postJson('/api/contact', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['nombre', 'email', 'asunto', 'mensaje']);

        Mail::assertNothingSent();
    }

    public function test_asunto_fuera_de_whitelist_se_rechaza(): void
    {
        Mail::fake();

        $this->postJson('/api/contact', array_merge($this->valid, ['asunto' => 'hack']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['asunto']);

        Mail::assertNothingSent();
    }

    public function test_honeypot_bloquea_bots_sin_enviar(): void
    {
        Mail::fake();

        $this->postJson('/api/contact', array_merge($this->valid, ['website' => 'http://spam.example']))
            ->assertOk();

        Mail::assertNothingSent();
    }

    public function test_email_invalido_se_rechaza(): void
    {
        Mail::fake();

        $this->postJson('/api/contact', array_merge($this->valid, ['email' => 'no-es-email']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }
}
