<?php
namespace local_adipaonboarding\local\external;

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use local_adipaonboarding\local\tour\tour_repository;
use local_adipaonboarding\local\tour\seeder;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/externallib.php');

/**
 * WS: reset to defaults para un tour shipped. Restaura visibility,
 * version, secuencia de steps y limpia dirty.
 */
class reset_tour_defaults extends external_api {

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'tour_id' => new external_value(PARAM_INT, 'ID del tour'),
        ]);
    }

    public static function execute(int $tourid): array {
        global $DB;
        $params = self::validate_parameters(self::execute_parameters(), [
            'tour_id' => $tourid,
        ]);
        self::validate_context(\context_system::instance());
        require_capability('local/adipaonboarding:manage_tours', \context_system::instance());

        $tour = $DB->get_record('local_adipaonboarding_tours', ['id' => $params['tour_id']]);
        if (!$tour) {
            throw new \invalid_parameter_exception('Tour no encontrado');
        }
        $coursetype = $tour->coursetype !== null ? (string)$tour->coursetype : null;
        $seed = seeder::get_tour_seed((string)$tour->scope, $coursetype);
        if ($seed === null) {
            throw new \invalid_parameter_exception('Tour no tiene seed (es custom). No se puede resetear.');
        }
        tour_repository::reset_to_defaults($params['tour_id'], $seed);
        return ['status' => 'ok'];
    }

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'status' => new external_value(PARAM_ALPHA, 'ok'),
        ]);
    }
}
