<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Equipment;
use App\Models\ScheduledVisit;
use App\Models\Site;
use App\Models\User;
use App\Models\VisitReminder;
use App\Models\VisitReminderSetting;
use App\Notifications\VisitCancelledNotification;
use App\Notifications\VisitReminderNotification;
use App\Notifications\VisitRescheduledNotification;
use App\Notifications\VisitScheduledNotification;
use Carbon\CarbonImmutable;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\ScheduleSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Fase 3: los recordatorios se materializan al programar, se rehacen al mover la
 * visita y el comando del scheduler los despacha a su hora.
 *
 * El tiempo va congelado en todos los tests: si no, "faltan 7 dias" depende del
 * momento en que se corra la suite.
 */
class ScheduleReminderTest extends TestCase
{
    use RefreshDatabase;

    private User $coordinator;

    private User $technician;

    private User $clientAdmin;

    private Equipment $equipment;

    /** Un lunes cualquiera a las 10 de la mañana. */
    private const NOW = '2026-08-03 10:00:00';

    /** Lunes de la semana siguiente: deja sitio a los avisos de 7, 3 y 1 días. */
    private const VISIT_DAY = '2026-08-17';

    protected function setUp(): void
    {
        parent::setUp();

        CarbonImmutable::setTestNow(self::NOW);
        $this->travelTo(self::NOW);

        $this->seed([PermissionSeeder::class, RoleSeeder::class, ScheduleSettingSeeder::class]);

        $this->coordinator = User::factory()->create();
        $this->coordinator->assignRole('coordinator');

        $this->technician = User::factory()->create(['name' => 'Técnico Uno']);
        $this->technician->assignRole('technician');

        $client = Client::create(['business_name' => 'Edificio Central', 'nit' => '900123456-1']);
        $site = Site::create(['client_id' => $client->id, 'name' => 'Torre A', 'address' => 'Calle 1']);

        $this->equipment = Equipment::create([
            'site_id' => $site->id,
            'internal_code' => 'TZ-A-0001',
            'equipment_type' => 'ascensor',
        ]);

        $this->clientAdmin = User::factory()->create(['client_id' => $client->id]);
        $this->clientAdmin->assignRole('admin');
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    private function schedule(string $date = self::VISIT_DAY, string $start = '09:00', string $end = '10:30'): ScheduledVisit
    {
        Sanctum::actingAs($this->coordinator);

        $this->postJson('/api/schedule/visits', [
            'equipment_id' => $this->equipment->id,
            'technician_id' => $this->technician->id,
            'scheduled_start' => "$date $start",
            'scheduled_end' => "$date $end",
        ])->assertCreated();

        return ScheduledVisit::latest('id')->first();
    }

    // ── Materializacion ──

    public function test_programar_una_visita_materializa_los_recordatorios(): void
    {
        Notification::fake();

        $visit = $this->schedule();

        // Cliente: 7, 3 y 1 días antes a las 08:00. Técnico: víspera 18:00 y el
        // mismo día 06:00.
        $this->assertSame(3, VisitReminder::where('user_id', $this->clientAdmin->id)->count());
        $this->assertSame(2, VisitReminder::where('user_id', $this->technician->id)->count());
        $this->assertSame(5, $visit->reminders()->count());
    }

    public function test_el_send_at_es_la_fecha_de_la_visita_menos_los_dias_a_la_hora_fijada(): void
    {
        Notification::fake();

        $this->schedule();

        $sendAts = VisitReminder::where('user_id', $this->clientAdmin->id)
            ->orderBy('send_at')->pluck('send_at')
            ->map(fn ($d) => $d->format('Y-m-d H:i'))->all();

        $this->assertSame(['2026-08-10 08:00', '2026-08-14 08:00', '2026-08-16 08:00'], $sendAts);
    }

    public function test_no_se_crean_recordatorios_con_fecha_pasada(): void
    {
        Notification::fake();

        // Pasado mañana: el aviso de 7 días caería en el pasado y el de 3 también.
        $visit = $this->schedule('2026-08-05');

        $sendAts = VisitReminder::where('user_id', $this->clientAdmin->id)->pluck('send_at');

        $this->assertCount(1, $sendAts);
        $this->assertSame('2026-08-04 08:00', $sendAts->first()->format('Y-m-d H:i'));
        $this->assertTrue($visit->reminders->every(fn ($r) => $r->send_at->isFuture()));
    }

    public function test_los_canales_no_incluyen_whatsapp_todavia(): void
    {
        Notification::fake();

        $this->schedule();

        $this->assertSame(['database', 'mail'], VisitReminder::first()->channels);
    }

    // ── Preferencias del usuario ──

    public function test_las_preferencias_propias_sustituyen_a_los_defaults(): void
    {
        Notification::fake();

        VisitReminderSetting::create([
            'user_id' => $this->clientAdmin->id,
            'enabled' => true,
            'offsets' => [['days_before' => 2, 'time' => '15:30']],
        ]);

        $this->schedule();

        $propios = VisitReminder::where('user_id', $this->clientAdmin->id)->get();

        $this->assertCount(1, $propios);
        $this->assertSame('2026-08-15 15:30', $propios->first()->send_at->format('Y-m-d H:i'));
    }

    public function test_desactivar_los_recordatorios_deja_al_usuario_sin_ninguno(): void
    {
        Notification::fake();

        VisitReminderSetting::create([
            'user_id' => $this->clientAdmin->id,
            'enabled' => false,
            'offsets' => [['days_before' => 1, 'time' => '08:00']],
        ]);

        $this->schedule();

        $this->assertSame(0, VisitReminder::where('user_id', $this->clientAdmin->id)->count());
        // El técnico sigue recibiendo los suyos.
        $this->assertSame(2, VisitReminder::where('user_id', $this->technician->id)->count());
    }

    public function test_cambiar_las_preferencias_rehace_los_recordatorios_futuros(): void
    {
        Notification::fake();

        $this->schedule();

        Sanctum::actingAs($this->clientAdmin);

        $this->putJson('/api/reminder-settings', [
            'enabled' => true,
            'offsets' => [['days_before' => 2, 'time' => '07:00']],
        ])->assertOk()->assertJsonPath('regenerated_visits', 1);

        $vigentes = VisitReminder::where('user_id', $this->clientAdmin->id)->pending()->get();

        $this->assertCount(1, $vigentes);
        $this->assertSame('2026-08-15 07:00', $vigentes->first()->send_at->format('Y-m-d H:i'));
        // Los anteriores no se borran: quedan como rastro.
        $this->assertSame(3, VisitReminder::where('user_id', $this->clientAdmin->id)->where('status', 'obsoleto')->count());
    }

    public function test_no_se_aceptan_mas_de_tres_momentos(): void
    {
        Sanctum::actingAs($this->clientAdmin);

        $this->putJson('/api/reminder-settings', [
            'enabled' => true,
            'offsets' => [
                ['days_before' => 7, 'time' => '08:00'],
                ['days_before' => 5, 'time' => '08:00'],
                ['days_before' => 3, 'time' => '08:00'],
                ['days_before' => 1, 'time' => '08:00'],
            ],
        ])->assertStatus(422)->assertJsonValidationErrors('offsets');
    }

    public function test_los_momentos_repetidos_se_colapsan(): void
    {
        Sanctum::actingAs($this->clientAdmin);

        $this->putJson('/api/reminder-settings', [
            'enabled' => true,
            'offsets' => [
                ['days_before' => 3, 'time' => '08:00'],
                ['days_before' => 3, 'time' => '08:00'],
            ],
        ])->assertOk()->assertJsonCount(1, 'offsets');
    }

    // ── Reprogramar y cancelar ──

    public function test_reprogramar_invalida_los_recordatorios_y_crea_los_nuevos(): void
    {
        Notification::fake();

        $visit = $this->schedule();

        Sanctum::actingAs($this->coordinator);
        $this->putJson("/api/schedule/visits/{$visit->id}", [
            'scheduled_start' => '2026-08-24 09:00',
            'scheduled_end' => '2026-08-24 10:30',
        ])->assertOk();

        $this->assertSame(5, VisitReminder::where('status', 'obsoleto')->count());

        $vigentes = VisitReminder::pending()->where('user_id', $this->clientAdmin->id)->orderBy('send_at')->get();
        $this->assertSame('2026-08-17 08:00', $vigentes->first()->send_at->format('Y-m-d H:i'));
    }

    public function test_cancelar_deja_los_recordatorios_obsoletos(): void
    {
        Notification::fake();

        $visit = $this->schedule();

        Sanctum::actingAs($this->coordinator);
        $this->postJson("/api/schedule/visits/{$visit->id}/cancel", ['cancel_reason' => 'El cliente aplazó'])->assertOk();

        $this->assertSame(0, VisitReminder::pending()->count());
        $this->assertSame(5, VisitReminder::where('status', 'obsoleto')->count());
    }

    // ── El comando ──

    public function test_el_comando_solo_envia_lo_vencido(): void
    {
        Notification::fake();

        $this->schedule();

        // Aún no vence ninguno.
        $this->artisan('visits:send-reminders')->assertSuccessful();
        Notification::assertNotSentTo($this->clientAdmin, VisitReminderNotification::class);
        $this->assertSame(5, VisitReminder::pending()->count());

        // Al llegar el primero (10 de agosto, 08:00) sale solo ese.
        $this->travelTo('2026-08-10 08:03:00');
        CarbonImmutable::setTestNow('2026-08-10 08:03:00');

        $this->artisan('visits:send-reminders')->assertSuccessful();

        Notification::assertSentTo($this->clientAdmin, VisitReminderNotification::class);
        $this->assertSame(1, VisitReminder::where('status', 'enviado')->count());
        $this->assertSame(4, VisitReminder::pending()->count());
    }

    public function test_el_comando_no_reenvia_lo_ya_enviado(): void
    {
        Notification::fake();

        $this->schedule();

        $this->travelTo('2026-08-10 08:03:00');
        CarbonImmutable::setTestNow('2026-08-10 08:03:00');

        $this->artisan('visits:send-reminders')->assertSuccessful();
        $this->artisan('visits:send-reminders')->assertSuccessful();

        Notification::assertSentToTimes($this->clientAdmin, VisitReminderNotification::class, 1);
    }

    /**
     * Dos pasadas simultaneas no mandan el mismo aviso dos veces. Aqui se simula
     * la que ya lo reclamo dejandole el sent_at puesto.
     */
    public function test_una_fila_ya_reclamada_no_se_vuelve_a_enviar(): void
    {
        Notification::fake();

        $this->schedule();

        $this->travelTo('2026-08-10 08:03:00');
        CarbonImmutable::setTestNow('2026-08-10 08:03:00');

        VisitReminder::query()
            ->where('user_id', $this->clientAdmin->id)
            ->pending()
            ->update(['sent_at' => now()]);

        $this->artisan('visits:send-reminders')->assertSuccessful();

        Notification::assertNotSentTo($this->clientAdmin, VisitReminderNotification::class);
    }

    /** Entre la materializacion y el envio la visita pudo cancelarse. */
    public function test_el_comando_no_avisa_de_una_visita_cancelada(): void
    {
        Notification::fake();

        $visit = $this->schedule();
        $visit->update(['status' => 'cancelada']);

        $this->travelTo('2026-08-10 08:03:00');
        CarbonImmutable::setTestNow('2026-08-10 08:03:00');

        $this->artisan('visits:send-reminders')->assertSuccessful();

        Notification::assertNotSentTo($this->clientAdmin, VisitReminderNotification::class);
        $this->assertSame(1, VisitReminder::where('status', 'obsoleto')->where('user_id', $this->clientAdmin->id)->count());
    }

    // ── Avisos inmediatos ──

    public function test_programar_avisa_al_cliente_y_al_tecnico(): void
    {
        Notification::fake();

        $this->schedule();

        Notification::assertSentTo($this->clientAdmin, VisitScheduledNotification::class);
        Notification::assertSentTo($this->technician, VisitScheduledNotification::class);
        // Coordinación no: ya lo ve en el tablero.
        Notification::assertNotSentTo($this->coordinator, VisitScheduledNotification::class);
    }

    public function test_cancelar_avisa_a_las_partes(): void
    {
        Notification::fake();

        $visit = $this->schedule();

        Sanctum::actingAs($this->coordinator);
        $this->postJson("/api/schedule/visits/{$visit->id}/cancel")->assertOk();

        Notification::assertSentTo($this->clientAdmin, VisitCancelledNotification::class);
        Notification::assertSentTo($this->technician, VisitCancelledNotification::class);
    }

    public function test_el_correo_del_recordatorio_lleva_lo_que_hace_falta_para_ir(): void
    {
        $this->schedule();

        $reminder = VisitReminder::where('user_id', $this->technician->id)->first();
        $mail = (new VisitReminderNotification($reminder))->toMail($this->technician);

        $rendered = $mail->render();

        $this->assertStringContainsString('TZ-A-0001', $rendered);
        $this->assertStringContainsString('Torre A', $rendered);
        $this->assertStringContainsString('Calle 1', $rendered);
        $this->assertStringContainsString('/tech/agenda', $rendered);
        $this->assertStringContainsString('Equipo de Ascensores Tzion', $rendered);
    }

    /**
     * Decisión de Antonio (2026-08-07): el asunto empieza por lo que le importa a
     * quien lo recibe y el código del equipo va al final, como referencia para
     * buscarlo en la bandeja.
     */
    public function test_el_asunto_lleva_la_fecha_delante_y_el_codigo_como_referencia(): void
    {
        $visit = $this->schedule();
        $recordatorio = VisitReminder::where('user_id', $this->clientAdmin->id)->first();

        $this->assertSame(
            'Recordatorio: mantenimiento del lunes 17 de agosto (TZ-A-0001)',
            (new VisitReminderNotification($recordatorio))->toMail($this->clientAdmin)->subject,
        );

        $this->assertSame(
            'Visita programada para el lunes 17 de agosto (TZ-A-0001)',
            (new VisitScheduledNotification($visit))->toMail($this->clientAdmin)->subject,
        );

        $this->assertSame(
            'Nueva visita en tu agenda — lunes 17 de agosto (TZ-A-0001)',
            (new VisitScheduledNotification($visit))->toMail($this->technician)->subject,
        );
    }

    /** "Antes" y "Ahora" con el mismo formato: si no, parecen de dos sitios. */
    public function test_el_correo_de_reprogramacion_usa_el_mismo_formato_en_las_dos_fechas(): void
    {
        $visit = $this->schedule();

        $rendered = (new VisitRescheduledNotification(
            $visit,
            CarbonImmutable::parse('2026-08-13 08:30'),
            CarbonImmutable::parse('2026-08-13 10:00'),
        ))->toMail($this->clientAdmin)->render();

        $this->assertStringContainsString('jueves 13 de agosto, 08:30–10:00', $rendered);
        $this->assertStringContainsString('lunes 17 de agosto, 09:00–10:30', $rendered);
    }

    public function test_el_enlace_del_correo_depende_del_rol(): void
    {
        $this->schedule();

        $reminder = VisitReminder::where('user_id', $this->clientAdmin->id)->first();
        $rendered = (new VisitReminderNotification($reminder))->toMail($this->clientAdmin)->render();

        $this->assertStringContainsString('/portal/cronograma', $rendered);
        $this->assertStringNotContainsString('/tech/agenda', $rendered);
    }
}
