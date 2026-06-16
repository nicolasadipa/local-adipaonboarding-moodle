<?php
namespace local_adipaonboarding\local\tour;

defined('MOODLE_INTERNAL') || die();

/**
 * CRUD del opt-out per-course. Una fila por curso. Si optout=1, el tour
 * no se muestra a los estudiantes de ese curso (independiente de course_type).
 */
class course_optout_repository {

    /**
     * ¿Este curso esta opt-out del tour?
     */
    public static function is_disabled(int $courseid): bool {
        global $DB;
        $value = $DB->get_field('local_adipaonboarding_optout', 'optout', ['courseid' => $courseid]);
        return $value !== false && (int)$value === 1;
    }

    /**
     * Setea (upsert) el estado de opt-out para un curso. $userid es quien lo cambio.
     */
    public static function set(int $courseid, bool $optout, int $userid): void {
        global $DB;
        $existing = $DB->get_record('local_adipaonboarding_optout', ['courseid' => $courseid]);
        $payload = (object)[
            'courseid'  => $courseid,
            'optout'    => $optout ? 1 : 0,
            'updatedby' => $userid,
            'updatedat' => time(),
        ];
        if ($existing) {
            $payload->id = $existing->id;
            $DB->update_record('local_adipaonboarding_optout', $payload);
        } else {
            $DB->insert_record('local_adipaonboarding_optout', $payload);
        }
    }
}
