/**
 * VMS GO VISTA — Ultra-Premium Loader Animation
 * Apple Motion Language | Seamless SVG Morphing | 60fps
 * 
 * Scenes:
 * 1. Airplane (continuous line draw)
 * 2. Car (morph from airplane)
 * 3. Hotel (morph from car)
 * 4. Hotel details animate in
 * 5. Collapse into logo icon → full logo reveal
 * Loop: Logo → airplane (seamless)
 */

class VMSUltraLoader {
    constructor(opts = {}) {
        this.logoSrc   = opts.logoSrc   || 'assets/loader-logo.png';
        this.holdTime  = opts.holdTime  || 1800;
        this.loop      = opts.loop !== false;
        this.onReady   = opts.onReady   || null;
        this._build();
        this._measure();
    }

    /* ── DOM Construction ────────────────────────────── */
    _build() {
        // Overlay
        this.overlay = document.createElement('div');
        this.overlay.className = 'vms-preloader';
        this.overlay.innerHTML = `
<div class="vms-preloader-stage">
  <svg class="vms-preloader-svg" viewBox="0 0 800 450" xmlns="http://www.w3.org/2000/svg">

    <!-- SCENE 1 — Airplane -->
    <g class="vms-scene active" data-scene="0">
      <path class="vms-line-primary vms-draw" data-shape="airplane"
        d="M200,225 Q260,185 340,195 L420,160 L460,180 L430,195 L500,175 L540,190
           L510,200 L580,195 L620,210 L580,215 L510,210 L540,220 L500,230
           L430,215 L460,240 L420,240 L340,230 Q280,240 200,225Z
           M340,195 L340,245 M420,175 L420,245"/>
    </g>

    <!-- SCENE 2 — Car -->
    <g class="vms-scene" data-scene="1">
      <path class="vms-line-primary vms-draw" data-shape="car"
        d="M220,260 L260,260 L290,210 L370,195 L430,195 L510,210 L540,260
           L580,260 L590,280 L210,280 L220,260Z
           M260,280 A25,25 0 1,0 310,280 A25,25 0 1,0 260,280
           M490,280 A25,25 0 1,0 540,280 A25,25 0 1,0 490,280
           M310,210 L490,210 L510,260 L290,260Z"/>
    </g>

    <!-- SCENE 3 — Hotel -->
    <g class="vms-scene" data-scene="2">
      <path class="vms-line-primary vms-draw" data-shape="hotel"
        d="M280,290 L280,160 L400,110 L520,160 L520,290Z
           M280,160 L400,100 L520,160
           M320,180 L320,220 L360,220 L360,180Z
           M440,180 L440,220 L480,220 L480,180Z
           M320,240 L320,275 L360,275 L360,240Z
           M440,240 L440,275 L480,275 L480,240Z
           M370,240 L370,290 L430,290 L430,240Z"/>
    </g>

    <!-- SCENE 4 — Hotel Details (accent) -->
    <g class="vms-scene" data-scene="3">
      <path class="vms-line-primary vms-draw" data-shape="hotel"
        d="M280,290 L280,160 L400,110 L520,160 L520,290Z
           M280,160 L400,100 L520,160
           M320,180 L320,220 L360,220 L360,180Z
           M440,180 L440,220 L480,220 L480,180Z
           M320,240 L320,275 L360,275 L360,240Z
           M440,240 L440,275 L480,275 L480,240Z
           M370,240 L370,290 L430,290 L430,240Z"/>
      <path class="vms-line-accent vms-draw" data-shape="hotel-details"
        d="M290,175 L290,225 M510,175 L510,225
           M290,245 L290,280 M510,245 L510,280
           M335,195 L335,210 M465,195 L465,210
           M335,255 L335,270 M465,255 L465,270
           M380,115 L380,130 M420,115 L420,130
           M365,250 L435,250"/>
    </g>

    <!-- SCENE 5 — Logo Icon (circle + mountain) -->
    <g class="vms-scene" data-scene="4">
      <path class="vms-line-primary vms-draw" data-shape="logo-circle"
        d="M400,140 A90,90 0 1,1 399.99,140"/>
      <path class="vms-line-primary vms-draw" data-shape="logo-icon"
        d="M345,250 L380,185 L400,210 L425,170 L455,250Z M440,165 A8,8 0 1,1 456,165"/>
    </g>
  </svg>

  <div class="vms-preloader-logo" id="vmsFinalLogo">
    <img src="${this.logoSrc}" alt="VMS GO VISTA">
  </div>

  <div class="vms-preloader-progress">
    <span class="vms-progress-dot" data-i="0"></span>
    <span class="vms-progress-dot" data-i="1"></span>
    <span class="vms-progress-dot" data-i="2"></span>
    <span class="vms-progress-dot" data-i="3"></span>
    <span class="vms-progress-dot" data-i="4"></span>
  </div>
</div>
<div class="vms-preloader-brand">VMS GO VISTA</div>`;
        document.body.prepend(this.overlay);

        // Cache
        this.logoEl   = this.overlay.querySelector('#vmsFinalLogo');
        this.scenes   = [...this.overlay.querySelectorAll('.vms-scene')];
        this.dots     = [...this.overlay.querySelectorAll('.vms-progress-dot')];
        this.allPaths = [...this.overlay.querySelectorAll('.vms-draw')];
    }

    /* ── Measure path lengths ──────────────────────────── */
    _measure() {
        this.allPaths.forEach(p => {
            const len = Math.ceil(p.getTotalLength());
            p.style.setProperty('--path-length', len);
            p.style.strokeDasharray  = len;
            p.style.strokeDashoffset = len;
        });
    }

    /* ── Scene orchestration ───────────────────────────── */
    async play() {
        for (let i = 0; i < this.scenes.length; i++) {
            await this._showScene(i);
            await this._wait(i === this.scenes.length - 1 ? this.holdTime : 600);
        }
        await this._revealLogo();
        if (this.loop) await this._loopBack();
    }

    _showScene(idx) {
        return new Promise(resolve => {
            // Update dots
            this.dots.forEach((d, i) => {
                d.classList.toggle('active', i === idx);
                d.classList.toggle('done',   i < idx);
            });

            // Transition scenes
            this.scenes.forEach((s, i) => {
                s.classList.remove('active', 'exit');
                if (i < idx) s.classList.add('exit');
            });
            this.scenes[idx].classList.add('active');

            // Draw paths in current scene
            const paths = this.scenes[idx].querySelectorAll('.vms-draw');
            let delay = 0;
            paths.forEach(p => {
                setTimeout(() => p.classList.add('drawing'), delay);
                delay += 180;
            });

            // Erase previous scene paths (except scene 4 which keeps hotel)
            if (idx > 0 && idx !== 3) {
                const prev = this.scenes[idx - 1].querySelectorAll('.vms-draw');
                prev.forEach(p => {
                    p.classList.remove('drawing');
                    p.classList.add('erase');
                });
            }

            setTimeout(resolve, delay + 700);
        });
    }

    _revealLogo() {
        return new Promise(resolve => {
            // Erase scene 5 paths
            this.scenes[4].querySelectorAll('.vms-draw').forEach(p => {
                p.classList.remove('drawing');
                p.classList.add('erase');
            });
            this.scenes[4].classList.remove('active');

            // Mark all dots done
            this.dots.forEach(d => { d.classList.remove('active'); d.classList.add('done'); });

            // Show logo
            this.overlay.classList.add('loaded');
            setTimeout(() => {
                this.logoEl.classList.add('visible');
                resolve();
            }, 300);
        });
    }

    _loopBack() {
        return new Promise(resolve => {
            setTimeout(() => {
                // Hide logo
                this.logoEl.classList.remove('visible');
                this.overlay.classList.remove('loaded');

                // Reset all paths
                this.allPaths.forEach(p => {
                    p.classList.remove('drawing', 'erase');
                });

                // Reset dots
                this.dots.forEach(d => { d.classList.remove('active', 'done'); });

                setTimeout(() => {
                    this.play().then(resolve);
                }, 600);
            }, 3000);
        });
    }

    /* ── Utilities ──────────────────────────────────────── */
    _wait(ms) { return new Promise(r => setTimeout(r, ms)); }

    hide() {
        this.overlay.classList.add('hidden');
        setTimeout(() => {
            this.overlay.remove();
            if (this.onReady) this.onReady();
        }, 900);
    }

    destroy() {
        this.loop = false;
        this.overlay.remove();
    }
}

/* ── Auto-init helper ──────────────────────────────── */
window.VMSUltraLoader = VMSUltraLoader;
