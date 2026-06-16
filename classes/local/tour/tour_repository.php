<?php
namespace local_adipaonboarding\local\tour;

defined('MOODLE_INTERNAL') || die();

/**
 * CRUD sobre tours + tour_steps. Construye manifests hidratando desde DB.
 */
class tour_repository {

    /**
     * Construye el manifest para (scope, course_type). Prioriza match exacto,
     * cae a coursetype=NULL si no encuentra (para scopes no por curso).
     * Devuelve null si no hay tour habilitado o esta vacio.
     */
    public static function build_manifest_for(string $scope, ?string $coursetype): ?manifest {
        global $DB;

        $params = ['scope' => $scope, 'enabled' => 1];
        $where  = 'scope = :scope AND enabled = :enabled';

        if ($coursetype !== null) {
            $where .= ' AND (coursetype = :coursetype OR coursetype IS NULL)';
            $params['coursetype'] = $coursetype;
            $orderby = 'CASE WHEN coursetype IS NULL THEN 1 ELSE 0 END ASC, version DESC';
        } else {
            $where .= ' AND coursetype IS NULL';
            $orderby = 'version DESC';
        }

        $tourrow = $DB->get_record_sql(
            "SELECT * FROM {local_adipaonboarding_tours} WHERE $where ORDER BY $orderby",
            $params,
            IGNORE_MULTIPLE
        );
        if (!$tourrow) {
            return null;
        }

        $steps = self::load_steps_for_tour((int)$tourrow->id);
        if (empty($steps)) {
            return null;
        }

        $visibility = [];
        if (!empty($tourrow->visibilityjson)) {
            $decoded = json_decode($tourrow->visibilityjson, true);
            if (is_array($decoded)) {
                $visibility = $decoded;
            }
        }

        $tourid = self::compose_manifest_id($tourrow->scope, $tourrow->coursetype);

        return new manifest($tourid, (int)$tourrow->version, $steps, $visibility);
    }

    /**
     * Lista todos los tours (para admin list page).
     * @return array de stdClass enriquecidos con stepcount.
     */
    public static function all_with_counts(): array {
        global $DB;
        $sql = "SELECT t.*, (SELECT COUNT(1) FROM {local_adipaonboarding_tsteps} ts WHERE ts.tourid = t.id) AS stepcount
                  FROM {local_adipaonboarding_tours} t
              ORDER BY t.scope ASC, t.coursetype ASC, t.version DESC";
        return array_values($DB->get_records_sql($sql));
    }

    /**
     * Devuelve un tour por id, con sus tour_steps + step library hidratados.
     * @return array{tour: \stdClass, items: array<int,array{tourstep:\stdClass,step:\stdClass}>}|null
     */
    public static function get_with_items(int $tourid): ?array {
        global $DB;
        $tour = $DB->get_record('local_adipaonboarding_tours', ['id' => $tourid]);
        if (!$tour) {
            return null;
        }
        $sql = "SELECT ts.id AS tsid, ts.sortorder, ts.overridejson,
                       s.id AS stepid, s.stepkey, s.selector, s.titlelangkey, s.bodylangkey, s.placement, s.responsivejson
                  FROM {local_adipaonboarding_tsteps} ts
                  JOIN {local_adipaonboarding_steps} s ON s.id = ts.stepid
                 WHERE ts.tourid = :tourid
              ORDER BY ts.sortorder ASC";
        $rows = $DB->get_records_sql($sql, ['tourid' => $tourid]);
        $items = [];
        foreach ($rows as $r) {
            $items[] = (object)[
                'tsid'         => (int)$r->tsid,
                'sortorder'    => (int)$r->sortorder,
                'overridejson' => $r->overridejson,
                'step'         => (object)[
                    'id'           => (int)$r->stepid,
                    'stepkey'      => $r->stepkey,
                    'selector'     => $r->selector,
                    'titlelangkey' => $r->titlelangkey,
                    'bodylangkey'  => $r->bodylangkey,
                    'placement'    => $r->placement,
                    'responsivejson' => $r->responsivejson,
                ],
            ];
        }
        return ['tour' => $tour, 'items' => $items];
    }

    /**
     * Insert tour nuevo. No setea step refs (usar replace_steps despues).
     */
    public static function insert(array $data, bool $shipped = false): int {
        global $DB;
        $now = time();
        return (int)$DB->insert_record('local_adipaonboarding_tours', (object)[
            'scope'          => $data['scope'],
            'coursetype'     => $data['course_type'] ?? null,
            'version'        => isset($data['version']) ? (int)$data['version'] : 1,
            'enabled'        => isset($data['enabled']) ? ($data['enabled'] ? 1 : 0) : 1,
            'visibilityjson' => json_encode($data['visibility'] ?? []),
            'shipped'        => $shipped ? 1 : 0,
            'dirty'          => 0,
            'createdat'      => $now,
            'updatedat'      => $now,
        ]);
    }

    /**
     * Busca tour shipped por (scope, course_type). Devuelve fila DB o null.
     */
    public static function find_shipped(string $scope, ?string $coursetype): ?\stdClass {
        global $DB;
        $row = $DB->get_record('local_adipaonboarding_tours', [
            'scope'      => $scope,
            'coursetype' => $coursetype,
            'shipped'    => 1,
        ]);
        return $row ?: null;
    }

    /**
     * Reemplaza atomicamente la secuencia de steps de un tour.
     * $items = [['step_id' => 5, 'override' => null], ...] en orden.
     */
    public static function replace_steps(int $tourid, array $items): void {
        global $DB;
        $transaction = $DB->start_delegated_transaction();
        try {
            $DB->delete_records('local_adipaonboarding_tsteps', ['tourid' => $tourid]);
            $sort = 0;
            foreach ($items as $item) {
                $DB->insert_record('local_adipaonboarding_tsteps', (object)[
                    'tourid'       => $tourid,
                    'stepid'       => (int)$item['step_id'],
                    'sortorder'    => $sort,
                    'overridejson' => !empty($item['override']) ? json_encode($item['override']) : null,
                ]);
                $sort++;
            }
            $DB->update_record('local_adipaonboarding_tours', (object)[
                'id'        => $tourid,
                'updatedat' => time(),
            ]);
            $transaction->allow_commit();
        } catch (\Throwable $e) {
            $transaction->rollback($e);
        }
    }

    public static function update_metadata_from_admin(int $tourid, array $data): void {
        global $DB;
        $update = (object)['id' => $tourid, 'dirty' => 1, 'updatedat' => time()];
        if (isset($data['enabled'])) {
            $update->enabled = $data['enabled'] ? 1 : 0;
        }
        if (isset($data['visibility'])) {
            $update->visibilityjson = json_encode($data['visibility']);
        }
        if (isset($data['version'])) {
            $update->version = (int)$data['version'];
        }
        $DB->update_record('local_adipaonboarding_tours', $update);
    }

    /**
     * Marca el tour como dirty (admin lo edito). Usado tras cambios estructurales
     * como reordenar steps, donde no hay otra columna que actualizar.
     */
    public static function mark_dirty(int $tourid): void {
        global $DB;
        $DB->update_record('local_adipaonboarding_tours', (object)[
            'id'        => $tourid,
            'dirty'     => 1,
            'updatedat' => time(),
        ]);
    }

    public static function toggle_enabled(int $tourid, bool $enabled): void {
        global $DB;
        $DB->update_record('local_adipaonboarding_tours', (object)[
            'id'        => $tourid,
            'enabled'   => $enabled ? 1 : 0,
            'dirty'     => 1,
            'updatedat' => time(),
        ]);
    }

    public static function bump_version(int $tourid): int {
        global $DB;
        $current = (int)$DB->get_field('local_adipaonboarding_tours', 'version', ['id' => $tourid]);
        $new = $current + 1;
        $DB->update_record('local_adipaonboarding_tours', (object)[
            'id'        => $tourid,
            'version'   => $new,
            'dirty'     => 1,
            'updatedat' => time(),
        ]);
        return $new;
    }

    /**
     * Setea (o limpia con null) el override de un step puntual dentro de un tour.
     */
    public static function set_step_override(int $tourstepid, ?array $override): void {
        global $DB;
        $DB->update_record('local_adipaonboarding_tsteps', (object)[
            'id'           => $tourstepid,
            'overridejson' => $override !== null ? json_encode($override) : null,
        ]);
        // Marcar el tour padre dirty.
        $tourid = (int)$DB->get_field('local_adipaonboarding_tsteps', 'tourid', ['id' => $tourstepid]);
        if ($tourid > 0) {
            self::mark_dirty($tourid);
        }
    }

    /**
     * Reset to defaults: restaura el tour a su seed shipped. $seeddata es la entrada
     * del array tours/* del seeder. Borra overrides, reconstruye junction.
     */
    public static function reset_to_defaults(int $tourid, array $seeddata): bool {
        global $DB;
        $existing = $DB->get_record('local_adipaonboarding_tours', ['id' => $tourid]);
        if (!$existing) {
            return false;
        }
        $DB->update_record('local_adipaonboarding_tours', (object)[
            'id'             => $tourid,
            'enabled'        => isset($seeddata['enabled']) ? ($seeddata['enabled'] ? 1 : 0) : 1,
            'visibilityjson' => json_encode($seeddata['visibility'] ?? []),
            'version'        => isset($seeddata['version']) ? (int)$seeddata['version'] : 1,
            'shipped'        => 1,
            'dirty'          => 0,
            'updatedat'      => time(),
        ]);
        // Reconstruir steps.
        $items = [];
        $stepkeys = $seeddata['step_keys'] ?? [];
        foreach ($stepkeys as $entry) {
            $stepkey = is_array($entry) ? $entry['key'] : $entry;
            $stepid = step_repository::id_for_key($stepkey);
            if ($stepid !== null) {
                $items[] = ['step_id' => $stepid, 'override' => null];
            }
        }
        self::replace_steps($tourid, $items);
        return true;
    }

    /**
     * Construye manifest por id directo (no por scope+coursetype). Para preview admin:
     * funciona incluso si el tour esta disabled. Devuelve null si el tour no existe
     * o no tiene steps.
     */
    public static function build_manifest_by_id(int $tourid): ?manifest {
        global $DB;
        $tourrow = $DB->get_record('local_adipaonboarding_tours', ['id' => $tourid]);
        if (!$tourrow) {
            return null;
        }
        $steps = self::load_steps_for_tour((int)$tourrow->id);
        if (empty($steps)) {
            return null;
        }
        $visibility = [];
        if (!empty($tourrow->visibilityjson)) {
            $decoded = json_decode($tourrow->visibilityjson, true);
            if (is_array($decoded)) {
                $visibility = $decoded;
            }
        }
        $manifestid = self::compose_manifest_id(
            (string)$tourrow->scope,
            $tourrow->coursetype !== null ? (string)$tourrow->coursetype : null
        );
        return new manifest($manifestid, (int)$tourrow->version, $steps, $visibility);
    }

    /**
     * Carga steps hidratados de un tour (con overrides aplicados).
     * @return step[]
     */
    private static function load_steps_for_tour(int $tourid): array {
        global $DB;
        $sql = "SELECT s.*, ts.overridejson, ts.sortorder
                  FROM {local_adipaonboarding_tsteps} ts
                  JOIN {local_adipaonboarding_steps} s ON s.id = ts.stepid
                 WHERE ts.tourid = :tourid
              ORDER BY ts.sortorder ASC";
        $rows = $DB->get_records_sql($sql, ['tourid' => $tourid]);
        $steps = [];
        foreach ($rows as $row) {
            $override = null;
            if (!empty($row->overridejson)) {
                $decoded = json_decode($row->overridejson, true);
                if (is_array($decoded)) {
                    $override = $decoded;
                }
            }
            $steps[] = step_repository::hydrate($row, $override);
        }
        return $steps;
    }

    /**
     * Compone el manifest id que ve el cliente (estable a traves de versiones).
     */
    private static function compose_manifest_id(string $scope, ?string $coursetype): string {
        if ($coursetype === null || $coursetype === '') {
            return $scope;
        }
        return $scope . '_' . $coursetype;
    }
}
