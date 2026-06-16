<?php
namespace local_adipaonboarding\local\external;

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use local_adipaonboarding\local\tour\tour_repository;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/externallib.php');

/**
 * WS: toggle rapido de enabled desde la lista de tours.
 */
class toggle_tour extends external_api {

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'tour_id' => new external_value(PARAM_INT, 'ID del tour'),
            'enabled' => new external_value(PARAM_BOOL, 'Estado final'),
        ]);
    }

    public static function execute(int $tourid, bool $enabled): array {
        $params = self::validate_parameters(self::execute_parameters(), [
            'tour_id' => $tourid,
            'enabled' => $enabled,
        ]);
        self::validate_context(\context_system::instance());
        require_capability('local/adipaonboarding:manage_tours', \context_system::instance());

        tour_repository::toggle_enabled($params['tour_id'], $params['enabled']);
        return ['status' => 'ok'];
    }

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'status' => new external_value(PARAM_ALPHA, 'ok'),
        ]);
    }
}
