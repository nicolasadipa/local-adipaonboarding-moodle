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
 * WS: setea (o limpia con clear=true) el override per-tour-step. Permite
 * que el mismo step en distintos tours tenga selector o placement diferente.
 */
class set_step_override extends external_api {

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'tour_step_id' => new external_value(PARAM_INT, 'ID de la fila en tsteps'),
            'clear'        => new external_value(PARAM_BOOL, 'Si true, borra el override', VALUE_DEFAULT, false),
            'selector'     => new external_value(PARAM_RAW_TRIMMED, 'Override de selector (vacio = sin override)', VALUE_DEFAULT, ''),
            'placement'    => new external_value(PARAM_ALPHA, 'Override de placement (vacio = sin override)', VALUE_DEFAULT, ''),
        ]);
    }

    public static function execute(
        int $tourstepid,
        bool $clear = false,
        string $selector = '',
        string $placement = ''
    ): array {
        $params = self::validate_parameters(self::execute_parameters(), [
            'tour_step_id' => $tourstepid,
            'clear'        => $clear,
            'selector'     => $selector,
            'placement'    => $placement,
        ]);
        self::validate_context(\context_system::instance());
        require_capability('local/adipaonboarding:manage_tours', \context_system::instance());

        if ($params['clear']) {
            tour_repository::set_step_override($params['tour_step_id'], null);
            return ['status' => 'cleared'];
        }

        $override = [];
        if ($params['selector'] !== '') {
            $override['selector'] = $params['selector'];
        }
        if ($params['placement'] !== '') {
            $validplacements = ['top', 'bottom', 'left', 'right', 'auto', 'over'];
            if (!in_array($params['placement'], $validplacements, true)) {
                throw new \invalid_parameter_exception('placement invalido');
            }
            $override['placement'] = $params['placement'];
        }

        if (empty($override)) {
            tour_repository::set_step_override($params['tour_step_id'], null);
            return ['status' => 'cleared'];
        }

        tour_repository::set_step_override($params['tour_step_id'], $override);
        return ['status' => 'ok'];
    }

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'status' => new external_value(PARAM_ALPHA, 'ok|cleared'),
        ]);
    }
}
