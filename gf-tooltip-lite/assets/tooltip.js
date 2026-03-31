/**
 * GF Tooltip Lite — lightweight JS for accessibility.
 * Hover is handled entirely by CSS; this script adds:
 *  - keyboard (focus/blur) toggle
 *  - touch support (tap to toggle)
 */
(function () {
    'use strict';

    function init() {
        document.querySelectorAll('.gftl-tooltip-wrap').forEach(function (wrap) {
            // Touch: toggle visibility on tap.
            wrap.addEventListener('click', function (e) {
                e.stopPropagation();
                wrap.classList.toggle('is-visible');
            });

            // Keyboard: show on focus, hide on blur.
            wrap.addEventListener('focus', function () {
                wrap.classList.add('is-visible');
            });
            wrap.addEventListener('blur', function () {
                wrap.classList.remove('is-visible');
            });
        });

        // Dismiss any open tooltip when clicking elsewhere.
        document.addEventListener('click', function () {
            document.querySelectorAll('.gftl-tooltip-wrap.is-visible').forEach(function (wrap) {
                wrap.classList.remove('is-visible');
            });
        });
    }

    // Run after DOM is ready and also after GF renders a form via AJAX.
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Re-init if Gravity Forms reloads a page via AJAX (multi-page forms).
    if (window.jQuery) {
        jQuery(document).on('gform_post_render', init);
    }
}());
