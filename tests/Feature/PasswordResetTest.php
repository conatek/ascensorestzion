<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::factory()->create(['email' => 'cliente@correo.com', 'active' => true]);
    }

    /** Token del enlace que llegó en el correo. */
    private function sentToken(User $user): string
    {
        $token = null;
        Notification::assertSentTo($user, ResetPasswordNotification::class, function ($n) use (&$token, $user) {
            $url = $n->toMail($user)->actionUrl;
            preg_match('#/restablecer/([^?]+)\?email=#', $url, $m);
            $token = $m[1] ?? null;

            return true;
        });

        return $token;
    }

    public function test_flujo_completo_de_recuperacion(): void
    {
        Notification::fake();
        $user = $this->user();

        $this->postJson('/api/forgot-password', ['email' => 'cliente@correo.com'])->assertOk();
        $token = $this->sentToken($user);

        $this->postJson('/api/reset-password', [
            'token' => $token,
            'email' => 'cliente@correo.com',
            'password' => 'nueva-clave-123',
            'password_confirmation' => 'nueva-clave-123',
        ])->assertOk();

        $this->assertTrue(Hash::check('nueva-clave-123', $user->fresh()->password));
        $this->postJson('/api/login', ['email' => 'cliente@correo.com', 'password' => 'nueva-clave-123'])->assertOk();
    }

    public function test_correo_con_espacios(): void
    {
        Notification::fake();
        $user = $this->user();

        // Mayúsculas no se prueban aquí: sqlite compara sensible a mayúsculas y
        // MySQL (utf8mb4_unicode_ci, producción) no.
        $this->postJson('/api/forgot-password', ['email' => ' cliente@correo.com '])->assertOk();
        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    /**
     * Throttle del broker (60 s): la segunda solicitud no manda otro correo,
     * aunque la respuesta sea la misma, y el enlace del primero sigue valiendo.
     */
    public function test_segunda_solicitud_inmediata_no_reenvia(): void
    {
        Notification::fake();
        $user = $this->user();

        $this->postJson('/api/forgot-password', ['email' => 'cliente@correo.com'])->assertOk();
        $this->postJson('/api/forgot-password', ['email' => 'cliente@correo.com'])->assertOk();
        Notification::assertSentToTimes($user, ResetPasswordNotification::class, 1);
    }

    public function test_correo_se_renderiza(): void
    {
        $user = $this->user();
        $html = (string) (new ResetPasswordNotification('abc'))->toMail($user)->render();
        $this->assertStringContainsString('/restablecer/abc?email=cliente%40correo.com', $html);
    }
}
