<?php
namespace local_adipaonboarding\local\external;

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use local_adipaonboarding\local\tour\step_repository;
use local_adipaonboarding\local\tour\seeder;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/externallib.php');

/**
 * WS: reset to defaults para un step shipped. Falla si el step no tiene
 * un seed asociado (steps custom no se pueden resetear, solo borrar).
 */
class reset_step_defaults extends external_api {

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'step_key' => new external_value(PARAM_ALPHANUMEXT, 'stepkey del step a resetear'),
        ]);
    }

    public static function execute(string $stepkey): array {
        $params = self::validate_parameters(self::execute_parameters(), [
            'step_key' => $stepkey,
        ]);
        self::validate_context(\context_system::instance());
        require_capability('local/adipaonboarding:manage_steps', \context_system::instance());

        $seed = seeder::get_step_seed($params['step_key']);
        if ($seed === null) {
            throw new \invalid_parameter_exception('Step no tiene seed (es custom). Borralo si no lo queres.');
        }
        $ok = step_repository::reset_to_defaults($params['step_key'], $seed);
        return ['status' => $ok ? 'ok' : 'notfound'];
    }

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'status' => new external_value(PARAM_ALPHA, 'ok|notfound'),
        ]);
    }
}
