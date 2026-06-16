<?php
require_once(__DIR__ . '/../../../config.php');

require_login();
require_capability('local/adipaonboarding:reset_all_seen', context_system::instance());
require_sesskey();

$count = \local_adipaonboarding\local\tour\storage::reset_all_seen();

redirect(
    new moodle_url('/admin/settings.php', ['section' => 'local_adipaonboarding_settings']),
    get_string('setting_reset_all_done', 'local_adipaonboarding', $count),
    null,
    \core\output\notification::NOTIFY_SUCCESS
);
