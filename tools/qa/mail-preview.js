#!/usr/bin/env node
/**
 * Captura los correos ya renderizados para poder revisar los textos como los ve
 * quien los recibe, en vez de leyendo Blade.
 *
 * Uso: los .html los genera scratchpad/render-mails.php; se le pasa la carpeta.
 */
const path = require('path');
const fs = require('fs');
const { chromium } = require('playwright-core');

const DIR = process.argv[2];
const OUT = process.argv[3] || path.join(__dirname, 'output-mails');

(async () => {
    fs.mkdirSync(OUT, { recursive: true });
    const b = await chromium.launch({ executablePath: '/usr/bin/google-chrome-stable', headless: true, args: ['--no-sandbox','--disable-dev-shm-usage'] });
    const p = await (await b.newContext({ viewport: { width: 760, height: 900 }, deviceScaleFactor: 2 })).newPage();

    for (const file of fs.readdirSync(DIR).filter(f => f.endsWith('.html'))) {
        await p.goto('file://' + path.join(DIR, file), { waitUntil: 'load' });
        await p.waitForTimeout(600);
        const name = file.replace('.html', '');
        await p.screenshot({ path: path.join(OUT, `${name}.png`), fullPage: true });
        console.log('✓ ' + name);
    }
    await b.close();
})();
