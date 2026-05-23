<template>
    <div class="signature-pad-wrapper">
        <canvas
            ref="canvas"
            class="signature-canvas"
            @mousedown="startDrawing"
            @mousemove="draw"
            @mouseup="stopDrawing"
            @mouseleave="stopDrawing"
            @touchstart.prevent="startDrawingTouch"
            @touchmove.prevent="drawTouch"
            @touchend="stopDrawing"
        ></canvas>
        <div class="signature-actions">
            <button type="button" class="btn-clear" @click="clear">
                <i class="fa fa-eraser"></i> Limpiar
            </button>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        width:    { type: Number, default: 400 },
        height:   { type: Number, default: 150 },
        penColor: { type: String, default: '#000000' },
        lineWidth: { type: Number, default: 2 },
    },
    data() {
        return {
            isDrawing: false,
            ctx: null,
            hasContent: false,
            resizeObserver: null,
        };
    },
    mounted() {
        const canvas = this.$refs.canvas;
        this.initCanvas(canvas);

        this.resizeObserver = new ResizeObserver(() => {
            this.resizeCanvas();
        });
        this.resizeObserver.observe(canvas);
    },
    beforeUnmount() {
        if (this.resizeObserver) {
            this.resizeObserver.disconnect();
        }
    },
    methods: {
        initCanvas(canvas) {
            const rect = canvas.getBoundingClientRect();
            canvas.width = rect.width;
            canvas.height = rect.height;
            this.ctx = canvas.getContext('2d');
            this.ctx.strokeStyle = this.penColor;
            this.ctx.lineWidth = this.lineWidth;
            this.ctx.lineCap = 'round';
            this.ctx.lineJoin = 'round';
        },
        resizeCanvas() {
            const canvas = this.$refs.canvas;
            if (!canvas) return;
            const imageData = this.hasContent ? canvas.toDataURL() : null;
            const rect = canvas.getBoundingClientRect();
            canvas.width = rect.width;
            canvas.height = rect.height;
            this.ctx = canvas.getContext('2d');
            this.ctx.strokeStyle = this.penColor;
            this.ctx.lineWidth = this.lineWidth;
            this.ctx.lineCap = 'round';
            this.ctx.lineJoin = 'round';
            if (imageData && this.hasContent) {
                const img = new Image();
                img.onload = () => {
                    this.ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                };
                img.src = imageData;
            }
        },
        getPos(e) {
            const canvas = this.$refs.canvas;
            const rect = canvas.getBoundingClientRect();
            const scaleX = canvas.width / rect.width;
            const scaleY = canvas.height / rect.height;
            return {
                x: (e.clientX - rect.left) * scaleX,
                y: (e.clientY - rect.top) * scaleY,
            };
        },
        getTouchPos(e) {
            const canvas = this.$refs.canvas;
            const rect = canvas.getBoundingClientRect();
            const touch = e.touches[0];
            const scaleX = canvas.width / rect.width;
            const scaleY = canvas.height / rect.height;
            return {
                x: (touch.clientX - rect.left) * scaleX,
                y: (touch.clientY - rect.top) * scaleY,
            };
        },
        startDrawing(e) {
            this.isDrawing = true;
            const pos = this.getPos(e);
            this.ctx.beginPath();
            this.ctx.moveTo(pos.x, pos.y);
        },
        draw(e) {
            if (!this.isDrawing) return;
            const pos = this.getPos(e);
            this.ctx.lineTo(pos.x, pos.y);
            this.ctx.stroke();
            this.hasContent = true;
        },
        startDrawingTouch(e) {
            this.isDrawing = true;
            const pos = this.getTouchPos(e);
            this.ctx.beginPath();
            this.ctx.moveTo(pos.x, pos.y);
        },
        drawTouch(e) {
            if (!this.isDrawing) return;
            const pos = this.getTouchPos(e);
            this.ctx.lineTo(pos.x, pos.y);
            this.ctx.stroke();
            this.hasContent = true;
        },
        stopDrawing() {
            this.isDrawing = false;
        },
        clear() {
            const canvas = this.$refs.canvas;
            this.ctx.clearRect(0, 0, canvas.width, canvas.height);
            this.hasContent = false;
            this.$emit('cleared');
        },
        toDataURL() {
            if (!this.hasContent) return null;
            return this.$refs.canvas.toDataURL('image/png');
        },
        isEmpty() {
            return !this.hasContent;
        },
    },
};
</script>

<style scoped>
.signature-pad-wrapper {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.signature-canvas {
    width: 100%;
    max-width: 400px;
    height: auto;
    aspect-ratio: 8/3;
    border: 2px dashed #cbd5e1;
    border-radius: 8px;
    cursor: crosshair;
    background: #fff;
    touch-action: none;
}

.signature-actions {
    display: flex;
    justify-content: flex-end;
}

.btn-clear {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.375rem 0.75rem;
    font-size: 0.8rem;
    color: #606060;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-clear:hover {
    background: #e2e8f0;
}
</style>
