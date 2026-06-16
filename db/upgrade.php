<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade del plugin local_adipaonboarding.
 *
 * - v0.2.0 (2026061500): refactor Step Library + Tour Composition. 5 tablas.
 * - v0.3.0 (2026061600): editor completo. Agrega titletext/bodytext a steps,
 *                        nueva tabla optout. Reseed defaults para usuarios shipped.
 */
function xmldb_local_adipaonboarding_upgrade(int $oldversion): bool {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2026061500) {

        $stepstable = new xmldb_table('local_adipaonboarding_steps');
        if (!$dbman->table_exists($stepstable)) {
            $stepstable->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
            $stepstable->add_field('stepkey', XMLDB_TYPE_CHAR, '64', null, XMLDB_NOTNULL);
            $stepstable->add_field('selector', XMLDB_TYPE_CHAR, '512', null, XMLDB_NOTNULL);
            $stepstable->add_field('titlelangkey', XMLDB_TYPE_CHAR, '128', null, null);
            $stepstable->add_field('bodylangkey', XMLDB_TYPE_CHAR, '128', null, null);
            $stepstable->add_field('placement', XMLDB_TYPE_CHAR, '16', null, XMLDB_NOTNULL, null, 'auto');
            $stepstable->add_field('responsivejson', XMLDB_TYPE_TEXT, null, null, null);
            $stepstable->add_field('shipped', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
            $stepstable->add_field('dirty', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
            $stepstable->add_field('createdat', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
            $stepstable->add_field('updatedat', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
            $stepstable->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
            $stepstable->add_index('stepkey_unique', XMLDB_INDEX_UNIQUE, ['stepkey']);
            $dbman->create_table($stepstable);
        }

        $tourstable = new xmldb_table('local_adipaonboarding_tours');
        if (!$dbman->table_exists($tourstable)) {
            $tourstable->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
            $tourstable->add_field('scope', XMLDB_TYPE_CHAR, '32', null, XMLDB_NOTNULL);
            $tourstable->add_field('coursetype', XMLDB_TYPE_CHAR, '32', null, null);
            $tourstable->add_field('version', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '1');
            $tourstable->add_field('enabled', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
            $tourstable->add_field('visibilityjson', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL);
            $tourstable->add_field('shipped', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
            $tourstable->add_field('dirty', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
            $tourstable->add_field('createdat', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
            $tourstable->add_field('updatedat', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
            $tourstable->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
            $tourstable->add_index('scope_coursetype', XMLDB_INDEX_NOTUNIQUE, ['scope', 'coursetype']);
            $dbman->create_table($tourstable);
        }

        $junctiontable = new xmldb_table('local_adipaonboarding_tsteps');
        if (!$dbman->table_exists($junctiontable)) {
            $junctiontable->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
            $junctiontable->add_field('tourid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
            $junctiontable->add_field('stepid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
            $junctiontable->add_field('sortorder', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
            $junctiontable->add_field('overridejson', XMLDB_TYPE_TEXT, null, null, null);
            $junctiontable->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
            $junctiontable->add_key('tourid_fk', XMLDB_KEY_FOREIGN, ['tourid'], 'local_adipaonboarding_tours', ['id']);
            $junctiontable->add_key('stepid_fk', XMLDB_KEY_FOREIGN, ['stepid'], 'local_adipaonboarding_steps', ['id']);
            $junctiontable->add_index('tour_sort', XMLDB_INDEX_UNIQUE, ['tourid', 'sortorder']);
            $dbman->create_table($junctiontable);
        }

        \local_adipaonboarding\local\tour\seeder::run();

        upgrade_plugin_savepoint(true, 2026061500, 'local', 'adipaonboarding');
    }

    if ($oldversion < 2026061600) {

        // Agregar titletext y bodytext a steps.
        $stepstable = new xmldb_table('local_adipaonboarding_steps');

        $titletext = new xmldb_field('titletext', XMLDB_TYPE_TEXT, null, null, null, null, null, 'bodylangkey');
        if (!$dbman->field_exists($stepstable, $titletext)) {
            $dbman->add_field($stepstable, $titletext);
        }
        $bodytext = new xmldb_field('bodytext', XMLDB_TYPE_TEXT, null, null, null, null, null, 'titletext');
        if (!$dbman->field_exists($stepstable, $bodytext)) {
            $dbman->add_field($stepstable, $bodytext);
        }

        // Lang keys ahora nullable (para permitir steps custom solo con text).
        $titlelangkey = new xmldb_field('titlelangkey', XMLDB_TYPE_CHAR, '128', null, null, null, null);
        if ($dbman->field_exists($stepstable, $titlelangkey)) {
            $dbman->change_field_notnull($stepstable, $titlelangkey);
        }
        $bodylangkey = new xmldb_field('bodylangkey', XMLDB_TYPE_CHAR, '128', null, null, null, null);
        if ($dbman->field_exists($stepstable, $bodylangkey)) {
            $dbman->change_field_notnull($stepstable, $bodylangkey);
        }

        // Tabla optout per-curso.
        $optouttable = new xmldb_table('local_adipaonboarding_optout');
        if (!$dbman->table_exists($optouttable)) {
            $optouttable->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
            $optouttable->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
            $optouttable->add_field('optout', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
            $optouttable->add_field('updatedby', XMLDB_TYPE_INTEGER, '10', null, null);
            $optouttable->add_field('updatedat', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
            $optouttable->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
            $optouttable->add_key('courseid_fk_unique', XMLDB_KEY_FOREIGN_UNIQUE, ['courseid'], 'course', ['id']);
            $dbman->create_table($optouttable);
        }

        upgrade_plugin_savepoint(true, 2026061600, 'local', 'adipaonboarding');
    }

    if ($oldversion < 2026061800) {
        // v0.5.0: nuevo scope mod_adipavideo + selectores actualizados para prod.
        // Reseed: agrega nuevos steps/tours; preserva los que tienen dirty=1.
        \local_adipaonboarding\local\tour\seeder::run();
        upgrade_plugin_savepoint(true, 2026061800, 'local', 'adipaonboarding');
    }

    if ($oldversion < 2026061802) {
        // v0.5.2: fix selector video_capsule + reseed para propagar.
        \local_adipaonboarding\local\tour\seeder::run();
        upgrade_plugin_savepoint(true, 2026061802, 'local', 'adipaonboarding');
    }

    if ($oldversion < 2026061803) {
        // v0.6.0: nuevo step nid_row + fix selector certification + reseed.
        \local_adipaonboarding\local\tour\seeder::run();
        upgrade_plugin_savepoint(true, 2026061803, 'local', 'adipaonboarding');
    }

    if ($oldversion < 2026061804) {
        // v0.6.1: fix video_transcript/video_notes selectors apuntan a tab btn (visible).
        \local_adipaonboarding\local\tour\seeder::run();
        upgrade_plugin_savepoint(true, 2026061804, 'local', 'adipaonboarding');
    }

    if ($oldversion < 2026061900) {
        // v0.7.0: Sprint B — 6 course_types nuevos + scope dashboard.
        \local_adipaonboarding\local\tour\seeder::run();
        upgrade_plugin_savepoint(true, 2026061900, 'local', 'adipaonboarding');
    }

    if ($oldversion < 2026061903) {
        // v0.8.0: Sprint C — hero/popup mode.
        // Agrega mediaurl + mediatype a steps; kind a tours.

        $stepstable = new xmldb_table('local_adipaonboarding_steps');

        $mediaurl = new xmldb_field('mediaurl', XMLDB_TYPE_TEXT, null, null, null, null, null, 'bodytext');
        if (!$dbman->field_exists($stepstable, $mediaurl)) {
            $dbman->add_field($stepstable, $mediaurl);
        }
        $mediatype = new xmldb_field('mediatype', XMLDB_TYPE_CHAR, '16', null, null, null, null, 'mediaurl');
        if (!$dbman->field_exists($stepstable, $mediatype)) {
            $dbman->add_field($stepstable, $mediatype);
        }

        $tourstable = new xmldb_table('local_adipaonboarding_tours');
        $kind = new xmldb_field('kind', XMLDB_TYPE_CHAR, '16', null, XMLDB_NOTNULL, null, 'tour', 'coursetype');
        if (!$dbman->field_exists($tourstable, $kind)) {
            $dbman->add_field($tourstable, $kind);
        }

        \local_adipaonboarding\local\tour\seeder::run();

        upgrade_plugin_savepoint(true, 2026061903, 'local', 'adipaonboarding');
    }

    if ($oldversion < 2026061904) {
        // v0.9.0: Sprint D — pre_action engine.
        // Agrega actionsjson a steps (JSON array de acciones tipo click/wait/scroll_to).

        $stepstable = new xmldb_table('local_adipaonboarding_steps');
        // En v1.0.0 el campo "mediatype" desaparece. Como base de inicio para el
        // nuevo campo "actionsjson", usamos "bodytext" (siempre existe).
        $actionsjson = new xmldb_field('actionsjson', XMLDB_TYPE_TEXT, null, null, null, null, null, 'bodytext');
        if (!$dbman->field_exists($stepstable, $actionsjson)) {
            $dbman->add_field($stepstable, $actionsjson);
        }

        // Reseed para propagar pre_actions a video_notes/video_transcript/video_capsule.
        \local_adipaonboarding\local\tour\seeder::run();

        upgrade_plugin_savepoint(true, 2026061904, 'local', 'adipaonboarding');
    }

    if ($oldversion < 2026061911) {
        // v1.0.0: Cleanup sprint.
        //   - Drop columnas Sprint C (kind, mediaurl, mediatype). Hero/popup mode removed.
        //   - Drop tours del scope 'dashboard' + sus tsteps + sus steps huerfanos.
        //   - Remover video_capsule de tour_steps (el step se va de la library tambien).

        // 1) Drop columnas hero.
        $stepstable = new xmldb_table('local_adipaonboarding_steps');
        foreach (['mediaurl', 'mediatype'] as $colname) {
            $field = new xmldb_field($colname);
            if ($dbman->field_exists($stepstable, $field)) {
                $dbman->drop_field($stepstable, $field);
            }
        }
        $tourstable = new xmldb_table('local_adipaonboarding_tours');
        $kindfield = new xmldb_field('kind');
        if ($dbman->field_exists($tourstable, $kindfield)) {
            $dbman->drop_field($tourstable, $kindfield);
        }

        // 2) Drop scope dashboard. tsteps tiene FK a tours, asi que primero limpiamos tsteps.
        $dashtourids = $DB->get_fieldset_select('local_adipaonboarding_tours', 'id', 'scope = ?', ['dashboard']);
        if (!empty($dashtourids)) {
            list($insql, $inparams) = $DB->get_in_or_equal($dashtourids);
            $DB->delete_records_select('local_adipaonboarding_tsteps', "tourid $insql", $inparams);
            $DB->delete_records_select('local_adipaonboarding_tours',  "id $insql",     $inparams);
        }

        // 3) Drop steps con stepkey que empieza con 'dash_' (huerfanos del scope).
        $dashstepids = $DB->get_fieldset_select(
            'local_adipaonboarding_steps',
            'id',
            $DB->sql_like('stepkey', ':pattern'),
            ['pattern' => 'dash_%']
        );
        if (!empty($dashstepids)) {
            list($insql, $inparams) = $DB->get_in_or_equal($dashstepids);
            $DB->delete_records_select('local_adipaonboarding_tsteps', "stepid $insql", $inparams);
            $DB->delete_records_select('local_adipaonboarding_steps',  "id $insql",     $inparams);
        }

        // 4) Remover video_capsule de la library + sus tsteps.
        $vcstepid = $DB->get_field('local_adipaonboarding_steps', 'id', ['stepkey' => 'video_capsule']);
        if ($vcstepid) {
            $DB->delete_records('local_adipaonboarding_tsteps', ['stepid' => $vcstepid]);
            $DB->delete_records('local_adipaonboarding_steps',  ['id' => $vcstepid]);
        }

        // 5) Reseed para que los tours quedan en estado coherente con los seeds nuevos.
        \local_adipaonboarding\local\tour\seeder::run();

        upgrade_plugin_savepoint(true, 2026061911, 'local', 'adipaonboarding');
    }

    if ($oldversion < 2026061913) {
        // v1.0.2: responsive overrides + nuevo placement nid_row + skip-on-mobile
        // para video_sidebar/video_progress. Reseed para que los steps shipped
        // reflejen los nuevos responsivejson y placements.
        \local_adipaonboarding\local\tour\seeder::run();
        upgrade_plugin_savepoint(true, 2026061913, 'local', 'adipaonboarding');
    }

    if ($oldversion < 2026061914) {
        // v1.0.3: nada de schema — solo fix de runner/telemetry/CSS. Bump por bookkeeping.
        upgrade_plugin_savepoint(true, 2026061914, 'local', 'adipaonboarding');
    }

    if ($oldversion < 2026061915) {
        // v1.0.4: CSS-only — breakpoint trigger (?) banner alineado a 700px de format_adipa.
        upgrade_plugin_savepoint(true, 2026061915, 'local', 'adipaonboarding');
    }

    if ($oldversion < 2026061916) {
        // v1.0.5: agrega progress_bar_date (sync) y progress_bar_completion (async)
        // a la Step Library + a las secuencias de tour. Bumpea version de cada tour.
        \local_adipaonboarding\local\tour\seeder::run();
        upgrade_plugin_savepoint(true, 2026061916, 'local', 'adipaonboarding');
    }

    if ($oldversion < 2026061917) {
        // v1.0.6: incluye ambos progress_bar variants en todas las secuencias
        // (fallback para sync courses sin sesiones configuradas). Bump version
        // de tour para forzar re-show del recorrido renovado.
        \local_adipaonboarding\local\tour\seeder::run();
        upgrade_plugin_savepoint(true, 2026061917, 'local', 'adipaonboarding');
    }

    return true;
}
