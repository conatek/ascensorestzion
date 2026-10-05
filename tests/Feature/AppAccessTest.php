<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureAppAccessEnabled;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class AppAccessTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::factory()->create(['email' => 'tec@correo.com', 'active' => true]);
    }

    public function test_con_acceso_habilitado_se_inicia_sesion(): void
    {
        config(['app.access_enabled' => true]);
        $this->user();

        $this->postJson('/api/login', ['email' => 'tec@correo.com', 'password' => 'password'])
            ->assertOk()
            ->assertJsonStructure(['access_token']);
    }

    public function test_con_acceso_deshabilitado_nadie_inicia_sesion(): void
    {
        config(['app.access_enabled' => false]);
        $this->user();

        $this->postJson('/api/login', ['email' => 'tec@correo.com', 'password' => 'password'])
            ->assertStatus(401)
            ->assertJson(['message' => EnsureAppAccessEnabled::MESSAGE]);

        $this->assertSame(0, PersonalAccessToken::count());
    }

    public function test_al_deshabilitar_se_cierran_todas_las_sesiones(): void
    {
        config(['app.access_enabled' => true]);
        $token = $this->user()->createToken('auth_token')->plainTextToken;
        User::factory()->create()->createToken('auth_token');

        $this->withToken($token)->getJson('/api/me')->assertOk();

        config(['app.access_enabled' => false]);
        $this->app['auth']->forgetGuards();
        $this->withToken($token)->getJson('/api/me')->assertStatus(401);
        $this->assertSame(0, PersonalAccessToken::count());

        // Al reactivar, el token viejo ya no sirve: hay que volver a entrar.
        config(['app.access_enabled' => true]);
        $this->app['auth']->forgetGuards();
        $this->withToken($token)->getJson('/api/me')->assertStatus(401);
    }

    public function test_tarea_programada_cierra_sesiones_sin_peticiones(): void
    {
        config(['app.access_enabled' => false]);
        $this->user()->createToken('auth_token');

        $this->artisan('schedule:run');

        $this->assertSame(0, PersonalAccessToken::count());
    }
}
