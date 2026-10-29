<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * El master cambia la contraseña de otro usuario desde "Editar usuario".
 */
class AdminUserPasswordTest extends TestCase
{
    use RefreshDatabase;

    private User $master;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $this->master = User::factory()->create(['active' => true]);
        $this->master->assignRole('master');

        $this->user = User::factory()->create(['email' => 'tec@correo.com', 'active' => true]);
        $this->user->assignRole('technician');
    }

    private function update(array $data)
    {
        Sanctum::actingAs($this->master);

        return $this->putJson("/api/admin/users/{$this->user->id}", array_merge([
            'name' => $this->user->name,
            'email' => $this->user->email,
            'role' => 'technician',
        ], $data));
    }

    public function test_master_cambia_la_contrasena_y_el_usuario_entra_con_ella(): void
    {
        $this->update(['password' => 'nueva-clave-123'])->assertOk();

        $this->assertTrue(Hash::check('nueva-clave-123', $this->user->fresh()->password));
        $this->app['auth']->forgetGuards();
        $this->postJson('/api/login', ['email' => 'tec@correo.com', 'password' => 'nueva-clave-123'])->assertOk();
    }

    public function test_al_cambiarla_se_cierran_sus_sesiones(): void
    {
        $this->user->createToken('auth_token');

        $this->update(['password' => 'nueva-clave-123'])->assertOk();

        $this->assertSame(0, $this->user->tokens()->count());
    }

    public function test_sin_contrasena_no_se_toca(): void
    {
        $before = $this->user->password;
        $this->user->createToken('auth_token');

        $this->update(['name' => 'Otro nombre'])->assertOk();

        $this->assertSame($before, $this->user->fresh()->password);
        $this->assertSame(1, $this->user->tokens()->count());
    }

    public function test_contrasena_corta_da_error_en_el_campo(): void
    {
        $this->update(['password' => 'corta'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('password');
    }
}
