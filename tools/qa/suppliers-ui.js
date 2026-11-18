#!/usr/bin/env node
/**
 * UI test — Gestión de proveedores, fase 1 (directorio, ficha, productos y precios).
 *
 * Dos master a la vez (Ana y Beto), cada uno en su navegador:
 *   1. Directorio: ejemplos cargados, aviso, búsqueda por producto, filtro por tipo.
 *   2. Alta de proveedor con sede y asesor principal en un paso.
 *   3. Ficha: nueva sede, nuevo asesor, producto nuevo con precio, producto del
 *      catálogo (sin duplicar), actualizar precio → historial.
 *   4. Tiempo real: lo que guarda Ana aparece en la pantalla de Beto sin recargar.
 *   5. Edición simultánea: el segundo en guardar recibe el aviso (409).
 *   6. Un coordinador no ve el menú ni entra por URL.
 *   7. Móvil: sin desbordes en directorio y ficha.
 *
 * Requisitos: app en QA_BASE_URL con build vigente, Reverb arriba, los usuarios
 * ana@qa.test / beto@qa.test (master) y coco@qa.test (coordinator), y los
 * proveedores de SupplierExampleSeeder. Ver notes/development/2026-11-18-gestion-proveedores.
 */
const path = require('path');
const fs = require('fs');
const { chromium } = require('playwright-core');

const BASE = process.env.QA_BASE_URL || 'http://localhost:8011';
const CHROME = process.env.QA_CHROME_PATH || '/usr/bin/google-chrome-stable';
const PASSWORD = process.env.QA_PASSWORD || 'password';
const OUT = process.env.QA_OUT || path.join(__dirname, 'output-suppliers');
fs.mkdirSync(OUT, { recursive: true });

const checks = [];
function check(name, ok, detail = '') {
    checks.push({ name, ok: !!ok, detail });
    console.log(`${ok ? '  ✓' : '  ✗'} ${name}${detail ? ' — ' + detail : ''}`);
}

async function login(page, email) {
    await page.goto(`${BASE}/login`, { waitUntil: 'domcontentloaded' });
    await page.waitForSelector('#email', { timeout: 15000 });
    await page.fill('#email', email);
    await page.fill('#password', PASSWORD);
    await page.click('.btn-submit');
    await page.waitForURL((u) => !u.pathname.endsWith('/login'), { timeout: 20000 });
    await page.waitForTimeout(800);
}

async function shot(page, name) {
    await page.screenshot({ path: path.join(OUT, name + '.png'), fullPage: true });
}

// Campo de un modal o formulario por el texto de su etiqueta.
function field(scope, label, group = '.sp-group') {
    return scope.locator(`${group}:has(label:has-text("${label}"))`).locator('input, select, textarea').first();
}

async function overflow(page) {
    return page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
}

(async () => {
    const browser = await chromium.launch({
        executablePath: CHROME, headless: true,
        args: ['--no-sandbox', '--disable-dev-shm-usage'],
    });
    const errors = [];

    try {
        const ctxA = await browser.newContext({ viewport: { width: 1440, height: 900 } });
        const ana = await ctxA.newPage();
        ana.on('pageerror', (e) => errors.push('ana: ' + e.message));
        // Solo cuentan los fallos del módulo: el panel de inicio lanza consultas
        // propias de MySQL que no corren en la base sqlite de pruebas.
        for (const [who, ctx] of [['ana', ctxA]]) {
            ctx.on('response', (r) => {
                if (r.status() >= 500 && /\/api\/(suppliers|supply-items|broadcasting)/.test(r.url())) {
                    errors.push(`${who}: ${r.status()} ${r.url()}`);
                }
            });
        }

        const ctxB = await browser.newContext({ viewport: { width: 1440, height: 900 } });
        const beto = await ctxB.newPage();
        beto.on('pageerror', (e) => errors.push('beto: ' + e.message));

        await login(ana, 'ana@qa.test');
        await login(beto, 'beto@qa.test');

        // ── 1. Directorio ──
        console.log('\n1. Directorio');
        check('El menú muestra Proveedores al master', await ana.locator('.sidebar-nav a[href="/proveedores"]').count() === 1);
        await ana.click('.sidebar-nav a[href="/proveedores"]');
        await ana.waitForSelector('.data-table tbody tr', { timeout: 15000 });
        const rows0 = await ana.locator('.data-table tbody tr').count();
        check('Lista los 4 proveedores de ejemplo', rows0 === 4, `${rows0} filas`);
        check('Aviso de datos de ejemplo', await ana.locator('.example-banner').isVisible());
        check('Etiqueta "Ejemplo" en cada fila', await ana.locator('.badge-example').count() === 4);
        check('Botones de contacto del asesor principal',
            await ana.locator('.data-table tbody tr').first().locator('.ca-btn').count() >= 2);
        const wa = await ana.locator('.ca-whatsapp').first().getAttribute('href');
        check('WhatsApp con indicativo 57', /^https:\/\/wa\.me\/57300\d{7}$/.test(wa || ''), wa);
        await shot(ana, '01-directorio');

        await ana.fill('.search-input', 'contactor');
        await ana.waitForTimeout(900);
        const byProduct = await ana.locator('.data-table tbody tr').count();
        check('Buscar "contactor" encuentra a los 2 que lo venden', byProduct === 2, `${byProduct} filas`);
        await shot(ana, '02-busqueda-por-producto');

        await ana.fill('.search-input', '');
        await ana.waitForTimeout(700);
        await ana.selectOption('.filter-select >> nth=0', 'taller');
        await ana.waitForTimeout(700);
        check('Filtro por tipo "Taller"', await ana.locator('.data-table tbody tr').count() === 1);
        await ana.selectOption('.filter-select >> nth=0', '');
        await ana.waitForTimeout(700);
        const cityOptions = await ana.locator('.filter-select >> nth=1').locator('option').count();
        check('El filtro de ciudad ofrece las ciudades de las sedes', cityOptions >= 4, `${cityOptions - 1} ciudades`);

        // Beto se queda mirando el directorio.
        await beto.goto(`${BASE}/proveedores`, { waitUntil: 'domcontentloaded' });
        await beto.waitForSelector('.data-table tbody tr');
        await beto.waitForTimeout(1500); // suscripción al canal

        // ── 2. Alta ──
        console.log('\n2. Alta de proveedor');
        await ana.click('.btn-create');
        await ana.waitForSelector('.form-grid');
        await ana.click('.btn-submit');
        await ana.waitForSelector('.error-text');
        check('Sin razón social no guarda y lo dice en español',
            (await ana.locator('.error-text').first().textContent()).includes('razón social'));

        const f = (label) => field(ana, label, '.form-group');
        await f('Razón social').fill('Ascensores y Partes QA S.A.S.');
        await f('Nombre comercial').fill('Partes QA');
        await f('NIT').fill('901.999.999-9');
        const tags = ana.locator('.tag-field');
        await tags.nth(0).fill('Tarjetas electrónicas'); await tags.nth(0).press('Enter');
        await tags.nth(0).fill('Guías y rozaderas'); await tags.nth(0).press('Enter');
        await tags.nth(1).fill('Mitsubishi'); await tags.nth(1).press('Enter');
        await f('Dirección').fill('Cl. 80 # 20-15');
        await f('Ciudad').fill('Bucaramanga');
        await f('Departamento').fill('Santander');
        const advisor = ana.locator('.section-card:has-text("Asesor principal")');
        await field(advisor, 'Nombre', '.form-group').fill('Sofía Prueba');
        await field(advisor, 'Cargo', '.form-group').fill('Asesora');
        await f('Celular / WhatsApp').fill('311 555 0101');
        await f('Forma de pago').fill('Contado');
        await f('Entrega habitual').fill('4');
        await ana.locator('.rating-star').nth(3).click();
        await shot(ana, '03-formulario-nuevo');
        await ana.click('.btn-submit');
        await ana.waitForURL(/\/proveedores\/\d+$/, { timeout: 15000 });
        await ana.waitForSelector('.tab-nav');
        const supplierUrl = ana.url();
        check('Tras guardar abre la ficha', true, supplierUrl.replace(BASE, ''));
        check('La sede principal quedó creada', (await ana.locator('.info-card').count()) === 1
            && (await ana.locator('.info-card').first().textContent()).includes('Bucaramanga'));
        check('Autoría visible', (await ana.locator('.summary-author').textContent()).includes('Creado por Ana Master'));

        // ── 4a. Tiempo real en el directorio ──
        await beto.waitForTimeout(2500);
        const betoRows = await beto.locator('.data-table tbody tr').count();
        check('TIEMPO REAL: el proveedor nuevo aparece en el directorio de Beto sin recargar', betoRows === 5, `${betoRows} filas`);

        // ── 3. Ficha ──
        console.log('\n3. Ficha');
        await ana.click('.btn-add');
        await ana.waitForSelector('.sp-box');
        await field(ana.locator('.sp-box'), 'Nombre de la sede').fill('Bodega Girón');
        await field(ana.locator('.sp-box'), 'Ciudad').fill('Girón');
        await field(ana.locator('.sp-box'), 'Celular').fill('312 555 0202');
        await ana.click('.sp-btn-save');
        await ana.waitForSelector('.sp-box', { state: 'detached' });
        await ana.waitForTimeout(500);
        check('Nueva sede añadida', await ana.locator('.info-card').count() === 2);
        check('Solo una sede principal', await ana.locator('.pill-main').count() === 1);
        await shot(ana, '04-ficha-sedes');

        await ana.click('.tab-btn:has-text("Asesores")');
        check('El asesor principal del alta está', (await ana.locator('.info-card').first().textContent()).includes('Sofía Prueba'));
        await ana.click('.btn-add');
        await ana.waitForSelector('.sp-box');
        await field(ana.locator('.sp-box'), 'Nombre').fill('Tomás Prueba');
        await field(ana.locator('.sp-box'), 'Celular').fill('313 555 0303');
        await field(ana.locator('.sp-box'), 'Correo').fill('no-es-correo');
        await ana.locator('.sp-box form').evaluate((el) => el.setAttribute('novalidate', ''));
        await ana.click('.sp-btn-save');
        await ana.waitForSelector('.sp-field-error');
        check('Correo inválido: error en el campo', (await ana.locator('.sp-field-error').textContent()).includes('formato'));
        await field(ana.locator('.sp-box'), 'Correo').fill('tomas@qa.test');
        await field(ana.locator('.sp-box'), 'Sede').selectOption({ label: 'Bodega Girón · Girón' });
        await ana.click('.sp-btn-save');
        await ana.waitForSelector('.sp-box', { state: 'detached' });
        await ana.waitForTimeout(500);
        check('Nuevo asesor añadido con su sede', (await ana.locator('.info-card').nth(1).textContent()).includes('Bodega Girón'));
        await shot(ana, '05-ficha-asesores');

        // Productos
        await ana.click('.tab-btn:has-text("Productos")');
        await ana.click('.btn-add');
        await ana.waitForSelector('.sp-box');
        await ana.fill('.om-search input', 'Rozadera de guía 16 mm');
        await ana.waitForSelector('.om-result--create');
        await ana.click('.om-result--create');
        await field(ana.locator('.sp-box'), 'Categoría').fill('Guías y rozaderas');
        await field(ana.locator('.sp-box'), 'Referencia del fabricante').fill('RZ-16');
        await field(ana.locator('.sp-box'), 'Precio').fill('59500');
        await ana.locator('.sp-check:has-text("incluye IVA") input').check();
        const hint = await ana.locator('.sp-hint:has-text("Se guardará")').textContent();
        check('Con IVA incluido avisa de lo que guardará', hint.includes('50.000'), hint.trim().replace(/\s+/g, ' '));
        await field(ana.locator('.sp-box'), 'Entrega').fill('2');
        await shot(ana, '06-modal-producto-nuevo');
        await ana.click('.sp-btn-save');
        await ana.waitForSelector('.sp-box', { state: 'detached' });
        await ana.waitForTimeout(500);
        const row1 = await ana.locator('.data-table tbody tr').first().textContent();
        check('Producto nuevo con código y precio sin IVA', /REP-\d{5}/.test(row1) && row1.includes('$ 50.000'), row1.replace(/\s+/g, ' ').trim().slice(0, 110));

        // Producto que ya existe en el catálogo (de los ejemplos)
        await ana.click('.btn-add');
        await ana.waitForSelector('.sp-box');
        await ana.fill('.om-search input', 'contactor');
        await ana.waitForSelector('.om-result:not(.om-result--create):not(.om-result--muted)');
        const found = await ana.locator('.om-result:not(.om-result--create):not(.om-result--muted)').count();
        check('El buscador del modal encuentra el producto existente', found === 1);
        await ana.locator('.om-result:not(.om-result--create):not(.om-result--muted)').first().click();
        check('Avisa de cuántos proveedores ya lo ofrecen', (await ana.locator('.om-picked').textContent()).includes('2 proveedores'));
        await field(ana.locator('.sp-box'), 'Precio').fill('172000');
        await ana.click('.sp-btn-save');
        await ana.waitForSelector('.sp-box', { state: 'detached' });
        await ana.waitForTimeout(500);
        check('Dos productos en la ficha', await ana.locator('.data-table tbody tr').count() === 2);

        // Actualizar precio → historial
        await ana.locator('.data-table tbody tr', { hasText: 'Contactor' }).locator('.icon-btn[title="Actualizar precio"]').click();
        await ana.waitForSelector('.sp-box');
        await field(ana.locator('.sp-box'), 'Precio').fill('189200');
        await ana.click('.sp-btn-save');
        await ana.waitForSelector('.sp-box', { state: 'detached' });
        await ana.waitForTimeout(500);
        await ana.locator('.data-table tbody tr', { hasText: 'Contactor' }).locator('.icon-btn[title="Historial de precios"]').click();
        await ana.waitForSelector('.sh-table tbody tr');
        const hist = await ana.locator('.sh-table tbody tr').count();
        const variation = await ana.locator('.sh-var').first().textContent().catch(() => '');
        check('Historial con las dos versiones y la variación', hist === 2 && variation.includes('+10,0'), `${hist} filas, ${variation.trim()}`);
        await shot(ana, '07-historial-precios');
        await ana.click('.sp-close');
        await shot(ana, '08-ficha-productos');

        // Ficha de un ejemplo: precio desactualizado y dólares
        await ana.goto(`${BASE}/proveedores`, { waitUntil: 'domcontentloaded' });
        await ana.waitForSelector('.data-table tbody tr');
        await ana.locator('.supplier-name', { hasText: 'Reverticales' }).click();
        await ana.waitForSelector('.tab-nav');
        await ana.click('.tab-btn:has-text("Productos")');
        check('Aviso de precio desactualizado en el ejemplo', await ana.locator('.stale-note').isVisible()
            && await ana.locator('.price-date.stale').count() === 1);
        await shot(ana, '09-ejemplo-precio-desactualizado');

        // ── 4b / 5. Tiempo real y edición simultánea en la ficha ──
        console.log('\n4-5. Dos master sobre la misma ficha');
        await ana.goto(supplierUrl, { waitUntil: 'domcontentloaded' });
        await ana.waitForSelector('.tab-nav');
        await beto.goto(supplierUrl, { waitUntil: 'domcontentloaded' });
        await beto.waitForSelector('.tab-nav');
        await ana.click('.tab-btn:has-text("Asesores")');
        await beto.click('.tab-btn:has-text("Asesores")');
        await beto.waitForTimeout(1500);

        // Los dos abren el mismo asesor.
        const openTomas = async (page) => {
            await page.locator('.info-card', { hasText: 'Tomás Prueba' }).locator('.icon-btn[title="Editar asesor"]').click();
            await page.waitForSelector('.sp-box');
        };
        await openTomas(ana);
        await openTomas(beto);

        await field(beto.locator('.sp-box'), 'Cargo').fill('Jefe de bodega');
        await beto.click('.sp-btn-save');
        await beto.waitForSelector('.sp-box', { state: 'detached' });

        await ana.waitForTimeout(2500);
        check('TIEMPO REAL: Ana ve el aviso de que Beto actualizó la ficha',
            await ana.locator('.live-notice').isVisible().catch(() => false),
            (await ana.locator('.live-notice').textContent().catch(() => '')).trim());

        await field(ana.locator('.sp-box'), 'Cargo').fill('Vendedor');
        await ana.click('.sp-btn-save');
        await ana.waitForSelector('.swal2-popup', { timeout: 8000 });
        const swal = await ana.locator('.swal2-popup').textContent();
        check('EDICIÓN SIMULTÁNEA: Ana no pisa a Beto y se le dice quién fue', swal.includes('Beto Master'), swal.replace(/\s+/g, ' ').trim().slice(0, 120));
        await shot(ana, '10-aviso-edicion-simultanea');
        await ana.click('.swal2-confirm');
        await ana.waitForTimeout(600);
        check('Queda el cambio de Beto', (await ana.locator('.info-card', { hasText: 'Tomás Prueba' }).textContent()).includes('Jefe de bodega'));

        // ── 6. Coordinador ──
        console.log('\n6. Otros roles');
        const ctxC = await browser.newContext({ viewport: { width: 1440, height: 900 } });
        const coco = await ctxC.newPage();
        await login(coco, 'coco@qa.test');
        check('El coordinador no ve Proveedores en el menú', await coco.locator('.sidebar-nav a[href="/proveedores"]').count() === 0);
        await coco.goto(`${BASE}/proveedores`, { waitUntil: 'domcontentloaded' });
        await coco.waitForTimeout(1500);
        check('El coordinador no entra por URL', !new URL(coco.url()).pathname.startsWith('/proveedores'), new URL(coco.url()).pathname);
        await ctxC.close();

        // ── 7. Móvil ──
        console.log('\n7. Móvil (390 px)');
        const ctxM = await browser.newContext({ viewport: { width: 390, height: 844 }, isMobile: true, hasTouch: true });
        const mob = await ctxM.newPage();
        await login(mob, 'ana@qa.test');
        await mob.goto(`${BASE}/proveedores`, { waitUntil: 'domcontentloaded' });
        await mob.waitForSelector('.data-table tbody tr');
        await mob.waitForTimeout(500);
        check('Directorio sin desborde horizontal', await overflow(mob) <= 1, `${await overflow(mob)} px`);
        await shot(mob, '11-movil-directorio');
        await mob.goto(supplierUrl, { waitUntil: 'domcontentloaded' });
        await mob.waitForSelector('.tab-nav');
        check('Ficha (sedes) sin desborde', await overflow(mob) <= 1, `${await overflow(mob)} px`);
        await shot(mob, '12-movil-ficha-sedes');
        await mob.click('.tab-btn:has-text("Productos")');
        await mob.waitForTimeout(400);
        check('Ficha (productos) sin desborde', await overflow(mob) <= 1, `${await overflow(mob)} px`);
        await shot(mob, '13-movil-ficha-productos');
        const btn = await mob.locator('.ca-btn').first().boundingBox().catch(() => null);
        await mob.click('.tab-btn:has-text("Asesores")');
        await mob.waitForTimeout(300);
        const round = await mob.locator('.ca-btn').first().boundingBox();
        check('Botones de contacto redondos en móvil', Math.abs(round.width - round.height) < 1, `${round.width}×${round.height}`);
        await mob.locator('.btn-add').click();
        await mob.waitForSelector('.sp-box');
        check('Modal sin desborde en móvil', await overflow(mob) <= 1);
        await shot(mob, '14-movil-modal-asesor');
        await ctxM.close();

        // ── 8. Limpieza: borrar el proveedor de la prueba ──
        console.log('\n8. Eliminar');
        await ana.click('.btn-delete');
        await ana.waitForSelector('.swal2-popup');
        await ana.click('.swal2-confirm');
        await ana.waitForURL(/\/proveedores$/, { timeout: 10000 });
        await ana.waitForSelector('.data-table tbody tr');
        check('Eliminar vuelve al directorio con los 4 de ejemplo', await ana.locator('.data-table tbody tr').count() === 4);
        await beto.waitForSelector('.swal2-popup', { timeout: 8000 }).catch(() => {});
        check('TIEMPO REAL: a Beto, que estaba en la ficha, se le avisa del borrado',
            (await beto.locator('.swal2-popup').textContent().catch(() => '')).includes('eliminar'));
    } catch (e) {
        check('El recorrido terminó sin excepción', false, e.message.split('\n')[0]);
    } finally {
        await browser.close();
    }

    const relevant = errors.filter((e) => !/service ?worker|sw\.js|favicon/i.test(e));
    check('Sin errores de JavaScript en consola', relevant.length === 0, relevant.slice(0, 3).join(' | '));

    const failed = checks.filter((c) => !c.ok);
    console.log(`\n${checks.length - failed.length}/${checks.length} comprobaciones correctas. Capturas en ${OUT}`);
    process.exit(failed.length ? 1 : 0);
})();
