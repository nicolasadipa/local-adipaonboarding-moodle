// local_adipaonboarding/runner — Driver.js orchestrator.
//
// Flujo lineal:
//   1. Lee payload del DOM (lib.php embebe <script type="application/json">).
//   2. Filtra steps cuyo target no es visible (excepto los que tienen pre-actions
//      click que pueden hacer aparecer al target).
//   3. Construye config Driver.js + override del ultimo step para "Finalizar".
//   4. Corre tour. Reporta telemetria viewed/completed/dismissed.
//
// Tipos de pre-action soportados: 'click' (con selector) y 'wait' (con ms).
define([
    'core/ajax',
    'core/log'
], function(Ajax, Log) {
    'use strict';

    // Cache buster con jsrev: los bundles vendoreados van via <script src>, no
    // pasan por el pipeline AMD de Moodle, asi que el browser los cachea hasta
    // que la URL cambie. Adjuntar ?v=jsrev fuerza refresh al purgear caches.
    var CACHE_BUSTER  = '?v=' + (M.cfg.jsrev || '0');
    var DRIVER_JS_URL  = M.cfg.wwwroot + '/local/adipaonboarding/lib/driver.js.iife.js' + CACHE_BUSTER;
    var DRIVER_CSS_URL = M.cfg.wwwroot + '/local/adipaonboarding/lib/driver.css'        + CACHE_BUSTER;
    var STYLES_ID = 'adipa-onboarding-styles';
    var MANIFEST_DOM_ID = 'adipa-onboarding-manifest';
    var ALLOWED_ACTION_TYPES = ['click', 'wait'];

    var driverPromise = null;

    function loadStyles() {
        if (document.getElementById(STYLES_ID)) {
            return;
        }
        var link = document.createElement('link');
        link.id = STYLES_ID;
        link.rel = 'stylesheet';
        link.href = M.cfg.wwwroot + '/local/adipaonboarding/styles.css' + CACHE_BUSTER;
        document.head.appendChild(link);
    }

    function loadDriver() {
        if (driverPromise) {
            return driverPromise;
        }
        driverPromise = new Promise(function(resolve, reject) {
            if (window.driver && window.driver.js && window.driver.js.driver) {
                resolve(window.driver.js.driver);
                return;
            }
            var css = document.createElement('link');
            css.rel = 'stylesheet';
            css.href = DRIVER_CSS_URL;
            document.head.appendChild(css);
            var script = document.createElement('script');
            script.src = DRIVER_JS_URL;
            script.onload = function() {
                if (window.driver && window.driver.js && window.driver.js.driver) {
                    resolve(window.driver.js.driver);
                } else {
                    reject(new Error('Driver.js no expone window.driver.js.driver'));
                }
            };
            script.onerror = function() {
                reject(new Error('No se pudo cargar Driver.js'));
            };
            document.head.appendChild(script);
        });
        return driverPromise;
    }

    function readManifest() {
        var el = document.getElementById(MANIFEST_DOM_ID);
        if (!el) {
            return null;
        }
        try {
            return JSON.parse(el.textContent || '');
        } catch (e) {
            Log.debug('local_adipaonboarding: payload JSON invalido');
            return null;
        }
    }

    function logEvent(payload, stepid, action) {
        Ajax.call([{
            methodname: 'local_adipaonboarding_log_event',
            args: {
                tourid: payload.id,
                tourversion: payload.version,
                stepid: stepid,
                action: action
            },
            fail: function(err) {
                Log.debug('log_event fallo', err);
            }
        }]);
    }

    function markSeen(payload, completed) {
        Ajax.call([{
            methodname: 'local_adipaonboarding_mark_seen',
            args: {
                tourid: payload.id,
                tourversion: payload.version,
                completed: completed
            },
            fail: function(err) {
                Log.debug('mark_seen fallo', err);
            }
        }]);
    }

    function isMobileViewport() {
        return window.matchMedia('(max-width: 767px)').matches;
    }

    function resolvePlacement(step) {
        if (isMobileViewport() && step.responsive && step.responsive.mobile && step.responsive.mobile.placement) {
            return step.responsive.mobile.placement;
        }
        return step.placement || 'auto';
    }

    /**
     * Visible = en DOM + offsetParent != null + bounding rect > 0.
     * Targets 'modal' siempre se consideran visibles (popover centrado).
     */
    function elementVisible(selector) {
        if (!selector || selector === 'modal') {
            return true;
        }
        try {
            var el = document.querySelector(selector);
            if (!el || el.offsetParent === null) {
                return false;
            }
            var rect = el.getBoundingClientRect();
            return rect.width > 0 && rect.height > 0;
        } catch (e) {
            return false;
        }
    }

    function hasClickAction(step) {
        if (!Array.isArray(step.actions)) {
            return false;
        }
        for (var i = 0; i < step.actions.length; i++) {
            if (step.actions[i] && step.actions[i].type === 'click') {
                return true;
            }
        }
        return false;
    }

    function runAction(action) {
        return new Promise(function(resolve) {
            if (!action || ALLOWED_ACTION_TYPES.indexOf(action.type) === -1) {
                resolve();
                return;
            }
            try {
                if (action.type === 'wait') {
                    var ms = Math.max(0, Math.min(parseInt(action.ms, 10) || 0, 3000));
                    setTimeout(resolve, ms);
                    return;
                }
                if (action.type === 'click') {
                    var target = action.selector ? document.querySelector(action.selector) : null;
                    if (target && typeof target.click === 'function') {
                        target.click();
                    }
                    resolve();
                    return;
                }
                resolve();
            } catch (e) {
                Log.debug('action ' + action.type + ' fallo', e);
                resolve();
            }
        });
    }

    function runActions(actions) {
        if (!Array.isArray(actions) || actions.length === 0) {
            return Promise.resolve();
        }
        return actions.reduce(function(prev, a) {
            return prev.then(function() { return runAction(a); });
        }, Promise.resolve());
    }

    function showPreviewBanner(payload) {
        if (document.querySelector('.adipa-preview-banner')) {
            return;
        }
        var banner = document.createElement('div');
        banner.className = 'adipa-preview-banner';
        banner.textContent = (payload.i18n && payload.i18n.preview_banner) || 'Preview mode';
        document.body.appendChild(banner);
        document.body.classList.add('adipa-preview-active');
    }

    /**
     * Filtra + convierte steps del payload a config Driver.js.
     * Steps con click action se incluyen aunque su target no sea visible ahora
     * (el click puede hacerlo aparecer). Steps sin click action requieren
     * target visible AHORA, sino skippean (popover quedaria mal posicionado).
     */
    function buildDriverSteps(payload, isPreview) {
        var driverSteps = [];
        payload.steps.forEach(function(step) {
            if (!hasClickAction(step) && !elementVisible(step.element)) {
                return;
            }
            var placement = resolvePlacement(step);
            var driverStep = {
                popover: {
                    title: step.title,
                    description: step.body,
                    side: placement === 'auto' ? 'over' : placement,
                    align: 'center',
                    nextBtnText: payload.i18n.next,
                    prevBtnText: payload.i18n.prev,
                    doneBtnText: payload.i18n.done,
                    onPopoverRender: function() {
                        if (!isPreview) {
                            logEvent(payload, step.id, 'viewed');
                        }
                    }
                },
                onHighlightStarted: function() {
                    if (Array.isArray(step.actions) && step.actions.length > 0) {
                        runActions(step.actions);
                    }
                }
            };
            if (step.element && step.element !== 'modal') {
                driverStep.element = step.element;
            }
            driverSteps.push(driverStep);
        });
        // Override ultimo step: forzar nextBtnText = doneBtnText. Driver.js v1
        // spread'ea d.popover sobre su calculo interno, asi que el "Finalizar"
        // del ultimo step no se aplica solo. Este override es el hack minimo.
        if (driverSteps.length > 0) {
            var last = driverSteps[driverSteps.length - 1];
            last.popover.nextBtnText = payload.i18n.done;
        }
        return driverSteps;
    }

    function lockScroll()   { document.body.classList.add('adipa-tour-active'); }
    function unlockScroll() { document.body.classList.remove('adipa-tour-active'); }

    function runTour(payload, isPreview) {
        loadStyles();
        loadDriver().then(function(driverFn) {
            var steps = buildDriverSteps(payload, isPreview);
            if (steps.length === 0) {
                Log.debug('ningun step matcheo el DOM, no se muestra tour');
                return;
            }
            var completed = false;
            var d = driverFn({
                showProgress: true,
                progressText: payload.i18n.progress,
                allowClose: true,
                disableActiveInteraction: true,
                nextBtnText: payload.i18n.next,
                prevBtnText: payload.i18n.prev,
                doneBtnText: payload.i18n.done,
                steps: steps,
                onDestroyStarted: function() {
                    if (!d.hasNextStep()) {
                        completed = true;
                    }
                    d.destroy();
                },
                onDestroyed: function() {
                    unlockScroll();
                    if (isPreview) {
                        return;
                    }
                    markSeen(payload, completed);
                    logEvent(payload, '_tour', completed ? 'completed' : 'dismissed');
                }
            });
            lockScroll();
            d.drive();
        }).catch(function(err) {
            Log.debug('local_adipaonboarding: ' + err.message);
        });
    }

    return {
        // Auto-launch desde lib.php (lee payload del DOM, respeta delay + min viewport).
        init: function() {
            var payload = readManifest();
            if (!payload) {
                return;
            }
            var minViewport = (payload.visibility && payload.visibility.min_viewport) || 320;
            if (window.innerWidth < minViewport) {
                return;
            }
            var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            var delay = reduced ? 0 : ((payload.visibility && payload.visibility.delay_ms) || 0);
            setTimeout(function() {
                runTour(payload, false);
            }, delay);
        },

        // Re-launch desde trigger.js (instant, sin delay).
        run: function(payload) {
            if (!payload) {
                payload = readManifest();
            }
            if (!payload) {
                return;
            }
            runTour(payload, false);
        },

        // Preview admin (sin telemetria ni markSeen).
        runPreview: function() {
            var payload = readManifest();
            if (!payload) {
                return;
            }
            loadStyles();
            showPreviewBanner(payload);
            runTour(payload, true);
        },

        ensureStyles: loadStyles,
        readManifest: readManifest
    };
});
