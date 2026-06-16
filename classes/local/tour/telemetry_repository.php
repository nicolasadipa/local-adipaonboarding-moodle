<?php
namespace local_adipaonboarding\local\tour;

defined('MOODLE_INTERNAL') || die();

/**
 * Queries agregadas sobre events para el dashboard de telemetria.
 *
 * Convenciones:
 *   - tourid en events: matchea manifest::id (no la PK numerica de tours).
 *     Ej: "course_view_course". Lo emite runner.js desde el payload.
 *   - stepid = "_tour" cuando el evento es del recorrido entero
 *     (completed/dismissed); cualquier otro string es step real (viewed).
 */
class telemetry_repository {

    /**
     * Resumen agregado para todos los tours. Retorna array indexado por tourid
     * con conteos: started, completed, dismissed, completion_rate.
     */
    public static function tour_summary(): array {
        global $DB;
        // Usamos get_recordset_sql porque get_records_sql keyea por primer columna
        // y aqui (tourid, action) puede tener filas con mismo tourid pero distinta
        // action → la segunda fila se perderia silenciosamente.
        $sql = "SELECT tourid, action, COUNT(1) AS cnt
                  FROM {local_adipaonboarding_events}
                 WHERE stepid = :tourstep
              GROUP BY tourid, action";
        $rs = $DB->get_recordset_sql($sql, ['tourstep' => '_tour']);
        $summary = [];
        foreach ($rs as $r) {
            $key = $r->tourid;
            if (!isset($summary[$key])) {
                $summary[$key] = ['tourid' => $key, 'completed' => 0, 'dismissed' => 0];
            }
            if (in_array($r->action, ['completed', 'dismissed'], true)) {
                $summary[$key][$r->action] = (int)$r->cnt;
            }
        }
        $rs->close();
        foreach ($summary as &$row) {
            $finished = $row['completed'] + $row['dismissed'];
            $row['finished'] = $finished;
            $row['completion_rate'] = $finished > 0 ? round($row['completed'] / $finished * 100, 1) : 0;
        }
        unset($row);
        return array_values($summary);
    }

    /**
     * Drop-off por step en un tour: cuantos usuarios vieron cada step.
     * Si un step tiene muchos viewed pero el siguiente tiene pocos, ahi se cae el funnel.
     * @return \stdClass[] rows con (stepid, viewed_count)
     */
    public static function step_drop_off(string $tourid): array {
        global $DB;
        $sql = "SELECT stepid, COUNT(DISTINCT userid) AS viewed_count
                  FROM {local_adipaonboarding_events}
                 WHERE tourid = :tourid
                   AND action = 'viewed'
                   AND stepid <> :tourstep
              GROUP BY stepid
              ORDER BY viewed_count DESC";
        return array_values($DB->get_records_sql($sql, [
            'tourid'   => $tourid,
            'tourstep' => '_tour',
        ]));
    }

    /**
     * Totales para overview del dashboard.
     */
    public static function totals(): array {
        global $DB;
        // Mismo motivo que tour_summary: action puede aparecer una vez por
        // valor; aca no hay colision (action es unique) pero usamos recordset
        // por consistencia.
        $sql = "SELECT action, COUNT(1) AS cnt
                  FROM {local_adipaonboarding_events}
                 WHERE stepid = :tourstep
              GROUP BY action";
        $rs = $DB->get_recordset_sql($sql, ['tourstep' => '_tour']);
        $totals = ['completed' => 0, 'dismissed' => 0];
        foreach ($rs as $r) {
            if (in_array($r->action, ['completed', 'dismissed'], true)) {
                $totals[$r->action] = (int)$r->cnt;
            }
        }
        $rs->close();
        $totals['finished'] = $totals['completed'] + $totals['dismissed'];
        $totals['completion_rate'] = $totals['finished'] > 0
            ? round($totals['completed'] / $totals['finished'] * 100, 1)
            : 0;
        return $totals;
    }
}
