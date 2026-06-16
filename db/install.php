<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Callback de instalacion fresca del plugin.
 *
 * Se ejecuta despues de que Moodle crea las tablas desde install.xml.
 * Corre el seeder para poblar Step Library + Tours desde tours/*.php.
 *
 * (En upgrades de v0.1.0+, el seeder lo dispara upgrade.php.)
 */
function xmldb_local_adipaonboarding_install(): bool {
    \local_adipaonboarding\local\tour\seeder::run();
    return true;
}
