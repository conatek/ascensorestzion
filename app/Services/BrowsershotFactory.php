<?php

namespace App\Services;

use Spatie\Browsershot\Browsershot;

/**
 * Punto único donde se fijan las rutas de Node/Chrome y el timeout de Browsershot,
 * leídas de config/browsershot.php. En producción el usuario web (www-data) no
 * tiene esos binarios en el PATH; sin esto Browsershot invoca `node` pelado y
 * falla con "sh: 1: node: not found".
 */
class BrowsershotFactory
{
    public static function configure(Browsershot $browsershot): Browsershot
    {
        // Chrome necesita un HOME escribible para su crashpad. Bajo PHP-FPM el
        // usuario web corre sin HOME (clear_env) y Chrome aborta con
        // "chrome_crashpad_handler: --database is required". Le damos uno temporal.
        // Los flags no bastan por sí solos (probado): lo esencial es el HOME.
        $home = getenv('HOME');
        if (! $home || ! is_writable($home)) {
            putenv('HOME='.sys_get_temp_dir());
        }

        $browsershot->addChromiumArguments([
            'disable-crash-reporter',   // no levantar crashpad
            'disable-dev-shm-usage',    // /dev/shm es pequeño en el t2.micro
            'disable-gpu',
        ]);

        if ($chromePath = config('browsershot.chrome_path')) {
            $browsershot->setChromePath($chromePath);
        }
        if ($nodePath = config('browsershot.node_path')) {
            $browsershot->setNodeBinary($nodePath);
        }
        if ($npmPath = config('browsershot.npm_path')) {
            $browsershot->setNpmBinary($npmPath);
        }
        if ($timeout = config('browsershot.timeout')) {
            $browsershot->timeout($timeout);
        }

        return $browsershot;
    }
}
