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
 * WS: actualiza metadata de un tour (enabled, delay_ms, frequency, min_viewport).
 * No toca la secuencia de steps — eso lo hace reorder_steps.
 */
class save_tour extends external_api {

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'tour_id'      => new external_value(PARAM_INT, 'ID del tour'),
            'enabled'      => new external_value(PARAM_BOOL, 'Activar/desactivar', VALUE_DEFAULT, true),
            'delay_ms'     => new external_value(PARAM_INT, 'Delay en ms', VALUE_DEFAULT, 7000),
            'min_viewport' => new external_value(PARAM_INT, 'Viewport minimo en px', VALUE_DEFAULT, 320),
            'frequency'    => new external_value(PARAM_ALPHAEXT, 'once_per_user|once_per_day|every_visit', VALUE_DEFAULT, 'once_per_user'),
        ]);
    }

    public static function execute(
        int $tourid,
        bool $enabled = true,
        int $delayms = 7000,
        int $minviewport = 320,
        string $frequency = 'once_per_user'
    ): array {
        $params = self::validate_parameters(self::execute_parameters(), [
            'tour_id'      => $tourid,
            'enabled'      => $enabled,
            'delay_ms'     => $delayms,
            'min_viewport' => $minviewport,
            'frequency'    => $frequency,
        ]);

        self::validate_context(\context_system::instance());
        require_capability('local/adipaonboarding:manage_tours', \context_system::instance());

        $validfrequencies = ['once_per_user', 'once_per_day', 'every_visit'];
        if (!in_array($params['frequency'], $validfrequencies, true)) {
            throw new \invalid_parameter_exception('frequency invalida');
        }

        tour_repository::update_metadata_from_admin($params['tour_id'], [
            'enabled'    => $params['enabled'],
            'visibility' => [
                'delay_ms'     => $params['delay_ms'],
                'min_viewport' => $params['min_viewport'],
                'frequency'    => $params['frequency'],
            ],
        ]);

        return ['status' => 'ok'];
    }

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'status' => new external_value(PARAM_ALPHA, 'ok'),
        ]);
    }
}
