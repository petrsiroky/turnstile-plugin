/* Cloudflare Turnstile Auto-Reset Helper for October CMS */
(function() {
    function resetTurnstile() {
        if (typeof window.turnstile !== 'undefined' && typeof window.turnstile.reset === 'function') {
            try {
                var widgets = document.querySelectorAll('.cf-turnstile');
                widgets.forEach(function(widget) {
                    window.turnstile.reset(widget);
                });
            } catch (e) {
                console.warn('Cloudflare Turnstile reset error:', e);
            }
        }
    }

    // October CMS Framework Events (jQuery)
    if (typeof jQuery !== 'undefined') {
        jQuery(document).on('ajaxError ajaxInvalid', function() {
            resetTurnstile();
        });
    }

    // Vanilla JS Event Listeners
    document.addEventListener('ajaxError', resetTurnstile);
    document.addEventListener('ajaxInvalid', resetTurnstile);
})();
