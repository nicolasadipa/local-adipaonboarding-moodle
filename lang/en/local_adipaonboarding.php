<?php
defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'ADIPA Onboarding';
$string['errornotfound'] = 'The requested resource was not found.';
$string['on'] = 'Enabled';
$string['off'] = 'Disabled';

$string['welcome_title'] = '✨ A renewed experience';
$string['welcome_body'] = 'We improved every detail of the classroom so your experience is the best! Grow professionally with the content and technology you deserve. We are with you on this journey 🎉';
$string['closing_title'] = 'You are all set!';
$string['closing_body'] = 'Whenever you want to walk through the classroom again, click the "How to use the classroom?" button at the bottom. We are here to support you every step of the way.';

$string['step_header_title'] = 'Your program, always visible';
$string['step_header_body'] = 'Here you will always see your program name and type. We designed this header so you know, at a glance, where you are inside the classroom.';
$string['step_view_toggle_title'] = 'Choose how you prefer to study';
$string['step_view_toggle_body'] = 'You can now switch between <strong>Mosaic</strong> (visual card view) and <strong>Accordion</strong> (compact view, ideal for review). You choose the format that best fits how you learn.';
$string['step_adipainfo_title'] = 'All your information in one place';
$string['step_adipainfo_body'] = 'Sessions, assessments and certification, organized with clarity. We always show your next key date so nothing slips through the cracks.';
$string['step_session_pill_title'] = 'Your next live session';
$string['step_session_pill_body'] = 'See how many sessions you have completed and when the next one is. We built this view so you never lose track of your program.';
$string['step_countdown_title'] = 'Real-time countdown';
$string['step_countdown_body'] = 'The clock updates itself, in your timezone. Technology built to accompany you minute by minute until your next session.';
$string['step_first_module_title'] = 'Start here';
$string['step_first_module_body'] = 'Each card is a module of your program. Move at your pace: our system tracks your progress so you always know where to pick up.';
$string['step_certification_title'] = 'Your achievement, secured';
$string['step_certification_body'] = 'When you complete your program, you can download your certification from here. Every step brings you closer to this achievement.';
$string['step_nid_title'] = 'Your ID document';
$string['step_nid_body']  = 'Before your certification, upload your ID document. It is a key step so your certificate is issued with your correct data.';

$string['step_documentation_title'] = 'Program documentation';
$string['step_documentation_body']  = 'All the official documentation for your accreditation is in this highlighted tile. Review it whenever you need — it is your reference.';

$string['setting_restricted_courses']      = 'Rollout: show tour only in these courses (IDs)';
$string['setting_restricted_courses_desc'] = 'Course IDs separated by comma (e.g. <code>2203, 2204</code>). Leave empty to show in ALL eligible courses. <strong>Use for gradual rollout</strong>: start with one or two pilot courses, watch Reports, then clear the field to release to all. NOT the same as the "Test ID" inside the step/tour editor (that one is just for admin preview).';

$string['trigger_button_label'] = 'How to use the classroom?';
$string['trigger_button_aria'] = 'Replay the classroom welcome tour';
$string['btn_next'] = 'Next';
$string['btn_prev'] = 'Back';
$string['btn_done'] = 'Finish';
$string['btn_skip'] = 'Skip tour';
$string['progress_text'] = '{{current}} of {{total}}';

$string['step_actions_label']     = 'Pre-actions (JSON)';
$string['step_actions_help']      = 'Optional. List of actions to run BEFORE showing the popover, in order. Useful to open a tab before highlighting. <br><strong>Types:</strong> <code>click</code> (requires <code>selector</code>), <code>wait</code> (requires <code>ms</code>).<br><strong>Example:</strong> <code>[{"type":"click","selector":".adv-tab-btn[data-tab=\"apuntes\"]"},{"type":"wait","ms":200}]</code>';

$string['admin_section_settings']  = 'Settings';
$string['admin_section_tours']     = 'Tours';
$string['admin_section_steps']     = 'Step Library';
$string['admin_section_telemetry'] = 'Reports';

$string['setting_enabled'] = 'Enable onboarding';
$string['setting_enabled_desc'] = 'When disabled, no student will see the tour (not even the replay button).';
$string['setting_default_delay'] = 'Default delay (ms)';
$string['setting_default_delay_desc'] = 'Time the tour waits before appearing when the student loads the page for the first time.';
$string['setting_reset_all'] = 'Reset "seen" status for all students';
$string['setting_reset_all_desc'] = 'Useful when you release new features and want all students to see the tour again.';
$string['setting_reset_all_btn'] = 'Reset now';
$string['setting_reset_all_done'] = '{$a} "seen" records were reset.';

$string['tours_list_col_scope']       = 'Scope';
$string['tours_list_col_course_type'] = 'Course type';
$string['tours_list_col_steps']       = 'Steps';
$string['tours_list_col_version']     = 'Version';
$string['tours_list_col_enabled']     = 'Enabled';
$string['tours_list_col_actions']     = 'Actions';
$string['tours_list_action_edit']     = 'Edit';
$string['tours_list_dirty_badge']     = 'Manually modified — upgrade will not overwrite it';
$string['tours_list_empty']           = 'No tours in the database yet. Reinstall the plugin or run the seeder.';
$string['tours_list_enabled_msg']     = 'Tour enabled.';
$string['tours_list_disabled_msg']    = 'Tour disabled.';

$string['tour_edit_title']              = 'Edit tour';
$string['tour_edit_back_btn']           = 'Back to list';
$string['tour_edit_steps_panel']        = 'Steps in this tour';
$string['tour_edit_library_panel']      = 'Step Library (drag to add)';
$string['tour_edit_library_empty']      = 'All steps are already in this tour.';
$string['tour_edit_save_btn']           = 'Save changes';
$string['tour_edit_bump_version_btn']   = 'Bump version & relaunch';
$string['tour_edit_saved']              = 'Tour saved successfully.';
$string['tour_edit_version_bumped']     = 'Version updated to v{$a}. Students will see it again.';
$string['tour_edit_dirty_warning']      = 'This tour was manually modified. Plugin updates will not overwrite it.';
$string['tour_edit_visibility_section'] = 'Visibility rules';
$string['tour_edit_lbl_enabled']        = 'Tour enabled';
$string['tour_edit_lbl_delay']          = 'Delay (ms)';
$string['tour_edit_lbl_min_viewport']   = 'Min viewport (px)';
$string['tour_edit_lbl_frequency']      = 'Frequency';
$string['tour_edit_drag_hint']          = 'Drag steps to reorder them or move them between panels. Changes save automatically.';
$string['tour_step_remove']             = 'Remove from this tour';

$string['freq_once_per_user']  = 'Once per user';
$string['freq_once_per_day']   = 'Once per day';
$string['freq_every_visit']    = 'Every visit';

$string['steps_list_col_key']          = 'Step key';
$string['steps_list_col_selector']     = 'Selector';
$string['steps_list_col_title']        = 'Title';
$string['steps_list_col_placement']    = 'Placement';
$string['steps_list_empty']            = 'The Step Library is empty. Reinstall the plugin to run the seeder.';
$string['steps_list_read_only_notice'] = 'Read-only view. The Step Library editor lands next sprint.';

$string['adipaonboarding:reset_all_seen'] = 'Reset tours seen by all users';
$string['adipaonboarding:manage_tours']   = 'Edit onboarding tours';
$string['adipaonboarding:manage_steps']   = 'Edit the onboarding Step Library';
$string['adipaonboarding:view_telemetry'] = 'View onboarding telemetry';

// Step Library editor.
$string['step_edit_title']         = 'Edit step';
$string['step_edit_new_title']     = 'New step';
$string['step_edit_lbl_step_key']  = 'Step key';
$string['step_edit_lbl_selector']  = 'CSS selector';
$string['step_edit_lbl_title_text'] = 'Title (direct text)';
$string['step_edit_lbl_body_text']  = 'Body (direct text)';
$string['step_edit_lbl_placement']  = 'Placement';
$string['step_edit_hint_step_key']  = 'Stable identifier: only letters, numbers and underscores. Cannot be changed later.';
$string['step_edit_hint_text_overrides_lang'] = 'If empty, the lang key translation is used';
$string['step_edit_shipped_info']  = 'This step ships with the plugin. You can override the text, but the lang key is preserved for multi-country.';
$string['step_edit_custom_info']   = 'Step created by an administrator. Uses direct text (no lang key).';
$string['step_edit_preview_title'] = 'Resolved title preview';
$string['step_edit_preview_body']  = 'Resolved body preview';
$string['step_edit_save_btn']      = 'Save';
$string['step_edit_reset_btn']     = 'Reset to defaults';
$string['step_edit_delete_btn']    = 'Delete';
$string['step_edit_back_btn']      = 'Back to list';
$string['step_edit_confirm_delete'] = 'Delete this step?';
$string['step_edit_confirm_reset']  = 'Reset this step to its original seed values? Manual changes will be lost.';
$string['step_edit_saved']         = 'Step saved successfully.';
$string['step_edit_deleted']       = 'Step deleted.';
$string['step_edit_reset_done']    = 'Step reset to defaults.';
$string['step_edit_validation_required'] = 'Step key and selector are required.';
$string['step_edit_delete_blocked'] = 'This step is used by {$a} tour(s):';

$string['steps_list_new_btn']        = 'New step';
$string['steps_list_shipped_badge']  = 'Shipped step (comes with plugin)';
$string['steps_list_custom_badge']   = 'Custom step (created by admin)';

$string['tour_edit_reset_btn']       = 'Reset to defaults';
$string['tour_edit_confirm_reset']   = 'Reset this tour to its seed? Manual changes will be lost (steps, overrides, visibility).';
$string['tour_edit_reset_done']      = 'Tour reset to defaults.';
$string['tour_step_override_btn']    = 'Override';
$string['tour_step_override_title']  = 'Override the step for this tour';
$string['tour_step_override_lbl_selector']  = 'Selector (empty = use library default)';
$string['tour_step_override_lbl_placement'] = 'Placement (— = use library default)';
$string['tour_step_override_clear']  = 'Clear override';
$string['tour_step_override_apply']  = 'Apply';
$string['tour_step_override_cancel'] = 'Cancel';

$string['telemetry_overview_title']   = 'Overview';
$string['telemetry_card_completed']   = 'Completed tours';
$string['telemetry_card_dismissed']   = 'Dismissed without finishing';
$string['telemetry_card_finished']    = 'Finished tours';
$string['telemetry_card_completion']  = 'Completion rate';
$string['telemetry_tours_table_title'] = 'Per tour';
$string['telemetry_col_tourid']    = 'Tour';
$string['telemetry_col_finished']  = 'Finished';
$string['telemetry_col_completed'] = 'Completed';
$string['telemetry_col_dismissed'] = 'Dismissed';
$string['telemetry_col_rate']      = 'Rate';
$string['telemetry_dropoff_title'] = 'Drop-off per step';
$string['telemetry_empty']         = 'No telemetry data yet. Wait for students to walk through the tours.';
$string['telemetry_reset_btn']     = 'Clear reports';
$string['telemetry_reset_hint']    = 'Deletes all recorded events. Useful if you have leftover data from earlier testing.';
$string['telemetry_reset_confirm'] = 'Delete ALL telemetry events? This cannot be undone.';
$string['telemetry_reset_done']    = 'Deleted {$a} telemetry events.';

$string['course_optout_nav_label']    = 'Onboarding';
$string['course_optout_page_title']   = 'Course onboarding';
$string['course_optout_page_heading'] = 'Onboarding · {$a}';
$string['course_optout_description']  = 'Disable the welcome tour for this course. Useful when a course does not benefit from the tour (e.g. internal-only course).';
$string['course_optout_lbl_toggle']   = 'Disable tour for this course';
$string['course_optout_hint_on']      = 'The tour is NOT shown to students of this course.';
$string['course_optout_hint_off']     = 'The tour IS shown (normal behavior).';
$string['course_optout_saved']        = 'Settings saved.';

$string['step_edit_test_selector_btn']         = 'Test selector';
$string['step_edit_test_selector_label']       = 'Test selector on a course';
$string['step_edit_test_selector_hint']        = 'Opens the course in a new tab and highlights matching elements.';
$string['step_edit_test_selector_no_selector'] = 'Type a selector first.';
$string['test_course_id_label']                = 'Course ID:';
$string['test_course_id_required']             = 'Set a test course ID first.';
$string['tour_edit_preview_btn']               = 'Preview tour';
$string['preview_banner_label']                = 'Preview mode — admin only (no seen state or telemetry saved)';
$string['selector_tester_matches']             = '{$a} match(es) found';
$string['selector_tester_error']               = 'Invalid selector';
$string['selector_tester_close']               = 'Close';

$string['video_step_welcome_title'] = '🎬 Your new player';
$string['video_step_welcome_body']  = 'We fully renewed the player so you get the most out of every capsule. Notes, transcript, bookmarks and more — all built to support you on your learning journey.';
$string['video_step_sidebar_title'] = 'Your capsules, always at hand';
$string['video_step_sidebar_body']  = 'On the side panel you see all the program capsules. Your progress saves automatically: the system knows where you left off.';
$string['video_step_player_title']  = 'Your player with superpowers';
$string['video_step_player_body']   = 'Adaptive quality, markers and adjustable speed. Designed so you learn comfortably, at your pace.';
$string['video_step_tabs_title']    = 'More than a video';
$string['video_step_tabs_body']     = 'In the tabs you have notes, transcript, summary and resources. Everything you need in one place.';
$string['video_step_notes_title']   = 'Notes with timestamps';
$string['video_step_notes_body']    = 'While watching, jot down whatever you want. The system saves the exact minute you wrote it — so you go right back to that point when reviewing.';
$string['video_step_transcript_title'] = 'Search any word in the video';
$string['video_step_transcript_body']  = 'The full transcript is searchable. Want to review a concept? Type it in the search and jump straight to that minute.';
$string['video_step_palette_title'] = 'Choose how it looks';
$string['video_step_palette_body']  = 'Switch between light and dark theme to your preference. Small details that make your studying more pleasant.';
$string['video_step_progress_title'] = 'Real-time progress';
$string['video_step_progress_body']  = 'The top bar shows how much of the program you have completed. Each capsule adds up — bringing you closer to your certification.';
$string['video_step_closing_title'] = 'Enjoy your program!';
$string['video_step_closing_body']  = 'Whenever you want to replay the tour, look for the "How to use the classroom?" button at the bottom-right. We are here to support you.';

$string['privacy:metadata:local_adipaonboarding_seen'] = 'Record of which tours each student has seen.';
$string['privacy:metadata:local_adipaonboarding_seen:userid'] = 'User ID';
$string['privacy:metadata:local_adipaonboarding_seen:tourid'] = 'Tour ID';
$string['privacy:metadata:local_adipaonboarding_seen:tourversion'] = 'Tour version when seen';
$string['privacy:metadata:local_adipaonboarding_seen:seenat'] = 'Date when the tour was completed';
$string['privacy:metadata:local_adipaonboarding_events'] = 'Telemetry events (step viewed, skipped, completed) of the tour.';
