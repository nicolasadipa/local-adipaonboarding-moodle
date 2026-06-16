// local_adipaonboarding/admin/course_optout — toggle inline per-curso.
define([
    'core/ajax',
    'core/notification',
    'core/str'
], function(Ajax, Notification, Str) {
    'use strict';

    function updateHint(optout) {
        var hint = document.getElementById('adipa-course-optout-hint');
        if (!hint) {
            return;
        }
        var key = optout ? 'course_optout_hint_on' : 'course_optout_hint_off';
        Str.get_string(key, 'local_adipaonboarding').then(function(msg) {
            hint.textContent = msg;
            return null;
        }).catch(function() {});
    }

    return {
        init: function(courseId) {
            courseId = parseInt(courseId, 10);
            var toggle = document.getElementById('adipa-course-optout-toggle');
            if (!toggle) {
                return;
            }
            toggle.addEventListener('change', function() {
                var optout = toggle.checked;
                toggle.disabled = true;
                Ajax.call([{
                    methodname: 'local_adipaonboarding_save_course_optout',
                    args: {
                        course_id: courseId,
                        optout:    optout
                    }
                }])[0].then(function() {
                    toggle.disabled = false;
                    updateHint(optout);
                    return Str.get_string('course_optout_saved', 'local_adipaonboarding');
                }).then(function(msg) {
                    Notification.addNotification({message: msg, type: 'success'});
                    return null;
                }).catch(function(err) {
                    toggle.disabled = false;
                    toggle.checked = !optout;
                    Notification.exception(err);
                });
            });
        }
    };
});
