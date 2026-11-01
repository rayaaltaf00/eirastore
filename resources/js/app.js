

import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

Alpine.plugin(collapse);

/**
 * ============================================================================
 * EiraStore UI Micro-Interactions & Security Utilities (Tahap 2)
 * ============================================================================
 */

/**
 * Global Toast Dispatcher
 * Allows any part of the application (JS, Alpine, event listeners) to trigger toasts.
 * 
 * @param {'success'|'error'|'warning'|'info'} type 
 * @param {string} title 
 * @param {string} message 
 * @param {number} [duration=4500] 
 */
window.showToast = function (type, title, message, duration = 4500) {
    window.dispatchEvent(new CustomEvent('toast', {
        detail: { type, title, message, duration }
    }));
};

/**
 * Universal Secure Copy to Clipboard
 * - Safe against non-secure contexts (HTTP fallback)
 * - Sanitizes input string
 * - Triggers global toast notification for instant user feedback
 * 
 * @param {string} text 
 * @param {string} label 
 */
window.copyToClipboard = function (text, label = 'Teks') {
    if (!text && text !== 0) return;
    const cleanText = String(text).trim();

    const notifySuccess = () => {
        window.showToast('success', 'Berhasil Disalin!', `${label} berhasil disalin ke clipboard.`);
    };

    const notifyError = () => {
        window.showToast('error', 'Gagal Menyalin', 'Browser membatasi akses salin otomatis. Silakan salin secara manual.');
    };

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(cleanText)
            .then(notifySuccess)
            .catch(() => fallbackCopy(cleanText, notifySuccess, notifyError));
    } else {
        fallbackCopy(cleanText, notifySuccess, notifyError);
    }

    function fallbackCopy(val, onSuccess, onError) {
        try {
            const textarea = document.createElement('textarea');
            textarea.value = val;
            textarea.setAttribute('readonly', '');
            textarea.style.position = 'fixed';
            textarea.style.left = '-9999px';
            textarea.style.top = '-9999px';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            const successful = document.execCommand('copy');
            document.body.removeChild(textarea);
            if (successful) {
                onSuccess();
            } else {
                onError();
            }
        } catch (e) {
            onError();
        }
    }
};

/**
 * Toast Manager (Alpine Component)
 * Manages toast stack, animations, timers, and hover-pause behavior.
 * Pure text rendering via x-text ensures 100% XSS immunity.
 */
window.toastManager = function (initialToasts = []) {
    return {
        toasts: [],
        nextId: 1,

        init() {
            if (Array.isArray(initialToasts) && initialToasts.length > 0) {
                initialToasts.forEach((item, index) => {
                    setTimeout(() => {
                        this.add(item);
                    }, index * 200);
                });
            }
        },

        add(detail) {
            if (!detail) return;
            const id = this.nextId++;
            const type = detail.type || 'info';
            const duration = detail.duration || 4500;

            const toast = {
                id,
                type,
                title: detail.title || this.defaultTitle(type),
                message: detail.message || '',
                visible: true,
                timeoutId: null,
                remaining: duration,
                startTime: Date.now()
            };

            this.toasts.push(toast);
            this.startTimer(toast);
        },

        startTimer(toast) {
            toast.startTime = Date.now();
            toast.timeoutId = setTimeout(() => {
                this.remove(toast.id);
            }, toast.remaining);
        },

        pauseToast(toast) {
            if (toast.timeoutId) {
                clearTimeout(toast.timeoutId);
                toast.remaining -= (Date.now() - toast.startTime);
                if (toast.remaining < 1000) toast.remaining = 1000;
            }
        },

        resumeToast(toast) {
            this.startTimer(toast);
        },

        remove(id) {
            const index = this.toasts.findIndex(t => t.id === id);
            if (index !== -1) {
                this.toasts[index].visible = false;
                setTimeout(() => {
                    this.toasts = this.toasts.filter(t => t.id !== id);
                }, 300);
            }
        },

        defaultTitle(type) {
            switch (type) {
                case 'success': return 'Berhasil';
                case 'error': return 'Terjadi Kesalahan';
                case 'warning': return 'Peringatan';
                default: return 'Informasi';
            }
        },

        colorClasses(type) {
            switch (type) {
                case 'success':
                    return 'bg-emerald-950/95 border-emerald-500/50 text-emerald-200 shadow-emerald-950/60 ring-1 ring-emerald-500/20';
                case 'error':
                    return 'bg-rose-950/95 border-rose-500/50 text-rose-200 shadow-rose-950/60 ring-1 ring-rose-500/20';
                case 'warning':
                    return 'bg-amber-950/95 border-amber-500/50 text-amber-200 shadow-amber-950/60 ring-1 ring-amber-500/20';
                default:
                    return 'bg-slate-900/95 border-brand-cyan/50 text-slate-200 shadow-brand-cyan/20 ring-1 ring-brand-cyan/20';
            }
        }
    };
};

/**
 * Double-Submission Prevention & Button Loading States
 * 
 * Attaches to any form with `data-loading` attribute.
 * - Validates HTML5 form validity before triggering.
 * - Blocks second submit attempts.
 * - Swaps submit button content with smooth spinning indicator and loading label.
 * - Restores original button state cleanly if restored from browser back/forward cache (bfcache).
 */
document.addEventListener('DOMContentLoaded', () => {
    document.addEventListener('submit', (e) => {
        const form = e.target;
        if (!form || !(form instanceof HTMLFormElement)) return;
        if (!form.hasAttribute('data-loading')) return;

        // Jangan blokir jika browser validation gagal
        if (form.checkValidity && !form.checkValidity()) {
            return;
        }

        // Cegah double-submission / race conditions
        if (form.dataset.submitting === 'true') {
            e.preventDefault();
            return;
        }

        form.dataset.submitting = 'true';

        // Temukan tombol submit dalam formulir
        const submitBtn = form.querySelector('button[type="submit"]:not([data-no-loading]), input[type="submit"]:not([data-no-loading])');
        if (submitBtn) {
            const loadingText = submitBtn.dataset.loadingText || form.dataset.loadingText || 'Memproses...';
            submitBtn.dataset.originalContent = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-75', 'cursor-not-allowed', 'pointer-events-none');

            submitBtn.innerHTML = `
                <span class="inline-flex items-center justify-center gap-2">
                    <svg class="animate-spin h-4 w-4 text-current shrink-0" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>${loadingText}</span>
                </span>
            `;
        }
    }, false);

    // Pemulihan tombol jika pengguna kembali dengan tombol Back browser (bfcache)
    window.addEventListener('pageshow', () => {
        document.querySelectorAll('form[data-submitting="true"]').forEach((form) => {
            delete form.dataset.submitting;
            const submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
            if (submitBtn && submitBtn.dataset.originalContent) {
                submitBtn.innerHTML = submitBtn.dataset.originalContent;
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-75', 'cursor-not-allowed', 'pointer-events-none');
            }
        });
    });
});

window.Alpine = Alpine;

Alpine.start();
