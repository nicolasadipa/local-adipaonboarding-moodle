<?php
require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

use local_adipaonboarding\local\tour\tour_repository;
use local_adipaonboarding\local\tour\step_repository;
use local_adipaonboarding\local\tour\seeder;

$tourid = required_param('id', PARAM_INT);

admin_externalpage_setup('local_adipaonboarding_tours');
require_capability('local/adipaonboarding:manage_tours', context_system::instance());

$data = tour_repository::get_with_items($tourid);
if ($data === null) {
    throw new moodle_exception('errornotfound', 'local_adipaonboarding');
}
$tour = $data['tour'];
$items = $data['items'];
$hasseed = (seeder::get_tour_seed(
    (string)$tour->scope,
    $tour->coursetype !== null ? (string)$tour->coursetype : null
) !== null);

$librarykeys = [];
foreach ($items as $it) {
    $librarykeys[$it->step->stepkey] = true;
}

$visibility = json_decode($tour->visibilityjson, true) ?: [];

$tourstepsdata = [];
foreach ($items as $it) {
    $hydrated = step_repository::hydrate($it->step);
    $override = !empty($it->overridejson) ? json_decode($it->overridejson, true) : null;
    $tourstepsdata[] = [
        'tour_step_id' => $it->tsid,
        'step_id'  => (int)$it->step->id,
        'stepkey'  => $it->step->stepkey,
        'selector' => $it->step->selector,
        'title'    => $hydrated->title,
        'placement' => $it->step->placement,
        'has_override' => is_array($override) && !empty($override),
        'override_selector'  => is_array($override) && $override['selector'] ?? '',
        'override_placement' => is_array($override) && $override['placement'] ?? '',
    ];
}

$libraryrows = step_repository::all_rows();
$librarydata = [];
foreach ($libraryrows as $row) {
    if (isset($librarykeys[$row->stepkey])) {
        continue;
    }
    $hydrated = step_repository::hydrate($row);
    $librarydata[] = [
        'tour_step_id' => 0,
        'step_id'  => (int)$row->id,
        'stepkey'  => $row->stepkey,
        'selector' => $row->selector,
        'title'    => $hydrated->title,
        'placement' => $row->placement,
        'has_override' => false,
        'override_selector'  => '',
        'override_placement' => '',
    ];
}

$placements = ['auto', 'top', 'bottom', 'left', 'right', 'over'];
$currentfreq = $visibility['frequency'] ?? 'once_per_user';

global $OUTPUT, $PAGE;
$PAGE->set_title(get_string('tour_edit_title', 'local_adipaonboarding'));
$PAGE->set_heading(get_string('tour_edit_title', 'local_adipaonboarding'));
$PAGE->requires->css('/local/adipaonboarding/styles.css');

$tpldata = [
    'tour' => [
        'id'         => (int)$tour->id,
        'scope'      => $tour->scope,
        'coursetype' => $tour->coursetype !== null ? $tour->coursetype : '',
        'version'    => (int)$tour->version,
        'enabled'    => (int)$tour->enabled === 1,
        'dirty'      => (int)$tour->dirty === 1,
        'has_seed'   => $hasseed,
    ],
    'visibility' => [
        'delay_ms'     => isset($visibility['delay_ms']) ? (int)$visibility['delay_ms'] : 7000,
        'min_viewport' => isset($visibility['min_viewport']) ? (int)$visibility['min_viewport'] : 320,
    ],
    'freq_options' => [
        ['value' => 'once_per_user', 'label' => get_string('freq_once_per_user', 'local_adipaonboarding'), 'selected' => ($currentfreq === 'once_per_user')],
        ['value' => 'once_per_day',  'label' => get_string('freq_once_per_day',  'local_adipaonboarding'), 'selected' => ($currentfreq === 'once_per_day')],
        ['value' => 'every_visit',   'label' => get_string('freq_every_visit',   'local_adipaonboarding'), 'selected' => ($currentfreq === 'every_visit')],
    ],
    'placement_options' => array_map(function($p) {
        return ['value' => $p, 'label' => $p];
    }, $placements),
    'tour_steps'    => $tourstepsdata,
    'library'       => $librarydata,
    'library_empty' => empty($librarydata),
    'back_url'      => (new moodle_url('/local/adipaonboarding/admin/tours.php'))->out(false),
    'sesskey'       => sesskey(),
    'str' => [
        'tour_steps_panel'   => get_string('tour_edit_steps_panel', 'local_adipaonboarding'),
        'library_panel'      => get_string('tour_edit_library_panel', 'local_adipaonboarding'),
        'save_btn'           => get_string('tour_edit_save_btn', 'local_adipaonboarding'),
        'bump_btn'           => get_string('tour_edit_bump_version_btn', 'local_adipaonboarding'),
        'reset_btn'          => get_string('tour_edit_reset_btn', 'local_adipaonboarding'),
        'back_btn'           => get_string('tour_edit_back_btn', 'local_adipaonboarding'),
        'dirty_warning'      => get_string('tour_edit_dirty_warning', 'local_adipaonboarding'),
        'visibility_section' => get_string('tour_edit_visibility_section', 'local_adipaonboarding'),
        'lbl_enabled'        => get_string('tour_edit_lbl_enabled', 'local_adipaonboarding'),
        'lbl_delay'          => get_string('tour_edit_lbl_delay', 'local_adipaonboarding'),
        'lbl_min_viewport'   => get_string('tour_edit_lbl_min_viewport', 'local_adipaonboarding'),
        'lbl_frequency'      => get_string('tour_edit_lbl_frequency', 'local_adipaonboarding'),
        'remove'             => get_string('tour_step_remove', 'local_adipaonboarding'),
        'empty_library'      => get_string('tour_edit_library_empty', 'local_adipaonboarding'),
        'drag_hint'          => get_string('tour_edit_drag_hint', 'local_adipaonboarding'),
        'override_btn'       => get_string('tour_step_override_btn', 'local_adipaonboarding'),
        'override_modal_title' => get_string('tour_step_override_title', 'local_adipaonboarding'),
        'override_lbl_selector' => get_string('tour_step_override_lbl_selector', 'local_adipaonboarding'),
        'override_lbl_placement' => get_string('tour_step_override_lbl_placement', 'local_adipaonboarding'),
        'override_clear'     => get_string('tour_step_override_clear', 'local_adipaonboarding'),
        'override_apply'     => get_string('tour_step_override_apply', 'local_adipaonboarding'),
        'override_cancel'    => get_string('tour_step_override_cancel', 'local_adipaonboarding'),
        'confirm_reset'      => get_string('tour_edit_confirm_reset', 'local_adipaonboarding'),
        'preview_btn'        => get_string('tour_edit_preview_btn', 'local_adipaonboarding'),
        'test_course_id_label' => get_string('test_course_id_label', 'local_adipaonboarding'),
    ],
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_adipaonboarding/tour_editor', $tpldata);
$PAGE->requires->js_call_amd('local_adipaonboarding/admin/tour_editor', 'init', [$tour->id]);
echo $OUTPUT->footer();
