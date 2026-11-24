/**
 * Sello de las fotos del reporte: fecha, hora y coordenadas grabadas en la imagen.
 * Va dibujado en el propio JPEG, así sale igual en el detalle, en el PDF y en
 * cualquier sitio al que se reenvíe la foto.
 */
import { getCurrentPosition } from './useGeolocation.js';
import { localDate, localTime } from './localDate.js';

const BRAND_GREEN = '#30ab0a';
const FONT_FAMILY = "'Poppins', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif";
/** Las medidas están pensadas para una foto de 800 px de lado y se escalan. */
const BASE_SIDE = 800;

/**
 * Coordenadas para el sello. Nunca rechaza: un cuarto de máquinas o un sótano no
 * tienen por qué dejar al técnico sin foto. Si el GPS no responde se usa la
 * ubicación del check-in de la visita; si tampoco hay, null.
 */
export async function resolveStampLocation() {
    try {
        const { latitude, longitude } = await getCurrentPosition({ timeout: 6000, maximumAge: 120000 });
        return { latitude, longitude };
    } catch {
        return checkinLocation();
    }
}

function checkinLocation() {
    try {
        const checkin = JSON.parse(sessionStorage.getItem('current_checkin') || '{}');
        const latitude = parseFloat(checkin.latitude);
        const longitude = parseFloat(checkin.longitude);
        if (Number.isFinite(latitude) && Number.isFinite(longitude)) return { latitude, longitude };
    } catch {}
    return null;
}

export function stampLines(date, location) {
    const head = `${localDate(date)} · ${localTime(date)}`;
    if (!location) return [head, 'Ubicación no disponible'];
    return [head, `Lat ${location.latitude.toFixed(6)}`, `Lon ${location.longitude.toFixed(6)}`];
}

/**
 * Dibuja el sello en la esquina inferior izquierda: placa oscura translúcida con
 * barra verde de marca y texto blanco, legible sobre fondos claros y oscuros.
 */
export async function drawPhotoStamp(canvas, { date = new Date(), location = null } = {}) {
    const ctx = canvas.getContext('2d');
    const lines = stampLines(date, location);

    // Poppins llega por Google Fonts; sin conexión y sin caché se cae a la del sistema.
    try {
        await Promise.race([
            Promise.all([document.fonts.load(`600 30px ${FONT_FAMILY}`), document.fonts.load(`500 25px ${FONT_FAMILY}`)]),
            new Promise(resolve => setTimeout(resolve, 1500)),
        ]);
    } catch {}

    let k = Math.min(canvas.width, canvas.height) / BASE_SIDE;
    const font = (weight, size) => `${weight} ${size * k}px ${FONT_FAMILY}`;
    const widest = () => Math.max(...lines.map((line, i) => {
        ctx.font = i === 0 ? font(600, 30) : font(500, 25);
        return ctx.measureText(line).width;
    }));

    // En un recorte muy estrecho la placa no puede salirse de la foto.
    const maxWidth = canvas.width * 0.92;
    const plateWidth = () => widest() + (18 * 2 + 8) * k;
    if (plateWidth() + 44 * k > maxWidth) k *= maxWidth / (plateWidth() + 44 * k);

    const pad = 18 * k;
    const bar = 8 * k;
    const margin = 22 * k;
    const radius = 12 * k;
    const headHeight = 40 * k;
    const lineHeight = 33 * k;
    const width = plateWidth();
    const height = pad * 2 + headHeight + lineHeight * (lines.length - 1) - 6 * k;
    const x = margin;
    const y = canvas.height - margin - height;

    ctx.save();
    roundedRect(ctx, x, y, width, height, radius);
    ctx.clip();
    ctx.fillStyle = 'rgba(0, 0, 0, 0.69)';
    ctx.fillRect(x, y, width, height);
    ctx.fillStyle = BRAND_GREEN;
    ctx.fillRect(x, y, bar, height);
    ctx.restore();

    ctx.save();
    ctx.textBaseline = 'middle';
    ctx.textAlign = 'left';
    const textX = x + bar + pad;
    let lineY = y + pad - 4 * k;
    lines.forEach((line, i) => {
        const h = i === 0 ? headHeight : lineHeight;
        ctx.font = i === 0 ? font(600, 30) : font(500, 25);
        ctx.fillStyle = i === 0 ? '#ffffff' : '#ebebeb';
        ctx.fillText(line, textX, lineY + h / 2);
        lineY += h;
    });
    ctx.restore();

    return canvas;
}

function roundedRect(ctx, x, y, w, h, r) {
    ctx.beginPath();
    ctx.moveTo(x + r, y);
    ctx.arcTo(x + w, y, x + w, y + h, r);
    ctx.arcTo(x + w, y + h, x, y + h, r);
    ctx.arcTo(x, y + h, x, y, r);
    ctx.arcTo(x, y, x + w, y, r);
    ctx.closePath();
}
