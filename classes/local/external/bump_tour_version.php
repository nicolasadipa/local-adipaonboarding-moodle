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
 * WS: bump version del tour para forzar relanzamiento incremental.
 * Todos los usuarios que ya lo vieron en version N volveran a verlo en N+1.
 */
class bump_tour_version extends external_api {

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'tour_id' => new external_value(PARAM_INT, 'ID del tour'),
        ]);
    }

    public static function execute(int $tourid): array {
        $params = self::validate_parameters(self::execute_parameters(), [
            'tour_id' => $tourid,
        ]);
        self::validate_context(\context_system::instance());
        require_capability('local/adipaonboarding:manage_tours', \context_system::instance());

        $newversion = tour_repository::bump_version($params['tour_id']);
        return ['status' => 'ok', 'version' => $newversion];
    }

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'status'  => new external_value(PARAM_ALPHA, 'ok'),
            'version' => new external_value(PARAM_INT, 'Nueva version'),
        ]);
    }
}
