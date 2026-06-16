<?php
namespace local_adipaonboarding\local\tour;

defined('MOODLE_INTERNAL') || die();

/**
 * Seeder: lee los archivos en tours/*.php (que devuelven seed arrays) e
 * inserta/actualiza la Step Library + Tours en DB.
 *
 * Reglas:
 *   - Si no existe en DB → insertar (shipped=1, dirty=0).
 *   - Si existe y dirty=0 → actualizar campos del seed.
 *   - Si existe y dirty=1 → no tocar (admin lo edito).
 *
 * Tambien expone helpers para reset_to_defaults: get_step_seed() / get_tour_seed().
 */
class seeder {

    /**
     * Lista de archivos seed. Centralizada para que get_*_seed() los recorra
     * sin duplicar logica.
     */
    private static function seed_files(): array {
        global $CFG;
        return [
            $CFG->dirroot . '/local/adipaonboarding/tours/course_view.php',
            $CFG->dirroot . '/local/adipaonboarding/tours/mod_adipavideo.php',
        ];
    }

    /**
     * Cada scope expone una funcion `local_adipaonboarding_<scope>_seeds()`.
     * Las cargamos todas y mergeamos steps + tours en un solo array.
     */
    private static function seed_functions(): array {
        return [
            'local_adipaonboarding_course_view_seeds',
            'local_adipaonboarding_mod_adipavideo_seeds',
        ];
    }

    private static function load_all_seeds(): array {
        $seeds = ['steps' => [], 'tours' => []];
        foreach (self::seed_files() as $file) {
            if (file_exists($file)) {
                require_once($file);
            }
        }
        foreach (self::seed_functions() as $fn) {
            if (function_exists($fn)) {
                $s = $fn();
                if (isset($s['steps'])) {
                    $seeds['steps'] = array_merge($seeds['steps'], $s['steps']);
                }
                if (isset($s['tours'])) {
                    $seeds['tours'] = array_merge($seeds['tours'], $s['tours']);
                }
            }
        }
        // Deduplicar steps por step_key (course_view + dashboard pueden compartir nombres).
        $seen = [];
        $uniq = [];
        foreach ($seeds['steps'] as $st) {
            if (isset($seen[$st['step_key']])) {
                continue;
            }
            $seen[$st['step_key']] = true;
            $uniq[] = $st;
        }
        $seeds['steps'] = $uniq;
        return $seeds;
    }

    public static function run(): void {
        $seed = self::load_all_seeds();
        self::seed_steps($seed['steps']);
        self::seed_tours($seed['tours']);
    }

    /**
     * Devuelve el array seed de un step (por step_key), o null.
     */
    public static function get_step_seed(string $stepkey): ?array {
        $seed = self::load_all_seeds();
        foreach ($seed['steps'] as $stepseed) {
            if ($stepseed['step_key'] === $stepkey) {
                return $stepseed;
            }
        }
        return null;
    }

    /**
     * Devuelve el array seed de un tour (por scope + coursetype), o null.
     */
    public static function get_tour_seed(string $scope, ?string $coursetype): ?array {
        $seed = self::load_all_seeds();
        foreach ($seed['tours'] as $tourseed) {
            $seedct = $tourseed['course_type'] ?? null;
            if ($tourseed['scope'] === $scope && $seedct === $coursetype) {
                return $tourseed;
            }
        }
        return null;
    }

    private static function seed_steps(array $steps): void {
        foreach ($steps as $stepseed) {
            $existing = step_repository::find_by_key($stepseed['step_key']);
            if ($existing === null) {
                step_repository::insert($stepseed, true);
            } else {
                step_repository::update_shipped_if_clean($stepseed['step_key'], $stepseed);
            }
        }
    }

    private static function seed_tours(array $tours): void {
        global $DB;
        foreach ($tours as $tourseed) {
            $existing = tour_repository::find_shipped(
                $tourseed['scope'],
                $tourseed['course_type'] ?? null
            );

            if ($existing === null) {
                $tourid = tour_repository::insert($tourseed, true);
                self::sync_tour_steps($tourid, $tourseed['step_keys']);
                continue;
            }

            if ((int)$existing->dirty === 1) {
                continue;
            }

            $DB->update_record('local_adipaonboarding_tours', (object)[
                'id'             => $existing->id,
                'version'        => isset($tourseed['version']) ? (int)$tourseed['version'] : 1,
                'enabled'        => isset($tourseed['enabled']) ? ($tourseed['enabled'] ? 1 : 0) : 1,
                'visibilityjson' => json_encode($tourseed['visibility'] ?? []),
                'updatedat'      => time(),
            ]);
            self::sync_tour_steps((int)$existing->id, $tourseed['step_keys']);
        }
    }

    private static function sync_tour_steps(int $tourid, array $stepkeys): void {
        $items = [];
        foreach ($stepkeys as $entry) {
            if (is_string($entry)) {
                $stepkey = $entry;
                $override = null;
            } else {
                $stepkey = $entry['key'];
                $override = $entry['override'] ?? null;
            }
            $stepid = step_repository::id_for_key($stepkey);
            if ($stepid === null) {
                continue;
            }
            $items[] = ['step_id' => $stepid, 'override' => $override];
        }
        tour_repository::replace_steps($tourid, $items);
    }
}
