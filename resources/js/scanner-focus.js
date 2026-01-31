/**
 * Scanner Focus Trap & Audio Feedback
 * 
 * Optimized for industrial Bluetooth/USB RF scanners (HID mode).
 * Features:
 * - Auto-refocus after scan submission
 * - Audible feedback (440Hz beep for success, 200Hz buzz for error)
 * - Rapid-fire scanning support
 */

class ScannerFocusTrap {
    constructor(inputSelector, options = {}) {
        this.input = document.querySelector(inputSelector);
        if (!this.input) return;

        this.options = {
            submitOnEnter: true,
            clearAfterScan: true,
            autoFocus: true,
            successBeep: true,
            errorBuzz: true,
            debounceMs: 100,
            onScan: null,
            onError: null,
            ...options
        };

        this.audioContext = null;
        this.lastScanTime = 0;

        this.init();
    }

    init() {
        // Ensure input is focused on page load
        if (this.options.autoFocus) {
            this.focus();
        }

        // Re-focus when clicking anywhere on the page
        document.addEventListener('click', (e) => {
            // Don't steal focus from other inputs
            if (e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA') {
                setTimeout(() => this.focus(), 10);
            }
        });

        // Handle barcode submission
        this.input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && this.options.submitOnEnter) {
                e.preventDefault();
                this.processScan();
            }
        });

        // Re-focus after any blur (tablets may lose focus)
        this.input.addEventListener('blur', () => {
            if (this.options.autoFocus) {
                setTimeout(() => this.focus(), 100);
            }
        });
    }

    focus() {
        this.input.focus();
        this.input.select();
    }

    async processScan() {
        const now = Date.now();
        // Debounce rapid scans
        if (now - this.lastScanTime < this.options.debounceMs) {
            return;
        }
        this.lastScanTime = now;

        const value = this.input.value.trim();
        if (!value) {
            this.playError();
            return;
        }

        try {
            if (typeof this.options.onScan === 'function') {
                const result = await this.options.onScan(value);
                if (result !== false) {
                    this.playSuccess();
                } else {
                    this.playError();
                }
            } else {
                // Default behavior: dispatch custom event
                const event = new CustomEvent('barcode-scanned', {
                    detail: { code: value },
                    bubbles: true
                });
                this.input.dispatchEvent(event);
                this.playSuccess();
            }
        } catch (error) {
            console.error('Scan processing error:', error);
            this.playError();
            if (typeof this.options.onError === 'function') {
                this.options.onError(error);
            }
        }

        // Clear and refocus for next scan
        if (this.options.clearAfterScan) {
            this.input.value = '';
        }
        this.focus();
    }

    /**
     * Play success beep (440Hz, 150ms)
     */
    playSuccess() {
        if (!this.options.successBeep) return;
        this.playTone(440, 150);

        // Visual feedback
        this.input.classList.add('bg-green-100');
        setTimeout(() => this.input.classList.remove('bg-green-100'), 200);
    }

    /**
     * Play error buzz (200Hz, 300ms)
     */
    playError() {
        if (!this.options.errorBuzz) return;
        this.playTone(200, 300);

        // Visual feedback
        this.input.classList.add('bg-red-100');
        setTimeout(() => this.input.classList.remove('bg-red-100'), 300);
    }

    /**
     * Generate tone using Web Audio API
     */
    playTone(frequency, duration) {
        try {
            if (!this.audioContext) {
                this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
            }

            const oscillator = this.audioContext.createOscillator();
            const gainNode = this.audioContext.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(this.audioContext.destination);

            oscillator.frequency.value = frequency;
            oscillator.type = 'sine';
            gainNode.gain.value = 0.3;

            oscillator.start();
            oscillator.stop(this.audioContext.currentTime + duration / 1000);
        } catch (e) {
            console.warn('Audio playback failed:', e);
        }
    }

    /**
     * Manually trigger focus (useful for form resets)
     */
    refocus() {
        this.focus();
    }

    /**
     * Destroy the focus trap
     */
    destroy() {
        if (this.audioContext) {
            this.audioContext.close();
        }
    }
}

// Auto-initialize on elements with data-scanner-input
document.addEventListener('DOMContentLoaded', () => {
    const scannerInputs = document.querySelectorAll('[data-scanner-input]');
    scannerInputs.forEach(input => {
        new ScannerFocusTrap('#' + input.id);
    });
});

// Export for module usage
export default ScannerFocusTrap;
