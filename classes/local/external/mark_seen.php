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
 * WS: marca un recorrido como visto (completado o dismissed) para el usuario actual.
 */
class mark_seen extends external_api {

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'tourid'      => new external_value(PARAM_ALPHANUMEXT, 'ID del recorrido'),
            'tourversion' => new external_value(PARAM_INT, 'Version del recorrido'),
            'completed'   => new external_value(PARAM_BOOL, 'true si el usuario llego al ultimo step', VALUE_DEFAULT, false),
        ]);
    }

    public static function execute(string $tourid, int $tourversion, bool $completed = false): array {
        global $USER;
        self::validate_parameters(self::execute_parameters(), [
            'tourid'      => $tourid,
            'tourversion' => $tourversion,
            'completed'   => $completed,
        ]);
        // Context check: el WS solo registra para el usuario logueado actual ($USER->id),
        // no acepta userid del cliente. validate_context cierra el loop ante guests/inactivos.
        self::validate_context(\context_system::instance());
        if (isguestuser() || !isloggedin()) {
            throw new \required_capability_exception(\context_system::instance(), 'local/adipaonboarding:view', 'nopermissions', '');
        }
        storage::mark_seen($USER->id, $tourid, $tourversion, $completed);
        return ['status' => 'ok'];
    }

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'status' => new external_value(PARAM_ALPHA, 'ok'),
        ]);
    }
}
