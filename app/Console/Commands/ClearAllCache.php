<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ClearAllCache extends Command
{
    protected $signature = 'cache:clearAll';

    protected $description = 'Limpiar cache de todo el sistema';

    public function handle()
    {
        Artisan::call('cache:clear');
        Artisan::call('route:cache');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        Artisan::call('config:cache');
        Artisan::call('optimize:clear');

        echo ">> caché eliminada con éxito\n";

        return Command::SUCCESS;
    }
}
