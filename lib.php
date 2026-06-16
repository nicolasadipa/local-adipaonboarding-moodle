<?php
defined('MOODLE_INTERNAL') || die();

use local_adipaonboarding\local\tour\resolver;
use local_adipaonboarding\local\tour\tour_repository;
use local_adipaonboarding\local\visibility\rules;

/**
 * Inyecta el motor de onboarding en el footer del curso.
 * Tiene 3 modos de operacion:
 *   1. Test selector (admin con manage_steps): query ?adipaonboarding_test_selector=...
 *   2. Preview tour (admin con manage_tours): query ?adipaonboarding_preview_tour=ID
 *   3. Runtime normal (estudiante): solo si pasa todas las reglas + esta inscrito.
 *
 * Handoff del payload al cliente: el payload va embebido como <script type="application/json">
 * en el HTML retornado (DOM handoff), NO como argumento de js_call_amd — Moodle warna cuando
 * los args superan 1024 chars, y nuestros tours con 9-11 steps siempre superan ese limite.
 */
function local_adipaonboarding_before_footer() {
    global $PAGE, $USER;

    if (isguestuser() || !isloggedin()) {
        return '';
    }

    $systemcontext = context_system::instance();

    // 1. Selector tester (admin). Funciona INCLUSO con plugin disabled
    //    porque es herramienta de testing para admin.
    $testselector = optional_param('adipaonboarding_test_selector', '', PARAM_RAW_TRIMMED);
    if ($testselector !== '' && has_capability('local/adipaonboarding:manage_steps', $systemcontext)) {
        $PAGE->requires->js_call_amd('local_adipaonboarding/selector_tester', 'init', [$testselector]);
        return '';
    }

    // 2. Preview tour (admin). Mismo: funciona con plugin disabled.
    $previewtourid = optional_param('adipaonboarding_preview_tour', 0, PARAM_INT);
    if ($previewtourid > 0 && has_capability('local/adipaonboarding:manage_tours', $systemcontext)) {
        $manifest = tour_repository::build_manifest_by_id($previewtourid);
        if ($manifest !== null) {
            $payload = $manifest->payload_for_client();
            $payload['preview'] = true;
            $PAGE->requires->js_call_amd('local_adipaonboarding/runner', 'runPreview', []);
            return local_adipaonboarding_emit_payload_script($payload);
        }
        return '';
    }

    // 3. Runtime normal (estudiantes): requiere plugin enabled.
    if (!get_config('local_adipaonboarding', 'enabled')) {
        return '';
    }
    if (is_siteadmin()) {
        return '';
    }
    $manifest = resolver::for_page($PAGE);
    if ($manifest === null) {
        return '';
    }

    // Ambos scopes (course_view, mod_adipavideo) requieren curso real. El resolver
    // ya devolvio null si no hay curso, asi que aca SI hay $PAGE->course->id.
    $courseid = (int)$PAGE->course->id;
    $restricted = trim((string)get_config('local_adipaonboarding', 'restricted_courses'));
    if ($restricted !== '') {
        $allowedids = array_filter(array_map('intval', preg_split('/[\s,]+/', $restricted)));
        if (!empty($allowedids) && !in_array($courseid, $allowedids, true)) {
            return '';
        }
    }
    // Role check: solo estudiantes inscritos sin permiso de edicion.
    $coursecontext = context_course::instance($courseid);
    if (has_capability('moodle/course:update', $coursecontext)) {
        return '';
    }
    if (!is_enrolled($coursecontext, $USER, '', true)) {
        return '';
    }

    $payload = $manifest->payload_for_client();
    $PAGE->requires->js_call_amd('local_adipaonboarding/trigger', 'init', []);
    if (rules::should_show($manifest, $USER->id)) {
        $PAGE->requires->js_call_amd('local_adipaonboarding/runner', 'init', []);
    }

    return local_adipaonboarding_emit_payload_script($payload);
}

/**
 * Emite el payload como JSON dentro de <script type="application/json">.
 * Usa flags HEX_TAG/QUOT/AMP/APOS para que el JSON sea seguro de embeber sin
 * riesgo de cerrar el script tag accidentalmente (defense in depth contra
 * cualquier admin que ponga </script> en un body_text).
 */
function local_adipaonboarding_emit_payload_script(array $payload): string {
    $json = json_encode(
        $payload,
        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE
    );
    if ($json === false) {
        return '';
    }
    return '<script type="application/json" id="adipa-onboarding-manifest">' . $json . '</script>';
}

/**
 * Helper para setup boilerplate de paginas admin del plugin.
 * Centraliza admin_externalpage_setup + set_title/heading + CSS include.
 * Llamar desde el top de cualquier admin/*.php.
 *
 * @param string $pagename   ID externo en settings.php (ej: 'local_adipaonboarding_tours').
 * @param string $titlekey   Lang key para set_title + set_heading.
 */
function local_adipaonboarding_admin_page_setup(string $pagename, string $titlekey): void {
    global $PAGE;
    admin_externalpage_setup($pagename);
    $title = get_string($titlekey, 'local_adipaonboarding');
    $PAGE->set_title($title);
    $PAGE->set_heading($title);
    $PAGE->requires->css('/local/adipaonboarding/styles.css');
}

/**
 * Agrega un nodo "Onboarding" en el menu de administracion del curso para
 * que profesores/managers puedan opt-out de su curso.
 */
function local_adipaonboarding_extend_navigation_course(
    navigation_node $navigation,
    stdClass $course,
    context_course $coursecontext
) {
    if (!has_capability('moodle/course:update', $coursecontext)) {
        return;
    }
    $url = new moodle_url('/local/adipaonboarding/admin/course_optout.php', ['courseid' => $course->id]);
    $node = navigation_node::create(
        get_string('course_optout_nav_label', 'local_adipaonboarding'),
        $url,
        navigation_node::TYPE_SETTING,
        null,
        'local_adipaonboarding_optout',
        new pix_icon('i/settings', '')
    );
    $navigation->add_node($node);
}
