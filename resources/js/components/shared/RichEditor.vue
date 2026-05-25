<template>
    <div class="rich-editor">
        <div class="editor-toolbar" v-if="editor">
            <button type="button" @click="editor.chain().focus().toggleBold().run()"
                :class="{ active: editor.isActive('bold') }" title="Negrita">
                <i class="fas fa-bold"></i>
            </button>
            <button type="button" @click="editor.chain().focus().toggleItalic().run()"
                :class="{ active: editor.isActive('italic') }" title="Itálica">
                <i class="fas fa-italic"></i>
            </button>
            <button type="button" @click="editor.chain().focus().toggleUnderline().run()"
                :class="{ active: editor.isActive('underline') }" title="Subrayado">
                <i class="fas fa-underline"></i>
            </button>
            <span class="toolbar-separator"></span>
            <button type="button" @click="editor.chain().focus().toggleBulletList().run()"
                :class="{ active: editor.isActive('bulletList') }" title="Viñetas">
                <i class="fas fa-list-ul"></i>
            </button>
            <button type="button" @click="editor.chain().focus().toggleOrderedList().run()"
                :class="{ active: editor.isActive('orderedList') }" title="Lista numerada">
                <i class="fas fa-list-ol"></i>
            </button>
            <span class="toolbar-separator"></span>
            <button type="button" @click="setLink" :class="{ active: editor.isActive('link') }" title="Enlace">
                <i class="fas fa-link"></i>
            </button>
            <button type="button" @click="editor.chain().focus().unsetAllMarks().clearNodes().run()" title="Limpiar formato">
                <i class="fas fa-eraser"></i>
            </button>
        </div>
        <EditorContent :editor="editor" class="editor-content" />
    </div>
</template>

<script>
import { Editor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import Link from '@tiptap/extension-link'
import Underline from '@tiptap/extension-underline'

export default {
    name: 'RichEditor',

    components: { EditorContent },

    props: {
        modelValue: {
            type: String,
            default: '',
        },
        placeholder: {
            type: String,
            default: 'Escribe aquí...',
        },
    },

    emits: ['update:modelValue'],

    data() {
        return {
            editor: null,
        }
    },

    watch: {
        modelValue(value) {
            const isSame = this.editor.getHTML() === value
            if (!isSame) {
                this.editor.commands.setContent(value, false)
            }
        },
    },

    mounted() {
        this.editor = new Editor({
            content: this.modelValue,
            extensions: [
                StarterKit.configure({
                    heading: false,
                    codeBlock: false,
                    blockquote: false,
                    horizontalRule: false,
                }),
                Link.configure({
                    openOnClick: false,
                }),
                Underline,
            ],
            onUpdate: () => {
                const html = this.editor.getHTML()
                this.$emit('update:modelValue', html === '<p></p>' ? '' : html)
            },
        })
    },

    beforeUnmount() {
        this.editor.destroy()
    },

    methods: {
        setLink() {
            if (this.editor.isActive('link')) {
                this.editor.chain().focus().unsetLink().run()
                return
            }
            const url = window.prompt('URL del enlace:')
            if (url) {
                this.editor.chain().focus().setLink({ href: url }).run()
            }
        },
    },
}
</script>

<style>
.rich-editor {
    border: 1px solid #ced4da;
    border-radius: 4px;
    overflow: hidden;
}

.editor-toolbar {
    display: flex;
    align-items: center;
    gap: 2px;
    padding: 6px 8px;
    border-bottom: 1px solid #ced4da;
    background: #f8f9fa;
}

.editor-toolbar button {
    background: none;
    border: none;
    padding: 4px 8px;
    border-radius: 3px;
    cursor: pointer;
    color: #495057;
    font-size: 13px;
}

.editor-toolbar button:hover {
    background: #e9ecef;
}

.editor-toolbar button.active {
    background: #dee2e6;
    color: #212529;
}

.toolbar-separator {
    width: 1px;
    height: 20px;
    background: #ced4da;
    margin: 0 4px;
}

.editor-content .tiptap {
    padding: 10px 12px;
    min-height: 120px;
    outline: none;
    font-size: 14px;
    line-height: 1.5;
    color: #495057;
}

.editor-content .tiptap p {
    margin: 0 0 0.5em;
}

.editor-content .tiptap ul,
.editor-content .tiptap ol {
    padding-left: 1.5em;
    margin: 0.5em 0;
}

.editor-content .tiptap li p {
    margin: 0;
}

.editor-content .tiptap a {
    color: #0d6efd;
    text-decoration: underline;
}

.rich-editor:focus-within {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}
</style>
