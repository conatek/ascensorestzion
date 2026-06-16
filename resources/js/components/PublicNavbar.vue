<template>
    <header class="pn-header" :class="{ 'pn-scrolled': scrolled }">
        <div class="pn-container">
            <router-link to="/inicio" class="pn-logo">
                <img :src="'/images/logo/logo-atzion.svg'" alt="Ascensores Tzion" class="pn-logo-img" />
            </router-link>

            <button class="pn-mobile-btn" @click="menuOpen = !menuOpen" aria-label="Menú">
                <span class="pn-bar" :class="{ open: menuOpen }"></span>
                <span class="pn-bar" :class="{ open: menuOpen }"></span>
                <span class="pn-bar" :class="{ open: menuOpen }"></span>
            </button>

            <nav class="pn-nav" :class="{ 'pn-nav-open': menuOpen }">
                <router-link to="/inicio" class="pn-link" :class="{ active: $route.path === '/inicio' }" @click="menuOpen = false">Inicio</router-link>
                <router-link to="/nosotros" class="pn-link" :class="{ active: $route.path === '/nosotros' }" @click="menuOpen = false">Nosotros</router-link>
                <router-link to="/servicios" class="pn-link" :class="{ active: $route.path === '/servicios' }" @click="menuOpen = false">Servicios</router-link>
                <router-link to="/contacto" class="pn-link" :class="{ active: $route.path === '/contacto' }" @click="menuOpen = false">Contacto</router-link>
                <router-link to="/login" class="pn-cta" @click="menuOpen = false">Portal Clientes</router-link>
            </nav>
        </div>
    </header>
</template>

<script>
export default {
    name: 'PublicNavbar',
    data() {
        return { menuOpen: false, scrolled: false };
    },
    mounted() {
        window.addEventListener('scroll', this.onScroll);
    },
    beforeUnmount() {
        window.removeEventListener('scroll', this.onScroll);
    },
    methods: {
        onScroll() {
            this.scrolled = window.scrollY > 20;
        },
    },
};
</script>

<style scoped>
.pn-header {
    position: fixed; top: 0; left: 0; right: 0; z-index: 200;
    background: rgba(11, 17, 32, 0.55); backdrop-filter: blur(14px);
    border-bottom: 1px solid rgba(255, 255, 255, 0.06);
    transition: background 0.3s, box-shadow 0.3s, border-color 0.3s;
}
.pn-scrolled {
    background: rgba(11, 17, 32, 0.9);
    box-shadow: 0 6px 28px rgba(0, 0, 0, 0.45);
    border-bottom-color: rgba(255, 255, 255, 0.09);
}

.pn-container {
    max-width: 1200px; margin: 0 auto; padding: 0 2rem;
    display: flex; align-items: center; justify-content: space-between; height: 76px;
}

.pn-logo { display: flex; align-items: center; text-decoration: none; }
.pn-logo-img { height: 56px; width: auto; }

.pn-nav {
    display: flex; align-items: center; gap: 0.25rem;
}

.pn-link {
    padding: 0.5rem 1rem; color: #c3cfe0; text-decoration: none;
    font-size: 0.95rem; font-weight: 500; border-radius: 8px;
    transition: all 0.2s; position: relative;
}
.pn-link:hover { color: #4cc40f; background: rgba(48, 171, 10, 0.1); }
.pn-link.active { color: #4cc40f; font-weight: 600; }
.pn-link.active::after {
    content: ''; position: absolute; bottom: 2px; left: 1rem; right: 1rem;
    height: 2px; background: #30ab0a; border-radius: 1px;
}

.pn-cta {
    margin-left: 0.75rem; padding: 0.6rem 1.5rem;
    background: linear-gradient(135deg, #30ab0a, #248007); color: #fff;
    font-weight: 600; font-size: 0.9rem;
    border-radius: 8px; text-decoration: none; transition: all 0.2s;
    box-shadow: 0 6px 18px rgba(48, 171, 10, 0.25);
}
.pn-cta:hover { transform: translateY(-1px); box-shadow: 0 8px 22px rgba(48, 171, 10, 0.38); }

.pn-mobile-btn {
    display: none; flex-direction: column; gap: 5px; padding: 8px;
    background: none; border: none; cursor: pointer;
}
.pn-bar {
    width: 22px; height: 2px; background: #c3cfe0; border-radius: 2px;
    transition: all 0.3s;
}
.pn-bar.open:nth-child(1) { transform: translateY(7px) rotate(45deg); }
.pn-bar.open:nth-child(2) { opacity: 0; }
.pn-bar.open:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }

@media (max-width: 768px) {
    .pn-mobile-btn { display: flex; }
    .pn-nav {
        display: none; position: absolute; top: 76px; left: 0; right: 0;
        background: rgba(13, 20, 36, 0.97); backdrop-filter: blur(14px);
        flex-direction: column; padding: 1rem 2rem 1.5rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        border-bottom: 1px solid rgba(255, 255, 255, 0.08); gap: 0;
    }
    .pn-nav-open { display: flex; }
    .pn-link { padding: 0.75rem 1rem; width: 100%; }
    .pn-cta { margin: 0.5rem 0 0; text-align: center; }
}
</style>
