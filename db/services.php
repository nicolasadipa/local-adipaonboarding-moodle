<?php
defined('MOODLE_INTERNAL') || die();

$functions = [
    // Runtime (estudiantes).
    'local_adipaonboarding_mark_seen' => [
        'classname'     => 'local_adipaonboarding\local\external\mark_seen',
        'methodname'    => 'execute',
        'description'   => 'Marca un recorrido como visto/completado por el usuario.',
        'type'          => 'write',
        'ajax'          => true,
        'loginrequired' => true,
    ],
    'local_adipaonboarding_log_event' => [
        'classname'     => 'local_adipaonboarding\local\external\log_event',
        'methodname'    => 'execute',
        'description'   => 'Registra evento de telemetria.',
        'type'          => 'write',
        'ajax'          => true,
        'loginrequired' => true,
    ],

    // Admin: tours.
    'local_adipaonboarding_save_tour' => [
        'classname'     => 'local_adipaonboarding\local\external\save_tour',
        'methodname'    => 'execute',
        'description'   => 'Actualiza metadata de un tour.',
        'type'          => 'write',
        'ajax'          => true,
        'loginrequired' => true,
        'capabilities'  => 'local/adipaonboarding:manage_tours',
    ],
    'local_adipaonboarding_reorder_steps' => [
        'classname'     => 'local_adipaonboarding\local\external\reorder_steps',
        'methodname'    => 'execute',
        'description'   => 'Reemplaza la secuencia de steps de un tour.',
        'type'          => 'write',
        'ajax'          => true,
        'loginrequired' => true,
        'capabilities'  => 'local/adipaonboarding:manage_tours',
    ],
    'local_adipaonboarding_toggle_tour' => [
        'classname'     => 'local_adipaonboarding\local\external\toggle_tour',
        'methodname'    => 'execute',
        'description'   => 'Activa o desactiva un tour.',
        'type'          => 'write',
        'ajax'          => true,
        'loginrequired' => true,
        'capabilities'  => 'local/adipaonboarding:manage_tours',
    ],
    'local_adipaonboarding_bump_tour_version' => [
        'classname'     => 'local_adipaonboarding\local\external\bump_tour_version',
        'methodname'    => 'execute',
        'description'   => 'Sube la version del tour para relanzamiento incremental.',
        'type'          => 'write',
        'ajax'          => true,
        'loginrequired' => true,
        'capabilities'  => 'local/adipaonboarding:manage_tours',
    ],
    'local_adipaonboarding_reset_tour_defaults' => [
        'classname'     => 'local_adipaonboarding\local\external\reset_tour_defaults',
        'methodname'    => 'execute',
        'description'   => 'Restaura el tour al seed shipped.',
        'type'          => 'write',
        'ajax'          => true,
        'loginrequired' => true,
        'capabilities'  => 'local/adipaonboarding:manage_tours',
    ],
    'local_adipaonboarding_set_step_override' => [
        'classname'     => 'local_adipaonboarding\local\external\set_step_override',
        'methodname'    => 'execute',
        'description'   => 'Setea override per-tour-step.',
        'type'          => 'write',
        'ajax'          => true,
        'loginrequired' => true,
        'capabilities'  => 'local/adipaonboarding:manage_tours',
    ],

    // Admin: step library.
    'local_adipaonboarding_save_step' => [
        'classname'     => 'local_adipaonboarding\local\external\save_step',
        'methodname'    => 'execute',
        'description'   => 'Crea o actualiza un step de la library.',
        'type'          => 'write',
        'ajax'          => true,
        'loginrequired' => true,
        'capabilities'  => 'local/adipaonboarding:manage_steps',
    ],
    'local_adipaonboarding_delete_step' => [
        'classname'     => 'local_adipaonboarding\local\external\delete_step',
        'methodname'    => 'execute',
        'description'   => 'Borra un step (con cascade check).',
        'type'          => 'write',
        'ajax'          => true,
        'loginrequired' => true,
        'capabilities'  => 'local/adipaonboarding:manage_steps',
    ],
    'local_adipaonboarding_reset_step_defaults' => [
        'classname'     => 'local_adipaonboarding\local\external\reset_step_defaults',
        'methodname'    => 'execute',
        'description'   => 'Restaura un step al seed shipped.',
        'type'          => 'write',
        'ajax'          => true,
        'loginrequired' => true,
        'capabilities'  => 'local/adipaonboarding:manage_steps',
    ],

    // Per-course opt-out.
    'local_adipaonboarding_save_course_optout' => [
        'classname'     => 'local_adipaonboarding\local\external\save_course_optout',
        'methodname'    => 'execute',
        'description'   => 'Toggle de opt-out de tour para un curso.',
        'type'          => 'write',
        'ajax'          => true,
        'loginrequired' => true,
    ],
];
