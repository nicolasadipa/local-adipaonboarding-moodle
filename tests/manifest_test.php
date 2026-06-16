<?php
namespace local_adipaonboarding;

use local_adipaonboarding\local\tour\manifest;
use local_adipaonboarding\local\tour\step;
use local_adipaonboarding\local\tour\step_repository;
use local_adipaonboarding\local\tour\tour_repository;
use local_adipaonboarding\local\tour\course_optout_repository;
use local_adipaonboarding\local\tour\telemetry_repository;
use local_adipaonboarding\local\tour\storage;
use local_adipaonboarding\local\tour\seeder;
use local_adipaonboarding\local\visibility\rules;

defined('MOODLE_INTERNAL') || die();

/**
 * @group local_adipaonboarding
 */
class manifest_test extends \advanced_testcase {

    public function test_payload_serializes_steps_resolved(): void {
        $this->resetAfterTest();

        $steps = [
            new step([
                'id'      => 'welcome',
                'element' => 'modal',
                'title'   => 'Bienvenida',
                'body'    => 'Cuerpo',
            ]),
        ];

        $m = new manifest('test_tour', 1, $steps, [
            'delay_ms'  => 5000,
            'frequency' => 'once_per_user',
        ]);
        $payload = $m->payload_for_client();

        $this->assertSame('test_tour', $payload['id']);
        $this->assertSame(1, $payload['version']);
        $this->assertCount(1, $payload['steps']);
        $this->assertSame('Bienvenida', $payload['steps'][0]['title']);
        $this->assertSame(5000, $payload['visibility']['delay_ms']);
        $this->assertArrayHasKey('next', $payload['i18n']);
    }

    public function test_storage_versioning_distinguishes_versions(): void {
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();

        $this->assertFalse(storage::has_seen($user->id, 'tour_x', 1));
        storage::mark_seen($user->id, 'tour_x', 1, true);
        $this->assertTrue(storage::has_seen($user->id, 'tour_x', 1));
        $this->assertFalse(storage::has_seen($user->id, 'tour_x', 2));
    }

    public function test_rules_once_per_user_blocks_after_seen(): void {
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $m = new manifest('tour_y', 1, [], ['frequency' => 'once_per_user']);

        $this->assertTrue(rules::should_show($m, $user->id));
        storage::mark_seen($user->id, 'tour_y', 1, true);
        $this->assertFalse(rules::should_show($m, $user->id));
    }

    public function test_step_repo_resolves_text_over_langkey(): void {
        $this->resetAfterTest();
        global $DB;

        $id = step_repository::insert([
            'step_key'       => 'unit_textonly',
            'selector'       => '.foo',
            'title_text'     => 'Texto directo',
            'body_text'      => 'Cuerpo directo',
            'placement'      => 'bottom',
        ], false);
        $row = $DB->get_record('local_adipaonboarding_steps', ['id' => $id]);
        $hydrated = step_repository::hydrate($row);
        $this->assertSame('Texto directo', $hydrated->title);
        $this->assertSame('Cuerpo directo', $hydrated->body);
    }

    public function test_step_repo_text_overrides_langkey_when_both_present(): void {
        $this->resetAfterTest();
        global $DB;
        seeder::run();
        $row = $DB->get_record('local_adipaonboarding_steps', ['stepkey' => 'welcome']);
        step_repository::update_from_admin((int)$row->id, [
            'title_text' => 'Override custom',
        ]);
        $updated = $DB->get_record('local_adipaonboarding_steps', ['id' => $row->id]);
        $hydrated = step_repository::hydrate($updated);
        $this->assertSame('Override custom', $hydrated->title);
        $this->assertSame(1, (int)$updated->dirty);
    }

    public function test_step_hydrate_applies_override(): void {
        $this->resetAfterTest();
        global $DB;
        $id = step_repository::insert([
            'step_key' => 'unit_override',
            'selector' => '.base',
            'title_text' => 'T',
            'body_text'  => 'B',
            'placement'  => 'bottom',
        ], false);
        $row = $DB->get_record('local_adipaonboarding_steps', ['id' => $id]);
        $hydrated = step_repository::hydrate($row, ['selector' => '.override', 'placement' => 'top']);
        $this->assertSame('.override', $hydrated->element);
        $this->assertSame('top', $hydrated->placement);
    }

    public function test_seeder_populates_and_is_idempotent(): void {
        $this->resetAfterTest();
        global $DB;
        seeder::run();
        $steps1 = $DB->count_records('local_adipaonboarding_steps');
        seeder::run();
        $steps2 = $DB->count_records('local_adipaonboarding_steps');
        $this->assertSame($steps1, $steps2);
        $this->assertGreaterThan(0, $steps1);
    }

    public function test_seeder_respects_dirty_flag(): void {
        $this->resetAfterTest();
        global $DB;
        seeder::run();
        $row = $DB->get_record('local_adipaonboarding_steps', ['stepkey' => 'welcome']);
        step_repository::update_from_admin((int)$row->id, ['selector' => '.custom-edit']);
        seeder::run();
        $after = $DB->get_record('local_adipaonboarding_steps', ['stepkey' => 'welcome']);
        $this->assertSame('.custom-edit', $after->selector);
        $this->assertSame(1, (int)$after->dirty);
    }

    public function test_seeder_get_seed_helpers(): void {
        $this->resetAfterTest();
        $stepseed = seeder::get_step_seed('welcome');
        $this->assertNotNull($stepseed);
        $this->assertSame('welcome', $stepseed['step_key']);

        $tourseed = seeder::get_tour_seed('course_view', 'course');
        $this->assertNotNull($tourseed);
        $this->assertSame('course', $tourseed['course_type']);

        $this->assertNull(seeder::get_step_seed('no_existe'));
        $this->assertNull(seeder::get_tour_seed('course_view', 'no_existe'));
    }

    public function test_step_repo_reset_to_defaults_clears_text_and_dirty(): void {
        $this->resetAfterTest();
        global $DB;
        seeder::run();
        $row = $DB->get_record('local_adipaonboarding_steps', ['stepkey' => 'welcome']);
        step_repository::update_from_admin((int)$row->id, [
            'title_text' => 'Override',
            'selector'   => '.custom',
        ]);
        $seed = seeder::get_step_seed('welcome');
        $ok = step_repository::reset_to_defaults('welcome', $seed);
        $this->assertTrue($ok);
        $after = $DB->get_record('local_adipaonboarding_steps', ['stepkey' => 'welcome']);
        $this->assertNull($after->titletext);
        $this->assertSame(0, (int)$after->dirty);
    }

    public function test_step_repo_tours_using_step(): void {
        $this->resetAfterTest();
        seeder::run();
        $welcomeid = step_repository::id_for_key('welcome');
        $tours = step_repository::tours_using_step($welcomeid);
        $this->assertGreaterThan(0, count($tours));
        $this->assertSame('course_view', $tours[0]->scope);
    }

    public function test_tour_repo_build_manifest_and_reset(): void {
        $this->resetAfterTest();
        seeder::run();
        global $DB;
        $m = tour_repository::build_manifest_for('course_view', 'course');
        $this->assertNotNull($m);
        $stepsbefore = count($m->steps);

        // Mutar y resetear.
        $tour = $DB->get_record('local_adipaonboarding_tours', ['scope' => 'course_view']);
        tour_repository::replace_steps((int)$tour->id, []);
        $empty = tour_repository::build_manifest_for('course_view', 'course');
        $this->assertNull($empty); // sin steps, devuelve null

        $seed = seeder::get_tour_seed('course_view', 'course');
        tour_repository::reset_to_defaults((int)$tour->id, $seed);
        $restored = tour_repository::build_manifest_for('course_view', 'course');
        $this->assertNotNull($restored);
        $this->assertSame($stepsbefore, count($restored->steps));
    }

    public function test_tour_repo_set_step_override(): void {
        $this->resetAfterTest();
        seeder::run();
        global $DB;
        $tour = $DB->get_record('local_adipaonboarding_tours', ['scope' => 'course_view']);
        $ts = $DB->get_record_sql(
            'SELECT id FROM {local_adipaonboarding_tsteps} WHERE tourid = ? ORDER BY sortorder ASC LIMIT 1',
            [$tour->id]
        );
        tour_repository::set_step_override((int)$ts->id, ['selector' => '.x', 'placement' => 'top']);
        $row = $DB->get_record('local_adipaonboarding_tsteps', ['id' => $ts->id]);
        $decoded = json_decode($row->overridejson, true);
        $this->assertSame('.x', $decoded['selector']);

        tour_repository::set_step_override((int)$ts->id, null);
        $row = $DB->get_record('local_adipaonboarding_tsteps', ['id' => $ts->id]);
        $this->assertNull($row->overridejson);
    }

    public function test_course_optout_repo(): void {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $this->assertFalse(course_optout_repository::is_disabled($course->id));
        course_optout_repository::set($course->id, true, 2);
        $this->assertTrue(course_optout_repository::is_disabled($course->id));
        course_optout_repository::set($course->id, false, 2);
        $this->assertFalse(course_optout_repository::is_disabled($course->id));
    }

    public function test_telemetry_repo_handles_empty(): void {
        $this->resetAfterTest();
        $totals = telemetry_repository::totals();
        $this->assertSame(0, $totals['completed']);
        $this->assertSame(0, $totals['dismissed']);
        $this->assertSame(0, $totals['completion_rate']);
        $this->assertSame([], telemetry_repository::tour_summary());
    }

    public function test_telemetry_repo_aggregates_events(): void {
        $this->resetAfterTest();
        $u1 = $this->getDataGenerator()->create_user();
        $u2 = $this->getDataGenerator()->create_user();
        storage::log_event($u1->id, 'tour_a', 1, '_tour', 'completed');
        storage::log_event($u2->id, 'tour_a', 1, '_tour', 'dismissed');
        storage::log_event($u1->id, 'tour_a', 1, 'step_x', 'viewed');
        storage::log_event($u2->id, 'tour_a', 1, 'step_x', 'viewed');

        $totals = telemetry_repository::totals();
        $this->assertSame(1, $totals['completed']);
        $this->assertSame(1, $totals['dismissed']);
        $this->assertSame(50.0, $totals['completion_rate']);

        $dropoff = telemetry_repository::step_drop_off('tour_a');
        $this->assertCount(1, $dropoff);
        $this->assertSame('step_x', $dropoff[0]->stepid);
        $this->assertSame(2, (int)$dropoff[0]->viewed_count);
    }
}
