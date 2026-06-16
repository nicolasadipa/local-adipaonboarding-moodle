<?php
namespace local_adipaonboarding\local\tour;

defined('MOODLE_INTERNAL') || die();

/**
 * Capa de persistencia: leer/escribir tabla seen + events.
 * Toda otra clase del plugin pasa por aca para tocar la DB.
 */
class storage {

    /**
     * ¿Ya vio el usuario este recorrido en esta version exacta?
     */
    public static function has_seen(int $userid, string $tourid, int $tourversion): bool {
        global $DB;
        return $DB->record_exists('local_adipaonboarding_seen', [
            'userid'      => $userid,
            'tourid'      => $tourid,
            'tourversion' => $tourversion,
        ]);
    }

    /**
     * Marca el recorrido como visto. Upsert por (userid, tourid, tourversion).
     */
    public static function mark_seen(int $userid, string $tourid, int $tourversion, bool $completed): void {
        global $DB;
        $existing = $DB->get_record('local_adipaonboarding_seen', [
            'userid'      => $userid,
            'tourid'      => $tourid,
            'tourversion' => $tourversion,
        ]);
        $record = (object)[
            'userid'      => $userid,
            'tourid'      => $tourid,
            'tourversion' => $tourversion,
            'completed'   => $completed ? 1 : 0,
            'seenat'      => time(),
        ];
        if ($existing) {
            $record->id = $existing->id;
            $DB->update_record('local_adipaonboarding_seen', $record);
        } else {
            $DB->insert_record('local_adipaonboarding_seen', $record);
        }
    }

    /**
     * Registra evento de telemetria.
     */
    public static function log_event(
        int $userid,
        string $tourid,
        int $tourversion,
        string $stepid,
        string $action,
        ?int $contextid = null
    ): void {
        global $DB;
        $DB->insert_record('local_adipaonboarding_events', (object)[
            'userid'      => $userid,
            'tourid'      => $tourid,
            'tourversion' => $tourversion,
            'stepid'      => $stepid,
            'action'      => $action,
            'contextid'   => $contextid,
            'createdat'   => time(),
        ]);
    }

    /**
     * Reset masivo (admin only). Devuelve cantidad de registros borrados.
     */
    public static function reset_all_seen(): int {
        global $DB;
        $count = $DB->count_records('local_adipaonboarding_seen');
        $DB->delete_records('local_adipaonboarding_seen');
        return $count;
    }

    /**
     * Limpia toda la telemetria (events). Util para resetear el dashboard
     * cuando hay eventos viejos contaminando los numeros.
     */
    public static function reset_all_events(): int {
        global $DB;
        $count = $DB->count_records('local_adipaonboarding_events');
        $DB->delete_records('local_adipaonboarding_events');
        return $count;
    }

    /**
     * ¿Fue visto hoy (cualquier version)?
     */
    public static function seen_today(int $userid, string $tourid): bool {
        global $DB;
        $today = strtotime('today midnight');
        return $DB->record_exists_select(
            'local_adipaonboarding_seen',
            'userid = ? AND tourid = ? AND seenat >= ?',
            [$userid, $tourid, $today]
        );
    }
}
