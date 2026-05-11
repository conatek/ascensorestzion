<template>
  <div v-if="loading" class="card-loading">
    <div class="spinner"></div>
  </div>

  <div v-else-if="notFound" class="card-not-found">
    <div class="nf-icon">🏢</div>
    <h2>Empresa no encontrada</h2>
    <p>Esta página no existe o no está disponible.</p>
  </div>

  <div v-else class="cp-root" :style="cssVars">

    <!-- Hero de empresa -->
    <div class="cp-hero" :style="{ background: `linear-gradient(160deg, var(--color-primary) 0%, var(--color-secondary) 100%)` }">
      <div class="cp-hero__logo" v-if="company.logo_path">
        <img :src="company.logo_path" :alt="company.name" />
      </div>
      <div v-else class="cp-hero__logo-placeholder">🏢</div>

      <h1>{{ company.name }}</h1>
      <p class="cp-hero__desc" v-if="company.description">{{ company.description }}</p>

      <!-- Contacto rápido -->
      <div class="cp-hero__contact" v-if="company.phone || company.email || company.website">
        <a v-if="company.phone"   :href="`tel:${company.phone}`"     class="cp-hero__badge">📞 {{ company.phone }}</a>
        <a v-if="company.email"   :href="`mailto:${company.email}`"  class="cp-hero__badge">✉️ {{ company.email }}</a>
        <a v-if="company.website" :href="company.website" target="_blank" class="cp-hero__badge">🌐 Sitio web</a>
      </div>
    </div>

    <!-- Tarjetas del equipo -->
    <div class="cp-section" v-if="company.cards?.length">
      <h2 class="cp-section__title">Nuestro equipo</h2>
      <div class="cp-cards">
        <router-link
          v-for="card in company.cards"
          :key="card.id"
          :to="`/${company.slug}/${card.slug}`"
          class="cp-card"
        >
          <div class="cp-card__photo">
            <img v-if="card.photo_path" :src="card.photo_path" :alt="card.first_name" />
            <div v-else class="cp-card__initials" :style="{ background: 'var(--color-primary)' }">
              {{ card.first_name?.[0] }}{{ card.last_name?.[0] }}
            </div>
          </div>
          <div class="cp-card__info">
            <strong>{{ card.first_name }} {{ card.last_name }}</strong>
            <span v-if="card.job_title">{{ card.job_title }}</span>
          </div>
          <div class="cp-card__arrow" :style="{ color: 'var(--color-primary)' }">›</div>
        </router-link>
      </div>
    </div>

    <!-- Servicios -->
    <div class="cp-section" v-if="design.show_services && company.services?.length">
      <h2 class="cp-section__title">Servicios</h2>
      <div class="cp-grid">
        <div class="cp-grid__item" v-for="s in company.services" :key="s.id">
          <div class="cp-grid__img" v-if="s.image_path"><img :src="s.image_path" :alt="s.name" /></div>
          <div class="cp-grid__img cp-grid__img--ph" v-else :style="{ background: 'var(--color-primary)' }">🛠️</div>
          <div class="cp-grid__body">
            <h3>{{ s.name }}</h3>
            <p v-if="s.description">{{ s.description }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Productos -->
    <div class="cp-section" v-if="design.show_products && company.products?.length">
      <h2 class="cp-section__title">Productos</h2>
      <div class="cp-grid">
        <div class="cp-grid__item" v-for="p in company.products" :key="p.id">
          <div class="cp-grid__img" v-if="p.image_path"><img :src="p.image_path" :alt="p.name" /></div>
          <div class="cp-grid__img cp-grid__img--ph" v-else :style="{ background: 'var(--color-secondary)' }">📦</div>
          <div class="cp-grid__body">
            <h3>{{ p.name }}</h3>
            <div class="cp-price">
              <span :style="{ color: 'var(--color-primary)', fontWeight: 700 }">${{ finalPrice(p) }}</span>
              <span v-if="p.discount > 0" class="cp-price__old">${{ Number(p.price).toFixed(2) }}</span>
              <span v-if="p.discount > 0" class="cp-price__badge" :style="{ background: 'var(--color-accent)' }">-{{ p.discount }}%</span>
            </div>
            <p v-if="p.description">{{ p.description }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Redes sociales -->
    <div class="cp-section cp-social" v-if="hasSocial">
      <h2 class="cp-section__title">Encuéntranos en</h2>
      <div class="cp-social__links">
        <a v-if="company.facebook"  :href="company.facebook"  target="_blank" class="cp-social__link" style="background:#1877F2">Facebook</a>
        <a v-if="company.instagram" :href="company.instagram" target="_blank" class="cp-social__link" style="background:linear-gradient(45deg,#f09433,#e6683c,#dc2743,#cc2366,#bc1888)">Instagram</a>
        <a v-if="company.twitter"   :href="company.twitter"   target="_blank" class="cp-social__link" style="background:#1DA1F2">Twitter</a>
        <a v-if="company.youtube"   :href="company.youtube"   target="_blank" class="cp-social__link" style="background:#FF0000">YouTube</a>
      </div>
    </div>

    <footer class="cp-footer">
      <p>Tarjetas digitales · Powered by NexosCard</p>
    </footer>
  </div>
</template>

<script>
import publicCardService from '@/services/publicCardService.js';

const defaultDesign = {
  primary_color: '#3B82F6', secondary_color: '#1E40AF',
  background_color: '#F9FAFB', text_color: '#111827', accent_color: '#F59E0B',
  font_family: 'Inter', border_radius: 8,
  show_services: true, show_products: true,
};

export default {
  name: 'CompanyPublic',

  data() {
    return { loading: true, notFound: false, company: {} };
  },

  computed: {
    design() {
      return { ...defaultDesign, ...(this.company.design_settings ?? {}) };
    },
    cssVars() {
      const d = this.design;
      return {
        '--color-primary':    d.primary_color,
        '--color-secondary':  d.secondary_color,
        '--color-background': d.background_color,
        '--color-text':       d.text_color,
        '--color-accent':     d.accent_color,
        '--font-family':      d.font_family,
        '--border-radius':    d.border_radius + 'px',
      };
    },
    hasSocial() {
      return this.company.facebook || this.company.instagram ||
             this.company.twitter  || this.company.youtube;
    },
  },

  async created() {
    try {
      const { data } = await publicCardService.company(this.$route.params.companySlug);
      this.company = data;
    } catch {
      this.notFound = true;
    } finally {
      this.loading = false;
    }
  },

  methods: {
    finalPrice(p) {
      return (p.price * (1 - p.discount / 100)).toFixed(2);
    },
  },
};
</script>

<style scoped>
.cp-root {
  background-color: var(--color-background);
  color: var(--color-text);
  font-family: var(--font-family), system-ui, sans-serif;
  min-height: 100vh;
  max-width: 480px;
  margin: 0 auto;
}

.cp-hero {
  padding: 36px 24px 28px;
  text-align: center;
  color: #fff;
}
.cp-hero__logo img { height: 60px; object-fit: contain; filter: brightness(0) invert(1); opacity: .85; margin-bottom: 12px; }
.cp-hero__logo-placeholder { font-size: 3rem; margin-bottom: 8px; }
.cp-hero h1 { font-size: 1.7rem; font-weight: 800; margin: 0 0 8px; }
.cp-hero__desc { font-size: .9rem; opacity: .85; margin: 0 0 16px; line-height: 1.5; }
.cp-hero__contact { display: flex; flex-wrap: wrap; justify-content: center; gap: 8px; }
.cp-hero__badge {
  background: rgba(255,255,255,.2);
  padding: 6px 14px;
  border-radius: 99px;
  font-size: .8rem;
  color: #fff;
  text-decoration: none;
}

.cp-section { padding: 20px 20px 0; }
.cp-section__title {
  font-size: .7rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .1em;
  color: var(--color-primary);
  margin: 0 0 14px;
}

/* Listado de tarjetas */
.cp-cards { display: flex; flex-direction: column; gap: 10px; }
.cp-card {
  display: flex;
  align-items: center;
  gap: 14px;
  background: #fff;
  border-radius: var(--border-radius);
  padding: 12px 16px;
  text-decoration: none;
  color: var(--color-text);
  box-shadow: 0 1px 3px rgba(0,0,0,.06);
  transition: box-shadow .15s;
}
.cp-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,.1); }
.cp-card__photo {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  overflow: hidden;
  flex-shrink: 0;
}
.cp-card__photo img { width: 100%; height: 100%; object-fit: cover; }
.cp-card__initials {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  color: #fff;
  font-size: 1rem;
}
.cp-card__info { flex: 1; display: flex; flex-direction: column; }
.cp-card__info strong { font-size: .95rem; }
.cp-card__info span { font-size: .8rem; color: #777; }
.cp-card__arrow { font-size: 1.5rem; font-weight: 300; }

/* Grid servicios / productos */
.cp-grid { display: flex; flex-direction: column; gap: 10px; }
.cp-grid__item { display: flex; gap: 12px; background: #fff; border-radius: var(--border-radius); overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.06); }
.cp-grid__img { width: 76px; min-width: 76px; height: 76px; overflow: hidden; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; }
.cp-grid__img img { width: 100%; height: 100%; object-fit: cover; }
.cp-grid__img--ph { border-radius: 0; }
.cp-grid__body { padding: 10px 14px 10px 0; flex: 1; }
.cp-grid__body h3 { font-size: .88rem; font-weight: 600; margin: 0 0 4px; }
.cp-grid__body p { font-size: .8rem; color: #666; margin: 0; }
.cp-price { display: flex; align-items: center; gap: 6px; margin: 4px 0; }
.cp-price__old { text-decoration: line-through; font-size: .78rem; color: #999; }
.cp-price__badge { font-size: .68rem; font-weight: 700; padding: 2px 5px; border-radius: 99px; color: #111; }

/* Redes */
.cp-social__links { display: flex; flex-wrap: wrap; gap: 8px; }
.cp-social__link { padding: 8px 16px; border-radius: 99px; font-size: .8rem; font-weight: 600; text-decoration: none; color: #fff; }

.cp-footer { text-align: center; padding: 28px 20px 40px; font-size: .75rem; color: #aaa; }

/* Loading / 404 */
.card-loading, .card-not-found {
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  min-height: 100vh; gap: 12px; text-align: center;
  font-family: system-ui, sans-serif; color: #555; padding: 24px;
}
.spinner { width: 40px; height: 40px; border: 3px solid #e5e7eb; border-top-color: #3B82F6; border-radius: 50%; animation: spin .7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
.nf-icon { font-size: 3rem; }
.card-not-found h2 { margin: 0; font-size: 1.3rem; }
.card-not-found p  { margin: 0; font-size: .9rem; }
</style>
