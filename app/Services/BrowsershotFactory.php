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
