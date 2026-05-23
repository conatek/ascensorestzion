<template>
    <div class="about-page">
        <PublicNavbar />

        <!-- Hero Banner -->
        <section class="ab-hero">
            <div class="ab-container">
                <h1 class="ab-hero-title">Sobre Nosotros</h1>
                <p class="ab-hero-subtitle">Conoce nuestra historia, misi&oacute;n y el equipo que hace posible cada proyecto</p>
            </div>
        </section>

        <!-- Quienes Somos -->
        <section class="ab-about">
            <div class="ab-container">
                <div class="ab-about-grid">
                    <div class="ab-about-image">
                        <div class="ab-placeholder">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                                <circle cx="12" cy="13" r="4"/>
                            </svg>
                            <span class="ab-placeholder-text">Fotograf&iacute;a del equipo</span>
                        </div>
                    </div>
                    <div class="ab-about-content">
                        <h2 class="ab-section-title">Acerca de Atzion</h2>
                        <p>En Ascensores Tzion, nuestra historia comienza hace cuatro a&ntilde;os con un prop&oacute;sito claro: convertirnos en aliados estrat&eacute;gicos en la seguridad y el rendimiento de los sistemas de transporte vertical. Desde nuestros inicios, nos especializamos en el mantenimiento de ascensores y escaleras el&eacute;ctricas, pero r&aacute;pidamente ampliamos nuestra oferta para incluir la instalaci&oacute;n de equipos nuevos, modernizaci&oacute;n y reparaciones especializadas.</p>
                        <p>Nuestro compromiso no se detiene en un solo servicio, gestionamos toda la cadena de valor. Nos encargamos de la importaci&oacute;n de equipos y repuestos, asegur&aacute;ndonos de trabajar &uacute;nicamente con proveedores confiables y productos de la m&aacute;s alta calidad. Una vez los equipos llegan al pa&iacute;s, supervisamos todo el proceso de instalaci&oacute;n, hasta la puesta en marcha, garantizando que cada ascensor o escalera el&eacute;ctrica opere de manera &oacute;ptima y segura.</p>
                        <p>Nuestra pasi&oacute;n por la excelencia en cada detalle nos ha permitido consolidarnos como un referente en el sector. Trabajamos de la mano con nuestros clientes, comprendiendo sus necesidades para ofrecer soluciones personalizadas y eficientes. En Ascensores Tzion, no solo brindamos servicios, sino que construimos relaciones basadas en la confianza, la seguridad y la innovaci&oacute;n constante.</p>
                        <p>Hoy, despu&eacute;s de m&aacute;s de 4 a&ntilde;os en el mercado, continuamos nuestro crecimiento con la misma dedicaci&oacute;n y compromiso que nos inspir&oacute; desde el primer d&iacute;a, apostando siempre por la tecnolog&iacute;a, la formaci&oacute;n de nuestro equipo t&eacute;cnico y un servicio al cliente impecable. Porque sabemos que cada equipo que mantenemos o instalamos es una garant&iacute;a de seguridad y tranquilidad para nuestros clientes.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Valores -->
        <section class="ab-values">
            <div class="ab-container">
                <h2 class="ab-section-title ab-centered">Nuestros Valores</h2>
                <div class="ab-values-grid">
                    <div class="ab-value-card" v-for="valor in valores" :key="valor.titulo">
                        <div class="ab-value-icon">
                            <i :class="valor.icono"></i>
                        </div>
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
                <h2 class="ab-section-title ab-centered">Nuestro Equipo Directivo</h2>
                <div class="ab-team-grid">
                    <div class="ab-team-card" v-for="miembro in equipo" :key="miembro.nombre">
                        <div class="ab-avatar">
                            <span class="ab-initials">{{ miembro.iniciales }}</span>
                        </div>
                        <h3 class="ab-team-name">{{ miembro.nombre }}</h3>
                        <p class="ab-team-role">{{ miembro.cargo }}</p>
                        <p class="ab-team-quote">&ldquo;{{ miembro.cita }}&rdquo;</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="ab-cta">
            <div class="ab-container">
                <h2 class="ab-cta-title">&iquest;Listo para trabajar con nosotros?</h2>
                <router-link to="/contacto" class="ab-cta-btn">Cont&aacute;ctenos</router-link>
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
                {
                    icono: 'fa fa-handshake',
                    titulo: 'Compromiso',
                    descripcion: 'Cumplimos cada promesa con dedicaci\u00f3n y responsabilidad',
                },
                {
                    icono: 'fa fa-award',
                    titulo: 'Calidad',
                    descripcion: 'Est\u00e1ndares superiores en cada servicio que ofrecemos',
                },
                {
                    icono: 'fa fa-shield-alt',
                    titulo: 'Seguridad',
                    descripcion: 'La protecci\u00f3n de personas y equipos es nuestra prioridad',
                },
                {
                    icono: 'fa fa-lightbulb',
                    titulo: 'Innovaci\u00f3n',
                    descripcion: 'Adoptamos tecnolog\u00eda de vanguardia para mejores resultados',
                },
                {
                    icono: 'fa fa-balance-scale',
                    titulo: 'Responsabilidad',
                    descripcion: 'Actuamos con integridad y transparencia en todo momento',
                },
                {
                    icono: 'fa fa-clock',
                    titulo: 'Puntualidad',
                    descripcion: 'Respetamos los tiempos acordados sin excepci\u00f3n',
                },
            ],
            equipo: [
                {
                    nombre: 'Jos\u00e9 Rub\u00e9n Florez',
                    cargo: 'Director Administrativo',
                    iniciales: 'JF',
                    cita: 'El \u00e9xito de una empresa no solo se mide por sus n\u00fameros, sino por la satisfacci\u00f3n de su equipo y la confianza que inspira en sus clientes. Nuestro enfoque est\u00e1 en construir relaciones s\u00f3lidas y gestionar con eficiencia para garantizar un crecimiento sostenible.',
                },
                {
                    nombre: 'Jenssy Sayago',
                    cargo: 'Director de Operaciones',
                    iniciales: 'JS',
                    cita: 'La excelencia operativa no es solo una meta, es un proceso constante. Nos aseguramos de que cada proyecto se ejecute con precisi\u00f3n y rapidez, minimizando tiempos de inactividad y superando las expectativas de nuestros clientes en cada detalle.',
                },
                {
                    nombre: 'Antonio Contreras',
                    cargo: 'Director de Ingenier\u00eda',
                    iniciales: 'AC',
                    cita: 'La ingenier\u00eda es el puente entre la visi\u00f3n y la realidad. Nos comprometemos a innovar constantemente, utilizando la tecnolog\u00eda y el conocimiento para garantizar que cada soluci\u00f3n sea segura, eficiente y adaptada a las necesidades espec\u00edficas de cada cliente.',
                },
            ],
        };
    },
};
</script>

<style scoped>
.about-page {
    font-family: 'Poppins', sans-serif;
    color: #1e293b;
}

/* Container */
.ab-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 2rem;
}

/* Hero */
.ab-hero {
    background: #1e293b;
    color: #fff;
    padding: 120px 0 60px;
    text-align: center;
}
.ab-hero-title {
    font-size: 2.75rem;
    font-weight: 700;
    margin: 0 0 1rem;
}
.ab-hero-subtitle {
    font-size: 1.15rem;
    color: #cbd5e1;
    max-width: 600px;
    margin: 0 auto;
    line-height: 1.7;
}

/* Section Title */
.ab-section-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 2rem;
    position: relative;
}
.ab-section-title.ab-centered {
    text-align: center;
}
.ab-section-title::after {
    content: '';
    display: block;
    width: 60px;
    height: 3px;
    background: #30ab0a;
    margin-top: 0.75rem;
}
.ab-section-title.ab-centered::after {
    margin-left: auto;
    margin-right: auto;
}

/* Quienes Somos */
.ab-about {
    padding: 5rem 0;
    background: #fff;
}
.ab-about-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 3rem;
    align-items: start;
}
.ab-about-content p {
    color: #475569;
    line-height: 1.8;
    margin: 0 0 1.25rem;
    font-size: 0.95rem;
}
.ab-about-content p:last-child {
    margin-bottom: 0;
}

/* Placeholder Image */
.ab-placeholder {
    width: 100%;
    height: 300px;
    border: 2px dashed #e2e8f0;
    border-radius: 12px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    background: #f8fafc;
}
.ab-placeholder-text {
    font-size: 0.9rem;
    color: #94a3b8;
    font-weight: 500;
}

/* Valores */
.ab-values {
    padding: 5rem 0;
    background: #f8fafc;
}
.ab-values-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem;
}
.ab-value-card {
    background: #fff;
    border-radius: 10px;
    padding: 1.75rem;
    box-shadow: 0 1px 8px rgba(0, 0, 0, 0.06);
    border-left: 3px solid #30ab0a;
    display: flex;
    gap: 1.25rem;
    align-items: flex-start;
    transition: box-shadow 0.2s;
}
.ab-value-card:hover {
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
}
.ab-value-icon {
    width: 44px;
    height: 44px;
    background: #f0faf0;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.ab-value-icon i {
    font-size: 1.15rem;
    color: #30ab0a;
}
.ab-value-title {
    font-size: 1rem;
    font-weight: 700;
    margin: 0 0 0.35rem;
    color: #1e293b;
}
.ab-value-desc {
    font-size: 0.9rem;
    color: #606060;
    line-height: 1.6;
    margin: 0;
}

/* Equipo */
.ab-team {
    padding: 5rem 0;
    background: #fff;
}
.ab-team-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 2rem;
}
.ab-team-card {
    background: #fff;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.07);
    text-align: center;
    transition: box-shadow 0.2s;
}
.ab-team-card:hover {
    box-shadow: 0 6px 24px rgba(0, 0, 0, 0.12);
}
.ab-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: #30ab0a;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.25rem;
}
.ab-initials {
    color: #fff;
    font-size: 1.5rem;
    font-weight: 700;
    letter-spacing: 1px;
}
.ab-team-name {
    font-size: 1.1rem;
    font-weight: 700;
    margin: 0 0 0.25rem;
    color: #1e293b;
}
.ab-team-role {
    font-size: 0.85rem;
    color: #30ab0a;
    font-weight: 600;
    margin: 0 0 1.25rem;
}
.ab-team-quote {
    font-size: 0.9rem;
    color: #606060;
    line-height: 1.7;
    font-style: italic;
    margin: 0;
}

/* CTA */
.ab-cta {
    background: #30ab0a;
    color: #fff;
    padding: 4rem 0;
    text-align: center;
}
.ab-cta-title {
    font-size: 2rem;
    font-weight: 700;
    margin: 0 0 1.5rem;
}
.ab-cta-btn {
    display: inline-block;
    background: #fff;
    color: #30ab0a;
    font-weight: 700;
    font-size: 1rem;
    padding: 0.85rem 2.5rem;
    border-radius: 8px;
    text-decoration: none;
    transition: all 0.2s;
}
.ab-cta-btn:hover {
    background: #f0faf0;
    transform: translateY(-2px);
}

/* Responsive 992px */
@media (max-width: 992px) {
    .ab-values-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    .ab-team-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    .ab-team-grid .ab-team-card:last-child {
        grid-column: 1 / -1;
        max-width: 400px;
        margin: 0 auto;
    }
}

/* Responsive 768px */
@media (max-width: 768px) {
    .ab-hero-title {
        font-size: 2rem;
    }
    .ab-hero-subtitle {
        font-size: 1rem;
    }
    .ab-about-grid {
        grid-template-columns: 1fr;
    }
    .ab-placeholder {
        height: 250px;
    }
    .ab-section-title {
        font-size: 1.5rem;
    }
    .ab-team-grid {
        grid-template-columns: 1fr;
    }
    .ab-team-grid .ab-team-card:last-child {
        max-width: none;
    }
    .ab-cta-title {
        font-size: 1.5rem;
    }
}

/* Responsive 576px */
@media (max-width: 576px) {
    .ab-container {
        padding: 0 1.25rem;
    }
    .ab-hero {
        padding: 100px 0 40px;
    }
    .ab-hero-title {
        font-size: 1.65rem;
    }
    .ab-about,
    .ab-values,
    .ab-team {
        padding: 3.5rem 0;
    }
    .ab-values-grid {
        grid-template-columns: 1fr;
    }
    .ab-cta {
        padding: 3rem 0;
    }
    .ab-cta-title {
        font-size: 1.3rem;
    }
}
</style>
