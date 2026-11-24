<template>
    <button type="button" :title="title" :disabled="busy" @click="open">
        <slot :busy="busy">
            <i :class="busy ? 'fa fa-spinner fa-spin' : 'fa fa-share-alt'"></i>
        </slot>

        <!-- En body: las tablas y cabeceras donde vive el botón recortan lo que se sale. -->
        <Teleport to="body">
            <div v-if="visible" class="dm-share" @click.self="close">
                <div class="dm-share__sheet" role="dialog" aria-modal="true" aria-label="Compartir reporte">
                    <div class="dm-share__header">
                        <span><i class="fa fa-share-alt"></i> Compartir reporte</span>
                        <button type="button" class="dm-share__close" title="Cerrar" @click="close">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>

                    <div v-if="state === 'loading'" class="dm-share__status">
                        <i class="fa fa-spinner fa-spin"></i>
                        <p>Preparando el PDF…</p>
                    </div>

                    <div v-else-if="state === 'error'" class="dm-share__status">
                        <i class="fa fa-exclamation-triangle dm-share__warn"></i>
                        <p>No se pudo generar el PDF.</p>
                        <button type="button" class="dm-share__primary" @click="prepare">Reintentar</button>
                    </div>

                    <div v-else class="dm-share__body">
                        <div class="dm-share__file">
                            <i class="fa fa-file-pdf"></i>
                            <span>{{ filename }}</span>
                        </div>

                        <template v-if="nativeShare">
                            <button type="button" class="dm-share__primary" @click="shareFile">
                                <i class="fa fa-share-alt"></i> Compartir
                            </button>
                            <p class="dm-share__hint">Elige WhatsApp, correo, Telegram o cualquier otra aplicación.</p>
                        </template>

                        <template v-else>
                            <p class="dm-share__hint">
                                Este navegador no permite adjuntar el archivo automáticamente. Al elegir una opción
                                se descarga el PDF y se abre la aplicación para que lo adjuntes.
                            </p>
                            <div class="dm-share__targets">
                                <button type="button" class="dm-share__target dm-share__target--whatsapp" @click="openTarget('whatsapp')">
                                    <i class="fab fa-whatsapp"></i><span>WhatsApp</span>
                                </button>
                                <button type="button" class="dm-share__target dm-share__target--mail" @click="openTarget('mail')">
                                    <i class="fa fa-envelope"></i><span>Correo</span>
                                </button>
                                <button type="button" class="dm-share__target dm-share__target--telegram" @click="openTarget('telegram')">
                                    <i class="fab fa-telegram-plane"></i><span>Telegram</span>
                                </button>
                            </div>
                        </template>

                        <button type="button" class="dm-share__secondary" @click="download">
                            <i class="fa fa-download"></i> Solo descargar
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </button>
</template>

<script>
import reportService from '@/services/reportService.js';

/**
 * Botón "Compartir" de un reporte. Genera el PDF y lo entrega a la hoja de compartir
 * del sistema (WhatsApp, correo, Telegram…). Donde el navegador no sabe compartir
 * archivos, lo descarga y abre la aplicación elegida.
 *
 * Lleva las clases que le ponga la vista (action-btn, btn-action…): cada vista del
 * panel define las suyas con CSS scoped.
 */
export default {
    name: 'ReportShareButton',

    props: {
        report: { type: Object, required: true },
        title: { type: String, default: 'Compartir' },
        // Para el enlace público, que no pasa por la API autenticada. Devuelve un Blob.
        fetcher: { type: Function, default: null },
    },

    data() {
        return {
            visible: false,
            state: 'loading',
            file: null,
            nativeShare: false,
        };
    },

    computed: {
        busy() {
            return this.visible && this.state === 'loading';
        },
        filename() {
            return `${this.report.report_number || 'reporte'}.pdf`;
        },
        shareText() {
            return `Reporte de servicio ${this.report.report_number || ''} · Ascensores Tzion`.replace('  ', ' ');
        },
    },

    methods: {
        open() {
            if (this.visible) return;
            this.visible = true;
            this.prepare();
        },

        close() {
            this.visible = false;
            this.file = null;
        },

        async prepare() {
            this.state = 'loading';
            try {
                const data = this.fetcher
                    ? await this.fetcher(this.report)
                    : (await reportService.getPdf(this.report.id)).data;
                if (!this.visible) return;

                this.file = new File([data], this.filename, { type: 'application/pdf' });
                this.nativeShare = !!navigator.canShare?.({ files: [this.file] });
                this.state = 'ready';

                // Si el PDF llegó rápido, el toque inicial todavía autoriza a abrir la
                // hoja de compartir. Si ya caducó, queda el botón "Compartir".
                if (this.nativeShare) this.shareFile({ silent: true });
            } catch {
                if (this.visible) this.state = 'error';
            }
        },

        async shareFile({ silent = false } = {}) {
            try {
                await navigator.share({ files: [this.file], title: this.filename, text: this.shareText });
                this.close();
            } catch (error) {
                // AbortError: cerró la hoja sin elegir. NotAllowedError: el toque caducó.
                if (silent || error?.name === 'AbortError') return;
                this.nativeShare = false;
            }
        },

        download() {
            const url = URL.createObjectURL(this.file);
            const link = document.createElement('a');
            link.href = url;
            link.download = this.filename;
            document.body.appendChild(link);
            link.click();
            link.remove();
            setTimeout(() => URL.revokeObjectURL(url), 10000);
        },

        openTarget(target) {
            const text = encodeURIComponent(this.shareText);
            const urls = {
                whatsapp: `https://wa.me/?text=${text}`,
                mail: `mailto:?subject=${text}&body=${encodeURIComponent(`Adjunto el ${this.shareText}.`)}`,
                telegram: 'https://web.telegram.org/',
            };
            this.download();
            if (target === 'mail') {
                window.location.href = urls.mail;
            } else {
                window.open(urls[target], '_blank', 'noopener');
            }
        },
    },
};
</script>

<style scoped>
.dm-share {
    position: fixed;
    inset: 0;
    z-index: 2000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    background: rgba(15, 23, 42, 0.55);
    cursor: default;
}

.dm-share__sheet {
    width: 100%;
    max-width: 380px;
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.25);
    overflow: hidden;
    font-family: 'Poppins', sans-serif;
    font-size: 0.9rem;
    font-weight: 400;
    color: #334155;
    text-align: left;
    white-space: normal;
}

.dm-share__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.85rem 1rem;
    border-bottom: 1px solid #e2e8f0;
    font-weight: 600;
    color: #1e293b;
}

.dm-share__header .fa-share-alt {
    color: #30ab0a;
    margin-right: 0.4rem;
}

.dm-share__close {
    width: 32px;
    height: 32px;
    min-height: 0;
    border: none;
    border-radius: 50%;
    background: #f1f5f9;
    color: #64748b;
    cursor: pointer;
}

.dm-share__status,
.dm-share__body {
    display: flex;
    flex-direction: column;
    align-items: stretch;
    gap: 0.75rem;
    padding: 1.1rem 1rem 1rem;
}

.dm-share__status {
    align-items: center;
    text-align: center;
    padding: 1.75rem 1rem;
    font-size: 1.4rem;
    color: #30ab0a;
}

.dm-share__status p {
    margin: 0;
    font-size: 0.9rem;
    color: #475569;
}

.dm-share__warn {
    color: #ba2831;
}

.dm-share__file {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.7rem 0.8rem;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    font-weight: 500;
    word-break: break-all;
}

.dm-share__file .fa-file-pdf {
    font-size: 1.3rem;
    color: #ba2831;
}

.dm-share__hint {
    margin: 0;
    font-size: 0.8rem;
    line-height: 1.45;
    color: #64748b;
}

.dm-share__primary,
.dm-share__secondary {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    min-height: 44px;
    padding: 0.6rem 1rem;
    border-radius: 10px;
    font-family: inherit;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
}

.dm-share__primary {
    border: none;
    background: #30ab0a;
    color: #fff;
}

.dm-share__secondary {
    border: 1px solid #cbd5e1;
    background: #fff;
    color: #475569;
}

.dm-share__targets {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.6rem;
}

.dm-share__target {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.35rem;
    padding: 0.75rem 0.25rem;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    background: #fff;
    font-family: inherit;
    font-size: 0.75rem;
    font-weight: 500;
    color: #334155;
    cursor: pointer;
}

.dm-share__target i {
    font-size: 1.5rem;
}

.dm-share__target--whatsapp i { color: #25d366; }
.dm-share__target--mail i { color: #606060; }
.dm-share__target--telegram i { color: #229ed9; }
</style>
