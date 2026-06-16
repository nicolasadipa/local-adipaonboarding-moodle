<?php
namespace local_adipaonboarding\local\external;

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use external_multiple_structure;
use local_adipaonboarding\local\tour\step_repository;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/externallib.php');

/**
 * WS: borra un step de la library. Pre-check: si tours lo referencian,
 * requiere force=true para hacer cascade (borra entradas en tsteps tambien).
 */
class delete_step extends external_api {

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'id'    => new external_value(PARAM_INT, 'ID del step'),
            'force' => new external_value(PARAM_BOOL, 'Si false, falla si hay tours usandolo', VALUE_DEFAULT, false),
        ]);
    }

    public static function execute(int $id, bool $force = false): array {
        $params = self::validate_parameters(self::execute_parameters(), [
            'id'    => $id,
            'force' => $force,
        ]);
        self::validate_context(\context_system::instance());
        require_capability('local/adipaonboarding:manage_steps', \context_system::instance());

        $tours = step_repository::tours_using_step($params['id']);
        if (!empty($tours) && !$params['force']) {
            // Reportar sin borrar.
            $toursinfo = [];
            foreach ($tours as $t) {
                $toursinfo[] = [
                    'tour_id'    => (int)$t->tourid,
                    'scope'      => (string)$t->scope,
                    'coursetype' => $t->coursetype !== null ? (string)$t->coursetype : '',
                ];
            }
            return ['status' => 'blocked', 'tours' => $toursinfo];
        }

        step_repository::delete($params['id']);
        return ['status' => 'ok', 'tours' => []];
    }

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'status' => new external_value(PARAM_ALPHA, 'ok|blocked'),
            'tours'  => new external_multiple_structure(
                new external_single_structure([
                    'tour_id'    => new external_value(PARAM_INT, ''),
                    'scope'      => new external_value(PARAM_ALPHANUMEXT, ''),
                    'coursetype' => new external_value(PARAM_ALPHANUMEXT, '', VALUE_DEFAULT, ''),
                ])
            ),
        ]);
    }
}
