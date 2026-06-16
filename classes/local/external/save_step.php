<?php
namespace local_adipaonboarding\local\external;

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use local_adipaonboarding\local\tour\step_repository;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/externallib.php');

/**
 * WS: crea (id=0) o actualiza (id>0) un step de la library.
 * Para steps shipped el admin puede tocar selector/placement/text override,
 * pero step_key queda inmutable.
 */
class save_step extends external_api {

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'id'         => new external_value(PARAM_INT, '0 para insert, >0 para update'),
            'step_key'   => new external_value(PARAM_ALPHANUMEXT, 'Solo para insert; en update es ignorado'),
            'selector'   => new external_value(PARAM_RAW_TRIMMED, 'CSS selector o "modal"'),
            'title_text' => new external_value(PARAM_RAW, 'Texto directo (gana sobre lang key)', VALUE_DEFAULT, ''),
            'body_text'  => new external_value(PARAM_RAW, 'Cuerpo directo', VALUE_DEFAULT, ''),
            'placement'  => new external_value(PARAM_ALPHA, 'top|bottom|left|right|auto|over', VALUE_DEFAULT, 'auto'),
            'actions'    => new external_value(PARAM_RAW,   'JSON array de pre-actions, o "" para ninguna', VALUE_DEFAULT, ''),
        ]);
    }

    public static function execute(
        int $id,
        string $stepkey,
        string $selector,
        string $titletext = '',
        string $bodytext = '',
        string $placement = 'auto',
        string $actions = ''
    ): array {
        $params = self::validate_parameters(self::execute_parameters(), [
            'id'         => $id,
            'step_key'   => $stepkey,
            'selector'   => $selector,
            'title_text' => $titletext,
            'body_text'  => $bodytext,
            'placement'  => $placement,
            'actions'    => $actions,
        ]);
        self::validate_context(\context_system::instance());
        require_capability('local/adipaonboarding:manage_steps', \context_system::instance());

        $validplacements = ['top', 'bottom', 'left', 'right', 'auto', 'over'];
        if (!in_array($params['placement'], $validplacements, true)) {
            throw new \invalid_parameter_exception('placement invalido');
        }

        $actionsnorm = self::parse_and_validate_actions($params['actions']);

        if ($params['id'] === 0) {
            // Insert: step custom (sin lang_key, solo text).
            if (step_repository::id_for_key($params['step_key']) !== null) {
                throw new \invalid_parameter_exception('step_key ya existe');
            }
            $newid = step_repository::insert([
                'step_key'   => $params['step_key'],
                'selector'   => $params['selector'],
                'title_text' => $params['title_text'],
                'body_text'  => $params['body_text'],
                'placement'  => $params['placement'],
                'actions'    => $actionsnorm,
            ], false);
            return ['status' => 'ok', 'id' => $newid];
        }

        // Update: el admin puede agregar text que pisa al lang_key shipped.
        step_repository::update_from_admin($params['id'], [
            'selector'   => $params['selector'],
            'title_text' => $params['title_text'],
            'body_text'  => $params['body_text'],
            'placement'  => $params['placement'],
            'actions'    => $actionsnorm,
        ]);
        return ['status' => 'ok', 'id' => $params['id']];
    }

    /**
     * Parsea + valida el JSON de pre-actions del admin. Devuelve array PHP listo
     * para guardar (el repo se encarga del json_encode), o null si esta vacio.
     * Lanza invalid_parameter_exception si el JSON o el shape son invalidos.
     */
    private static function parse_and_validate_actions(string $raw): ?array {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }
        $decoded = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \invalid_parameter_exception('actions: JSON invalido (' . json_last_error_msg() . ')');
        }
        if (!is_array($decoded)) {
            throw new \invalid_parameter_exception('actions: debe ser un array JSON');
        }
        $allowed = ['click', 'wait'];
        foreach ($decoded as $i => $action) {
            if (!is_array($action) || !isset($action['type']) || !in_array($action['type'], $allowed, true)) {
                throw new \invalid_parameter_exception('actions[' . $i . ']: type debe ser click|wait');
            }
            if ($action['type'] === 'click') {
                if (!isset($action['selector']) || !is_string($action['selector']) || $action['selector'] === '') {
                    throw new \invalid_parameter_exception('actions[' . $i . ']: falta selector');
                }
            }
            if ($action['type'] === 'wait') {
                if (!isset($action['ms']) || !is_numeric($action['ms'])) {
                    throw new \invalid_parameter_exception('actions[' . $i . ']: ms debe ser numero');
                }
            }
        }
        return $decoded;
    }

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'status' => new external_value(PARAM_ALPHA, 'ok'),
            'id'     => new external_value(PARAM_INT, 'ID del step (post insert/update)'),
        ]);
    }
}
