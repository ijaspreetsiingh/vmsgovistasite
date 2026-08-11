/**
 *
 * -----------------------------------------------------------------------------
 *
 * Template : Touriza HTML Template
 * Author : themewant
 * Author URI : https://themewant.com/ 
 *
 * -----------------------------------------------------------------------------
 *
 **/

(function ($) {
    'use strict';
    // Get the form (static contact pages only — skip enquiry / placeholder actions).
    var form = $('#contact-form');
    var action = (form.attr('action') || '').trim();
    if (!form.length || action === '' || action === '#') {
        return;
    }
    // Get the messages div.
    var formMessages = $('#form-messages');
    var submitBtn = form.find('#contactSubmitBtn');

    // ── Button state helpers ──────────────────────────────
    function setButtonState(state) {
        if (!submitBtn.length) return;
        submitBtn.removeClass('loading success');
        submitBtn.prop('disabled', state !== 'idle');
        submitBtn.attr('aria-busy', state === 'loading' ? 'true' : 'false');
        if (state === 'loading') submitBtn.addClass('loading');
        if (state === 'success') submitBtn.addClass('success');
    }

    $(form).submit(function (e) {
        // Stop the browser from submitting the form.
        e.preventDefault();

        // Serialize the form data.
        var formData = $(form).serialize();

        setButtonState('loading');

        // Submit the form using AJAX.
        $.ajax({
                type: 'POST',
                url: action,
                data: formData
            })
            .done(function (response) {
                setButtonState('success');

                // Make sure that the formMessages div has the 'success' class.
                $(formMessages).removeClass('error');
                $(formMessages).addClass('success');

                // Set the message text.
                $(formMessages).text(response);

                // Clear the form.
                $('#name, #email, #company, #website, #message').val('');

                // Let the checkmark pop finish, then throw the party.
                setTimeout(function () {
                    showPartyPopup();
                }, 450);
            })
            .fail(function (data) {
                // Reset the button so the user can try again.
                setButtonState('idle');

                // Make sure that the formMessages div has the 'error' class.
                $(formMessages).removeClass('success');
                $(formMessages).addClass('error');

                // Set the message text.
                if (data.responseText !== '') {
                    $(formMessages).text(data.responseText);
                } else {
                    $(formMessages).text('Oops! An error occured and your message could not be sent.');
                }
            });
    });

    // ── Party success popup ────────────────────────────────
    function showPartyPopup() {
        var overlay = $('#vmsPartyOverlay');
        if (!overlay.length) return;
        // Only one success message — clear the inline feedback
        $(formMessages).removeClass('success error').text('');
        overlay.addClass('active').attr('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        startConfetti();
        // Move focus into the dialog for accessibility
        setTimeout(function () { $('#vmsPartyOk').trigger('focus'); }, 60);
        // Auto-stop confetti after ~8s to avoid pointless frame cost
        setTimeout(function () { if (overlay.hasClass('active')) stopConfetti(); }, 8000);
    }

    function hidePartyPopup() {
        var overlay = $('#vmsPartyOverlay');
        if (!overlay.length) return;
        overlay.removeClass('active').attr('aria-hidden', 'true');
        document.body.style.overflow = '';
        stopConfetti();
        // Return focus to the submit button (skip while it is disabled in the success state)
        if (submitBtn.length && !submitBtn.prop('disabled')) submitBtn.trigger('focus');
    }

    $('#vmsPartyClose, #vmsPartyOk').on('click', hidePartyPopup);
    $('#vmsPartyOverlay').on('click', function (e) {
        if (e.target === this) hidePartyPopup();
    });
    $(document).on('keydown', function (e) {
        if (e.key === 'Escape') hidePartyPopup();
    });

    // ── Confetti (vanilla canvas — zero dependencies) ──────
    var confetti = null;

    function startConfetti() {
        stopConfetti();
        var canvas = document.createElement('canvas');
        canvas.id = 'vmsConfettiCanvas';
        document.body.appendChild(canvas);
        confetti = createConfetti(canvas);
    }

    function stopConfetti() {
        if (confetti) { confetti.stop(); confetti = null; }
        var c = document.getElementById('vmsConfettiCanvas');
        if (c) c.remove();
    }

    function createConfetti(canvas) {
        var ctx = canvas.getContext('2d');
        var W = 0, H = 0, running = true, pieces = [];
        var colors = ['#003A59', '#C9A567', '#FF6B35', '#0A4D68', '#7BD3EA', '#F5B841', '#1a9e5c', '#e07c00'];

        function resize() {
            W = canvas.width = window.innerWidth;
            H = canvas.height = window.innerHeight;
        }
        resize();
        window.addEventListener('resize', resize);

        function spawn(x, y, count, burst) {
            for (var i = 0; i < count; i++) {
                pieces.push({
                    x: x + (Math.random() - 0.5) * 120,
                    y: y + (Math.random() - 0.5) * 60,
                    vx: (Math.random() - 0.5) * (burst ? 14 : 6),
                    vy: burst ? (Math.random() * -13) - 2 : 2 + Math.random() * 4,
                    w: 6 + Math.random() * 7,
                    h: 3 + Math.random() * 6,
                    rot: Math.random() * Math.PI * 2,
                    vr: (Math.random() - 0.5) * 0.3,
                    color: colors[Math.floor(Math.random() * colors.length)],
                    life: 0,
                    maxLife: burst ? 100 + Math.random() * 40 : 150,
                    shape: Math.random() > 0.4 ? 'rect' : 'circle'
                });
            }
        }

        // Initial burst from the center
        spawn(W / 2, H * 0.45, 130, true);

        // Occasional falling confetti to keep the party going
        var burstTimer = setInterval(function () {
            if (!running) return;
            spawn(W * Math.random(), -40, 22, false);
        }, 900);

        function frame() {
            if (!running) return;
            ctx.clearRect(0, 0, W, H);
            pieces = pieces.filter(function (p) {
                p.life++;
                p.x += p.vx;
                p.y += p.vy;
                p.vy += 0.22; // gravity
                p.vx *= 0.985;
                p.rot += p.vr;
                var alpha = 1 - (p.life / p.maxLife);
                if (alpha <= 0) return false;
                ctx.save();
                ctx.translate(p.x, p.y);
                ctx.rotate(p.rot);
                ctx.globalAlpha = Math.max(0, alpha);
                ctx.fillStyle = p.color;
                if (p.shape === 'circle') {
                    ctx.beginPath();
                    ctx.arc(0, 0, p.w / 2, 0, Math.PI * 2);
                    ctx.fill();
                } else {
                    ctx.fillRect(-p.w / 2, -p.h / 2, p.w, p.h);
                }
                ctx.restore();
                return true;
            });
            requestAnimationFrame(frame);
        }
        frame();

        return {
            stop: function () {
                running = false;
                clearInterval(burstTimer);
                window.removeEventListener('resize', resize);
            }
        };
    }

})(jQuery);
