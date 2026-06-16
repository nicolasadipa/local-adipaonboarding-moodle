<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    // Category bajo localplugins → permite anidar varias subpaginas.
    $ADMIN->add('localplugins', new admin_category(
        'local_adipaonboarding_cat',
        new lang_string('pluginname', 'local_adipaonboarding')
    ));

    // Pagina principal de settings.
    $settings = new admin_settingpage(
        'local_adipaonboarding_settings',
        new lang_string('admin_section_settings', 'local_adipaonboarding')
    );
    $ADMIN->add('local_adipaonboarding_cat', $settings);

    $settings->add(new admin_setting_configcheckbox(
        'local_adipaonboarding/enabled',
        new lang_string('setting_enabled', 'local_adipaonboarding'),
        new lang_string('setting_enabled_desc', 'local_adipaonboarding'),
        1
    ));

    $settings->add(new admin_setting_configtext(
        'local_adipaonboarding/default_delay',
        new lang_string('setting_default_delay', 'local_adipaonboarding'),
        new lang_string('setting_default_delay_desc', 'local_adipaonboarding'),
        7000,
        PARAM_INT
    ));

    $settings->add(new admin_setting_configtext(
        'local_adipaonboarding/restricted_courses',
        new lang_string('setting_restricted_courses', 'local_adipaonboarding'),
        new lang_string('setting_restricted_courses_desc', 'local_adipaonboarding'),
        '',
        PARAM_TEXT
    ));

    $reseturl = new moodle_url('/local/adipaonboarding/admin/reset_seen.php', ['sesskey' => sesskey()]);
    $resetdesc = html_writer::link(
        $reseturl,
        get_string('setting_reset_all_btn', 'local_adipaonboarding'),
        ['class' => 'btn btn-secondary']
    );
    $resetdesc .= '<p class="text-muted mt-2">'
        . get_string('setting_reset_all_desc', 'local_adipaonboarding')
        . '</p>';
    $settings->add(new admin_setting_description(
        'local_adipaonboarding/reset_all',
        new lang_string('setting_reset_all', 'local_adipaonboarding'),
        $resetdesc
    ));

    // Subpaginas: Tours, Step Library, Telemetria.
    $ADMIN->add('local_adipaonboarding_cat', new admin_externalpage(
        'local_adipaonboarding_tours',
        new lang_string('admin_section_tours', 'local_adipaonboarding'),
        new moodle_url('/local/adipaonboarding/admin/tours.php'),
        'local/adipaonboarding:manage_tours'
    ));
    $ADMIN->add('local_adipaonboarding_cat', new admin_externalpage(
        'local_adipaonboarding_steps',
        new lang_string('admin_section_steps', 'local_adipaonboarding'),
        new moodle_url('/local/adipaonboarding/admin/steps.php'),
        'local/adipaonboarding:manage_steps'
    ));

    $ADMIN->add('local_adipaonboarding_cat', new admin_externalpage(
        'local_adipaonboarding_telemetry',
        new lang_string('admin_section_telemetry', 'local_adipaonboarding'),
        new moodle_url('/local/adipaonboarding/admin/telemetry.php'),
        'local/adipaonboarding:view_telemetry'
    ));
}
