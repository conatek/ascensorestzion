<template>
    <DmModal :title="offer ? 'Editar precio' : 'Añadir producto y precio'" :saving="saving" :error="error" wide
             :submit-label="offer ? 'Guardar' : 'Añadir'" @close="$emit('close')" @submit="submit">

        <!-- 1. Qué producto -->
        <div class="sp-section-title">Producto</div>

        <!-- Editando: el producto no cambia -->
        <div v-if="offer" class="om-picked">
            <div>
                <div class="om-picked-name">{{ offer.supply_item?.name }}</div>
                <div class="om-picked-meta">{{ offer.supply_item?.code }} · {{ unitOf(offer.supply_item) }}</div>
            </div>
        </div>

        <!-- Elegido del catálogo -->
        <div v-else-if="picked" class="om-picked">
            <div>
                <div class="om-picked-name">{{ picked.name }}</div>
                <div class="om-picked-meta">
                    {{ picked.code }} · {{ unitOf(picked) }}
                    <span v-if="picked.offers_count"> · ya lo {{ picked.offers_count === 1 ? 'ofrece 1 proveedor' : `ofrecen ${picked.offers_count} proveedores` }}</span>
                </div>
            </div>
            <button type="button" class="om-link" @click="picked = null">Cambiar</button>
        </div>

        <!-- Buscar o crear -->
        <template v-else>
            <div v-if="!creating" class="sp-group om-search">
                <label class="sp-label">Busca el producto en el catálogo <span class="sp-req">*</span></label>
                <input ref="search" v-model="query" type="text" class="sp-input" autocomplete="off"
                       :class="{ 'sp-has-error': fieldError('supply_item_id') }"
                       placeholder="Nombre, código o referencia (mínimo 2 letras)" @input="onSearch" />
                <span v-if="fieldError('supply_item_id')" class="sp-field-error">{{ fieldError('supply_item_id') }}</span>
                <p class="sp-hint">Si ya existe, úsalo: así se pueden comparar los precios de varios proveedores.</p>

                <div v-if="query.trim().length >= 2" class="om-results">
                    <div v-if="searching" class="om-result om-result--muted">Buscando...</div>
                    <button v-for="item in results" :key="item.id" type="button" class="om-result" @click="pick(item)">
                        <span class="om-result-name">{{ item.name }}</span>
                        <span class="om-result-meta">{{ item.code }}{{ item.manufacturer_ref ? ' · ' + item.manufacturer_ref : '' }}</span>
                    </button>
                    <button type="button" class="om-result om-result--create" @click="startCreating">
                        <i class="fa fa-plus"></i> Crear «{{ query.trim() }}» como producto nuevo
                    </button>
                </div>
            </div>

            <template v-else>
                <div class="sp-group">
                    <label class="sp-label">Nombre del producto <span class="sp-req">*</span></label>
                    <input v-model="item.name" type="text" class="sp-input" :class="{ 'sp-has-error': fieldError('item.name') }"
                           placeholder="Ej: Contactor tripolar 220 V 32 A" />
                    <span v-if="fieldError('item.name')" class="sp-field-error">{{ fieldError('item.name') }}</span>
                </div>
                <div class="sp-row sp-row--3">
                    <div class="sp-group">
                        <label class="sp-label">Tipo</label>
                        <select v-model="item.item_type" class="sp-input">
                            <option v-for="t in itemTypes" :key="t.value" :value="t.value">{{ t.label }}</option>
                        </select>
                    </div>
                    <div class="sp-group">
                        <label class="sp-label">Unidad</label>
                        <input v-model="item.unit" type="text" class="sp-input" list="om-units" maxlength="20"
                               autocomplete="off" :class="{ 'sp-has-error': fieldError('item.unit') }"
                               placeholder="und, m, gal, caja…" @blur="normalizeUnit" />
                        <datalist id="om-units">
                            <option v-for="u in units" :key="u" :value="u"></option>
                        </datalist>
                        <span v-if="fieldError('item.unit')" class="sp-field-error">{{ fieldError('item.unit') }}</span>
                        <p v-if="isNewUnit" class="sp-hint">Unidad nueva: quedará disponible para todos.</p>
                    </div>
                    <div class="sp-group">
                        <label class="sp-label">Categoría</label>
                        <input v-model="item.category" type="text" class="sp-input" list="om-categories" placeholder="Ej: Tarjetas electrónicas" />
                        <datalist id="om-categories">
                            <option v-for="c in categories" :key="c" :value="c"></option>
                        </datalist>
                    </div>
                </div>
                <div class="sp-row">
                    <div class="sp-group">
                        <label class="sp-label">Referencia del fabricante</label>
                        <input v-model="item.manufacturer_ref" type="text" class="sp-input" placeholder="Número de parte original" />
                    </div>
                    <div class="sp-group">
                        <label class="sp-label">Marcas de ascensor compatibles</label>
                        <TagInput v-model="item.compatible_brands" :suggestions="brandSuggestions" placeholder="Vacío = universal" />
                    </div>
                </div>
                <div class="sp-group">
                    <label class="sp-label">Descripción</label>
                    <textarea v-model="item.description" class="sp-input" rows="2" placeholder="El texto que saldrá en la cotización"></textarea>
                </div>
                <button type="button" class="om-link om-back" @click="creating = false">
                    <i class="fa fa-arrow-left"></i> Volver a buscar en el catálogo
                </button>
            </template>
        </template>

        <!-- 2. A qué precio -->
        <div class="sp-section-title om-price-title">Precio de este proveedor</div>

        <div class="sp-row sp-row--3">
            <div class="sp-group">
                <label class="sp-label">Precio <span class="sp-req">*</span></label>
                <input v-model="form.price" type="number" step="0.01" min="0" class="sp-input"
                       :class="{ 'sp-has-error': fieldError('price') }" placeholder="0" />
                <span v-if="fieldError('price')" class="sp-field-error">{{ fieldError('price') }}</span>
            </div>
            <div class="sp-group">
                <label class="sp-label">Moneda</label>
                <select v-model="form.currency" class="sp-input">
                    <option value="COP">COP · pesos</option>
                    <option value="USD">USD · dólares</option>
                </select>
            </div>
            <div class="sp-group">
                <label class="sp-label">
                    Fecha del precio <span class="sp-req">*</span>
                    <button v-if="form.price_date !== today" type="button" class="om-link om-today" @click="form.price_date = today">Hoy</button>
                </label>
                <input v-model="form.price_date" type="date" class="sp-input" :max="today"
                       :class="{ 'sp-has-error': fieldError('price_date') }" />
                <span v-if="fieldError('price_date')" class="sp-field-error">{{ fieldError('price_date') }}</span>
            </div>
        </div>

        <div class="sp-group">
            <label class="sp-check">
                <input v-model="form.price_includes_tax" type="checkbox" />
                El precio que escribí ya incluye IVA
            </label>
            <p class="sp-hint">
                Los precios se guardan sin IVA.
                <template v-if="form.price_includes_tax && form.price">
                    Se guardará <strong>{{ money(priceWithoutTax, form.currency) }}</strong> (IVA del {{ taxRate }} %).
                </template>
            </p>
        </div>

        <div class="sp-row sp-row--3">
            <div class="sp-group">
                <label class="sp-label">Referencia del proveedor</label>
                <input v-model="form.supplier_ref" type="text" class="sp-input" placeholder="Cómo lo llama él" />
            </div>
            <div class="sp-group">
                <label class="sp-label">Entrega (días)</label>
                <input v-model="form.lead_time_days" type="number" min="0" max="365" class="sp-input"
                       :placeholder="defaultLeadTime !== null ? `Habitual: ${defaultLeadTime}` : 'Ej: 3'" />
            </div>
            <div class="sp-group">
                <label class="sp-label">De dónde salió</label>
                <select v-model="form.source" class="sp-input">
                    <option v-for="s in sources" :key="s.value" :value="s.value">{{ s.label }}</option>
                </select>
            </div>
        </div>

        <div class="sp-group">
            <label class="sp-label">Notas</label>
            <textarea v-model="form.notes" class="sp-input" rows="2" placeholder="Cantidad mínima, condiciones, garantía..."></textarea>
        </div>

        <label class="sp-check">
            <input v-model="form.is_preferred" type="checkbox" />
            Proveedor preferido para este producto
        </label>
    </DmModal>
</template>

<script>
import DmModal from './DmModal.vue';
import TagInput from './TagInput.vue';
import modalForm from './modalForm.js';
import supplierService from '@/services/supplierService.js';
import api from '@/services/api.js';
import {
    ITEM_TYPES, UNITS, PRICE_SOURCES, CATEGORY_SUGGESTIONS, formatMoney, todayYmd,
} from '@/services/supplierOptions.js';

export default {
    name: 'OfferModal',
    components: { DmModal, TagInput },
    mixins: [modalForm],

    props: {
        supplierId: { type: [Number, String], required: true },
        offer: { type: Object, default: null },
        defaultCurrency: { type: String, default: 'COP' },
        defaultLeadTime: { type: Number, default: null },
    },

    data() {
        const o = this.offer || {};
        return {
            itemTypes: ITEM_TYPES,
            units: UNITS,
            sources: PRICE_SOURCES,
            categories: CATEGORY_SUGGESTIONS,
            brandSuggestions: [],
            today: todayYmd(),

            query: '',
            results: [],
            searching: false,
            searchTimer: null,
            picked: null,
            creating: false,
            item: {
                name: '', item_type: 'repuesto', unit: 'und', category: '',
                manufacturer_ref: '', compatible_brands: [], description: '',
            },

            form: {
                price: o.price != null ? Number(o.price) : '',
                currency: o.currency || this.defaultCurrency || 'COP',
                // Al editar se conserva la fecha: cambiar una nota no "confirma" el
                // precio. El enlace "Hoy" la actualiza cuando sí lo confirmaron.
                price_date: o.price_date || todayYmd(),
                price_includes_tax: false,
                supplier_ref: o.supplier_ref || '',
                lead_time_days: o.lead_time_days ?? '',
                source: o.source && o.source !== 'importacion' ? o.source : 'cotizacion',
                notes: o.notes || '',
                is_preferred: !!o.is_preferred,
            },
        };
    },

    computed: {
        taxRate() {
            const item = this.offer?.supply_item || this.picked;
            return Number(item?.tax_rate ?? 19);
        },
        isNewUnit() {
            const unit = this.cleanUnit(this.item.unit);
            return unit !== '' && !this.units.includes(unit);
        },
        priceWithoutTax() {
            return Math.round((Number(this.form.price) / (1 + this.taxRate / 100)) * 100) / 100;
        },
    },

    async mounted() {
        if (!this.offer) this.$nextTick(() => this.$refs.search?.focus());

        // Unidades: primero las que ya usa el catálogo, luego las habituales.
        supplierService.itemUnits()
            .then(({ data }) => { this.units = [...new Set([...data, ...UNITS])]; })
            .catch(() => { /* quedan las habituales */ });

        // Marcas de ascensor del catálogo existente, como sugerencias.
        try {
            const { data } = await api.get('/catalogs', { params: { scope: 'equipment', category: 'brand' } });
            this.brandSuggestions = data.map((c) => c.label);
        } catch { /* sin sugerencias */ }
    },

    beforeUnmount() {
        clearTimeout(this.searchTimer);
    },

    methods: {
        money: formatMoney,

        cleanUnit(value) {
            return String(value || '').trim().replace(/\s+/g, ' ').toLowerCase();
        },
        normalizeUnit() {
            this.item.unit = this.cleanUnit(this.item.unit) || 'und';
        },

        unitOf(item) {
            return item?.unit ? `por ${item.unit}` : '';
        },

        onSearch() {
            clearTimeout(this.searchTimer);
            const q = this.query.trim();
            if (q.length < 2) { this.results = []; return; }

            this.searching = true;
            this.searchTimer = setTimeout(async () => {
                try {
                    const { data } = await supplierService.searchItems(q);
                    // Solo si la respuesta sigue siendo la de lo que hay escrito.
                    if (q === this.query.trim()) this.results = data;
                } catch {
                    this.results = [];
                } finally {
                    this.searching = false;
                }
            }, 250);
        },

        pick(item) {
            this.picked = item;
            this.errors = {};
        },

        startCreating() {
            this.item.name = this.query.trim();
            this.creating = true;
        },

        submit() {
            const payload = {
                ...this.form,
                lead_time_days: this.form.lead_time_days === '' ? null : this.form.lead_time_days,
            };

            if (this.offer) {
                this.run(() => supplierService.updateOffer(this.supplierId, this.offer.id, {
                    ...payload, expected_updated_at: this.offer.updated_at,
                }));
                return;
            }

            if (this.picked) {
                payload.supply_item_id = this.picked.id;
            } else if (this.creating) {
                payload.item = { ...this.item };
            } else {
                this.errors = { supply_item_id: ['Busca un producto del catálogo o crea uno nuevo.'] };
                return;
            }

            this.run(() => supplierService.storeOffer(this.supplierId, payload));
        },
    },
};
</script>

<style>
/* Sin scope: el modal va teleportado a <body>. */
.om-search { position: relative; }

.om-results {
    margin-top: 0.5rem;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    overflow: hidden;
    max-height: 260px;
    overflow-y: auto;
}

.om-result {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 0.1rem;
    width: 100%;
    padding: 0.6rem 0.85rem;
    border: none;
    border-bottom: 1px solid #f1f5f9;
    background: #fff;
    text-align: left;
    cursor: pointer;
    font-size: 0.9rem;
    color: #1e293b;
}

.om-result:hover { background: #f1f9ee; }
.om-result:last-child { border-bottom: none; }
.om-result--muted { color: #94a3b8; cursor: default; }
.om-result--muted:hover { background: #fff; }

.om-result--create {
    flex-direction: row;
    align-items: center;
    gap: 0.5rem;
    color: #279208;
    font-weight: 600;
    background: #f8fafc;
}

.om-result-name { font-weight: 600; }
.om-result-meta { font-size: 0.78rem; color: #64748b; }

.om-picked {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 0.75rem 1rem;
    margin-bottom: 1rem;
    background: #f1f9ee;
    border: 1px solid #cfe8c6;
    border-radius: 10px;
}

.om-picked-name { font-weight: 600; color: #1e293b; }
.om-picked-meta { font-size: 0.8rem; color: #64748b; }

.om-link {
    min-height: 0;
    padding: 0;
    border: none;
    background: none;
    color: #279208;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    white-space: nowrap;
}

.om-link:hover { text-decoration: underline; }
.om-back { margin-bottom: 0.5rem; }
.om-today { margin-left: 0.4rem; font-size: 0.78rem; }
.om-price-title { margin-top: 1.25rem; }
</style>
