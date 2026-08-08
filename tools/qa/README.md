# QA visual responsive (matriz rol × viewport)

Herramienta para detectar **regresiones de responsive/PWA** capturando las vistas
clave de cada rol en **móvil (390×844)** y **escritorio (1366×900)**.

## Requisitos
- App sirviendo: `php artisan serve` (def. `http://127.0.0.1:8000`).
- BD con seeders (usuarios sembrados con contraseña `password`).
- Chrome del sistema (def. `/usr/bin/google-chrome-stable`).
- `playwright-core` (ya está en devDependencies).
- Para QA fiel a producción: `npm run build` y que **no exista `public/hot`**
  (si corres `npm run dev`, el blade carga del dev server, no del build).

## Uso
```bash
node tools/qa/visual-matrix.js
# solo algunos roles:
QA_ONLY=master,technician node tools/qa/visual-matrix.js
```
Variables: `QA_BASE_URL`, `QA_CHROME_PATH`, `QA_PASSWORD`, `QA_OUT`, `QA_ONLY`.

Salida: PNG en `tools/qa/output/<rol>-<vista>-<viewport>.png` (ignorado por git) +
resumen en consola. **Exit code 1** si alguna vista falla o redirige a
`/acceso-denegado` o `/login` inesperadamente (útil como gate).

## Checklist — matriz a revisar (móvil + escritorio)

Revisar cada captura buscando: contenido completo (sin recorte), bottom nav por
rol (móvil), tablas como cards (móvil) sobre el fondo gris, botones de acción
uniformes, gráficos legibles, y nada solapado.

| Rol | Vistas |
|-----|--------|
| **master** | dashboard, clientes, cliente-detalle, equipos, equipo-detalle, reportes, usuarios, empresas, tarjetas |
| **super** | dashboard, clientes, equipos, reportes, empresas |
| **coordinator** | clientes, equipos, reportes |
| **admin (portal)** | portal, portal-equipos, portal-reportes, portal-cronograma |
| **technician** | tech-inicio, tech-checkin, tech-agenda |

Puntos por viewport:
- **Móvil:** bottom nav visible con los ítems del rol; tablas en formato card sin
  desborde horizontal; gráficos con leyenda abajo / barras horizontales con valor;
  header sin tapar contenido; sin panel blanco detrás de las cards.
- **Escritorio:** sidebar visible (sin bottom nav); tablas como tabla; gráficos con
  leyenda arriba; sin regresiones respecto al diseño previo.

Ver el plan completo en `notes/pwa/README.md`.
