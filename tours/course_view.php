<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Seed del scope course_view: Step Library + Tour Composition para los 7
 * course_types soportados por format_adipa.
 *
 * Estrategia:
 *   - Una sola Step Library compartida (helper steps_library()).
 *   - 7 tours, uno por course_type, cada uno con su propia secuencia de step_keys.
 *   - Sync types (course/diploma/postitulo/acreditacion): incluyen session_pill,
 *     countdown, certification. Acreditacion ademas tiene 'documentation_tile'.
 *   - Async types (especializacion/magistral/asincronico): omiten session_pill,
 *     countdown y certification (no aplican).
 *
 * Selectores verificados contra HTML de format_adipa.
 */
function local_adipaonboarding_course_view_seeds(): array {
    return [
        'steps' => local_adipaonboarding_course_view_steps_library(),
        'tours' => local_adipaonboarding_course_view_tours(),
    ];
}

function local_adipaonboarding_course_view_steps_library(): array {
    return [
        [
            'step_key' => 'welcome', 'selector' => 'modal',
            'title_lang_key' => 'welcome_title', 'body_lang_key' => 'welcome_body',
            'placement' => 'auto', 'responsive' => [],
        ],
        [
            'step_key' => 'header', 'selector' => '.adipa-s0-banner',
            'title_lang_key' => 'step_header_title', 'body_lang_key' => 'step_header_body',
            'placement' => 'bottom', 'responsive' => ['mobile' => ['placement' => 'bottom']],
        ],
        [
            'step_key' => 'view_toggle', 'selector' => '.adipa-view-toggle',
            'title_lang_key' => 'step_view_toggle_title', 'body_lang_key' => 'step_view_toggle_body',
            'placement' => 'bottom', 'responsive' => ['mobile' => ['placement' => 'bottom']],
        ],
        [
            'step_key' => 'session_pill', 'selector' => '.adipa-s0-pill',
            'title_lang_key' => 'step_session_pill_title', 'body_lang_key' => 'step_session_pill_body',
            'placement' => 'bottom', 'responsive' => ['mobile' => ['placement' => 'bottom']],
        ],
        [
            'step_key' => 'countdown', 'selector' => '.adipa-s0-clock',
            'title_lang_key' => 'step_countdown_title', 'body_lang_key' => 'step_countdown_body',
            // El banner colapsa a 1 columna en <700px y el reloj puede quedar oculto;
            // si esta visible se muestra como bottom, sino el elementVisible lo skipea.
            'placement' => 'bottom', 'responsive' => ['mobile' => ['placement' => 'bottom']],
        ],
        [
            'step_key' => 'adipainfo_card', 'selector' => '.adipainfo-card',
            'title_lang_key' => 'step_adipainfo_title', 'body_lang_key' => 'step_adipainfo_body',
            'placement' => 'top', 'responsive' => ['mobile' => ['placement' => 'bottom']],
        ],
        [
            'step_key' => 'first_module', 'selector' => '.adipa-tile:first-of-type',
            'title_lang_key' => 'step_first_module_title', 'body_lang_key' => 'step_first_module_body',
            'placement' => 'right', 'responsive' => ['mobile' => ['placement' => 'bottom']],
        ],
        [
            'step_key' => 'nid_row', 'selector' => '.adipanid-row',
            'title_lang_key' => 'step_nid_title', 'body_lang_key' => 'step_nid_body',
            // Bottom explicito + scroll-into-view en runner para no quedar entre
            // menu y seccion. Mobile usa top para no taparse con el siguiente bloque.
            'placement' => 'bottom', 'responsive' => ['mobile' => ['placement' => 'top']],
        ],
        [
            'step_key' => 'certification', 'selector' => '.adipainfo-cert:not(.adipanid-row)',
            'title_lang_key' => 'step_certification_title', 'body_lang_key' => 'step_certification_body',
            'placement' => 'top', 'responsive' => ['mobile' => ['placement' => 'bottom']],
        ],
        // Step extra para acreditacion: tile de Documentacion (primer tile especial).
        [
            'step_key' => 'documentation_tile', 'selector' => '.adipa-tile.adipa-tile-docs, .adipa-tile:first-of-type',
            'title_lang_key' => 'step_documentation_title', 'body_lang_key' => 'step_documentation_body',
            'placement' => 'right', 'responsive' => ['mobile' => ['placement' => 'bottom']],
        ],
        [
            'step_key' => 'closing', 'selector' => 'modal',
            'title_lang_key' => 'closing_title', 'body_lang_key' => 'closing_body',
            'placement' => 'auto', 'responsive' => [],
        ],
    ];
}

function local_adipaonboarding_course_view_tours(): array {
    $visibility = [
        'delay_ms'     => 7000,
        'min_viewport' => 320,
        'frequency'    => 'once_per_user',
    ];

    // Secuencia para sync course_types (course, diploma, postitulo).
    $syncsteps = [
        'welcome', 'header', 'view_toggle', 'session_pill', 'countdown',
        'adipainfo_card', 'first_module',
        'nid_row', 'certification', 'closing',
    ];

    // Secuencia para async course_types (especializacion, magistral, asincronico).
    // Sin session_pill, countdown, certification (no aplican).
    $asyncsteps = [
        'welcome', 'header', 'view_toggle',
        'adipainfo_card', 'first_module',
        'nid_row', 'closing',
    ];

    // Secuencia para acreditacion: sync + step Documentacion extra antes del primer modulo.
    $acreditacionsteps = [
        'welcome', 'header', 'view_toggle', 'session_pill', 'countdown',
        'adipainfo_card', 'documentation_tile', 'first_module',
        'nid_row', 'certification', 'closing',
    ];

    return [
        [
            'scope' => 'course_view', 'course_type' => 'course',
            'version' => 6, 'enabled' => true, 'visibility' => $visibility,
            'step_keys' => $syncsteps,
        ],
        [
            'scope' => 'course_view', 'course_type' => 'diploma',
            'version' => 2, 'enabled' => true, 'visibility' => $visibility,
            'step_keys' => $syncsteps,
        ],
        [
            'scope' => 'course_view', 'course_type' => 'postitulo',
            'version' => 2, 'enabled' => true, 'visibility' => $visibility,
            'step_keys' => $syncsteps,
        ],
        [
            'scope' => 'course_view', 'course_type' => 'acreditacion',
            'version' => 2, 'enabled' => true, 'visibility' => $visibility,
            'step_keys' => $acreditacionsteps,
        ],
        [
            'scope' => 'course_view', 'course_type' => 'especializacion',
            'version' => 2, 'enabled' => true, 'visibility' => $visibility,
            'step_keys' => $asyncsteps,
        ],
        [
            'scope' => 'course_view', 'course_type' => 'magistral',
            'version' => 2, 'enabled' => true, 'visibility' => $visibility,
            'step_keys' => $asyncsteps,
        ],
        [
            'scope' => 'course_view', 'course_type' => 'asincronico',
            'version' => 2, 'enabled' => true, 'visibility' => $visibility,
            'step_keys' => $asyncsteps,
        ],
    ];
}
