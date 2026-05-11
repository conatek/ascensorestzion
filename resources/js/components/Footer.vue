<template>
    <footer class="app-footer">
        <!-- Decorative top border -->
        <div class="footer-border"></div>

        <div class="footer-content">
            <div class="footer-left">
                <div class="footer-brand">
                    <svg class="footer-logo" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="24" height="24" rx="6" fill="url(#footer-gradient)"/>
                        <path d="M7 12C7 9.23858 9.23858 7 12 7C14.7614 7 17 9.23858 17 12C17 14.7614 14.7614 17 12 17" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                        <circle cx="12" cy="12" r="2.5" fill="white"/>
                        <defs>
                            <linearGradient id="footer-gradient" x1="0" y1="0" x2="24" y2="24">
                                <stop stop-color="#8b5cf6"/>
                                <stop offset="1" stop-color="#ec4899"/>
                            </linearGradient>
                        </defs>
                    </svg>
                    <span class="footer-brand-text">NexosCard</span>
                </div>
                <span class="footer-separator">|</span>
                <span class="footer-copy">
                    &copy; {{ currentYear }} Tarjetas de Presentacion Digital
                </span>
            </div>

            <div class="footer-right">
                <div class="footer-version">
                    <span class="version-label">Version</span>
                    <span class="version-badge">1.0.0</span>
                </div>
            </div>
        </div>

        <!-- Decorative elements -->
        <div class="footer-decoration">
            <div class="decoration-line line-1"></div>
            <div class="decoration-line line-2"></div>
        </div>
    </footer>
</template>

<script>
export default {
    name: 'AppFooter',
    computed: {
        currentYear() {
            return new Date().getFullYear();
        }
    }
}
</script>

<style scoped>
.app-footer {
    position: relative;
    background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
    height: 60px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

/* Decorative top border */
.footer-border {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg,
        transparent 0%,
        #8b5cf6 20%,
        #ec4899 50%,
        #8b5cf6 80%,
        transparent 100%
    );
    opacity: 0.6;
}

.footer-content {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 1.5rem;
    position: relative;
    z-index: 1;
}

/* Footer Left */
.footer-left {
    display: flex;
    align-items: center;
    gap: 0.875rem;
}

.footer-brand {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.footer-logo {
    width: 24px;
    height: 24px;
}

.footer-brand-text {
    font-size: 0.875rem;
    font-weight: 600;
    color: #1e293b;
}

.footer-separator {
    color: #cbd5e1;
    font-weight: 300;
}

.footer-copy {
    font-size: 0.8rem;
    color: #64748b;
}

/* Footer Right */
.footer-right {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.footer-version {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.version-label {
    font-size: 0.75rem;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.version-badge {
    font-size: 0.75rem;
    font-weight: 600;
    color: #8b5cf6;
    background: linear-gradient(135deg, #f3e8ff, #fce7f3);
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    border: 1px solid rgba(139, 92, 246, 0.2);
}

/* Decorative elements */
.footer-decoration {
    position: absolute;
    inset: 0;
    pointer-events: none;
    overflow: hidden;
}

.decoration-line {
    position: absolute;
    height: 1px;
    background: linear-gradient(90deg,
        transparent,
        rgba(139, 92, 246, 0.1),
        rgba(236, 72, 153, 0.1),
        transparent
    );
}

.line-1 {
    bottom: 20px;
    left: 10%;
    right: 60%;
}

.line-2 {
    bottom: 15px;
    left: 60%;
    right: 10%;
}

/* Responsive */
@media (max-width: 768px) {
    .footer-content {
        padding: 0 1rem;
    }

    .footer-separator,
    .footer-copy {
        display: none;
    }

    .version-label {
        display: none;
    }
}

@media (max-width: 480px) {
    .footer-brand-text {
        display: none;
    }
}
</style>
