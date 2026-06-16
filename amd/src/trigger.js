// local_adipaonboarding/trigger — boton "?" para relanzar el tour.
//
// 1 host por scope:
//   - course_view    : icono "?" circular dentro de `.adipa-s0-banner` (compacto,
//                      con color adaptado al brillo del banner via CSS).
//   - mod_adipavideo : link sutil "? Como usar mi aula" insertado DESPUES del
//                      hint "Tip: haz click..." (.adv-notes__help). Si ese host
//                      no existe (capsula sin panel apuntes activo), fallback a
//                      despues de `.adv-tabs`.
//
// Si ningun host existe, NO se renderiza el boton.
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

    // Link sutil para mod_adipavideo: icono "?" inline + texto pequeño que se
    // siente parte del flujo de apuntes, no un CTA dominante. Sin fondo/borde.
    function buildSubtleLink(payload) {
        var link = document.createElement('button');
        link.id = BUTTON_ID;
        link.type = 'button';
        link.className = 'adipa-onboarding-trigger-link';
        link.setAttribute('aria-label', payload.i18n.trigger);
        link.title = payload.i18n.trigger;
        var icon = document.createElement('span');
        icon.className = 'adipa-onboarding-trigger-link__icon';
        icon.textContent = '?';
        icon.setAttribute('aria-hidden', 'true');
        var text = document.createElement('span');
        text.className = 'adipa-onboarding-trigger-link__text';
        text.textContent = payload.i18n.trigger;
        link.appendChild(icon);
        link.appendChild(text);
        link.addEventListener('click', function() {
            Runner.run(payload);
        });
        return link;
    }

    function isVideoViewer(payload) {
        return payload && payload.id && payload.id.indexOf('mod_adipavideo') === 0;
    }

    function inject(payload) {
        if (document.getElementById(BUTTON_ID)) {
            return;
        }
        if (isVideoViewer(payload)) {
            // Preferido: justo despues del Tip "haz click en cualquier chip..." dentro del panel apuntes.
            var hintHost = document.querySelector('.adv-notes__help');
            if (hintHost && hintHost.parentNode) {
                hintHost.parentNode.insertBefore(buildSubtleLink(payload), hintHost.nextSibling);
                return;
            }
            // Fallback: capsula sin panel apuntes (raro) — usar despues de tabs.
            var tabsHost = document.querySelector('.adv-tabs');
            if (tabsHost && tabsHost.parentNode) {
                tabsHost.parentNode.insertBefore(buildSubtleLink(payload), tabsHost.nextSibling);
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
