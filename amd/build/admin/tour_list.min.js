// local_adipaonboarding/admin/tour_list — handlers de la lista de tours.
// Toggle enabled inline via WS, sin recargar pagina.
define([
    'core/ajax',
    'core/notification',
    'core/str'
], function(Ajax, Notification, Str) {
    'use strict';

    function bindToggles() {
        var toggles = document.querySelectorAll('.adipa-tour-toggle');
        toggles.forEach(function(input) {
            input.addEventListener('change', function() {
                var tourId = parseInt(input.dataset.tourId, 10);
                var enabled = input.checked;
                input.disabled = true;
                Ajax.call([{
                    methodname: 'local_adipaonboarding_toggle_tour',
                    args: {
                        tour_id: tourId,
                        enabled: enabled
                    }
                }])[0].then(function() {
                    input.disabled = false;
                    return Str.get_string(enabled ? 'tours_list_enabled_msg' : 'tours_list_disabled_msg', 'local_adipaonboarding');
                }).then(function(msg) {
                    Notification.addNotification({
                        message: msg,
                        type: 'success'
                    });
                    return null;
                }).catch(function(err) {
                    input.disabled = false;
                    input.checked = !enabled; // revertir UI
                    Notification.exception(err);
                });
            });
        });
    }

    return {
        init: function() {
            bindToggles();
        }
    };
});
