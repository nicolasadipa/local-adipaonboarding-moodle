<?php
require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once(__DIR__ . '/../lib.php');

use local_adipaonboarding\local\tour\telemetry_repository;

local_adipaonboarding_admin_page_setup('local_adipaonboarding_telemetry', 'admin_section_telemetry');
require_capability('local/adipaonboarding:view_telemetry', context_system::instance());
global $OUTPUT, $PAGE;

$totals = telemetry_repository::totals();
$summary = telemetry_repository::tour_summary();

// Drop-off por tour: array indexado para Chart.js.
$dropoffdata = [];
foreach ($summary as $row) {
    $steps = telemetry_repository::step_drop_off($row['tourid']);
    $labels = [];
    $values = [];
    foreach ($steps as $s) {
        $labels[] = $s->stepid;
        $values[] = (int)$s->viewed_count;
    }
    $dropoffdata[] = [
        'tourid' => $row['tourid'],
        'labels' => $labels,
        'values' => $values,
    ];
}

$reseturl = new moodle_url('/local/adipaonboarding/admin/reset_events.php', ['sesskey' => sesskey()]);

$data = [
    'totals' => [
        'completed'       => (int)$totals['completed'],
        'dismissed'       => (int)$totals['dismissed'],
        'finished'        => (int)$totals['finished'],
        'completion_rate' => $totals['completion_rate'],
    ],
    'tours'  => $summary,
    'tours_empty' => empty($summary),
    'dropoffdata_json' => json_encode($dropoffdata),
    'reset_url' => $reseturl->out(false),
    'total_events' => $totals['finished'],
    'str' => [
        'overview_title'    => get_string('telemetry_overview_title', 'local_adipaonboarding'),
        'card_completed'    => get_string('telemetry_card_completed', 'local_adipaonboarding'),
        'card_dismissed'    => get_string('telemetry_card_dismissed', 'local_adipaonboarding'),
        'card_finished'     => get_string('telemetry_card_finished', 'local_adipaonboarding'),
        'card_completion'   => get_string('telemetry_card_completion', 'local_adipaonboarding'),
        'tours_table_title' => get_string('telemetry_tours_table_title', 'local_adipaonboarding'),
        'col_tourid'        => get_string('telemetry_col_tourid', 'local_adipaonboarding'),
        'col_finished'      => get_string('telemetry_col_finished', 'local_adipaonboarding'),
        'col_completed'     => get_string('telemetry_col_completed', 'local_adipaonboarding'),
        'col_dismissed'     => get_string('telemetry_col_dismissed', 'local_adipaonboarding'),
        'col_rate'          => get_string('telemetry_col_rate', 'local_adipaonboarding'),
        'dropoff_title'     => get_string('telemetry_dropoff_title', 'local_adipaonboarding'),
        'empty_msg'         => get_string('telemetry_empty', 'local_adipaonboarding'),
        'reset_btn'         => get_string('telemetry_reset_btn', 'local_adipaonboarding'),
        'reset_hint'        => get_string('telemetry_reset_hint', 'local_adipaonboarding'),
        'confirm_reset'     => get_string('telemetry_reset_confirm', 'local_adipaonboarding'),
    ],
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_adipaonboarding/telemetry', $data);
$PAGE->requires->js_call_amd('local_adipaonboarding/admin/telemetry', 'init', [$dropoffdata]);
echo $OUTPUT->footer();
