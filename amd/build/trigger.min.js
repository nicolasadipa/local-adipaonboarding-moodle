// local_adipaonboarding/trigger — boton "?" para relanzar el tour.
//
// 1 host por scope, sin cascada ni fallback:
//   - course_view    : icono "?" circular dentro de `.adipa-s0-banner`.
//   - mod_adipavideo : tip wrap "?  ¿Como usar el aula?" despues de `.adv-tabs`.
//
// Si el host no existe, NO se renderiza el boton (es un caso edge poco probable
// con format_adipa + mod_adipavideo correctamente instalados).
define([
    'local_adipaonboarding/runner'
], function(Runner) {
    'use strict';

    var BUTTON_ID = 'adipa-onboarding-trigger';

    function buildIcon(payload) {
        var btn = document.createElement('button');
        btn.id = BUTTON_ID;
        btn.type = 'button';
        btn.className = 'adipa-onboarding-trigger adipa-onboarding-trigger--icon';
        btn.setAttribute('aria-label', payload.i18n.trigger);
        btn.title = payload.i18n.trigger;
        btn.textContent = '?';
        btn.addEventListener('click', function() {
            Runner.run(payload);
        });
        return btn;
    }

    function buildTipWrap(payload) {
        var wrap = document.createElement('div');
        wrap.id = BUTTON_ID;
        wrap.className = 'adipa-onboarding-trigger-wrap';
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'adipa-onboarding-trigger adipa-onboarding-trigger--icon';
        btn.setAttribute('aria-label', payload.i18n.trigger);
        btn.title = payload.i18n.trigger;
        btn.textContent = '?';
        btn.addEventListener('click', function() {
            Runner.run(payload);
        });
        wrap.appendChild(btn);
        var text = document.createElement('span');
        text.className = 'adipa-onboarding-trigger__tip-text';
        text.textContent = payload.i18n.trigger;
        wrap.appendChild(text);
        return wrap;
    }

    function isVideoViewer(payload) {
        return payload && payload.id && payload.id.indexOf('mod_adipavideo') === 0;
    }

    function inject(payload) {
        if (document.getElementById(BUTTON_ID)) {
            return;
        }
        if (isVideoViewer(payload)) {
            var tabsHost = document.querySelector('.adv-tabs');
            if (tabsHost && tabsHost.parentNode) {
                tabsHost.parentNode.insertBefore(buildTipWrap(payload), tabsHost.nextSibling);
            }
            return;
        }
        // course_view: dentro del banner del programa.
        var banner = document.querySelector('.adipa-s0-banner');
        if (banner) {
            banner.appendChild(buildIcon(payload));
        }
    }

    return {
        init: function() {
            var payload = Runner.readManifest && Runner.readManifest();
            if (!payload) {
                return;
            }
            Runner.ensureStyles();
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', function() {
                    inject(payload);
                });
            } else {
                inject(payload);
            }
        }
    };
});
