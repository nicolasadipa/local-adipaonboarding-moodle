<?php
namespace local_adipaonboarding\local\external;

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use external_multiple_structure;
use local_adipaonboarding\local\tour\tour_repository;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/externallib.php');

/**
 * WS: reemplaza la secuencia de steps de un tour. Idempotente.
 * Recibe step_ids ordenados; el repo borra el junction y reinserta.
 */
class reorder_steps extends external_api {

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'tour_id'  => new external_value(PARAM_INT, 'ID del tour'),
            'step_ids' => new external_multiple_structure(
                new external_value(PARAM_INT, 'ID de un step de la library')
            ),
        ]);
    }

    public static function execute(int $tourid, array $stepids): array {
        $params = self::validate_parameters(self::execute_parameters(), [
            'tour_id'  => $tourid,
            'step_ids' => $stepids,
        ]);

        self::validate_context(\context_system::instance());
        require_capability('local/adipaonboarding:manage_tours', \context_system::instance());

        $items = [];
        foreach ($params['step_ids'] as $stepid) {
            $items[] = ['step_id' => (int)$stepid, 'override' => null];
        }
        tour_repository::replace_steps($params['tour_id'], $items);
        tour_repository::mark_dirty($params['tour_id']);

        return ['status' => 'ok', 'count' => count($items)];
    }

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'status' => new external_value(PARAM_ALPHA, 'ok'),
            'count'  => new external_value(PARAM_INT, 'Cantidad de steps en el tour'),
        ]);
    }
}
