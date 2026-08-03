/**
 * VMS GO VISTA — Page Transition Animation
 * Bouncing dots SVG loader shown during page navigation
 * Works on all pages EXCEPT index-three.php (which has its own premium loader)
 */
(function () {
    // Don't run on index-three.php (it has its own premium loader)
    if (window.location.pathname.includes('index-three')) return;

    // Create the transition overlay
    var overlay = document.createElement('div');
    overlay.id = 'vms-page-transition';
    overlay.innerHTML = '<svg fill="#003A59FF" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" width="48" height="48"><circle cx="4" cy="12" r="3"><animate attributeName="cy" calcMode="spline" dur="0.6s" values="12;6;12" keySplines=".33,.66,.66,1;.33,0,.66,.33" repeatCount="indefinite" begin="0s"/></circle><circle cx="12" cy="12" r="3"><animate attributeName="cy" calcMode="spline" dur="0.6s" values="12;6;12" keySplines=".33,.66,.66,1;.33,0,.66,.33" repeatCount="indefinite" begin="0.1s"/></circle><circle cx="20" cy="12" r="3"><animate attributeName="cy" calcMode="spline" dur="0.6s" values="12;6;12" keySplines=".33,.66,.66,1;.33,0,.66,.33" repeatCount="indefinite" begin="0.2s"/></circle></svg>';
    document.body.appendChild(overlay);

    function showTransition() {
        overlay.classList.add('active');
    }

    function hideTransition() {
        overlay.classList.remove('active');
    }

    // Turbo events (primary method)
    if (typeof Turbo !== 'undefined' || typeof window.Turbo !== 'undefined') {
        document.addEventListener('turbo:before-visit', showTransition);
        document.addEventListener('turbo:load', hideTransition);
    }

    // Safety: auto-hide after 3 seconds if something goes wrong
    var safetyTimer = null;
    function startSafetyTimer() {
        clearTimeout(safetyTimer);
        safetyTimer = setTimeout(hideTransition, 3000);
    }

    // Show on any navigation attempt
    window.addEventListener('beforeunload', showTransition);

    // Fallback: hide on load for non-Turbo pages
    window.addEventListener('load', function () {
        setTimeout(hideTransition, 200);
    });

    // Handle back/forward navigation
    window.addEventListener('popstate', function () {
        showTransition();
        startSafetyTimer();
    });
})();
