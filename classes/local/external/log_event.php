<?php
namespace local_adipaonboarding\local\external;

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use local_adipaonboarding\local\tour\storage;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/externallib.php');

/**
 * WS: registra evento de telemetria (step viewed/skipped/completed/dismissed).
 */
class log_event extends external_api {

    /** Acciones validas que el runner puede emitir. */
    private const VALID_ACTIONS = ['viewed', 'skipped', 'completed', 'dismissed'];

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'tourid'      => new external_value(PARAM_ALPHANUMEXT, 'ID del recorrido'),
            'tourversion' => new external_value(PARAM_INT, 'Version'),
            'stepid'      => new external_value(PARAM_ALPHANUMEXT, 'ID del step'),
            'action'      => new external_value(PARAM_ALPHA, 'viewed|skipped|completed|dismissed'),
        ]);
    }

    public static function execute(string $tourid, int $tourversion, string $stepid, string $action): array {
        global $USER, $PAGE;
        self::validate_parameters(self::execute_parameters(), [
            'tourid'      => $tourid,
            'tourversion' => $tourversion,
            'stepid'      => $stepid,
            'action'      => $action,
        ]);
        // Solo usuarios logueados pueden registrar eventos (la telemetria es per-user).
        self::validate_context(\context_system::instance());
        if (isguestuser() || !isloggedin()) {
            throw new \required_capability_exception(\context_system::instance(), 'local/adipaonboarding:view', 'nopermissions', '');
        }
        if (!in_array($action, self::VALID_ACTIONS, true)) {
            throw new \invalid_parameter_exception('Action invalido: ' . $action);
        }
        $contextid = ($PAGE && $PAGE->context) ? (int)$PAGE->context->id : null;
        storage::log_event($USER->id, $tourid, $tourversion, $stepid, $action, $contextid);
        return ['status' => 'ok'];
    }

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'status' => new external_value(PARAM_ALPHA, 'ok'),
        ]);
    }
}
