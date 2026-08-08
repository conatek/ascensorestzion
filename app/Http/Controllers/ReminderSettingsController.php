<?php

namespace App\Http\Controllers;

use App\Models\ScheduleSetting;
use App\Models\VisitReminderSetting;
use App\Services\ScheduleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Cuando quiere cada usuario que le recuerden sus visitas. Siempre las propias:
 * no hay forma de tocar las de otro.
 */
class ReminderSettingsController extends Controller
{
    public function __construct(
        private ScheduleService $schedule,
    ) {}

    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $own = VisitReminderSetting::query()->where('user_id', $user->id)->first();
        $role = $user->hasRole('technician') ? 'technician' : 'admin';

        return response()->json([
            'enabled' => $own?->enabled ?? true,
            'offsets' => $own?->offsets ?? [],
            // Los de fabrica van aparte para que la interfaz pueda enseñarlos
            // como lo que se aplica mientras el usuario no configure los suyos.
            'defaults' => ScheduleSetting::get(
                $role === 'technician' ? 'default_technician_offsets' : 'default_admin_offsets'
            ),
            'is_custom' => (bool) $own,
            'max_offsets' => VisitReminderSetting::MAX_OFFSETS,
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'enabled' => ['required', 'boolean'],
            'offsets' => ['present', 'array', 'max:'.VisitReminderSetting::MAX_OFFSETS],
            'offsets.*.days_before' => ['required', 'integer', 'between:0,30'],
            'offsets.*.time' => ['required', 'date_format:H:i'],
        ], [
            'offsets.max' => 'Puedes configurar hasta '.VisitReminderSetting::MAX_OFFSETS.' recordatorios.',
            'offsets.*.days_before.between' => 'Los días de antelación van de 0 (el mismo día) a 30.',
        ]);

        // Dos avisos al mismo momento generarian dos correos identicos.
        $offsets = collect($data['offsets'])
            ->unique(fn (array $o) => $o['days_before'].'@'.$o['time'])
            ->sortByDesc('days_before')
            ->values()
            ->all();

        VisitReminderSetting::updateOrCreate(
            ['user_id' => $user->id],
            ['enabled' => $data['enabled'], 'offsets' => $offsets],
        );

        // Lo ya materializado sigue con los momentos viejos: se rehace para sus
        // visitas futuras, que si no el cambio no se nota hasta la siguiente.
        $regenerated = $this->schedule->regenerateUpcomingRemindersFor($user);

        return response()->json([
            'enabled' => $data['enabled'],
            'offsets' => $offsets,
            'is_custom' => true,
            'regenerated_visits' => $regenerated,
        ]);
    }

    /** Volver a los momentos de fabrica. */
    public function destroy(Request $request): JsonResponse
    {
        $user = $request->user();

        VisitReminderSetting::query()->where('user_id', $user->id)->delete();

        $this->schedule->regenerateUpcomingRemindersFor($user);

        return $this->show($request);
    }
}
