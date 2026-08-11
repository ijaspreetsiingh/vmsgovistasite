/**
 * VMS GO VISTA — Corporate Loader
 * Uses static HTML in <body>, just animates progress bar
 */
class VMSLoader {
    constructor(opts = {}) {
        this.onReady   = opts.onReady   || null;
        this._duration = opts.duration  || 1400; // ms
        this.overlay   = document.getElementById('vmsPreloader');
        this.fill      = document.getElementById('vmsLoaderFill');
    }

    play() {
        if (!this.overlay || !this.fill) return;
        const start = performance.now();
        const dur = this._duration;

        const tick = (now) => {
            const elapsed = now - start;
            const progress = Math.min(elapsed / dur, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            this.fill.style.width = (eased * 100) + '%';

            if (progress < 1) {
                requestAnimationFrame(tick);
            } else {
                setTimeout(() => this.hide(), 200);
            }
        };
        requestAnimationFrame(tick);
    }

    hide() {
        if (!this.overlay) return;
        this.overlay.classList.add('hidden');
        setTimeout(() => {
            this.overlay.remove();
            if (this.onReady) this.onReady();
        }, 500);
    }
}

window.VMSLoader = VMSLoader;
