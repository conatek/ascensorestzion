#!/usr/bin/env node
/**
 * Revision visual del mapa de la sede: buscador de direcciones y capas.
 *
 * Comprueba lo que se anadio en la opcion A: buscar por direccion con Nominatim
 * y alternar entre callejero, satelite e hibrido. Necesita internet: los tiles y
 * el geocodificador son servicios externos.
 *
 * Uso: QA_BASE_URL=http://127.0.0.1:8011 NODE_PATH=$PWD/node_modules \
 *      node tools/qa/mapa-ui.js
 */
const { chromium } = require('playwright-core');
const fs = require('fs');
const path = require('path');

const BASE = process.env.QA_BASE_URL || 'http://127.0.0.1:8000';
const CHROME = process.env.QA_CHROME_PATH || '/usr/bin/google-chrome-stable';
const SALIDA = path.join(__dirname, 'output-mapa');

const ESCRITORIO = { viewport: { width: 1440, height: 900 } };
const MOVIL = { viewport: { width: 390, height: 844 }, deviceScaleFactor: 2, isMobile: true, hasTouch: true };

const fallos = [];

function comprobar(cond, texto) {
    if (cond) {
        console.log(`  ok   ${texto}`);
    } else {
        console.log(`  FALLA ${texto}`);
        fallos.push(texto);
    }
}

async function entrar(p, email) {
    await p.goto(`${BASE}/login`, { waitUntil: 'domcontentloaded' });
    await p.waitForSelector('#email', { timeout: 20000 });
    await p.fill('#email', email);
    await p.fill('#password', 'password');
    await p.click('.btn-submit');
    await p.waitForURL((u) => !u.pathname.endsWith('/login'), { timeout: 25000 });
    await p.waitForTimeout(2500);
}

/** Espera a que el mapa tenga tiles pintados de verdad, no solo el contenedor. */
async function esperarTiles(p, minimo = 4) {
    await p.waitForFunction(
        (n) => document.querySelectorAll('.leaflet-tile-loaded').length >= n,
        minimo,
        { timeout: 20000 },
    ).catch(() => {});
    await p.waitForTimeout(1200);
}

async function foto(p, nombre) {
    const caja = await p.$('.location-picker');
    const destino = path.join(SALIDA, `${nombre}.png`);
    if (caja) {
        await caja.screenshot({ path: destino });
    } else {
        await p.screenshot({ path: destino });
    }
    console.log(`       -> ${path.relative(process.cwd(), destino)}`);
}

(async () => {
    fs.mkdirSync(SALIDA, { recursive: true });
    const browser = await chromium.launch({ executablePath: CHROME, args: ['--no-sandbox'] });

    try {
        // ---- Escritorio ----
        const ctx = await browser.newContext(ESCRITORIO);
        const p = await ctx.newPage();
        p.on('console', (m) => {
            if (m.type() === 'error') console.log(`       [consola] ${m.text()}`);
        });

        await entrar(p, 'coordinador@ascensorestzion.com');

        console.log('\n/clientes/1/sedes/1/editar');
        await p.goto(`${BASE}/clientes/1/sedes/1/editar`, { waitUntil: 'domcontentloaded' });
        await p.waitForSelector('.location-picker', { timeout: 20000 });
        await esperarTiles(p);

        comprobar(await p.isVisible('.lp-search input'), 'el buscador aparece');
        comprobar(await p.isVisible('.lp-basemap'), 'el selector de capa aparece');
        comprobar(
            (await p.$$('.lp-basemap-btn')).length === 3,
            'hay tres capas (Mapa, Satelite, Hibrido)',
        );
        await foto(p, '01-editar-callejero');

        // El atajo solo sale si la sede ya trae direccion escrita
        const atajo = await p.$('.lp-use-address');
        console.log(`  info  atajo "usar la direccion del formulario": ${atajo ? 'visible' : 'no aplica'}`);

        // ---- Buscador ----
        console.log('\nbuscador de direcciones');
        await p.fill('.lp-search input', 'Calle 50, Ciudad de Panama');
        await p.click('.lp-btn-search');
        await p.waitForSelector('.lp-results .lp-result', { timeout: 20000 }).catch(() => {});
        const resultados = (await p.$$('.lp-results .lp-result')).length;
        comprobar(resultados > 0, `Nominatim devuelve resultados (${resultados})`);
        await foto(p, '02-resultados');

        if (resultados > 0) {
            const latAntes = await p.inputValue('.lp-fields .lp-field:nth-child(1) input');
            await p.click('.lp-results .lp-result');
            await p.waitForTimeout(1500);
            await esperarTiles(p);
            const latDespues = await p.inputValue('.lp-fields .lp-field:nth-child(1) input');
            comprobar(latDespues !== '' && latDespues !== latAntes, `elegir un resultado fija la latitud (${latDespues})`);
            comprobar(await p.isVisible('.lp-pin'), 'el marcador se dibuja');
            comprobar((await p.$$('path.leaflet-interactive')).length > 0, 'el circulo del radio se dibuja');
            await foto(p, '03-resultado-elegido');
        }

        // ---- Capas ----
        console.log('\ncapas del mapa');
        for (const [indice, nombre] of [[1, 'satelite'], [2, 'hibrido']]) {
            await p.click(`.lp-basemap-btn:nth-child(${indice + 1})`);
            await esperarTiles(p, 2);
            const activa = (await p.textContent('.lp-basemap-btn.is-active'))
                .trim().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
            comprobar(activa === nombre, `la capa activa es ${nombre}`);
            const tilesEsri = await p.$$eval(
                '.leaflet-tile',
                (els) => els.filter((e) => e.src.includes('arcgisonline')).length,
            );
            comprobar(tilesEsri > 0, `se cargan tiles de la ortofoto en ${nombre} (${tilesEsri})`);
            await foto(p, `0${indice + 3}-${nombre}`);
        }

        await ctx.close();

        // ---- Movil ----
        console.log('\nmovil (390px)');
        const ctxM = await browser.newContext(MOVIL);
        const pm = await ctxM.newPage();
        await entrar(pm, 'coordinador@ascensorestzion.com');
        await pm.goto(`${BASE}/clientes/1/sedes/1/editar`, { waitUntil: 'domcontentloaded' });
        await pm.waitForSelector('.location-picker', { timeout: 20000 });
        await esperarTiles(pm);

        const desborde = await pm.evaluate(() => {
            const caja = document.querySelector('.location-picker');
            const rc = caja.getBoundingClientRect();
            let peor = 0;
            for (const el of caja.querySelectorAll('input, button, .lp-basemap, .lp-results')) {
                const r = el.getBoundingClientRect();
                if (r.width > 0) peor = Math.max(peor, r.right - rc.right);
            }
            return Math.round(peor);
        });
        comprobar(desborde <= 2, `nada se sale de la tarjeta en movil (peor: ${desborde}px)`);
        await foto(pm, '06-movil');

        await ctxM.close();
    } finally {
        await browser.close();
    }

    console.log(`\n${fallos.length ? `${fallos.length} fallo(s)` : 'todo en verde'}`);
    process.exit(fallos.length ? 1 : 0);
})();
