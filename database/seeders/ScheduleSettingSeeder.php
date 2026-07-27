<?php

namespace Database\Seeders;

use App\Models\ScheduleSetting;
use Illuminate\Database\Seeder;

/**
 * Siembra la configuracion del cronograma con los defaults de fabrica.
 *
 * Idempotente y no destructivo: usa firstOrCreate, asi que re-ejecutarlo en
 * produccion no pisa lo que coordinacion haya ajustado desde la interfaz.
 */
class ScheduleSettingSeeder extends Seeder
{
    public function run(): void
    {
        foreach (ScheduleSetting::DEFAULTS as $key => $value) {
            ScheduleSetting::firstOrCreate(['key' => $key], ['value' => $value]);
        }

        ScheduleSetting::flushCache();
    }
}
