<?php
namespace local_adipaonboarding\local\tour;

defined('MOODLE_INTERNAL') || die();

/**
 * CRUD sobre la Step Library (local_adipaonboarding_steps).
 *
 * Steps shipped usan titlelangkey/bodylangkey (multipais via lang files).
 * Steps custom (creados por admin desde UI) usan titletext/bodytext directo.
 * El resolver de texto en hydrate() prefiere text > langkey.
 */
class step_repository {

    public static function find_by_key(string $stepkey): ?step {
        global $DB;
        $row = $DB->get_record('local_adipaonboarding_steps', ['stepkey' => $stepkey]);
        if (!$row) {
            return null;
        }
        return self::hydrate($row);
    }

    public static function find_by_id(int $id): ?\stdClass {
        global $DB;
        $row = $DB->get_record('local_adipaonboarding_steps', ['id' => $id]);
        return $row ?: null;
    }

    public static function id_for_key(string $stepkey): ?int {
        global $DB;
        $id = $DB->get_field('local_adipaonboarding_steps', 'id', ['stepkey' => $stepkey]);
        return $id === false ? null : (int)$id;
    }

    /**
     * Lista todos los steps como rows DB (para admin UI).
     * @return \stdClass[]
     */
    public static function all_rows(): array {
        global $DB;
        return array_values($DB->get_records('local_adipaonboarding_steps', null, 'stepkey ASC'));
    }

    /**
     * Lista todos los steps hidratados como value objects (para resolver/tests).
     * @return step[]
     */
    public static function all(): array {
        $rows = self::all_rows();
        $steps = [];
        foreach ($rows as $row) {
            $steps[] = self::hydrate($row);
        }
        return $steps;
    }

    public static function insert(array $data, bool $shipped = false): int {
        global $DB;
        $now = time();
        return (int)$DB->insert_record('local_adipaonboarding_steps', (object)[
            'stepkey'        => $data['step_key'],
            'selector'       => $data['selector'],
            'titlelangkey'   => $data['title_lang_key'] ?? null,
            'bodylangkey'    => $data['body_lang_key'] ?? null,
            'titletext'      => $data['title_text'] ?? null,
            'bodytext'       => $data['body_text'] ?? null,
            'actionsjson'    => isset($data['actions']) && is_array($data['actions']) ? json_encode($data['actions']) : null,
            'placement'      => $data['placement'] ?? 'auto',
            'responsivejson' => isset($data['responsive']) ? json_encode($data['responsive']) : null,
            'shipped'        => $shipped ? 1 : 0,
            'dirty'          => 0,
            'createdat'      => $now,
            'updatedat'      => $now,
        ]);
    }

    public static function update_shipped_if_clean(string $stepkey, array $data): bool {
        global $DB;
        $existing = $DB->get_record('local_adipaonboarding_steps', ['stepkey' => $stepkey]);
        if (!$existing) {
            return false;
        }
        if ((int)$existing->dirty === 1) {
            return false;
        }
        $DB->update_record('local_adipaonboarding_steps', (object)[
            'id'             => $existing->id,
            'selector'       => $data['selector'],
            'titlelangkey'   => $data['title_lang_key'] ?? null,
            'bodylangkey'    => $data['body_lang_key'] ?? null,
            'actionsjson'    => isset($data['actions']) && is_array($data['actions']) ? json_encode($data['actions']) : null,
            'placement'      => $data['placement'] ?? 'auto',
            'responsivejson' => isset($data['responsive']) ? json_encode($data['responsive']) : null,
            'shipped'        => 1,
            'updatedat'      => time(),
        ]);
        return true;
    }

    /**
     * Update desde admin UI. Marca dirty=1.
     * $data acepta cualquier subset de {selector, title_text, body_text,
     * title_lang_key, body_lang_key, placement, responsive}.
     */
    public static function update_from_admin(int $id, array $data): void {
        global $DB;
        $update = (object)['id' => $id, 'dirty' => 1, 'updatedat' => time()];
        if (array_key_exists('selector', $data)) {
            $update->selector = $data['selector'];
        }
        if (array_key_exists('title_lang_key', $data)) {
            $update->titlelangkey = $data['title_lang_key'];
        }
        if (array_key_exists('body_lang_key', $data)) {
            $update->bodylangkey = $data['body_lang_key'];
        }
        if (array_key_exists('title_text', $data)) {
            $update->titletext = $data['title_text'];
        }
        if (array_key_exists('body_text', $data)) {
            $update->bodytext = $data['body_text'];
        }
        if (array_key_exists('actions', $data)) {
            $update->actionsjson = ($data['actions'] === null || $data['actions'] === [])
                ? null
                : json_encode($data['actions']);
        }
        if (array_key_exists('placement', $data)) {
            $update->placement = $data['placement'];
        }
        if (array_key_exists('responsive', $data)) {
            $update->responsivejson = json_encode($data['responsive']);
        }
        $DB->update_record('local_adipaonboarding_steps', $update);
    }

    /**
     * Borra un step. NO valida cascade — el caller (WS) decide via tours_using_step().
     */
    public static function delete(int $id): void {
        global $DB;
        $DB->delete_records('local_adipaonboarding_tsteps', ['stepid' => $id]);
        $DB->delete_records('local_adipaonboarding_steps', ['id' => $id]);
    }

    /**
     * Devuelve los tours que referencian este step. Para alertar al admin
     * antes de borrar.
     * @return \stdClass[] rows con (tourid, scope, coursetype)
     */
    public static function tours_using_step(int $stepid): array {
        global $DB;
        $sql = "SELECT DISTINCT t.id AS tourid, t.scope, t.coursetype
                  FROM {local_adipaonboarding_tsteps} ts
                  JOIN {local_adipaonboarding_tours} t ON t.id = ts.tourid
                 WHERE ts.stepid = :stepid
              ORDER BY t.scope ASC, t.coursetype ASC";
        return array_values($DB->get_records_sql($sql, ['stepid' => $stepid]));
    }

    /**
     * Reset to defaults: limpia titletext/bodytext, marca dirty=0, restaura
     * el seed shipped. El caller pasa $seeddata del archivo tours/*.php.
     */
    public static function reset_to_defaults(string $stepkey, array $seeddata): bool {
        global $DB;
        $existing = $DB->get_record('local_adipaonboarding_steps', ['stepkey' => $stepkey]);
        if (!$existing) {
            return false;
        }
        $DB->update_record('local_adipaonboarding_steps', (object)[
            'id'             => $existing->id,
            'selector'       => $seeddata['selector'],
            'titlelangkey'   => $seeddata['title_lang_key'] ?? null,
            'bodylangkey'    => $seeddata['body_lang_key'] ?? null,
            'titletext'      => null,
            'bodytext'       => null,
            'actionsjson'    => isset($seeddata['actions']) && is_array($seeddata['actions']) ? json_encode($seeddata['actions']) : null,
            'placement'      => $seeddata['placement'] ?? 'auto',
            'responsivejson' => isset($seeddata['responsive']) ? json_encode($seeddata['responsive']) : null,
            'shipped'        => 1,
            'dirty'          => 0,
            'updatedat'      => time(),
        ]);
        return true;
    }

    /**
     * Hidrata un row DB a un value object step.
     * Resuelve title/body: prefiere titletext > titlelangkey > '' (idem body).
     * Si pasa $override, sus campos (selector/placement/responsive) pisan.
     */
    public static function hydrate(\stdClass $row, ?array $override = null): step {
        $responsive = [];
        if (!empty($row->responsivejson)) {
            $decoded = json_decode($row->responsivejson, true);
            if (is_array($decoded)) {
                $responsive = $decoded;
            }
        }

        $title = self::resolve_text($row->titletext ?? null, $row->titlelangkey ?? null);
        $body  = self::resolve_text($row->bodytext ?? null, $row->bodylangkey ?? null);

        $element   = $row->selector;
        $placement = $row->placement;

        if ($override !== null) {
            if (isset($override['selector'])) {
                $element = $override['selector'];
            }
            if (isset($override['element'])) {
                $element = $override['element'];
            }
            if (isset($override['placement'])) {
                $placement = $override['placement'];
            }
            if (isset($override['responsive']) && is_array($override['responsive'])) {
                $responsive = $override['responsive'];
            }
        }

        $actions = [];
        if (!empty($row->actionsjson)) {
            $decoded = json_decode($row->actionsjson, true);
            if (is_array($decoded)) {
                $actions = $decoded;
            }
        }
        if ($override !== null && isset($override['actions']) && is_array($override['actions'])) {
            $actions = $override['actions'];
        }

        return new step([
            'id'         => $row->stepkey,
            'element'    => $element,
            'title'      => $title,
            'body'       => $body,
            'placement'  => $placement,
            'responsive' => $responsive,
            'actions'    => $actions,
        ]);
    }

    /**
     * Resuelve texto: text directo > lang key > vacio.
     */
    private static function resolve_text(?string $text, ?string $langkey): string {
        if ($text !== null && $text !== '') {
            return $text;
        }
        if ($langkey !== null && $langkey !== '') {
            try {
                return get_string($langkey, 'local_adipaonboarding');
            } catch (\Throwable $e) {
                return '[' . $langkey . ']';
            }
        }
        return '';
    }
}
