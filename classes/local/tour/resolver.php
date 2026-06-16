<?php
namespace local_adipaonboarding\local\tour;

defined('MOODLE_INTERNAL') || die();

/**
 * Resolver: unica entrada para pedir "que manifest aplica a esta pagina".
 *
 * Scopes soportados:
 *   - course_view: course-view-* (requiere course con course_type via format_adipa)
 *   - mod_adipavideo: mod-adipavideo-view
 */
class resolver {

    public static function for_page(\moodle_page $page): ?manifest {
        $pagetype = $page->pagetype;

        // Necesitamos un curso real (no SITE).
        $course = $page->course;
        if (!$course || (int)$course->id === SITEID) {
            return null;
        }
        if (course_optout_repository::is_disabled((int)$course->id)) {
            return null;
        }

        // Scope: course view (format_adipa con course_type).
        if (self::is_course_view_page($pagetype)) {
            $coursetype = self::resolve_course_type($course);
            return tour_repository::build_manifest_for('course_view', $coursetype);
        }

        // Scope: mod_adipavideo viewer.
        if ($pagetype === 'mod-adipavideo-view') {
            return tour_repository::build_manifest_for('mod_adipavideo', null);
        }

        return null;
    }

    private static function is_course_view_page(string $pagetype): bool {
        return strpos($pagetype, 'course-view-') === 0;
    }

    private static function resolve_course_type(\stdClass $course): string {
        try {
            $format = course_get_format($course);
            $options = $format->get_format_options();
            return isset($options['course_type']) ? (string)$options['course_type'] : 'course';
        } catch (\Throwable $e) {
            return 'course';
        }
    }
}
