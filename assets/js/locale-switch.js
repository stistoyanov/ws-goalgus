(function () {
    'use strict';

    function closeAll(except) {
        document.querySelectorAll('[data-locale-switch][open]').forEach(function (el) {
            if (el !== except) {
                el.removeAttribute('open');
            }
        });
    }

    document.addEventListener('click', function (event) {
        var target = event.target;
        if (!(target instanceof Element)) {
            return;
        }
        var open = target.closest('[data-locale-switch][open]');
        if (!open) {
            closeAll(null);
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeAll(null);
        }
    });

    document.querySelectorAll('[data-locale-switch]').forEach(function (el) {
        el.addEventListener('toggle', function () {
            if (el.open) {
                closeAll(el);
            }
        });
    });
})();
