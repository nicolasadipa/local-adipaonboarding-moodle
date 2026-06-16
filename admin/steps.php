<?php
require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once(__DIR__ . '/../lib.php');

use local_adipaonboarding\local\tour\step_repository;
use local_adipaonboarding\local\tour\seeder;

local_adipaonboarding_admin_page_setup('local_adipaonboarding_steps', 'admin_section_steps');
global $OUTPUT, $PAGE;

$rows = step_repository::all_rows();
$tplrows = [];
foreach ($rows as $r) {
    $hydrated = step_repository::hydrate($r);
    $editurl = new moodle_url('/local/adipaonboarding/admin/step_edit.php', ['id' => $r->id]);
    $tplrows[] = [
        'id'            => (int)$r->id,
        'stepkey'       => $r->stepkey,
        'selector'      => $r->selector,
        'placement'     => $r->placement,
        'resolved_title' => $hydrated->title,
        'shipped'       => (int)$r->shipped === 1,
        'dirty'         => (int)$r->dirty === 1,
        'edit_url'      => $editurl->out(false),
    ];
}

$newurl = new moodle_url('/local/adipaonboarding/admin/step_edit.php', ['id' => 0]);

$data = [
    'steps'    => $tplrows,
    'empty'    => empty($tplrows),
    'new_url'  => $newurl->out(false),
    'str' => [
        'col_key'        => get_string('steps_list_col_key', 'local_adipaonboarding'),
        'col_selector'   => get_string('steps_list_col_selector', 'local_adipaonboarding'),
        'col_title'      => get_string('steps_list_col_title', 'local_adipaonboarding'),
        'col_placement'  => get_string('steps_list_col_placement', 'local_adipaonboarding'),
        'col_actions'    => get_string('tours_list_col_actions', 'local_adipaonboarding'),
        'action_edit'    => get_string('tours_list_action_edit', 'local_adipaonboarding'),
        'new_step'       => get_string('steps_list_new_btn', 'local_adipaonboarding'),
        'empty_msg'      => get_string('steps_list_empty', 'local_adipaonboarding'),
        'shipped_badge'  => get_string('steps_list_shipped_badge', 'local_adipaonboarding'),
        'custom_badge'   => get_string('steps_list_custom_badge', 'local_adipaonboarding'),
        'dirty_badge'    => get_string('tours_list_dirty_badge', 'local_adipaonboarding'),
    ],
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_adipaonboarding/steps_list', $data);
echo $OUTPUT->footer();
