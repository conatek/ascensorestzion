<template>
    <div class="qr-scanner">
        <div v-if="error" class="qr-scanner__error">
            <i class="fa fa-exclamation-triangle"></i>
            <p>{{ error }}</p>
            <button class="qr-scanner__retry-btn" @click="startScanner">Reintentar</button>
        </div>

        <div v-show="!error" class="qr-scanner__viewport">
            <div id="qr-reader" ref="qrReader"></div>
            <div class="qr-scanner__overlay">
                <div class="qr-scanner__frame"></div>
                <p class="qr-scanner__hint">Apunte la cámara al código QR del equipo</p>
            </div>
        </div>

        <button v-if="scanning" class="qr-scanner__stop-btn" @click="stopScanner">
            <i class="fa fa-times"></i> Cancelar escaneo
        </button>
    </div>
</template>

<script>
export default {
    name: 'QrScanner',

    emits: ['scanned', 'error'],

    data() {
        return {
            scanner: null,
            scanning: false,
            error: null,
        };
    },

    mounted() {
        this.startScanner();
    },

    beforeUnmount() {
        this.stopScanner();
    },

    methods: {
        async startScanner() {
            this.error = null;

            try {
                const { Html5Qrcode } = await import('html5-qrcode');

                if (this.scanner) {
                    try { await this.scanner.stop(); } catch {}
                }

                this.scanner = new Html5Qrcode('qr-reader');

                await this.scanner.start(
                    { facingMode: 'environment' },
                    {
                        fps: 10,
                        qrbox: { width: 250, height: 250 },
                        aspectRatio: 1,
                    },
                    (decodedText) => {
                        this.onScanSuccess(decodedText);
                    },
                    () => {
                        // Scan en progreso, sin match aun
                    },
                );

                this.scanning = true;
            } catch (err) {
                this.scanning = false;
                if (err.toString().includes('NotAllowedError')) {
                    this.error = 'Permiso de cámara denegado. Actívelo en la configuración del navegador.';
                } else if (err.toString().includes('NotFoundError')) {
                    this.error = 'No se encontró cámara en este dispositivo.';
                } else {
                    this.error = 'Error al iniciar la cámara: ' + err.message;
                }
                this.$emit('error', this.error);
            }
        },

        onScanSuccess(decodedText) {
            // Extraer código del equipo de la URL del QR o usar texto directo
            let code = decodedText;

            // Si es una URL, extraer el parámetro code
            try {
                const url = new URL(decodedText);
                const codeParam = url.searchParams.get('code');
                if (codeParam) {
                    code = codeParam;
                }
            } catch {
                // No es URL, usar el texto tal cual
            }

            this.stopScanner();
            this.$emit('scanned', code);
        },

        async stopScanner() {
            if (this.scanner && this.scanning) {
                try {
                    await this.scanner.stop();
                } catch {}
            }
            this.scanning = false;
        },
    },
};
</script>

<style scoped>
.qr-scanner {
    position: relative;
    width: 100%;
}

.qr-scanner__viewport {
    position: relative;
    width: 100%;
    max-width: 400px;
    margin: 0 auto;
    border-radius: 16px;
    overflow: hidden;
    background: #000;
}

:deep(#qr-reader) {
    width: 100% !important;
    border: none !important;
}

:deep(#qr-reader video) {
    width: 100% !important;
    border-radius: 16px;
}

:deep(#qr-reader__scan_region) {
    min-height: 300px;
}

:deep(#qr-reader__dashboard) {
    display: none !important;
}

.qr-scanner__overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 1rem;
    background: linear-gradient(transparent, rgba(0, 0, 0, 0.6));
    text-align: center;
    pointer-events: none;
}

.qr-scanner__hint {
    color: white;
    font-size: 0.85rem;
    font-weight: 500;
    margin: 0;
    text-shadow: 0 1px 3px rgba(0, 0, 0, 0.5);
}

.qr-scanner__error {
    text-align: center;
    padding: 2rem 1rem;
    color: #ba2831;
}

.qr-scanner__error i {
    font-size: 2rem;
    margin-bottom: 0.75rem;
    display: block;
}

.qr-scanner__error p {
    font-size: 0.9rem;
    margin-bottom: 1rem;
    color: #64748b;
}

.qr-scanner__retry-btn {
    padding: 0.6rem 1.5rem;
    background: #30ab0a;
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    min-height: 44px;
}

.qr-scanner__stop-btn {
    display: block;
    width: 100%;
    max-width: 400px;
    margin: 1rem auto 0;
    padding: 0.75rem;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    color: #64748b;
    font-size: 0.9rem;
    font-weight: 500;
    cursor: pointer;
    min-height: 44px;
}
</style>
