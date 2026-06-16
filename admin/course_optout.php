<?php
require_once(__DIR__ . '/../../../config.php');

use local_adipaonboarding\local\tour\course_optout_repository;

$courseid = required_param('courseid', PARAM_INT);

$course = get_course($courseid);
$context = context_course::instance($course->id);

require_login($course);
require_capability('moodle/course:update', $context);

global $OUTPUT, $PAGE;
$PAGE->set_context($context);
$PAGE->set_url('/local/adipaonboarding/admin/course_optout.php', ['courseid' => $courseid]);
$PAGE->set_pagelayout('incourse');
$PAGE->set_title(get_string('course_optout_page_title', 'local_adipaonboarding'));
$PAGE->set_heading(get_string('course_optout_page_heading', 'local_adipaonboarding', format_string($course->fullname)));
$PAGE->requires->css('/local/adipaonboarding/styles.css');

$isdisabled = course_optout_repository::is_disabled($courseid);

$data = [
    'courseid'    => $courseid,
    'optout'      => $isdisabled,
    'course_name' => format_string($course->fullname),
    'str' => [
        'description' => get_string('course_optout_description', 'local_adipaonboarding'),
        'lbl_toggle'  => get_string('course_optout_lbl_toggle', 'local_adipaonboarding'),
        'hint_on'     => get_string('course_optout_hint_on', 'local_adipaonboarding'),
        'hint_off'    => get_string('course_optout_hint_off', 'local_adipaonboarding'),
        'saved'       => get_string('course_optout_saved', 'local_adipaonboarding'),
    ],
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_adipaonboarding/course_optout', $data);
$PAGE->requires->js_call_amd('local_adipaonboarding/admin/course_optout', 'init', [$courseid]);
echo $OUTPUT->footer();
