// Canal privado "masters": avisa cuando otro master cambia algo del módulo de
// proveedores para que las pantallas abiertas se refresquen solas.
//
// Varias vistas pueden escuchar a la vez, así que la suscripción a Echo es una
// sola y aquí se reparte; se abandona el canal cuando ya nadie escucha.
//
// Es una mejora, no una garantía: si el websocket no conecta, todo sigue
// funcionando y lo que evita pisarse es el 409 del servidor.

const listeners = new Set();
let subscribed = false;

function currentUserId() {
    try {
        return JSON.parse(localStorage.getItem('auth_user') || 'null')?.id ?? null;
    } catch {
        return null;
    }
}

function subscribe() {
    if (subscribed || !window.Echo) return;
    try {
        window.Echo.private('masters').listen('.supply.changed', (event) => {
            // Los cambios propios ya están en pantalla.
            if (Number(event.user_id) === Number(currentUserId())) return;
            listeners.forEach((fn) => {
                try { fn(event); } catch { /* una vista rota no calla a las demás */ }
            });
        });
        subscribed = true;
    } catch {
        subscribed = false;
    }
}

export function onSupplyChange(callback) {
    listeners.add(callback);
    subscribe();

    return () => {
        listeners.delete(callback);
        if (listeners.size === 0 && subscribed && window.Echo) {
            try { window.Echo.leave('masters'); } catch { /* nada */ }
            subscribed = false;
        }
    };
}
