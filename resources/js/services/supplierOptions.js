// Listas y formateadores del módulo de proveedores.

export const SUPPLIER_TYPES = [
    { value: 'distribuidor', label: 'Distribuidor' },
    { value: 'importador', label: 'Importador' },
    { value: 'fabricante', label: 'Fabricante' },
    { value: 'taller', label: 'Taller' },
    { value: 'servicios', label: 'Servicios' },
];

export const ITEM_TYPES = [
    { value: 'repuesto', label: 'Repuesto' },
    { value: 'insumo', label: 'Insumo' },
    { value: 'servicio', label: 'Servicio' },
    { value: 'mano_obra', label: 'Mano de obra' },
];

export const UNITS = ['und', 'm', 'kg', 'gal', 'l', 'par', 'juego', 'hora', 'servicio'];

export const PRICE_SOURCES = [
    { value: 'cotizacion', label: 'Cotización' },
    { value: 'lista', label: 'Lista de precios' },
    { value: 'factura', label: 'Factura' },
    { value: 'telefono', label: 'Teléfono / WhatsApp' },
];

// Sugerencias; el campo acepta cualquier texto.
export const CATEGORY_SUGGESTIONS = [
    'Tarjetas electrónicas',
    'Componentes eléctricos',
    'Variadores',
    'Máquinas y motores',
    'Cables y poleas',
    'Puertas y operadores',
    'Botoneras y señalización',
    'Guías y rozaderas',
    'Seguridad y paracaídas',
    'Lubricantes',
    'Ferretería',
    'Rebobinado de motores',
    'Reparación de tarjetas',
    'Mecanizado',
];

export function typeLabel(list, value) {
    return list.find((t) => t.value === value)?.label || value || '—';
}

export function formatMoney(value, currency = 'COP') {
    if (value === null || value === undefined || value === '') return '—';
    const n = Number(value);
    if (currency === 'USD') {
        return 'USD ' + n.toLocaleString('es-CO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    return '$ ' + n.toLocaleString('es-CO', { maximumFractionDigits: 0 });
}

// "2026-11-10" → "10 nov 2026", sin pasar por zona horaria.
export function formatDay(ymd) {
    if (!ymd) return '—';
    const [y, m, d] = String(ymd).slice(0, 10).split('-').map(Number);
    const months = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
    return `${d} ${months[m - 1]} ${y}`;
}

export function daysSince(ymd) {
    if (!ymd) return null;
    const [y, m, d] = String(ymd).slice(0, 10).split('-').map(Number);
    const then = new Date(y, m - 1, d);
    const now = new Date();
    const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    return Math.round((today - then) / 86400000);
}

export function todayYmd() {
    const n = new Date();
    const p = (v) => String(v).padStart(2, '0');
    return `${n.getFullYear()}-${p(n.getMonth() + 1)}-${p(n.getDate())}`;
}

// "hace 2 horas", "ayer", "hace 5 días"
export function timeAgo(iso) {
    if (!iso) return '';
    const mins = Math.max(0, Math.round((Date.now() - new Date(iso).getTime()) / 60000));
    if (mins < 1) return 'hace un momento';
    if (mins < 60) return `hace ${mins} min`;
    const hours = Math.round(mins / 60);
    if (hours < 24) return `hace ${hours} h`;
    const days = Math.round(hours / 24);
    if (days === 1) return 'ayer';
    if (days < 30) return `hace ${days} días`;
    return 'el ' + formatDay(new Date(iso).toISOString());
}

// Número colombiano → enlace de WhatsApp. Un celular de 10 dígitos lleva el 57.
export function whatsappLink(number, text = '') {
    let digits = String(number || '').replace(/\D/g, '');
    if (!digits) return null;
    if (digits.length === 10 && digits.startsWith('3')) digits = '57' + digits;
    return `https://wa.me/${digits}` + (text ? `?text=${encodeURIComponent(text)}` : '');
}

export function telLink(number) {
    const digits = String(number || '').replace(/[^\d+]/g, '');
    return digits ? `tel:${digits}` : null;
}
