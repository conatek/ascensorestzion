<?php

namespace App\Console\Commands;

use App\Models\ScheduledVisit;
use App\Models\VisitReminder;
use App\Notifications\VisitReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Despacha los recordatorios vencidos. Corre cada cinco minutos, asi que la hora
 * configurada por el usuario se respeta con ese margen.
 */
class SendVisitReminders extends Command
{
    protected $signature = 'visits:send-reminders {--limit=200 : Tope de recordatorios por pasada}';

    protected $description = 'Envia los recordatorios de visita que ya vencieron';

    public function handle(): int
    {
        $sent = 0;
        $failed = 0;
        $obsolete = 0;

        $due = VisitReminder::query()
            ->due()
            ->with(['user', 'scheduledVisit'])
            ->orderBy('send_at')
            ->limit((int) $this->option('limit'))
            ->get();

        foreach ($due as $reminder) {
            $visit = $reminder->scheduledVisit;

            // Entre que se materializo y ahora la visita pudo cancelarse o
            // completarse. Avisar de algo que ya no va a pasar es peor que callar.
            if (! $visit || ! in_array($visit->status, ScheduledVisit::BLOCKING_STATUSES, true)) {
                $reminder->update(['status' => 'obsoleto']);
                $obsolete++;

                continue;
            }

            if (! $reminder->user) {
                $reminder->update(['status' => 'obsoleto']);
                $obsolete++;

                continue;
            }

            try {
                $reminder->user->notify(new VisitReminderNotification($reminder));

                // "enviado" es despachado: las notificaciones van por cola y el
                // correo sale en el worker. Si la entrega acaba fallando,
                // MarkVisitReminderFailed corrige la fila.
                $reminder->update(['status' => 'enviado', 'sent_at' => now(), 'error' => null]);
                $sent++;
            } catch (\Throwable $e) {
                // Se marca fallido y se sigue: un destinatario con el correo mal
                // escrito no puede dejar sin aviso a los demas.
                $reminder->update([
                    'status' => 'fallido',
                    'error' => mb_substr($e->getMessage(), 0, 255),
                ]);
                $failed++;

                Log::warning('Recordatorio de visita fallido', [
                    'reminder_id' => $reminder->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Recordatorios enviados: {$sent} · fallidos: {$failed} · obsoletos: {$obsolete}");

        return self::SUCCESS;
    }
}
