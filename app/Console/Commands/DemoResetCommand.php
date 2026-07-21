<?php

namespace App\Console\Commands;

use Database\Seeders\DemoSeeder;
use Illuminate\Console\Command;

class DemoResetCommand extends Command
{
    protected $signature = 'demo:reset {--force : Forzar ejecución en producción}';

    protected $description = 'Resetea los datos del cliente demo (Edificio Inteligente Poblado)';

    public function handle(): int
    {
        if (app()->environment('production') && ! $this->option('force')) {
            $this->error('Use --force para ejecutar en producción.');

            return 1;
        }

        $this->info('Reseteando datos demo...');

        $seeder = new DemoSeeder;
        $seeder->setCommand($this);
        $seeder->run();

        return 0;
    }
}
