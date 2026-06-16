// local_adipaonboarding/admin/step_editor — CRUD de un step de la library.
define([
    'core/ajax',
    'core/notification',
    'core/str'
], function(Ajax, Notification, Str) {
    'use strict';

    function readForm() {
        var actionsEl = document.getElementById('adipa-actions');
        return {
            step_key:   document.getElementById('adipa-step-key').value.trim(),
            selector:   document.getElementById('adipa-selector').value.trim(),
            title_text: document.getElementById('adipa-title-text').value,
            body_text:  document.getElementById('adipa-body-text').value,
            placement:  document.getElementById('adipa-placement').value,
            actions:    actionsEl ? actionsEl.value.trim() : ''
        };
    }

    /**
     * Valida que el JSON de pre-actions sea parseable y matchee el shape esperado.
     * Devuelve {ok: true, json: '...'} o {ok: false, error: '...'}.
     * Vacio = no actions, devuelve cadena vacia (el WS lo interpreta como null).
     */
    function validateActions(raw) {
        if (!raw) {
            return {ok: true, json: ''};
        }
        var parsed;
        try {
            parsed = JSON.parse(raw);
        } catch (e) {
            return {ok: false, error: 'JSON invalido: ' + e.message};
        }
        if (!Array.isArray(parsed)) {
            return {ok: false, error: 'Debe ser un array JSON ([{...}])'};
        }
        var allowed = ['click', 'wait'];
        for (var i = 0; i < parsed.length; i++) {
            var a = parsed[i];
            if (!a || typeof a !== 'object' || allowed.indexOf(a.type) === -1) {
                return {ok: false, error: 'Action #' + (i + 1) + ': type debe ser click | wait'};
            }
            if (a.type === 'click' && typeof a.selector !== 'string') {
                return {ok: false, error: 'Action #' + (i + 1) + ': falta selector'};
            }
            if (a.type === 'wait' && typeof a.ms !== 'number') {
                return {ok: false, error: 'Action #' + (i + 1) + ': ms debe ser numero'};
            }
        }
        return {ok: true, json: JSON.stringify(parsed)};
    }

    function setActionsError(msg) {
        var el = document.getElementById('adipa-actions-error');
        if (!el) {
            return;
        }
        if (msg) {
            el.textContent = msg;
            el.style.display = 'block';
        } else {
            el.textContent = '';
            el.style.display = 'none';
        }
    }

    function notify(stringkey, type) {
        return Str.get_string(stringkey, 'local_adipaonboarding').then(function(msg) {
            Notification.addNotification({message: msg, type: type});
            return null;
        }).catch(function() {});
    }

    function listUrl() {
        return M.cfg.wwwroot + '/local/adipaonboarding/admin/steps.php';
    }

    function bindSave(stepId) {
        var btn = document.getElementById('adipa-save-step');
        if (!btn) {
            return;
        }
        btn.addEventListener('click', function() {
            var data = readForm();
            if (!data.step_key || !data.selector) {
                Str.get_string('step_edit_validation_required', 'local_adipaonboarding').then(function(msg) {
                    Notification.addNotification({message: msg, type: 'warning'});
                    return null;
                }).catch(function() {});
                return;
            }
            var actionsValidation = validateActions(data.actions);
            if (!actionsValidation.ok) {
                setActionsError(actionsValidation.error);
                return;
            }
            setActionsError('');
            btn.disabled = true;
            Ajax.call([{
                methodname: 'local_adipaonboarding_save_step',
                args: {
                    id:         stepId,
                    step_key:   data.step_key,
                    selector:   data.selector,
                    title_text: data.title_text,
                    body_text:  data.body_text,
                    placement:  data.placement,
                    actions:    actionsValidation.json
                }
            }])[0].then(function(result) {
                btn.disabled = false;
                notify('step_edit_saved', 'success');
                // Si era insert, redirigir al editor del id nuevo para poder seguir editando.
                if (stepId === 0 && result.id > 0) {
                    setTimeout(function() {
                        window.location.href = M.cfg.wwwroot +
                            '/local/adipaonboarding/admin/step_edit.php?id=' + result.id;
                    }, 600);
                }
                return null;
            }).catch(function(err) {
                btn.disabled = false;
                Notification.exception(err);
            });
        });
    }

    function bindReset(stepId, stepKey) {
        var btn = document.getElementById('adipa-reset-step');
        if (!btn) {
            return;
        }
        btn.addEventListener('click', function() {
            var msg = btn.getAttribute('data-confirm') || 'Reset?';
            if (!window.confirm(msg)) {
                return;
            }
            btn.disabled = true;
            Ajax.call([{
                methodname: 'local_adipaonboarding_reset_step_defaults',
                args: {step_key: stepKey}
            }])[0].then(function() {
                btn.disabled = false;
                notify('step_edit_reset_done', 'success');
                setTimeout(function() { window.location.reload(); }, 600);
                return null;
            }).catch(function(err) {
                btn.disabled = false;
                Notification.exception(err);
            });
        });
    }

    function bindDelete(stepId) {
        var btn = document.getElementById('adipa-delete-step');
        if (!btn) {
            return;
        }
        btn.addEventListener('click', function() {
            var msg = btn.getAttribute('data-confirm') || 'Delete?';
            if (!window.confirm(msg)) {
                return;
            }
            attemptDelete(stepId, false);
        });
    }

    function attemptDelete(stepId, force) {
        var btn = document.getElementById('adipa-delete-step');
        btn.disabled = true;
        Ajax.call([{
            methodname: 'local_adipaonboarding_delete_step',
            args: {id: stepId, force: force}
        }])[0].then(function(result) {
            if (result.status === 'blocked') {
                btn.disabled = false;
                var lines = result.tours.map(function(t) {
                    return '  • ' + t.scope + (t.coursetype ? '/' + t.coursetype : '') + ' (tour #' + t.tour_id + ')';
                }).join('\n');
                Str.get_string('step_edit_delete_blocked', 'local_adipaonboarding', result.tours.length)
                    .then(function(header) {
                        if (window.confirm(header + '\n\n' + lines + '\n\n¿Borrar igual (cascade)?')) {
                            attemptDelete(stepId, true);
                        }
                        return null;
                    }).catch(function() {});
                return null;
            }
            notify('step_edit_deleted', 'success');
            setTimeout(function() { window.location.href = listUrl(); }, 600);
            return null;
        }).catch(function(err) {
            btn.disabled = false;
            Notification.exception(err);
        });
    }

    function bindTestSelector() {
        var btn = document.getElementById('adipa-test-selector');
        if (!btn) {
            return;
        }
        btn.addEventListener('click', function() {
            var selector = document.getElementById('adipa-selector').value.trim();
            var courseId = parseInt(document.getElementById('adipa-test-course-id').value, 10);
            if (!selector) {
                Str.get_string('step_edit_test_selector_no_selector', 'local_adipaonboarding').then(function(msg) {
                    Notification.addNotification({message: msg, type: 'warning'});
                    return null;
                }).catch(function() {});
                return;
            }
            if (!courseId || courseId <= 0) {
                Str.get_string('test_course_id_required', 'local_adipaonboarding').then(function(msg) {
                    Notification.addNotification({message: msg, type: 'warning'});
                    return null;
                }).catch(function() {});
                return;
            }
            var url = M.cfg.wwwroot + '/course/view.php' +
                '?id=' + courseId +
                '&adipaonboarding_test_selector=' + encodeURIComponent(selector);
            window.open(url, '_blank');
        });
    }

    return {
        init: function(stepId, stepKey) {
            stepId = parseInt(stepId, 10);
            bindSave(stepId);
            bindReset(stepId, stepKey);
            bindDelete(stepId);
            bindTestSelector();
        }
    };
});
