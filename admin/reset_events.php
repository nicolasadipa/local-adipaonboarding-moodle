<?php
require_once(__DIR__ . '/../../../config.php');

require_login();
require_capability('local/adipaonboarding:view_telemetry', context_system::instance());
require_sesskey();

$count = \local_adipaonboarding\local\tour\storage::reset_all_events();

redirect(
    new moodle_url('/local/adipaonboarding/admin/telemetry.php'),
    get_string('telemetry_reset_done', 'local_adipaonboarding', $count),
    null,
    \core\output\notification::NOTIFY_SUCCESS
);
