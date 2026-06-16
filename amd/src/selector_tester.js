// local_adipaonboarding/selector_tester — overlay que resalta matches de un CSS
// selector en la pagina actual. Disparado via query param desde el editor.
define([
    'core/str',
    'core/log'
], function(Str, Log) {
    'use strict';

    var STYLES_ID = 'adipa-onboarding-styles';

    function ensureStyles() {
        if (document.getElementById(STYLES_ID)) {
            return;
        }
        var link = document.createElement('link');
        link.id = STYLES_ID;
        link.rel = 'stylesheet';
        link.href = M.cfg.wwwroot + '/local/adipaonboarding/styles.css';
        document.head.appendChild(link);
    }

    function showError(selector, errorMessage) {
        Str.get_strings([
            {key: 'selector_tester_error', component: 'local_adipaonboarding'},
            {key: 'selector_tester_close', component: 'local_adipaonboarding'}
        ]).then(function(strs) {
            renderBanner(strs[0] + ': ' + errorMessage + ' — ' + selector, strs[1], true);
            return null;
        }).catch(function() {});
    }

    function showResult(selector, count) {
        Str.get_strings([
            {key: 'selector_tester_matches', component: 'local_adipaonboarding', param: count},
            {key: 'selector_tester_close', component: 'local_adipaonboarding'}
        ]).then(function(strs) {
            renderBanner(strs[0] + ' — ' + selector, strs[1], false);
            return null;
        }).catch(function() {});
    }

    function renderBanner(message, closeLabel, isError) {
        var banner = document.createElement('div');
        banner.className = 'adipa-selector-tester-banner' + (isError ? ' error' : '');

        var text = document.createElement('div');
        // Reemplazar el selector dentro del mensaje por un <code> para resaltar.
        var parts = message.split(' — ');
        var label = document.createElement('span');
        label.textContent = parts[0];
        text.appendChild(label);
        if (parts.length > 1) {
            text.appendChild(document.createTextNode(' '));
            var code = document.createElement('code');
            code.textContent = parts[1];
            text.appendChild(code);
        }
        banner.appendChild(text);

        var closeBtn = document.createElement('button');
        closeBtn.type = 'button';
        closeBtn.className = 'adipa-selector-tester-close';
        closeBtn.textContent = closeLabel;
        closeBtn.addEventListener('click', function() {
            cleanup();
        });
        banner.appendChild(closeBtn);

        document.body.appendChild(banner);
        document.body.classList.add('adipa-selector-tester-active');
    }

    function highlightMatches(selector) {
        var matches;
        try {
            matches = document.querySelectorAll(selector);
        } catch (e) {
            showError(selector, e.message);
            return 0;
        }
        matches.forEach(function(el, idx) {
            el.classList.add('adipa-selector-tester-match');
            el.setAttribute('data-adipa-match-index', idx + 1);
            el.scrollIntoView && idx === 0 && el.scrollIntoView({behavior: 'smooth', block: 'center'});
        });
        return matches.length;
    }

    function cleanup() {
        var banner = document.querySelector('.adipa-selector-tester-banner');
        if (banner) {
            banner.remove();
        }
        document.body.classList.remove('adipa-selector-tester-active');
        document.querySelectorAll('.adipa-selector-tester-match').forEach(function(el) {
            el.classList.remove('adipa-selector-tester-match');
            el.removeAttribute('data-adipa-match-index');
        });
    }

    return {
        init: function(selector) {
            if (!selector) {
                return;
            }
            ensureStyles();
            // Esperar a que el DOM termine de cargar antes de matchear (por si el
            // contenido del curso se inyecta async via AMD modules de format_adipa).
            var run = function() {
                var count = highlightMatches(selector);
                if (count >= 0) {
                    showResult(selector, count);
                }
            };
            if (document.readyState === 'complete') {
                // Pequeno delay para que JS de la pagina termine de manipular DOM.
                setTimeout(run, 500);
            } else {
                window.addEventListener('load', function() {
                    setTimeout(run, 500);
                });
            }
        }
    };
});
