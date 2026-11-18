<template>
    <div class="tag-input" :class="{ 'tag-input--focus': focused }" @click="$refs.input.focus()">
        <span v-for="(tag, i) in modelValue" :key="tag + i" class="tag-chip">
            {{ tag }}
            <button type="button" class="tag-remove" :title="`Quitar ${tag}`" @click.stop="remove(i)">&times;</button>
        </span>
        <input
            ref="input"
            v-model="draft"
            type="text"
            class="tag-field"
            :list="listId"
            :placeholder="modelValue.length ? '' : placeholder"
            @focus="focused = true"
            @blur="focused = false; commit()"
            @keydown.enter.prevent="commit"
            @keydown.,.prevent="commit"
            @keydown.backspace="onBackspace"
            @change="commit"
        />
        <datalist :id="listId">
            <option v-for="s in available" :key="s" :value="s"></option>
        </datalist>
    </div>
</template>

<script>
let uid = 0;

// Etiquetas libres con sugerencias: Enter o coma añaden, retroceso quita la última.
export default {
    name: 'TagInput',

    props: {
        modelValue: { type: Array, default: () => [] },
        suggestions: { type: Array, default: () => [] },
        placeholder: { type: String, default: 'Escribe y pulsa Enter' },
    },

    emits: ['update:modelValue'],

    data() {
        return { draft: '', focused: false, listId: `tag-list-${++uid}` };
    },

    computed: {
        available() {
            const used = this.modelValue.map((t) => t.toLowerCase());
            return this.suggestions.filter((s) => !used.includes(s.toLowerCase()));
        },
    },

    methods: {
        commit() {
            const value = this.draft.trim().replace(/,$/, '').trim();
            this.draft = '';
            if (!value) return;
            if (this.modelValue.some((t) => t.toLowerCase() === value.toLowerCase())) return;
            this.$emit('update:modelValue', [...this.modelValue, value]);
        },
        remove(i) {
            this.$emit('update:modelValue', this.modelValue.filter((_, idx) => idx !== i));
        },
        onBackspace() {
            if (!this.draft && this.modelValue.length) this.remove(this.modelValue.length - 1);
        },
    },
};
</script>

<style scoped>
.tag-input {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.35rem;
    min-height: 42px;
    padding: 0.35rem 0.6rem;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: #fff;
    cursor: text;
    transition: all 0.2s;
}

.tag-input--focus {
    border-color: #279208;
    box-shadow: 0 0 0 3px rgba(39, 146, 8, 0.1);
}

.tag-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.2rem 0.3rem 0.2rem 0.6rem;
    background: #e8f5e4;
    color: #1f7506;
    border-radius: 14px;
    font-size: 0.8rem;
    font-weight: 500;
}

.tag-remove {
    width: 18px;
    height: 18px;
    min-height: 0;
    padding: 0;
    border: none;
    border-radius: 50%;
    background: transparent;
    color: #1f7506;
    font-size: 1rem;
    line-height: 1;
    cursor: pointer;
}

.tag-remove:hover { background: #c9e8c0; }

.tag-field {
    flex: 1;
    min-width: 120px;
    min-height: 0;
    border: none;
    outline: none;
    font-size: 0.9rem;
    padding: 0.2rem 0;
    background: transparent;
}

.tag-field::placeholder { color: #94a3b8; }
</style>
