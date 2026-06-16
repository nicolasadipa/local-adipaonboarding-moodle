<?php
require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

use local_adipaonboarding\local\tour\step_repository;
use local_adipaonboarding\local\tour\seeder;

$id = optional_param('id', 0, PARAM_INT); // 0 = creando nuevo step custom

admin_externalpage_setup('local_adipaonboarding_steps');
require_capability('local/adipaonboarding:manage_steps', context_system::instance());

$isnew = ($id === 0);
$row = null;
$hasseed = false;
$resolvedtitle = '';
$resolvedbody = '';

if (!$isnew) {
    $row = step_repository::find_by_id($id);
    if ($row === null) {
        throw new moodle_exception('errornotfound', 'local_adipaonboarding');
    }
    $hasseed = (seeder::get_step_seed($row->stepkey) !== null);

    // Resolver textos como los veria el estudiante.
    $hydrated = step_repository::hydrate($row);
    $resolvedtitle = $hydrated->title;
    $resolvedbody = $hydrated->body;
}

global $OUTPUT, $PAGE;
$PAGE->set_title(get_string($isnew ? 'step_edit_new_title' : 'step_edit_title', 'local_adipaonboarding'));
$PAGE->set_heading(get_string($isnew ? 'step_edit_new_title' : 'step_edit_title', 'local_adipaonboarding'));
$PAGE->requires->css('/local/adipaonboarding/styles.css');

$placements = ['auto', 'top', 'bottom', 'left', 'right', 'over'];
$placementoptions = [];
$currentplacement = $row ? $row->placement : 'auto';
foreach ($placements as $p) {
    $placementoptions[] = [
        'value'    => $p,
        'label'    => $p,
        'selected' => ($p === $currentplacement),
    ];
}

$tplstr = [
    'lbl_step_key'    => get_string('step_edit_lbl_step_key', 'local_adipaonboarding'),
    'lbl_selector'    => get_string('step_edit_lbl_selector', 'local_adipaonboarding'),
    'lbl_title_text'  => get_string('step_edit_lbl_title_text', 'local_adipaonboarding'),
    'lbl_body_text'   => get_string('step_edit_lbl_body_text', 'local_adipaonboarding'),
    'lbl_placement'   => get_string('step_edit_lbl_placement', 'local_adipaonboarding'),
    'lbl_actions'     => get_string('step_actions_label', 'local_adipaonboarding'),
    'hint_actions'    => get_string('step_actions_help', 'local_adipaonboarding'),
    'hint_step_key'   => get_string('step_edit_hint_step_key', 'local_adipaonboarding'),
    'hint_text_overrides_lang' => get_string('step_edit_hint_text_overrides_lang', 'local_adipaonboarding'),
    'shipped_info'    => get_string('step_edit_shipped_info', 'local_adipaonboarding'),
    'custom_info'     => get_string('step_edit_custom_info', 'local_adipaonboarding'),
    'preview_title'   => get_string('step_edit_preview_title', 'local_adipaonboarding'),
    'preview_body'    => get_string('step_edit_preview_body', 'local_adipaonboarding'),
    'save_btn'        => get_string('step_edit_save_btn', 'local_adipaonboarding'),
    'reset_btn'       => get_string('step_edit_reset_btn', 'local_adipaonboarding'),
    'delete_btn'      => get_string('step_edit_delete_btn', 'local_adipaonboarding'),
    'back_btn'        => get_string('step_edit_back_btn', 'local_adipaonboarding'),
    'confirm_delete'  => get_string('step_edit_confirm_delete', 'local_adipaonboarding'),
    'confirm_reset'   => get_string('step_edit_confirm_reset', 'local_adipaonboarding'),
    'test_selector_btn'    => get_string('step_edit_test_selector_btn', 'local_adipaonboarding'),
    'test_selector_label'  => get_string('step_edit_test_selector_label', 'local_adipaonboarding'),
    'test_selector_hint'   => get_string('step_edit_test_selector_hint', 'local_adipaonboarding'),
    'test_course_id_label' => get_string('test_course_id_label', 'local_adipaonboarding'),
];

$data = [
    'is_new'        => $isnew,
    'has_seed'      => $hasseed,
    'shipped'       => $row ? ((int)$row->shipped === 1) : false,
    'dirty'         => $row ? ((int)$row->dirty === 1) : false,
    'id'            => $row ? (int)$row->id : 0,
    'stepkey'       => $row ? $row->stepkey : '',
    'selector'      => $row ? $row->selector : '',
    'titletext'     => $row && $row->titletext !== null ? $row->titletext : '',
    'bodytext'      => $row && $row->bodytext !== null ? $row->bodytext : '',
    'titlelangkey'  => $row ? $row->titlelangkey : '',
    'bodylangkey'   => $row ? $row->bodylangkey : '',
    'actionsjson'   => $row && !empty($row->actionsjson) ? (string)$row->actionsjson : '',
    'resolved_title' => $resolvedtitle,
    'resolved_body'  => $resolvedbody,
    'placement_options'  => $placementoptions,
    'back_url'      => (new moodle_url('/local/adipaonboarding/admin/steps.php'))->out(false),
    'str'           => $tplstr,
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_adipaonboarding/step_editor', $data);
$PAGE->requires->js_call_amd('local_adipaonboarding/admin/step_editor', 'init', [$data['id'], $data['stepkey']]);
echo $OUTPUT->footer();
