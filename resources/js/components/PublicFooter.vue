<template>
    <footer class="pf-footer">
        <div class="pf-container">
            <div class="pf-grid">
                <!-- Columna 1: Empresa -->
                <div class="pf-col">
                    <img :src="'/images/logo/logo-atzion.svg'" alt="Ascensores Tzion" class="pf-logo" />
                    <p class="pf-desc">Especialistas en mantenimiento, reparación, modernización e instalación de equipos de transporte vertical.</p>
                    <p class="pf-nit">NIT: 901.334.693-6</p>
                </div>

                <!-- Columna 2: Enlaces -->
                <div class="pf-col">
                    <h4 class="pf-title">Enlaces</h4>
                    <router-link to="/inicio" class="pf-link">Inicio</router-link>
                    <router-link to="/nosotros" class="pf-link">Nosotros</router-link>
                    <router-link to="/servicios" class="pf-link">Servicios</router-link>
                    <router-link to="/contacto" class="pf-link">Contacto</router-link>
                    <router-link to="/login" class="pf-link">Portal Clientes</router-link>
                </div>

                <!-- Columna 3: Contacto -->
                <div class="pf-col">
                    <h4 class="pf-title">Contacto</h4>
                    <div class="pf-contact-item">
                        <i class="fa fa-map-marker-alt"></i>
                        <span>Carrera 78 # 41-32, Laureles Lorena<br>Medellín, Antioquia</span>
                    </div>
                    <div class="pf-contact-item">
                        <i class="fa fa-phone-alt"></i>
                        <span>PBX: +57 (604) 322 5315<br>+57 302 311 9169<br>+57 300 401 9483</span>
                    </div>
                    <div class="pf-contact-item">
                        <i class="fa fa-envelope"></i>
                        <span>operaciones@ascensorestzion.com</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="pf-bottom">
            <div class="pf-container">
                <p>&copy; {{ new Date().getFullYear() }} Ascensores y Nivelaciones Eléctricas ATZION S.A.S. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>
</template>

<script>
export default { name: 'PublicFooter' };
</script>

<style scoped>
.pf-footer {
    background: #080d18; color: #cbd5e1; font-family: 'Poppins', sans-serif;
    border-top: 1px solid rgba(48, 171, 10, 0.25);
    position: relative;
}
.pf-footer::before {
    content: ''; position: absolute; top: -1px; left: 0; right: 0; height: 1px;
    background: linear-gradient(90deg, transparent, rgba(48, 171, 10, 0.6), transparent);
}

.pf-container { max-width: 1200px; margin: 0 auto; padding: 0 2rem; }

.pf-grid {
    display: grid; grid-template-columns: 2fr 1fr 1.5fr; gap: 3rem;
    padding: 3.5rem 0;
}

.pf-logo { height: 50px; width: auto; filter: brightness(0) invert(1); margin-bottom: 1rem; }

.pf-desc { font-size: 0.9rem; line-height: 1.7; color: #94a3b8; margin-bottom: 0.75rem; }
.pf-nit { font-size: 0.8rem; color: #64748b; }

.pf-title {
    font-size: 1rem; font-weight: 700; color: #fff; margin-bottom: 1.25rem;
    padding-bottom: 0.5rem; border-bottom: 2px solid #30ab0a; display: inline-block;
}

.pf-link {
    display: block; color: #94a3b8; text-decoration: none; font-size: 0.9rem;
    padding: 0.3rem 0; transition: color 0.2s;
}
.pf-link:hover { color: #30ab0a; }

.pf-contact-item {
    display: flex; gap: 0.75rem; margin-bottom: 1rem; font-size: 0.9rem; line-height: 1.6;
}
.pf-contact-item i { color: #30ab0a; margin-top: 0.2rem; flex-shrink: 0; width: 16px; }

.pf-bottom {
    border-top: 1px solid rgba(255, 255, 255, 0.06); padding: 1.25rem 0; text-align: center;
}
.pf-bottom p { font-size: 0.8rem; color: #64748b; margin: 0; }

@media (max-width: 768px) {
    .pf-grid { grid-template-columns: 1fr; gap: 2rem; }
}
</style>
