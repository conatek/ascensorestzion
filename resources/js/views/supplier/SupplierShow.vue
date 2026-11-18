<template>
    <div>
        <div v-if="loading" class="loading-state">
            <div class="spinner-border text-primary"></div>
            <p class="text-muted mt-3">Cargando proveedor...</p>
        </div>

        <template v-else-if="supplier">
            <!-- Cabecera -->
            <div class="app-page-title">
                <div class="page-title-wrapper">
                    <div class="page-title-heading">
                        <div class="page-title-icon">
                            <i class="fa fa-truck icon-gradient bg-mean-fruit"></i>
                        </div>
                        <div>
                            {{ supplier.trade_name || supplier.business_name }}
                            <span v-if="supplier.is_example" class="badge-example">Ejemplo</span>
                            <span v-if="!supplier.active" class="badge-inactive">Inactivo</span>
                            <div class="page-title-subheading text-muted">
                                {{ typeLabel(supplier.supplier_type) }}
                                <template v-if="supplier.trade_name"> · {{ supplier.business_name }}</template>
                                <template v-if="supplier.nit"> · NIT {{ supplier.nit }}</template>
                            </div>
                        </div>
                    </div>
                    <div class="page-title-actions d-flex gap-2">
                        <router-link :to="{ name: 'suppliers.index' }" class="btn-action btn-back">
                            <i class="fa fa-arrow-left me-1"></i> Volver
                        </router-link>
                        <router-link :to="{ name: 'suppliers.edit', params: { id: supplier.id } }" class="btn-action btn-edit">
                            <i class="fa fa-edit me-1"></i> Editar
                        </router-link>
                        <button class="btn-action btn-delete" @click="deleteSupplier">
                            <i class="fa fa-trash me-1"></i> Eliminar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Otro master cambió algo mientras la ficha estaba abierta -->
            <transition name="fade">
                <div v-if="liveNotice" class="live-notice">
                    <i class="fa fa-sync-alt"></i> {{ liveNotice }}
                </div>
            </transition>

            <div v-if="supplier.is_example" class="example-banner">
                <i class="fa fa-lightbulb"></i>
                <div>
                    <strong>Proveedor de ejemplo.</strong> Sus datos son ficticios. Úsalo para conocer el módulo y elimínalo
                    cuando ya no haga falta: sus productos de ejemplo se van con él.
                </div>
            </div>

            <!-- Resumen -->
            <div class="summary-card">
                <div class="summary-grid">
                    <div class="summary-item">
                        <span class="summary-label">Forma de pago</span>
                        <span class="summary-value">
                            {{ supplier.payment_terms || '—' }}
                            <small v-if="supplier.credit_days"> ({{ supplier.credit_days }} días)</small>
                        </span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Cotiza en</span>
                        <span class="summary-value">{{ supplier.default_currency === 'USD' ? 'Dólares (USD)' : 'Pesos (COP)' }}</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Entrega habitual</span>
                        <span class="summary-value">{{ leadTimeText(supplier.default_lead_time_days) }}</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Valoración</span>
                        <span class="summary-value">
                            <span v-if="supplier.rating" class="stars">
                                <i v-for="n in 5" :key="n" class="fa fa-star" :class="{ on: n <= supplier.rating }"></i>
                            </span>
                            <span v-else>—</span>
                        </span>
                    </div>
                    <div v-if="supplier.website" class="summary-item">
                        <span class="summary-label">Sitio web</span>
                        <a :href="websiteHref" target="_blank" rel="noopener" class="summary-value summary-link">{{ supplier.website }}</a>
                    </div>
                </div>

                <div v-if="supplier.categories?.length || supplier.brands?.length" class="summary-tags">
                    <span v-for="c in supplier.categories || []" :key="'c' + c" class="tag tag-category">{{ c }}</span>
                    <span v-for="b in supplier.brands || []" :key="'b' + b" class="tag tag-brand">{{ b }}</span>
                </div>

                <p v-if="supplier.notes" class="summary-notes">{{ supplier.notes }}</p>

                <div class="summary-author">
                    <span v-if="supplier.creator">Creado por {{ supplier.creator.name }}</span>
                    <span v-if="supplier.editor"> · Última edición de {{ supplier.editor.name }}, {{ timeAgo(supplier.updated_at) }}</span>
                </div>
            </div>

            <!-- Pestañas -->
            <div class="tab-nav">
                <button v-for="tab in tabs" :key="tab.key" class="tab-btn" :class="{ active: activeTab === tab.key }" @click="activeTab = tab.key">
                    <i :class="tab.icon" class="me-1"></i>
                    {{ tab.label }}
                    <span class="tab-count">{{ tab.count }}</span>
                </button>
            </div>

            <div class="tab-content-area">
                <!-- ============ SEDES ============ -->
                <div v-if="activeTab === 'sedes'">
                    <div class="tab-toolbar">
                        <p class="tab-hint">Dónde atiende el proveedor. La principal es la que aparece en el directorio.</p>
                        <button class="btn-add" @click="openBranch()"><i class="fa fa-plus me-1"></i> Nueva sede</button>
                    </div>

                    <div v-if="!supplier.branches.length" class="tab-empty">
                        <i class="fa fa-map-marker-alt"></i>
                        <p>Este proveedor aún no tiene sedes registradas.</p>
                    </div>

                    <div v-else class="card-grid">
                        <div v-for="b in supplier.branches" :key="b.id" class="info-card">
                            <div class="info-card-head">
                                <div class="info-card-title">
                                    {{ b.name }}
                                    <span v-if="b.is_main" class="pill pill-main">Principal</span>
                                </div>
                                <div class="card-actions">
                                    <button class="icon-btn" title="Editar sede" @click="openBranch(b)"><i class="fa fa-edit"></i></button>
                                    <button class="icon-btn icon-btn-danger" title="Eliminar sede" @click="deleteBranch(b)"><i class="fa fa-trash"></i></button>
                                </div>
                            </div>
                            <ul class="info-list">
                                <li v-if="b.address || b.city">
                                    <i class="fa fa-map-marker-alt"></i>
                                    <span>
                                        {{ b.address }}<template v-if="b.address && b.city">, </template>{{ b.city }}<template v-if="b.department"> ({{ b.department }})</template>
                                    </span>
                                </li>
                                <li v-if="b.phone"><i class="fa fa-phone"></i><span>{{ b.phone }}</span></li>
                                <li v-if="b.mobile"><i class="fa fa-mobile-alt"></i><span>{{ b.mobile }}</span></li>
                                <li v-if="b.email"><i class="fa fa-envelope"></i><span class="break">{{ b.email }}</span></li>
                                <li v-if="b.business_hours"><i class="fa fa-clock"></i><span>{{ b.business_hours }}</span></li>
                                <li v-if="b.notes"><i class="fa fa-sticky-note"></i><span>{{ b.notes }}</span></li>
                            </ul>
                            <div class="info-card-foot">
                                <ContactActions :phone="b.phone" :mobile="b.mobile" :email="b.email" large />
                                <a v-if="mapLink(b)" :href="mapLink(b)" target="_blank" rel="noopener" class="map-link">
                                    <i class="fa fa-directions"></i> Cómo llegar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============ ASESORES ============ -->
                <div v-if="activeTab === 'asesores'">
                    <div class="tab-toolbar">
                        <p class="tab-hint">Las personas de contacto. El principal es el de los botones rápidos del directorio.</p>
                        <button class="btn-add" @click="openContact()"><i class="fa fa-plus me-1"></i> Nuevo asesor</button>
                    </div>

                    <div v-if="!supplier.contacts.length" class="tab-empty">
                        <i class="fa fa-user-tie"></i>
                        <p>Este proveedor aún no tiene asesores registrados.</p>
                    </div>

                    <div v-else class="card-grid">
                        <div v-for="c in supplier.contacts" :key="c.id" class="info-card">
                            <div class="info-card-head">
                                <div>
                                    <div class="info-card-title">
                                        {{ c.name }}
                                        <span v-if="c.is_primary" class="pill pill-main">Principal</span>
                                    </div>
                                    <div v-if="c.position" class="info-card-sub">{{ c.position }}</div>
                                </div>
                                <div class="card-actions">
                                    <button class="icon-btn" title="Editar asesor" @click="openContact(c)"><i class="fa fa-edit"></i></button>
                                    <button class="icon-btn icon-btn-danger" title="Eliminar asesor" @click="deleteContact(c)"><i class="fa fa-trash"></i></button>
                                </div>
                            </div>
                            <ul class="info-list">
                                <li v-if="c.mobile"><i class="fa fa-mobile-alt"></i><span>{{ c.mobile }}</span></li>
                                <li v-if="c.whatsapp && c.whatsapp !== c.mobile"><i class="fab fa-whatsapp"></i><span>{{ c.whatsapp }}</span></li>
                                <li v-if="c.phone">
                                    <i class="fa fa-phone"></i>
                                    <span>{{ c.phone }}<template v-if="c.extension"> ext. {{ c.extension }}</template></span>
                                </li>
                                <li v-if="c.email"><i class="fa fa-envelope"></i><span class="break">{{ c.email }}</span></li>
                                <li v-if="c.branch"><i class="fa fa-map-marker-alt"></i><span>{{ c.branch.name }}<template v-if="c.branch.city"> · {{ c.branch.city }}</template></span></li>
                                <li v-if="c.notes"><i class="fa fa-sticky-note"></i><span>{{ c.notes }}</span></li>
                            </ul>
                            <div class="info-card-foot">
                                <ContactActions :phone="c.phone" :mobile="c.mobile" :whatsapp-number="c.whatsapp" :email="c.email" large />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============ PRODUCTOS Y PRECIOS ============ -->
                <div v-if="activeTab === 'productos'">
                    <div class="tab-toolbar">
                        <div class="offer-search">
                            <i class="fa fa-search"></i>
                            <input v-model="offerSearch" type="text" placeholder="Filtrar por nombre, código o referencia..." />
                        </div>
                        <button class="btn-add" @click="openOffer()"><i class="fa fa-plus me-1"></i> Añadir producto</button>
                    </div>

                    <div v-if="staleCount" class="stale-note">
                        <i class="fa fa-exclamation-triangle"></i>
                        {{ staleCount === 1 ? '1 precio tiene' : `${staleCount} precios tienen` }} más de {{ staleDays }} días.
                        Conviene confirmarlos con el asesor antes de cotizar.
                    </div>

                    <div v-if="!supplier.items.length" class="tab-empty">
                        <i class="fa fa-box-open"></i>
                        <p>Aún no hay productos ni precios de este proveedor.</p>
                    </div>

                    <div v-else-if="!filteredOffers.length" class="tab-empty">
                        <i class="fa fa-search"></i>
                        <p>Ningún producto coincide con «{{ offerSearch }}».</p>
                    </div>

                    <div v-else class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Ref. proveedor</th>
                                    <th class="text-end">Precio sin IVA</th>
                                    <th class="text-center">Entrega</th>
                                    <th>Fecha del precio</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="o in filteredOffers" :key="o.id">
                                    <td data-label="Producto">
                                        <div class="offer-product">
                                            <div class="offer-name">
                                                {{ o.supply_item?.name }}
                                                <i v-if="o.is_preferred" class="fa fa-star preferred" title="Proveedor preferido para este producto"></i>
                                            </div>
                                            <div class="offer-meta">
                                                {{ o.supply_item?.code }} · {{ itemTypeLabel(o.supply_item?.item_type) }}
                                                <template v-if="o.supply_item?.manufacturer_ref"> · Ref. {{ o.supply_item.manufacturer_ref }}</template>
                                            </div>
                                            <div v-if="o.notes" class="offer-notes">{{ o.notes }}</div>
                                        </div>
                                    </td>
                                    <td data-label="Ref. proveedor">{{ o.supplier_ref || '—' }}</td>
                                    <td class="text-end" data-label="Precio sin IVA">
                                        <span class="price">{{ money(o.price, o.currency) }}</span>
                                        <span class="price-unit">/ {{ o.supply_item?.unit || 'und' }}</span>
                                    </td>
                                    <td class="text-center" data-label="Entrega">{{ leadTimeText(o.lead_time_days ?? supplier.default_lead_time_days) }}</td>
                                    <td data-label="Fecha del precio">
                                        <span class="price-date" :class="{ stale: o.is_stale }">
                                            <i v-if="o.is_stale" class="fa fa-exclamation-triangle"></i>
                                            {{ formatDay(o.price_date) }}
                                        </span>
                                        <div class="price-age">{{ ageText(o.price_date) }}</div>
                                    </td>
                                    <td class="text-center cell-actions" data-label="Acciones">
                                        <div class="table-actions">
                                            <button class="icon-btn" title="Historial de precios" @click="openHistory(o)"><i class="fa fa-history"></i></button>
                                            <button class="icon-btn" title="Actualizar precio" @click="openOffer(o)"><i class="fa fa-edit"></i></button>
                                            <button class="icon-btn icon-btn-danger" title="Quitar producto" @click="deleteOffer(o)"><i class="fa fa-trash"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </template>

        <!-- Modales -->
        <BranchModal v-if="modal === 'branch'" :supplier-id="supplier.id" :branch="editing"
                     @close="closeModal" @saved="onSaved('Sede guardada')" @stale="onStale" />
        <ContactModal v-if="modal === 'contact'" :supplier-id="supplier.id" :contact="editing" :branches="supplier.branches"
                      @close="closeModal" @saved="onSaved('Asesor guardado')" @stale="onStale" />
        <OfferModal v-if="modal === 'offer'" :supplier-id="supplier.id" :offer="editing"
                    :default-currency="supplier.default_currency" :default-lead-time="supplier.default_lead_time_days"
                    @close="closeModal" @saved="onSaved('Precio guardado')" @stale="onStale" />

        <!-- Historial de precios -->
        <Teleport to="body">
            <div v-if="history" class="dm-overlay sp-overlay" @mousedown.self="history = null">
                <div class="sp-box">
                    <div class="sp-header">
                        <h5>Historial de precios</h5>
                        <button type="button" class="sp-close" title="Cerrar" @click="history = null">&times;</button>
                    </div>
                    <div class="sp-body">
                        <p class="sh-product">{{ history.offer.supply_item?.name }}</p>
                        <div v-if="history.loading" class="sh-empty">Cargando...</div>
                        <div v-else-if="!history.rows.length" class="sh-empty">Sin registros.</div>
                        <table v-else class="sh-table">
                            <thead>
                                <tr><th>Fecha del precio</th><th class="sh-right">Precio</th><th>Origen</th><th>Registró</th></tr>
                            </thead>
                            <tbody>
                                <tr v-for="(row, i) in history.rows" :key="row.id" :class="{ 'sh-current': i === 0 }">
                                    <td>{{ formatDay(row.price_date) }}</td>
                                    <td class="sh-right">
                                        {{ money(row.price, row.currency) }}
                                        <span v-if="variation(i)" class="sh-var" :class="variation(i).up ? 'sh-up' : 'sh-down'">
                                            {{ variation(i).text }}
                                        </span>
                                    </td>
                                    <td>{{ sourceLabel(row.source) }}</td>
                                    <td>{{ row.user?.name || 'Carga inicial' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>

<script>
import supplierService from '@/services/supplierService.js';
import ContactActions from '@/components/supplier/ContactActions.vue';
import BranchModal from '@/components/supplier/BranchModal.vue';
import ContactModal from '@/components/supplier/ContactModal.vue';
import OfferModal from '@/components/supplier/OfferModal.vue';
import { onSupplyChange } from '@/services/mastersChannel.js';
import {
    SUPPLIER_TYPES, ITEM_TYPES, PRICE_SOURCES, typeLabel, formatMoney, formatDay, daysSince, timeAgo,
} from '@/services/supplierOptions.js';

const STALE_DAYS = 90;

export default {
    name: 'SupplierShow',
    components: { ContactActions, BranchModal, ContactModal, OfferModal },

    data() {
        return {
            supplier: null,
            loading: true,
            activeTab: 'sedes',
            modal: null,
            editing: null,
            history: null,
            offerSearch: '',
            staleDays: STALE_DAYS,
            liveNotice: null,
            liveTimer: null,
            stopListening: null,
        };
    },

    computed: {
        tabs() {
            return [
                { key: 'sedes', label: 'Sedes', icon: 'fa fa-map-marker-alt', count: this.supplier.branches.length },
                { key: 'asesores', label: 'Asesores', icon: 'fa fa-user-tie', count: this.supplier.contacts.length },
                { key: 'productos', label: 'Productos y precios', icon: 'fa fa-tags', count: this.supplier.items.length },
            ];
        },
        websiteHref() {
            const url = this.supplier.website || '';
            return /^https?:\/\//i.test(url) ? url : `https://${url}`;
        },
        sortedOffers() {
            return [...this.supplier.items].sort((a, b) =>
                (a.supply_item?.name || '').localeCompare(b.supply_item?.name || '', 'es'));
        },
        filteredOffers() {
            const q = this.offerSearch.toLowerCase().trim();
            if (!q) return this.sortedOffers;
            return this.sortedOffers.filter((o) =>
                [o.supply_item?.name, o.supply_item?.code, o.supply_item?.manufacturer_ref, o.supplier_ref]
                    .some((v) => (v || '').toLowerCase().includes(q)));
        },
        staleCount() {
            return this.supplier.items.filter((o) => o.is_stale).length;
        },
    },

    async created() {
        if (['sedes', 'asesores', 'productos'].includes(this.$route.query.tab)) {
            this.activeTab = this.$route.query.tab;
        }

        await this.load();

        // Otro master tocó este proveedor: se refresca debajo y se avisa.
        this.stopListening = onSupplyChange((event) => {
            if (!this.supplier || Number(event.supplier_id) !== Number(this.supplier.id)) return;

            if (event.entity === 'supplier' && event.action === 'deleted') {
                this.$swal.fire({
                    icon: 'info',
                    title: 'Proveedor eliminado',
                    text: `${event.user_name || 'Otra persona'} acaba de eliminar este proveedor.`,
                }).then(() => this.$router.replace({ name: 'suppliers.index' }));
                return;
            }

            this.load({ silent: true });
            this.flashNotice(`${event.user_name || 'Otra persona'} acaba de actualizar esta ficha.`);
        });
    },

    beforeUnmount() {
        this.stopListening?.();
        clearTimeout(this.liveTimer);
    },

    methods: {
        money: formatMoney,
        formatDay,
        timeAgo,

        typeLabel(value) {
            return typeLabel(SUPPLIER_TYPES, value);
        },
        itemTypeLabel(value) {
            return typeLabel(ITEM_TYPES, value);
        },
        sourceLabel(value) {
            if (value === 'importacion') return 'Importación';
            return typeLabel(PRICE_SOURCES, value);
        },

        leadTimeText(days) {
            if (days === null || days === undefined || days === '') return '—';
            if (Number(days) === 0) return 'Inmediata';
            return Number(days) === 1 ? '1 día' : `${days} días`;
        },

        ageText(ymd) {
            const days = daysSince(ymd);
            if (days === null) return '';
            if (days <= 0) return 'hoy';
            if (days === 1) return 'ayer';
            if (days < 60) return `hace ${days} días`;
            return `hace ${Math.round(days / 30)} meses`;
        },

        mapLink(branch) {
            const q = [branch.address, branch.city, branch.department].filter(Boolean).join(', ');
            return branch.address ? `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(q)}` : null;
        },

        async load({ silent = false } = {}) {
            if (!silent) this.loading = true;
            try {
                const { data } = await supplierService.get(this.$route.params.id);
                this.supplier = data;
            } catch (err) {
                if (!silent) {
                    this.$router.replace({ name: 'suppliers.index' });
                }
            } finally {
                this.loading = false;
            }
        },

        flashNotice(text) {
            this.liveNotice = text;
            clearTimeout(this.liveTimer);
            this.liveTimer = setTimeout(() => { this.liveNotice = null; }, 6000);
        },

        openBranch(branch = null) { this.editing = branch; this.modal = 'branch'; },
        openContact(contact = null) { this.editing = contact; this.modal = 'contact'; },
        openOffer(offer = null) { this.editing = offer; this.modal = 'offer'; },
        closeModal() { this.modal = null; this.editing = null; },

        async onSaved(message) {
            this.closeModal();
            await this.load({ silent: true });
            this.toast(message);
        },

        // 409: otro master guardó ese mismo registro mientras el modal estaba abierto.
        async onStale(info) {
            this.closeModal();
            await this.load({ silent: true });
            this.$swal.fire({
                icon: 'warning',
                title: 'Alguien más lo modificó',
                text: (info?.message || 'Otra persona modificó este registro.') + ' Ya ves la versión actual: vuelve a abrirlo si aún quieres cambiarlo.',
                confirmButtonColor: '#279208',
            });
        },

        toast(title) {
            this.$swal.fire({ toast: true, position: 'top-end', icon: 'success', title, timer: 1800, showConfirmButton: false });
        },

        async confirm(title, text) {
            const res = await this.$swal.fire({
                icon: 'warning', title, text,
                showCancelButton: true,
                confirmButtonText: 'Eliminar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#ba2831',
            });
            return res.isConfirmed;
        },

        async remove(request, okMessage) {
            try {
                await request();
                await this.load({ silent: true });
                this.toast(okMessage);
            } catch (err) {
                this.$swal.fire({
                    icon: 'error',
                    title: 'No se pudo eliminar',
                    text: err.response?.data?.message || 'Inténtalo de nuevo.',
                });
            }
        },

        async deleteBranch(branch) {
            if (!await this.confirm('Eliminar sede', `¿Eliminar la sede "${branch.name}"?`)) return;
            await this.remove(() => supplierService.destroyBranch(this.supplier.id, branch.id), 'Sede eliminada');
        },

        async deleteContact(contact) {
            if (!await this.confirm('Eliminar asesor', `¿Eliminar a "${contact.name}"?`)) return;
            await this.remove(() => supplierService.destroyContact(this.supplier.id, contact.id), 'Asesor eliminado');
        },

        async deleteOffer(offer) {
            const ok = await this.confirm(
                'Quitar producto',
                `¿Quitar "${offer.supply_item?.name}" de este proveedor? Se pierde su precio y su historial.`,
            );
            if (!ok) return;
            await this.remove(() => supplierService.destroyOffer(this.supplier.id, offer.id), 'Producto quitado');
        },

        async deleteSupplier() {
            const name = this.supplier.trade_name || this.supplier.business_name;
            const ok = await this.confirm(
                'Eliminar proveedor',
                `¿Eliminar "${name}"? Se eliminarán también sus sedes, asesores y precios.`
                    + (this.supplier.is_example ? ' Al ser de ejemplo, también se quitan sus productos de ejemplo.' : ''),
            );
            if (!ok) return;

            try {
                await supplierService.destroy(this.supplier.id);
                this.$router.replace({ name: 'suppliers.index' });
            } catch (err) {
                this.$swal.fire({
                    icon: 'error',
                    title: 'No se pudo eliminar',
                    text: err.response?.data?.message || 'No se pudo eliminar el proveedor.',
                });
            }
        },

        async openHistory(offer) {
            this.history = { offer, rows: [], loading: true };
            try {
                const { data } = await supplierService.offerHistory(this.supplier.id, offer.id);
                if (this.history?.offer.id === offer.id) this.history.rows = data;
            } finally {
                if (this.history) this.history.loading = false;
            }
        },

        // Variación respecto al precio anterior (la lista va del más nuevo al más viejo).
        variation(i) {
            const rows = this.history.rows;
            const current = rows[i];
            const previous = rows[i + 1];
            if (!previous || previous.currency !== current.currency || !Number(previous.price)) return null;

            const pct = ((Number(current.price) - Number(previous.price)) / Number(previous.price)) * 100;
            if (Math.abs(pct) < 0.05) return null;

            return { up: pct > 0, text: `${pct > 0 ? '+' : ''}${pct.toFixed(1).replace('.', ',')} %` };
        },
    },
};
</script>

<style scoped>
.loading-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 4rem 2rem;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-radius: 20px;
}

/* Botones de cabecera */
.btn-action {
    display: inline-flex;
    align-items: center;
    padding: 0.5rem 1rem;
    font-size: 0.85rem;
    font-weight: 500;
    border-radius: 8px;
    text-decoration: none;
    border: 1px solid transparent;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-back { background: #f1f5f9; color: #475569; border-color: #e2e8f0; }
.btn-back:hover { background: #e2e8f0; color: #334155; }
.btn-edit { background: #279208; color: #fff; }
.btn-edit:hover { background: #1f7506; color: #fff; }
.btn-delete { background: #fff; color: #ba2831; border-color: #f3c6c9; }
.btn-delete:hover { background: #fef2f2; }

.badge-example,
.badge-inactive {
    display: inline-block;
    margin-left: 0.5rem;
    padding: 0.15rem 0.55rem;
    border-radius: 10px;
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.03em;
    text-transform: uppercase;
    vertical-align: middle;
}

.badge-example { background: #fef3c7; color: #b45309; }
.badge-inactive { background: #f1f5f9; color: #64748b; }

/* Avisos */
.example-banner,
.live-notice,
.stale-note {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 0.85rem 1.1rem;
    margin-bottom: 1.25rem;
    border-radius: 12px;
    font-size: 0.9rem;
    line-height: 1.5;
}

.example-banner,
.stale-note { background: #fffbeb; border: 1px solid #fde68a; color: #92400e; }
.example-banner > i,
.stale-note > i { margin-top: 0.2rem; color: #d97706; }
.stale-note { margin-bottom: 1rem; }

.live-notice { background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; align-items: center; }

.fade-enter-active,
.fade-leave-active { transition: opacity 0.3s; }
.fade-enter-from,
.fade-leave-to { opacity: 0; }

/* Resumen */
.summary-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    padding: 1.25rem 1.5rem;
    margin-bottom: 1.5rem;
}

.summary-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
    gap: 1rem 1.5rem;
}

.summary-item { display: flex; flex-direction: column; gap: 0.15rem; min-width: 0; }

.summary-label {
    font-size: 0.72rem;
    font-weight: 600;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    color: #94a3b8;
}

.summary-value { font-size: 0.95rem; font-weight: 500; color: #1e293b; overflow-wrap: anywhere; }
.summary-value small { color: #64748b; font-weight: 400; }
.summary-link { color: #279208; text-decoration: none; }
.summary-link:hover { text-decoration: underline; }

.stars .fa-star { color: #e2e8f0; font-size: 0.9rem; }
.stars .fa-star.on { color: #f59e0b; }

.summary-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #f1f5f9;
}

.tag {
    padding: 0.2rem 0.65rem;
    border-radius: 14px;
    font-size: 0.78rem;
    font-weight: 500;
}

.tag-category { background: #e8f5e4; color: #1f7506; }
.tag-brand { background: #f1f5f9; color: #475569; }

.summary-notes {
    margin: 1rem 0 0;
    font-size: 0.9rem;
    color: #475569;
    line-height: 1.55;
    white-space: pre-line;
}

.summary-author {
    margin-top: 0.9rem;
    font-size: 0.76rem;
    color: #94a3b8;
}

/* Pestañas */
.tab-nav {
    display: flex;
    background: white;
    border-radius: 12px 12px 0 0;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    border: 1px solid #e2e8f0;
    border-bottom: 1px solid #e2e8f0;
    overflow-x: auto;
    /* overflow-x: auto arrastra overflow-y a auto: sin esto, cualquier píxel de
       más saca una barra vertical junto a las pestañas. */
    overflow-y: hidden;
}

.tab-btn {
    flex: 1;
    min-width: max-content;
    padding: 0.875rem 1.25rem;
    background: none;
    border: none;
    border-bottom: 3px solid transparent;
    color: #64748b;
    font-size: 0.9rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
}

.tab-btn:hover { color: #279208; background: #f8faf8; }
.tab-btn.active { color: #279208; border-bottom-color: #30ab0a; font-weight: 600; }

.tab-count {
    display: inline-block;
    margin-left: 0.35rem;
    padding: 0.05rem 0.45rem;
    background: #f1f5f9;
    border-radius: 10px;
    font-size: 0.72rem;
    font-weight: 600;
    color: #64748b;
}

.tab-btn.active .tab-count { background: #e8f5e4; color: #279208; }

.tab-content-area {
    background: white;
    border-radius: 0 0 12px 12px;
    border: 1px solid #e2e8f0;
    border-top: none;
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    margin-bottom: 1.5rem;
}

.tab-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1.25rem;
}

.tab-hint { margin: 0; font-size: 0.85rem; color: #64748b; }

.btn-add {
    display: inline-flex;
    align-items: center;
    flex-shrink: 0;
    padding: 0.55rem 1rem;
    background: #279208;
    color: #fff;
    font-size: 0.85rem;
    font-weight: 600;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.2s;
}

.btn-add:hover { background: #1f7506; }

.tab-empty {
    padding: 2.5rem 1rem;
    text-align: center;
    color: #94a3b8;
}

.tab-empty i { font-size: 1.8rem; margin-bottom: 0.75rem; }
.tab-empty p { margin: 0; }

/* Tarjetas de sedes y asesores */
.card-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(290px, 1fr));
    gap: 1rem;
}

.info-card {
    display: flex;
    flex-direction: column;
    min-width: 0;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 1rem 1.1rem;
    background: #fff;
    transition: box-shadow 0.2s;
}

.info-card:hover { box-shadow: 0 4px 14px rgba(0, 0, 0, 0.07); }

.info-card-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
}

.info-card-title { font-weight: 700; color: #1e293b; }
.info-card-sub { font-size: 0.82rem; color: #64748b; }

.pill {
    display: inline-block;
    margin-left: 0.35rem;
    padding: 0.08rem 0.5rem;
    border-radius: 10px;
    font-size: 0.66rem;
    font-weight: 700;
    letter-spacing: 0.03em;
    text-transform: uppercase;
    vertical-align: middle;
}

.pill-main { background: #e8f5e4; color: #279208; }

.card-actions,
.table-actions { display: inline-flex; gap: 0.35rem; flex-shrink: 0; }

.icon-btn {
    width: 30px;
    height: 30px;
    min-height: 0;
    border: none;
    border-radius: 6px;
    background: #f1f5f9;
    color: #64748b;
    font-size: 0.8rem;
    cursor: pointer;
    transition: all 0.2s;
}

.icon-btn:hover { background: #e2e8f0; color: #334155; }
.icon-btn-danger:hover { background: #fef2f2; color: #dc2626; }

.info-list {
    flex: 1;
    list-style: none;
    margin: 0;
    padding: 0;
}

.info-list li {
    display: flex;
    align-items: flex-start;
    gap: 0.6rem;
    padding: 0.2rem 0;
    font-size: 0.88rem;
    color: #475569;
}

.info-list i {
    width: 16px;
    margin-top: 0.2rem;
    text-align: center;
    color: #94a3b8;
    font-size: 0.8rem;
    flex-shrink: 0;
}

.break { overflow-wrap: anywhere; }

.info-card-foot {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    margin-top: 0.9rem;
    padding-top: 0.8rem;
    border-top: 1px solid #f1f5f9;
}

.map-link {
    font-size: 0.82rem;
    font-weight: 600;
    color: #279208;
    text-decoration: none;
    white-space: nowrap;
}

.map-link:hover { text-decoration: underline; }

/* Productos y precios */
.offer-search {
    position: relative;
    flex: 1;
    max-width: 380px;
}

.offer-search i {
    position: absolute;
    left: 0.85rem;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 0.85rem;
}

.offer-search input {
    width: 100%;
    padding: 0.55rem 0.8rem 0.55rem 2.3rem;
    font-size: 0.9rem;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
}

.offer-search input:focus {
    outline: none;
    border-color: #279208;
    box-shadow: 0 0 0 3px rgba(39, 146, 8, 0.1);
}

.table-responsive { overflow-x: auto; }

.data-table { width: 100%; border-collapse: collapse; }
.data-table thead { background: #f8fafc; border-bottom: 1px solid #e2e8f0; }

.data-table th {
    padding: 0.75rem 1rem;
    font-size: 0.76rem;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    white-space: nowrap;
}

.data-table td {
    padding: 0.8rem 1rem;
    font-size: 0.9rem;
    color: #475569;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}

.data-table tbody tr:last-child td { border-bottom: none; }
.data-table tbody tr:hover { background: #fafafa; }

.offer-product { min-width: 0; }
.offer-name { font-weight: 600; color: #1e293b; }
.offer-meta { font-size: 0.78rem; color: #64748b; font-weight: 400; }
.offer-notes { margin-top: 0.2rem; font-size: 0.78rem; color: #94a3b8; font-style: italic; font-weight: 400; }
.preferred { margin-left: 0.3rem; color: #f59e0b; font-size: 0.8rem; }

.price { font-weight: 700; color: #1e293b; white-space: nowrap; }
.price-unit { margin-left: 0.2rem; font-size: 0.78rem; color: #94a3b8; white-space: nowrap; }

.price-date { white-space: nowrap; }
.price-date.stale { color: #b45309; font-weight: 600; }
.price-age { font-size: 0.76rem; color: #94a3b8; }

@media (max-width: 768px) {
    .tab-toolbar { flex-direction: column; align-items: stretch; }
    .offer-search { max-width: none; }
    .page-title-actions { flex-wrap: wrap; }
    .offer-product { text-align: left; }
    .tab-content-area { padding: 1rem; }
}
</style>

<style>
/* Historial de precios: va teleportado a <body>, sin scope. */
.sh-product { margin: 0 0 1rem; font-weight: 600; color: #1e293b; }
.sh-empty { padding: 1.5rem 0; text-align: center; color: #94a3b8; }

.sh-table { width: 100%; border-collapse: collapse; font-size: 0.88rem; }

.sh-table th {
    padding: 0.5rem 0.6rem;
    border-bottom: 1px solid #e2e8f0;
    font-size: 0.72rem;
    font-weight: 600;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    text-align: left;
    color: #64748b;
}

.sh-table td { padding: 0.55rem 0.6rem; border-bottom: 1px solid #f1f5f9; color: #475569; }
.sh-right { text-align: right !important; white-space: nowrap; }
.sh-current td { font-weight: 600; color: #1e293b; background: #f8fdf6; }

.sh-var { margin-left: 0.35rem; font-size: 0.74rem; font-weight: 600; }
.sh-up { color: #dc2626; }
.sh-down { color: #16a34a; }
</style>
