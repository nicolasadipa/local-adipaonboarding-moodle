<?php
namespace local_adipaonboarding\local\external;

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use local_adipaonboarding\local\tour\course_optout_repository;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/externallib.php');

/**
 * WS: setea el opt-out de tour per-curso. Disponible para usuarios con
 * moodle/course:update en el contexto del curso (teachers/managers/admin).
 */
class save_course_optout extends external_api {

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'course_id' => new external_value(PARAM_INT, 'ID del curso'),
            'optout'    => new external_value(PARAM_BOOL, 'true = ocultar tour, false = mostrar'),
        ]);
    }

    public static function execute(int $courseid, bool $optout): array {
        global $USER;
        $params = self::validate_parameters(self::execute_parameters(), [
            'course_id' => $courseid,
            'optout'    => $optout,
        ]);
        $coursecontext = \context_course::instance($params['course_id']);
        self::validate_context($coursecontext);
        require_capability('moodle/course:update', $coursecontext);

        course_optout_repository::set($params['course_id'], $params['optout'], (int)$USER->id);
        return ['status' => 'ok'];
    }

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'status' => new external_value(PARAM_ALPHA, 'ok'),
        ]);
    }
}
