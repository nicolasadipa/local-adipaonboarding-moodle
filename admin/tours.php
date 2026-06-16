<?php
require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once(__DIR__ . '/../lib.php');

use local_adipaonboarding\local\tour\tour_repository;

local_adipaonboarding_admin_page_setup('local_adipaonboarding_tours', 'admin_section_tours');
global $OUTPUT, $PAGE;

$tours = tour_repository::all_with_counts();

$rows = [];
foreach ($tours as $t) {
    $editurl = new moodle_url('/local/adipaonboarding/admin/tour_edit.php', ['id' => $t->id]);
    $rows[] = [
        'id'           => (int)$t->id,
        'scope'        => $t->scope,
        'coursetype'   => $t->coursetype !== null ? $t->coursetype : '—',
        'version'      => (int)$t->version,
        'enabled'      => (int)$t->enabled === 1,
        'stepcount'    => (int)$t->stepcount,
        'dirty'        => (int)$t->dirty === 1,
        'edit_url'     => $editurl->out(false),
    ];
}

$data = [
    'tours'      => $rows,
    'empty'      => empty($rows),
    'sesskey'    => sesskey(),
    'str' => [
        'col_scope'      => get_string('tours_list_col_scope', 'local_adipaonboarding'),
        'col_coursetype' => get_string('tours_list_col_course_type', 'local_adipaonboarding'),
        'col_steps'      => get_string('tours_list_col_steps', 'local_adipaonboarding'),
        'col_version'    => get_string('tours_list_col_version', 'local_adipaonboarding'),
        'col_enabled'    => get_string('tours_list_col_enabled', 'local_adipaonboarding'),
        'col_actions'    => get_string('tours_list_col_actions', 'local_adipaonboarding'),
        'action_edit'    => get_string('tours_list_action_edit', 'local_adipaonboarding'),
        'dirty_badge'    => get_string('tours_list_dirty_badge', 'local_adipaonboarding'),
        'empty_msg'      => get_string('tours_list_empty', 'local_adipaonboarding'),
        'on'             => get_string('on', 'local_adipaonboarding'),
        'off'            => get_string('off', 'local_adipaonboarding'),
    ],
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_adipaonboarding/tours_list', $data);
$PAGE->requires->js_call_amd('local_adipaonboarding/admin/tour_list', 'init');
echo $OUTPUT->footer();
