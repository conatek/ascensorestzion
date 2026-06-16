<template>
    <div class="about-page">
        <PublicNavbar />

        <!-- Hero -->
        <section class="ab-hero">
            <div class="ab-hero-bg">
                <div class="orb orb--green"></div>
                <div class="grid-texture"></div>
            </div>
            <div class="ab-container ab-hero-inner">
                <span class="eyebrow">Sobre nosotros</span>
                <h1 class="ab-hero-title">Construimos confianza, <span class="accent">elevamos estándares</span></h1>
                <p class="ab-hero-subtitle">Conoce nuestra historia, misión y el equipo que hace posible cada proyecto.</p>
            </div>
        </section>

        <!-- Quiénes Somos -->
        <section class="ab-about">
            <div class="ab-container">
                <div class="ab-about-grid">
                    <div class="ab-about-visual">
                        <div class="ab-visual-card">
                            <svg xmlns="http://www.w3.org/2000/svg" width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="#37c20c" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                                <circle cx="12" cy="13" r="4"/>
                            </svg>
                            <span class="ab-visual-text">Fotografía del equipo</span>
                        </div>
                        <div class="ab-float-badge">
                            <span class="ab-float-num">+4</span>
                            <span class="ab-float-label">años elevando<br>estándares</span>
                        </div>
                    </div>
                    <div class="ab-about-content">
                        <span class="eyebrow">Quiénes somos</span>
                        <h2 class="ab-section-title">Acerca de Atzion</h2>
                        <p>En Ascensores Tzion, nuestra historia comienza hace cuatro años con un propósito claro: convertirnos en aliados estratégicos en la seguridad y el rendimiento de los sistemas de transporte vertical. Desde nuestros inicios nos especializamos en el mantenimiento de ascensores y escaleras eléctricas, pero rápidamente ampliamos nuestra oferta para incluir instalación de equipos nuevos, modernización y reparaciones especializadas.</p>
                        <p>Gestionamos toda la cadena de valor: nos encargamos de la importación de equipos y repuestos, trabajando únicamente con proveedores confiables y productos de la más alta calidad. Una vez los equipos llegan al país, supervisamos todo el proceso de instalación hasta la puesta en marcha, garantizando que cada ascensor o escalera eléctrica opere de manera óptima y segura.</p>
                        <p>Trabajamos de la mano con nuestros clientes, comprendiendo sus necesidades para ofrecer soluciones personalizadas y eficientes. No solo brindamos servicios: construimos relaciones basadas en la confianza, la seguridad y la innovación constante.</p>
                        <p>Hoy continuamos creciendo con la misma dedicación que nos inspiró desde el primer día, apostando por la tecnología, la formación de nuestro equipo técnico y un servicio al cliente impecable.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Valores -->
        <section class="ab-values">
            <div class="ab-container">
                <div class="section-head section-head--center">
                    <span class="eyebrow">Lo que nos mueve</span>
                    <h2 class="ab-section-title">Nuestros valores</h2>
                </div>
                <div class="ab-values-grid">
                    <div class="ab-value-card" v-for="valor in valores" :key="valor.titulo">
                        <div class="ab-value-icon"><i :class="valor.icono"></i></div>
                        <div class="ab-value-body">
                            <h3 class="ab-value-title">{{ valor.titulo }}</h3>
                            <p class="ab-value-desc">{{ valor.descripcion }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Equipo Directivo -->
        <section class="ab-team">
            <div class="ab-container">
                <div class="section-head section-head--center">
                    <span class="eyebrow">Quienes lideran</span>
                    <h2 class="ab-section-title">Nuestro equipo directivo</h2>
                </div>
                <div class="ab-team-grid">
                    <div class="ab-team-card" v-for="miembro in equipo" :key="miembro.nombre">
                        <div class="ab-avatar"><span class="ab-initials">{{ miembro.iniciales }}</span></div>
                        <h3 class="ab-team-name">{{ miembro.nombre }}</h3>
                        <p class="ab-team-role">{{ miembro.cargo }}</p>
                        <p class="ab-team-quote">&ldquo;{{ miembro.cita }}&rdquo;</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="ab-cta">
            <div class="ab-cta-glow"></div>
            <div class="ab-container ab-cta-inner">
                <h2 class="ab-cta-title">¿Listo para trabajar con nosotros?</h2>
                <router-link to="/contacto" class="ab-cta-btn">Contáctenos</router-link>
            </div>
        </section>

        <PublicFooter />
    </div>
</template>

<script>
import PublicNavbar from '@/components/PublicNavbar.vue';
import PublicFooter from '@/components/PublicFooter.vue';

export default {
    name: 'About',
    components: { PublicNavbar, PublicFooter },
    data() {
        return {
            valores: [
                { icono: 'fa fa-handshake', titulo: 'Compromiso', descripcion: 'Cumplimos cada promesa con dedicación y responsabilidad' },
                { icono: 'fa fa-award', titulo: 'Calidad', descripcion: 'Estándares superiores en cada servicio que ofrecemos' },
                { icono: 'fa fa-shield-alt', titulo: 'Seguridad', descripcion: 'La protección de personas y equipos es nuestra prioridad' },
                { icono: 'fa fa-lightbulb', titulo: 'Innovación', descripcion: 'Adoptamos tecnología de vanguardia para mejores resultados' },
                { icono: 'fa fa-balance-scale', titulo: 'Responsabilidad', descripcion: 'Actuamos con integridad y transparencia en todo momento' },
                { icono: 'fa fa-clock', titulo: 'Puntualidad', descripcion: 'Respetamos los tiempos acordados sin excepción' },
            ],
            equipo: [
                {
                    nombre: 'José Rubén Florez', cargo: 'Director Administrativo', iniciales: 'JF',
                    cita: 'El éxito de una empresa no solo se mide por sus números, sino por la satisfacción de su equipo y la confianza que inspira en sus clientes. Nuestro enfoque está en construir relaciones sólidas y gestionar con eficiencia para garantizar un crecimiento sostenible.',
                },
                {
                    nombre: 'Jenssy Sayago', cargo: 'Director de Operaciones', iniciales: 'JS',
                    cita: 'La excelencia operativa no es solo una meta, es un proceso constante. Nos aseguramos de que cada proyecto se ejecute con precisión y rapidez, minimizando tiempos de inactividad y superando las expectativas de nuestros clientes en cada detalle.',
                },
                {
                    nombre: 'Antonio Contreras', cargo: 'Director de Ingeniería', iniciales: 'AC',
                    cita: 'La ingeniería es el puente entre la visión y la realidad. Nos comprometemos a innovar constantemente, utilizando la tecnología y el conocimiento para garantizar que cada solución sea segura, eficiente y adaptada a las necesidades específicas de cada cliente.',
                },
            ],
        };
    },
};
</script>

<style scoped>
.about-page {
    font-family: 'Poppins', sans-serif;
    background: #0b1120;
    color: #e8eef7;
}

.ab-container { max-width: 1200px; margin: 0 auto; padding: 0 2rem; }

.eyebrow {
    display: inline-block; font-size: 0.8rem; font-weight: 700; letter-spacing: 0.08em;
    text-transform: uppercase; color: #37c20c; margin-bottom: 0.75rem;
}
.accent {
    background: linear-gradient(120deg, #37c20c, #9bf06f);
    -webkit-background-clip: text; background-clip: text; color: transparent;
}
.section-head--center { text-align: center; max-width: 640px; margin: 0 auto 3rem; }

/* Hero */
.ab-hero { position: relative; padding: 150px 0 5rem; overflow: hidden; }
.ab-hero-bg { position: absolute; inset: 0; z-index: 0; }
.orb { position: absolute; border-radius: 50%; filter: blur(90px); }
.orb--green { width: 480px; height: 480px; top: -140px; left: 50%; transform: translateX(-50%); background: rgba(48, 171, 10, 0.18); }
.grid-texture {
    position: absolute; inset: 0;
    background-image:
        linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
    background-size: 48px 48px;
    mask-image: radial-gradient(circle at 50% 30%, #000 20%, transparent 70%);
    -webkit-mask-image: radial-gradient(circle at 50% 30%, #000 20%, transparent 70%);
}
.ab-hero-inner { position: relative; z-index: 1; text-align: center; }
.ab-hero-title { font-size: 2.9rem; font-weight: 800; margin: 0 0 1rem; color: #f8fafc; line-height: 1.15; }
.ab-hero-subtitle { font-size: 1.15rem; color: #93a3bb; max-width: 620px; margin: 0 auto; line-height: 1.7; }

/* Section title */
.ab-section-title { font-size: 2rem; font-weight: 700; color: #f8fafc; margin: 0 0 1.5rem; }
.section-head--center .ab-section-title { margin-bottom: 0; }

/* Quiénes somos */
.ab-about { padding: 6rem 0; }
.ab-about-grid { display: grid; grid-template-columns: 0.9fr 1.1fr; gap: 3.5rem; align-items: center; }
.ab-about-visual { position: relative; }
.ab-visual-card {
    height: 340px; border: 1px dashed rgba(48, 171, 10, 0.35); border-radius: 18px;
    display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 1rem;
    background: radial-gradient(circle at 50% 40%, rgba(48, 171, 10, 0.08), transparent 70%), rgba(255, 255, 255, 0.02);
}
.ab-visual-text { font-size: 0.9rem; color: #6b7c96; font-weight: 500; }
.ab-float-badge {
    position: absolute; bottom: -22px; right: -10px;
    background: linear-gradient(180deg, #131f36, #0f1830); border: 1px solid rgba(48, 171, 10, 0.3);
    border-radius: 14px; padding: 1rem 1.25rem; text-align: center;
    box-shadow: 0 16px 40px rgba(0, 0, 0, 0.45);
}
.ab-float-num {
    display: block; font-size: 1.8rem; font-weight: 800; line-height: 1;
    background: linear-gradient(120deg, #37c20c, #9bf06f);
    -webkit-background-clip: text; background-clip: text; color: transparent;
}
.ab-float-label { display: block; font-size: 0.72rem; color: #93a3bb; margin-top: 0.25rem; line-height: 1.3; }
.ab-about-content p { color: #9aa9c0; line-height: 1.8; margin: 0 0 1.1rem; font-size: 0.95rem; }
.ab-about-content p:last-child { margin-bottom: 0; }

/* Valores */
.ab-values { padding: 5rem 0; background: #0d1424; border-top: 1px solid rgba(255, 255, 255, 0.05); border-bottom: 1px solid rgba(255, 255, 255, 0.05); }
.ab-values-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.25rem; }
.ab-value-card {
    background: rgba(255, 255, 255, 0.025); border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 14px; padding: 1.6rem; display: flex; gap: 1.1rem; align-items: flex-start;
    border-left: 3px solid #30ab0a; transition: transform 0.2s, background 0.2s;
}
.ab-value-card:hover { transform: translateY(-3px); background: rgba(255, 255, 255, 0.045); }
.ab-value-icon {
    width: 46px; height: 46px; border-radius: 12px; flex-shrink: 0;
    background: rgba(48, 171, 10, 0.12); border: 1px solid rgba(48, 171, 10, 0.25);
    display: flex; align-items: center; justify-content: center;
}
.ab-value-icon i { font-size: 1.15rem; color: #4cc40f; }
.ab-value-title { font-size: 1rem; font-weight: 700; margin: 0 0 0.35rem; color: #f1f5f9; }
.ab-value-desc { font-size: 0.9rem; color: #93a3bb; line-height: 1.6; margin: 0; }

/* Equipo */
.ab-team { padding: 6rem 0; }
.ab-team-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; }
.ab-team-card {
    background: rgba(255, 255, 255, 0.025); border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 16px; padding: 2rem; text-align: center; transition: transform 0.25s, box-shadow 0.25s;
}
.ab-team-card:hover { transform: translateY(-4px); box-shadow: 0 16px 40px rgba(0, 0, 0, 0.4); }
.ab-avatar {
    width: 78px; height: 78px; border-radius: 50%; margin: 0 auto 1.25rem;
    background: linear-gradient(135deg, #30ab0a, #248007);
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 8px 22px rgba(48, 171, 10, 0.35);
}
.ab-initials { color: #fff; font-size: 1.5rem; font-weight: 700; letter-spacing: 1px; }
.ab-team-name { font-size: 1.1rem; font-weight: 700; margin: 0 0 0.25rem; color: #f1f5f9; }
.ab-team-role { font-size: 0.85rem; color: #4cc40f; font-weight: 600; margin: 0 0 1.25rem; }
.ab-team-quote { font-size: 0.9rem; color: #93a3bb; line-height: 1.7; font-style: italic; margin: 0; }

/* CTA */
.ab-cta { position: relative; padding: 5rem 0; text-align: center; overflow: hidden; }
.ab-cta-glow { position: absolute; width: 600px; height: 280px; left: 50%; top: 50%; transform: translate(-50%, -50%); background: rgba(48, 171, 10, 0.16); filter: blur(100px); }
.ab-cta-inner { position: relative; z-index: 1; }
.ab-cta-title { font-size: 2rem; font-weight: 700; margin: 0 0 1.75rem; color: #f8fafc; }
.ab-cta-btn {
    display: inline-block; background: linear-gradient(135deg, #30ab0a, #248007); color: #fff;
    font-weight: 700; font-size: 1rem; padding: 0.9rem 2.5rem; border-radius: 10px; text-decoration: none;
    box-shadow: 0 12px 30px rgba(48, 171, 10, 0.35); transition: transform 0.2s, box-shadow 0.2s;
}
.ab-cta-btn:hover { transform: translateY(-2px); box-shadow: 0 16px 38px rgba(48, 171, 10, 0.5); }

/* Responsive */
@media (max-width: 992px) {
    .ab-values-grid { grid-template-columns: repeat(2, 1fr); }
    .ab-team-grid { grid-template-columns: repeat(2, 1fr); }
    .ab-team-grid .ab-team-card:last-child { grid-column: 1 / -1; max-width: 420px; margin: 0 auto; }
    .ab-about-grid { grid-template-columns: 1fr; gap: 3.5rem; }
    .ab-float-badge { right: 1.5rem; }
}
@media (max-width: 768px) {
    .ab-hero-title { font-size: 2.1rem; }
    .ab-hero-subtitle { font-size: 1rem; }
    .ab-section-title { font-size: 1.6rem; }
    .ab-team-grid { grid-template-columns: 1fr; }
    .ab-team-grid .ab-team-card:last-child { max-width: none; }
    .ab-cta-title { font-size: 1.5rem; }
    .ab-about, .ab-values, .ab-team { padding: 4rem 0; }
}
@media (max-width: 576px) {
    .ab-container { padding: 0 1.25rem; }
    .ab-hero { padding: 120px 0 3.5rem; }
    .ab-hero-title { font-size: 1.75rem; }
    .ab-values-grid { grid-template-columns: 1fr; }
}
</style>
