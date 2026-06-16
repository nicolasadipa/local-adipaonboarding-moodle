// local_adipaonboarding/admin/tour_editor — drag-drop editor + override modal + reset.
//
// Estructura DOM: dos <ul> (tour + library) con items identicos
// (.adipa-step-item). SortableJS solo mueve LI; CSS decide visibilidad de
// botones override/remove segun el panel padre.
define([
    'core/ajax',
    'core/notification',
    'core/str',
    'core/log'
], function(Ajax, Notification, Str, Log) {
    'use strict';

    // Cache buster: estos bundles vendoreados se cargan via <script src> y no
    // pasan por el pipeline jsrev de Moodle. Sin ?v= el browser sirve la version
    // cacheada (problema cuando patcheamos el UMD de Sortable para no contaminar
    // RequireJS — la version cacheada sin patch sigue llamando define()).
    var SORTABLE_URL = M.cfg.wwwroot + '/local/adipaonboarding/lib/Sortable.min.js?v=' + (M.cfg.jsrev || '0');
    var sortablePromise = null;

    function loadSortable() {
        if (sortablePromise) {
            return sortablePromise;
        }
        sortablePromise = new Promise(function(resolve, reject) {
            if (window.Sortable) {
                resolve(window.Sortable);
                return;
            }
            var script = document.createElement('script');
            script.src = SORTABLE_URL;
            script.onload = function() {
                if (window.Sortable) {
                    resolve(window.Sortable);
                } else {
                    reject(new Error('SortableJS no expone window.Sortable'));
                }
            };
            script.onerror = function() {
                reject(new Error('No se pudo cargar SortableJS desde CDN'));
            };
            document.head.appendChild(script);
        });
        return sortablePromise;
    }

    function collectTourStepIds() {
        var ids = [];
        document.querySelectorAll('#adipa-tour-steps .adipa-step-item').forEach(function(li) {
            ids.push(parseInt(li.dataset.stepId, 10));
        });
        return ids;
    }

    function persistOrder(tourId) {
        return Ajax.call([{
            methodname: 'local_adipaonboarding_reorder_steps',
            args: {
                tour_id: tourId,
                step_ids: collectTourStepIds()
            }
        }])[0];
    }

    function notify(stringkey, type, arg) {
        return Str.get_string(stringkey, 'local_adipaonboarding', arg).then(function(msg) {
            Notification.addNotification({message: msg, type: type});
            return null;
        }).catch(function() {});
    }

    function bindRemoveDelegation(tourId) {
        var tourPanel = document.getElementById('adipa-tour-steps');
        if (!tourPanel) {
            return;
        }
        tourPanel.addEventListener('click', function(e) {
            var btn = e.target.closest('.adipa-step-remove');
            if (!btn) {
                return;
            }
            var li = btn.closest('.adipa-step-item');
            if (!li) {
                return;
            }
            var library = document.getElementById('adipa-library');
            if (library) {
                var emptyPh = library.querySelector('.adipa-library-empty');
                if (emptyPh) {
                    emptyPh.remove();
                }
                library.appendChild(li);
            }
            persistOrder(tourId).then(function() {
                notify('tour_edit_saved', 'success');
                return null;
            }).catch(Notification.exception);
        });
    }

    function bindMetadataSave(tourId) {
        var btn = document.getElementById('adipa-save-metadata');
        if (!btn) {
            return;
        }
        btn.addEventListener('click', function() {
            var enabled = document.getElementById('adipa-tour-enabled').checked;
            var delayMs = parseInt(document.getElementById('adipa-delay-ms').value, 10) || 7000;
            var minViewport = parseInt(document.getElementById('adipa-min-viewport').value, 10) || 320;
            var frequency = document.getElementById('adipa-frequency').value;
            btn.disabled = true;
            Ajax.call([{
                methodname: 'local_adipaonboarding_save_tour',
                args: {
                    tour_id: tourId,
                    enabled: enabled,
                    delay_ms: delayMs,
                    min_viewport: minViewport,
                    frequency: frequency
                }
            }])[0].then(function() {
                btn.disabled = false;
                notify('tour_edit_saved', 'success');
                return null;
            }).catch(function(err) {
                btn.disabled = false;
                Notification.exception(err);
            });
        });
    }

    function bindBumpVersion(tourId) {
        var btn = document.getElementById('adipa-bump-version');
        if (!btn) {
            return;
        }
        btn.addEventListener('click', function() {
            btn.disabled = true;
            Ajax.call([{
                methodname: 'local_adipaonboarding_bump_tour_version',
                args: {tour_id: tourId}
            }])[0].then(function(result) {
                btn.disabled = false;
                notify('tour_edit_version_bumped', 'success', result.version);
                setTimeout(function() { window.location.reload(); }, 800);
                return null;
            }).catch(function(err) {
                btn.disabled = false;
                Notification.exception(err);
            });
        });
    }

    function bindResetTour(tourId) {
        var btn = document.getElementById('adipa-reset-tour');
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
                methodname: 'local_adipaonboarding_reset_tour_defaults',
                args: {tour_id: tourId}
            }])[0].then(function() {
                notify('tour_edit_reset_done', 'success');
                setTimeout(function() { window.location.reload(); }, 600);
                return null;
            }).catch(function(err) {
                btn.disabled = false;
                Notification.exception(err);
            });
        });
    }

    var overrideContext = {tourstepid: 0, li: null};

    function openOverrideModal(li) {
        var modal = document.getElementById('adipa-override-modal');
        if (!modal) {
            return;
        }
        overrideContext.tourstepid = parseInt(li.dataset.tourStepId, 10);
        overrideContext.li = li;
        document.getElementById('adipa-override-selector').value = li.dataset.overrideSelector || '';
        document.getElementById('adipa-override-placement').value = li.dataset.overridePlacement || '';
        modal.hidden = false;
    }

    function closeOverrideModal() {
        var modal = document.getElementById('adipa-override-modal');
        if (modal) {
            modal.hidden = true;
        }
        overrideContext = {tourstepid: 0, li: null};
    }

    function bindOverrideModal() {
        var tourPanel = document.getElementById('adipa-tour-steps');
        if (tourPanel) {
            tourPanel.addEventListener('click', function(e) {
                var btn = e.target.closest('.adipa-step-override');
                if (!btn) {
                    return;
                }
                var li = btn.closest('.adipa-step-item');
                if (!li || parseInt(li.dataset.tourStepId, 10) === 0) {
                    return;
                }
                openOverrideModal(li);
            });
        }

        var cancel = document.getElementById('adipa-override-cancel');
        if (cancel) {
            cancel.addEventListener('click', closeOverrideModal);
        }
        var backdrop = document.querySelector('.adipa-override-backdrop');
        if (backdrop) {
            backdrop.addEventListener('click', closeOverrideModal);
        }

        var clear = document.getElementById('adipa-override-clear');
        if (clear) {
            clear.addEventListener('click', function() {
                if (overrideContext.tourstepid === 0) {
                    return;
                }
                clear.disabled = true;
                Ajax.call([{
                    methodname: 'local_adipaonboarding_set_step_override',
                    args: {
                        tour_step_id: overrideContext.tourstepid,
                        clear: true,
                        selector: '',
                        placement: ''
                    }
                }])[0].then(function() {
                    clear.disabled = false;
                    notify('tour_edit_saved', 'success');
                    closeOverrideModal();
                    setTimeout(function() { window.location.reload(); }, 400);
                    return null;
                }).catch(function(err) {
                    clear.disabled = false;
                    Notification.exception(err);
                });
            });
        }

        var apply = document.getElementById('adipa-override-apply');
        if (apply) {
            apply.addEventListener('click', function() {
                if (overrideContext.tourstepid === 0) {
                    return;
                }
                var selector = document.getElementById('adipa-override-selector').value.trim();
                var placement = document.getElementById('adipa-override-placement').value;
                apply.disabled = true;
                Ajax.call([{
                    methodname: 'local_adipaonboarding_set_step_override',
                    args: {
                        tour_step_id: overrideContext.tourstepid,
                        clear: false,
                        selector: selector,
                        placement: placement
                    }
                }])[0].then(function() {
                    apply.disabled = false;
                    notify('tour_edit_saved', 'success');
                    closeOverrideModal();
                    setTimeout(function() { window.location.reload(); }, 400);
                    return null;
                }).catch(function(err) {
                    apply.disabled = false;
                    Notification.exception(err);
                });
            });
        }
    }

    function makeSortable(Sortable, tourId) {
        var tourPanel = document.getElementById('adipa-tour-steps');
        var libraryPanel = document.getElementById('adipa-library');
        if (!tourPanel || !libraryPanel) {
            return;
        }

        Sortable.create(tourPanel, {
            group: 'adipa-steps',
            handle: '.adipa-step-handle',
            animation: 150,
            ghostClass: 'sortable-ghost',
            dragClass: 'sortable-drag',
            onSort: function() {
                persistOrder(tourId).catch(Notification.exception);
            }
        });

        Sortable.create(libraryPanel, {
            group: 'adipa-steps',
            handle: '.adipa-step-handle',
            animation: 150,
            ghostClass: 'sortable-ghost',
            dragClass: 'sortable-drag',
            sort: false,
            onAdd: function() {
                var emptyPh = libraryPanel.querySelector('.adipa-library-empty');
                if (emptyPh) {
                    emptyPh.remove();
                }
                persistOrder(tourId).catch(Notification.exception);
            }
        });
    }

    function bindPreviewTour(tourId) {
        var btn = document.getElementById('adipa-preview-tour');
        if (!btn) {
            return;
        }
        btn.addEventListener('click', function() {
            var courseInput = document.getElementById('adipa-preview-course-id');
            var courseId = parseInt(courseInput.value, 10);
            if (!courseId || courseId <= 0) {
                Str.get_string('test_course_id_required', 'local_adipaonboarding').then(function(msg) {
                    Notification.addNotification({message: msg, type: 'warning'});
                    return null;
                }).catch(function() {});
                return;
            }
            var url = M.cfg.wwwroot + '/course/view.php' +
                '?id=' + courseId +
                '&adipaonboarding_preview_tour=' + tourId;
            window.open(url, '_blank');
        });
    }

    return {
        init: function(tourId) {
            tourId = parseInt(tourId, 10);
            bindRemoveDelegation(tourId);
            bindMetadataSave(tourId);
            bindBumpVersion(tourId);
            bindResetTour(tourId);
            bindOverrideModal();
            bindPreviewTour(tourId);

            loadSortable().then(function(Sortable) {
                makeSortable(Sortable, tourId);
                return null;
            }).catch(function(err) {
                Log.debug('local_adipaonboarding: ' + err.message);
                Notification.exception(err);
            });
        }
    };
});
