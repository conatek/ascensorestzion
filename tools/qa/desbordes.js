#!/usr/bin/env node
/**
 * Busca elementos que se salen de su contenedor por la derecha.
 *
 * Nace de un fallo real: en `/tech/perfil`, en movil, los campos de «Datos de la
 * cuenta» sobresalian 126 px de la tarjeta. La causa —el `min-width: auto` que
 * traen los elementos de una rejilla CSS— se repite con facilidad, y a simple
 * vista solo se nota si te fijas en el borde de la tarjeta.
 *
 * Recorre las pantallas de los cinco roles en movil y en escritorio y reporta:
 *
 *   · scroll horizontal de la pagina entera;
 *   · cualquier control o tabla que sobresalga de la tarjeta que lo contiene.
 *
 * Uso: QA_BASE_URL=http://127.0.0.1:8011 NODE_PATH=$PWD/node_modules \
 *      node tools/qa/desbordes.js
 */
const { chromium } = require('playwright-core');

const BASE = process.env.QA_BASE_URL || 'http://127.0.0.1:8000';
const CHROME = process.env.QA_CHROME_PATH || '/usr/bin/google-chrome-stable';

const TOLERANCIA = 2;   // px; por debajo es redondeo del navegador

const MOVIL = { viewport: { width: 390, height: 844 }, deviceScaleFactor: 2, isMobile: true, hasTouch: true };
const ESCRITORIO = { viewport: { width: 1440, height: 900 } };

const CUENTAS = {
    tecnico: {
        email: 'tecnico1@ascensorestzion.com',
        rutas: ['/tech', '/tech/agenda', '/tech/checkin', '/tech/equipo/1',
                '/tech/pendientes', '/tech/firmas-pendientes', '/tech/perfil'],
        soloMovil: true,
    },
    coordinador: {
        email: 'coordinador@ascensorestzion.com',
        rutas: ['/', '/clientes', '/clientes/crear', '/clientes/1', '/clientes/1/sedes/crear',
                '/equipos', '/equipos/crear', '/equipos/1', '/cronograma', '/reportes', '/perfil'],
    },
    master: {
        email: 'master@ascensorestzion.com',
        rutas: ['/admin', '/admin/usuarios', '/admin/usuarios/3', '/tarjetas',
                '/tarjetas/crear', '/tarjetas/editar', '/tarjetas/productos/crear',
                '/tarjetas/servicios/crear'],
    },
    cliente: {
        email: 'admin@ccoviedo.com',
        rutas: ['/portal', '/portal/equipos', '/portal/reportes', '/portal/cronograma', '/portal/perfil'],
    },
};

/**
 * Lo que se mide dentro de la pagina.
 *
 * Se compara cada control con la TARJETA que lo contiene, no con su padre
 * inmediato: el padre suele haberse estirado tambien, y entonces el desborde no
 * se ve por ningun lado aunque este a la vista.
 */
function sondear() {
    const TOL = 2;
    const CONTENEDORES = '.card, .info-card, .rem-card, section[class*="card"], .app-card, .panel, .form-card';
    const CONTROLES = 'input, select, textarea, table, .table-responsive > table';
    const fuera = [];

    for (const caja of document.querySelectorAll(CONTENEDORES)) {
        const rc = caja.getBoundingClientRect();
        if (rc.width < 40) continue;

        for (const el of caja.querySelectorAll(CONTROLES)) {
            const r = el.getBoundingClientRect();
            if (r.width < 4) continue;                       // ocultos
            if (getComputedStyle(el).position === 'fixed') continue;

            // Un elemento dentro de algo con scroll propio puede sobresalir sin
            // que sea un fallo: es scroll, no desborde.
            let padre = el.parentElement, conScroll = false;
            while (padre && padre !== caja) {
                const s = getComputedStyle(padre);
                if (s.overflowX === 'auto' || s.overflowX === 'scroll') { conScroll = true; break; }
                padre = padre.parentElement;
            }
            if (conScroll) continue;

            const exceso = r.right - rc.right;
            if (exceso > TOL) {
                fuera.push({
                    control: `${el.tagName.toLowerCase()}${el.name ? '[' + el.name + ']' : ''}`,
                    clase: (el.className || '').toString().slice(0, 40),
                    caja: (caja.className || '').toString().slice(0, 40),
                    exceso: Math.round(exceso),
                });
            }
        }
    }

    return {
        scrollHorizontal: Math.round(document.documentElement.scrollWidth - window.innerWidth),
        fuera,
    };
}

async function entrar(p, email) {
    await p.goto(`${BASE}/login`, { waitUntil: 'domcontentloaded' });
    await p.waitForSelector('#email', { timeout: 20000 });
    await p.fill('#email', email);
    await p.fill('#password', 'password');
    await p.click('.btn-submit');
    await p.waitForURL(u => !u.pathname.endsWith('/login'), { timeout: 25000 });
    await p.waitForTimeout(2500);
}

(async () => {
    const b = await chromium.launch({
        executablePath: CHROME, headless: true,
        args: ['--no-sandbox', '--disable-dev-shm-usage'],
    });

    let problemas = 0;

    for (const [rol, cfg] of Object.entries(CUENTAS)) {
        const vistas = cfg.soloMovil ? [['móvil', MOVIL]] : [['móvil', MOVIL], ['escritorio', ESCRITORIO]];

        for (const [nombre, ctxCfg] of vistas) {
            const ctx = await b.newContext(ctxCfg);
            const p = await ctx.newPage();
            await entrar(p, cfg.email);

            for (const ruta of cfg.rutas) {
                await p.goto(BASE + ruta, { waitUntil: 'domcontentloaded' });
                await p.waitForTimeout(3200);

                const r = await p.evaluate(sondear);
                const avisos = [];

                if (r.scrollHorizontal > TOLERANCIA) {
                    avisos.push(`la página se desplaza ${r.scrollHorizontal} px de lado`);
                }
                for (const f of r.fuera) {
                    avisos.push(`${f.control} ${f.clase ? '.' + f.clase + ' ' : ''}se sale ${f.exceso} px de .${f.caja}`);
                }

                if (avisos.length) {
                    problemas += avisos.length;
                    console.log(`\n✗ ${rol} · ${nombre} · ${ruta}`);
                    avisos.forEach(a => console.log('    ' + a));
                }
            }

            await ctx.close();
            console.log(`  · ${rol} ${nombre}: ${cfg.rutas.length} pantallas revisadas`);
        }
    }

    await b.close();
    console.log(problemas ? `\n${problemas} desbordes` : '\n✓ ninguna pantalla desborda');
    process.exit(problemas ? 1 : 0);
})().catch(e => { console.error(e); process.exit(2); });
